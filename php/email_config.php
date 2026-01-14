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

// APP_BASE_URL: Use environment variable if set, otherwise auto-detect from request
$envAppBaseUrl = $_ENV['APP_BASE_URL'] ?? getenv('APP_BASE_URL');
if ($envAppBaseUrl && $envAppBaseUrl !== '') {
    define('APP_BASE_URL', $envAppBaseUrl);
} else {
    // Auto-detect base URL from current request
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Determine the base path
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = '';

    // Extract the base directory from the script path (e.g., /BuzzDM from /BuzzDM/php/forgot_password_action.php)
    if (preg_match('#^(/[^/]+)(?:/|$)#', $scriptName, $matches)) {
        $basePath = $matches[1];
    }

    // For hostinger or similar hosts where the app is at the root, don't add a base path
    if (strpos($host, 'hostingersite.com') !== false || strpos($host, 'hostinger') !== false) {
        $basePath = '';
    }

    define('APP_BASE_URL', $protocol . '://' . $host . $basePath);
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
