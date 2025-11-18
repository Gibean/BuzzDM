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
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE estado = 'activo'");
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

} catch (PDOException $e) {
    // En caso de error, se puede registrar el error y devolver una respuesta de error
    http_response_code(500);
    $response['error'] = 'Error al consultar la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
