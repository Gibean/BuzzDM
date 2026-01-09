<?php
/**
 * Backend para procesar solicitudes de recuperación de contraseña
 */

// Iniciar output buffering para evitar que se envíe HTML antes del JSON
ob_start();

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1); // Sí guardar en log

// Manejador de errores fatal para siempre devolver JSON
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error interno del servidor. Revisa los logs para más detalles.',
            'debug' => [
                'error' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line']
            ]
        ]);
        exit;
    }
});

// Manejador de excepciones no capturadas
set_exception_handler(function($e) {
    ob_end_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error no controlado: ' . $e->getMessage()
    ]);
    error_log('Excepción no capturada: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    exit;
});

// Incluir dependencias (db.php ya carga vendor/autoload.php)
require_once 'db.php';
require_once 'email_config.php';

// Asegurarse de que PHPMailer esté disponible
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $vendor_path = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($vendor_path)) {
        require_once $vendor_path;
    } else {
        ob_end_clean();
        error_log('ERROR: vendor/autoload.php no encontrado. Ejecuta: composer install');
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Sistema no configurado correctamente. Contacta al administrador.'
        ]);
        exit;
    }
}

// Establecer header después de cargar dependencias
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new \Exception('Método no permitido');
    }

    // Obtener el email del usuario
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new \Exception('Por favor, proporciona un correo electrónico válido');
    }

    // Verificar configuración de correo
    if (empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
        error_log('ERROR: Credenciales SMTP no configuradas en .env');
        throw new \Exception('El sistema de recuperación de contraseña no está configurado. Contacta al administrador.');
    }

    // Conectar a la base de datos
    $pdo = getDbConnection();

    // Buscar el usuario por email
    $stmt = $pdo->prepare("SELECT id, nombre, email, estado FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Por seguridad, siempre mostramos el mismo mensaje aunque el usuario no exista
    // Esto evita que alguien pueda verificar qué emails están registrados
    if (!$usuario) {
        // Esperar un poco para simular el procesamiento
        usleep(500000); // 0.5 segundos

        ob_end_clean(); // Limpiar buffer
        echo json_encode([
            'success' => true,
            'message' => 'Si el correo electrónico está registrado, recibirás un enlace de recuperación en unos minutos.'
        ]);
        exit;
    }

    // Verificar que el usuario no esté bloqueado
    if ($usuario['estado'] === 'bloqueado') {
        throw new \Exception('Esta cuenta está bloqueada. Contacta al administrador.');
    }

    if ($usuario['estado'] === 'inactivo') {
        throw new \Exception('Esta cuenta está inactiva. Contacta al administrador.');
    }

    // Generar token único y seguro
    $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales

    // Establecer tiempo de expiración (1 hora desde ahora)
    $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Invalidar tokens anteriores del usuario que no hayan sido usados
    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE usuario_id = ? AND usado = 0");
    $stmt->execute([$usuario['id']]);

    // Insertar el nuevo token en la base de datos
    $stmt = $pdo->prepare(
        "INSERT INTO password_reset_tokens (usuario_id, token, expiracion) VALUES (?, ?, ?)"
    );
    $stmt->execute([$usuario['id'], $token, $expiracion]);

    // Crear el enlace de recuperación
    $resetLink = getAppBaseUrl() . '/reset_password.php?token=' . $token;

    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    $config = getMailerConfig();

    try {
        // Configuración del servidor SMTP
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
        $mail->addAddress($usuario['email'], $usuario['nombre']);

        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - BUZZ Data Manager';

        // Cuerpo del email en HTML
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #1a1f28 0%, #2a3040 100%); color: #FFA000; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; padding: 15px 30px; background: #FFA000; color: #1a1f28 !important; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .button:hover { background: #FF8F00; }
                .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Recuperación de Contraseña</h1>
                </div>
                <div class='content'>
                    <p>Hola <strong>{$usuario['nombre']}</strong>,</p>

                    <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en <strong>BUZZ Data Manager</strong>.</p>

                    <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>

                    <div style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Restablecer Contraseña</a>
                    </div>

                    <p>Si el botón no funciona, copia y pega el siguiente enlace en tu navegador:</p>
                    <p style='word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px;'><a href='{$resetLink}'>{$resetLink}</a></p>

                    <div class='warning'>
                        <strong>⚠️ Importante:</strong>
                        <ul>
                            <li>Este enlace es válido por <strong>1 hora</strong></li>
                            <li>Solo puede ser usado una vez</li>
                            <li>Si no solicitaste este cambio, ignora este correo</li>
                        </ul>
                    </div>

                    <p>Si tienes algún problema, contacta al administrador del sistema.</p>

                    <p>Saludos,<br><strong>El equipo de BUZZ Data Manager</strong></p>
                </div>
                <div class='footer'>
                    <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                    <p>&copy; " . date('Y') . " BUZZ Data Manager. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Versión de texto plano
        $mail->AltBody = "
Hola {$usuario['nombre']},

Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en BUZZ Data Manager.

Para crear una nueva contraseña, visita el siguiente enlace:
{$resetLink}

IMPORTANTE:
- Este enlace es válido por 1 hora
- Solo puede ser usado una vez
- Si no solicitaste este cambio, ignora este correo

Saludos,
El equipo de BUZZ Data Manager

---
Este es un correo automático, por favor no respondas a este mensaje.
        ";

        // Enviar el correo
        $mail->send();

        // Limpiar buffer y enviar respuesta
        ob_end_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Si el correo electrónico está registrado, recibirás un enlace de recuperación en unos minutos.'
        ]);

    } catch (PHPMailerException $e) {
        // Log del error (en producción, usar un sistema de logs apropiado)
        error_log("Error al enviar correo de recuperación: " . $mail->ErrorInfo);

        // No revelamos detalles del error al usuario
        throw new \Exception('Hubo un problema al enviar el correo. Por favor, intenta nuevamente más tarde.');
    }

} catch (\PDOException $e) {
    // Error de base de datos
    ob_end_clean(); // Limpiar buffer
    error_log('ERROR DB en forgot_password: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos. Verifica que la tabla password_reset_tokens exista.'
    ]);
} catch (\Exception $e) {
    // Log del error completo para debugging
    ob_end_clean(); // Limpiar buffer
    error_log('ERROR en forgot_password: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
