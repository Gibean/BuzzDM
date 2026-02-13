<?php
/**
 * BuzzDM - Email Configuration
 *
 * This file pulls email settings from environment variables (.env).
 * Fallbacks are provided for local development.
 * Uses centralized config.php for URL detection.
 */

// Load centralized configuration
require_once __DIR__ . '/config.php';

// Define email constants from environment
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '');
define('MAIL_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: SMTP_USERNAME);
define('MAIL_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? getenv('SMTP_FROM_NAME') ?: 'BuzzDM');

// Use centralized config for BASE_URL
define('APP_URL', base_url());

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
        'base_url' => APP_URL
    ];
}

/**
 * Helper function to get the base URL of the application
 *
 * @return string The application base URL
 */
function getAppBaseUrl() {
    return rtrim(APP_URL, '/');
}
