<?php
require_once __DIR__ . '/../Repository/ShipmentRepository.php';

/**
 * ShipmentService - Business logic for shipments
 */
class ShipmentService {
    private $repository;

    public function __construct() {
        $this->repository = new ShipmentRepository();
    }

    /**
     * Get all shipments with filters
     */
    public function getAllShipments($filters = []) {
        return $this->repository->findAll($filters);
    }

    /**
     * Update shipment status
     */
    public function updateShipmentStatus($id, $status, $trackingNumber = null) {
        if (empty($id)) {
            throw new Exception('ID de envío requerido');
        }

        $validStatuses = ['pendiente', 'enviado', 'en_transito', 'entregado', 'cancelado'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Estado de envío no válido');
        }

        $this->repository->updateStatus($id, $status, $trackingNumber);
        return ['message' => 'Estado de envío actualizado exitosamente'];
    }
}
