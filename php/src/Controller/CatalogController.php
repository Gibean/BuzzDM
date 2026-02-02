<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Catalogs", description: "Operations related to Catalogs (Categories, Brands, etc.)")]
class CatalogController {
    private $service;

    public function __construct() {
        $this->service = new CatalogService();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        try {
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->createItem($input);
                    break;
                case 'PUT':
                    $this->updateItem($input);
                    break;
                case 'DELETE':
                    $this->deleteItem();
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_catalogs.php",
        tags: ["Catalogs"],
        summary: "Get catalogs or items",
        parameters: [
            new OA\Parameter(name: "type", in: "query", description: "Type (resumen)", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "catalog", in: "query", description: "Strict catalog name", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Catalog data")
        ]
    )]
    private function handleGet() {
        $type = $_GET['type'] ?? null;
        $catalog = $_GET['catalog'] ?? null;

        if ($type === 'resumen') {
            $this->jsonResponse(true, ['catalogos' => $this->service->getSummary()]);
        } elseif ($catalog) {
            $this->jsonResponse(true, ['data' => $this->service->getCatalog($catalog)]);
        } else {
            $data = $this->service->getAllCatalogs();
            $this->jsonResponse(true, $data);
        }
    }

    #[OA\Post(
        path: "/php/api_catalogs.php",
        tags: ["Catalogs"],
        summary: "Create catalog item",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Item created")
        ]
    )]
    private function createItem($input) {
        $catalog = $input['catalog'] ?? null;
        $result = $this->service->createItem($catalog, $input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Put(
        path: "/php/api_catalogs.php",
        tags: ["Catalogs"],
        summary: "Update catalog item",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Item updated")
        ]
    )]
    private function updateItem($input) {
        $catalog = $input['catalog'] ?? null;
        $id = $input['id'] ?? null;
        $result = $this->service->updateItem($catalog, $id, $input);
        $this->jsonResponse(true, $result);
    }

    #[OA\Delete(
        path: "/php/api_catalogs.php",
        tags: ["Catalogs"],
        summary: "Delete catalog item",
        parameters: [
            new OA\Parameter(name: "catalog", in: "query", description: "Catalog name", required: true, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "id", in: "query", description: "Item ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Item deleted")
        ]
    )]
    private function deleteItem() {
        $catalog = $_GET['catalog'] ?? null;
        $id = $_GET['id'] ?? null;
        $result = $this->service->deleteItem($catalog, $id);
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
