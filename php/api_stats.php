<?php
header('Content-Type: application/json');
require_once 'sesion.php';
require_once 'db.php'; // Provides $pdo

$response = [
    'totalProducts' => 0,
    'totalOrders' => 0,
    'pendingShipments' => 0,
    'monthlyRevenue' => 0
];

try {
    $pdo = getDbConnection();

    // Todas las cifras son de la empresa activa. Sin este filtro el endpoint
    // devolvia totales de todos los tenants a cualquiera que lo pidiera por URL.
    $empresaId = TenantContext::require('api_stats');

    // Total de productos activos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empresaId]);
    $response['totalProducts'] = $stmt->fetchColumn();

    // Total de pedidos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE empresa_id = ?");
    $stmt->execute([$empresaId]);
    $response['totalOrders'] = $stmt->fetchColumn();

    // Envíos pendientes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE empresa_id = ? AND estado_envio = 'pendiente'");
    $stmt->execute([$empresaId]);
    $response['pendingShipments'] = $stmt->fetchColumn();

    // Ingresos del mes
    $stmt = $pdo->prepare("SELECT SUM(total) FROM pedidos WHERE empresa_id = ? AND fecha_creacion >= DATE_FORMAT(NOW(), '%Y-%m-01')");
    $stmt->execute([$empresaId]);
    $response['monthlyRevenue'] = $stmt->fetchColumn() ?? 0;

    // --- NUEVAS ESTADÍSTICAS ---

    // Clientes totales
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE empresa_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empresaId]);
    $response['totalCustomers'] = $stmt->fetchColumn();

    // Productos con bajo stock
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM v_low_stock WHERE empresa_id = ?");
    $stmt->execute([$empresaId]);
    $response['lowStockCount'] = $stmt->fetchColumn();

    // Valor total del inventario
    $stmt = $pdo->prepare("SELECT SUM(stock * precio) FROM productos WHERE empresa_id = ? AND deleted_at IS NULL");
    $stmt->execute([$empresaId]);
    $response['totalInventoryValue'] = $stmt->fetchColumn() ?? 0;

    // Pedidos de hoy
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE empresa_id = ? AND DATE(fecha_creacion) = CURDATE()");
    $stmt->execute([$empresaId]);
    $response['dailyOrders'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    // En caso de error, se puede registrar el error y devolver una respuesta de error
    http_response_code(500);
    $response['error'] = 'Error al consultar la base de datos: ' . $e->getMessage();
}

echo json_encode($response);
