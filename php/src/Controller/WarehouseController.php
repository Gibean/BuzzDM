<?php
require_once __DIR__ . '/../Service/WarehouseService.php';

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
            if ($action === 'locations' || ($method === 'GET' && isset($_GET['almacen_id']))) { // Support legacy param if needed, but clean API prefers consistent action
                 // Note: The frontend calls `php/ubicaciones_actions.php?almacen_id=1` -> mapped to query param
                 // My plan says: `GET /?action=locations&warehouse_id=X`
                 // I will handle legacy query alias here for robustness
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

    private function listWarehouses($showInactive) {
        $result = $this->service->getAllWarehouses($showInactive);
        $this->jsonResponse(true, $result);
    }

    private function getWarehouse($id) {
        $warehouse = $this->service->getWarehouse($id);
        $this->jsonResponse(true, ['almacen' => $warehouse]);
    }

    private function createWarehouse() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $this->service->createWarehouse($data);
        $this->jsonResponse(true, ['id' => $id], 'Almacén creado exitosamente');
    }

    private function updateWarehouse($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $data = json_decode(file_get_contents('php://input'), true);
        $this->service->updateWarehouse($id, $data);
        $this->jsonResponse(true, [], 'Almacén actualizado exitosamente');
    }

    private function deactivateWarehouse($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        $this->service->deactivateWarehouse($id);
        $this->jsonResponse(true, [], 'Almacén desactivado exitosamente');
    }

    // --- Locations Handlers ---

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
