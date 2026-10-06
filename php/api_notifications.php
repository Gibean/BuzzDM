<?php
require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

// La sesión ya está verificada por sesion.php
$userId = $_SESSION['usuario_id'];
$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'fetch') {
        // Fetch unread count
        $stmtCount = $pdo->prepare("SELECT COUNT(*) as count FROM notificaciones WHERE usuario_id = ? AND leido = 0");
        $stmtCount->execute([$userId]);
        $unreadCount = $stmtCount->fetch()['count'];

        // Fetch latest 50 notifications
        $limit = 50;
        $stmt = $pdo->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY creado_at DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        $notifications = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    elseif ($method === 'POST') {
        // Todas las acciones POST de este endpoint modifican estado. Sin token
        // CSRF, cualquier sitio podria marcar notificaciones como leidas o
        // borrar las del usuario mediante el navegador con su sesion abierta.
        $csrf = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!validateCsrfToken($csrf)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Error de seguridad: token CSRF inválido o ausente.',
            ]);
            exit;
        }

        if ($action === 'mark_read') {
            $id = $_POST['id'] ?? null;
            if ($id === 'all') {
                $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?");
                $stmt->execute([$userId]);
            } elseif ($id) {
                $stmt = $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$id, $userId]);
            } else {
                throw new Exception("ID inválido");
            }
            echo json_encode(['success' => true]);
        }

        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? null;
            if ($id === 'all_read') {
                $stmt = $pdo->prepare("DELETE FROM notificaciones WHERE usuario_id = ? AND leido = 1");
                $stmt->execute([$userId]);
            } elseif ($id) {
                $stmt = $pdo->prepare("DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?");
                $stmt->execute([$id, $userId]);
            } else {
                throw new Exception("ID inválido");
            }
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

            $empresaId = TenantContext::require('api_notifications:create');

            // El destinatario tiene que pertenecer a la empresa activa. Sin esta
            // comprobacion, un POST con user_id ajeno escribe una fila que lleva
            // nuestra empresa_id pero aparece en la bandeja del otro tenant.
            $stmtDestino = $pdo->prepare("SELECT empresa_id FROM usuarios WHERE id = ?");
            $stmtDestino->execute([$targetUserId]);
            $empresaDestino = $stmtDestino->fetchColumn();

            if ($empresaDestino === false) {
                throw new Exception("El usuario destinatario no existe");
            }

            if ((int)$empresaDestino !== $empresaId) {
                throw new Exception("No se puede notificar a un usuario de otra empresa");
            }

            $stmt = $pdo->prepare("INSERT INTO notificaciones (empresa_id, usuario_id, tipo, titulo, mensaje, enlace) VALUES (?, ?, ?, ?, ?, ?)");

            if ($stmt->execute([$empresaId, $targetUserId, $type, $title, $message, $link])) {
                echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            } else {
                throw new Exception("Error al crear la notificación");
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
}
