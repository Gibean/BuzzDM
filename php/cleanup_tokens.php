<?php
/**
 * Script para limpiar tokens de recuperación expirados o usados
 * Se recomienda ejecutar este script periódicamente mediante cron job
 */

require_once __DIR__ . '/db.php';

try {
    $pdo = getDbConnection();

    // Eliminar tokens expirados o usados
    $stmt = $pdo->prepare("
        DELETE FROM password_reset_tokens
        WHERE expiracion < NOW() OR usado = 1
    ");

    $stmt->execute();
    $deletedCount = $stmt->rowCount();

    // Registrar en logs si existe la tabla
    try {
        $stmt = $pdo->prepare("
            INSERT INTO logs (accion, detalles, fecha)
            VALUES ('Limpieza de tokens', ?, NOW())
        ");
        $stmt->execute(["Tokens eliminados: $deletedCount"]);
    } catch (Exception $e) {
        // Si no existe la tabla de logs, no es crítico
    }

    $message = "Limpieza completada. Tokens eliminados: $deletedCount";
    echo $message . "\n";
    error_log($message);

} catch (Exception $e) {
    $error = "Error en limpieza de tokens: " . $e->getMessage();
    echo $error . "\n";
    error_log($error);
}
?>

