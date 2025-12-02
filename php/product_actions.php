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

        if ($id) {
            // Update
            $sql = "UPDATE productos SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $id]);
            json_response(true, [], 'Producto actualizado exitosamente.');
        } else {
            // Create
            // Check if SKU exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE sku = ?");
            $stmt->execute([$sku]);
            if ($stmt->fetchColumn() > 0) {
                json_response(false, [], 'El SKU ya existe.');
            }

            $sql = "INSERT INTO productos (sku, nombre, descripcion, precio, stock, min_stock, estado, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado]);
            json_response(true, [], 'Producto creado exitosamente.');
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

        $sql = "UPDATE productos SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $id]);
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
            // Fetch single product
            $stmt = $pdo->prepare("SELECT id, sku, nombre as name, descripcion as description, precio as price, stock, min_stock, estado as status, deleted_at FROM productos WHERE id = ?");
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
            $sortBy = $_GET['sortBy'] ?? 'created_at';
            $sortOrder = $_GET['sortOrder'] ?? 'DESC';
            $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === 'true';

            // Allowed sort columns
            $allowedSorts = ['sku', 'nombre', 'precio', 'stock', 'created_at'];
            if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';
            if ($sortBy === 'name') $sortBy = 'nombre';
            if ($sortBy === 'price') $sortBy = 'precio';

            $whereClauses = ["1=1"];
            $params = [];

            // Filter by deleted status
            if (!$showDeleted) {
                $whereClauses[] = "deleted_at IS NULL";
            } else {
                // If showing deleted, we might want to show ONLY deleted or ALL.
                // Usually "Show Deleted" toggle implies showing them mixed in or exclusively.
                // Based on UI implementation, it seems to filter to show deleted items.
                // Let's assume we show ALL if show_deleted is true, or maybe we should filter?
                // The previous implementation likely showed all or filtered.
                // Let's stick to: if show_deleted is true, we don't filter by deleted_at IS NULL.
                // But wait, if the user wants to see *only* deleted items, the UI might send a specific filter?
                // The UI checkbox says "Ver eliminados". If checked, it sends show_deleted=true.
                // If unchecked, it doesn't send it.
                // So if checked, we show everything (including deleted).
            }

            if ($search) {
                $whereClauses[] = "(sku LIKE ? OR nombre LIKE ? OR descripcion LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if ($status) {
                $whereClauses[] = "estado = ?";
                $params[] = $status;
            }

            $whereSql = implode(' AND ', $whereClauses);

            // Count total
            $countSql = "SELECT COUNT(*) FROM productos WHERE $whereSql";
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = $stmt->fetchColumn();

            // Fetch data
            $sql = "SELECT id, sku, nombre as name, descripcion as description, precio as price, stock, min_stock, estado as status, deleted_at
                    FROM productos
                    WHERE $whereSql
                    ORDER BY $sortBy $sortOrder
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
