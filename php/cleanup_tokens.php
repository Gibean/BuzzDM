<?php
/**
 * Script para limpiar tokens de recuperación expirados o usados
 * Se recomienda ejecutar este script periódicamente mediante cron job
 *
 * Programación cron:
 * 0 * * * * /usr/bin/php /home/uXXXXXX/public_html/php/cleanup_tokens.php >/dev/null 2>&1
 */

// Antes este script era alcanzable por HTTP sin autenticación: el .htaccess raíz
// no restringe php/*.php, así que cualquiera podía disparar el DELETE de
// tokens y provocar la creación de empresas de prueba. Se cierra igual que
// php/cron/demo_cleanup.php: sólo CLI, o por HTTP con la clave del cron.
if (php_sapi_name() !== 'cli' && (!isset($_GET['secret']) || $_GET['secret'] !== getenv('CRON_SECRET_KEY'))) {
    http_response_code(403);
    die("Acceso denegado: este script solo puede ejecutarse por CLI o con clave autorizada.\n");
}

require_once __DIR__ . '/config.php';
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

    // Registrar en logs.
    //
    // `logs.empresa_id` es NOT NULL y sin DEFAULT: este INSERT omitía la columna
    // y reventaba con error 1364, y el catch de dentro se tragaba la excepción,
    // de modo que el script anunciaba "Limpieza completada" sin registrar nada.
    //
    // La limpieza de tokens es una tarea de la instalación, no de un tenant:
    // password_reset_tokens es global. Por eso se atribuye a la empresa
    // propietaria de la instalación, igual que hace BackupRepository con los
    // respaldos, y no a un DEFAULT que escondería a quién pertenece la fila.
    $empresaId = $pdo->query("SELECT MIN(id) FROM empresa")->fetchColumn();

    if ($empresaId !== null && (int)$empresaId > 0) {
        try {
            $logStmt = $pdo->prepare("
                INSERT INTO logs (empresa_id, usuario_id, accion, detalles, fecha)
                VALUES (?, NULL, 'Limpieza de tokens', ?, NOW())
            ");
            $logStmt->execute([(int)$empresaId, "Tokens eliminados: $deletedCount"]);
        } catch (Exception $e) {
            // La bitácora no debe impedir la limpieza, que es lo importante.
            error_log('No se pudo registrar la limpieza de tokens: ' . $e->getMessage());
        }
    } else {
        error_log('No se pudo registrar la limpieza de tokens: no hay empresas en la tabla `empresa`.');
    }

    $message = "Limpieza completada. Tokens eliminados: $deletedCount";
    echo $message . "\n";
    error_log($message);

} catch (Exception $e) {
    $error = "Error en limpieza de tokens: " . $e->getMessage();
    echo $error . "\n";
    error_log($error);
}
