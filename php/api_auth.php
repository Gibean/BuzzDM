<?php
/**
 * Unified Auth API Entry Point
 * Handles: login, forgot_password, reset_password, logout
 */

// Disable error display to prevent HTML output in JSON response
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/src/Controller/AuthController.php';

    $controller = new AuthController();
    $controller->handleRequest();
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
