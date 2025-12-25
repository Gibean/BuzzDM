<?php
/**
 * Backup Configuration
 * Settings for automatic backup system
 */

// Secret token for cron/API access - CHANGE THIS IN PRODUCTION!
define('BACKUP_SECRET_TOKEN', 'buzz_backup_' . md5('BuzzDM_AutoBackup_2024'));

// Interval between automatic backups (in hours)
define('AUTO_BACKUP_INTERVAL_HOURS', 24);

// Enable/disable fallback "Poor Man's Cron" system
define('BACKUP_FALLBACK_ENABLED', true);

// Maximum number of automatic backups to keep (rotation)
define('MAX_AUTO_BACKUPS', 30);

// Backup directory (relative to project root)
define('BACKUP_DIRECTORY', __DIR__ . '/../backups/');

// Database configuration for backups
// These should match your db.php settings
define('BACKUP_DB_HOST', 'localhost');
define('BACKUP_DB_USER', 'root');
define('BACKUP_DB_PASS', '35128');
define('BACKUP_DB_NAME', 'dbuzz');

// Path to mysqldump executable
// For Windows/XAMPP: 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
// For Linux hosting: usually just 'mysqldump' (in PATH)
define('MYSQLDUMP_PATH', PHP_OS_FAMILY === 'Windows'
    ? 'c:\\xampp\\mysql\\bin\\mysqldump.exe'
    : 'mysqldump');

// Minimum time between fallback backup checks (seconds)
// Prevents multiple backups if many users load pages simultaneously
define('BACKUP_CHECK_COOLDOWN', 300); // 5 minutes
?>
