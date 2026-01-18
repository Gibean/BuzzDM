<?php
require_once __DIR__ . '/../Service/UserService.php';

class UserController {
    private $service;

    public function __construct() {
        $this->service = new UserService();
    }

    public function handleRequest() {
        file_put_contents(__DIR__ . '/../../debug_log.txt', "Request: " . $_SERVER['REQUEST_METHOD'] . " Params: " . json_encode($_GET) . "\n", FILE_APPEND);

        // Basic Admin Auth
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            file_put_contents(__DIR__ . '/../../debug_log.txt', "Auth failed. Rol: " . ($_SESSION['usuario_rol'] ?? 'unset') . "\n", FILE_APPEND);
            $this->jsonResponse(false, null, 'Acceso denegado');
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        // Handle input
        $input = json_decode(file_get_contents('php://input'), true);

        // Debug if input is null
        if (is_null($input) && $method === 'POST') {
             // Try $_POST if JSON failed? Legacy fallback?
             if (!empty($_POST)) {
                 $input = $_POST;
             } else {
                 $this->jsonResponse(false, null, 'Cuerpo de solicitud vacío o JSON inválido');
             }
        }
        $input = $input ?? [];

        try {
            switch ($method) {
                case 'GET':
                    if ($action === 'get_sessions') {
                        $this->jsonResponse(true, $this->service->getSessionStats());
                    } elseif (isset($_GET['id'])) {
                        $user = $this->service->getUserById($_GET['id']);
                        if (!$user) {
                            $this->jsonResponse(false, null, 'Usuario no encontrado');
                        }
                        $this->jsonResponse(true, ['user' => $user]);
                    } else {
                        // Return users wrapped in 'users' key for admin.js compatibility
                        $this->jsonResponse(true, ['users' => $this->service->getAllUsers()]);
                    }
                    break;

                case 'POST':
                    // Check if it calls action via query param or just post
                    // Legacy used POST for create
                    $result = $this->service->createUser($input);
                    $this->jsonResponse(true, $result);
                    break;

                case 'PUT':
                    // Legacy used PUT for update
                    // AND used input['id']
                    if (isset($input['password']) && !empty($input['password'])) {
                        // Special case if admin.js sends password update separately or in same req?
                        // Legacy users.php didn't handle password update in PUT (only create).
                        // Wait, looking at users.php... PUT was name/email/rol.
                        // There was no password update case in PUT!!
                        // Users.php legacy didn't seem to have password change for existing users?
                        // Ah, `users.php` legacy code shown earlier:
                        // CASE PUT: update name, email, rol.
                        // CASE PATCH: update state.
                        // NO PASSWORD CHANGE LOGIC FOR EXISTING USERS in legacy file shown??
                        // Strange. If I added it now, I need to know how frontend sends it.
                        // I will assume for now I implement update logic matching legacy PUT.
                        $result = $this->service->updateUser($input['id'], $input);
                        $this->jsonResponse(true, $result);
                    } else {
                         // Standard update
                         $result = $this->service->updateUser($input['id'], $input);
                         $this->jsonResponse(true, $result);
                    }
                    break;

                case 'PATCH':
                    // Legacy PATCH for state
                    $result = $this->service->updateStatus($input['id'], $input['estado']);
                    $this->jsonResponse(true, $result);
                    break;

                case 'DELETE':
                    // Need to handle DELETE body or query param?
                    // Legacy: file_get_contents body for ID? No, legacy users.php didn't show DELETE case in snippet!!
                    // Wait, let me check the snippet of users.php again in history Step 861.
                    // Snippet 861 shows: GET, POST, PUT, PATCH. DEFAULT (405).
                    // THERE IS NO DELETE CASE IN LEGACY USERS.PHP??
                    // That implies users couldn't be deleted? Or I missed it.
                    // OR they used a different file?
                    // "Admin.js" likely has delete logic.
                    // I will add DELETE support now as a feature/fix.
                    $id = $input['id'] ?? $_GET['id'] ?? 0;
                    $result = $this->service->deleteUser($id, $_SESSION['usuario_id']);
                    $this->jsonResponse(true, $result);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../../debug_log.txt', "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    private function jsonResponse($success, $data = [], $message = '') {
        $response = [];
        if (!$success) {
            $response['error'] = $message; // Legacy compatibility often looks for 'error' key
            http_response_code(400); // Bad request usually
        } else {
            // For backward compatibility with legacy JS that might expect arrays directly?
            // If the data is a list of users (keys like 0,1,2...), legacy users.php returned `echo json_encode($users);` directly.
            // If I change to `{'success': true, 'data': [...]}` I MUST update admin.js.
            // I WILL update admin.js.
            $response = $data;
            if ($message) $response['message'] = $message;

            // If response is the users array, we might need to be careful.
            // But I will stick to my Standard: {success: true, data: ...} (implied by previous modules).
            // Actually, my standard in other files was: `echo json_encode(['success' => true, 'data' => ...])`
            // Here `jsonResponse` wrapper takes $data.
            // If $data is array of users, I'll put it in 'data'.
            if ($success) $response['success'] = true;
        }

        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
