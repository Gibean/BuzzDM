<?php
/**
 * API para búsqueda de productos
 * Usado por el autocomplete en cargos de existencias
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$limit = intval($_GET['limit'] ?? 10);

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query muy corta', 'productos' => []]);
    exit;
}

try {
    $pdo = getDBConnection();

    // Búsqueda por SKU exacto primero, luego por coincidencia parcial
    $sql = "SELECT id, sku, nombre, precio, stock
            FROM productos
            WHERE deleted_at IS NULL
              AND (sku LIKE :query_exact
                   OR sku LIKE :query_like
                   OR nombre LIKE :query_like)
            ORDER BY
                CASE WHEN sku = :query_raw THEN 0
                     WHEN sku LIKE :query_start THEN 1
                     ELSE 2
                END,
                nombre ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':query_raw', $query, PDO::PARAM_STR);
    $stmt->bindValue(':query_exact', $query, PDO::PARAM_STR);
    $stmt->bindValue(':query_start', $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':query_like', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en búsqueda: ' . $e->getMessage(),
        'productos' => []
    ]);
}
