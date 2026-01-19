<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;
require_once __DIR__ . '/../Service/SupplierService.php';

#[OA\Tag(name: "Suppliers", description: "Operations related to Suppliers")]
class SupplierController {
    private $service;

    public function __construct() {
        $this->service = new SupplierService();
    }

    /*
     * Standardized JSON Response to match ProductController
     */
    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        $response = ['success' => $success];
        if ($message) $response['message'] = $message;

        // Merge data into root if logic requires, or keep as is.
        // ProductController merges. We will do the same for consistency.
        if (!empty($data)) {
            $response = array_merge($response, $data);
        }

        echo json_encode($response);
        exit;
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $input = json_decode(file_get_contents('php://input'), true);

            switch ($method) {
                case 'GET':
                    if (isset($_GET['action']) && $_GET['action'] === 'search') {
                        $this->searchSuppliers($_GET['q'] ?? '');
                    } elseif (isset($_GET['id'])) {
                        $this->getSupplier((int)$_GET['id']);
                    } else {
                        $this->getAllSuppliers();
                    }
                    break;

                case 'POST':
                    $this->createSupplier($input);
                    break;

                case 'PUT':
                    $this->updateSupplier($input);
                    break;

                case 'DELETE':
                    $this->deleteSupplier();
                    break;

                default:
                    $this->jsonResponse(false, [], 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_suppliers.php",
        tags: ["Suppliers"],
        summary: "List or search suppliers",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Supplier ID", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "action", in: "query", description: "Action 'search'", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "q", in: "query", description: "Search query", required: false, schema: new OA\Schema(type: "string"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Supplier list")
        ]
    )]
    private function getAllSuppliers() {
        $suppliers = $this->service->getAllSuppliers();
        // Wrapper 'proveedores' to match frontend expectation
        $this->jsonResponse(true, ['proveedores' => $suppliers], 'Proveedores obtenidos');
    }

    private function searchSuppliers($query) {
        $suppliers = $this->service->searchSuppliers($query);
        $this->jsonResponse(true, ['proveedores' => $suppliers], 'Resultados de búsqueda');
    }

    private function getSupplier($id) {
        $supplier = $this->service->getSupplierById($id);
        if ($supplier) {
            $this->jsonResponse(true, ['proveedor' => $supplier], 'Proveedor obtenido');
        } else {
            $this->jsonResponse(false, [], 'Proveedor no encontrado');
        }
    }

    #[OA\Post(
        path: "/php/api_suppliers.php",
        tags: ["Suppliers"],
        summary: "Create a new supplier",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Supplier created")
        ]
    )]
    private function createSupplier($data) {
        $id = $this->service->createSupplier($data);
        $this->jsonResponse(true, ['id' => $id], 'Proveedor creado');
    }

    #[OA\Put(
        path: "/php/api_suppliers.php",
        tags: ["Suppliers"],
        summary: "Update a supplier",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Supplier ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Supplier updated")
        ]
    )]
    private function updateSupplier($data) {
        $id = $_GET['id'] ?? $data['id'] ?? null;
        if (!$id) {
            throw new Exception("ID de proveedor requerido para actualización");
        }
        $this->service->updateSupplier($id, $data);
        $this->jsonResponse(true, [], 'Proveedor actualizado');
    }

    #[OA\Delete(
        path: "/php/api_suppliers.php",
        tags: ["Suppliers"],
        summary: "Delete a supplier",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Supplier ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Supplier deleted")
        ]
    )]
    private function deleteSupplier() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception("ID de proveedor requerido para eliminación");
        }
        $this->service->deleteSupplier($id);
        $this->jsonResponse(true, [], 'Proveedor eliminado');
    }
}
