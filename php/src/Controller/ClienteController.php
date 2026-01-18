<?php
require_once __DIR__ . '/../Service/ClienteService.php';

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="BuzzDM API - Clientes", version="1.0")
 */
class ClienteController {
    private $service;

    public function __construct() {
        $this->service = new ClienteService();
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
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
                    $this->handleDelete();
                    break;
                default:
                    $this->jsonResponse(false, [], 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/php/api_clientes.php",
     *     @OA\Parameter(name="id", in="query", required=false, description="ID del cliente"),
     *     @OA\Response(response="200", description="Retorna cliente(s)")
     * )
     */
    private function handleGet() {
        if (isset($_GET['id'])) {
            $customer = $this->service->getCustomer($_GET['id']);
            if ($customer) {
                $this->jsonResponse(true, ['customer' => $customer]);
            } else {
                $this->jsonResponse(false, [], 'Cliente no encontrado');
            }
        } elseif (isset($_GET['action']) && $_GET['action'] === 'frequent') {
            $customers = $this->service->getFrequentCustomers();
            $this->jsonResponse(true, ['frequentCustomers' => $customers]);
        } else {
            $customers = $this->service->searchCustomers($_GET);
            $this->jsonResponse(true, ['customers' => $customers]);
        }
    }

    private function handlePost($input) {
        if (isset($input['action']) && $input['action'] === 'restore') {
            $id = $input['id'] ?? null;
            $this->service->restoreCustomer($id);
            $this->jsonResponse(true, [], 'Cliente restaurado exitosamente');
        } else {
            $id = $this->service->createCustomer($input);
            $this->jsonResponse(true, ['id' => $id], 'Cliente creado exitosamente');
        }
    }

    private function handlePut($input) {
        $id = $_GET['id'] ?? $input['id'] ?? null;
        if (!$id) {
            throw new Exception("ID Requerido para actualizar");
        }
        $this->service->updateCustomer($id, $input);
        $this->jsonResponse(true, [], 'Cliente actualizado exitosamente');
    }

    private function handleDelete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception("ID Requerido para eliminar");
        }
        $this->service->deleteCustomer($id);
        $this->jsonResponse(true, [], 'Cliente eliminado exitosamente');
    }
}
