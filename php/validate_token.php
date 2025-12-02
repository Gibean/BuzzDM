<?php
/**
 * Backend para validar tokens de recuperación de contraseña
 */

header('Content-Type: application/json');

// Incluir dependencias
require_once 'db.php';

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener el token
    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$token) {
        throw new Exception('Token no proporcionado');
    }

    // Conectar a la base de datos
    $pdo = getDbConnection();

    // Buscar el token y verificar que no esté expirado ni usado
    $stmt = $pdo->prepare("
        SELECT
            prt.id,
            prt.usuario_id,
            prt.expiracion,
            prt.usado,
            u.email,
            u.nombre,
            u.estado
        FROM password_reset_tokens prt
        INNER JOIN usuarios u ON prt.usuario_id = u.id
        WHERE prt.token = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar que el token existe
    if (!$tokenData) {
        echo json_encode([
            'valid' => false,
            'message' => 'El enlace de recuperación es inválido.'
        ]);
        exit;
    }

    // Validar que el token no haya sido usado
    if ($tokenData['usado'] == 1) {
        echo json_encode([
            'valid' => false,
            'message' => 'Este enlace ya ha sido utilizado. Por favor, solicita un nuevo enlace de recuperación.'
        ]);
        exit;
    }

    // Validar que el token no haya expirado
    $now = new DateTime();
    $expiration = new DateTime($tokenData['expiracion']);

    if ($now > $expiration) {
        // Marcar el token como usado para que no pueda ser reutilizado
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE id = ?");
        $stmt->execute([$tokenData['id']]);

        echo json_encode([
            'valid' => false,
            'message' => 'El enlace ha expirado. Por favor, solicita un nuevo enlace de recuperación.'
        ]);
        exit;
    }

    // Validar el estado del usuario
    if ($tokenData['estado'] === 'bloqueado') {
        echo json_encode([
            'valid' => false,
            'message' => 'Esta cuenta está bloqueada. Contacta al administrador.'
        ]);
        exit;
    }

    if ($tokenData['estado'] === 'inactivo') {
        echo json_encode([
            'valid' => false,
            'message' => 'Esta cuenta está inactiva. Contacta al administrador.'
        ]);
        exit;
    }

    // Calcular tiempo restante
    $interval = $now->diff($expiration);
    $minutesRemaining = ($interval->h * 60) + $interval->i;

    // Token válido
    echo json_encode([
        'valid' => true,
        'message' => 'Token válido',
        'minutes_remaining' => $minutesRemaining,
        'user_email' => $tokenData['email']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'valid' => false,
        'message' => $e->getMessage()
    ]);
}
?>

