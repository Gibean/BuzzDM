<?php
require_once 'sesion.php';
require_once 'db.php';

// Call the function to get the PDO object
try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    // If connection fails, immediately return a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

header('Content-Type: application/json');

// Function to send JSON response
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

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Define the columns with aliases to match the frontend JS expectations
            $select_fields = "id, sku, nombre AS name, descripcion AS description, precio AS price, stock, min_stock, estado AS status";

            if (isset($_GET['id'])) {
                // Get single product
                $stmt = $pdo->prepare("SELECT $select_fields FROM productos WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                json_response(true, ['products' => $product ? [$product] : []]); // Wrap single product in 'products' array
            } else {
                // Get all products with pagination and filtering
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;

                $sql_from = "FROM productos WHERE 1=1";
                $params = [];

                if (!empty($_GET['search'])) {
                    $sql_from .= " AND (sku LIKE ? OR nombre LIKE ?)";
                    $params[] = '%' . $_GET['search'] . '%';
                    $params[] = '%' . $_GET['search'] . '%';
                }
                if (!empty($_GET['status'])) {
                    $sql_from .= " AND estado = ?";
                    $params[] = $_GET['status'];
                }

                // Total count for pagination
                $totalStmt = $pdo->prepare("SELECT COUNT(*) " . $sql_from);
                $totalStmt->execute($params);
                $total = $totalStmt->fetchColumn();

                // Data query with sorting and pagination
                $dataSql = "SELECT $select_fields " . $sql_from;
                if (!empty($_GET['sortBy'])) {
                    $sortBy = $_GET['sortBy'];
                    // Whitelist columns to prevent SQL injection
                    $allowedSorts = ['name' => 'nombre', 'sku' => 'sku', 'stock' => 'stock', 'price' => 'precio'];
                    if (array_key_exists($sortBy, $allowedSorts)) {
                        $dataSql .= " ORDER BY " . $allowedSorts[$sortBy] . " ASC";
                    }
                }
                $dataSql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

                $dataStmt = $pdo->prepare($dataSql);
                $dataStmt->execute($params);
                $products = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                json_response(true, ['products' => $products, 'total' => $total]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true); // Expect JSON
            $sku = $input['sku'] ?? '';
            $nombre = $input['name'] ?? '';
            $descripcion = $input['description'] ?? '';
            $precio = $input['price'] ?? 0;
            $stock = $input['stock'] ?? 0;
            $min_stock = $input['min_stock'] ?? 10;
            $estado = $input['status'] ?? 'activo'; // Get status

            if (empty($sku) || empty($nombre)) {
                json_response(false, [], 'SKU y Nombre son obligatorios.');
            }

            // Create
            $sql = "INSERT INTO productos (sku, nombre, descripcion, precio, stock, min_stock, estado) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado]);
            json_response(true, ['id' => $pdo->lastInsertId()], 'Producto creado exitosamente.');
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true); // Expect JSON
            $id = $input['id'] ?? null;
            $sku = $input['sku'] ?? ''; // SKU might be updated, but usually not for existing products
            $nombre = $input['name'] ?? '';
            $descripcion = $input['description'] ?? '';
            $precio = $input['price'] ?? 0;
            $stock = $input['stock'] ?? 0;
            $min_stock = $input['min_stock'] ?? 10;
            $estado = $input['status'] ?? 'activo'; // Get status

            if (!$id) {
                json_response(false, [], 'ID de producto no proporcionado para actualizar.');
            }
            if (empty($sku) || empty($nombre)) {
                json_response(false, [], 'SKU y Nombre son obligatorios.');
            }

            // Update
            $sql = "UPDATE productos SET sku = ?, nombre = ?, descripcion = ?, precio = ?, stock = ?, min_stock = ?, estado = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$sku, $nombre, $descripcion, $precio, $stock, $min_stock, $estado, $id]);
            json_response(true, [], 'Producto actualizado exitosamente.');
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true); // Expect JSON
            $id = $input['id'] ?? null;
            if (!$id) {
                json_response(false, [], 'ID de producto no proporcionado.');
            }
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
            $stmt->execute([$id]);
            json_response(true, [], 'Producto eliminado exitosamente.');
            break;

        default:
            json_response(false, [], 'Método no permitido.');
            break;
    }
} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
?>