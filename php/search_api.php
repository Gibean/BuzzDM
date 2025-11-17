<?php
require_once 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$query = $_GET['query'] ?? '';

if (empty($action) || empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Acción o consulta no especificada.']);
    exit;
}

$conn = getDbConnection();

switch ($action) {
    

    case 'search_clients':
        try {
            $stmt = $conn->prepare("SELECT id, nombre, email, telefono FROM clientes WHERE nombre LIKE ? OR id LIKE ? LIMIT 10");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm, $searchTerm]); // PDO execute with array of parameters
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results
            echo json_encode(['success' => true, 'clients' => $clients]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al buscar clientes: ' . $e->getMessage()]);
        }
        break;

    case 'search_products':
        try {
            $stmt = $conn->prepare("SELECT id, sku, nombre, precio, stock FROM productos WHERE nombre LIKE ? OR sku LIKE ? LIMIT 10");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm, $searchTerm]); // PDO execute with array of parameters
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results
            echo json_encode(['success' => true, 'products' => $products]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al buscar productos: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
}

?>