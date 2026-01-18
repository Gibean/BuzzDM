<?php
require_once __DIR__ . '/../Service/BackupService.php';

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
                    } elseif ($action === 'toggle_fallback') { // Can be a get param or post
                         // Also check if action is in query string but payload in body (admin.js toggle)
                         // Actually the JS uses query param ?action=toggle but POST body
                         $enabled = $input['enabled'] ?? true;
                         $this->toggleFallback($enabled);
                    } else {
                         // Check query param if payload didn't specify 'create' etc
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

    private function list() {
        $backups = $this->service->getAllBackups();
        $this->jsonResponse(true, ['backups' => $backups]);
    }

    private function create() {
        $result = $this->service->performBackup('manual', $_SESSION['usuario_id']);
        $this->jsonResponse(true, $result);
    }

    private function restore($id) {
        $result = $this->service->restoreBackup($id, $_SESSION['usuario_id']);
        $this->jsonResponse(true, $result);
    }

    private function delete($id) {
        $this->service->deleteBackup($id);
        $this->jsonResponse(true, null, 'Backup eliminado exitosamente');
    }

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

    private function status() {
        $data = $this->service->getAutoBackupStatus();
        $this->jsonResponse(true, $data);
    }

    private function toggleFallback($enabled) {
        $this->service->setFallbackEnabled($enabled);
        $this->jsonResponse(true, ['enabled' => $enabled]);
    }

    private function jsonResponse($success, $data = [], $message = '') {
        // If data has keys, merge them to root or keep in 'data' key?
        // Admin.js expects root keys like 'backups', 'autoBackup', etc.
        // So I should pass array directly if it's already structured, or wrap it.
        // My Service methods performBackup returns array with 'backup' key.
        // status returns array with 'autoBackup' key.
        // So I will merge $data into the response array.

        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!$success && $message) $response['error'] = $message; // For compatibility

        if (is_array($data)) {
            $response = array_merge($response, $data);
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
