<?php
// Autoload handled by composer

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Products", description: "Operations related to Products")]
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

    #[OA\Get(
        path: "/api_products.php",
        tags: ["Products"],
        summary: "List products",
        parameters: [
            new OA\Parameter(name: "search", in: "query", description: "Search term", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "page", in: "query", description: "Page number", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successful operation",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "success", type: "boolean"),
                        new OA\Property(property: "products", type: "array", items: new OA\Items(type: "object")),
                        new OA\Property(property: "pagination", type: "object")
                    ]
                )
            )
        ]
    )]
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

    #[OA\Post(
        path: "/api_products.php",
        tags: ["Products"],
        summary: "Create a new product",
        requestBody: new OA\RequestBody(
            description: "Product data",
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["nombre", "sku", "precio_venta"],
                properties: [
                    new OA\Property(property: "nombre", type: "string"),
                    new OA\Property(property: "sku", type: "string"),
                    new OA\Property(property: "precio_venta", type: "number")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Product created")
        ]
    )]
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

    #[OA\Put(
        path: "/api_products.php",
        tags: ["Products"],
        summary: "Update an existing product",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Product ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Product updated")
        ]
    )]
    private function handlePut($input) {
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) throw new Exception("ID Requerido para actualizar");

        $this->service->updateProduct($id, $input);
        $this->jsonResponse(true, [], 'Producto actualizado exitosamente');
    }

    #[OA\Delete(
        path: "/api_products.php",
        tags: ["Products"],
        summary: "Delete a product",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Product ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Product deleted")
        ]
    )]
    private function handleDelete($input) {
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) throw new Exception("ID Requerido para eliminar");

        $this->service->deleteProduct($id);
        $this->jsonResponse(true, [], 'Producto eliminado exitosamente');
    }
}
