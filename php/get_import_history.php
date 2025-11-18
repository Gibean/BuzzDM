<?php
require_once 'sesion.php'; // Ensure user is logged in
require_once 'db.php';     // Database connection

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'history' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query the logs table for import and backup actions
        $query = "SELECT l.id, l.fecha, l.accion, l.detalles, u.nombre as usuario_nombre
                  FROM logs l
                  LEFT JOIN usuarios u ON l.usuario_id = u.id
                  WHERE l.accion LIKE 'Importación%' OR l.accion LIKE 'Backup%'
                  ORDER BY l.fecha DESC
                  LIMIT 50"; // Limit to the last 50 entries

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['history'] = $results;

    } catch (Exception $e) {
        $response['message'] = 'Error al obtener el historial: ' . $e->getMessage();
        error_log("Error fetching history: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
?>