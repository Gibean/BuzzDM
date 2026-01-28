<?php
/**
 * BuzzDM - Email Configuration
 *
 * This file pulls email settings from environment variables (.env).
 * Fallbacks are provided for local development.
 */

// Define email constants from environment
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '');
define('MAIL_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: SMTP_USERNAME);
define('MAIL_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'BuzzDM');

// Dynamic BASE_URL detection
$envBaseUrl = $_ENV['APP_BASE_URL'] ?? getenv('APP_BASE_URL') ?: '';

if (empty($envBaseUrl) && isset($_SERVER['HTTP_HOST'])) {
    // Dynamic detection based on server variables
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];

    // Get project directory path from current script
    // This detects "/BuzzDM" or wherever the project is located
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

    // Remove trailing subdirectories if we're in /php or /php/src/...
    // We want the root project folder, not the current script's folder
    if (preg_match('#^(.*?/BuzzDM)#i', $scriptDir, $matches)) {
        $scriptDir = $matches[1];
    } elseif (preg_match('#^(.*/[^/]+)(?:/php.*)?$#', $scriptDir, $matches)) {
        // Generic fallback: find root project folder
        $scriptDir = $matches[1];
    }

    $scriptDir = ($scriptDir === '/' || $scriptDir === '.') ? '' : $scriptDir;
    $envBaseUrl = $protocol . "://" . $host . $scriptDir;
}

define('APP_BASE_URL', rtrim($envBaseUrl ?: 'http://localhost/BuzzDM', '/'));

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
