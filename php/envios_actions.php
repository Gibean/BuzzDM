<?php
require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

function json_response($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $sql_from = "FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE 1=1";
            $params = [];

            // Filters
            if (isset($_GET['search']) && $_GET['search'] !== '') {
                $search = '%' . $_GET['search'] . '%';
                $sql_from .= " AND (p.id LIKE ? OR p.referencia LIKE ? OR c.nombre LIKE ? OR p.direccion_envio LIKE ? OR p.tracking_number LIKE ?)";
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
                $params[] = $search;
            }
            if (isset($_GET['statusFilter']) && $_GET['statusFilter'] !== '') {
                $sql_from .= " AND p.estado_envio = ?";
                $params[] = $_GET['statusFilter'];
            }

            // Total count for pagination (if implemented later)
            $totalStmt = $pdo->prepare("SELECT COUNT(p.id) " . $sql_from);
            $totalStmt->execute($params);
            $total = $totalStmt->fetchColumn();

            $select_fields = "
                p.id,
                p.referencia,
                c.nombre AS customer,
                p.total AS amount,
                p.estado_envio AS status,
                p.direccion_envio AS address,
                p.tracking_number AS trackingNumber,
                p.estimated_delivery_date AS estimatedDelivery,
                p.fecha_creacion AS date
            ";
            $dataSql = "SELECT $select_fields " . $sql_from;

            // Sorting
            $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'date';
            switch ($sortBy) {
                case 'date':
                    $dataSql .= " ORDER BY p.fecha_creacion DESC";
                    break;
                case 'status':
                    $dataSql .= " ORDER BY p.estado_envio ASC";
                    break;
                case 'customer':
                    $dataSql .= " ORDER BY c.nombre ASC";
                    break;
                case 'amount':
                    $dataSql .= " ORDER BY p.total DESC";
                    break;
                default:
                    $dataSql .= " ORDER BY p.fecha_creacion DESC";
                    break;
            }

            // Pagination (if needed, currently not used by envios.js but good practice)
            // $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            // $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            // $offset = ($page - 1) * $limit;
            // $dataSql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

            $dataStmt = $pdo->prepare($dataSql);
            $dataStmt->execute($params);
            $shipments = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            json_response(true, ['shipments' => $shipments, 'total' => $total]);
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $shipmentId = (int)$input['id'];
            $newStatus = $input['status'];
            $trackingNumber = isset($input['trackingNumber']) ? $input['trackingNumber'] : null;

            $pdo->beginTransaction();
            try {
                $updateSql = "UPDATE pedidos SET estado_envio = ?";
                $updateParams = [$newStatus];

                if ($trackingNumber !== null) {
                    $updateSql .= ", tracking_number = ?";
                    $updateParams[] = $trackingNumber;
                }
                $updateSql .= " WHERE id = ?";
                $updateParams[] = $shipmentId;

                $stmt = $pdo->prepare($updateSql);
                $stmt->execute($updateParams);

                $pdo->commit();
                json_response(true, [], 'Estado de envío actualizado exitosamente.');
            } catch (PDOException $e) {
                $pdo->rollBack();
                json_response(false, [], 'Error al actualizar estado de envío: ' . $e->getMessage());
            }
            break;

        default:
            json_response(false, [], 'Método no permitido.');
            break;
    }
} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
?>