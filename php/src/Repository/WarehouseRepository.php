<?php
require_once __DIR__ . '/../../db.php';

class WarehouseRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    // --- Warehouses (Almacenes) ---

    public function findAllWarehouses($showInactive = false) {
        $whereClause = $showInactive ? '1=1' : 'a.activo = 1';

        $sql = "
            SELECT a.*,
                   (SELECT COUNT(*) FROM ubicaciones WHERE almacen_id = a.id AND activo = 1) as total_ubicaciones,
                   (SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE almacen_id = a.id AND cantidad > 0) as total_productos
            FROM almacenes a
            WHERE $whereClause
            ORDER BY a.es_principal DESC, a.nombre ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWarehouseById($id) {
        $stmt = $this->pdo->prepare("
            SELECT a.*,
                   (SELECT COUNT(*) FROM ubicaciones WHERE almacen_id = a.id) as total_ubicaciones,
                   (SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE almacen_id = a.id) as total_productos
            FROM almacenes a
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findWarehouseByCode($code, $excludeId = null) {
        $sql = "SELECT id FROM almacenes WHERE codigo = ?";
        $params = [$code];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function createWarehouse($data) {
        $sql = "INSERT INTO almacenes (codigo, nombre, direccion, telefono, tipo, es_principal, activo, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $data['tipo'],
            $data['es_principal'] ? 1 : 0,
            $data['activo'] ? 1 : 0,
            $data['notas']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateWarehouse($id, $data) {
        $sql = "UPDATE almacenes SET
                    codigo = ?, nombre = ?, direccion = ?, telefono = ?,
                    tipo = ?, es_principal = ?, activo = ?, notas = ?,
                    actualizado_at = NOW()
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $data['tipo'],
            $data['es_principal'] ? 1 : 0,
            $data['activo'] ? 1 : 0,
            $data['notas'],
            $id
        ]);
    }

    public function resetPrincipalWarehouse($excludeId = null) {
        $sql = "UPDATE almacenes SET es_principal = 0";
        if ($excludeId) {
            $sql .= " WHERE id != ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$excludeId]);
        } else {
            return $this->pdo->exec($sql);
        }
    }

    public function deactivateWarehouse($id) {
        $stmt = $this->pdo->prepare("UPDATE almacenes SET activo = 0, actualizado_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getStats() {
        return [
            'total_almacenes' => $this->pdo->query("SELECT COUNT(*) FROM almacenes WHERE activo = 1")->fetchColumn(),
            'total_ubicaciones' => $this->pdo->query("SELECT COUNT(*) FROM ubicaciones WHERE activo = 1")->fetchColumn(),
            'total_productos' => $this->pdo->query("SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE cantidad > 0")->fetchColumn(),
            'almacen_principal' => $this->pdo->query("SELECT nombre FROM almacenes WHERE es_principal = 1 LIMIT 1")->fetchColumn() ?: 'N/A'
        ];
    }

    // --- Locations (Ubicaciones) ---

    public function findLocationsByWarehouseId($warehouseId) {
        $sql = "
            SELECT u.*,
                   COALESCE((SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE ubicacion_id = u.id), 0) as productos_count
            FROM ubicaciones u
            WHERE u.almacen_id = ?
            ORDER BY u.codigo ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$warehouseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findLocationEmptyFields($warehouseId) {
         // Dummy method if needed in future, currently select U.* covers all
         return [];
    }

    public function findLocationById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ubicaciones WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findLocationByCodeInWarehouse($warehouseId, $code, $excludeId = null) {
        $sql = "SELECT id FROM ubicaciones WHERE almacen_id = ? AND codigo = ?";
        $params = [$warehouseId, $code];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function createLocation($data) {
        $sql = "INSERT INTO ubicaciones (almacen_id, codigo, nombre, pasillo, estante, nivel, capacidad_maxima, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['almacen_id'],
            $data['codigo'],
            $data['nombre'],
            $data['pasillo'],
            $data['estante'],
            $data['nivel'],
            $data['capacidad_maxima'],
            $data['activo'] ? 1 : 0
        ]);
        return $this->pdo->lastInsertId();
    }

    public function updateLocation($id, $data) {
        $sql = "UPDATE ubicaciones SET
                    codigo = ?, nombre = ?, pasillo = ?, estante = ?,
                    nivel = ?, capacidad_maxima = ?, activo = ?
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['codigo'],
            $data['nombre'],
            $data['pasillo'],
            $data['estante'],
            $data['nivel'],
            $data['capacidad_maxima'],
            $data['activo'] ? 1 : 0,
            $id
        ]);
    }

    public function deleteLocation($id) {
        $stmt = $this->pdo->prepare("DELETE FROM ubicaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getLocationStockCount($id) {
        $stmt = $this->pdo->prepare("SELECT SUM(cantidad) as total FROM stock_ubicacion WHERE ubicacion_id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ? (float)$res['total'] : 0;
    }
}
