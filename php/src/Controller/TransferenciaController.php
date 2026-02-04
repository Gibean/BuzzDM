<?php
/**
 * TransferenciaController - HTTP handler for warehouse transfers
 */

use OpenApi\Attributes as OA;
require_once __DIR__ . '/../Service/TransferenciaService.php';

#[OA\Tag(name: "Transfers", description: "Operations related to warehouse transfers")]
class TransferenciaController {

    private TransferenciaService $service;

    public function __construct() {
        $this->service = new TransferenciaService();
    }

    public function handleRequest() {
        header('Content-Type: application/json');
        $method = $_SERVER['REQUEST_METHOD'];

        try {
            switch ($method) {
                case 'GET':
                    if (isset($_GET['action']) && $_GET['action'] === 'buscar_productos') {
                        $this->buscarProductosConStock();
                    } elseif (isset($_GET['id'])) {
                        $this->getTransferencia((int)$_GET['id']);
                    } else {
                        $this->getAllTransferencias();
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);
                    if (isset($_GET['action'])) {
                        $this->handleAction($_GET['action'], $data);
                    } else {
                        $this->createTransferencia($data);
                    }
                    break;

                case 'PUT':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        $this->jsonResponse(false, [], 'ID requerido');
                        return;
                    }
                    $data = json_decode(file_get_contents('php://input'), true);
                    $this->updateTransferencia((int)$id, $data);
                    break;

                case 'DELETE':
                    $id = $_GET['id'] ?? null;
                    if (!$id) {
                        $this->jsonResponse(false, [], 'ID requerido');
                        return;
                    }
                    $this->deleteTransferencia((int)$id);
                    break;

                default:
                    $this->jsonResponse(false, [], 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    private function handleAction($action, $data) {
        $id = $data['id'] ?? $_GET['id'] ?? null;

        if (!$id && in_array($action, ['enviar', 'recibir', 'cancelar'])) {
            $this->jsonResponse(false, [], 'ID de transferencia requerido');
            return;
        }

        switch ($action) {
            case 'enviar':
                $this->enviarTransferencia((int)$id, $data['cantidades'] ?? []);
                break;

            case 'recibir':
                $this->recibirTransferencia((int)$id, $data['cantidades'] ?? []);
                break;

            case 'cancelar':
                $this->cancelarTransferencia((int)$id);
                break;

            default:
                $this->jsonResponse(false, [], 'Acción no reconocida');
        }
    }

    #[OA\Get(
        path: "/php/api_transferencias.php",
        tags: ["Transfers"],
        summary: "Get all transfers with filters",
        parameters: [
            new OA\Parameter(name: "page", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "estado", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "almacen_origen", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "almacen_destino", in: "query", schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "List of transfers")]
    )]
    private function getAllTransferencias() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 10;

        $filters = [
            'estado' => $_GET['estado'] ?? null,
            'almacen_origen' => $_GET['almacen_origen'] ?? null,
            'almacen_destino' => $_GET['almacen_destino'] ?? null,
            'fecha_desde' => $_GET['fecha_desde'] ?? null,
            'fecha_hasta' => $_GET['fecha_hasta'] ?? null,
            'search' => $_GET['search'] ?? null
        ];

        $result = $this->service->getTransferencias($page, $limit, array_filter($filters));

        $this->jsonResponse(true, [
            'transferencias' => $result['data'],
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit']
        ], 'Transferencias obtenidas');
    }

    #[OA\Get(
        path: "/php/api_transferencias.php?id={id}",
        tags: ["Transfers"],
        summary: "Get single transfer by ID",
        parameters: [
            new OA\Parameter(name: "id", in: "query", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [new OA\Response(response: 200, description: "Transfer details")]
    )]
    private function getTransferencia($id) {
        $transfer = $this->service->getTransferenciaById($id);

        if ($transfer) {
            $this->jsonResponse(true, ['transferencia' => $transfer], 'Transferencia obtenida');
        } else {
            $this->jsonResponse(false, [], 'Transferencia no encontrada');
        }
    }

    #[OA\Post(
        path: "/php/api_transferencias.php",
        tags: ["Transfers"],
        summary: "Create new transfer",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["almacen_origen_id", "almacen_destino_id", "fecha_solicitud"],
                properties: [
                    new OA\Property(property: "almacen_origen_id", type: "integer"),
                    new OA\Property(property: "almacen_destino_id", type: "integer"),
                    new OA\Property(property: "fecha_solicitud", type: "string", format: "date"),
                    new OA\Property(property: "motivo", type: "string"),
                    new OA\Property(property: "lineas", type: "array", items: new OA\Items(type: "object"))
                ]
            )
        ),
        responses: [new OA\Response(response: 200, description: "Transfer created")]
    )]
    private function createTransferencia($data) {
        // Validate required fields
        if (empty($data['almacen_origen_id']) || empty($data['almacen_destino_id'])) {
            $this->jsonResponse(false, [], 'Almacén origen y destino son requeridos');
            return;
        }

        if (empty($data['fecha_solicitud'])) {
            $data['fecha_solicitud'] = date('Y-m-d');
        }

        $userId = $_SESSION['usuario_id'] ?? 1;
        $id = $this->service->createTransferencia($data, $userId);

        $this->jsonResponse(true, ['id' => $id], 'Transferencia creada correctamente');
    }

    private function updateTransferencia($id, $data) {
        $this->service->updateTransferencia($id, $data);
        $this->jsonResponse(true, [], 'Transferencia actualizada correctamente');
    }

    private function enviarTransferencia($id, $cantidades = []) {
        $userId = $_SESSION['usuario_id'] ?? 1;
        $this->service->enviarTransferencia($id, $userId, $cantidades);
        $this->jsonResponse(true, [], 'Transferencia enviada - stock descontado de almacén origen');
    }

    private function recibirTransferencia($id, $cantidades = []) {
        $userId = $_SESSION['usuario_id'] ?? 1;
        $this->service->recibirTransferencia($id, $userId, $cantidades);
        $this->jsonResponse(true, [], 'Transferencia recibida - stock agregado a almacén destino');
    }

    private function cancelarTransferencia($id) {
        $this->service->cancelarTransferencia($id);
        $this->jsonResponse(true, [], 'Transferencia cancelada');
    }

    private function deleteTransferencia($id) {
        $this->service->deleteTransferencia($id);
        $this->jsonResponse(true, [], 'Transferencia eliminada');
    }

    /**
     * Search products with stock available in specific warehouse
     */
    private function buscarProductosConStock() {
        $search = $_GET['search'] ?? '';
        $almacenId = $_GET['almacen_id'] ?? null;
        $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));

        if (!$almacenId) {
            $this->jsonResponse(false, [], 'Almacén ID requerido');
            return;
        }

        if (strlen($search) < 2) {
            $this->jsonResponse(true, ['productos' => []], 'Búsqueda muy corta');
            return;
        }

        try {
            // Get PDO connection
            require_once __DIR__ . '/../../db.php';
            $pdo = Database::getConnection();

            // Search products with stock in the specified warehouse
            $sql = "SELECT
                        p.id,
                        p.sku,
                        p.nombre,
                        p.stock AS stock_global,
                        COALESCE(su.cantidad, 0) AS stock_disponible
                    FROM productos p
                    LEFT JOIN stock_ubicacion su ON p.id = su.producto_id AND su.almacen_id = ?
                    WHERE p.deleted_at IS NULL
                      AND (p.sku LIKE ? OR p.nombre LIKE ?)
                      AND COALESCE(su.cantidad, 0) > 0
                    ORDER BY stock_disponible DESC, p.sku ASC
                    LIMIT ?";

            $searchTerm = '%' . $search . '%';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$almacenId, $searchTerm, $searchTerm, $limit]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(true, ['productos' => $productos], 'Productos encontrados');

        } catch (Exception $e) {
            $this->jsonResponse(false, [], 'Error: ' . $e->getMessage());
        }
    }

    private function jsonResponse($success, $data = [], $message = '') {
        echo json_encode(array_merge(
            ['success' => $success, 'message' => $message],
            $data
        ));
    }
}
