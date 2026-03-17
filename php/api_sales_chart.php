<?php
header('Content-Type: application/json');
require_once 'db.php'; // Provides $pdo

try {
    $pdo = getDbConnection();

    // Get days parameter (default 30, max 365)
    $days = isset($_GET['days']) ? intval($_GET['days']) : 30;
    $days = max(1, min($days, 365)); // Clamp between 1 and 365

    // Consulta para obtener las ventas diarias del período especificado
    // Usa pedidos.total como fuente de verdad (monto final del pedido)
    $query = "
        SELECT
            DATE(p.fecha_creacion) as fecha,
            SUM(p.total) as total_ventas
        FROM pedidos p
        WHERE p.fecha_creacion >= CURDATE() - INTERVAL :days DAY
        GROUP BY DATE(p.fecha_creacion)
        ORDER BY fecha ASC;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['days' => $days]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
