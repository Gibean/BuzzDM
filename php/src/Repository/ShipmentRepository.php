<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * ShipmentRepository
 *
 * La pantalla de envíos lee de `pedidos`, así que comparte aislamiento con
 * OrderRepository. El fallo que importa aquí es updateStatus(): hacía
 * `UPDATE pedidos SET estado_envio = ? WHERE id = ?` sin filtro de empresa, de
 * modo que un administrador de la empresa 14 podía cambiar el estado de envío de
 * un pedido de la empresa 1 con solo adivinar el id. Devolvía true porque el
 * UPDATE sí afectaba una fila, así que la interfaz confirmaba un cambio que no
 * le pertenecía.
 */
class ShipmentRepository extends BaseRepository
{
    public function findAll($filters = []) {
        $where = ["p.deleted_at IS NULL"];
        $params = [];

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(p.id LIKE ? OR p.referencia LIKE ? OR c.nombre LIKE ? OR p.direccion_envio LIKE ? OR p.tracking_number LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['status'])) {
            $where[] = "p.estado_envio = ?";
            $params[] = $filters['status'];
        }

        $this->scopeTenant($where, $params, 'p', 'ShipmentRepository::findAll');

        $sql_from = "FROM pedidos p
                    LEFT JOIN clientes c ON p.cliente_id = c.id AND c.empresa_id = p.empresa_id
                    WHERE " . implode(' AND ', $where);

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
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'shipments' => $orders,
            'total' => $total
        ];
    }

    /**
     * Update shipment status
     *
     * Devuelve false si el pedido no existe o es de otra empresa, en lugar de
     * un true que confirmaría al usuario un cambio sobre datos ajenos.
     */
    public function updateStatus($id, $status, $trackingNumber = null) {
        $data = ['estado_envio' => $status];

        if ($trackingNumber !== null) {
            $data['tracking_number'] = $trackingNumber;
        }

        return $this->applyTenantUpdate('pedidos', $data, (int)$id, "ShipmentRepository::updateStatus($id)");
    }
}
