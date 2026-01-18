<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Logs", description: "Audit Logs")]
class LogController {
    private $service;

    public function __construct() {
        $this->service = new LogService();
    }

    public function handleRequest() {
        // Admin only check
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            $this->jsonResponse(false, null, 'Acceso denegado');
        }

        try {
            $this->getLogs();
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
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
            'dateTo' => $_GET['dateTo'] ?? ''
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
            echo json_encode($response);
        }
        exit;
    }
}
