<?php
require_once __DIR__ . '/../Service/ShipmentService.php';

/**
 * ShipmentController - HTTP request handler for shipments
 */
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
                    $filters = [
                        'search' => $_GET['search'] ?? '',
                        'status' => $_GET['statusFilter'] ?? '',
                        'sortBy' => $_GET['sortBy'] ?? 'date'
                    ];
                    $result = $this->service->getAllShipments($filters);
                    $this->jsonResponse(true, $result);
                    break;

                case 'PUT':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $this->service->updateShipmentStatus(
                        $input['id'] ?? null,
                        $input['status'] ?? '',
                        $input['trackingNumber'] ?? null
                    );
                    $this->jsonResponse(true, $result);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
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
            $response = ['success' => true];
            if (is_array($data)) {
                $response = array_merge($response, ['data' => $data]);
            }
            echo json_encode($response);
        }
        exit;
    }
}
