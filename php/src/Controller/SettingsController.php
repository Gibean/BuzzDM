<?php
require_once __DIR__ . '/../Service/SettingsService.php';

class SettingsController {
    private $service;

    public function __construct() {
        $this->service = new SettingsService();
    }

    public function handleRequest() {
        // Ensure admin access
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            $this->jsonResponse(false, null, 'Acceso denegado. Solo administradores pueden modificar la configuración.');
        }

        try {
            // Check POST 'action' or GET 'action'
            $action = $_REQUEST['action'] ?? '';

            // Handle JSON input for raw body, but also POST fields for forms/uploads
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
                 $input = json_decode(file_get_contents('php://input'), true);
                 if ($input && isset($input['action'])) $action = $input['action'];
            } else {
                 $input = $_POST;
            }

            switch ($action) {
                case 'get_company_info':
                    $this->getCompanyInfo();
                    break;

                case 'update_company_info':
                    $this->updateCompanyInfo($input);
                    break;

                case 'upload_logo':
                    $this->uploadLogo();
                    break;

                default:
                    $this->jsonResponse(false, null, 'Acción no válida o no especificada.');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    private function getCompanyInfo() {
        $data = $this->service->getCompanyInfo();
        if ($data) {
             $this->jsonResponse(true, $data);
        } else {
             $this->jsonResponse(false, null, 'No se encontró información de la empresa.');
        }
    }

    private function updateCompanyInfo($input) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             throw new Exception('Método no permitido.');
        }
        $this->service->updateCompanyInfo($input);
        $this->jsonResponse(true, null, 'Información de la empresa actualizada exitosamente.');
    }

    private function uploadLogo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             throw new Exception('Método no permitido.');
        }
        $this->service->uploadLogo($_FILES['logoFile'] ?? null);
        $this->jsonResponse(true, null, 'Logo subido y actualizado exitosamente.');
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}
