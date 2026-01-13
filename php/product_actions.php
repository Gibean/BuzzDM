<?php
require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

// Helper function for JSON response
function json_response($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit;
}

try {
    $pdo = getDbConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;

    // Handle POST requests (Create, Update, Restore)
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        // Handle Restore Action
        if (isset($input['action']) && $input['action'] === 'restore') {
            if (!isset($input['id'])) {
                json_response(false, [], 'ID de producto no proporcionado.');
            }

            $stmt = $pdo->prepare("UPDATE productos SET deleted_at = NULL WHERE id = ?");
            $stmt->execute([$input['id']]);

            json_response(true, [], 'Producto restaurado exitosamente.');
        }

        // Handle Create/Update
        $id = isset($_GET['id']) ? $_GET['id'] : (isset($input['id']) ? $input['id'] : null);

        // Validate required fields
        if (empty($input['sku']) || empty($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
             json_response(false, [], 'Datos incompletos. SKU, Nombre, Precio y Stock son obligatorios.');
        }

        $sku = trim($input['sku']);
        $nombre = trim($input['name']);
        $descripcion = trim($input['description'] ?? '');
        $precio = floatval($input['price']);
        $stock = intval($input['stock']);
        $min_stock = intval($input['min_stock'] ?? 0);
        $estado = $input['status'] ?? 'activo';

        // New attribute fields
        $marca_id = !empty($input['marca_id']) ? intval($input['marca_id']) : null;
        $categoria = !empty($input['categoria']) ? intval($input['categoria']) : null;
        $proveedor_id = !empty($input['proveedor_id']) ? intval($input['proveedor_id']) : null;
        $modelo = trim($input['modelo'] ?? '');
        $color = trim($input['color'] ?? '');
        $tamanio = trim($input['tamanio'] ?? '');
        $medidas = trim($input['medidas'] ?? '');
        $peso_kg = !empty($input['peso_kg']) ? floatval($input['peso_kg']) : null;
        $material = trim($input['material'] ?? '');
        $unidad_venta = trim($input['unidad_venta'] ?? '');

        // Product type fields
        $tipo_producto = $input['tipo_producto'] ?? 'fisico';
        $maneja_stock = isset($input['maneja_stock']) ? ($input['maneja_stock'] ? 1 : 0) : 1;
        $producto_padre_id = !empty($input['producto_padre_id']) ? intval($input['producto_padre_id']) : null;
        $es_kit_virtual = isset($input['es_kit_virtual']) ? ($input['es_kit_virtual'] ? 1 : 0) : 0;
        $variante_atributo = trim($input['variante_atributo'] ?? '');

        if ($id) {
            // Update
            $sql = "UPDATE productos SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ?, marca_id = ?, categoria = ?, proveedor_id = ?, modelo = ?, color = ?, tamanio = ?, medidas = ?, peso_kg = ?, material = ?, unidad_venta = ?, tipo_producto = ?, maneja_stock = ?, producto_padre_id = ?, es_kit_virtual = ?, variante_atributo = ?, actualizado_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $marca_id, $categoria, $proveedor_id, $modelo, $color, $tamanio, $medidas, $peso_kg, $material, $unidad_venta, $tipo_producto, $maneja_stock, $producto_padre_id, $es_kit_virtual, $variante_atributo, $id]);
            json_response(true, [], 'Producto actualizado exitosamente.');
        } else {
            // Create
            // Check if SKU exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetchColumn() > 0) {
                json_response(false, [], 'El SKU ya existe.');
            }

            $sql = "INSERT INTO productos (sku, nombre, descripcion, precio, stock, min_stock, estado, marca_id, categoria, proveedor_id, modelo, color, tamanio, medidas, peso_kg, material, unidad_venta, tipo_producto, maneja_stock, producto_padre_id, es_kit_virtual, variante_atributo, creado_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $marca_id, $categoria, $proveedor_id, $modelo, $color, $tamanio, $medidas, $peso_kg, $material, $unidad_venta, $tipo_producto, $maneja_stock, $producto_padre_id, $es_kit_virtual, $variante_atributo]);
            $newId = $pdo->lastInsertId();
            json_response(true, ['id' => $newId], 'Producto creado exitosamente.');
        }
    }
    // Handle PUT requests (Update via method override or actual PUT)
    elseif ($method === 'PUT') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? $input['id'] ?? null;

        if (!$id) {
            json_response(false, [], 'ID de producto no proporcionado para actualización.');
        }

        // Validate required fields (similar to POST)
        if (empty($input['sku']) || empty($input['name']) || !isset($input['price']) || !isset($input['stock'])) {
             json_response(false, [], 'Datos incompletos.');
        }

        $sku = trim($input['sku']);
        $nombre = trim($input['name']);
        $descripcion = trim($input['description'] ?? '');
        $precio = floatval($input['price']);
        $stock = intval($input['stock']);
        $min_stock = intval($input['min_stock'] ?? 0);
        $estado = $input['status'] ?? 'activo';

        // New attribute fields
        $marca_id = !empty($input['marca_id']) ? intval($input['marca_id']) : null;
        $categoria = !empty($input['categoria']) ? intval($input['categoria']) : null;
        $proveedor_id = !empty($input['proveedor_id']) ? intval($input['proveedor_id']) : null;
        $modelo = trim($input['modelo'] ?? '');
        $color = trim($input['color'] ?? '');
        $tamanio = trim($input['tamanio'] ?? '');
        $medidas = trim($input['medidas'] ?? '');
        $peso_kg = !empty($input['peso_kg']) ? floatval($input['peso_kg']) : null;
        $material = trim($input['material'] ?? '');
        $unidad_venta = trim($input['unidad_venta'] ?? '');

        // Product type fields
        $tipo_producto = $input['tipo_producto'] ?? 'fisico';
        $maneja_stock = isset($input['maneja_stock']) ? ($input['maneja_stock'] ? 1 : 0) : 1;
        $producto_padre_id = !empty($input['producto_padre_id']) ? intval($input['producto_padre_id']) : null;
        $es_kit_virtual = isset($input['es_kit_virtual']) ? ($input['es_kit_virtual'] ? 1 : 0) : 0;
        $variante_atributo = trim($input['variante_atributo'] ?? '');

        $sql = "UPDATE productos SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ?, marca_id = ?, categoria = ?, proveedor_id = ?, modelo = ?, color = ?, tamanio = ?, medidas = ?, peso_kg = ?, material = ?, unidad_venta = ?, tipo_producto = ?, maneja_stock = ?, producto_padre_id = ?, es_kit_virtual = ?, variante_atributo = ?, actualizado_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $marca_id, $categoria, $proveedor_id, $modelo, $color, $tamanio, $medidas, $peso_kg, $material, $unidad_venta, $tipo_producto, $maneja_stock, $producto_padre_id, $es_kit_virtual, $variante_atributo, $id]);
        json_response(true, [], 'Producto actualizado exitosamente.');
    }
    // Handle DELETE requests (Soft Delete)
    elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? $input['id'] ?? null;

        if (!$id) {
            json_response(false, [], 'ID de producto no proporcionado.');
        }

        // Soft delete: Update deleted_at timestamp
        $stmt = $pdo->prepare("UPDATE productos SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        json_response(true, [], 'Producto eliminado exitosamente.');
    }
    // Handle GET requests (Fetch)
    elseif ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Fetch single product with type fields and components
            $stmt = $pdo->prepare("SELECT id, sku, nombre as name, descripcion as description, precio as price, stock, min_stock, estado as status, marca_id, categoria, proveedor_id, modelo, color, tamanio, medidas, peso_kg, material, unidad_venta, tipo_producto, maneja_stock, producto_padre_id, es_kit_virtual, variante_atributo, deleted_at FROM productos WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product) {
                json_response(true, ['products' => [$product]]);
            } else {
                json_response(false, [], 'Producto no encontrado.');
            }
        } else {
            // Fetch list with pagination and filters
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';
            $sortBy = $_GET['sortBy'] ?? 'creado_at';
            $sortOrder = $_GET['sortOrder'] ?? 'DESC';
            $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === 'true';

            // New filters
            $marca_id = $_GET['marca_id'] ?? '';
            $categoria_id = $_GET['categoria_id'] ?? '';
            $modelo = $_GET['modelo'] ?? '';
            $unidad_venta = $_GET['unidad_venta'] ?? '';

            // Allowed sort columns
            $allowedSorts = ['sku', 'nombre', 'precio', 'stock', 'creado_at'];
            if (!in_array($sortBy, $allowedSorts)) $sortBy = 'creado_at';
            if ($sortBy === 'name') $sortBy = 'nombre';
            if ($sortBy === 'price') $sortBy = 'precio';

            $whereClauses = ["1=1"];
            $params = [];

            // Filter by deleted status
            if (!$showDeleted) {
                $whereClauses[] = "p.deleted_at IS NULL";
            }

            if ($search) {
                $whereClauses[] = "(p.sku LIKE ? OR p.nombre LIKE ? OR p.descripcion LIKE ? OR m.nombre LIKE ? OR c.nombre LIKE ? OR p.modelo LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($status) {
                if ($status === 'stock-bajo') {
                    // Logic consistent with v_low_stock: stock <= min_stock and not specifically marked as 'agotado'
                    $whereClauses[] = "p.stock <= p.min_stock AND p.estado <> 'agotado'";
                } elseif ($status === 'agotado') {
                    $whereClauses[] = "(p.stock <= 0 OR p.estado = 'agotado')";
                } else {
                    $whereClauses[] = "p.estado = ?";
                    $params[] = $status;
                }
            }

            if ($marca_id) {
                $whereClauses[] = "p.marca_id = ?";
                $params[] = $marca_id;
            }

            if ($categoria_id) {
                $whereClauses[] = "p.categoria = ?";
                $params[] = $categoria_id;
            }

            if ($modelo) {
                $whereClauses[] = "p.modelo LIKE ?";
                $params[] = "%$modelo%";
            }

            if ($unidad_venta) {
                $whereClauses[] = "p.unidad_venta = ?";
                $params[] = $unidad_venta;
            }

            $whereSql = implode(' AND ', $whereClauses);

            // Count total
            $countSql = "SELECT COUNT(*) FROM productos p
                         LEFT JOIN marcas m ON p.marca_id = m.id
                         LEFT JOIN categorias c ON p.categoria = c.id
                         WHERE $whereSql";
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // Fetch data
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

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(true, ['products' => $products, 'total' => $total]);
        }
    } else {
        json_response(false, [], 'Método no permitido.');
    }

} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
?>
