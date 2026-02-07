<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mock session and environment for CLI
if (php_sapi_name() === 'cli') {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['usuario_id'] = 1;
    $_SESSION['usuario_rol'] = 'admin';
    $_SESSION['usuario_nombre'] = 'Debug User';
    $_SERVER['PHP_SELF'] = 'api_requisiciones.php';
    $_SERVER['REQUEST_URI'] = '/buzzDM/php/api_requisiciones.php';
}

require_once 'sesion.php';
require_once __DIR__ . '/../vendor/autoload.php';

try {
    echo "Iniciando prueba...\n";
    $controller = new RequisicionController();
    echo "Controlador instanciado.\n";

    // Simular GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['page'] = 1;

    ob_start();
    $controller->handleRequest();
    $output = ob_get_clean();

    echo "Respuesta capturada:\n";
    echo $output;

} catch (Throwable $e) {
    echo "ERROR CAPTURADO:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
