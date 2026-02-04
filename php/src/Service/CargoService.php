<?php
require_once __DIR__ . '/../Repository/CargoRepository.php';

class CargoService {
    private $repository;

    public function __construct() {
        $this->repository = new CargoRepository();
    }

    public function listCargos($filters) {
        return $this->repository->findAll($filters);
    }

    public function getCargo($id) {
        $cargo = $this->repository->findById($id);
        if (!$cargo) {
            throw new Exception('Cargo no encontrado');
        }
        return $cargo;
    }

    public function createCargo($data, $userId) {
        $this->validateData($data);

        $this->repository->beginTransaction();
        try {
            $codigo = $this->repository->generateNextCode();
            $estado = ($data['confirmar'] ?? false) ? 'confirmado' : 'borrador';

            $headerData = [
                'codigo' => $codigo,
                'fecha' => $data['fecha'],
                'concepto' => $data['concepto'],
                'concepto_otro' => $data['concepto_otro'] ?? null,
                'proveedor_id' => !empty($data['proveedor_id']) ? $data['proveedor_id'] : null,
                'almacen_id' => !empty($data['almacen_id']) ? $data['almacen_id'] : null,
                'referencia_externa' => $data['referencia_externa'] ?? null,
                'notas' => $data['notas'] ?? null,
                'usuario_id' => $userId,
                'estado' => $estado
            ];

            $cargoId = $this->repository->createHeader($headerData);

            // Process lines
            $lines = $this->prepareLines($data['lineas'] ?? []);
            $totals = $this->repository->insertDetails($cargoId, $lines);

            // Update header with totals
            $this->repository->updateTotals($cargoId, $totals);

            if ($estado === 'confirmado') {
                $this->repository->updateConfirmation($cargoId, $userId);
                // Register purchase prices when cargo is confirmed with proveedor
                if (!empty($data['proveedor_id'])) {
                    $this->repository->registerPurchasePrices($cargoId, $data['proveedor_id'], $data['fecha']);
                }
            }

            $this->repository->commit();
            return ['id' => $cargoId, 'codigo' => $codigo, 'estado' => $estado];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function updateCargo($id, $data, $userId) {
        $current = $this->getCargo($id);
        if ($current['estado'] !== 'borrador') {
            throw new Exception('Solo se pueden editar cargos en estado borrador');
        }

        $this->validateData($data);

        $this->repository->beginTransaction();
        try {
            $estado = ($data['confirmar'] ?? false) ? 'confirmado' : 'borrador';

            $headerData = [
                'fecha' => $data['fecha'],
                'concepto' => $data['concepto'],
                'concepto_otro' => $data['concepto_otro'] ?? null,
                'proveedor_id' => !empty($data['proveedor_id']) ? $data['proveedor_id'] : null,
                'almacen_id' => !empty($data['almacen_id']) ? $data['almacen_id'] : null,
                'referencia_externa' => $data['referencia_externa'] ?? null,
                'notas' => $data['notas'] ?? null,
                'estado' => $estado
            ];

            $this->repository->updateHeader($id, $headerData);

            // Replace lines
            $this->repository->deleteDetails($id);
            $lines = $this->prepareLines($data['lineas'] ?? []);
            $totals = $this->repository->insertDetails($id, $lines);

            $this->repository->updateTotals($id, $totals);

            if ($estado === 'confirmado') {
                $this->repository->updateConfirmation($id, $userId);
                // Register purchase prices when cargo is confirmed with proveedor
                $proveedor_id = !empty($data['proveedor_id']) ? $data['proveedor_id'] : null;
                if ($proveedor_id) {
                    $this->repository->registerPurchasePrices($id, $proveedor_id, $data['fecha']);
                }
            }

            $this->repository->commit();
            return ['id' => $id, 'estado' => $estado];

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function confirmCargo($id, $userId) {
        $current = $this->getCargo($id);
        if ($current['estado'] !== 'borrador') {
             // Idempotency: if already confirmed, just return success? Or error?
             // Original logic implies explicit action. Let's error.
             throw new Exception('El cargo no está en borrador o ya fue procesado');
        }

        if (!$this->repository->hasDetails($id)) {
            throw new Exception('El cargo no tiene productos');
        }

        // Updating status triggers the DB stock update via Trigger
        // We also set confirmed_by
        $this->repository->beginTransaction();
        try {
            $this->repository->updateStatus($id, 'confirmado');
            $this->repository->updateConfirmation($id, $userId);

            // Register purchase prices when cargo is confirmed with proveedor
            if (!empty($current['proveedor_id'])) {
                $this->repository->registerPurchasePrices($id, $current['proveedor_id'], $current['fecha']);
            }

            $this->repository->commit();
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function cancelCargo($id, $reason, $userId) {
        if (empty($reason)) throw new Exception('El motivo de anulación es requerido');

        $current = $this->getCargo($id);
        if ($current['estado'] !== 'confirmado') {
            throw new Exception('Solo se pueden anular cargos confirmados');
        }

        $this->repository->cancel($id, $userId, $reason);
    }

    public function deleteCargo($id) {
        $current = $this->getCargo($id);
        if ($current['estado'] !== 'borrador') {
             // Usually only drafts can be hard deleted. Anulled ones are kept for history.
             // Original logic allowed delete if not confirmed? No, original: obtenerCargoParaEdicion -> borrador check.
             throw new Exception('Solo se pueden eliminar cargos en estado borrador');
        }
        $this->repository->delete($id);
    }

    private function validateData($data) {
        if (empty($data['fecha'])) throw new Exception('La fecha es requerida');
        if (empty($data['concepto'])) throw new Exception('El concepto es requerido');
        if ($data['concepto'] === 'otro' && empty($data['concepto_otro'])) {
            throw new Exception('Debe especificar el concepto');
        }
        if (empty($data['almacen_id'])) throw new Exception('Debes seleccionar un almacén destino');
    }

    private function prepareLines($rawLines) {
        $lines = [];
        foreach ($rawLines as $line) {
            if (empty($line['producto_id']) || empty($line['cantidad'])) continue;

            $cantidad = intval($line['cantidad']);
            if ($cantidad <= 0) continue;

            $lines[] = [
                'producto_id' => $line['producto_id'],
                'cantidad' => $cantidad,
                'costo_unitario' => floatval($line['costo_unitario'] ?? 0),
                'notas' => $line['notas'] ?? null
            ];
        }
        return $lines;
    }
}
