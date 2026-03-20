<?php
/**
 * RequisicionRepository
 * Data access layer for purchase requisitions
 */
class RequisicionRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    // ==========================================
    // TRANSACTION HELPERS
    // ==========================================
    public function beginTransaction() { $this->pdo->beginTransaction(); }
    public function commit() { $this->pdo->commit(); }
    public function rollBack() { $this->pdo->rollBack(); }

    // ==========================================
    // CODE GENERATION
    // ==========================================
    public function generateNextCode(): string {
        $stmt = $this->pdo->query("SELECT fn_generar_codigo_requisicion()");
        return $stmt->fetchColumn();
    }

    // ==========================================
    // SUPPLIER INTELLIGENCE
    // ==========================================

    /**
     * Get best supplier for a product based on price history
     * Uses precios_proveedor table (most recent lowest price)
     */
    public function getBestSupplierFromPrices(int $productoId): ?array {
        $sql = "SELECT pp.proveedor_id, p.nombre as proveedor_nombre,
                       pp.precio_compra, pp.fecha_registro
                FROM precios_proveedor pp
                JOIN proveedores p ON p.id = pp.proveedor_id
                WHERE pp.producto_id = ? AND pp.es_ultimo = 1
                ORDER BY pp.precio_compra ASC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Fallback: Get best supplier from cargo history
     * Searches confirmed cargos for lowest unit cost
     */
    public function getBestSupplierFromCargos(int $productoId): ?array {
        $sql = "SELECT ce.proveedor_id, prov.nombre as proveedor_nombre,
                       dce.costo_unitario as precio_compra, ce.fecha as fecha_registro
                FROM detalle_cargo_existencias dce
                JOIN cargos_existencias ce ON ce.id = dce.cargo_id
                JOIN proveedores prov ON prov.id = ce.proveedor_id
                WHERE dce.producto_id = ?
                  AND ce.estado = 'confirmado'
                  AND ce.proveedor_id IS NOT NULL
                ORDER BY dce.costo_unitario ASC, ce.fecha DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Final fallback: Get default supplier assigned to product
     */
    public function getDefaultSupplier(int $productoId): ?array {
        $sql = "SELECT p.proveedor_id, prov.nombre as proveedor_nombre,
                       p.costo_ultimo as precio_compra, NULL as fecha_registro
                FROM productos p
                LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
                WHERE p.id = ? AND p.proveedor_id IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all active products with low or zero stock
     * Used for auto-populating new requisitions
     */
    public function getProductosStockBajo(): array {
        $sql = "SELECT * FROM vw_productos_stock_bajo_activos";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==========================================
    // CRUD OPERATIONS
    // ==========================================

    public function findAll(array $filters = []): array {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['estado'])) {
            $where[] = "r.estado = ?";
            $params[] = $filters['estado'];
        }
        if (!empty($filters['prioridad'])) {
            $where[] = "r.prioridad = ?";
            $params[] = $filters['prioridad'];
        }
        if (!empty($filters['fecha_desde'])) {
            $where[] = "r.fecha_solicitud >= ?";
            $params[] = $filters['fecha_desde'];
        }
        if (!empty($filters['fecha_hasta'])) {
            $where[] = "r.fecha_solicitud <= ?";
            $params[] = $filters['fecha_hasta'];
        }
        if (!empty($filters['search'])) {
            $where[] = "(r.codigo LIKE ? OR r.notas LIKE ?)";
            $params[] = "%{$filters['search']}%";
            $params[] = "%{$filters['search']}%";
        }

        $whereClause = implode(" AND ", $where);

        // Pagination
        $page = max(1, intval($filters['page'] ?? 1));
        $limit = max(1, min(100, intval($filters['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        // Count total
        $countSql = "SELECT COUNT(*) FROM requisiciones r WHERE {$whereClause}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();

        // Fetch data with relations
        $sql = "SELECT r.*,
                       u.nombre as usuario_nombre,
                       ua.nombre as aprobador_nombre,
                       (SELECT COUNT(*) FROM detalle_requisicion WHERE requisicion_id = r.id) as total_lineas,
                       (SELECT SUM(cantidad_solicitada) FROM detalle_requisicion WHERE requisicion_id = r.id) as total_unidades
                FROM requisiciones r
                LEFT JOIN usuarios u ON u.id = r.usuario_id
                LEFT JOIN usuarios ua ON ua.id = r.aprobado_por
                WHERE {$whereClause}
                ORDER BY
                    CASE r.prioridad WHEN 'urgente' THEN 1 WHEN 'alta' THEN 2 WHEN 'media' THEN 3 ELSE 4 END,
                    r.fecha_solicitud DESC
                LIMIT {$limit} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
    }

    public function findById(int $id): ?array {
        $sql = "SELECT r.*,
                       u.nombre as usuario_nombre,
                       ua.nombre as aprobador_nombre
                FROM requisiciones r
                LEFT JOIN usuarios u ON u.id = r.usuario_id
                LEFT JOIN usuarios ua ON ua.id = r.aprobado_por
                WHERE r.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $header = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$header) return null;

        // Get details
        $detailSql = "SELECT dr.*,
                             p.sku, p.nombre as producto_nombre, p.stock as stock_actual,
                             ps.nombre as proveedor_sugerido_nombre,
                             psel.nombre as proveedor_seleccionado_nombre
                      FROM detalle_requisicion dr
                      JOIN productos p ON p.id = dr.producto_id
                      LEFT JOIN proveedores ps ON ps.id = dr.proveedor_sugerido_id
                      LEFT JOIN proveedores psel ON psel.id = dr.proveedor_seleccionado_id
                      WHERE dr.requisicion_id = ?";
        $detailStmt = $this->pdo->prepare($detailSql);
        $detailStmt->execute([$id]);
        $header['detalles'] = $detailStmt->fetchAll(PDO::FETCH_ASSOC);

        return $header;
    }

    public function createHeader(array $data): int {
        $sql = "INSERT INTO requisiciones (codigo, fecha_solicitud, estado, prioridad, notas, usuario_id)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['codigo'],
            $data['fecha_solicitud'],
            $data['estado'] ?? 'pendiente',
            $data['prioridad'] ?? 'media',
            $data['notas'] ?? null,
            $data['usuario_id']
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function insertDetail(int $requisicionId, array $line): int {
        $sql = "INSERT INTO detalle_requisicion
                (requisicion_id, producto_id, cantidad_solicitada, proveedor_sugerido_id, precio_sugerido, proveedor_seleccionado_id, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $requisicionId,
            $line['producto_id'],
            $line['cantidad_solicitada'],
            $line['proveedor_sugerido_id'] ?? null,
            $line['precio_sugerido'] ?? null,
            $line['proveedor_seleccionado_id'] ?? null,
            $line['notas'] ?? null
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateHeader(int $id, array $data): void {
        $sql = "UPDATE requisiciones SET
                    fecha_solicitud = ?,
                    prioridad = ?,
                    notas = ?,
                    actualizado_at = NOW()
                WHERE id = ? AND estado = 'pendiente'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['fecha_solicitud'],
            $data['prioridad'] ?? 'media',
            $data['notas'] ?? null,
            $id
        ]);
    }

    public function deleteDetails(int $requisicionId): void {
        $stmt = $this->pdo->prepare("DELETE FROM detalle_requisicion WHERE requisicion_id = ?");
        $stmt->execute([$requisicionId]);
    }

    public function updateStatus(int $id, string $status, ?int $aprobadorId = null): void {
        if ($aprobadorId && in_array($status, ['aprobada', 'rechazada'])) {
            $sql = "UPDATE requisiciones SET estado = ?, aprobado_por = ?, fecha_aprobacion = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$status, $aprobadorId, $id]);
        } else {
            $sql = "UPDATE requisiciones SET estado = ? WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$status, $id]);
        }
    }

    public function delete(int $id): void {
        $stmt = $this->pdo->prepare("DELETE FROM requisiciones WHERE id = ? AND estado = 'pendiente'");
        $stmt->execute([$id]);
    }

    public function hasDetails(int $id): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM detalle_requisicion WHERE requisicion_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
}
