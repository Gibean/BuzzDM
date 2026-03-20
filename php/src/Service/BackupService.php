<?php
require_once __DIR__ . '/../Repository/BackupRepository.php';
require_once __DIR__ . '/../../backup_config.php'; // Ensure we access constant params

class BackupService {
    private $repository;

    public function __construct() {
        $this->repository = new BackupRepository();
    }

    public function getAllBackups() {
        return $this->repository->findAll();
    }

    public function performBackup($type = 'manual', $userId = null, $isFallback = false) {
        $inicio = microtime(true);
        $backupDir = dirname(__DIR__, 2) . '/backups/'; // c:\xampp\htdocs\BuzzDM\php\..\backups\

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "dbuzzbackup_{$fecha}.sql";
        $rutaCompleta = $backupDir . $nombreArchivo;

        // Use constants from backup_config.php
        $cmd = sprintf(
            '"%s" --user=%s --password=%s --host=%s %s > "%s" 2>&1',
            MYSQLDUMP_PATH,
            BACKUP_DB_USER,
            BACKUP_DB_PASS,
            BACKUP_DB_HOST,
            BACKUP_DB_NAME,
            $rutaCompleta
        );

        exec($cmd, $output, $returnCode);
        $duracion = round((microtime(true) - $inicio) * 1000);

        if ($returnCode === 0 && file_exists($rutaCompleta)) {
            $tamanioBytes = filesize($rutaCompleta);

            // Count tables
            $contenido = file_get_contents($rutaCompleta, false, null, 0, 50000);
            preg_match_all('/CREATE TABLE/', $contenido, $matches);
            $numTablas = count($matches[0]);

            $detalles = json_encode([
                'tablas_exportadas' => $numTablas,
                'duracion_ms' => $duracion,
                'tamanio_mb' => round($tamanioBytes / 1048576, 2),
                'metodo' => $isFallback ? 'fallback' : 'standard'
            ]);

            $statusText = 'completado';
            $resultText = ($type === 'manual' ? 'Backup creado exitosamente' : 'Backup automático creado exitosamente') . ($isFallback ? ' (fallback)' : '');

            $backupId = $this->repository->create([
                'archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaCompleta,
                'tamaño_bytes' => $tamanioBytes,
                'tipo' => $type,
                'estado' => $statusText,
                'resultado' => $resultText,
                'detalles' => $detalles,
                'usuario_id' => $userId
            ]);

            // Log
            if ($userId) {
                $this->repository->logAction($userId, ($type === 'manual' ? 'Backup Manual' : 'Backup Automático'), [
                    'archivo' => $nombreArchivo,
                    'tamanio_mb' => round($tamanioBytes / 1048576, 2),
                    'duracion_ms' => $duracion
                ]);
            }

            // Auto rotation if automatic
            if ($type === 'automatico') {
                $this->rotateBackups();
            }

            return [
                'success' => true,
                'backup' => [
                    'id' => $backupId,
                    'archivo' => $nombreArchivo,
                    'tamaño_bytes' => $tamanioBytes,
                    'tamaño_mb' => round($tamanioBytes / 1048576, 2),
                    'duracion_ms' => $duracion
                ]
            ];
        } else {
             $errorMsg = implode("\n", $output);
             // Log failure in DB
             $this->repository->create([
                'archivo' => $nombreArchivo,
                'ruta_archivo' => $rutaCompleta,
                'tamaño_bytes' => 0,
                'tipo' => $type,
                'estado' => 'error',
                'resultado' => 'Error al crear backup: ' . $errorMsg,
                'detalles' => json_encode(['error_output' => $output, 'return_code' => $returnCode]),
                'usuario_id' => $userId
             ]);

             throw new Exception("Error mysqldump: " . $errorMsg);
        }
    }

    public function restoreBackup($id, $userId) {
        $backup = $this->repository->findById($id);
        if (!$backup || !file_exists($backup['ruta_archivo'])) {
            throw new Exception("Backup no encontrado");
        }

        $inicio = microtime(true);

        // MySQL Import Command
        $mysqlPath = 'c:\\xampp\\mysql\\bin\\mysql.exe'; // Could also use config constant if available or infer
        if (defined('MYSQL_PATH')) $mysqlPath = constant('MYSQL_PATH');

        $cmd = sprintf(
            '"%s" --user=%s --password=%s --host=%s %s < "%s" 2>&1',
            $mysqlPath,
            BACKUP_DB_USER,
            BACKUP_DB_PASS,
            BACKUP_DB_HOST,
            BACKUP_DB_NAME,
            $backup['ruta_archivo']
        );

        // Bat wrapper to avoid redirection issues on Windows
        $batFile = sys_get_temp_dir() . '/restore_' . uniqid() . '.bat';
        file_put_contents($batFile, "@echo off\n" . $cmd);

        exec($batFile, $output, $returnCode);
        unlink($batFile);

        $duracion = round((microtime(true) - $inicio) * 1000);

        if ($returnCode === 0) {
            $this->repository->logAction($userId, 'Restauración de Backup', [
                'archivo' => $backup['archivo'],
                'duracion_ms' => $duracion,
                'resultado' => 'Exitoso'
            ]);
            return ['success' => true, 'duracion_ms' => $duracion];
        } else {
            $errorMsg = implode("\n", $output);
            $this->repository->logAction($userId, 'Fallo Restauración Backup', [
                'archivo' => $backup['archivo'],
                'error' => $errorMsg
            ]);
            throw new Exception("Error al restaurar: " . $errorMsg);
        }
    }

    public function deleteBackup($id) {
        $backup = $this->repository->findById($id);
        if (!$backup) throw new Exception("Backup no encontrado");

        if (file_exists($backup['ruta_archivo'])) {
            unlink($backup['ruta_archivo']);
        }
        return $this->repository->delete($id);
    }

    public function getDownloadPath($id) {
         $backup = $this->repository->findById($id);
         if (!$backup || !file_exists($backup['ruta_archivo'])) return null;
         return ['path' => $backup['ruta_archivo'], 'filename' => $backup['archivo']];
    }

    public function rotateBackups() {
        $limit = defined('MAX_AUTO_BACKUPS') ? MAX_AUTO_BACKUPS : 7;
        $oldBackups = $this->repository->getOldBackups($limit);
        $count = 0;
        foreach ($oldBackups as $b) {
            if (file_exists($b['ruta_archivo'])) unlink($b['ruta_archivo']);
            $this->repository->delete($b['id']);
            $count++;
        }
        return $count;
    }

    public function getAutoBackupStatus() {
        $fallbackEnabled = $this->repository->getFallbackEnabled();
        $lastAuto = $this->repository->findLastAuto();

        $total = $this->repository->countAutoBackups();
        $success = $this->repository->countAutoSuccess();

        // Logic for health status
        $status = 'inactive';
        $message = 'No se han detectado backups automáticos';
        $lastRunAgo = null;
        $hoursAgo = null;

        if ($lastAuto) {
            $lastRunTime = new DateTime($lastAuto['fecha']);
            $now = new DateTime();
            $diff = $now->diff($lastRunTime);
            $hoursAgo = ($diff->days * 24) + $diff->h;

            // Format string logic (simplified)
            $lastRunAgo = "hace " . ($diff->days > 0 ? "{$diff->days}d " : "") . "{$diff->h}h {$diff->i}m";

            if ($hoursAgo < 25) {
                $status = 'active';
                $message = 'Sistema funcionando correctamente';
            } elseif ($hoursAgo < 48) {
                $status = 'warning';
                $message = 'Más de 24h sin backup';
            } else {
                $status = 'inactive';
                $message = 'Sin backups recientes (> ' . floor($hoursAgo/24) . ' dias)';
            }

            if ($lastAuto['estado'] === 'error') {
                $status = 'warning';
                $message = 'Último backup con errores';
            }
        }

        if (!$fallbackEnabled && $status === 'inactive') {
            $message = 'Backup automático desactivado';
        }

        $nextExpected = new DateTime('today 02:00');
        if ($nextExpected <= new DateTime()) $nextExpected->modify('+1 day');

        return [
            'fallbackEnabled' => $fallbackEnabled,
            'autoBackup' => [
                'status' => $status,
                'statusLabel' => strtoupper($status),
                'lastRun' => $lastAuto['fecha'] ?? null,
                'lastRunAgo' => $lastRunAgo,
                'hoursAgo' => $hoursAgo,
                'nextExpected' => $nextExpected->format('Y-m-d H:i:s'),
                'nextExpectedFormatted' => $nextExpected->format('d/m H:i'),
                'totalAutoBackups' => (int)$total,
                'successfulBackups' => (int)$success,
                'message' => $message,
                'lastBackupFile' => $lastAuto['archivo'] ?? null,
                'lastBackupSize' => isset($lastAuto['tamaño_bytes']) ? round($lastAuto['tamaño_bytes'] / 1048576, 2) : null
            ]
        ];
    }

    public function setFallbackEnabled($enabled) {
        return $this->repository->setFallbackEnabled($enabled);
    }
}
