<?php
require_once __DIR__ . '/../Service/UserService.php';
require_once __DIR__ . '/../helpers/logger.php';

// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Users", description: "Operations related to Users")]
class UserController {
    private $service;

    public function __construct() {
        $this->service = new UserService();
    }

    public function handleRequest() {
        // Basic Admin Auth
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            $this->jsonResponse(false, null, 'Acceso denegado');
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);

        // Debug logging removed after fix

        if (is_null($input) && $method === 'POST' && !empty($_POST)) $input = $_POST;
        $input = $input ?? [];

        // CSRF Validation for state-changing methods
        if ($method !== 'GET') {
            $token = $input['csrf_token'] ?? '';
            if (!validateCsrfToken($token)) {
                $this->jsonResponse(false, null, 'Error de seguridad: Token CSRF inválido o ausente.');
            }
        }

        try {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['action']) && $_GET['action'] === 'get_sessions') {
                        $this->getSessionStats();
                    } elseif (isset($_GET['id'])) {
                        $this->getUser($_GET['id']);
                    } else {
                        $this->getAllUsers();
                    }
                    break;
                case 'POST':
                    $this->createUser($input);
                    break;
                case 'PUT':
                    $this->updateUser($input);
                    break;
                case 'DELETE':
                    $this->deleteUser();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_users.php",
        tags: ["Users"],
        summary: "List all users",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "User ID", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User list")
        ]
    )]
    private function getAllUsers() {
        $users = $this->service->getAllUsers();
        // Return with 'users' key as expected by frontend
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    private function getUser($id) {
        $user = $this->service->getUserById($id);
        if ($user) $this->jsonResponse(true, $user);
        else $this->jsonResponse(false, null, 'Usuario no encontrado');
    }

    private function getSessionStats() {
        $stats = $this->service->getSessionStats();
        header('Content-Type: application/json');
        echo json_encode($stats);
        exit;
    }

    #[OA\Post(
        path: "/php/api_users.php",
        tags: ["Users"],
        summary: "Create a new user",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["nombre", "email", "password", "rol"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Carlos Rodríguez", description: "Full name"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "carlos@empresa.com", description: "Email address"),
                    new OA\Property(property: "password", type: "string", example: "SecurePass123!", description: "Password"),
                    new OA\Property(property: "rol", type: "string", enum: ["admin", "supervisor", "operador"], example: "supervisor", description: "Role"),
                    new OA\Property(property: "activo", type: "boolean", example: true, description: "Active status")
                ],
                example: [
                    "nombre" => "Carlos Rodríguez",
                    "email" => "carlos@empresa.com",
                    "password" => "SecurePass123!",
                    "rol" => "supervisor",
                    "activo" => true
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "User created")
        ]
    )]
    private function createUser($data) {
        global $pdo;
        $result = $this->service->createUser($data);
        $id = $result['id'];
        logSystemAction($pdo, getCurrentUserId(), 'Usuario creado', '#' . $id . ' | ' . ($data['nombre'] ?? '') . ' (' . ($data['email'] ?? '') . ') | Rol: ' . ($data['rol'] ?? ''));
        $this->jsonResponse(true, ['id' => $id], 'Usuario creado exitosamente');
    }

    #[OA\Put(
        path: "/php/api_users.php",
        tags: ["Users"],
        summary: "Update a user",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "User ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Carlos Actualizado"),
                    new OA\Property(property: "email", type: "string", example: "nuevo.email@empresa.com"),
                    new OA\Property(property: "password", type: "string", example: "NuevoPass456!", description: "Leave empty to keep current"),
                    new OA\Property(property: "rol", type: "string", enum: ["admin", "supervisor", "operador"], example: "admin"),
                    new OA\Property(property: "activo", type: "boolean", example: true)
                ],
                example: [
                    "nombre" => "Carlos Actualizado",
                    "rol" => "admin",
                    "activo" => true
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "User updated")
        ]
    )]
    private function updateUser($data) {
        global $pdo;
        $id = $_GET['id'] ?? $data['id'] ?? null;
        if (!$id) throw new Exception("ID requerido");
        $user = $this->service->getUserById($id);
        $this->service->updateUser($id, $data);
        logSystemAction($pdo, getCurrentUserId(), 'Usuario actualizado', '#' . $id . ' | ' . ($user['nombre'] ?? '') . ' (' . ($user['email'] ?? '') . ')');
        $this->jsonResponse(true, null, 'Usuario actualizado exitosamente');
    }

    #[OA\Delete(
        path: "/php/api_users.php",
        tags: ["Users"],
        summary: "Delete a user",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "User ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "User deleted")
        ]
    )]
    private function deleteUser() {
        global $pdo;
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("ID requerido");
        // Get user info before delete
        $user = $this->service->getUserById($id);
        $this->service->deleteUser($id, getCurrentUserId());
        logSystemAction($pdo, getCurrentUserId(), 'Usuario eliminado', '#' . $id . ' | ' . ($user['nombre'] ?? '') . ' (' . ($user['email'] ?? '') . ')');
        $this->jsonResponse(true, null, 'Usuario eliminado exitosamente');
    }

    private function jsonResponse($success, $data, $message = '') {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
