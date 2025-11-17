<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    $pdo = getDbConnection();

    // Consulta para obtener las ventas diarias de los últimos 30 días
    $query = "
        SELECT 
            DATE(p.fecha_creacion) as fecha,
            SUM(dp.subtotal) as total_ventas
        FROM pedidos p
        JOIN detalle_pedido dp ON p.id = dp.pedido_id
        WHERE p.fecha_creacion >= CURDATE() - INTERVAL 30 DAY
        GROUP BY DATE(p.fecha_creacion)
        ORDER BY fecha ASC;
    ";

    $stmt = $pdo->query($query); // Use PDO query
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results

    $data = [
        'labels' => [],
        'datasets' => [
            [
                'label' => 'Ventas Diarias',
                'data' => [],
                'borderColor' => '#FFA000',
                'backgroundColor' => 'rgba(255, 160, 0, 0.1)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.4
            ]
        ]
    ];

    foreach ($results as $row) {
        $data['labels'][] = $row['fecha'];
        $data['datasets'][0]['data'][] = $row['total_ventas'];
    }

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos al obtener ventas: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado al obtener ventas: ' . $e->getMessage()
    ]);
}
?>
