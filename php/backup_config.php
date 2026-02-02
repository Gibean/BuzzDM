<?php
/**
 * Backup Configuration
 * Settings for automatic backup system
 * PRODUCTION-READY: Uses .env for sensitive data
 */

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

// Secret token for cron/API access - FROM .ENV
define('BACKUP_SECRET_TOKEN', $_ENV['BACKUP_SECRET_TOKEN'] ?? 'INSECURE_DEFAULT_CHANGE_ME');

// Interval between automatic backups (in hours)
define('AUTO_BACKUP_INTERVAL_HOURS', 24);

// DISABLED for hosting - Use real Cron Jobs instead
define('BACKUP_FALLBACK_ENABLED', false);

// Maximum number of automatic backups to keep (rotation)
define('MAX_AUTO_BACKUPS', 30);

// Backup directory (relative to project root)
define('BACKUP_DIRECTORY', __DIR__ . '/../backups/');

// Database configuration - FROM .ENV
define('BACKUP_DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('BACKUP_DB_USER', $_ENV['DB_USER'] ?? 'root');
define('BACKUP_DB_PASS', $_ENV['DB_PASS'] ?? '');
define('BACKUP_DB_NAME', $_ENV['DB_NAME'] ?? 'dbuzz');

// Path to mysqldump executable
// For Windows/XAMPP: 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
// For Linux hosting: usually just 'mysqldump' (in PATH)
define('MYSQLDUMP_PATH', PHP_OS_FAMILY === 'Windows'
    ? 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
    : 'mysqldump');

// Minimum time between fallback backup checks (seconds)
define('BACKUP_CHECK_COOLDOWN', 300); // 5 minutes
