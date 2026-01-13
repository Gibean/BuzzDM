<?php
/**
 * API para búsqueda de proveedores
 * Usado por el autocomplete en cargos de existencias
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$limit = intval($_GET['limit'] ?? 10);

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'message' => 'Query muy corta', 'proveedores' => []]);
    exit;
}

try {
    $pdo = getDbConnection();

    $searchTerm = '%' . $query . '%';

    $sql = "SELECT id, nombre, contacto_nombre, telefono
            FROM proveedores
            WHERE nombre LIKE ?
               OR contacto_nombre LIKE ?
            ORDER BY nombre ASC
            LIMIT ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $limit]);

    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'proveedores' => $proveedores
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en búsqueda: ' . $e->getMessage(),
        'proveedores' => []
    ]);
}
