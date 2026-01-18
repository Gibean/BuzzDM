<?php
require_once __DIR__ . '/../Service/ProductService.php';

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="BuzzDM API - Productos", version="1.0")
 */
class ProductController {
    private $service;

    public function __construct() {
        $this->service = new ProductService();
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        echo json_encode($response);
        exit;
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
                    $this->handlePost($input);
                    break;
                case 'PUT':
                    $this->handlePut($input);
                    break;
                case 'DELETE':
                    $this->handleDelete($input);
                    break;
                default:
                    $this->jsonResponse(false, [], 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    private function handleGet() {
        if (isset($_GET['id'])) {
            $product = $this->service->getProduct($_GET['id']);
            if ($product) {
                $this->jsonResponse(true, ['products' => [$product]]);
            } else {
                $this->jsonResponse(false, [], 'Producto no encontrado');
            }
        } else {
            // Pass all GET parameters as filters
            $result = $this->service->listProducts($_GET);
            $this->jsonResponse(true, $result);
        }
    }

    private function handlePost($input) {
        if (isset($input['action']) && $input['action'] === 'restore') {
            $id = $input['id'] ?? null;
            if (!$id) throw new Exception("ID Requerido para restaurar");

            $this->service->restoreProduct($id);
            $this->jsonResponse(true, [], 'Producto restaurado exitosamente');
        } else {
            $id = $this->service->createProduct($input);
            $this->jsonResponse(true, ['id' => $id], 'Producto creado exitosamente');
        }
    }

    private function handlePut($input) {
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) throw new Exception("ID Requerido para actualizar");

        $this->service->updateProduct($id, $input);
        $this->jsonResponse(true, [], 'Producto actualizado exitosamente');
    }

    private function handleDelete($input) {
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) throw new Exception("ID Requerido para eliminar");

        $this->service->deleteProduct($id);
        $this->jsonResponse(true, [], 'Producto eliminado exitosamente');
    }
}
