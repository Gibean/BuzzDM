<?php
header('Content-Type: application/json');
require_once 'sesion.php';
require_once 'db.php'; // Provides $pdo

try {
    $pdo = getDbConnection();
    $query = "
        SELECT
            estado_pago,
            COUNT(*) as total_pedidos
        FROM pedidos
        WHERE empresa_id = :empresa_id
        GROUP BY estado_pago;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['empresa_id' => TenantContext::require('api_sales_by_status_chart')]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data = [];
    $backgroundColors = [];

    // Define colors for statuses
    $statusColors = [
        'pagado' => '#FFC107', // Yellow for paid
        'no_pagado' => '#6c757d', // Grey for unpaid
        // Add more as needed
    ];

    foreach ($results as $row) {
        $labels[] = ucfirst(str_replace('_', ' ', $row['estado_pago'])); // Format label
        $data[] = $row['total_pedidos'];
        $backgroundColors[] = $statusColors[$row['estado_pago']] ?? '#ffa000'; // Default grey if status not found
    }

    echo json_encode([
        'labels' => $labels,
        'datasets' => [
            [
                'data' => $data,
                'backgroundColor' => $backgroundColors,
                'hoverOffset' => 4
            ]
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos al obtener ventas por estado: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado al obtener ventas por estado: ' . $e->getMessage()
    ]);
}
?>
