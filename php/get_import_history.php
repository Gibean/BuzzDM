<?php
require_once 'sesion.php'; // Ensure user is logged in
require_once 'db.php';     // Database connection

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'history' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query both the importaciones table and logs table for complete history
        // First get from importaciones table (main source)
        $queryImportaciones = "SELECT i.id, i.fecha as fecha,
                                      CONCAT('Importación: ', i.tipo) as accion,
                                      CONCAT(i.archivo_nombre, ' - Estado: ', i.estado) as detalles,
                                      u.nombre as usuario_nombre
                                FROM importaciones i
                                LEFT JOIN usuarios u ON i.usuario_id = u.id
                                ORDER BY i.fecha DESC";

        $stmtImportaciones = $pdo->prepare($queryImportaciones);
        $stmtImportaciones->execute();
        $resultsImportaciones = $stmtImportaciones->fetchAll(PDO::FETCH_ASSOC);

        // Also get from logs table for import-related actions
        $queryLogs = "SELECT l.id, l.fecha, l.accion, l.detalles, u.nombre as usuario_nombre
                      FROM logs l
                      LEFT JOIN usuarios u ON l.usuario_id = u.id
                      WHERE l.accion LIKE '%Importación%' OR l.accion LIKE '%import%' OR l.accion LIKE '%Backup%'
                      ORDER BY l.fecha DESC";

        $stmtLogs = $pdo->prepare($queryLogs);
        $stmtLogs->execute();
        $resultsLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        // Combine both results
        $allResults = array_merge($resultsImportaciones, $resultsLogs);

        // Sort by date descending
        usort($allResults, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        // Limit to last 50
        $allResults = array_slice($allResults, 0, 50);

        $response['success'] = true;
        $response['history'] = $allResults;

    } catch (Exception $e) {
        $response['message'] = 'Error al obtener el historial: ' . $e->getMessage();
        error_log("Error fetching history: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
?>
