<?php
/**
 * RequisicionService
 * Business logic for purchase requisitions with supplier intelligence
 */
require_once __DIR__ . '/../Repository/RequisicionRepository.php';
require_once __DIR__ . '/CargoService.php';

class RequisicionService {
    private $repository;
    private $cargoService;

    public function __construct() {
        $this->repository = new RequisicionRepository();
        $this->cargoService = new CargoService();
    }

    // ==========================================
    // LIST & READ
    // ==========================================

    public function listRequisiciones(array $filters = []): array {
        return $this->repository->findAll($filters);
    }

    public function getRequisicion(int $id): array {
        $requisicion = $this->repository->findById($id);
        if (!$requisicion) {
            throw new Exception('Requisición no encontrada');
        }
        return $requisicion;
    }

    // ==========================================
    // SUPPLIER INTELLIGENCE
    // ==========================================

    /**
     * Suggest best supplier for a product using 3-level fallback:
     * 1. precios_proveedor table (historical prices)
     * 2. cargos_existencias history (confirmed purchases)
     * 3. Default supplier assigned to product
     */
    public function sugerirMejorProveedor(int $productoId): ?array {
        // Level 1: Price history table
        $result = $this->repository->getBestSupplierFromPrices($productoId);
        if ($result) {
            $result['fuente'] = 'historial_precios';
            return $result;
        }

        // Level 2: Cargo history
        $result = $this->repository->getBestSupplierFromCargos($productoId);
        if ($result) {
            $result['fuente'] = 'historial_cargos';
            return $result;
        }

        // Level 3: Product default supplier
        $result = $this->repository->getDefaultSupplier($productoId);
        if ($result) {
            $result['fuente'] = 'proveedor_default';
            return $result;
        }

        return null;
    }

    /**
     * Get all active products with low/zero stock for auto-population
     */
    public function getProductosParaRequisicion(): array {
        $productos = $this->repository->getProductosStockBajo();

        // Enrich with supplier suggestions
        foreach ($productos as &$producto) {
            $suggestion = $this->sugerirMejorProveedor($producto['id']);
            $producto['proveedor_sugerido'] = $suggestion;
        }

        return $productos;
    }

    // ==========================================
    // CREATE & UPDATE
    // ==========================================

    public function createRequisicion(array $data, int $userId): array {
        $this->validateData($data);

        $this->repository->beginTransaction();
        try {
            $codigo = $this->repository->generateNextCode();

            $headerData = [
                'codigo' => $codigo,
                'fecha_solicitud' => $data['fecha_solicitud'] ?? date('Y-m-d'),
                'prioridad' => $data['prioridad'] ?? 'media',
                'notas' => $data['notas'] ?? null,
                'usuario_id' => $userId,
                'estado' => 'pendiente'
            ];

            $requisicionId = $this->repository->createHeader($headerData);

            // Process lines with supplier suggestions
            foreach ($data['lineas'] ?? [] as $linea) {
                if (empty($linea['producto_id']) || empty($linea['cantidad_solicitada'])) continue;

                $suggestion = $this->sugerirMejorProveedor($linea['producto_id']);

                $lineData = [
                    'producto_id' => $linea['producto_id'],
                    'cantidad_solicitada' => intval($linea['cantidad_solicitada']),
                    'proveedor_sugerido_id' => $suggestion['proveedor_id'] ?? null,
                    'precio_sugerido' => $suggestion['precio_compra'] ?? null,
                    'proveedor_seleccionado_id' => $linea['proveedor_seleccionado_id'] ?? null,
                    'notas' => $linea['notas'] ?? null
                ];

                $this->repository->insertDetail($requisicionId, $lineData);
            }

            $this->repository->commit();
            return ['id' => $requisicionId, 'codigo' => $codigo, 'estado' => 'pendiente'];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Create requisition auto-populated with all low-stock active products
     */
    public function createFromLowStock(int $userId, string $prioridad = 'media'): array {
        $productos = $this->repository->getProductosStockBajo();

        if (empty($productos)) {
            throw new Exception('No hay productos con stock bajo que requieran reposición');
        }

        $lineas = [];
        foreach ($productos as $producto) {
            $cantidadSugerida = max(1, $producto['cantidad_faltante']);
            $lineas[] = [
                'producto_id' => $producto['id'],
                'cantidad_solicitada' => $cantidadSugerida
            ];
        }

        return $this->createRequisicion([
            'fecha_solicitud' => date('Y-m-d'),
            'prioridad' => $prioridad,
            'notas' => 'Requisición automática por stock bajo (' . count($lineas) . ' productos)',
            'lineas' => $lineas
        ], $userId);
    }

    public function updateRequisicion(int $id, array $data, int $userId): array {
        $current = $this->getRequisicion($id);
        if ($current['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden editar requisiciones pendientes');
        }

        $this->validateData($data);

        $this->repository->beginTransaction();
        try {
            $this->repository->updateHeader($id, [
                'fecha_solicitud' => $data['fecha_solicitud'] ?? $current['fecha_solicitud'],
                'prioridad' => $data['prioridad'] ?? $current['prioridad'],
                'notas' => $data['notas'] ?? $current['notas']
            ]);

            // Replace lines
            $this->repository->deleteDetails($id);

            foreach ($data['lineas'] ?? [] as $linea) {
                if (empty($linea['producto_id']) || empty($linea['cantidad_solicitada'])) continue;

                $suggestion = $this->sugerirMejorProveedor($linea['producto_id']);

                $lineData = [
                    'producto_id' => $linea['producto_id'],
                    'cantidad_solicitada' => intval($linea['cantidad_solicitada']),
                    'proveedor_sugerido_id' => $suggestion['proveedor_id'] ?? null,
                    'precio_sugerido' => $suggestion['precio_compra'] ?? null,
                    'proveedor_seleccionado_id' => $linea['proveedor_seleccionado_id'] ?? null,
                    'notas' => $linea['notas'] ?? null
                ];

                $this->repository->insertDetail($id, $lineData);
            }

            $this->repository->commit();
            return ['id' => $id, 'estado' => 'pendiente'];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    // ==========================================
    // WORKFLOW: APPROVE / REJECT / PROCESS
    // ==========================================

    public function aprobarRequisicion(int $id, int $aprobadorId): void {
        $current = $this->getRequisicion($id);
        if ($current['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden aprobar requisiciones pendientes');
        }
        if (!$this->repository->hasDetails($id)) {
            throw new Exception('La requisición no tiene productos');
        }
        $this->repository->updateStatus($id, 'aprobada', $aprobadorId);
    }

    public function rechazarRequisicion(int $id, int $aprobadorId): void {
        $current = $this->getRequisicion($id);
        if ($current['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden rechazar requisiciones pendientes');
        }
        $this->repository->updateStatus($id, 'rechazada', $aprobadorId);
    }

    /**
     * Generate draft Cargos from approved requisition
     * Groups lines by supplier and creates one cargo per supplier
     */
    public function generarCargos(int $requisicionId, int $userId, ?int $almacenId = null): array {
        $req = $this->getRequisicion($requisicionId);

        if ($req['estado'] !== 'aprobada') {
            throw new Exception('Solo requisiciones aprobadas pueden generar cargos');
        }

        if (empty($req['detalles'])) {
            throw new Exception('La requisición no tiene productos');
        }

        // Group lines by supplier (selected > suggested > null)
        $porProveedor = [];
        foreach ($req['detalles'] as $linea) {
            $provId = $linea['proveedor_seleccionado_id']
                   ?? $linea['proveedor_sugerido_id']
                   ?? 0; // 0 = sin proveedor
            $porProveedor[$provId][] = $linea;
        }

        // Create one draft Cargo per supplier
        $cargosCreados = [];
        foreach ($porProveedor as $proveedorId => $lineas) {
            $cargoData = [
                'fecha' => date('Y-m-d'),
                'concepto' => 'compra',
                'proveedor_id' => $proveedorId > 0 ? $proveedorId : null,
                'almacen_id' => $almacenId,
                'notas' => "Generado automáticamente desde {$req['codigo']}",
                'lineas' => array_map(fn($l) => [
                    'producto_id' => $l['producto_id'],
                    'cantidad' => $l['cantidad_solicitada'],
                    'costo_unitario' => floatval($l['precio_sugerido'] ?? 0)
                ], $lineas),
                'confirmar' => false // Create as draft
            ];

            $result = $this->cargoService->createCargo($cargoData, $userId);
            $cargosCreados[] = $result;
        }

        // Mark requisition as processed
        $this->repository->updateStatus($requisicionId, 'procesada', $userId);

        return $cargosCreados;
    }

    // ==========================================
    // DELETE
    // ==========================================

    public function deleteRequisicion(int $id): void {
        $current = $this->getRequisicion($id);
        if ($current['estado'] !== 'pendiente') {
            throw new Exception('Solo se pueden eliminar requisiciones pendientes');
        }
        $this->repository->delete($id);
    }

    // ==========================================
    // VALIDATION
    // ==========================================

    private function validateData(array $data): void {
        if (empty($data['lineas']) || !is_array($data['lineas'])) {
            throw new Exception('Debe agregar al menos un producto');
        }

        $validPrioridades = ['baja', 'media', 'alta', 'urgente'];
        if (!empty($data['prioridad']) && !in_array($data['prioridad'], $validPrioridades)) {
            throw new Exception('Prioridad inválida');
        }
    }
}
