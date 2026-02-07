<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Warehouses", description: "Operations related to Warehouses")]
class WarehouseController {
    private $service;

    public function __construct() {
        $this->service = new WarehouseService();
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $action = $_GET['action'] ?? null;
            $id = $_GET['id'] ?? null;

            // Handle Locations Sub-resource
            if ($action === 'locations' || ($method === 'GET' && isset($_GET['almacen_id']))) {
                 $warehouseId = $_GET['warehouse_id'] ?? $_GET['almacen_id'] ?? null;

                 if ($method === 'GET') {
                     $this->listLocations($warehouseId);
                 } elseif ($method === 'POST') {
                     $this->createLocation();
                 } elseif ($method === 'PUT') {
                     $this->updateLocation($id);
                 } elseif ($method === 'DELETE') {
                     $this->deleteLocation($id);
                 } else {
                     $this->jsonResponse(false, [], 'Método no permitido para ubicaciones.');
                 }
                 return;
            }

            // Handle Warehouses
            if ($method === 'GET') {
                if ($id) {
                    $this->getWarehouse($id);
                } else {
                    $showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] === 'true';
                    $this->listWarehouses($showInactive);
                }
            } elseif ($method === 'POST') {
                $this->createWarehouse();
            } elseif ($method === 'PUT') {
                $this->updateWarehouse($id);
            } elseif ($method === 'DELETE') {
                $this->deactivateWarehouse($id);
            } else {
                $this->jsonResponse(false, [], 'Método no permitido.');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    // --- Warehouses Handlers ---

    #[OA\Get(
        path: "/php/api_warehouses.php",
        tags: ["Warehouses"],
        summary: "List warehouses or get details",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Warehouse ID", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "show_inactive", in: "query", description: "Show inactive", required: false, schema: new OA\Schema(type: "boolean"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Warehouse list or details")
        ]
    )]
    private function listWarehouses($showInactive) {
        $result = $this->service->getAllWarehouses($showInactive);
        $this->jsonResponse(true, $result);
    }

    private function getWarehouse($id) {
        $warehouse = $this->service->getWarehouse($id);
        $this->jsonResponse(true, ['almacen' => $warehouse]);
    }

    #[OA\Post(
        path: "/php/api_warehouses.php",
        tags: ["Warehouses"],
        summary: "Create a new warehouse",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                required: ["nombre"],
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Almacén Principal", description: "Warehouse name"),
                    new OA\Property(property: "direccion", type: "string", example: "Zona Industrial, Galpón 10", description: "Address"),
                    new OA\Property(property: "descripcion", type: "string", example: "Almacén de productos terminados", description: "Description"),
                    new OA\Property(property: "activo", type: "boolean", example: true, description: "Active status")
                ],
                example: [
                    "nombre" => "Almacén Principal",
                    "direccion" => "Zona Industrial, Galpón 10",
                    "descripcion" => "Almacén de productos terminados",
                    "activo" => true
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Warehouse created")
        ]
    )]
    private function createWarehouse() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->service->createWarehouse($data);
        $this->jsonResponse(true, ['id' => $id], 'Almacén creado exitosamente');
    }

    #[OA\Put(
        path: "/php/api_warehouses.php",
        tags: ["Warehouses"],
        summary: "Update a warehouse",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Warehouse ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "nombre", type: "string", example: "Almacén Actualizado"),
                    new OA\Property(property: "direccion", type: "string", example: "Nueva dirección"),
                    new OA\Property(property: "descripcion", type: "string", example: "Nueva descripción"),
                    new OA\Property(property: "activo", type: "boolean", example: true)
                ],
                example: [
                    "nombre" => "Almacén Actualizado",
                    "direccion" => "Nueva dirección"
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Warehouse updated")
        ]
    )]
    private function updateWarehouse($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->updateWarehouse($id, $data);
        $this->jsonResponse(true, [], 'Almacén actualizado exitosamente');
    }

    #[OA\Delete(
        path: "/php/api_warehouses.php",
        tags: ["Warehouses"],
        summary: "Deactivate a warehouse",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Warehouse ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Warehouse deactivated")
        ]
    )]
    private function deactivateWarehouse($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $this->service->deactivateWarehouse($id);
        $this->jsonResponse(true, [], 'Almacén desactivado exitosamente');
    }

    // --- Locations Handlers (Mapped to api_warehouses.php?action=locations) ---
    // Note: Can also be accessed via LocationController.
    // I will annotate them here as alias paths or just leave them implied.
    // For coverage, I'll add simplified annotations.

    #[OA\Get(
        path: "/php/api_warehouses.php?action=locations",
        tags: ["Warehouses"],
        summary: "List locations in warehouse",
        parameters: [
            new OA\Parameter(name: "warehouse_id", in: "query", description: "Warehouse ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Location list")
        ]
    )]
    private function listLocations($warehouseId) {
        $locations = $this->service->getLocations($warehouseId);
        $this->jsonResponse(true, ['ubicaciones' => $locations]);
    }

    private function createLocation() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->service->createLocation($data);
        $this->jsonResponse(true, ['id' => $id], 'Ubicación creada exitosamente');
    }

    private function updateLocation($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->updateLocation($id, $data);
        $this->jsonResponse(true, [], 'Ubicación actualizada exitosamente');
    }

    private function deleteLocation($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $this->service->deleteLocation($id);
        $this->jsonResponse(true, [], 'Ubicación eliminada exitosamente');
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!empty($data)) $response = array_merge($response, $data);
        echo json_encode($response);
        exit;
    }
}
