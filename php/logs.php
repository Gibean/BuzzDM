<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

// Get PDO connection
try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $sql = "SELECT ll.id, u.nombre AS nombre_usuario, ll.accion, ll.detalles, ll.fecha FROM logs_login ll LEFT JOIN usuarios u ON ll.usuario_id = u.id WHERE 1=1";
        $params = [];

        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $sql .= " AND u.nombre LIKE ?";
            $params[] = '%' . $_GET['search'] . '%';
        }
        if (isset($_GET['dateFrom']) && $_GET['dateFrom'] !== '') {
            $sql .= " AND DATE(ll.fecha) >= ?";
            $params[] = $_GET['dateFrom'];
        }
        if (isset($_GET['dateTo']) && $_GET['dateTo'] !== '') {
            $sql .= " AND DATE(ll.fecha) <= ?";
            $params[] = $_GET['dateTo'];
        }

        $sql .= " ORDER BY ll.fecha DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($logs);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error de base de datos al obtener logs: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>