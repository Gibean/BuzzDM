<?php
/**
 * Create configuraciones table if it doesn't exist
 * Run this script once to set up the configuration table
 */

require_once __DIR__ . '/db.php';

echo "<h2>Creando tabla configuraciones...</h2>";

try {
    $pdo = getDbConnection();

    // Create table
    $sql = "
        CREATE TABLE IF NOT EXISTS `configuraciones` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `clave` VARCHAR(100) NOT NULL UNIQUE,
            `valor` TEXT DEFAULT NULL,
            `descripcion` VARCHAR(255) DEFAULT NULL,
            `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✓ Tabla 'configuraciones' creada exitosamente.</p>";

    // Insert default values
    $stmt = $pdo->prepare("
        INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`)
        VALUES ('backup_fallback_enabled', '1', 'Habilitar sistema de backup automático fallback')
        ON DUPLICATE KEY UPDATE `clave` = `clave`
    ");
    $stmt->execute();
    echo "<p style='color: green;'>✓ Configuración por defecto insertada.</p>";

    echo "<br><p><strong>¡Listo!</strong> Ahora puedes volver a la <a href='../admin.php'>página de administración</a>.</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
