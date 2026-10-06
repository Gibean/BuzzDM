<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * OrderRepository
 *
 * Antes de migrar a BaseRepository este repositorio no filtraba por empresa ni
 * en lectura ni en escritura: findAll(), findById() y getDetails()aban la tabla
 * entera, y createHeader() insertaba sin empresa_id, de modo que un pedido
 * creado por la empresa 14 se guardaba en la 1 (el DEFAULT 1 de la columna).
 *
 * `detalle_pedido` no tiene columna empresa_id: hereda el tenant de su pedido
 * padre. Por eso las operaciones sobre detalles comprueban antes que el pedido
 * pertenezca al tenant activo. Sin esa comprobación, deleteDetails($id) con un
 * id de otra empresa borraba los detalles de un pedido ajeno.
 */
class OrderRepository extends BaseRepository
{
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

        // El filtro de tenant se añade al final para que el orden de $params
        // coincida con el de las condiciones.
        $this->scopeTenant($where, $params, 'p', 'OrderRepository::findAll');

        $whereClause = implode(' AND ', $where);

        // cliente_id es un varchar, no una clave foránea, así que el JOIN puede
        // emparejar un pedido con un cliente de otra empresa. La condición se
        // pone en el ON y no en el WHERE para no convertir el LEFT JOIN en
        // INNER y perder pedidos sin cliente.
        $joinClientes = "LEFT JOIN clientes c ON p.cliente_id = c.id AND c.empresa_id = p.empresa_id";

        // Count Total
        $sqlCount = "SELECT COUNT(p.id) FROM pedidos p $joinClientes WHERE $whereClause";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetchColumn();

        // Fetch Data
        $sql = "SELECT p.id, p.empresa_id, p.referencia, c.nombre AS customer, c.email,
                       p.fecha_creacion AS date, p.total, p.estado_pago AS paymentStatus,
                       p.estado_envio AS shippingStatus, p.direccion_envio AS shippingAddress,
                       p.notas_envio AS shippingNotes, p.deleted_at
                FROM pedidos p
                $joinClientes
                WHERE $whereClause
                ORDER BY p.fecha_creacion DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['orders' => $orders, 'total' => intval($total)];
    }

    public function findById($id) {
        $where = ['p.id = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, 'p', "OrderRepository::findById($id)");

        $sql = "SELECT p.*, c.nombre AS customer_name, c.email AS customer_email
                FROM pedidos p
                LEFT JOIN clientes c ON p.cliente_id = c.id AND c.empresa_id = p.empresa_id
                WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $order = $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);

        if ($order) {
            $order['products'] = $this->getDetails($id);
        }

        return $order;
    }

    public function getDetails($orderId) {
        // El detalle se alcanza a través de su pedido padre, que es quien
        // aporta el tenant. Filtrar solo por pr.empresa_id dejaría pasar los
        // detalles de un pedido ajeno cuyo producto coincida de tenant.
        $where = ['pp.pedido_id = ?', 'p.empresa_id = ?'];
        $params = [$orderId, $this->tenantId("OrderRepository::getDetails($orderId)")];

        $sql = "SELECT pp.producto_id, pr.nombre AS product_name, pp.cantidad, pp.precio_unitario
                FROM detalle_pedido pp
                JOIN pedidos p ON pp.pedido_id = p.id
                JOIN productos pr ON pp.producto_id = pr.id AND pr.empresa_id = p.empresa_id
                WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper for stock calculation during updates
    public function getDetailsForStockReversion($orderId) {
        $where = ['pedido_id = ?', 'p.empresa_id = ?'];
        $params = [$orderId, $this->tenantId("OrderRepository::getDetailsForStockReversion($orderId)")];

        $stmt = $this->pdo->prepare(
            "SELECT d.producto_id, d.cantidad
             FROM detalle_pedido d
             JOIN pedidos p ON d.pedido_id = p.id
             WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adjustStock($productId, $delta) {
        $where = ['id = ?'];
        $params = [$delta, $productId];
        $this->scopeTenant($where, $params, '', "OrderRepository::adjustStock($productId)");

        $sql = "UPDATE productos SET stock = stock + ? WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getProductStock($productId) {
        $where = ['id = ?'];
        $params = [$productId];
        $this->scopeTenant($where, $params, '', "OrderRepository::getProductStock($productId)");

        $stmt = $this->pdo->prepare("SELECT stock, nombre FROM productos WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createHeader($data) {
        // empresa_id lo inyecta insertTenant desde el TenantContext. Antes se
        // omitía y la fila caía en el DEFAULT 1, que es la empresa 1.
        return $this->insertTenant('pedidos', [
            'cliente_id' => $data['cliente_id'],
            'referencia' => $data['referencia'],
            'fecha_creacion' => $data['fecha_creacion'],
            'total' => $data['total'],
            'estado_pago' => $data['estado_pago'],
            'estado_envio' => $data['estado_envio'],
            'direccion_envio' => $data['direccion_envio'],
            'notas_envio' => $data['notas_envio'],
        ], 'OrderRepository::createHeader');
    }

    public function updateHeader($id, $data) {
        return $this->applyTenantUpdate('pedidos', [
            'cliente_id' => $data['cliente_id'],
            'referencia' => $data['referencia'],
            'fecha_creacion' => $data['fecha_creacion'],
            'total' => $data['total'],
            'estado_pago' => $data['estado_pago'],
            'estado_envio' => $data['estado_envio'],
            'direccion_envio' => $data['direccion_envio'],
            'notas_envio' => $data['notas_envio'],
        ], (int)$id, "OrderRepository::updateHeader($id)");
    }

    public function createDetail($orderId, $product) {
        // detalle_pedido no tiene columna empresa_id: hereda el tenant del
        // pedido padre. Por eso no se puede usar insertTenant() (intentaría
        // inyectar empresa_id en una tabla que no la tiene) y en su lugar se
        // comprueba que el pedido pertenezca al tenant activo antes de
        // insertar. Si el pedido es de otra empresa, no se inserta nada.
        if (!$this->rowExists('pedidos', (int)$orderId, "OrderRepository::createDetail($orderId)")) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario)
             VALUES (?, ?, ?, ?)"
        );

        return $stmt->execute([
            $orderId,
            $product['producto_id'],
            $product['cantidad'],
            $product['precio_unitario'],
        ]);
    }

    public function deleteDetails($orderId) {
        // El DELETE va sobre detalle_pedido, que no tiene empresa_id. Sin
        // comprobar el padre, un id de pedido ajeno permitia borrar sus
        // lineas: era la forma mas directa de destructar datos de otro tenant.
        if (!$this->rowExists('pedidos', (int)$orderId, "OrderRepository::deleteDetails($orderId)")) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
        return $stmt->execute([$orderId]);
    }

    public function softDelete($id) {
        return $this->applyTenantUpdate('pedidos', ['deleted_at' => date('Y-m-d H:i:s')], (int)$id, "OrderRepository::softDelete($id)");
    }

    public function restore($id) {
        return $this->applyTenantUpdate('pedidos', ['deleted_at' => null], (int)$id, "OrderRepository::restore($id)");
    }
}
