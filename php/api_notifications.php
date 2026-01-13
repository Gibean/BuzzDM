<?php
require_once 'db.php';
require_once 'sesion.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];
$conn = connectDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Set header for JSON response
header('Content-Type: application/json');

try {
    if ($method === 'GET' && $action === 'fetch') {
        // Fetch unread count
        $stmtCount = $conn->prepare("SELECT COUNT(*) as count FROM notificaciones WHERE usuario_id = ? AND leido = 0");
        $stmtCount->bind_param("i", $userId);
        $stmtCount->execute();
        $resCount = $stmtCount->get_result();
        $unreadCount = $resCount->fetch_assoc()['count'];

        // Fetch latest 50 notifications
        $limit = 50;
        $stmt = $conn->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY creado_at DESC LIMIT ?");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    elseif ($method === 'POST') {
        if ($action === 'mark_read') {
            $id = $_POST['id'] ?? null;
            if ($id === 'all') {
                $stmt = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?");
                $stmt->bind_param("i", $userId);
            } elseif ($id) {
                $stmt = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
                $stmt->bind_param("ii", $id, $userId);
            } else {
                throw new Exception("ID inválido");
            }
            $stmt->execute();
            echo json_encode(['success' => true]);
        }

        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? null;
            if ($id === 'all_read') {
                 $stmt = $conn->prepare("DELETE FROM notificaciones WHERE usuario_id = ? AND leido = 1");
                 $stmt->bind_param("i", $userId);
            } elseif ($id) {
                $stmt = $conn->prepare("DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?");
                $stmt->bind_param("ii", $id, $userId);
            } else {
                throw new Exception("ID inválido");
            }
            $stmt->execute();
            echo json_encode(['success' => true]);
        }

        // Internal helper to test or manually create (protected by session check above, maybe add role check later)
        elseif ($action === 'create') {
            // Verify if user has permission to create notifications if needed
            // For now, allow any logged in user to Self-Test or System calls

            $targetUserId = $_POST['user_id'] ?? $userId; // Default to self
            $type = $_POST['type'] ?? 'info';
            $title = $_POST['title'] ?? 'Notificación';
            $message = $_POST['message'] ?? '';
            $link = $_POST['link'] ?? null;

            if (empty($message)) throw new Exception("El mensaje es requerido");

            $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $targetUserId, $type, $title, $message, $link);

            if ($stmt->execute()) {
                 echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
            } else {
                 throw new Exception("Error al crear la notificación: " . $stmt->error);
            }
        }
        else {
             throw new Exception("Acción no válida");
        }
    } else {
         throw new Exception("Método no permitido");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) $conn->close();
}
