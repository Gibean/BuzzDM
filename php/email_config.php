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

// URL del dominio de producción (sin / al final)
define('PRODUCTION_DOMAIN', 'darkslategrey-duck-561917.hostingersite.com');

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
 * Detecta automáticamente si está en localhost o producción
 */
function getAppBaseUrl() {
    // Detectar el protocolo (http o https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443))
                 ? 'https://' : 'http://';

    // Obtener el host
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

    // Verificar si estamos en localhost
    $isLocalhost = (
        strpos($host, 'localhost') !== false ||
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, '::1') !== false
    );

    if ($isLocalhost) {
        // Entorno local - detectar el path del proyecto
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = '';

        // Extraer el path base (ej: /BuzzDM)
        if (preg_match('/^(\/[^\/]+)/', $scriptName, $matches)) {
            $basePath = $matches[1];
        }

        return $protocol . $host . $basePath;
    } else {
        // Entorno de producción - usar dominio configurado
        // Determinar protocolo en producción (preferir https)
        $productionProtocol = 'https://';

        return $productionProtocol . PRODUCTION_DOMAIN;
    }
}
?>

