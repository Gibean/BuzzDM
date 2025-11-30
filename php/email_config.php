<?php
/**
 * Configuración de email para el sistema
 * IMPORTANTE: Actualizar con las credenciales reales del servidor SMTP
 */

// Configuración SMTP
define('SMTP_HOST', 'smtp.gmail.com'); // Cambiar según proveedor
define('SMTP_PORT', 587); // 587 para TLS, 465 para SSL
define('SMTP_SECURE', 'tls'); // 'tls' o 'ssl'
define('SMTP_USERNAME', 'juan.luis9313@gmail.com'); // Email del remitente
define('SMTP_PASSWORD', 'omib pojl rlrs qchv'); // Contraseña o App Password

// Información del remitente
define('MAIL_FROM_EMAIL', 'juan.luis9313@gmail.com');
define('MAIL_FROM_NAME', 'BUZZ Data Manager');

// URL base de la aplicación (sin / al final)
define('APP_BASE_URL', 'http://localhost/BuzzDM');

/**
 * Función para obtener la configuración de PHPMailer
 */
function getMailerConfig() {
    return [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => MAIL_FROM_EMAIL,
        'from_name' => MAIL_FROM_NAME
    ];
}

/**
 * Función para obtener la URL base de la aplicación
 */
function getAppBaseUrl() {
    return APP_BASE_URL;
}
?>

