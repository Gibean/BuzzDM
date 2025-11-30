<?php
/**
 * Script para crear la tabla password_reset_tokens
 */

require_once __DIR__ . '/db.php';

try {
    $pdo = getDbConnection();

    echo "=== Creando Tabla password_reset_tokens ===\n\n";

    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/../db/password_reset_tokens.sql');

    // Ejecutar el SQL
    $pdo->exec($sql);

    echo "✅ Tabla password_reset_tokens creada exitosamente!\n\n";

    // Verificar que la tabla existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Tabla verificada en la base de datos\n";

        // Mostrar estructura de la tabla
        echo "\nEstructura de la tabla:\n";
        echo "========================\n";
        $stmt = $pdo->query("DESCRIBE password_reset_tokens");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo sprintf("%-15s %-20s %s\n",
                $column['Field'],
                $column['Type'],
                $column['Null'] === 'NO' ? 'NOT NULL' : 'NULL'
            );
        }
    }

    echo "\n✅ Sistema de recuperación de contraseña listo!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

