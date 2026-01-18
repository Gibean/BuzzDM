<?php
require_once __DIR__ . '/../Service/SupplierService.php';

class SupplierController {
    private $service;

    public function __construct() {
        $this->service = new SupplierService();
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $input = json_decode(file_get_contents('php://input'), true);

            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
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
                    $this->deleteSupplier($_GET['id'] ?? null);
                    break;

                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    private function getAllSuppliers() {
        $data = $this->service->getAllSuppliers();
        $this->jsonResponse(true, ['proveedores' => $data]); // Keep 'proveedores' key for frontend compatibility
    }

    private function getSupplier($id) {
        $data = $this->service->getSupplierById($id);
        if ($data) {
            $this->jsonResponse(true, $data);
        } else {
            $this->jsonResponse(false, null, "Proveedor no encontrado");
        }
    }

    private function createSupplier($input) {
        $newId = $this->service->createSupplier($input);
        $this->jsonResponse(true, ['id' => $newId], "Proveedor creado correctamente");
    }

    private function updateSupplier($input) {
        $id = $input['id'] ?? ($_GET['id'] ?? null);
        if (!$id) {
            throw new Exception("ID de proveedor no proporcionado");
        }
        $this->service->updateSupplier($id, $input);
        $this->jsonResponse(true, null, "Proveedor actualizado correctamente");
    }

    private function deleteSupplier($id) {
        if (!$id) {
            throw new Exception("ID de proveedor no proporcionado");
        }
        $this->service->deleteSupplier($id);
        $this->jsonResponse(true, null, "Proveedor eliminado correctamente");
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data, // New standard
            'proveedores' => $data['proveedores'] ?? null, // Backward compatibility for list
            'message' => $message
        ]);
        exit;
    }
}
