<?php
require_once __DIR__ . '/../../db.php';

class ProductRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, sku, nombre as name, descripcion as description, precio as price, stock, min_stock,
                   estado as status, marca_id, categoria, proveedor_id, modelo, color, tamanio, medidas,
                   peso_kg, material, unidad_venta, tipo_producto, maneja_stock, producto_padre_id,
                   es_kit_virtual, variante_atributo, deleted_at
            FROM productos
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findBySku($sku) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM productos WHERE sku = ?");
        $stmt->execute([$sku]);
        return $stmt->fetchColumn() > 0;
    }

    public function countByCompanyId(int $companyId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND deleted_at IS NULL");
        $stmt->execute([$companyId]);
        return (int)$stmt->fetchColumn();
    }

    public function findAll($filters = []) {
        $page = isset($filters['page']) ? max(1, intval($filters['page'])) : 1;
        $limit = isset($filters['limit']) ? max(1, intval($filters['limit'])) : 10;
        $offset = ($page - 1) * $limit;

        $sortBy = $filters['sortBy'] ?? 'creado_at';
        $sortOrder = $filters['sortOrder'] ?? 'DESC';

        // Allowed sort columns
        $allowedSorts = ['sku', 'nombre', 'precio', 'stock', 'creado_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'creado_at';
        if ($sortBy === 'name') $sortBy = 'nombre';
        if ($sortBy === 'price') $sortBy = 'precio';

        $whereClauses = ["1=1"];
        $params = [];

        // Filter by deleted status
        if (empty($filters['show_deleted']) || $filters['show_deleted'] !== 'true') {
            $whereClauses[] = "p.deleted_at IS NULL";
        }

        // Search
        if (!empty($filters['search'])) {
            $whereClauses[] = "(p.sku LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ? OR m.nombre LIKE ? OR c.nombre LIKE ? OR p.modelo LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params = array_merge($params, array_fill(0, 6, $searchTerm));
        }

        // Status
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'stock-bajo') {
                $whereClauses[] = "p.stock <= p.min_stock AND p.estado <> 'agotado'";
            } elseif ($filters['status'] === 'agotado') {
                $whereClauses[] = "(p.stock <= 0 OR p.estado = 'agotado')";
            } else {
                $whereClauses[] = "p.estado = ?";
                $params[] = $filters['status'];
            }
        }

        // Other filters
        if (!empty($filters['marca_id'])) {
            $whereClauses[] = "p.marca_id = ?";
            $params[] = $filters['marca_id'];
        }
        if (!empty($filters['categoria_id'])) {
            $whereClauses[] = "p.categoria = ?";
            $params[] = $filters['categoria_id'];
        }
        if (!empty($filters['modelo'])) {
            $whereClauses[] = "p.modelo LIKE ?";
            $params[] = "%" . $filters['modelo'] . "%";
        }
        if (!empty($filters['unidad_venta'])) {
            $whereClauses[] = "p.unidad_venta = ?";
            $params[] = $filters['unidad_venta'];
        }

        $whereSql = implode(' AND ', $whereClauses);

        // Count Query
        $countSql = "SELECT COUNT(*) FROM productos p
                     LEFT JOIN marcas m ON p.marca_id = m.id
                     LEFT JOIN categorias c ON p.categoria = c.id
                     WHERE $whereSql";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Data Query
        $sql = "SELECT p.*, p.nombre as name, p.descripcion as description, p.precio as price, p.estado as status,
                       m.nombre as marca_nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM productos v WHERE v.producto_padre_id = p.id AND v.deleted_at IS NULL) as total_variantes,
                       (SELECT COUNT(*) FROM producto_componentes pc WHERE pc.kit_id = p.id) as total_componentes
                FROM productos p
                LEFT JOIN marcas m ON p.marca_id = m.id
                LEFT JOIN categorias c ON p.categoria = c.id
                WHERE $whereSql
                ORDER BY p.$sortBy $sortOrder
                LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['products' => $products, 'total' => $total];
    }

    public function create($data) {
        $sql = "INSERT INTO productos (
            sku, nombre, descripcion, precio, stock, min_stock, estado,
            marca_id, categoria, proveedor_id, modelo, color, tamanio,
            medidas, peso_kg, material, unidad_venta, tipo_producto,
            maneja_stock, producto_padre_id, es_kit_virtual, variante_atributo, creado_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, NOW()
        )";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['sku'], $data['nombre'], $data['descripcion'], $data['precio'], $data['stock'], $data['min_stock'], $data['estado'],
            $data['marca_id'], $data['categoria'], $data['proveedor_id'], $data['modelo'], $data['color'], $data['tamanio'],
            $data['medidas'], $data['peso_kg'], $data['material'], $data['unidad_venta'], $data['tipo_producto'],
            $data['maneja_stock'], $data['producto_padre_id'], $data['es_kit_virtual'], $data['variante_atributo']
        ]);

        return $this->pdo->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE productos SET
            sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ?,
            marca_id = ?, categoria = ?, proveedor_id = ?, modelo = ?, color = ?, tamanio = ?,
            medidas = ?, peso_kg = ?, material = ?, unidad_venta = ?, tipo_producto = ?,
            maneja_stock = ?, producto_padre_id = ?, es_kit_virtual = ?, variante_atributo = ?, actualizado_at = NOW()
            WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['sku'], $data['nombre'], $data['descripcion'], $data['precio'], $data['stock'], $data['min_stock'], $data['estado'],
            $data['marca_id'], $data['categoria'], $data['proveedor_id'], $data['modelo'], $data['color'], $data['tamanio'],
            $data['medidas'], $data['peso_kg'], $data['material'], $data['unidad_venta'], $data['tipo_producto'],
            $data['maneja_stock'], $data['producto_padre_id'], $data['es_kit_virtual'], $data['variante_atributo'],
            $id
        ]);
    }

    public function softDelete($id) {
        $stmt = $this->pdo->prepare("UPDATE productos SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore($id) {
        $stmt = $this->pdo->prepare("UPDATE productos SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
