<?php
require_once __DIR__ . '/../Service/LocationService.php';

/**
 * LocationController - HTTP request handler for warehouse locations
 */
class LocationController {
    private $service;

    public function __construct() {
        $this->service = new LocationService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $locations = $this->service->getByWarehouse($_GET['almacen_id'] ?? null);
                    $this->jsonResponse(true, ['ubicaciones' => $locations]);
                    break;

                case 'POST':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $this->service->create($input);
                    $this->jsonResponse(true, $result);
                    break;

                case 'PUT':
                    $input = json_decode(file_get_contents('php://input'), true);
                    $result = $this->service->update($_GET['id'] ?? null, $input);
                    $this->jsonResponse(true, $result);
                    break;

                case 'DELETE':
                    $result = $this->service->delete($_GET['id'] ?? null);
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
                $response = array_merge($response, $data);
            }
            echo json_encode($response);
        }
        exit;
    }
}
