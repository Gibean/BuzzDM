<?php
require_once __DIR__ . '/../../db.php';

class CargoRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
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

        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $where[] = "(c.codigo LIKE :search OR p.nombre LIKE :search OR c.referencia_externa LIKE :search)";
            $params[':search'] = $search;
        }

        if (!empty($filters['estado'])) {
            $where[] = "c.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['concepto'])) {
            $where[] = "c.concepto = :concepto";
            $params[':concepto'] = $filters['concepto'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = "c.fecha >= :fecha_desde";
            $params[':fecha_desde'] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = "c.fecha <= :fecha_hasta";
            $params[':fecha_hasta'] = $filters['fecha_hasta'];
        }

        $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count
        $sqlCount = "SELECT COUNT(DISTINCT c.id) as total FROM cargos_existencias c LEFT JOIN proveedores p ON c.proveedor_id = p.id $whereClause";
        $stmtCount = $this->pdo->prepare($sqlCount);
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

        // Data
        $sql = "SELECT c.id, c.codigo, c.fecha, c.concepto,
                CASE c.concepto
                    WHEN 'compra' THEN 'Compra'
                    WHEN 'donacion' THEN 'Donación'
                    WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                    WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                    WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                    WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
                END AS concepto_texto,
                p.nombre AS proveedor_nombre,
                c.referencia_externa, c.total_productos, c.total_unidades, c.valor_total, c.estado,
                CASE c.estado
                    WHEN 'borrador' THEN 'Borrador'
                    WHEN 'confirmado' THEN 'Confirmado'
                    WHEN 'anulado' THEN 'Anulado'
                END AS estado_texto,
                u.nombre AS registrado_por_nombre, c.creado_at
                FROM cargos_existencias c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                LEFT JOIN usuarios u ON c.registrado_por = u.id
                $whereClause
                ORDER BY c.creado_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['cargos' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => intval($total)];
    }

    public function findById($id) {
        $sql = "SELECT c.*,
                CASE c.concepto
                    WHEN 'compra' THEN 'Compra'
                    WHEN 'donacion' THEN 'Donación'
                    WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                    WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                    WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                    WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
                END AS concepto_texto,
                CASE c.estado
                    WHEN 'borrador' THEN 'Borrador'
                    WHEN 'confirmado' THEN 'Confirmado'
                    WHEN 'anulado' THEN 'Anulado'
                END AS estado_texto,
                c.almacen_id, a.nombre AS almacen_nombre,
                p.nombre AS proveedor_nombre,
                u.nombre AS registrado_por_nombre,
                uc.nombre AS confirmado_por_nombre,
                ua.nombre AS anulado_por_nombre
                FROM cargos_existencias c
                LEFT JOIN proveedores p ON c.proveedor_id = p.id
                LEFT JOIN almacenes a ON c.almacen_id = a.id
                LEFT JOIN usuarios u ON c.registrado_por = u.id
                LEFT JOIN usuarios uc ON c.confirmado_por = uc.id
                LEFT JOIN usuarios ua ON c.anulado_por = ua.id
                WHERE c.id = :id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cargo) {
            $sqlDetalles = "SELECT d.id, d.producto_id, pr.sku AS producto_sku, pr.nombre AS producto_nombre,
                            d.cantidad, d.costo_unitario, d.subtotal, d.notas
                            FROM detalle_cargo_existencias d
                            INNER JOIN productos pr ON d.producto_id = pr.id
                            WHERE d.cargo_id = :cargo_id ORDER BY d.id";
            $stmtDetalles = $this->pdo->prepare($sqlDetalles);
            $stmtDetalles->execute([':cargo_id' => $id]);
            $cargo['detalles'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);
        }

        return $cargo;
    }

    public function generateNextCode() {
        $year = date('Y');
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 10) AS UNSIGNED)), 0) + 1 as next_num
                               FROM cargos_existencias WHERE codigo LIKE ?");
        $stmt->execute(["CGO-$year-%"]);
        $nextNum = $stmt->fetchColumn();
        return sprintf("CGO-%s-%04d", $year, $nextNum);
    }

    public function createHeader($data) {
        $sql = "INSERT INTO cargos_existencias
                (codigo, fecha, concepto, concepto_otro, proveedor_id, almacen_id,
                 referencia_externa, notas, registrado_por, estado)
                VALUES
                (:codigo, :fecha, :concepto, :concepto_otro, :proveedor_id, :almacen_id,
                 :referencia, :notas, :usuario_id, :estado)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':codigo' => $data['codigo'],
            ':fecha' => $data['fecha'],
            ':concepto' => $data['concepto'],
            ':concepto_otro' => $data['concepto_otro'] ?? null,
            ':proveedor_id' => $data['proveedor_id'],
            ':almacen_id' => $data['almacen_id'],
            ':referencia' => $data['referencia_externa'] ?? null,
            ':notas' => $data['notas'] ?? null,
            ':usuario_id' => $data['usuario_id'],
            ':estado' => $data['estado']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateHeader($id, $data) {
        $sql = "UPDATE cargos_existencias SET
                fecha = :fecha, concepto = :concepto, concepto_otro = :concepto_otro,
                proveedor_id = :proveedor_id, almacen_id = :almacen_id,
                referencia_externa = :referencia, notas = :notas, estado = :estado
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':fecha' => $data['fecha'],
            ':concepto' => $data['concepto'],
            ':concepto_otro' => $data['concepto_otro'] ?? null,
            ':proveedor_id' => $data['proveedor_id'],
            ':almacen_id' => $data['almacen_id'],
            ':referencia' => $data['referencia_externa'] ?? null,
            ':notas' => $data['notas'] ?? null,
            ':estado' => $data['estado'],
            ':id' => $id
        ]);
    }

    public function deleteDetails($cargoId) {
        $stmt = $this->pdo->prepare("DELETE FROM detalle_cargo_existencias WHERE cargo_id = ?");
        return $stmt->execute([$cargoId]);
    }

    public function insertDetails($cargoId, $lines) {
        $sql = "INSERT INTO detalle_cargo_existencias (cargo_id, producto_id, cantidad, costo_unitario, notas)
                VALUES (:cargo_id, :producto_id, :cantidad, :costo, :notas)";
        $stmt = $this->pdo->prepare($sql);

        $res = ['productos' => 0, 'unidades' => 0, 'valor' => 0];

        foreach ($lines as $line) {
            $stmt->execute([
                ':cargo_id' => $cargoId,
                ':producto_id' => $line['producto_id'],
                ':cantidad' => $line['cantidad'],
                ':costo' => $line['costo_unitario'],
                ':notas' => $line['notas'] ?? null
            ]);
            $res['productos']++;
            $res['unidades'] += $line['cantidad'];
            $res['valor'] += $line['cantidad'] * $line['costo_unitario'];
        }
        return $res;
    }

    public function updateTotals($id, $totals) {
        $stmt = $this->pdo->prepare("UPDATE cargos_existencias SET total_productos = ?, total_unidades = ?, valor_total = ? WHERE id = ?");
        return $stmt->execute([$totals['productos'], $totals['unidades'], $totals['valor'], $id]);
    }

    public function updateConfirmation($id, $userId) {
        $stmt = $this->pdo->prepare("UPDATE cargos_existencias SET confirmado_por = ?, fecha_confirmacion = NOW() WHERE id = ?");
        return $stmt->execute([$userId, $id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE cargos_existencias SET estado = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function cancel($id, $userId, $reason) {
        $sql = "UPDATE cargos_existencias SET estado = 'anulado', anulado_por = ?, fecha_anulacion = NOW(), motivo_anulacion = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $reason, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM cargos_existencias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasDetails($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM detalle_cargo_existencias WHERE cargo_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Register purchase prices in precios_proveedor table
     * Called when a cargo is confirmed
     */
    public function registerPurchasePrices($cargoId, $proveedorId, $fecha) {
        if (!$proveedorId) return; // No proveedor, no price history

        // Get all detail lines for this cargo
        $sqlDetails = "SELECT producto_id, costo_unitario FROM detalle_cargo_existencias WHERE cargo_id = ?";
        $stmtDetails = $this->pdo->prepare($sqlDetails);
        $stmtDetails->execute([$cargoId]);
        $detalles = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

        foreach ($detalles as $detalle) {
            if (empty($detalle['costo_unitario']) || $detalle['costo_unitario'] <= 0) continue;

            // Mark previous prices as not latest
            $sqlUpdateOld = "UPDATE precios_proveedor SET es_ultimo = 0
                             WHERE producto_id = ? AND proveedor_id = ? AND es_ultimo = 1";
            $stmtOld = $this->pdo->prepare($sqlUpdateOld);
            $stmtOld->execute([$detalle['producto_id'], $proveedorId]);

            // Insert new price (use INSERT IGNORE to handle duplicate key on same date)
            $sqlInsert = "INSERT INTO precios_proveedor
                          (producto_id, proveedor_id, precio_compra, fecha_registro, cargo_id, es_ultimo)
                          VALUES (?, ?, ?, ?, ?, 1)
                          ON DUPLICATE KEY UPDATE
                            precio_compra = VALUES(precio_compra),
                            cargo_id = VALUES(cargo_id),
                            es_ultimo = 1";
            $stmtInsert = $this->pdo->prepare($sqlInsert);
            $stmtInsert->execute([
                $detalle['producto_id'],
                $proveedorId,
                $detalle['costo_unitario'],
                $fecha,
                $cargoId
            ]);

            // Update product's costo_ultimo
            $sqlUpdateProduct = "UPDATE productos SET costo_ultimo = ? WHERE id = ?";
            $stmtProd = $this->pdo->prepare($sqlUpdateProduct);
            $stmtProd->execute([$detalle['costo_unitario'], $detalle['producto_id']]);
        }
    }

    /**
     * Get purchase price history for a product
     */
    public function getPriceHistory($productoId) {
        $sql = "SELECT pp.*, p.nombre AS proveedor_nombre, c.codigo AS cargo_codigo
                FROM precios_proveedor pp
                LEFT JOIN proveedores p ON pp.proveedor_id = p.id
                LEFT JOIN cargos_existencias c ON pp.cargo_id = c.id
                WHERE pp.producto_id = ?
                ORDER BY pp.fecha_registro DESC, pp.creado_at DESC
                LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get products supplied by a provider with their last prices
     */
    public function getProductsByProvider($proveedorId) {
        $sql = "SELECT pp.producto_id, pr.sku, pr.nombre AS producto_nombre,
                       pp.precio_compra, pp.fecha_registro, c.codigo AS cargo_codigo
                FROM precios_proveedor pp
                INNER JOIN productos pr ON pp.producto_id = pr.id
                LEFT JOIN cargos_existencias c ON pp.cargo_id = c.id
                WHERE pp.proveedor_id = ? AND pp.es_ultimo = 1
                ORDER BY pr.nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$proveedorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
