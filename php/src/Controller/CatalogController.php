<?php
require_once __DIR__ . '/../Service/CatalogService.php';

/**
 * CatalogController - HTTP request handler for catalogs
 */
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
                    $catalog = $input['catalog'] ?? null;
                    $result = $this->service->createItem($catalog, $input);
                    $this->jsonResponse(true, $result);
                    break;

                case 'PUT':
                    $catalog = $input['catalog'] ?? null;
                    $id = $input['id'] ?? null;
                    $result = $this->service->updateItem($catalog, $id, $input);
                    $this->jsonResponse(true, $result);
                    break;

                case 'DELETE':
                    $catalog = $_GET['catalog'] ?? null;
                    $id = $_GET['id'] ?? null;
                    $result = $this->service->deleteItem($catalog, $id);
                    $this->jsonResponse(true, $result);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    private function handleGet() {
        $type = $_GET['type'] ?? null;
        $catalog = $_GET['catalog'] ?? null;

        if ($type === 'resumen') {
            // For dropdowns in other modules
            $this->jsonResponse(true, ['catalogos' => $this->service->getSummary()]);
        } elseif ($catalog) {
            // Specific catalog
            $this->jsonResponse(true, ['data' => $this->service->getCatalog($catalog)]);
        } else {
            // All catalogs for management page
            $data = $this->service->getAllCatalogs();
            $this->jsonResponse(true, $data);
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
