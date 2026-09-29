<?php
require_once __DIR__ . '/BaseRepository.php';

class ProductRepository extends BaseRepository
{
    public function findById($id)
    {
        $where = ['id = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', "ProductRepository::findById($id)");

        $stmt = $this->pdo->prepare("
            SELECT id, empresa_id, sku, nombre as name, descripcion as description, precio as price, stock, min_stock,
                   estado as status, marca_id, categoria, proveedor_id, modelo, color, tamanio, medidas,
                   peso_kg, material, unidad_venta, tipo_producto, maneja_stock, producto_padre_id,
                   es_kit_virtual, variante_atributo, deleted_at
            FROM productos
            WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }

    public function findBySku($sku)
    {
        $where = ['sku = ?'];
        $params = [$sku];
        $this->scopeTenant($where, $params, '', "ProductRepository::findBySku($sku)");

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM productos WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Cuenta los productos visibles para el tenant activo.
     *
     * Antes este método recibía un empresa_id arbitrario, lo que lo convertía en
     * una forma trivial de interrobar el tamaño del catálogo de otra empresa si
     * el valor llegaba desde un parámetro controlado por el usuario. El único
     * llamador le pasaba el empresa_id de su propia sesión, así que el
     * parámetro era redundante: el tenant ya viene del contexto.
     */
    public function countForTenant(): int
    {
        $where = ['deleted_at IS NULL'];
        $params = [];
        $this->scopeTenant($where, $params, '', 'ProductRepository::countForTenant');

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM productos WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findAll($filters = [])
    {
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
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereClauses = ["1=1"];
        $params = [];

        // Filtro de tenant: sin esto cada empresa veía el catálogo completo.
        $this->scopeTenant($whereClauses, $params, 'p', 'ProductRepository::findAll');

        // Los catálogos se correlacionan con p.empresa_id en vez de con el tenant
        // de sesión. Así un marca_id o categoria_id que por error apunte a la
        // fila de otra empresa se resuelve como NULL (LEFT JOIN) en lugar de
        // mostrar el nombre de un tercero.
        $marcasJoin    = 'LEFT JOIN marcas m ON p.marca_id = m.id AND m.empresa_id = p.empresa_id';
        $categoriasJoin = 'LEFT JOIN categorias c ON p.categoria = c.id AND c.empresa_id = p.empresa_id';

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
                     $marcasJoin
                     $categoriasJoin
                     WHERE $whereSql";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        // Data Query
        $sql = "SELECT p.*, p.nombre as name, p.descripcion as description, p.precio as price, p.estado as status,
                       m.nombre as marca_nombre, c.nombre as categoria_nombre,
                       (SELECT COUNT(*) FROM productos v WHERE v.producto_padre_id = p.id AND v.empresa_id = p.empresa_id AND v.deleted_at IS NULL) as total_variantes,
                       (SELECT COUNT(*) FROM producto_componentes pc WHERE pc.kit_id = p.id) as total_componentes
                FROM productos p
                $marcasJoin
                $categoriasJoin
                WHERE $whereSql
                ORDER BY p.$sortBy $sortOrder
                LIMIT $limit OFFSET $offset";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return ['products' => $products, 'total' => $total];
    }

    public function create($data)
    {
        // empresa_id lo inyecta insertTenant; antes se dependía del DEFAULT 1 de la
        // columna, lo que metía los productos de cada demo en el tenant principal.
        return $this->insertTenant('productos', [
            'sku' => $data['sku'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'precio' => $data['precio'],
            'stock' => $data['stock'],
            'min_stock' => $data['min_stock'],
            'estado' => $data['estado'],
            'marca_id' => $data['marca_id'],
            'categoria' => $data['categoria'],
            'proveedor_id' => $data['proveedor_id'],
            'modelo' => $data['modelo'],
            'color' => $data['color'],
            'tamanio' => $data['tamanio'],
            'medidas' => $data['medidas'],
            'peso_kg' => $data['peso_kg'],
            'material' => $data['material'],
            'unidad_venta' => $data['unidad_venta'],
            'tipo_producto' => $data['tipo_producto'],
            'maneja_stock' => $data['maneja_stock'],
            'producto_padre_id' => $data['producto_padre_id'],
            'es_kit_virtual' => $data['es_kit_virtual'],
            'variante_atributo' => $data['variante_atributo'],
            'creado_at' => date('Y-m-d H:i:s'),
        ], 'ProductRepository::create');
    }

    public function update($id, $data)
    {
        return $this->applyTenantUpdate('productos', [
            'sku' => $data['sku'],
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'],
            'precio' => $data['precio'],
            'stock' => $data['stock'],
            'min_stock' => $data['min_stock'],
            'estado' => $data['estado'],
            'marca_id' => $data['marca_id'],
            'categoria' => $data['categoria'],
            'proveedor_id' => $data['proveedor_id'],
            'modelo' => $data['modelo'],
            'color' => $data['color'],
            'tamanio' => $data['tamanio'],
            'medidas' => $data['medidas'],
            'peso_kg' => $data['peso_kg'],
            'material' => $data['material'],
            'unidad_venta' => $data['unidad_venta'],
            'tipo_producto' => $data['tipo_producto'],
            'maneja_stock' => $data['maneja_stock'],
            'producto_padre_id' => $data['producto_padre_id'],
            'es_kit_virtual' => $data['es_kit_virtual'],
            'variante_atributo' => $data['variante_atributo'],
            'actualizado_at' => date('Y-m-d H:i:s'),
        ], (int)$id, "ProductRepository::update($id)");
    }

    public function softDelete($id)
    {
        return $this->applyTenantUpdate(
            'productos',
            ['deleted_at' => date('Y-m-d H:i:s')],
            (int)$id,
            "ProductRepository::softDelete($id)"
        );
    }

    public function restore($id)
    {
        return $this->applyTenantUpdate(
            'productos',
            ['deleted_at' => null],
            (int)$id,
            "ProductRepository::restore($id)"
        );
    }
}
