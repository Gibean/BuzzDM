<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

// resolveLogEmpresaId() vive en el helper de bitácora. Los otros controladores
// lo cargan en su línea 3; este necesita lo mismo para poder atribuir la fila a
// una empresa, ya que logs.empresa_id es NOT NULL.
require_once __DIR__ . '/../helpers/logger.php';

#[OA\Tag(name: "Logs", description: "Audit Logs")]
class LogController {
    private $service;

    public function __construct() {
        $this->service = new LogService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    // Admin only for reading logs
                    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
                        $this->jsonResponse(false, null, 'Acceso denegado');
                    }
                    $this->getLogs();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    private function handlePost() {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        switch ($action) {
            case 'log_inactivity_logout':
                $this->logInactivityLogout($input);
                break;
            default:
                $this->jsonResponse(false, null, 'Acción no válida');
        }
    }

    #[OA\Post(
        path: "/php/api_logs.php",
        tags: ["Logs"],
        summary: "Log inactivity logout",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "action", type: "string", example: "log_inactivity_logout"),
                    new OA\Property(property: "minutes", type: "integer", example: 30)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Log created successfully")
        ]
    )]
    private function logInactivityLogout($input) {
        // User must be logged in to log inactivity
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(false, null, 'Usuario no autenticado');
            return;
        }

        $userId = $_SESSION['usuario_id'];
        $userName = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $minutes = $input['minutes'] ?? 30;

        // Log to database
        global $pdo;

        // `logs` solo tiene id, empresa_id, usuario_id, accion, detalles y fecha.
        // Este INSERT pedia ademas `usuario`, `ip_address`, `user_agent` y
        // `fecha_hora`, que no existen: el POST fallaba con "Unknown column" y
        // devolvia HTTP 400. El nombre de usuario, la IP y el user agent viajan
        // dentro de detalles, que es texto libre.
        $stmt = $pdo->prepare("
            INSERT INTO logs (empresa_id, usuario_id, accion, detalles, fecha)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $details = json_encode([
            'mensaje' => "Cierre automatico por inactividad ({$minutes} minutos)",
            'usuario_nombre' => $userName,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ], JSON_UNESCAPED_UNICODE);

        $stmt->execute([
            resolveLogEmpresaId(),
            $userId,
            'logout_inactivity',
            $details,
        ]);

        $this->jsonResponse(true, ['logged' => true], 'Log de inactividad registrado');
    }

    #[OA\Get(
        path: "/php/api_logs.php",
        tags: ["Logs"],
        summary: "Get system logs",
        parameters: [
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", description: "Items per page", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "search", in: "query", description: "Search query", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "dateFrom", in: "query", description: "Date from", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "dateTo", in: "query", description: "Date to", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Logs data")
        ]
    )]
    private function getLogs() {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 10;

        $filters = [
            'search' => $_GET['search'] ?? '',
            'dateFrom' => $_GET['dateFrom'] ?? '',
            'dateTo' => $_GET['dateTo'] ?? '',
            'action' => $_GET['action'] ?? ''
        ];

        $result = $this->service->getSystemLogs($filters, $page, $limit);
        $this->jsonResponse(true, $result);
    }

    private function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');

        if (!$success) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            // New standard: success: true, data: { ... }
            $response = ['success' => true];
            if ($data) {
                // Merge data directly if possible? No, put in 'data' key usually.
                // standard: { success: true, data: { logs: [], pagination: {} } }
                // My repos returns ['data' => [], 'pagination' => []]
                // So merged response: { success: true, data: [], pagination: [] }
                $response = array_merge($response, $data);
            }
            if ($message) {
                $response['message'] = $message;
            }
            echo json_encode($response);
        }
        exit;
    }
}
