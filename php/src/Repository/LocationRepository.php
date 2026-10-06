<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../Tenant/TenantContext.php';

/**
 * LocationRepository - Handles DB operations for warehouse locations
 */
class LocationRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    /**
     * Get all locations for a warehouse
     */
    public function findByWarehouse($warehouseId) {
        $sql = "SELECT u.*,
                COALESCE((SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE ubicacion_id = u.id), 0) as productos_count
                FROM ubicaciones u
                WHERE u.almacen_id = ?
                ORDER BY u.codigo ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find location by ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ubicaciones WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Check if code exists in warehouse
     */
    public function codeExists($warehouseId, $code, $excludeId = null) {
        $sql = "SELECT id FROM ubicaciones WHERE almacen_id = ? AND codigo = ?";
        $params = [$warehouseId, $code];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Create location
     */
    public function create($data) {
        $sql = "INSERT INTO ubicaciones (empresa_id, almacen_id, codigo, nombre, pasillo, estante, nivel, capacidad_maxima, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            TenantContext::require('LocationRepository::create'),
            $data['almacen_id'],
            trim($data['codigo']),
            trim($data['nombre'] ?? ''),
            trim($data['pasillo'] ?? ''),
            trim($data['estante'] ?? ''),
            trim($data['nivel'] ?? ''),
            $data['capacidad_maxima'] ?: null,
            isset($data['activo']) ? ($data['activo'] ? 1 : 0) : 1
        ]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Update location
     */
    public function update($id, $data) {
        $sql = "UPDATE ubicaciones SET codigo = ?, nombre = ?, pasillo = ?, estante = ?,
                nivel = ?, capacidad_maxima = ?, activo = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            trim($data['codigo']),
            trim($data['nombre'] ?? ''),
            trim($data['pasillo'] ?? ''),
            trim($data['estante'] ?? ''),
            trim($data['nivel'] ?? ''),
            $data['capacidad_maxima'] ?: null,
            isset($data['activo']) ? ($data['activo'] ? 1 : 0) : 1,
            $id
        ]);
    }

    /**
     * Check if location has stock
     */
    public function hasStock($id) {
        $stmt = $this->pdo->prepare("SELECT SUM(cantidad) as total FROM stock_ubicacion WHERE ubicacion_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result && $result['total'] > 0;
    }

    /**
     * Delete location
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM ubicaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
