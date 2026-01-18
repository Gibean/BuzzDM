<?php
require_once __DIR__ . '/../Service/CargoService.php';

use OpenApi\Annotations as OA;

/**
 * @OA\Info(title="BuzzDM API - Cargos de Existencias", version="1.0")
 */
class CargoController {
    private $service;

    public function __construct() {
        $this->service = new CargoService();
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        http_response_code($success ? 200 : ($message == 'No autorizado' ? 403 : 500));

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

        // Role check handled by sesion.php / allowed pages, but good to reinforce if specific methods need it.
        // Original code checked for admin/supervisor explicitly at start.
        if (!in_array($_SESSION['usuario_rol'], ['admin', 'supervisor'])) {
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
        if (isset($_GET['id'])) {
            $cargo = $this->service->getCargo($_GET['id']);
            $this->jsonResponse(true, ['cargo' => $cargo]);
        } else {
            $result = $this->service->listCargos($_GET);
            $this->jsonResponse(true, $result);
        }
    }

    private function handlePost($input) {
        $action = $_GET['action'] ?? 'create';

        if ($action === 'confirm' && isset($_GET['id'])) {
            $this->service->confirmCargo(intval($_GET['id']), $_SESSION['usuario_id']);
            $this->jsonResponse(true, [], 'Cargo confirmado y stock actualizado.');
        }
        elseif ($action === 'cancel' && isset($_GET['id'])) {
            $this->service->cancelCargo(intval($_GET['id']), $input['motivo'] ?? '', $_SESSION['usuario_id']);
            $this->jsonResponse(true, [], 'Cargo anulado. Stock revertido correctamente.');
        }
        else {
            if (isset($_GET['id'])) throw new Exception("ID no esperado para creación");

            $result = $this->service->createCargo($input, $_SESSION['usuario_id']);
            $msg = $result['estado'] === 'confirmado'
                ? 'Cargo confirmado y stock actualizado'
                : 'Cargo guardado como borrador';

            $this->jsonResponse(true, [
                'cargo_id' => $result['id'],
                'codigo' => $result['codigo']
            ], $msg);
        }
    }

    private function handlePut($input) {
        if (!isset($_GET['id'])) throw new Exception('ID de cargo requerido');

        $result = $this->service->updateCargo(intval($_GET['id']), $input, $_SESSION['usuario_id']);
        $msg = $result['estado'] === 'confirmado'
                ? 'Cargo confirmado y stock actualizado'
                : 'Cargo actualizado correctamente';

        $this->jsonResponse(true, [], $msg);
    }

    private function handleDelete($input) {
        if (!isset($_GET['id'])) throw new Exception('ID de cargo requerido');

        $this->service->deleteCargo(intval($_GET['id']));
        $this->jsonResponse(true, [], 'Cargo eliminado correctamente');
    }
}
