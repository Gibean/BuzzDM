<?php
require_once 'sesion.php'; // Ensure user is logged in
require_once 'db.php';     // Database connection

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'history' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT i.id, i.fecha, i.tipo, i.archivo_nombre, i.estado, i.detalles, u.nombre as usuario_nombre
                  FROM importaciones i
                  LEFT JOIN usuarios u ON i.usuario_id = u.id
                  ORDER BY i.fecha DESC";
        $pdo = getDbConnection();
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $history = [];
        foreach ($results as $row) {
            $totalProcessed = 0;
            $totalInserted = 0;
            $totalUpdated = 0;
            $totalFailed = 0;
            $allErrors = [];

            if (!empty($row['detalles'])) {
                $details = json_decode($row['detalles'], true);
                if (is_array($details)) {
                    foreach ($details as $sheetResult) {
                        $totalProcessed += ($sheetResult['processed'] ?? 0);
                        $totalInserted += ($sheetResult['inserted'] ?? 0);
                        $totalUpdated += ($sheetResult['updated'] ?? 0);
                        $totalFailed += ($sheetResult['failed'] ?? 0);
                        if (!empty($sheetResult['errors'])) {
                            $allErrors = array_merge($allErrors, $sheetResult['errors']);
                        }
                    }
                }
            }

            $history[] = [
                'id' => $row['id'],
                'date' => $row['fecha'],
                'type' => $row['tipo'],
                'filename' => $row['archivo_nombre'],
                'status' => $row['estado'],
                'processed' => $totalProcessed,
                'inserted' => $totalInserted,
                'updated' => $totalUpdated,
                'failed' => $totalFailed,
                'errors_list' => $allErrors,
                'user' => $row['usuario_nombre'] ?? 'Desconocido'
            ];
        }

        $response['success'] = true;
        $response['history'] = $history;

    } catch (Exception $e) {
        $response['message'] = 'Error al obtener el historial de importaciones: ' . $e->getMessage();
        error_log("Error fetching import history: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
?>