<?php
/**
 * API Entry Point: Requisiciones
 * Purchase Requisitions API
 */
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent HTML errors from breaking JSON
header('Content-Type: application/json'); // Force JSON header early

require_once 'sesion.php';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $controller = new RequisicionController();
    $controller->handleRequest();
} catch (Throwable $e) {
    // Return structured JSON on fatal error
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error crítico del sistema: ' . $e->getMessage()
    ]);
    error_log("Requisiciones API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
}
