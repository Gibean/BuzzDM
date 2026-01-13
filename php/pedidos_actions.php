<?php
require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

// Function to send JSON response
function json_response($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) {
        $response['message'] = $message;
    }
    // Directly assign data based on keys, avoiding nested 'order' for single fetches
    if (isset($data['orders'])) {
        $response['orders'] = $data['orders'];
        $response['total'] = $data['total'];
    } elseif (isset($data['order'])) { // Added this condition
        $response['order'] = $data['order']; // Directly assign the order object
    } elseif (isset($data['products'])) { // Added this condition for get_products action
        $response['products'] = $data['products'];
    } else {
        $response['data'] = $data; // Fallback for other data
    }
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Fetch single order
                $orderId = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT p.*, c.nombre AS customer_name, c.email AS customer_email FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE p.id = ?");
                $stmt->execute([$orderId]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($order) {
                    // Fetch products for this order
                    $stmt_products = $pdo->prepare("SELECT pp.producto_id, pr.nombre AS product_name, pp.cantidad, pp.precio_unitario FROM detalle_pedido pp JOIN productos pr ON pp.producto_id = pr.id WHERE pp.pedido_id = ?");
                    $stmt_products->execute([$orderId]);
                    $order['products'] = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
                    json_response(true, ['order' => $order]);
                } else {
                    json_response(false, [], 'Pedido no encontrado.');
                }
            } elseif (isset($_GET['action']) && $_GET['action'] === 'get_products' && isset($_GET['order_id'])) {
                // Fetch products for a specific order
                $orderId = (int)$_GET['order_id'];
                $stmt_products = $pdo->prepare("SELECT pp.producto_id, pr.nombre AS product_name, pp.cantidad, pp.precio_unitario FROM detalle_pedido pp JOIN productos pr ON pp.producto_id = pr.id WHERE pp.pedido_id = ?");
                $stmt_products->execute([$orderId]);
                $products = $stmt_products->fetchAll(PDO::FETCH_ASSOC);
                json_response(true, ['products' => $products]);
            } else {
                // Fetch list of orders (existing logic)
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                $offset = ($page - 1) * $limit;
                $showDeleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === 'true';

                $sql_from = "FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE 1=1";
                if (!$showDeleted) {
                    $sql_from .= " AND p.deleted_at IS NULL";
                }
                $params = [];

                // Apply filters
                if (isset($_GET['search']) && $_GET['search'] !== '') {
                    $search = '%' . $_GET['search'] . '%';
                    $sql_from .= " AND (p.id LIKE ? OR p.referencia LIKE ? OR c.nombre LIKE ? OR c.email LIKE ?)";
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                    $params[] = $search;
                }
                if (isset($_GET['paymentStatus']) && $_GET['paymentStatus'] !== '') {
                    $sql_from .= " AND p.estado_pago = ?";
                    $params[] = $_GET['paymentStatus'];
                }
                if (isset($_GET['dateFrom']) && $_GET['dateFrom'] !== '') {
                    $sql_from .= " AND p.fecha_creacion >= ?";
                    $params[] = $_GET['dateFrom'] . ' 00:00:00';
                }
                if (isset($_GET['dateTo']) && $_GET['dateTo'] !== '') {
                    $sql_from .= " AND p.fecha_creacion <= ?";
                    $params[] = $_GET['dateTo'] . ' 23:59:59';
                }

                $totalStmt = $pdo->prepare("SELECT COUNT(p.id) " . $sql_from);
                $totalStmt->execute($params);
                $total = $totalStmt->fetchColumn();

                $select_fields = "
                    p.id,
                    p.referencia,
                    c.nombre AS customer,
                    c.email,
                    p.fecha_creacion AS date,
                    p.total,
                    p.estado_pago AS paymentStatus,
                    p.estado_envio AS shippingStatus,
                    p.direccion_envio AS shippingAddress,
                    p.notas_envio AS shippingNotes,
                    p.deleted_at
                ";
                $dataSql = "SELECT $select_fields " . $sql_from;
                $dataSql .= " ORDER BY p.fecha_creacion DESC";
                $dataSql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

                $dataStmt = $pdo->prepare($dataSql);
                $dataStmt->execute($params);
                $orders = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

                json_response(true, ['orders' => $orders, 'total' => $total]);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            // Handle Restore Action
            if (isset($input['action']) && $input['action'] === 'restore') {
                $orderId = $input['id'] ?? null;
                if (!$orderId) {
                    json_response(false, [], 'ID de pedido no proporcionado para restaurar.');
                }
                $stmt = $pdo->prepare("UPDATE pedidos SET deleted_at = NULL WHERE id = ?");
                $stmt->execute([$orderId]);
                json_response(true, [], 'Pedido restaurado exitosamente.');
                return;
            }

            $pdo->beginTransaction();
            try {
                // Generate a 10-digit random reference number
                $referencia = strval(mt_rand(1000000000, 9999999999));

                // Insert order
                $stmt = $pdo->prepare("INSERT INTO pedidos (cliente_id, referencia, fecha_creacion, total, estado_pago, estado_envio, direccion_envio, notas_envio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['cliente_id'],
                    $referencia,
                    $input['fecha_creacion'] ?? date('Y-m-d H:i:s'),
                    $input['total'],
                    $input['estado_pago'],
                    $input['estado_envio'],
                    $input['direccion_envio'],
                    $input['notas_envio']
                ]);
                $orderId = $pdo->lastInsertId();

                // Insert products if provided
                if (isset($input['products']) && is_array($input['products'])) {
                    $stmt_products = $pdo->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    $stmt_update_stock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

                    foreach ($input['products'] as $product) {
                        $stmt_products->execute([
                            $orderId,
                            $product['producto_id'],
                            $product['cantidad'],
                            $product['precio_unitario']
                        ]);

                        // Update inventory - subtract sold quantity from stock
                        $stmt_update_stock->execute([
                            $product['cantidad'],
                            $product['producto_id']
                        ]);
                    }
                }
                $pdo->commit();
                json_response(true, ['id' => $orderId], 'Pedido creado exitosamente.');
            } catch (PDOException $e) {
                $pdo->rollBack();
                json_response(false, [], 'Error al crear pedido: ' . $e->getMessage());
            }
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = (int)$_GET['id']; // Assuming ID is passed in URL for PUT
            $pdo->beginTransaction();
            try {
                // First, restore inventory from old products before deleting them
                $stmt_get_old_products = $pdo->prepare("SELECT producto_id, cantidad FROM detalle_pedido WHERE pedido_id = ?");
                $stmt_get_old_products->execute([$orderId]);
                $old_products = $stmt_get_old_products->fetchAll(PDO::FETCH_ASSOC);

                $stmt_restore_stock = $pdo->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                foreach ($old_products as $old_product) {
                    // Return quantities to inventory
                    $stmt_restore_stock->execute([
                        $old_product['cantidad'],
                        $old_product['producto_id']
                    ]);
                }

                // Update order
                $stmt = $pdo->prepare("UPDATE pedidos SET cliente_id = ?, referencia = ?, fecha_creacion = ?, total = ?, estado_pago = ?, estado_envio = ?, direccion_envio = ?, notas_envio = ? WHERE id = ?");
                $stmt->execute([
                    $input['cliente_id'], // Removed (int) cast
                    $input['referencia'],
                    $input['fecha_creacion'] ?? date('Y-m-d H:i:s'),
                    $input['total'],
                    $input['estado_pago'],
                    $input['estado_envio'],
                    $input['direccion_envio'],
                    $input['notas_envio'],
                    $orderId
                ]);

                // Update products: simple approach is to delete all and re-insert
                $stmt_delete_products = $pdo->prepare("DELETE FROM detalle_pedido WHERE pedido_id = ?");
                $stmt_delete_products->execute([$orderId]);

                if (isset($input['products']) && is_array($input['products'])) {
                    $stmt_insert_products = $pdo->prepare("INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                    $stmt_update_stock = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");

                    foreach ($input['products'] as $product) {
                        $stmt_insert_products->execute([
                            $orderId,
                            $product['producto_id'],
                            $product['cantidad'],
                            $product['precio_unitario']
                        ]);

                        // Subtract new quantities from inventory
                        $stmt_update_stock->execute([
                            $product['cantidad'],
                            $product['producto_id']
                        ]);
                    }
                }
                $pdo->commit();
                json_response(true, [], 'Pedido actualizado exitosamente.');
            } catch (PDOException $e) {
                $pdo->rollBack();
                json_response(false, [], 'Error al actualizar pedido: ' . $e->getMessage());
            }
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);
            $orderId = (int)$input['id']; // Assuming ID is passed in body for DELETE

            // Soft delete
            $stmt = $pdo->prepare("UPDATE pedidos SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$orderId]);
            json_response(true, [], 'Pedido eliminado exitosamente.');
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
