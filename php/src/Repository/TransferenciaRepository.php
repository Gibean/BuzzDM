<?php
/**
 * TransferenciaRepository - Data access for warehouse transfers
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Tenant/TenantContext.php';

class TransferenciaRepository {
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

    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Get all transfers with pagination and filters
     */
    public function getAll($page = 1, $limit = 10, $filters = []) {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['estado'])) {
            $where[] = "t.estado = ?";
            $params[] = $filters['estado'];
        }

        if (!empty($filters['almacen_origen'])) {
            $where[] = "t.almacen_origen_id = ?";
            $params[] = $filters['almacen_origen'];
        }

        if (!empty($filters['almacen_destino'])) {
            $where[] = "t.almacen_destino_id = ?";
            $params[] = $filters['almacen_destino'];
        }

        if (!empty($filters['fecha_desde'])) {
            $where[] = "t.fecha_solicitud >= ?";
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $where[] = "t.fecha_solicitud <= ?";
            $params[] = $filters['fecha_hasta'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(t.codigo LIKE ? OR t.referencia_externa LIKE ? OR t.motivo LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = implode(' AND ', $where);

        // Count total
        $countSql = "SELECT COUNT(*) FROM transferencias_almacen t WHERE $whereClause";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Get data with joins
        $sql = "SELECT t.*,
                       ao.nombre AS almacen_origen_nombre,
                       ad.nombre AS almacen_destino_nombre,
                       us.nombre AS solicitado_por_nombre,
                       ue.nombre AS enviado_por_nombre,
                       ur.nombre AS recibido_por_nombre,
                       (SELECT COUNT(*) FROM detalle_transferencia dt WHERE dt.transferencia_id = t.id) AS total_productos,
                       (SELECT SUM(dt.cantidad_solicitada) FROM detalle_transferencia dt WHERE dt.transferencia_id = t.id) AS total_unidades
                FROM transferencias_almacen t
                LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
                LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
                LEFT JOIN usuarios us ON t.solicitado_por = us.id
                LEFT JOIN usuarios ue ON t.enviado_por = ue.id
                LEFT JOIN usuarios ur ON t.recibido_por = ur.id
                WHERE $whereClause
                ORDER BY t.creado_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * Get single transfer by ID with details
     */
    public function getById($id) {
        $sql = "SELECT t.*,
                       ao.nombre AS almacen_origen_nombre,
                       ad.nombre AS almacen_destino_nombre,
                       us.nombre AS solicitado_por_nombre,
                       ue.nombre AS enviado_por_nombre,
                       ur.nombre AS recibido_por_nombre
                FROM transferencias_almacen t
                LEFT JOIN almacenes ao ON t.almacen_origen_id = ao.id
                LEFT JOIN almacenes ad ON t.almacen_destino_id = ad.id
                LEFT JOIN usuarios us ON t.solicitado_por = us.id
                LEFT JOIN usuarios ue ON t.enviado_por = ue.id
                LEFT JOIN usuarios ur ON t.recibido_por = ur.id
                WHERE t.id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $transfer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($transfer) {
            $transfer['detalles'] = $this->getDetalles($id);
        }

        return $transfer;
    }

    /**
     * Get transfer details (products)
     */
    public function getDetalles($transferenciaId) {
        $sql = "SELECT dt.*, p.sku AS producto_sku, p.nombre AS producto_nombre
                FROM detalle_transferencia dt
                INNER JOIN productos p ON dt.producto_id = p.id
                WHERE dt.transferencia_id = ?
                ORDER BY p.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$transferenciaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new transfer
     */
    public function create($data) {
        $sql = "INSERT INTO transferencias_almacen
                (empresa_id, almacen_origen_id, almacen_destino_id, fecha_solicitud,
                 motivo, notas, referencia_externa, solicitado_por, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'borrador')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            TenantContext::require('TransferenciaRepository::create'),
            $data['almacen_origen_id'],
            $data['almacen_destino_id'],
            $data['fecha_solicitud'],
            $data['motivo'] ?? null,
            $data['notas'] ?? null,
            $data['referencia_externa'] ?? null,
            $data['solicitado_por']
        ]);

        return $this->pdo->lastInsertId();
    }

    /**
     * Update transfer
     */
    public function update($id, $data) {
        $sql = "UPDATE transferencias_almacen SET
                almacen_origen_id = ?,
                almacen_destino_id = ?,
                fecha_solicitud = ?,
                motivo = ?,
                notas = ?,
                referencia_externa = ?
                WHERE id = ? AND estado = 'borrador'";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['almacen_origen_id'],
            $data['almacen_destino_id'],
            $data['fecha_solicitud'],
            $data['motivo'] ?? null,
            $data['notas'] ?? null,
            $data['referencia_externa'] ?? null,
            $id
        ]);
    }

    /**
     * Add detail line
     */
    public function addDetalle($transferenciaId, $productoId, $cantidad, $notas = null) {
        $sql = "INSERT INTO detalle_transferencia (transferencia_id, producto_id, cantidad_solicitada, notas)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad_solicitada = VALUES(cantidad_solicitada), notas = VALUES(notas)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$transferenciaId, $productoId, $cantidad, $notas]);
    }

    /**
     * Remove detail line
     */
    public function removeDetalle($transferenciaId, $productoId) {
        $sql = "DELETE FROM detalle_transferencia WHERE transferencia_id = ? AND producto_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$transferenciaId, $productoId]);
    }

    /**
     * Clear all details
     */
    public function clearDetalles($transferenciaId) {
        $sql = "DELETE FROM detalle_transferencia WHERE transferencia_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$transferenciaId]);
    }

    /**
     * Update status
     */
    public function updateEstado($id, $estado) {
        $sql = "UPDATE transferencias_almacen SET estado = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$estado, $id]);
    }

    /**
     * Mark as sent (en_transito)
     */
    public function marcarEnviado($id, $userId) {
        $sql = "UPDATE transferencias_almacen
                SET estado = 'en_transito', fecha_envio = CURDATE(), enviado_por = ?
                WHERE id = ? AND estado = 'borrador'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $id]);
    }

    /**
     * Mark as received
     */
    public function marcarRecibido($id, $userId) {
        $sql = "UPDATE transferencias_almacen
                SET estado = 'recibido', fecha_recepcion = CURDATE(), recibido_por = ?
                WHERE id = ? AND estado = 'en_transito'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $id]);
    }

    /**
     * Update received quantities
     */
    public function updateCantidadesRecibidas($transferenciaId, $detalles) {
        $sql = "UPDATE detalle_transferencia SET cantidad_recibida = ? WHERE transferencia_id = ? AND producto_id = ?";
        $stmt = $this->pdo->prepare($sql);

        foreach ($detalles as $detalle) {
            $stmt->execute([$detalle['cantidad_recibida'], $transferenciaId, $detalle['producto_id']]);
        }
    }

    /**
     * Update sent quantities
     */
    public function updateCantidadesEnviadas($transferenciaId, $detalles) {
        $sql = "UPDATE detalle_transferencia SET cantidad_enviada = ? WHERE transferencia_id = ? AND producto_id = ?";
        $stmt = $this->pdo->prepare($sql);

        foreach ($detalles as $detalle) {
            $stmt->execute([$detalle['cantidad_enviada'], $transferenciaId, $detalle['producto_id']]);
        }
    }

    /**
     * Cancel transfer
     */
    public function cancelar($id) {
        $sql = "UPDATE transferencias_almacen SET estado = 'cancelado' WHERE id = ? AND estado IN ('borrador', 'en_transito')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Delete draft transfer
     */
    public function delete($id) {
        // Only delete drafts
        $sql = "DELETE FROM transferencias_almacen WHERE id = ? AND estado = 'borrador'";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get stock at specific warehouse for a product
     */
    public function getStockEnAlmacen($productoId, $almacenId) {
        $sql = "SELECT cantidad FROM stock_ubicacion WHERE producto_id = ? AND almacen_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$productoId, $almacenId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int)$result['cantidad'] : 0;
    }
}
