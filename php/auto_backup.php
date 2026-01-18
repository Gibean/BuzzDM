<?php
/**
 * Automatic Backup Script (Refactored)
 * Uses BackupService to perform backups.
 */

// If running from CLI, we might not have $_SERVER setup, so manually define basic context if needed.
// sesion.php might try to start session.
// We should include Service directly and bypassing sesion.php if CLI?
// But Service uses Repository which uses getDbConnection which uses db.php.
// Simple way: Include Service and manual config.

if (php_sapi_name() === 'cli') {
    // Defines for CLI
}

require_once __DIR__ . '/backup_config.php';
require_once __DIR__ . '/src/Service/BackupService.php';

// Auth logic for HTTP
if (php_sapi_name() !== 'cli') {
    $providedToken = $_GET['token'] ?? '';
    if ($providedToken !== BACKUP_SECRET_TOKEN) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }
    header('Content-Type: application/json');
}

$isFallback = isset($_GET['fallback']) && $_GET['fallback'] === '1';
$triggeredBy = $_GET['triggered_by'] ?? null;

$service = new BackupService();

try {
    $result = $service->performBackup('automatico', $triggeredBy, $isFallback);

    if (php_sapi_name() !== 'cli') {
        echo json_encode($result);
    } else {
        echo "Backup OK: " . $result['backup']['archivo'] . "\n";
    }
} catch (Exception $e) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } else {
         echo "Error: " . $e->getMessage() . "\n";
         exit(1);
    }
}
