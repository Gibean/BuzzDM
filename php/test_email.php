<?php
/**
 * Script de prueba para verificar el envío de correos
 * Ejecuta este script para probar que tu configuración SMTP funcione correctamente
 */

require_once __DIR__ . '/email_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "=== Prueba de Envío de Correo - BUZZ Data Manager ===\n\n";

// Email de destino para la prueba
$emailDestino = readline("Ingresa el email de destino para la prueba: ");

if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
    echo "Error: Email inválido\n";
    exit(1);
}

echo "\nConfigurando PHPMailer...\n";

$mail = new PHPMailer(true);
$config = getMailerConfig();

try {
    // Habilitar modo debug
    $mail->SMTPDebug = 2; // 0 = sin debug, 1 = mensajes del cliente, 2 = mensajes cliente + servidor
    $mail->Debugoutput = 'echo';

    // Configuración del servidor SMTP
    echo "\nConfiguración SMTP:\n";
    echo "Host: " . $config['host'] . "\n";
    echo "Port: " . $config['port'] . "\n";
    echo "Secure: " . $config['secure'] . "\n";
    echo "Username: " . $config['username'] . "\n";
    echo "From: " . $config['from_email'] . "\n\n";

    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['secure'];
    $mail->Port = $config['port'];
    $mail->CharSet = 'UTF-8';

    // Remitente y destinatario
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($emailDestino, 'Usuario de Prueba');

    // Contenido del email
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de Correo - BUZZ Data Manager';

    $mail->Body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1a1f28 0%, #2a3040 100%); color: #FFA000; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ Prueba de Correo Exitosa</h1>
            </div>
            <div class='content'>
                <p>¡Hola!</p>

                <p>Este es un correo de prueba del sistema <strong>BUZZ Data Manager</strong>.</p>

                <p>Si estás leyendo este mensaje, significa que:</p>
                <ul>
                    <li>✓ La configuración SMTP es correcta</li>
                    <li>✓ PHPMailer está funcionando correctamente</li>
                    <li>✓ El sistema de recuperación de contraseña está listo para usar</li>
                </ul>

                <p><strong>Fecha y hora:</strong> " . date('d/m/Y H:i:s') . "</p>

                <p>Saludos,<br><strong>El equipo de BUZZ Data Manager</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->AltBody = "
    ✅ Prueba de Correo Exitosa

    ¡Hola!

    Este es un correo de prueba del sistema BUZZ Data Manager.

    Si estás leyendo este mensaje, significa que la configuración SMTP es correcta.

    Fecha y hora: " . date('d/m/Y H:i:s') . "

    Saludos,
    El equipo de BUZZ Data Manager
    ";

    echo "\nEnviando correo...\n";
    $mail->send();

    echo "\n✅ ¡Correo enviado exitosamente!\n";
    echo "Revisa la bandeja de entrada de $emailDestino\n";
    echo "(No olvides revisar la carpeta de spam)\n";

} catch (Exception $e) {
    echo "\n❌ Error al enviar el correo\n";
    echo "Mensaje de error: {$mail->ErrorInfo}\n";
    echo "\nPosibles soluciones:\n";
    echo "1. Verifica que las credenciales SMTP en email_config.php sean correctas\n";
    echo "2. Para Gmail, asegúrate de usar un App Password, no tu contraseña normal\n";
    echo "3. Verifica que tu firewall no bloquee el puerto " . $config['port'] . "\n";
    echo "4. Asegúrate de tener la extensión OpenSSL habilitada en PHP\n";
    exit(1);
}
?>

