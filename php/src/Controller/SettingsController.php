<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Settings", description: "System Settings")]
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

            // CSRF Validation for write operations
            if ($action === 'update_company_info' || $action === 'upload_logo') {
                $token = $input['csrf_token'] ?? '';
                if (!validateCsrfToken($token)) {
                    $this->jsonResponse(false, null, 'Error de seguridad: Token CSRF inválido o ausente.');
                }
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

    #[OA\Get(
        path: "/php/api_settings.php?action=get_company_info",
        tags: ["Settings"],
        summary: "Get company info",
        responses: [
            new OA\Response(response: 200, description: "Company info")
        ]
    )]
    private function getCompanyInfo() {
        $data = $this->service->getCompanyInfo();
        if ($data) {
             $this->jsonResponse(true, $data);
        } else {
             $this->jsonResponse(false, null, 'No se encontró información de la empresa.');
        }
    }

    #[OA\Post(
        path: "/php/api_settings.php?action=update_company_info",
        tags: ["Settings"],
        summary: "Update company info",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Info updated")
        ]
    )]
    private function updateCompanyInfo($input) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             throw new Exception('Método no permitido.');
        }
        $this->service->updateCompanyInfo($input);
        $this->jsonResponse(true, null, 'Información de la empresa actualizada exitosamente.');
    }

    #[OA\Post(
        path: "/php/api_settings.php?action=upload_logo",
        tags: ["Settings"],
        summary: "Upload logo",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["logoFile"],
                    properties: [new OA\Property(property: "logoFile", type: "string", format: "binary")]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Logo uploaded")
        ]
    )]
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
