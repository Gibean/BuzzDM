<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Kits", description: "Operations related to Kit Components")]
class KitController {
    private $service;

    public function __construct() {
        $this->service = new KitService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    $this->getComponents();
                    break;
                case 'POST':
                    $this->addComponent();
                    break;
                case 'PUT':
                    $this->updateComponent();
                    break;
                case 'DELETE':
                    $this->deleteComponent();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_kits.php",
        tags: ["Kits"],
        summary: "Get kit components",
        parameters: [
            new OA\Parameter(name: "kit_id", in: "query", description: "Kit ID", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Component list")
        ]
    )]
    private function getComponents() {
        $result = $this->service->getComponents($_GET['kit_id'] ?? null);
        $this->jsonResponse(true, $result);
    }

    #[OA\Post(
        path: "/php/api_kits.php",
        tags: ["Kits"],
        summary: "Add component to kit",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Component added")
        ]
    )]
    private function addComponent() {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->addComponent($input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Put(
        path: "/php/api_kits.php",
        tags: ["Kits"],
        summary: "Update component",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Component ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Component updated")
        ]
    )]
    private function updateComponent() {
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->service->updateComponent($_GET['id'] ?? null, $input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Delete(
        path: "/php/api_kits.php",
        tags: ["Kits"],
        summary: "Remove component from kit",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Component ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Component removed")
        ]
    )]
    private function deleteComponent() {
        $result = $this->service->deleteComponent($_GET['id'] ?? null);
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
