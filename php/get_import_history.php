<?php
require_once 'sesion.php'; // Ensure user is logged in
require_once 'db.php';     // Database connection

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'history' => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Query the importaciones table
        $query = "SELECT i.id, i.fecha, i.tipo, i.archivo_nombre, i.estado, i.detalles, u.nombre as usuario_nombre
                  FROM importaciones i
                  LEFT JOIN usuarios u ON i.usuario_id = u.id
                  ORDER BY i.fecha DESC
                  LIMIT 50";

        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $history = [];
        foreach ($results as $row) {
            $records = 0;
            $errors = 0;

            // Parse details JSON to get stats
            $details = json_decode($row['detalles'], true);
            if (is_array($details) && isset($details['results'])) {
                foreach ($details['results'] as $sheetResult) {
                    $records += ($sheetResult['inserted'] ?? 0) + ($sheetResult['updated'] ?? 0);
                    $errors += ($sheetResult['failed'] ?? 0);
                }
            }

            $history[] = [
                'id' => $row['id'],
                'date' => $row['fecha'],
                'type' => $row['tipo'],
                'filename' => $row['archivo_nombre'],
                'status' => $row['estado'],
                'records' => $records,
                'errors' => $errors,
                'user' => $row['usuario_nombre'] ?? 'Desconocido',
                'details' => $details // Keep full details if needed for view details
            ];
        }

        $response['success'] = true;
        $response['history'] = $history;

    } catch (Exception $e) {
        $response['message'] = 'Error al obtener el historial: ' . $e->getMessage();
        error_log("Error fetching history: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
?>
