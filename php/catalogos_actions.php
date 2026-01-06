<?php
/**
 * API para obtener catálogos (Marcas, Categorías, Proveedores)
 * Usado para llenar selects en formularios
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();

    // Obtener marcas
    $marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")
                  ->fetchAll(PDO::FETCH_ASSOC);

    // Obtener categorías
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")
                      ->fetchAll(PDO::FETCH_ASSOC);

    // Obtener proveedores
    $proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre")
                       ->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'catalogos' => [
            'marcas' => $marcas,
            'categorias' => $categorias,
            'proveedores' => $proveedores
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error cargando catálogos: ' . $e->getMessage()
    ]);
}
