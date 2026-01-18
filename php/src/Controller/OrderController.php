<?php
require_once __DIR__ . '/../Service/OrderService.php';

class OrderController {
    private $service;

    public function __construct() {
        $this->service = new OrderService();
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');

        $response = ['success' => $success];
        if ($message) $response['message'] = $message;

        // Merge data keys directly into response root for compatibility with specific frontend expectations
        // (e.g. data.orders, data.products)
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }
        echo json_encode($response);
        exit;
    }

    public function handleRequest() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(false, [], 'No autorizado');
        }

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
        if (isset($_GET['action']) && $_GET['action'] === 'get_products' && isset($_GET['order_id'])) {
            $products = $this->service->getOrderProducts($_GET['order_id']);
            $this->jsonResponse(true, ['products' => $products]);
        } elseif (isset($_GET['id'])) {
            $order = $this->service->getOrder($_GET['id']);
            $this->jsonResponse(true, ['order' => $order]);
        } else {
            $result = $this->service->listOrders($_GET);
            $this->jsonResponse(true, $result);
        }
    }

    private function handlePost($input) {
        // Check for Restore Action
        if (isset($input['action']) && $input['action'] === 'restore') {
            if (empty($input['id'])) throw new Exception("ID necesario para restaurar");
            $this->service->restoreOrder($input['id']);
            $this->jsonResponse(true, [], 'Pedido restaurado exitosamente.');
            return;
        }

        // Create Order
        $result = $this->service->createOrder($input);
        $this->jsonResponse(true, ['id' => $result['id']], 'Pedido creado exitosamente.');
    }

    private function handlePut($input) {
        if (!isset($_GET['id'])) throw new Exception("ID de pedido requerido");

        // Merge ID into input for convenience if needed, or just pass separately
        $id = intval($_GET['id']);
        $this->service->updateOrder($id, $input);

        $this->jsonResponse(true, [], 'Pedido actualizado exitosamente.');
    }

    private function handleDelete($input) {
        // DELETE usually comes with ID in query string for strict REST, but legacy might send body
        // Original code: $input['id'] form body
        // Let's support both
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) throw new Exception("ID de pedido requerido");

        $this->service->deleteOrder($id);
        $this->jsonResponse(true, [], 'Pedido eliminado exitosamente.');
    }
}
