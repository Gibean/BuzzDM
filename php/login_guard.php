<?php
ini_set('display_errors', 'Off'); // Turn off display_errors for production, log them instead
error_reporting(E_ALL);

// Set a global error handler to catch fatal errors
set_exception_handler(function ($exception) {
    // Log the exception message to a file or error tracking service
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado en el servidor.'
    ]);
    exit();
});

// Set headers for JSON response at the very beginning
header('Content-Type: application/json');

try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    require_once 'db.php';
    $pdo = getDbConnection(); // Initialize $pdo here

    // --- Helper Function to Log Events ---
    function log_login_attempt($pdo, $usuario_id, $accion, $detalles) {
        $sql = "INSERT INTO logs_login (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id, $accion, $detalles]);
        return $pdo->lastInsertId();
    }

    // --- Main Logic ---
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'El email y la contraseña son obligatorios.']);
        exit();
    }

    // --- User and Status Check ---
    $stmt = $pdo->prepare("SELECT id, password, rol, estado, intentos_fallidos FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        log_login_attempt($pdo, null, 'Denegado', "Intento de inicio de sesión con email no existente: $email");
        echo json_encode(['success' => false, 'message' => 'Credenciales incorrectas.']);
        exit();
    }

    $user_id = $user['id'];

    if ($user['estado'] === 'bloqueado') {
        log_login_attempt($pdo, $user_id, 'Denegado', 'Intento de inicio de sesión con usuario bloqueado');
        echo json_encode(['success' => false, 'message' => 'Usuario bloqueado. Contacte al administrador para recuperar su contraseña.']);
        exit();
    }

    // --- Password Verification ---
    if (password_verify($password, $user['password'])) {
        // --- Success Case ---
        if (($user['intentos_fallidos'] ?? 0) > 0) {
            $update_stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?");
            $update_stmt->execute([$user_id]);
        }

        $log_detail = 'Inicio de sesión de ' . $user['rol'];
        log_login_attempt($pdo, $user_id, 'Exitoso', $log_detail);

        // --- API Session Token Generation ---
        // Generate a secure token and an expiration date for the API session record.
        $token = bin2hex(random_bytes(32)); // 64-character hexadecimal token
        $expiration = date('Y-m-d H:i:s', strtotime('+8 hours')); // Token expires in 8 hours for this record

        // Insert the new session record into the database.
        $session_stmt = $pdo->prepare(
            "INSERT INTO sesiones_api (usuario_id, token, expiracion) VALUES (?, ?, ?)"
        );
        $session_stmt->execute([$user_id, $token, $expiration]);

        $_SESSION['usuario_id'] = $user_id;
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['email'] = $email;

        echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso.', 'redirect' => 'dashboard.php']);

    } else {
        // --- Failure Case ---
        $new_attempts = ($user['intentos_fallidos'] ?? 0) + 1;
        $response_message = 'Credenciales incorrectas.';
        $log_detail = 'Intento de inicio de sesión con contraseña incorrecta';

        if ($new_attempts >= 10) {
            $update_stmt = $pdo->prepare("UPDATE usuarios SET estado = 'bloqueado', intentos_fallidos = ? WHERE id = ?");
            $update_stmt->execute([$new_attempts, $user_id]);

            $response_message = 'Demasiados intentos, usuario bloqueado. Contacte al administrador para recuperar su contraseña.';
            $log_detail = 'Máximo intento de contraseña, usuario bloqueado';

            // Notify Admins
            require_once 'notification_functions.php'; // Ensure helper is available
            $stmtAdmins = $pdo->query("SELECT id FROM usuarios WHERE rol = 'admin'");
            while ($admin = $stmtAdmins->fetch(PDO::FETCH_ASSOC)) {
                $msg = "El usuario con email $email ha sido bloqueado tras 10 intentos fallidos.";
                createNotification($pdo, $admin['id'], 'security', 'Usuario Bloqueado', $msg, 'users.php?search=' . urlencode($email));
            }
        } else {
            $update_stmt = $pdo->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?");
            $update_stmt->execute([$new_attempts, $user_id]);
        }

        log_login_attempt($pdo, $user_id, 'Denegado', $log_detail);

        echo json_encode(['success' => false, 'message' => $response_message]);
    }

} catch (Throwable $e) {
    // Log the error message to a file or error tracking service
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado en el servidor.',
        // 'error' => $e->getMessage() // Uncomment for debugging
    ]);
}
?>
