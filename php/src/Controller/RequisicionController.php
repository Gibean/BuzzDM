<?php
/**
 * RequisicionController
 * API endpoints for purchase requisitions
 */
require_once __DIR__ . '/../helpers/logger.php';
require_once __DIR__ . '/../Service/RequisicionService.php';

use OpenApi\Attributes as OA;

#[OA\Tag(name: "Requisiciones", description: "Purchase Requisitions with Supplier Intelligence")]
class RequisicionController {
    private $service;

    public function __construct() {
        $this->service = new RequisicionService();
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
        // Check authorization
        if (!isset($_SESSION['usuario_id'])) {
            $this->jsonResponse(false, [], 'No autenticado');
        }

        // Permission check based on action
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? null;
        $userRole = $_SESSION['usuario_rol'] ?? 'operador';

        // Only admin/supervisor can approve/reject/process
        if (in_array($action, ['approve', 'reject', 'generate']) && !in_array($userRole, ['admin', 'supervisor'])) {
            $this->jsonResponse(false, [], 'No autorizado para esta acción');
        }

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

    // ==========================================
    // GET - List, Read, Suggest, Low Stock
    // ==========================================

    #[OA\Get(
        path: "/php/api_requisiciones.php",
        tags: ["Requisiciones"],
        summary: "List requisitions, get single, or get suggestions",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Requisition ID", required: false, schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "action", in: "query", description: "Action: suggest, lowstock", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "producto_id", in: "query", description: "Product ID for suggestion", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Requisition data or suggestions")
        ]
    )]
    private function handleGet() {
        $action = $_GET['action'] ?? null;

        // Get supplier suggestion for a product
        if ($action === 'suggest' && isset($_GET['producto_id'])) {
            $suggestion = $this->service->sugerirMejorProveedor(intval($_GET['producto_id']));
            $this->jsonResponse(true, ['suggestion' => $suggestion]);
        }

        // Get all products with low stock (for auto-populate)
        if ($action === 'lowstock') {
            $productos = $this->service->getProductosParaRequisicion();
            $this->jsonResponse(true, ['productos' => $productos, 'count' => count($productos)]);
        }

        // Get single requisition
        if (isset($_GET['id'])) {
            $requisicion = $this->service->getRequisicion(intval($_GET['id']));
            $this->jsonResponse(true, ['requisicion' => $requisicion]);
        }

        // List requisitions with filters
        $result = $this->service->listRequisiciones($_GET);
        $this->jsonResponse(true, $result);
    }

    // ==========================================
    // POST - Create, Approve, Reject, Generate
    // ==========================================

    #[OA\Post(
        path: "/php/api_requisiciones.php",
        tags: ["Requisiciones"],
        summary: "Create requisition or perform actions",
        parameters: [
            new OA\Parameter(name: "action", in: "query", description: "Action: create, fromLowStock, approve, reject, generate", required: false, schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "id", in: "query", description: "Requisition ID for actions", required: false, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                example: [
                    "fecha_solicitud" => "2026-02-07",
                    "prioridad" => "alta",
                    "notas" => "Urgente para evento",
                    "lineas" => [
                        ["producto_id" => 1, "cantidad_solicitada" => 50],
                        ["producto_id" => 2, "cantidad_solicitada" => 100, "proveedor_seleccionado_id" => 3]
                    ]
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Requisition created or action performed")
        ]
    )]
    private function handlePost($input) {
        $pdo = getDbConnection();
        $action = $_GET['action'] ?? 'create';
        $userId = $_SESSION['usuario_id'];

        // Action: Approve
        if ($action === 'approve' && isset($_GET['id'])) {
            $this->service->aprobarRequisicion(intval($_GET['id']), $userId);
            logSystemAction($pdo, $userId, 'Requisición aprobada', '#' . $_GET['id']);
            $this->jsonResponse(true, [], 'Requisición aprobada exitosamente');
        }

        // Action: Reject
        if ($action === 'reject' && isset($_GET['id'])) {
            $this->service->rechazarRequisicion(intval($_GET['id']), $userId);
            logSystemAction($pdo, $userId, 'Requisición rechazada', '#' . $_GET['id']);
            $this->jsonResponse(true, [], 'Requisición rechazada');
        }

        // Action: Generate Cargos
        if ($action === 'generate' && isset($_GET['id'])) {
            $almacenId = $input['almacen_id'] ?? null;
            $cargos = $this->service->generarCargos(intval($_GET['id']), $userId, $almacenId);
            logSystemAction($pdo, $userId, 'Cargos generados desde requisición', '#' . $_GET['id'] . ' → ' . count($cargos) . ' cargo(s)');
            $this->jsonResponse(true, [
                'cargos' => $cargos,
                'mensaje' => 'Se generaron ' . count($cargos) . ' cargo(s) borrador'
            ], 'Cargos generados exitosamente');
        }

        // Action: Create from Low Stock (auto-populate)
        if ($action === 'fromLowStock') {
            $prioridad = $input['prioridad'] ?? 'media';
            $result = $this->service->createFromLowStock($userId, $prioridad);
            logSystemAction($pdo, $userId, 'Requisición creada (auto stock bajo)', $result['codigo']);
            $this->jsonResponse(true, [
                'requisicion_id' => $result['id'],
                'codigo' => $result['codigo']
            ], 'Requisición creada con productos de stock bajo');
        }

        // Action: Create (normal)
        if (isset($_GET['id'])) {
            throw new Exception("ID no esperado para creación");
        }

        $result = $this->service->createRequisicion($input, $userId);
        logSystemAction($pdo, $userId, 'Requisición creada', $result['codigo'] . ' | ' . count($input['lineas'] ?? []) . ' productos');
        $this->jsonResponse(true, [
            'requisicion_id' => $result['id'],
            'codigo' => $result['codigo']
        ], 'Requisición creada exitosamente');
    }

    // ==========================================
    // PUT - Update
    // ==========================================

    #[OA\Put(
        path: "/php/api_requisiciones.php",
        tags: ["Requisiciones"],
        summary: "Update requisition",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Requisition ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Requisition updated")
        ]
    )]
    private function handlePut($input) {
        $pdo = getDbConnection();
        if (!isset($_GET['id'])) {
            throw new Exception('ID de requisición requerido');
        }

        $result = $this->service->updateRequisicion(intval($_GET['id']), $input, $_SESSION['usuario_id']);
        logSystemAction($pdo, $_SESSION['usuario_id'], 'Requisición actualizada', '#' . $_GET['id']);
        $this->jsonResponse(true, [], 'Requisición actualizada correctamente');
    }

    // ==========================================
    // DELETE
    // ==========================================

    #[OA\Delete(
        path: "/php/api_requisiciones.php",
        tags: ["Requisiciones"],
        summary: "Delete requisition",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Requisition ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Requisition deleted")
        ]
    )]
    private function handleDelete() {
        $pdo = getDbConnection();
        if (!isset($_GET['id'])) {
            throw new Exception('ID de requisición requerido');
        }

        $req = $this->service->getRequisicion(intval($_GET['id']));
        $this->service->deleteRequisicion(intval($_GET['id']));
        logSystemAction($pdo, $_SESSION['usuario_id'], 'Requisición eliminada', $req['codigo']);
        $this->jsonResponse(true, [], 'Requisición eliminada correctamente');
    }
}
