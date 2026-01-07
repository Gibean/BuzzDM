<?php
/**
 * BuzzDM - Mantenimiento del Sistema
 *
 * Limpia automáticamente registros antiguos de la base de datos:
 * - Tokens de recuperación expirados o usados
 * - Sesiones de API expiradas (+30 días)
 * - Logs de inicio de sesión antiguos (+90 días)
 * - Logs generales antiguos (+180 días)
 */

require_once __DIR__ . '/db.php';

try {
    $pdo = getDbConnection();
    $stats = [];

    // 1. Limpiar tokens de recuperación
    $stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE expiracion < NOW() OR usado = 1");
    $stmt->execute();
    $stats['tokens_password'] = $stmt->rowCount();

    // 2. Limpiar sesiones API expiradas (más de 30 días)
    // Asumimos que la tabla sesiones_api tiene un campo 'expiracion' o 'actualizado_at'
    try {
        $stmt = $pdo->prepare("DELETE FROM sesiones_api WHERE expiracion < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stmt->execute();
        $stats['sesiones_api'] = $stmt->rowCount();
    } catch (Exception $e) {
        $stats['sesiones_api'] = "Error: " . $e->getMessage();
    }

    // 3. Limpiar logs de login antiguos (más de 90 días)
    try {
        $stmt = $pdo->prepare("DELETE FROM logs_login WHERE fecha < DATE_SUB(NOW(), INTERVAL 90 DAY)");
        $stmt->execute();
        $stats['logs_login'] = $stmt->rowCount();
    } catch (Exception $e) {
        $stats['logs_login'] = "Error: " . $e->getMessage();
    }

    // 4. Limpiar logs generales antiguos (más de 180 días)
    try {
        $stmt = $pdo->prepare("DELETE FROM logs WHERE fecha < DATE_SUB(NOW(), INTERVAL 180 DAY)");
        $stmt->execute();
        $stats['logs_generales'] = $stmt->rowCount();
    } catch (Exception $e) {
        $stats['logs_generales'] = "Error: " . $e->getMessage();
    }

    // Registrar acción en el log principal
    $detalles = json_encode($stats);
    $stmt = $pdo->prepare("INSERT INTO logs (accion, detalles, fecha) VALUES ('Limpieza del Sistema', ?, NOW())");
    $stmt->execute([$detalles]);

    // Salida (para ejecución manual o CRON)
    echo "¡Limpieza completada con éxito!\n";
    foreach ($stats as $key => $val) {
        echo "- $key: $val\n";
    }

} catch (Exception $e) {
    error_log("Error en mantenimiento del sistema: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . "\n";
}
