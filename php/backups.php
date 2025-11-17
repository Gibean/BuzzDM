<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';

// Get PDO connection
try {
    $pdo = getDbConnection();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            $stmt = $pdo->prepare("SELECT id, filename, type, status, created_at FROM backups ORDER BY created_at DESC");
            $stmt->execute();
            $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($backups);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al obtener backups: " . $e->getMessage()]);
        }
        break;

    case 'POST':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $filename = $data['filename'] ?? '';
            $type = $data['type'] ?? 'manual'; // e.g., 'manual', 'scheduled'
            $status = $data['status'] ?? 'completed'; // e.g., 'pending', 'completed', 'failed'

            // In a real application, you would trigger the actual backup process here
            // and then record its details. For now, we just record the metadata.

            $stmt = $pdo->prepare("INSERT INTO backups (filename, type, status) VALUES (?, ?, ?)");
            $stmt->execute([$filename, $type, $status]);

            echo json_encode(["message" => "Backup registrado", "id" => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al registrar backup: " . $e->getMessage()]);
        }
        break;

    case 'DELETE':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;

            $stmt = $pdo->prepare("DELETE FROM backups WHERE id = ?");
            $stmt->execute([$id]);

            echo json_encode(["message" => "Backup eliminado"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Error de base de datos al eliminar backup: " . $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método no permitido"]);
        break;
}
?>