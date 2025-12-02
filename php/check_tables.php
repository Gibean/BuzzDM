<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();

    // Check the structure of importaciones table
    $stmt = $pdo->query("DESCRIBE importaciones");
    $importacionesColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check the structure of logs table
    $stmt2 = $pdo->query("DESCRIBE logs");
    $logsColumns = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are any records in importaciones
    $stmt3 = $pdo->query("SELECT COUNT(*) as count FROM importaciones");
    $importacionesCount = $stmt3->fetch(PDO::FETCH_ASSOC);

    // Check if there are any records in logs
    $stmt4 = $pdo->query("SELECT COUNT(*) as count FROM logs");
    $logsCount = $stmt4->fetch(PDO::FETCH_ASSOC);

    // Get sample records from importaciones
    $stmt5 = $pdo->query("SELECT * FROM importaciones LIMIT 5");
    $importacionesSamples = $stmt5->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'importaciones_structure' => $importacionesColumns,
        'logs_structure' => $logsColumns,
        'importaciones_count' => $importacionesCount['count'],
        'logs_count' => $logsCount['count'],
        'importaciones_samples' => $importacionesSamples
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
