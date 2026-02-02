<?php
require_once __DIR__ . '/../../db.php';

/**
 * ShipmentRepository - Handles DB operations for shipments
 */
class ShipmentRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Get all shipments with filters
     */
    public function findAll($filters = []) {
        $sql_from = "FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql_from .= " AND (p.id LIKE ? OR p.referencia LIKE ? OR c.nombre LIKE ? OR p.direccion_envio LIKE ? OR p.tracking_number LIKE ?)";
            $params = array_merge($params, [$search, $search, $search, $search, $search]);
        }

        if (!empty($filters['status'])) {
            $sql_from .= " AND p.estado_envio = ?";
            $params[] = $filters['status'];
        }

        // Get total
        $totalStmt = $this->pdo->prepare("SELECT COUNT(p.id) " . $sql_from);
        $totalStmt->execute($params);
        $total = $totalStmt->fetchColumn();

        // Get data
        $select = "p.id, p.referencia, c.nombre AS customer, p.total AS amount,
                   p.estado_envio AS status, p.direccion_envio AS address,
                   p.tracking_number AS trackingNumber, p.estimated_delivery_date AS estimatedDelivery,
                   p.fecha_creacion AS date";

        $sortBy = $filters['sortBy'] ?? 'date';
        $orderBy = match($sortBy) {
            'status' => 'p.estado_envio ASC',
            'customer' => 'c.nombre ASC',
            'amount' => 'p.total DESC',
            default => 'p.fecha_creacion DESC'
        };

        $sql = "SELECT $select $sql_from ORDER BY $orderBy";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'shipments' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    /**
     * Update shipment status
     */
    public function updateStatus($id, $status, $trackingNumber = null) {
        $sql = "UPDATE pedidos SET estado_envio = ?";
        $params = [$status];

        if ($trackingNumber !== null) {
            $sql .= ", tracking_number = ?";
            $params[] = $trackingNumber;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}
