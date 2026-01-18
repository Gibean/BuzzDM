<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Auth", description: "Authentication Operations")]
class AuthController {
    private $service;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->service = new AuthService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? $_POST['action'] ?? null;

        try {
            switch ($action) {
                case 'login':
                    $email = trim($_POST['email'] ?? '');
                    $password = $_POST['password'] ?? '';
                    $result = $this->service->login($email, $password);
                    $this->jsonResponse(true, $result);
                    break;

                case 'forgot_password':
                    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
                    $result = $this->service->forgotPassword($email);
                    $this->jsonResponse(true, $result);
                    break;

                case 'reset_password':
                    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                    $password = $_POST['password'] ?? '';
                    $confirmPassword = $_POST['confirmPassword'] ?? '';
                    $result = $this->service->resetPassword($token, $password, $confirmPassword);
                    $this->jsonResponse(true, $result);
                    break;

                case 'logout':
                    $result = $this->service->logout();
                    $this->jsonResponse(true, $result);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Acción no válida');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Post(
        path: "/php/api_auth.php",
        tags: ["Auth"],
        summary: "Authentication Actions",
        description: "Login, Logout, Forgot Password, Reset Password via 'action' param",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/x-www-form-urlencoded",
                schema: new OA\Schema(
                    required: ["action"],
                    properties: [
                        new OA\Property(property: "action", type: "string", enum: ["login", "logout", "forgot_password", "reset_password"]),
                        new OA\Property(property: "email", type: "string", description: "For login/forgot"),
                        new OA\Property(property: "password", type: "string", description: "For login/reset"),
                        new OA\Property(property: "confirmPassword", type: "string", description: "For reset"),
                        new OA\Property(property: "token", type: "string", description: "For reset")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Action result")
        ]
    )]
    private function authActions() {
        // Placeholder for OpenAPI to attach to
    }

    private function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');

        if (!$success) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            echo json_encode($data);
        }
        exit;
    }
}
