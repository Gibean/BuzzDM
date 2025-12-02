<?php
/**
 * Automatic Backup Script
 * To be executed via CLI for scheduled backups
 *
 * Usage: php auto_backup.php
 * Schedule this script to run every 24 hours using Task Scheduler
 */

// Permitir ejecución desde CLI
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

require_once __DIR__ . '/db.php';

echo "=== Iniciando backup automático ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$inicio = microtime(true);

try {
    $pdo = getDbConnection();

    // Definir configuración
    $backupDir = __DIR__ . '/../backups/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        echo "✓ Directorio de backups creado\n";
    }

    $fecha = date('Y-m-d_H-i-s');
    $nombreArchivo = "dbuzzbackup_{$fecha}.sql";
    $rutaCompleta = $backupDir . $nombreArchivo;

    // Configuración de base de datos
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '35128';
    $dbName = 'dbuzz';

    echo "📁 Archivo: {$nombreArchivo}\n";
    echo "📍 Ruta: {$rutaCompleta}\n\n";

    // Construir comando mysqldump
    $mysqldumpPath = 'c:\\xampp\\mysql\\bin\\mysqldump.exe';
    $comando = sprintf(
        '"%s" --user=%s --password=%s --host=%s %s > "%s" 2>&1',
        $mysqldumpPath,
        $dbUser,
        $dbPass,
        $dbHost,
        $dbName,
        $rutaCompleta
    );

    echo "⏳ Ejecutando mysqldump...\n";
    exec($comando, $output, $returnCode);

    $duracion = round((microtime(true) - $inicio) * 1000); // ms

    if ($returnCode === 0 && file_exists($rutaCompleta)) {
        $tamanioBytes = filesize($rutaCompleta);
        $tamanioMB = round($tamanioBytes / 1048576, 2);

        echo "✓ Backup creado exitosamente\n";
        echo "📊 Tamaño: {$tamanioMB} MB\n";
        echo "⏱️  Duración: {$duracion} ms\n\n";

        // Contar tablas en el backup
        $contenido = file_get_contents($rutaCompleta, false, null, 0, 50000);
        preg_match_all('/CREATE TABLE/', $contenido, $matches);
        $numTablas = count($matches[0]);

        echo "📋 Tablas exportadas: {$numTablas}\n\n";

        $detalles = json_encode([
            'tablas_exportadas' => $numTablas,
            'duracion_ms' => $duracion,
            'tamanio_mb' => $tamanioMB,
            'tipo_ejecucion' => 'automatico'
        ]);

        // Registrar en base de datos (usuario_id NULL para automático)
        $stmt = $pdo->prepare("
            INSERT INTO backups
            (archivo, ruta_archivo, tamaño_bytes, tipo, estado, resultado, detalles, usuario_id)
            VALUES (?, ?, ?, 'automatico', 'completado', ?, ?, NULL)
        ");
        $stmt->execute([
            $nombreArchivo,
            $rutaCompleta,
            $tamanioBytes,
            'Backup automático creado exitosamente',
            $detalles
        ]);

        echo "✓ Backup registrado en base de datos (ID: " . $pdo->lastInsertId() . ")\n";

        // Registrar en logs
        $stmt = $pdo->prepare("
            INSERT INTO logs (usuario_id, accion, detalles)
            VALUES (NULL, ?, ?)
        ");
        $stmt->execute([
            'Backup Automático',
            json_encode([
                'archivo' => $nombreArchivo,
                'tamanio_mb' => $tamanioMB,
                'duracion_ms' => $duracion,
                'tablas' => $numTablas
            ])
        ]);

        echo "✓ Acción registrada en logs\n\n";

        // Rotación de backups: mantener solo los últimos 30
        echo "🔄 Limpiando backups antiguos...\n";
        $stmt = $pdo->query("
            SELECT id, archivo, ruta_archivo
            FROM backups
            WHERE tipo = 'automatico'
            ORDER BY fecha DESC
            LIMIT 30, 1000
        ");
        $backupsAntiguos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $eliminados = 0;
        foreach ($backupsAntiguos as $backup) {
            // Eliminar archivo físico
            if (file_exists($backup['ruta_archivo'])) {
                unlink($backup['ruta_archivo']);
            }
            // Eliminar registro
            $stmtDelete = $pdo->prepare("DELETE FROM backups WHERE id = ?");
            $stmtDelete->execute([$backup['id']]);
            $eliminados++;
        }

        if ($eliminados > 0) {
            echo "🗑️  Backups antiguos eliminados: {$eliminados}\n";
        } else {
            echo "✓ No hay backups antiguos para eliminar\n";
        }

        echo "\n=== Backup automático completado ===\n";
        exit(0);

    } else {
        // Error al crear backup
        $errorMsg = implode("\n", $output);
        echo "✗ Error al crear backup\n";
        echo "Error: {$errorMsg}\n";

        $stmt = $pdo->prepare("
            INSERT INTO backups
            (archivo, ruta_archivo, tipo, estado, resultado, detalles, usuario_id)
            VALUES (?, ?, 'automatico', 'error', ?, ?, NULL)
        ");
        $stmt->execute([
            $nombreArchivo,
            $rutaCompleta,
            'Error al crear backup automático: ' . $errorMsg,
            json_encode(['error_output' => $output, 'return_code' => $returnCode])
        ]);

        echo "\n=== Backup automático fallido ===\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "✗ Error grave: " . $e->getMessage() . "\n";
    echo "\n=== Backup automático fallido ===\n";
    exit(1);
}
?>
