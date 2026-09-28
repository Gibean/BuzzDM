<?php
/**
 * API Endpoint: Registro de Nueva Empresa para Prueba Demo de 15 Días
 * POST /php/api_register_demo.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/Service/CompanyService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Leer payload JSON o Form-data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

try {
    $companyService = new CompanyService();
    $result = $companyService->createTrialEnvironment([
        'company_name'       => $input['company_name'] ?? '',
        'admin_email'        => $input['admin_email'] ?? '',
        'admin_password'     => $input['admin_password'] ?? '',
        'admin_name'         => $input['admin_name'] ?? 'Administrador',
        'device_fingerprint' => $input['device_fingerprint'] ?? null,
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => '¡Entorno de demostración creado exitosamente! Puedes iniciar sesión ahora.',
        'data' => [
            'company_id'     => $result['company_id'],
            'company_name'   => $result['company_name'],
            'admin_email'    => $result['admin_email'],
            'expires_at'     => $result['expires_at'],
            'days_remaining' => $result['days_remaining'],
            'login_url'      => 'login.php'
        ]
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (RuntimeException $e) {
    http_response_code(409); // Conflict / Blocked
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Error in api_register_demo: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno al aprovisionar el entorno de prueba.']);
}
