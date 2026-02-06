<?php
// Autoload handled by composer
require_once __DIR__ . '/../helpers/logger.php';

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Shipments", description: "Operations related to Shipments")]
class ShipmentController {
    private $service;

    public function __construct() {
        $this->service = new ShipmentService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $this->getAllShipments();
                    break;
                case 'PUT':
                    $this->updateShipmentStatus();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_shipments.php",
        tags: ["Shipments"],
        summary: "List shipments",
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Search", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "statusFilter", in: "query", description: "Status filter", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "sortBy", in: "query", description: "Sort field", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Shipment list")
        ]
    )]
    private function getAllShipments() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['statusFilter'] ?? '',
            'sortBy' => $_GET['sortBy'] ?? 'date'
        ];
        $result = $this->service->getAllShipments($filters);
        $this->jsonResponse(true, $result);
    }

    #[OA\Put(
        path: "/php/api_shipments.php",
        tags: ["Shipments"],
        summary: "Update shipment status",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Status updated")
        ]
    )]
    private function updateShipmentStatus() {
        global $pdo;
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->updateShipmentStatus(
            $input['id'] ?? null,
            $input['status'] ?? '',
            $input['trackingNumber'] ?? null
        );
        logSystemAction($pdo, getCurrentUserId(), 'Envío actualizado', '#' . ($input['id'] ?? '') . ' | Estado: ' . ($input['status'] ?? '') . ($input['trackingNumber'] ? ' | Tracking: ' . $input['trackingNumber'] : ''));
        $this->jsonResponse(true, $result);
    }

    private function jsonResponse($success, $data = null, $message = '') {
        header('Content-Type: application/json');

        if (!$success) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            $response = ['success' => true];
            if (is_array($data)) {
                $response = array_merge($response, ['data' => $data]);
            }
            echo json_encode($response);
        }
        exit;
    }
}
