<?php
require_once __DIR__ . '/../../db.php';

class OrderRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection(); // Use the global function from db.php
    }

    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollBack() {
        return $this->pdo->rollBack();
    }

    public function findAll($filters = []) {
        $page = max(1, intval($filters['page'] ?? 1));
        $limit = max(1, min(100, intval($filters['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $showDeleted = isset($filters['show_deleted']) && $filters['show_deleted'] === 'true';

        $where = ["1=1"];
        $params = [];

        if (!$showDeleted) {
            $where[] = "p.deleted_at IS NULL";
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(p.id LIKE ? OR p.referencia LIKE ? OR c.nombre LIKE ? OR c.email LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        if (!empty($filters['paymentStatus'])) {
            $where[] = "p.estado_pago = ?";
            $params[] = $filters['paymentStatus'];
        }

        if (!empty($filters['dateFrom'])) {
            $where[] = "p.fecha_creacion >= ?";
            $params[] = $filters['dateFrom'] . ' 00:00:00';
        }

        if (!empty($filters['dateTo'])) {
            $where[] = "p.fecha_creacion <= ?";
            $params[] = $filters['dateTo'] . ' 23:59:59';
        }

        if (!empty($filters['shippingStatus'])) {
            $where[] = "p.estado_envio = ?";
            $params[] = $filters['shippingStatus'];
        }

        $whereClause = implode(' AND ', $where);

        // Count Total
        $sqlCount = "SELECT COUNT(p.id) FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE $whereClause";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetchColumn();

        // Fetch Data
        $sql = "SELECT p.id, p.referencia, c.nombre AS customer, c.email,
                       p.fecha_creacion AS date, p.total, p.estado_pago AS paymentStatus,
                       p.estado_envio AS shippingStatus, p.direccion_envio AS shippingAddress,
                       p.notas_envio AS shippingNotes, p.deleted_at
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE $whereClause
                ORDER BY p.fecha_creacion DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['orders' => $orders, 'total' => intval($total)];
    }

    public function findById($id) {
        $sql = "SELECT p.*, c.nombre AS customer_name, c.email AS customer_email
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['products'] = $this->getDetails($id);
        }

        return $order;
    }

    public function getDetails($orderId) {
        $sql = "SELECT pp.producto_id, pr.nombre AS product_name, pp.cantidad, pp.precio_unitario
                FROM detalle_pedido pp
                JOIN productos pr ON pp.producto_id = pr.id
                WHERE pp.pedido_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper for stock calculation during updates
    public function getDetailsForStockReversion($orderId) {
        $stmt = $this->pdo->prepare("SELECT producto_id, cantidad FROM detalle_pedido WHERE pedido_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adjustStock($productId, $delta) {
        // Delta can be positive (restocking/restoring) or negative (selling)
        // Note: Logic in old code was: "UPDATE productos SET stock = stock - ?" for creation
        // So here:
        // Selling 5 units -> delta = -5 -> stock = stock + (-5)
        $sql = "UPDATE productos SET stock = stock + ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$delta, $productId]);
    }

    public function createHeader($data) {
        $sql = "INSERT INTO pedidos (cliente_id, referencia, fecha_creacion, total, estado_pago, estado_envio, direccion_envio, notas_envio)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['cliente_id'],
            $data['referencia'],
            $data['fecha_creacion'],
            $data['total'],
            $data['estado_pago'],
            $data['estado_envio'],
            $data['direccion_envio'],
            $data['notas_envio']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateHeader($id, $data) {
        $sql = "UPDATE pedidos SET cliente_id = ?, referencia = ?, fecha_creacion = ?, total = ?,
                                   estado_pago = ?, estado_envio = ?, direccion_envio = ?, notas_envio = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['cliente_id'],
            $data['referencia'],
            $data['fecha_creacion'],
            $data['total'],
            $data['estado_pago'],
            $data['estado_envio'],
            $data['direccion_envio'],
            $data['notas_envio'],
            $id
        ]);
    }

    public function createDetail($orderId, $product) {
        $sql = "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $orderId,
            $product['producto_id'],
            $product['cantidad'],
            $product['precio_unitario']
        ]);
    }

    public function deleteDetails($orderId) {
        $stmt = $this->pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
        return $stmt->execute([$orderId]);
    }

    public function softDelete($id) {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore($id) {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
