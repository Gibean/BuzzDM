<?php
/**
 * Cronjob de Limpieza Automatizada para Entorno SaaS Demo en Hostinger
 * php/cron/demo_cleanup.php
 * 
 * Programación recomendada en hPanel/cPanel:
 * 0 0 * * * /usr/bin/php /home/uXXXXXX/public_html/php/cron/demo_cleanup.php >/dev/null 2>&1
 */

if (php_sapi_name() !== 'cli' && (!isset($_GET['secret']) || $_GET['secret'] !== getenv('CRON_SECRET_KEY'))) {
    http_response_code(403);
    die("Acceso denegado: Este script solo puede ejecutarse por CLI o con clave autorizada.\n");
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../src/Service/DemoExportService.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

echo "[" . date('Y-m-d H:i:s') . "] Iniciando Cronjob de Limpieza Demo SaaS...\n";

try {
    $pdo = getDbConnection();
    $exportService = new DemoExportService($pdo);

    // 1. Marcar como 'expired' las empresas cuyo período de 15 días ya terminó
    $stmtExpire = $pdo->prepare("
        UPDATE empresa 
        SET status = 'expired' 
        WHERE is_demo = 1 
          AND status = 'active' 
          AND trial_ends_at < NOW()
    ");
    $stmtExpire->execute();
    $expiredNow = $stmtExpire->rowCount();
    echo "Empresas pasadas a estado 'expired': {$expiredNow}\n";

    // 2. Buscar empresas que expiraron hace más de 3 días (fin del período de gracia)
    // Procesar en lotes pequeños (LIMIT 10) para evitar timeouts en Hostinger
    $stmtPurge = $pdo->prepare("
        SELECT id, nombre, admin_email, trial_ends_at 
        FROM empresa 
        WHERE is_demo = 1 
          AND status = 'expired' 
          AND trial_ends_at < DATE_SUB(NOW(), INTERVAL 3 DAY)
        ORDER BY id ASC
        LIMIT 10
    ");
    $stmtPurge->execute();
    $companiesToPurge = $stmtPurge->fetchAll(PDO::FETCH_ASSOC);

    echo "Empresas elegibles para eliminación definitiva (Hard Delete): " . count($companiesToPurge) . "\n";

    foreach ($companiesToPurge as $company) {
        $companyId = (int)$company['id'];
        $email = $company['admin_email'];
        $name = $company['nombre'];

        echo " -> Procesando empresa ID {$companyId} ({$name})...\n";

        $tempFile = null;
        try {
            // A. Generar archivo Excel de respaldo final
            $tempFile = $exportService->generateCompanyDataExport($companyId);

            // B. Enviar correo de despedida con respaldo y encuesta
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                sendGoodbyeMail($email, $name, $tempFile);
            }
        } catch (Exception $e) {
            echo "    [Aviso] No se pudo enviar el correo de respaldo: " . $e->getMessage() . "\n";
        }

        // C. Eliminar carpeta física de subidas de la empresa
        $companyUploadDir = __DIR__ . "/../../assets/img/uploads/companies/{$companyId}/";
        deleteDirectoryRecursively($companyUploadDir);

        // D. Eliminar la empresa de la base de datos (ON DELETE CASCADE limpiará todos sus registros)
        $delStmt = $pdo->prepare("DELETE FROM empresa WHERE id = :id");
        $delStmt->execute([':id' => $companyId]);

        echo "    Empresa ID {$companyId} y sus datos asociados eliminados correctamente.\n";

        // Limpiar archivo temporal si existe
        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Limpieza completada con éxito.\n";

} catch (Exception $e) {
    echo "[ERROR CRON] " . $e->getMessage() . "\n";
}

/**
 * Función auxiliar para borrar un directorio y su contenido de forma recursiva
 */
function deleteDirectoryRecursively($dir): bool {
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = "$dir/$file";
        is_dir($path) ? deleteDirectoryRecursively($path) : @unlink($path);
    }
    return @rmdir($dir);
}

/**
 * Envía el correo final con el adjunto Excel y la encuesta
 */
function sendGoodbyeMail(string $toEmail, string $companyName, string $attachmentPath): void {
    require_once __DIR__ . '/../email_config.php'; // Usa configuración SMTP centralizada si existe
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
        $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['SMTP_PORT'] ?? 587);

        $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@buzzdm.com', 'BuzzDM');
        $mail->addAddress($toEmail, $companyName);

        if (file_exists($attachmentPath)) {
            $mail->addAttachment($attachmentPath, "Respaldo_BuzzDM_{$companyName}.xlsx");
        }

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Respaldo final de datos - Período de prueba BuzzDM ({$companyName})";
        $mail->Body    = "
            <h3>Hola, {$companyName}</h3>
            <p>Tu período de prueba y los días de gracia han finalizado. Como parte de nuestro compromiso con tus datos, adjuntamos este correo con un archivo Excel consolidado de tus registros (productos, clientes y proveedores).</p>
            <p>Para ayudarnos a mejorar este proyecto, te agradeceríamos enormemente si respondes una breve encuesta de 2 minutos:</p>
            <p><a href='https://forms.gle/' style='background:#f59e0b;color:#000;padding:8px 16px;text-decoration:none;border-radius:4px;font-weight:bold;'>Llenar Encuesta de Feedback</a></p>
            <p>Atentamente,<br>El equipo de BuzzDM</p>
        ";

        $mail->send();
    } catch (MailException $e) {
        error_log("Fallo al enviar correo en demo_cleanup: " . $mail->ErrorInfo);
    }
}
