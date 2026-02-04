<?php
/**
 * TransferenciaService - Business logic for warehouse transfers
 */

require_once __DIR__ . '/../Repository/TransferenciaRepository.php';

class TransferenciaService {

    private TransferenciaRepository $repository;

    public function __construct() {
        $this->repository = new TransferenciaRepository();
    }

    /**
     * Get paginated list of transfers
     */
    public function getTransferencias($page = 1, $limit = 10, $filters = []) {
        return $this->repository->getAll($page, $limit, $filters);
    }

    /**
     * Get single transfer with details
     */
    public function getTransferenciaById($id) {
        return $this->repository->getById($id);
    }

    /**
     * Create new transfer with details
     */
    public function createTransferencia($data, $userId) {
        // Validate warehouses are different
        if ($data['almacen_origen_id'] == $data['almacen_destino_id']) {
            throw new Exception('El almacén origen y destino deben ser diferentes');
        }

        $this->repository->beginTransaction();

        try {
            $data['solicitado_por'] = $userId;
            $id = $this->repository->create($data);

            // Add detail lines
            if (!empty($data['lineas'])) {
                foreach ($data['lineas'] as $linea) {
                    $this->repository->addDetalle(
                        $id,
                        $linea['producto_id'],
                        $linea['cantidad'],
                        $linea['notas'] ?? null
                    );
                }
            }

            $this->repository->commit();
            return $id;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Update transfer (only drafts)
     */
    public function updateTransferencia($id, $data) {
        $transfer = $this->repository->getById($id);

        if (!$transfer) {
            throw new Exception('Transferencia no encontrada');
        }

        if ($transfer['estado'] !== 'borrador') {
            throw new Exception('Solo se pueden editar transferencias en borrador');
        }

        if ($data['almacen_origen_id'] == $data['almacen_destino_id']) {
            throw new Exception('El almacén origen y destino deben ser diferentes');
        }

        $this->repository->beginTransaction();

        try {
            $this->repository->update($id, $data);

            // Replace detail lines
            $this->repository->clearDetalles($id);
            if (!empty($data['lineas'])) {
                foreach ($data['lineas'] as $linea) {
                    $this->repository->addDetalle(
                        $id,
                        $linea['producto_id'],
                        $linea['cantidad'],
                        $linea['notas'] ?? null
                    );
                }
            }

            $this->repository->commit();
            return true;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Send transfer (borrador → en_transito)
     * Decrements stock at origin warehouse
     */
    public function enviarTransferencia($id, $userId, $cantidades = []) {
        $transfer = $this->repository->getById($id);

        if (!$transfer) {
            throw new Exception('Transferencia no encontrada');
        }

        if ($transfer['estado'] !== 'borrador') {
            throw new Exception('Solo se pueden enviar transferencias en borrador');
        }

        if (empty($transfer['detalles'])) {
            throw new Exception('La transferencia no tiene productos');
        }

        $this->repository->beginTransaction();

        try {
            // Validate and update stock at origin
            foreach ($transfer['detalles'] as $detalle) {
                $cantidadEnviar = $cantidades[$detalle['producto_id']] ?? $detalle['cantidad_solicitada'];
                $stockActual = $this->repository->getStockEnAlmacen(
                    $detalle['producto_id'],
                    $transfer['almacen_origen_id']
                );

                if ($stockActual < $cantidadEnviar) {
                    throw new Exception("Stock insuficiente de {$detalle['producto_nombre']} en almacén origen (disponible: $stockActual, solicitado: $cantidadEnviar)");
                }
            }

            // Decrement stock at origin
            foreach ($transfer['detalles'] as $detalle) {
                $cantidadEnviar = $cantidades[$detalle['producto_id']] ?? $detalle['cantidad_solicitada'];

                $this->decrementarStock(
                    $detalle['producto_id'],
                    $transfer['almacen_origen_id'],
                    $cantidadEnviar
                );
            }

            // Update sent quantities
            if (!empty($cantidades)) {
                $detallesActualizados = [];
                foreach ($cantidades as $productoId => $cantidad) {
                    $detallesActualizados[] = [
                        'producto_id' => $productoId,
                        'cantidad_enviada' => $cantidad
                    ];
                }
                $this->repository->updateCantidadesEnviadas($id, $detallesActualizados);
            }

            $this->repository->marcarEnviado($id, $userId);
            $this->repository->commit();

            return true;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Receive transfer (en_transito → recibido)
     * Increments stock at destination warehouse
     */
    public function recibirTransferencia($id, $userId, $cantidades = []) {
        $transfer = $this->repository->getById($id);

        if (!$transfer) {
            throw new Exception('Transferencia no encontrada');
        }

        if ($transfer['estado'] !== 'en_transito') {
            throw new Exception('Solo se pueden recibir transferencias en tránsito');
        }

        $this->repository->beginTransaction();

        try {
            // Increment stock at destination
            foreach ($transfer['detalles'] as $detalle) {
                $cantidadRecibir = $cantidades[$detalle['producto_id']]
                    ?? $detalle['cantidad_enviada']
                    ?? $detalle['cantidad_solicitada'];

                $this->incrementarStock(
                    $detalle['producto_id'],
                    $transfer['almacen_destino_id'],
                    $cantidadRecibir
                );
            }

            // Update received quantities
            if (!empty($cantidades)) {
                $detallesActualizados = [];
                foreach ($cantidades as $productoId => $cantidad) {
                    $detallesActualizados[] = [
                        'producto_id' => $productoId,
                        'cantidad_recibida' => $cantidad
                    ];
                }
                $this->repository->updateCantidadesRecibidas($id, $detallesActualizados);
            }

            $this->repository->marcarRecibido($id, $userId);
            $this->repository->commit();

            return true;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Cancel transfer
     * If en_transito, return stock to origin
     */
    public function cancelarTransferencia($id) {
        $transfer = $this->repository->getById($id);

        if (!$transfer) {
            throw new Exception('Transferencia no encontrada');
        }

        if ($transfer['estado'] === 'recibido') {
            throw new Exception('No se puede cancelar una transferencia ya recibida');
        }

        if ($transfer['estado'] === 'cancelado') {
            throw new Exception('La transferencia ya está cancelada');
        }

        $this->repository->beginTransaction();

        try {
            // If en_transito, return stock to origin
            if ($transfer['estado'] === 'en_transito') {
                foreach ($transfer['detalles'] as $detalle) {
                    $cantidad = $detalle['cantidad_enviada'] ?? $detalle['cantidad_solicitada'];
                    $this->incrementarStock(
                        $detalle['producto_id'],
                        $transfer['almacen_origen_id'],
                        $cantidad
                    );
                }
            }

            $this->repository->cancelar($id);
            $this->repository->commit();

            return true;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    /**
     * Delete draft transfer
     */
    public function deleteTransferencia($id) {
        $transfer = $this->repository->getById($id);

        if (!$transfer) {
            throw new Exception('Transferencia no encontrada');
        }

        if ($transfer['estado'] !== 'borrador') {
            throw new Exception('Solo se pueden eliminar transferencias en borrador');
        }

        return $this->repository->delete($id);
    }

    // =====================================================
    // HELPER METHODS FOR STOCK MANAGEMENT
    // =====================================================

    private function decrementarStock($productoId, $almacenId, $cantidad) {
        $pdo = $this->repository->getPdo();

        // Update stock_ubicacion
        $sql = "UPDATE stock_ubicacion SET cantidad = cantidad - ?
                WHERE producto_id = ? AND almacen_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cantidad, $productoId, $almacenId]);

        // Update global stock in productos
        $sql = "UPDATE productos SET stock = stock - ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cantidad, $productoId]);
    }

    private function incrementarStock($productoId, $almacenId, $cantidad) {
        $pdo = $this->repository->getPdo();

        // Upsert stock_ubicacion
        $sql = "INSERT INTO stock_ubicacion (producto_id, almacen_id, cantidad)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productoId, $almacenId, $cantidad]);

        // Update global stock in productos
        $sql = "UPDATE productos SET stock = stock + ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cantidad, $productoId]);
    }
}
