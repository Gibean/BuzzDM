<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Cargos", description: "Operations related to Stock Cargos")]
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
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_rol'], ['admin', 'supervisor'])) {
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
        path: "/php/api_cargos.php",
        tags: ["Cargos"],
        summary: "List cargos or get cargo",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Cargo ID", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Cargo data")
        ]
    )]
    private function handleGet() {
        if (isset($_GET['id'])) {
            $cargo = $this->service->getCargo($_GET['id']);
            $this->jsonResponse(true, ['cargo' => $cargo]);
        } else {
            $result = $this->service->listCargos($_GET);
            $this->jsonResponse(true, $result);
        }
    }

    #[OA\Post(
        path: "/php/api_cargos.php",
        tags: ["Cargos"],
        summary: "Create or act on cargo",
        parameters: [
            new OA\Parameter(name: "action", in: "query", description: "Action (create/confirm/cancel)", required: false, schema: new OA\Schema(type: "string"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Cargo created or updated")
        ]
    )]
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

    #[OA\Put(
        path: "/php/api_cargos.php",
        tags: ["Cargos"],
        summary: "Update cargo",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Cargo ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Cargo updated")
        ]
    )]
    private function handlePut($input) {
        if (!isset($_GET['id'])) throw new Exception('ID de cargo requerido');

        $result = $this->service->updateCargo(intval($_GET['id']), $input, $_SESSION['usuario_id']);
        $msg = $result['estado'] === 'confirmado'
                ? 'Cargo confirmado y stock actualizado'
                : 'Cargo actualizado correctamente';

        $this->jsonResponse(true, [], $msg);
    }

    #[OA\Delete(
        path: "/php/api_cargos.php",
        tags: ["Cargos"],
        summary: "Delete cargo",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Cargo ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Cargo deleted")
        ]
    )]
    private function handleDelete($input) {
        if (!isset($_GET['id'])) throw new Exception('ID de cargo requerido');

        $this->service->deleteCargo(intval($_GET['id']));
        $this->jsonResponse(true, [], 'Cargo eliminado correctamente');
    }
}
