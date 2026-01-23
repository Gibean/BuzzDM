<?php
/**
 * TwoFactorController - API Controller for 2FA Management
 * Endpoints for setup, enable, disable, and verification
 */

use OpenApi\Attributes as OA;

#[OA\Tag(name: "TwoFactor", description: "Two-Factor Authentication Management")]
class TwoFactorController {
    private $service;

    public function __construct() {
        $this->service = new TwoFactorService();
    }

    public function handleRequest() {
        // Require authentication
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(false, [], 'No autorizado', 401);
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            switch ($method) {
                case 'GET':
                    $this->getSetupData();
                    break;
                case 'POST':
                    $this->handleAction($input);
                    break;
                default:
                    $this->jsonResponse(false, [], 'Método no permitido', 405);
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage(), 500);
        }
    }

    #[OA\Get(
        path: "/php/api_2fa.php",
        tags: ["TwoFactor"],
        summary: "Get 2FA setup data (QR code and secret)",
        responses: [
            new OA\Response(response: 200, description: "Setup data with QR code")
        ]
    )]
    private function getSetupData() {
        $userId = $_SESSION['usuario_id'];

        // Get user email from database (more reliable than session)
        $userRepo = new UserRepository();
        $user = $userRepo->findById($userId);
        $email = $user['email'] ?? '';

        $isEnabled = $this->service->isEnabled($userId);

        if ($isEnabled) {
            // Already enabled, just return status
            $this->jsonResponse(true, [
                'is_enabled' => true,
                'message' => '2FA ya está activado en tu cuenta.'
            ]);
        }

        // Generate new secret for setup (unique per user request)
        $secret = $this->service->generateSecret();

        // Store temporarily in session for verification (keyed by user ID)
        $_SESSION['2fa_temp_secret_' . $userId] = $secret;

        // Generate QR code
        $qrSvg = $this->service->getQRCodeSvg($email, $secret);

        $this->jsonResponse(true, [
            'is_enabled' => false,
            'qr_svg' => base64_encode($qrSvg),
            'secret' => $secret,
            'message' => 'Escanea el código QR con Google Authenticator'
        ]);
    }

    #[OA\Post(
        path: "/php/api_2fa.php",
        tags: ["TwoFactor"],
        summary: "Enable, disable, or verify 2FA",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "action", type: "string", enum: ["enable", "disable", "verify"]),
                    new OA\Property(property: "code", type: "string"),
                    new OA\Property(property: "password", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Action result")
        ]
    )]
    private function handleAction($input) {
        $action = $input['action'] ?? '';

        switch ($action) {
            case 'enable':
                $this->enableTwoFactor($input);
                break;
            case 'disable':
                $this->disableTwoFactor($input);
                break;
            case 'verify':
                $this->verifyCode($input);
                break;
            default:
                throw new Exception('Acción no válida');
        }
    }

    private function enableTwoFactor($input) {
        $userId = $_SESSION['usuario_id'];
        $secret = $_SESSION['2fa_temp_secret_' . $userId] ?? '';
        $code = $input['code'] ?? '';

        if (empty($secret)) {
            throw new Exception('Sesión expirada. Por favor, recarga la página para generar un nuevo código QR.');
        }

        if (empty($code) || strlen($code) !== 6) {
            throw new Exception('Ingresa un código de 6 dígitos válido.');
        }

        if ($this->service->enable($userId, $secret, $code)) {
            // Clear temp secret
            unset($_SESSION['2fa_temp_secret_' . $userId]);

            $this->jsonResponse(true, [
                'is_enabled' => true
            ], '¡2FA activado exitosamente! Tu cuenta ahora es más segura.');
        } else {
            throw new Exception('Código inválido. Verifica que el código coincida con tu app.');
        }
    }

    private function disableTwoFactor($input) {
        $userId = $_SESSION['usuario_id'];
        $password = $input['password'] ?? '';

        if (empty($password)) {
            throw new Exception('Debes ingresar tu contraseña para desactivar 2FA.');
        }

        // Verify password
        $userRepo = new UserRepository();
        $user = $userRepo->findById($userId);

        if (!password_verify($password, $user['password'])) {
            throw new Exception('Contraseña incorrecta.');
        }

        if ($this->service->disable($userId)) {
            $this->jsonResponse(true, [
                'is_enabled' => false
            ], '2FA desactivado. Tu cuenta ya no requiere código de autenticación.');
        } else {
            throw new Exception('Error al desactivar 2FA.');
        }
    }

    private function verifyCode($input) {
        $userId = $_SESSION['usuario_id'];
        $code = $input['code'] ?? '';

        if (empty($code)) {
            throw new Exception('Código requerido.');
        }

        $secret = $this->service->getSecret($userId);

        if (!$secret) {
            throw new Exception('2FA no configurado para este usuario.');
        }

        if ($this->service->verifyCode($secret, $code)) {
            $this->jsonResponse(true, [], 'Código válido.');
        } else {
            throw new Exception('Código inválido o expirado.');
        }
    }

    private function jsonResponse($success, $data = [], $message = '', $httpCode = 200) {
        http_response_code($httpCode);
        header('Content-Type: application/json');

        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!empty($data)) $response['data'] = $data;

        echo json_encode($response);
        exit;
    }
}
