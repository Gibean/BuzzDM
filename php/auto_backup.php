<?php
/**
 * Automatic Backup Script
 * Supports both CLI and HTTP execution for hosting environments
 *
 * Usage:
 *   CLI:  php auto_backup.php
 *   HTTP: auto_backup.php?token=YOUR_SECRET_TOKEN
 *
 * Schedule via:
 *   - Hosting cron job (cPanel/Plesk)
 *   - Windows Task Scheduler
 *   - Linux crontab
 *   - External cron service (cron-job.org)
 */

require_once __DIR__ . '/backup_config.php';
require_once __DIR__ . '/db.php';

// Determine execution mode
$isCLI = (php_sapi_name() === 'cli');
$isHTTP = !$isCLI;
$providedToken = $_GET['token'] ?? '';
$isFallback = isset($_GET['fallback']) && $_GET['fallback'] === '1';
$triggeredById = $_GET['triggered_by'] ?? NULL;

// Authentication check
if ($isHTTP) {
    // For HTTP requests, require valid token
    if ($providedToken !== BACKUP_SECRET_TOKEN) {
        http_response_code(403);
        if (isset($_GET['json'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        } else {
            echo 'Unauthorized access';
        }
        exit;
    }

    // For HTTP, output JSON
    header('Content-Type: application/json');
}

// Helper function for output
function output($message, $isCLI) {
    if ($isCLI) {
        echo $message . "\n";
    }
}

output("=== Iniciando backup automático ===", $isCLI);
output("Fecha: " . date('Y-m-d H:i:s'), $isCLI);
output("Modo: " . ($isCLI ? 'CLI' : 'HTTP') . ($isFallback ? ' (Fallback)' : ' (Cron)'), $isCLI);
output("", $isCLI);

$inicio = microtime(true);
$resultado = ['success' => false];

try {
    $pdo = getDbConnection();

    // Use configuration
    $backupDir = BACKUP_DIRECTORY;
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        output("✓ Directorio de backups creado", $isCLI);
    }

    $fecha = date('Y-m-d_H-i-s');
    $nombreArchivo = "dbuzzbackup_{$fecha}.sql";
    $rutaCompleta = $backupDir . $nombreArchivo;

    output("📁 Archivo: {$nombreArchivo}", $isCLI);
    output("📍 Ruta: {$rutaCompleta}", $isCLI);
    output("", $isCLI);

    // Build mysqldump command using config
    $comando = sprintf(
        '"%s" --user=%s --password=%s --host=%s %s > "%s" 2>&1',
        MYSQLDUMP_PATH,
        BACKUP_DB_USER,
        BACKUP_DB_PASS,
        BACKUP_DB_HOST,
        BACKUP_DB_NAME,
        $rutaCompleta
    );

    output("⏳ Ejecutando mysqldump...", $isCLI);
    exec($comando, $output_lines, $returnCode);

    $duracion = round((microtime(true) - $inicio) * 1000); // ms

    if ($returnCode === 0 && file_exists($rutaCompleta)) {
        $tamanioBytes = filesize($rutaCompleta);
        $tamanioMB = round($tamanioBytes / 1048576, 2);

        output("✓ Backup creado exitosamente", $isCLI);
        output("📊 Tamaño: {$tamanioMB} MB", $isCLI);
        output("⏱️ Duración: {$duracion} ms", $isCLI);
        output("", $isCLI);

        // Count tables
        $contenido = file_get_contents($rutaCompleta, false, null, 0, 50000);
        preg_match_all('/CREATE TABLE/', $contenido, $matches);
        $numTablas = count($matches[0]);

        output("📋 Tablas exportadas: {$numTablas}", $isCLI);
        output("", $isCLI);

        // Determine backup type based on execution method
        $tipoBackup = $isFallback ? 'automatico_fallback' : 'automatico';

        $detalles = json_encode([
            'tablas_exportadas' => $numTablas,
            'duracion_ms' => $duracion,
            'tamanio_mb' => $tamanioMB,
            'tipo_ejecucion' => $isCLI ? 'cli' : 'http',
            'metodo' => $isFallback ? 'fallback' : 'cron'
        ]);

        // Register in database
        $stmt = $pdo->prepare("
            INSERT INTO backups
            (archivo, ruta_archivo, tamaño_bytes, tipo, estado, resultado, detalles, usuario_id)
            VALUES (?, ?, ?, ?, 'completado', ?, ?, ?)
        ");
        $stmt->execute([
            $nombreArchivo,
            $rutaCompleta,
            $tamanioBytes,
            'automatico', // Keep as 'automatico' for compatibility
            'Backup automático creado exitosamente' . ($isFallback ? ' (fallback)' : ''),
            $detalles,
            $triggeredById
        ]);

        $backupId = $pdo->lastInsertId();
        output("✓ Backup registrado en base de datos (ID: {$backupId})", $isCLI);

        // Log the action
        $stmt = $pdo->prepare("
            INSERT INTO logs (usuario_id, accion, detalles)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $triggeredById,
            'Backup Automático' . ($isFallback ? ' (Fallback)' : ''),
            json_encode([
                'archivo' => $nombreArchivo,
                'tamanio_mb' => $tamanioMB,
                'duracion_ms' => $duracion,
                'tablas' => $numTablas,
                'metodo' => $isFallback ? 'fallback' : 'cron'
            ])
        ]);

        output("✓ Acción registrada en logs", $isCLI);
        output("", $isCLI);

        // Rotation: keep only last MAX_AUTO_BACKUPS automatic backups
        output("🔄 Limpiando backups antiguos...", $isCLI);
        $stmt = $pdo->query("
            SELECT id, archivo, ruta_archivo
            FROM backups
            WHERE tipo = 'automatico'
            ORDER BY fecha DESC
            LIMIT " . MAX_AUTO_BACKUPS . ", 1000
        ");
        $backupsAntiguos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eliminados = 0;
        foreach ($backupsAntiguos as $backup) {
            if (file_exists($backup['ruta_archivo'])) {
                unlink($backup['ruta_archivo']);
            }
            $stmtDelete = $pdo->prepare("DELETE FROM backups WHERE id = ?");
            $stmtDelete->execute([$backup['id']]);
            $eliminados++;
        }

        if ($eliminados > 0) {
            output("🗑️ Backups antiguos eliminados: {$eliminados}", $isCLI);
        } else {
            output("✓ No hay backups antiguos para eliminar", $isCLI);
        }

        output("", $isCLI);
        output("=== Backup automático completado ===", $isCLI);

        $resultado = [
            'success' => true,
            'message' => 'Backup creado exitosamente',
            'backup' => [
                'id' => $backupId,
                'archivo' => $nombreArchivo,
                'tamanio_mb' => $tamanioMB,
                'duracion_ms' => $duracion,
                'tablas' => $numTablas
            ]
        ];

        if ($isCLI) {
            exit(0);
        }

    } else {
        // Error creating backup
        $errorMsg = implode("\n", $output_lines);
        output("✗ Error al crear backup", $isCLI);
        output("Error: {$errorMsg}", $isCLI);

        $stmt = $pdo->prepare("
            INSERT INTO backups
            (archivo, ruta_archivo, tipo, estado, resultado, detalles, usuario_id)
            VALUES (?, ?, 'automatico', 'error', ?, ?, NULL)
        ");
        $stmt->execute([
            $nombreArchivo,
            $rutaCompleta,
            'Error al crear backup automático: ' . $errorMsg,
            json_encode(['error_output' => $output_lines, 'return_code' => $returnCode])
        ]);

        output("", $isCLI);
        output("=== Backup automático fallido ===", $isCLI);

        $resultado = [
            'success' => false,
            'error' => 'Error al crear backup',
            'details' => $errorMsg
        ];

        if ($isCLI) {
            exit(1);
        }
    }

} catch (Exception $e) {
    output("✗ Error grave: " . $e->getMessage(), $isCLI);
    output("", $isCLI);
    output("=== Backup automático fallido ===", $isCLI);

    $resultado = [
        'success' => false,
        'error' => $e->getMessage()
    ];

    if ($isCLI) {
        exit(1);
    }
}

// For HTTP requests, output JSON result
if ($isHTTP) {
    echo json_encode($resultado);
}
?>
