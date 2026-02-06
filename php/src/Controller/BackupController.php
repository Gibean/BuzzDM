<?php
// Autoload handled by composer
require_once __DIR__ . '/../helpers/logger.php';

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Backups", description: "Backup Management")]
class BackupController {
    private $service;

    public function __construct() {
        $this->service = new BackupService();
    }

    public function handleRequest() {
        // Auth check - Admin only
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            $this->jsonResponse(false, null, 'Acceso denegado');
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        // Handle POST body if JSON
        $input = [];
        if ($method === 'POST' || $method === 'DELETE') {
             $raw = file_get_contents('php://input');
             $input = json_decode($raw, true) ?? [];
        }

        // CSRF Validation for state-changing methods
        if ($method === 'POST' || $method === 'DELETE') {
            $token = $input['csrf_token'] ?? '';
            if (!validateCsrfToken($token)) {
                $this->jsonResponse(false, null, 'Error de seguridad: Token CSRF inválido o ausente.');
            }
        }

        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'download') {
                        $this->download($_GET['id'] ?? 0);
                    } elseif ($action === 'status') {
                        $this->status();
                    } else {
                        $this->list();
                    }
                    break;
                case 'POST':
                    if ($action === 'create') {
                        $this->create();
                    } elseif ($action === 'restore') {
                        $this->restore($input['id'] ?? 0);
                    } elseif ($action === 'toggle_fallback') {
                         $enabled = $input['enabled'] ?? true;
                         $this->toggleFallback($enabled);
                    } else {
                         if (isset($_GET['action']) && $_GET['action'] === 'toggle') {
                             $this->toggleFallback($input['enabled'] ?? true);
                         } else {
                             $this->jsonResponse(false, null, 'Acción no válida');
                         }
                    }
                    break;
                case 'DELETE':
                    $this->delete($input['id'] ?? 0);
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_backups.php",
        tags: ["Backups"],
        summary: "List backups",
        responses: [
            new OA\Response(response: 200, description: "Backup list")
        ]
    )]
    private function list() {
        $backups = $this->service->getAllBackups();
        $this->jsonResponse(true, ['backups' => $backups]);
    }

    #[OA\Post(
        path: "/php/api_backups.php?action=create",
        tags: ["Backups"],
        summary: "Create backup",
        responses: [
            new OA\Response(response: 200, description: "Backup created")
        ]
    )]
    private function create() {
        $result = $this->service->performBackup('manual', $_SESSION['usuario_id']);
        $this->jsonResponse(true, $result);
    }

    #[OA\Post(
        path: "/php/api_backups.php?action=restore",
        tags: ["Backups"],
        summary: "Restore backup",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object", properties: [new OA\Property(property: "id", type: "integer")])),
        responses: [
            new OA\Response(response: 200, description: "Backup restored")
        ]
    )]
    private function restore($id) {
        global $pdo;
        $result = $this->service->restoreBackup($id, $_SESSION['usuario_id']);
        logSystemAction($pdo, getCurrentUserId(), 'Backup restaurado', '#' . $id . ' | ' . ($result['archivo'] ?? ''));
        $this->jsonResponse(true, $result);
    }

    #[OA\Delete(
        path: "/php/api_backups.php",
        tags: ["Backups"],
        summary: "Delete backup",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object", properties: [new OA\Property(property: "id", type: "integer")])),
        responses: [
            new OA\Response(response: 200, description: "Backup deleted")
        ]
    )]
    private function delete($id) {
        global $pdo;
        // Get backup info before delete for logging
        $backups = $this->service->getAllBackups();
        $archivo = '';
        foreach ($backups as $b) {
            if ($b['id'] == $id) {
                $archivo = $b['archivo'] ?? '';
                break;
            }
        }
        $this->service->deleteBackup($id);
        logSystemAction($pdo, getCurrentUserId(), 'Backup eliminado', '#' . $id . ' | ' . $archivo);
        $this->jsonResponse(true, null, 'Backup eliminado exitosamente');
    }

    #[OA\Get(
        path: "/php/api_backups.php?action=download",
        tags: ["Backups"],
        summary: "Download backup",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Backup ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "File download", content: new OA\MediaType(mediaType: "application/octet-stream"))
        ]
    )]
    private function download($id) {
        $file = $this->service->getDownloadPath($id);
        if (!$file) {
            http_response_code(404);
            die('Archivo no encontrado');
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
        header('Content-Length: ' . filesize($file['path']));
        readfile($file['path']);
        exit;
    }

    #[OA\Get(
        path: "/php/api_backups.php?action=status",
        tags: ["Backups"],
        summary: "Get backup status",
        responses: [
            new OA\Response(response: 200, description: "Status")
        ]
    )]
    private function status() {
        $data = $this->service->getAutoBackupStatus();
        $this->jsonResponse(true, $data);
    }

    #[OA\Post(
        path: "/php/api_backups.php?action=toggle_fallback",
        tags: ["Backups"],
        summary: "Toggle fallback backup",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object", properties: [new OA\Property(property: "enabled", type: "boolean")])),
        responses: [
            new OA\Response(response: 200, description: "Toggled")
        ]
    )]
    private function toggleFallback($enabled) {
        $this->service->setFallbackEnabled($enabled);
        $this->jsonResponse(true, ['enabled' => $enabled]);
    }

    private function jsonResponse($success, $data = [], $message = '') {
        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!$success && $message) $response['error'] = $message;

        if (is_array($data)) {
            $response = array_merge($response, $data);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
