<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Locations", description: "Operations related to Warehouse Locations")]
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
                    $this->listLocations();
                    break;
                case 'POST':
                    $this->createLocation();
                    break;
                case 'PUT':
                    $this->updateLocation();
                    break;
                case 'DELETE':
                    $this->deleteLocation();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_locations.php",
        tags: ["Locations"],
        summary: "List locations",
        parameters: [
            new OA\Parameter(name: "almacen_id", in: "query", description: "Warehouse ID", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Location list")
        ]
    )]
    private function listLocations() {
        $locations = $this->service->getByWarehouse($_GET['almacen_id'] ?? null);
        $this->jsonResponse(true, ['ubicaciones' => $locations]);
    }

    #[OA\Post(
        path: "/php/api_locations.php",
        tags: ["Locations"],
        summary: "Create a location",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Location created")
        ]
    )]
    private function createLocation() {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->create($input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Put(
        path: "/php/api_locations.php",
        tags: ["Locations"],
        summary: "Update a location",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Location ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Location updated")
        ]
    )]
    private function updateLocation() {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->update($_GET['id'] ?? null, $input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Delete(
        path: "/php/api_locations.php",
        tags: ["Locations"],
        summary: "Delete a location",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Location ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Location deleted")
        ]
    )]
    private function deleteLocation() {
        $result = $this->service->delete($_GET['id'] ?? null);
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
                $response = array_merge($response, $data);
            }
            echo json_encode($response);
        }
        exit;
    }
}
