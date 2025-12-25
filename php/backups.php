<?php
/**
 * Backup Management API
 * Handles manual and automatic database backups
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'sesion.php';
require_once 'db.php';

// Only set JSON header if not downloading a file
$action = $_GET['action'] ?? '';
if ($action !== 'download') {
    header('Content-Type: application/json');
}

// Verify admin access
if ($_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso denegado. Solo administradores.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

switch ($method) {
    case 'GET':
        $action = $_GET['action'] ?? '';
        if ($action === 'download') {
            handleDownloadBackup($pdo);
        } else {
            handleGetBackups($pdo);
        }
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';
        if ($action === 'create') {
            handleCreateBackup($pdo, $_SESSION['usuario_id']);
        } elseif ($action === 'download') {
            handleDownloadBackup($pdo);
        } elseif ($action === 'restore') {
            handleRestoreBackup($pdo, $_SESSION['usuario_id']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Acción no especificada']);
        }
        break;

    case 'DELETE':
        handleDeleteBackup($pdo);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        break;
}

/**
 * Get list of all backups
 */
function handleGetBackups($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT b.id, b.archivo, b.ruta_archivo, b.tamaño_bytes, b.fecha,
                   b.tipo, b.estado, b.resultado, b.detalles,
                   u.nombre as usuario_nombre
            FROM backups b
            LEFT JOIN usuarios u ON b.usuario_id = u.id
            ORDER BY b.fecha DESC
        ");
        $stmt->execute();
        $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'backups' => $backups]);
    } catch (PDOException $e) {
        error_log("Error getting backups: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al obtener backups']);
    }
}

/**
 * Create a new backup
 */
function handleCreateBackup($pdo, $usuario_id) {
    $inicio = microtime(true);

    try {
        // Definir configuración
        $backupDir = __DIR__ . '/../backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $fecha = date('Y-m-d_H-i-s');
        $nombreArchivo = "dbuzzbackup_{$fecha}.sql";
        $rutaCompleta = $backupDir . $nombreArchivo;

        // Obtener configuración de base de datos desde db.php
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '35128';
        $dbName = 'dbuzz';

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

        // Ejecutar backup
        exec($comando, $output, $returnCode);

        $duracion = round((microtime(true) - $inicio) * 1000); // ms

        if ($returnCode === 0 && file_exists($rutaCompleta)) {
            $tamanioBytes = filesize($rutaCompleta);

            // Contar tablas en el backup
            $contenido = file_get_contents($rutaCompleta, false, null, 0, 50000);
            preg_match_all('/CREATE TABLE/', $contenido, $matches);
            $numTablas = count($matches[0]);

            $detalles = json_encode([
                'tablas_exportadas' => $numTablas,
                'duracion_ms' => $duracion,
                'tamanio_mb' => round($tamanioBytes / 1048576, 2)
            ]);

            // Registrar en base de datos
            $stmt = $pdo->prepare("
                INSERT INTO backups
                (archivo, ruta_archivo, tamaño_bytes, tipo, estado, resultado, detalles, usuario_id)
                VALUES (?, ?, ?, 'manual', 'completado', ?, ?, ?)
            ");
            $stmt->execute([
                $nombreArchivo,
                $rutaCompleta,
                $tamanioBytes,
                'Backup creado exitosamente',
                $detalles,
                $usuario_id
            ]);

            $backupId = $pdo->lastInsertId();

            // Registrar en logs
            $stmt = $pdo->prepare("
                INSERT INTO logs (usuario_id, accion, detalles)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $usuario_id,
                'Backup Manual',
                json_encode([
                    'archivo' => $nombreArchivo,
                    'tamanio_mb' => round($tamanioBytes / 1048576, 2),
                    'duracion_ms' => $duracion,
                    'tablas' => $numTablas
                ])
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Backup creado exitosamente',
                'backup' => [
                    'id' => $backupId,
                    'archivo' => $nombreArchivo,
                    'tamaño_bytes' => $tamanioBytes,
                    'tamaño_mb' => round($tamanioBytes / 1048576, 2),
                    'duracion_ms' => $duracion
                ]
            ]);
        } else {
            // Error al crear backup
            $errorMsg = implode("\n", $output);
            error_log("Backup failed: $errorMsg");

            $stmt = $pdo->prepare("
                INSERT INTO backups
                (archivo, ruta_archivo, tipo, estado, resultado, detalles, usuario_id)
                VALUES (?, ?, 'manual', 'error', ?, ?, ?)
            ");
            $stmt->execute([
                $nombreArchivo,
                $rutaCompleta,
                'Error al crear backup: ' . $errorMsg,
                json_encode(['error_output' => $output, 'return_code' => $returnCode]),
                $usuario_id
            ]);

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al crear el backup',
                'details' => $output
            ]);
        }

    } catch (Exception $e) {
        error_log("Backup creation error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear backup: ' . $e->getMessage()
        ]);
    }
}

/**
 * Download a backup file
 */
function handleDownloadBackup($pdo) {
    // Accept ID from GET parameter (for direct downloads) or JSON body (for API calls)
    $backupId = $_GET['id'] ?? 0;

    if ($backupId === 0) {
        // Fallback to JSON body for backward compatibility
        $data = json_decode(file_get_contents('php://input'), true);
        $backupId = $data['id'] ?? 0;
    }

    try {
        $stmt = $pdo->prepare("SELECT archivo, ruta_archivo FROM backups WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$backup) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Backup no encontrado']);
            return;
        }

        $rutaArchivo = $backup['ruta_archivo'];

        if (!file_exists($rutaArchivo)) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Archivo no encontrado en el servidor']);
            return;
        }

        // Descargar el archivo
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $backup['archivo'] . '"');
        header('Content-Length: ' . filesize($rutaArchivo));
        readfile($rutaArchivo);
        exit;

    } catch (PDOException $e) {
        error_log("Error downloading backup: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al descargar backup']);
    }
}

/**
 * Delete a backup
 */
function handleDeleteBackup($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $backupId = $data['id'] ?? 0;

    try {
        // Obtener información del backup
        $stmt = $pdo->prepare("SELECT archivo, ruta_archivo FROM backups WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$backup) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Backup no encontrado']);
            return;
        }

        // Eliminar archivo físico
        if (file_exists($backup['ruta_archivo'])) {
            unlink($backup['ruta_archivo']);
        }

        // Eliminar registro de base de datos
        $stmt = $pdo->prepare("DELETE FROM backups WHERE id = ?");
        $stmt->execute([$backupId]);

        echo json_encode([
            'success' => true,
            'message' => 'Backup eliminado exitosamente'
        ]);

    } catch (Exception $e) {
        error_log("Error deleting backup: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error al eliminar backup']);
    }
}
/**
 * Restore a backup
 */
function handleRestoreBackup($pdo, $usuario_id) {
    $data = json_decode(file_get_contents('php://input'), true);
    $backupId = $data['id'] ?? 0;

    $inicio = microtime(true);

    try {
        // Obtener información del backup
        $stmt = $pdo->prepare("SELECT archivo, ruta_archivo FROM backups WHERE id = ?");
        $stmt->execute([$backupId]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$backup) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Backup no encontrado']);
            return;
        }

        if (!file_exists($backup['ruta_archivo'])) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Archivo de backup no encontrado en el servidor']);
            return;
        }

        // Configuración de base de datos
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '35128';
        $dbName = 'dbuzz';

        // Construir comando mysql para importar
        $mysqlPath = 'c:\\xampp\\mysql\\bin\\mysql.exe';
        $comando = sprintf(
            '"%s" --user=%s --password=%s --host=%s %s < "%s" 2>&1',
            $mysqlPath,
            $dbUser,
            $dbPass,
            $dbHost,
            $dbName,
            $backup['ruta_archivo']
        );

        // Ejecutar restauración
        // Crear archivo bat temporal para evitar problemas de redirección
        $batFile = __DIR__ . '/temp_restore_' . uniqid() . '.bat';
        $batContent = "@echo off\n" . $comando;
        file_put_contents($batFile, $batContent);

        exec($batFile, $output, $returnCode);

        // Eliminar archivo temporal
        unlink($batFile);

        $duracion = round((microtime(true) - $inicio) * 1000); // ms

        if ($returnCode === 0) {
            // Registrar en logs
            $stmt = $pdo->prepare("
                INSERT INTO logs (usuario_id, accion, detalles)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $usuario_id,
                'Restauración de Backup',
                json_encode([
                    'archivo' => $backup['archivo'],
                    'duracion_ms' => $duracion,
                    'resultado' => 'Exitoso'
                ])
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Base de datos restaurada exitosamente',
                'duracion_ms' => $duracion
            ]);
        } else {
            $errorMsg = implode("\n", $output);
            error_log("Restore failed: $errorMsg");

            // Registrar fallo en logs
            $stmt = $pdo->prepare("
                INSERT INTO logs (usuario_id, accion, detalles)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $usuario_id,
                'Fallo Restauración Backup',
                json_encode([
                    'archivo' => $backup['archivo'],
                    'error' => $errorMsg
                ])
            ]);

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al restaurar la base de datos',
                'details' => $output
            ]);
        }

    } catch (Exception $e) {
        error_log("Restore error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error crítico al restaurar: ' . $e->getMessage()
        ]);
    }
}
?>
