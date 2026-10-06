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

$action = $_GET['action'] ?? ($input['action'] ?? 'register');

try {
    $companyService = new CompanyService();

    if ($action === 'verify_code') {
        $email = $input['email'] ?? '';
        $code = $input['code'] ?? '';

        if (empty($email) || empty($code)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El correo y el código de 6 dígitos son obligatorios.']);
            exit;
        }

        $result = $companyService->verifyEmailCode($email, $code);
        http_response_code(200);
        echo json_encode($result);
        exit;
    }

    if ($action === 'resend_code') {
        $email = $input['email'] ?? '';
        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El correo electrónico es obligatorio.']);
            exit;
        }

        $result = $companyService->resendVerificationCode($email);
        http_response_code(200);
        echo json_encode($result);
        exit;
    }

    // Default action: register
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
        'requires_verification' => true,
        'message' => 'Se ha enviado un código de 6 dígitos a tu correo electrónico.',
        'data' => [
            'company_id'     => $result['company_id'],
            'company_name'   => $result['company_name'],
            'admin_email'    => $result['admin_email'],
            'expires_at'     => $result['expires_at'],
            'days_remaining' => $result['days_remaining']
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
    echo json_encode(['success' => false, 'message' => 'Error interno al procesar la solicitud: ' . $e->getMessage()]);
}
