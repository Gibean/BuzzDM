<?php
header('Content-Type: application/json');
require_once 'db.php'; // Provides $pdo

$response = [
    'totalProducts' => 0,
    'totalOrders' => 0,
    'pendingShipments' => 0,
    'monthlyRevenue' => 0
];

try {
    $pdo = getDbConnection();
    // Total de productos activos
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE deleted_at IS NULL");
    $response['totalProducts'] = $stmt->fetchColumn();

    // Total de pedidos
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos");
    $response['totalOrders'] = $stmt->fetchColumn();

    // Envíos pendientes
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado_envio = 'pendiente'");
    $response['pendingShipments'] = $stmt->fetchColumn();

    // Ingresos del mes
    $stmt = $pdo->query("SELECT SUM(total) FROM pedidos WHERE fecha_creacion >= DATE_FORMAT(NOW(), '%Y-%m-01')");
    $response['monthlyRevenue'] = $stmt->fetchColumn() ?? 0;

    // --- NUEVAS ESTADÍSTICAS ---

    // Clientes totales
    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL");
    $response['totalCustomers'] = $stmt->fetchColumn();

    // Productos con bajo stock
    $stmt = $pdo->query("SELECT COUNT(*) FROM v_low_stock");
    $response['lowStockCount'] = $stmt->fetchColumn();

    // Valor total del inventario
    $stmt = $pdo->query("SELECT SUM(stock * precio) FROM productos WHERE deleted_at IS NULL");
    $response['totalInventoryValue'] = $stmt->fetchColumn() ?? 0;

    // Pedidos de hoy
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_creacion) = CURDATE()");
    $response['dailyOrders'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    // En caso de error, se puede registrar el error y devolver una respuesta de error
    http_response_code(500);
    $response['error'] = 'Error al consultar la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
