<?php
/**
 * Poor Man's Cron - Automatic Backup Check
 *
 * This script checks if an automatic backup is needed and triggers it
 * in a non-blocking way. It's called on every page load for logged-in users.
 */

// Load centralized config for URL generation
require_once __DIR__ . '/config.php';

// Only load config if not already loaded
if (!defined('BACKUP_SECRET_TOKEN')) {
    require_once __DIR__ . '/backup_config.php';
}

/**
 * Check if fallback is enabled in database
 */
function isFallbackEnabled($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = 'backup_fallback_enabled'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['valor'] === '1';
        }
        // Default to true if no setting found
        return true;
    } catch (Exception $e) {
        // If table doesn't exist, default to true
        return true;
    }
}

/**
 * Check if an automatic backup is needed and trigger it if so
 * This function is designed to be fast and non-blocking
 */
function checkAndRunAutoBackup() {
    global $pdo;

    // Make sure we have a database connection
    if (!isset($pdo) || !$pdo) {
        return;
    }

    // Check if fallback is enabled
    if (!isFallbackEnabled($pdo)) {
        return;
    }

    // Only run for admin users to reduce overhead
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
        return;
    }

    // Use a lock file to prevent race conditions
    $lockFile = sys_get_temp_dir() . '/buzzdm_backup_check.lock';

    // Check cooldown using lock file modification time (5 minutes default)
    $cooldown = defined('BACKUP_CHECK_COOLDOWN') ? BACKUP_CHECK_COOLDOWN : 300;

    if (file_exists($lockFile)) {
        $lastCheck = filemtime($lockFile);
        $timeSinceLastCheck = time() - $lastCheck;

        if ($timeSinceLastCheck < $cooldown) {
            return;
        }
    }

    // Update lock file timestamp
    @touch($lockFile);

    try {
        // Check time since last automatic backup
        $stmt = $pdo->prepare("
            SELECT fecha
            FROM backups
            WHERE tipo = 'automatico' AND estado = 'completado'
            ORDER BY fecha DESC
            LIMIT 1
        ");
        $stmt->execute();
        $lastBackup = $stmt->fetch(PDO::FETCH_ASSOC);

        $needsBackup = false;
        $interval = defined('AUTO_BACKUP_INTERVAL_HOURS') ? AUTO_BACKUP_INTERVAL_HOURS : 24;

        if (!$lastBackup) {
            // No automatic backups exist yet
            $needsBackup = true;
            error_log("BUZZ Backup Check: No automatic backups found, triggering first backup");
        } else {
            $lastBackupTime = strtotime($lastBackup['fecha']);
            $hoursSinceBackup = (time() - $lastBackupTime) / 3600;

            if ($hoursSinceBackup >= $interval) {
                $needsBackup = true;
                error_log("BUZZ Backup Check: Last backup was {$hoursSinceBackup}h ago, triggering new backup");
            }
        }

        if ($needsBackup) {
            // Trigger backup in background (non-blocking)
            triggerBackupAsync();
        }

    } catch (Exception $e) {
        // Silently fail - don't break the page for backup issues
        error_log("BUZZ Backup check error: " . $e->getMessage());
    }
}

/**
 * Check if a BCV rate sync is needed and trigger it
 */
function checkAndRunBcvSync() {
    global $pdo;

    if (!isset($pdo) || !$pdo) return;

    // Cooldown check for BCV (e.g., check every 4 hours)
    $lockFile = sys_get_temp_dir() . '/buzzdm_bcv_check.lock';
    $cooldown = 14400; // 4 hours

    if (file_exists($lockFile)) {
        if ((time() - filemtime($lockFile)) < $cooldown) return;
    }

    // Update lock file
    @touch($lockFile);

    try {
        // Check if we already have today's rate
        $stmt = $pdo->query("SELECT id FROM tasas_cambio WHERE fecha = CURDATE() AND fuente = 'bcv' LIMIT 1");
        if (!$stmt->fetch()) {
            // Trigger bcv update in background
            triggerBcvSyncAsync();
        }
    } catch (Exception $e) {
        error_log("BUZZ BCV Sync check error: " . $e->getMessage());
    }
}

/**
 * Trigger the BCV sync script asynchronously
 */
function triggerBcvSyncAsync() {
    $url = app_url('php/tasa_bcv_update.php');

    // Fire and forget
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create(['http' => ['timeout' => 0.5, 'ignore_errors' => true]]);
        @file_get_contents($url, false, $context);
    }
}

/**
 * Trigger the backup script asynchronously
 * Uses different methods depending on server configuration
 */
function triggerBackupAsync() {
    $backupUrl = getBackupUrl();

    error_log("BUZZ Backup: Triggering async backup at URL: " . $backupUrl);

    // Method 1: Use curl with very short timeout (non-blocking)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $backupUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500); // 500ms timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Execute and immediately close - fire and forget
        @curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("BUZZ Backup cURL error (expected for async): " . $error);
        }

        error_log("BUZZ Backup: Fallback triggered via cURL");
        return;
    }

    // Method 2: Use file_get_contents with stream context
    $context = stream_context_create([
        'http' => [
            'timeout' => 0.5,
            'ignore_errors' => true
        ]
    ]);

    @file_get_contents($backupUrl, false, $context);
    error_log("BUZZ Backup: Fallback triggered via file_get_contents");
}

/**
 * Get the full URL to the backup script with token
 */
function getBackupUrl() {
    // Build full URL with token and fallback flag using centralized config
    $token = defined('BACKUP_SECRET_TOKEN') ? BACKUP_SECRET_TOKEN : '';
    $url = app_url('php/auto_backup.php')
         . '?token=' . urlencode($token)
         . '&fallback=1&json=1'
         . '&triggered_by=' . ($_SESSION['usuario_id'] ?? 'unknown');

    return $url;
}
?>
