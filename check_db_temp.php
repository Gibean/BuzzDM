<?php
require_once 'php/db.php';
try {
    $pdo = getDbConnection();

    echo "--- TABLE: tasas_cambio ---\n";
    $stmt = $pdo->query("DESCRIBE tasas_cambio");
    while ($row = $stmt->fetch()) {
        echo "Field: {$row['Field']} | Type: {$row['Type']} | Key: {$row['Key']}\n";
    }

    echo "\n--- INDEXES ---\n";
    $stmt = $pdo->query("SHOW INDEX FROM tasas_cambio");
    while ($row = $stmt->fetch()) {
        echo "Table: {$row['Table']} | Non_unique: {$row['Non_unique']} | Key_name: {$row['Key_name']} | Column_name: {$row['Column_name']}\n";
    }

    echo "\n--- SAMPLE DATA ---\n";
    $stmt = $pdo->query("SELECT * FROM tasas_cambio ORDER BY fecha DESC, creado_at DESC LIMIT 5");
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
