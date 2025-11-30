<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
require_once 'db.php';

header('Content-Type: application/json');

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
        // Pagination parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;
        $offset = ($page - 1) * $limit;

        // Base query for counting total records
        $countSql = "SELECT COUNT(*) as total FROM logs_login ll LEFT JOIN usuarios u ON ll.usuario_id = u.id WHERE 1=1";
        $sql = "SELECT ll.id, u.nombre AS nombre_usuario, ll.accion, ll.detalles, ll.fecha FROM logs_login ll LEFT JOIN usuarios u ON ll.usuario_id = u.id WHERE 1=1";
        $params = [];

        // Apply filters
        if (isset($_GET['search']) && $_GET['search'] !== '') {
            $searchCondition = " AND u.nombre LIKE ?";
            $sql .= $searchCondition;
            $countSql .= $searchCondition;
            $params[] = '%' . $_GET['search'] . '%';
        }
        if (isset($_GET['dateFrom']) && $_GET['dateFrom'] !== '') {
            $dateFromCondition = " AND DATE(ll.fecha) >= ?";
            $sql .= $dateFromCondition;
            $countSql .= $dateFromCondition;
            $params[] = $_GET['dateFrom'];
        }
        if (isset($_GET['dateTo']) && $_GET['dateTo'] !== '') {
            $dateToCondition = " AND DATE(ll.fecha) <= ?";
            $sql .= $dateToCondition;
            $countSql .= $dateToCondition;
            $params[] = $_GET['dateTo'];
        }

        // Get total count
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);

        // Add ordering and pagination
        $sql .= " ORDER BY ll.fecha DESC LIMIT ? OFFSET ?";
        $stmt = $pdo->prepare($sql);

        // Bind all parameters except the last two (limit/offset) which are not in $params array yet
        for ($i = 0; $i < count($params); $i++) {
            $stmt->bindValue($i + 1, $params[$i]);
        }

        $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);

        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return data with pagination metadata
        echo json_encode([
            'data' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'per_page' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Error de base de datos al obtener logs: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
}
?>
