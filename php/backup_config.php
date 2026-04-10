<?php
/**
 * Backup Configuration
 * Settings for automatic backup system
 * PRODUCTION-READY: Uses .env for sensitive data
 */

// Load environment variables — reuse db.php's loading if available,
// otherwise load .env + .env.local in proper order
require_once __DIR__ . '/../vendor/autoload.php';

// Only load env if not yet loaded by db.php
if (!isset($_ENV['DB_HOST']) || empty($_ENV['DB_HOST'])) {
    $envPath = __DIR__ . '/..';
    if (class_exists('Dotenv\Dotenv')) {
        // Load base .env first
        if (file_exists($envPath . '/.env')) {
            $dotenv = Dotenv\Dotenv::createMutable($envPath, '.env');
            $dotenv->safeLoad();
        }
        // Then .env.local overrides (for local development)
        if (file_exists($envPath . '/.env.local')) {
            $dotenvLocal = Dotenv\Dotenv::createMutable($envPath, '.env.local');
            $dotenvLocal->safeLoad();
        }
    }
}

// Secret token for cron/API access - FROM .ENV
if (!defined('BACKUP_SECRET_TOKEN')) {
    define('BACKUP_SECRET_TOKEN', $_ENV['BACKUP_SECRET_TOKEN'] ?? 'INSECURE_DEFAULT_CHANGE_ME');
}

// Interval between automatic backups (in hours)
if (!defined('AUTO_BACKUP_INTERVAL_HOURS')) {
    define('AUTO_BACKUP_INTERVAL_HOURS', 24);
}

// DISABLED for hosting - Use real Cron Jobs instead
if (!defined('BACKUP_FALLBACK_ENABLED')) {
    define('BACKUP_FALLBACK_ENABLED', false);
}

// Maximum number of automatic backups to keep (rotation)
if (!defined('MAX_AUTO_BACKUPS')) {
    define('MAX_AUTO_BACKUPS', 30);
}

// Backup directory (relative to project root)
if (!defined('BACKUP_DIRECTORY')) {
    define('BACKUP_DIRECTORY', __DIR__ . '/../backups/');
}

// Database configuration - FROM .ENV
if (!defined('BACKUP_DB_HOST')) {
    define('BACKUP_DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
}
if (!defined('BACKUP_DB_USER')) {
    define('BACKUP_DB_USER', $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root');
}
if (!defined('BACKUP_DB_PASS')) {
    define('BACKUP_DB_PASS', $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '');
}
if (!defined('BACKUP_DB_NAME')) {
    define('BACKUP_DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'dbuzz');
}

// Path to mysqldump executable
// For Windows/XAMPP: 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
// For Linux hosting: usually just 'mysqldump' (in PATH)
if (!defined('MYSQLDUMP_PATH')) {
    define('MYSQLDUMP_PATH', PHP_OS_FAMILY === 'Windows'
        ? 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
        : 'mysqldump');
}

// Path to mysql executable (same directory as mysqldump)
if (!defined('MYSQL_PATH')) {
    define('MYSQL_PATH', PHP_OS_FAMILY === 'Windows'
        ? dirname(MYSQLDUMP_PATH) . '\\mysql.exe'
        : 'mysql');
}

// Minimum time between fallback backup checks (seconds)
if (!defined('BACKUP_CHECK_COOLDOWN')) {
    define('BACKUP_CHECK_COOLDOWN', 300); // 5 minutes
}

