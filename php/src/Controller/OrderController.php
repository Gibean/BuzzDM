<?php
// Autoload handled by composer
require_once __DIR__ . '/../helpers/logger.php';

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
            $tasa = getTasaCambioHoy(); // Function from db.php (included in sesion.php)
            $this->jsonResponse(true, ['order' => $order, 'tasa' => $tasa]);
        } else {
            $result = $this->service->listOrders($_GET);
            $this->jsonResponse(true, $result);
        }
    }

    #[OA\Post(
        path: "/php/api_orders.php",
        tags: ["Orders"],
        summary: "Create a new order",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["cliente_id", "direccion_envio", "products"],
                properties: [
                    new OA\Property(property: "cliente_id", type: "integer", example: 1, description: "Client ID"),
                    new OA\Property(property: "direccion_envio", type: "string", example: "Av. Principal #123, Caracas", description: "Shipping address"),
                    new OA\Property(property: "notas_envio", type: "string", example: "Entregar en horario de oficina", description: "Shipping notes"),
                    new OA\Property(property: "estado_pago", type: "string", enum: ["pagado", "no pagado"], example: "pagado", description: "Payment status"),
                    new OA\Property(property: "estado_envio", type: "string", enum: ["pendiente", "enviado", "entregado"], example: "pendiente", description: "Shipping status"),
                    new OA\Property(property: "fecha_creacion", type: "string", format: "datetime", example: "2026-02-07 10:30:00", description: "Order date"),
                    new OA\Property(
                        property: "products",
                        type: "array",
                        description: "List of products in the order",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "producto_id", type: "integer", example: 5, description: "Product ID"),
                                new OA\Property(property: "cantidad", type: "integer", example: 2, description: "Quantity"),
                                new OA\Property(property: "precio_unitario", type: "number", format: "float", example: 25.50, description: "Unit price")
                            ]
                        )
                    )
                ],
                example: [
                    "cliente_id" => 1,
                    "direccion_envio" => "Av. Principal #123, Caracas",
                    "notas_envio" => "Entregar en horario de oficina",
                    "estado_pago" => "pagado",
                    "estado_envio" => "pendiente",
                    "fecha_creacion" => "2026-02-07 10:30:00",
                    "products" => [
                        ["producto_id" => 5, "cantidad" => 2, "precio_unitario" => 25.50],
                        ["producto_id" => 12, "cantidad" => 1, "precio_unitario" => 100.00]
                    ]
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Order created")
        ]
    )]
    private function handlePost($input) {
        global $pdo;
        // Check for Restore Action
        if (isset($input['action']) && $input['action'] === 'restore') {
            if (empty($input['id'])) throw new Exception("ID necesario para restaurar");
            $order = $this->service->getOrder($input['id']);
            $this->service->restoreOrder($input['id']);
            logSystemAction($pdo, getCurrentUserId(), 'Pedido restaurado', '#' . $input['id'] . ' | Ref: ' . ($order['referencia'] ?? ''));
            $this->jsonResponse(true, [], 'Pedido restaurado exitosamente.');
            return;
        }

        // Create Order
        $result = $this->service->createOrder($input);
        logSystemAction($pdo, getCurrentUserId(), 'Pedido creado', '#' . $result['id'] . ' | Ref: ' . ($input['referencia'] ?? '') . ' | Total: $' . number_format($input['total'] ?? 0, 2));
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
        global $pdo;
        if (!isset($_GET['id'])) throw new Exception("ID de pedido requerido");

        $id = intval($_GET['id']);
        $order = $this->service->getOrder($id);
        $this->service->updateOrder($id, $input);
        logSystemAction($pdo, getCurrentUserId(), 'Pedido actualizado', '#' . $id . ' | Ref: ' . ($order['referencia'] ?? ''));

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
        global $pdo;
        $id = $input['id'] ?? $_GET['id'] ?? null;
        if (!$id) throw new Exception("ID de pedido requerido");

        // Get order info before delete for logging
        $order = $this->service->getOrder($id);
        $this->service->deleteOrder($id);
        logSystemAction($pdo, getCurrentUserId(), 'Pedido eliminado', '#' . $id . ' | Ref: ' . ($order['referencia'] ?? ''));
        $this->jsonResponse(true, [], 'Pedido eliminado exitosamente.');
    }
}
