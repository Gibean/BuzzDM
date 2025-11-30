<?php
require_once 'sesion.php';
require_once 'db.php';
$pdo = getDbConnection();

header('Content-Type: application/json');

// Function to send JSON response
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
            if (isset($_GET['id'])) {
                // Fetch single customer
                $customerId = $_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
                $stmt->execute([$customerId]);
                $customer = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($customer) {
                    json_response(true, ['customer' => $customer]);
                } else {
                    json_response(false, [], 'Cliente no encontrado.');
                }
            } elseif (isset($_GET['action']) && $_GET['action'] === 'frequent') {
                // Fetch frequent customers (e.g., top 5 by number of orders)
                $stmt = $pdo->prepare("
                    SELECT
                        c.id,
                        c.nombre,
                        c.email,
                        COUNT(p.id) AS total_orders
                    FROM
                        clientes c
                    JOIN
                        pedidos p ON c.id = p.cliente_id
                    WHERE
                        c.deleted_at IS NULL
                    GROUP BY
                        c.id, c.nombre, c.email
                    ORDER BY
                        total_orders DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $frequentCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                json_response(true, ['frequentCustomers' => $frequentCustomers]);
            } else {
                // Fetch list of all customers
                $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
                $typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
                $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === 'true';

                $sql = "SELECT * FROM clientes WHERE (id LIKE ? OR nombre LIKE ? OR email LIKE ?)";
                if (!$showDeleted) {
                    $sql .= " AND deleted_at IS NULL";
                }
                $params = [$search, $search, $search];

                if ($typeFilter === 'vip') {
                    $sql .= " AND vip = 1";
                } elseif ($typeFilter === 'regular') {
                    $sql .= " AND vip = 0";
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                json_response(true, ['customers' => $customers]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            // Handle Restore Action
            if (isset($input['action']) && $input['action'] === 'restore') {
                $customerId = $input['id'] ?? null;
                if (!$customerId) {
                    json_response(false, [], 'ID de cliente no proporcionado para restaurar.');
                }
                $stmt = $pdo->prepare("UPDATE clientes SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$customerId]);
                json_response(true, [], 'Cliente restaurado exitosamente.');
                return;
            }

            try {
                $stmt = $pdo->prepare("INSERT INTO clientes (id, nombre, email, telefono, direccion, notas, vip) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['id'],
                    $input['nombre'],
                    $input['email'] ?? null,
                    $input['telefono'] ?? null,
                    $input['direccion'] ?? null,
                    $input['notas'] ?? null,
                    isset($input['vip']) && $input['vip'] == 1 ? 1 : 0
                ]);
                json_response(true, ['id' => $input['id']], 'Cliente creado exitosamente.');
            } catch (PDOException $e) {
                json_response(false, [], 'Error al crear cliente: ' . $e->getMessage());
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $customerId = $_GET['id']; // Assuming ID is passed in URL for PUT
            try {
                $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ?, direccion = ?, notas = ?, vip = ? WHERE id = ?");
                $stmt->execute([
                    $input['nombre'],
                    $input['email'] ?? null,
                    $input['telefono'] ?? null,
                    $input['direccion'] ?? null,
                    $input['notas'] ?? null,
                    isset($input['vip']) && $input['vip'] == 1 ? 1 : 0,
                    $customerId
                ]);
                json_response(true, [], 'Cliente actualizado exitosamente.');
            } catch (PDOException $e) {
                json_response(false, [], 'Error al actualizar cliente: ' . $e->getMessage());
            }
            break;

        case 'DELETE':
            $customerId = $_GET['id']; // Assuming ID is passed in URL for DELETE
            try {
                // Soft delete
                $stmt = $pdo->prepare("UPDATE clientes SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$customerId]);
                json_response(true, [], 'Cliente eliminado exitosamente.');
            } catch (PDOException $e) {
                json_response(false, [], 'Error al eliminar cliente: ' . $e->getMessage());
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
