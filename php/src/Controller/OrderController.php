<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Orders", description: "Operations related to Orders")]
class OrderController {
    private $service;

    public function __construct() {
        $this->service = new OrderService();
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

    #[OA\Get(
        path: "/php/api_orders.php",
        tags: ["Orders"],
        summary: "List orders or get details",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Order ID", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "action", in: "query", description: "Action 'get_products'", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "order_id", in: "query", description: "Order ID for products", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Order list or details")
        ]
    )]
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

    #[OA\Post(
        path: "/php/api_orders.php",
        tags: ["Orders"],
        summary: "Create a new order",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Order created")
        ]
    )]
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

    #[OA\Put(
        path: "/php/api_orders.php",
        tags: ["Orders"],
        summary: "Update an order",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Order ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Order updated")
        ]
    )]
    private function handlePut($input) {
        if (!isset($_GET['id'])) throw new Exception("ID de pedido requerido");

        // Merge ID into input for convenience if needed, or just pass separately
        $id = intval($_GET['id']);
        $this->service->updateOrder($id, $input);

        $this->jsonResponse(true, [], 'Pedido actualizado exitosamente.');
    }

    #[OA\Delete(
        path: "/php/api_orders.php",
        tags: ["Orders"],
        summary: "Delete an order",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Order ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Order deleted")
        ]
    )]
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
