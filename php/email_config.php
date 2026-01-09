<?php
/**
 * BuzzDM - Email Configuration
 *
 * This file pulls email settings from environment variables (.env).
 * Fallbacks are provided for local development.
 */

// Load Composer autoloader if not already loaded
if (!class_exists('Dotenv\Dotenv')) {
    $autoload_path = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    }
}

// Load .env configuration if available
$dotenv_path = __DIR__ . '/../';
if (class_exists('Dotenv\Dotenv') && file_exists($dotenv_path . '.env')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($dotenv_path);
        $dotenv->safeLoad(); // safeLoad won't throw if .env doesn't exist
    } catch (\Dotenv\Exception\InvalidFileException $e) {
        // Error de formato en el archivo .env
        error_log('ERROR: Formato inválido en archivo .env: ' . $e->getMessage());
        error_log('SOLUCIÓN: Verifica que el archivo .env no tenga espacios innecesarios, comillas incorrectas o caracteres especiales.');
        // Continuar con valores por defecto
    } catch (Exception $e) {
        // Otros errores - continuar con defaults
        error_log('WARNING: No se pudo cargar .env: ' . $e->getMessage());
    }
}

// Define email constants from environment (solo si no están definidas)
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
}
if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
}
if (!defined('SMTP_SECURE')) {
    define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
}
if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '');
}
if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '');
}
if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: (defined('SMTP_USERNAME') ? SMTP_USERNAME : ''));
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'BuzzDM');
}
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', $_ENV['APP_BASE_URL'] ?? getenv('APP_BASE_URL') ?: 'http://localhost/BuzzDM');
}

/**
 * Helper function to get mailer configuration as an array
 * Used by forgot_password_action.php and other mailing scripts
 *
 * @return array Mailer configuration
 */
function getMailerConfig() {
    return [
        'host' => SMTP_HOST,
        'port' => SMTP_PORT,
        'secure' => SMTP_SECURE,
        'username' => SMTP_USERNAME,
        'password' => SMTP_PASSWORD,
        'from_email' => MAIL_FROM_EMAIL,
        'from_name' => MAIL_FROM_NAME,
        'base_url' => APP_BASE_URL
    ];
}

/**
 * Helper function to get the base URL of the application
 *
 * @return string The application base URL
 */
function getAppBaseUrl() {
    return rtrim(APP_BASE_URL, '/');
}
