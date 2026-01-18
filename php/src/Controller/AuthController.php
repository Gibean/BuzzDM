<?php
require_once __DIR__ . '/../Service/AuthService.php';

/**
 * AuthController - HTTP request handler for authentication
 */
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
