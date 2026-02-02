<?php
require_once __DIR__ . '/../../db.php';

/**
 * KitRepository - Handles DB operations for product kit components
 */
class KitRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Get components of a kit
     */
    public function getComponents($kitId) {
        $sql = "SELECT pc.id, pc.kit_id, pc.componente_id,
                    p.sku AS componente_sku, p.nombre AS componente_nombre,
                    p.tipo_producto AS componente_tipo, p.precio AS componente_precio,
                    p.stock AS componente_stock, pc.cantidad,
                    (pc.cantidad * p.precio) AS subtotal, pc.orden,
                    CASE
                        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual = 1
                        THEN (SELECT fn_stock_kit_virtual(p.id))
                        WHEN p.tipo_producto = 'servicio' THEN 999999
                        ELSE p.stock
                    END AS stock_disponible
                FROM producto_componentes pc
                INNER JOIN productos p ON p.id = pc.componente_id
                WHERE pc.kit_id = ?
                ORDER BY pc.orden, pc.id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kitId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get kit info
     */
    public function getKit($kitId) {
        $stmt = $this->pdo->prepare("SELECT tipo_producto, es_kit_virtual FROM productos WHERE id = ?");
        $stmt->execute([$kitId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get kit virtual stock
     */
    public function getKitVirtualStock($kitId) {
        $stmt = $this->pdo->prepare("SELECT fn_stock_kit_virtual(?)");
        $stmt->execute([$kitId]);
        return $stmt->fetchColumn();
    }

    /**
     * Check if product exists
     */
    public function productExists($productId) {
        $stmt = $this->pdo->prepare("SELECT id FROM productos WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$productId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Get max component order
     */
    public function getMaxOrder($kitId) {
        $stmt = $this->pdo->prepare("SELECT MAX(orden) FROM producto_componentes WHERE kit_id = ?");
        $stmt->execute([$kitId]);
        return $stmt->fetchColumn() ?? 0;
    }

    /**
     * Add component to kit
     */
    public function addComponent($kitId, $componentId, $quantity, $order) {
        $sql = "INSERT INTO producto_componentes (kit_id, componente_id, cantidad, orden)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$kitId, $componentId, $quantity, $order]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update component
     */
    public function updateComponent($id, $quantity, $order = null) {
        $sql = "UPDATE producto_componentes SET cantidad = ?";
        $params = [$quantity];

        if ($order !== null) {
            $sql .= ", orden = ?";
            $params[] = $order;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete component
     */
    public function deleteComponent($id) {
        $stmt = $this->pdo->prepare("DELETE FROM producto_componentes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
