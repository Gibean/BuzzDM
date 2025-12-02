<?php
/**
 * Backend para restablecer la contraseña del usuario
 */

header('Content-Type: application/json');

// Incluir dependencias
require_once 'db.php';

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener los datos del formulario
    $token = filter_input(INPUT_POST, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validaciones básicas
    if (!$token) {
        throw new Exception('Token no proporcionado');
    }

    if (empty($password)) {
        throw new Exception('Por favor, ingresa una contraseña');
    }

    if (strlen($password) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }

    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception('La contraseña debe contener al menos una letra mayúscula');
    }

    if (!preg_match('/[a-z]/', $password)) {
        throw new Exception('La contraseña debe contener al menos una letra minúscula');
    }

    if (!preg_match('/[0-9]/', $password)) {
        throw new Exception('La contraseña debe contener al menos un número');
    }

    if ($password !== $confirmPassword) {
        throw new Exception('Las contraseñas no coinciden');
    }

    // Conectar a la base de datos
    $pdo = getDbConnection();

    // Iniciar transacción
    $pdo->beginTransaction();

    try {
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
            throw new Exception('El enlace de recuperación es inválido.');
        }

        // Validar que el token no haya sido usado
        if ($tokenData['usado'] == 1) {
            throw new Exception('Este enlace ya ha sido utilizado. Por favor, solicita un nuevo enlace de recuperación.');
        }

        // Validar que el token no haya expirado
        $now = new DateTime();
        $expiration = new DateTime($tokenData['expiracion']);

        if ($now > $expiration) {
            throw new Exception('El enlace ha expirado. Por favor, solicita un nuevo enlace de recuperación.');
        }

        // Validar el estado del usuario
        if ($tokenData['estado'] === 'bloqueado') {
            throw new Exception('Esta cuenta está bloqueada. Contacta al administrador.');
        }

        if ($tokenData['estado'] === 'inactivo') {
            throw new Exception('Esta cuenta está inactiva. Contacta al administrador.');
        }

        // Hash de la nueva contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar la contraseña del usuario
        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET password = ?,
                actualizado_at = NOW(),
                intentos_fallidos = 0
            WHERE id = ?
        ");
        $stmt->execute([$hashedPassword, $tokenData['usuario_id']]);

        // Marcar el token como usado
        $stmt = $pdo->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE id = ?");
        $stmt->execute([$tokenData['id']]);

        // Invalidar todos los demás tokens del usuario
        $stmt = $pdo->prepare("
            UPDATE password_reset_tokens
            SET usado = 1
            WHERE usuario_id = ? AND id != ? AND usado = 0
        ");
        $stmt->execute([$tokenData['usuario_id'], $tokenData['id']]);

        // Registrar en los logs (si existe la tabla de logs)
        try {
            $stmt = $pdo->prepare("
                INSERT INTO logs (usuario_id, accion, detalles, fecha)
                VALUES (?, 'Contraseña restablecida', 'Usuario restableció su contraseña exitosamente', NOW())
            ");
            $stmt->execute([$tokenData['usuario_id']]);
        } catch (Exception $e) {
            // Si falla el log, no es crítico
            error_log("Error al registrar log de cambio de contraseña: " . $e->getMessage());
        }

        // Commit de la transacción
        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => '¡Contraseña actualizada exitosamente! Serás redirigido al inicio de sesión...'
        ]);

    } catch (Exception $e) {
        // Rollback en caso de error
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

