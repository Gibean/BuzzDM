<?php
require_once 'php/db.php';
try {
    $pdo = getDbConnection();

    // 1. Drop the unique constraint on 'fecha' if it exists
    echo "Dropping uk_fecha...\n";
    try {
        $pdo->exec("ALTER TABLE tasas_cambio DROP INDEX uk_fecha");
    } catch (Exception $e) {
        echo "uk_fecha non-existent or already dropped.\n";
    }

    // 2. Add composite unique index (fecha, fuente)
    echo "Adding unique index (fecha, fuente)...\n";
    $pdo->exec("ALTER TABLE tasas_cambio ADD UNIQUE INDEX uk_fecha_fuente (fecha, fuente)");

    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
