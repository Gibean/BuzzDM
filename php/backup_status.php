<?php
/**
 * Auto-Backup Status Check API
 * Returns the health status of automatic backups
 * Also handles toggle action for enabling/disabling fallback
 */

require_once 'sesion.php';
require_once 'db.php';
require_once 'backup_config.php';

header('Content-Type: application/json');

// Verify admin access
if ($_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado']);
    exit;
}

// Handle toggle action
$action = $_GET['action'] ?? '';

if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the request body
    $input = json_decode(file_get_contents('php://input'), true);
    $enabled = $input['enabled'] ?? true;

    // For now, we store the setting in the database
    // This is a simple approach - you could also use a config table
    try {
        $pdo = getDbConnection();

        // Check if setting exists
        $stmt = $pdo->prepare("SELECT id FROM configuraciones WHERE clave = 'backup_fallback_enabled'");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $pdo->prepare("UPDATE configuraciones SET valor = ? WHERE clave = 'backup_fallback_enabled'");
            $stmt->execute([$enabled ? '1' : '0']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO configuraciones (clave, valor) VALUES ('backup_fallback_enabled', ?)");
            $stmt->execute([$enabled ? '1' : '0']);
        }

        echo json_encode(['success' => true, 'enabled' => $enabled]);
        exit;

    } catch (Exception $e) {
        error_log("Error toggling backup: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error al guardar configuración']);
        exit;
    }
}

// Get fallback enabled status from database
function getFallbackEnabled($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuraciones WHERE clave = 'backup_fallback_enabled'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $result['valor'] === '1';
        }
        // Default to config file value
        return defined('BACKUP_FALLBACK_ENABLED') ? BACKUP_FALLBACK_ENABLED : true;
    } catch (Exception $e) {
        return defined('BACKUP_FALLBACK_ENABLED') ? BACKUP_FALLBACK_ENABLED : true;
    }
}

try {
    $pdo = getDbConnection();

    // Get fallback enabled status
    $fallbackEnabled = getFallbackEnabled($pdo);

    // Get the last automatic backup
    $stmt = $pdo->prepare("
        SELECT id, archivo, fecha, estado, tamaño_bytes
        FROM backups
        WHERE tipo = 'automatico'
        ORDER BY fecha DESC
        LIMIT 1
    ");
    $stmt->execute();
    $lastAutoBackup = $stmt->fetch(PDO::FETCH_ASSOC);

    // Count total automatic backups
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico'");
    $stmt->execute();
    $totalAutoBackups = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Count successful automatic backups
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico' AND estado = 'completado'");
    $stmt->execute();
    $successfulBackups = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Determine status
    $status = 'inactive';
    $message = 'No se han detectado backups automáticos';
    $lastRunAgo = null;
    $hoursAgo = null;

    if ($lastAutoBackup) {
        $lastRunTime = new DateTime($lastAutoBackup['fecha']);
        $now = new DateTime();
        $diff = $now->diff($lastRunTime);

        // Calculate hours ago
        $hoursAgo = ($diff->days * 24) + $diff->h;

        // Format "time ago" string
        if ($diff->days > 0) {
            $lastRunAgo = "hace " . $diff->days . " día" . ($diff->days > 1 ? "s" : "");
            if ($diff->h > 0) {
                $lastRunAgo .= " y " . $diff->h . "h";
            }
        } elseif ($diff->h > 0) {
            $lastRunAgo = "hace " . $diff->h . "h";
            if ($diff->i > 0) {
                $lastRunAgo .= " " . $diff->i . "m";
            }
        } else {
            $lastRunAgo = "hace " . $diff->i . "m";
        }

        // Determine status based on time since last backup
        if ($hoursAgo < 25) {
            $status = 'active';
            $message = 'Sistema funcionando correctamente';
        } elseif ($hoursAgo < 48) {
            $status = 'warning';
            $message = 'Más de 24h sin backup';
        } else {
            $status = 'inactive';
            $message = 'Sin backups recientes (>' . floor($hoursAgo / 24) . ' días)';
        }

        // Check if last backup had errors
        if ($lastAutoBackup['estado'] === 'error') {
            $status = 'warning';
            $message = 'Último backup tuvo errores';
        }
    }

    // If fallback is disabled, adjust message
    if (!$fallbackEnabled && $status === 'inactive') {
        $message = 'Backup automático desactivado';
    }

    // Calculate next expected backup (assuming daily at 2:00 AM)
    $nextExpected = new DateTime('today 02:00');
    if ($nextExpected <= new DateTime()) {
        $nextExpected->modify('+1 day');
    }

    echo json_encode([
        'success' => true,
        'fallbackEnabled' => $fallbackEnabled,
        'autoBackup' => [
            'status' => $status,
            'statusLabel' => $status === 'active' ? 'ACTIVO' : ($status === 'warning' ? 'ADVERTENCIA' : 'INACTIVO'),
            'lastRun' => $lastAutoBackup ? $lastAutoBackup['fecha'] : null,
            'lastRunAgo' => $lastRunAgo,
            'hoursAgo' => $hoursAgo,
            'nextExpected' => $nextExpected->format('Y-m-d H:i:s'),
            'nextExpectedFormatted' => $nextExpected->format('d/m H:i'),
            'totalAutoBackups' => (int)$totalAutoBackups,
            'successfulBackups' => (int)$successfulBackups,
            'message' => $message,
            'lastBackupFile' => $lastAutoBackup ? $lastAutoBackup['archivo'] : null,
            'lastBackupSize' => $lastAutoBackup ? round($lastAutoBackup['tamaño_bytes'] / 1048576, 2) : null
        ]
    ]);

} catch (Exception $e) {
    error_log("Error checking backup status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al verificar estado de backups']);
}
?>
