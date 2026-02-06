<?php
/**
 * API endpoint to get current exchange rate
 * Returns the BCV rate for today
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

try {
    $tasa = getTasaCambioHoy();

    if ($tasa) {
        echo json_encode([
            'success' => true,
            'tasa' => $tasa
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No hay tasa de cambio configurada'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
