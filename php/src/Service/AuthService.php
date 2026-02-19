<?php
require_once __DIR__ . '/../Repository/AuthRepository.php';
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../email_config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * AuthService - Business logic for authentication
 */
class AuthService {
    private $repository;

    public function __construct() {
        $this->repository = new AuthRepository();
    }

    /**
     * Handle user login
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            throw new Exception('El email y la contraseña son obligatorios.');
        }

        $user = $this->repository->findUserByEmail($email);

        if (!$user) {
            $this->repository->logLoginAttempt(null, 'Denegado', "Intento con email no existente: $email");
            throw new Exception('Credenciales incorrectas.');
        }

        $userId = $user['id'];

        if ($user['estado'] === 'bloqueado' || $user['estado'] === 'inactivo') {
            $reason = ($user['estado'] === 'bloqueado') ? 'Usuario bloqueado' : 'Usuario desactivado';
            $this->repository->logLoginAttempt($userId, 'Denegado', $reason);
            $message = ($user['estado'] === 'bloqueado')
                ? 'Usuario bloqueado. Contacte al administrador.'
                : 'Esta cuenta ha sido desactivada. Contacte al administrador.';
            throw new Exception($message);
        }

        if (!password_verify($password, $user['password'])) {
            return $this->handleFailedLogin($user, $email);
        }

        return $this->handleSuccessfulLogin($user, $email);
    }

    private function handleFailedLogin($user, $email) {
        $newAttempts = ($user['intentos_fallidos'] ?? 0) + 1;
        $message = 'Credenciales incorrectas.';
        $logDetail = 'Contraseña incorrecta';

        if ($newAttempts >= 10) {
            $this->repository->blockUser($user['id'], $newAttempts);
            $message = 'Demasiados intentos, usuario bloqueado.';
            $logDetail = 'Usuario bloqueado por máximos intentos';

            // Notify admins
            $this->notifyAdminsOfBlock($email);
        } else {
            $this->repository->updateFailedAttempts($user['id'], $newAttempts);
        }

        $this->repository->logLoginAttempt($user['id'], 'Denegado', $logDetail);
        throw new Exception($message);
    }

    private function handleSuccessfulLogin($user, $email) {
        if (($user['intentos_fallidos'] ?? 0) > 0) {
            $this->repository->resetFailedAttempts($user['id']);
        }

        // Check if user has 2FA enabled
        if (!empty($user['two_factor_secret']) && isset($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1) {
            // Store pending login data in session for 2FA verification
            $_SESSION['pending_2fa_user_id'] = $user['id'];
            $_SESSION['pending_2fa_email'] = $email;
            $_SESSION['pending_2fa_time'] = time();

            return [
                'success' => true,
                'requires_2fa' => true,
                'message' => 'Se requiere verificación de dos factores.',
                'user' => [
                    'id' => $user['id'],
                    'nombre' => $user['nombre']
                ]
            ];
        }

        return $this->completeLogin($user, $email);
    }

    /**
     * Complete login after password (and optional 2FA) verification
     */
    private function completeLogin($user, $email) {
        $this->repository->logLoginAttempt($user['id'], 'Exitoso', 'Login de ' . $user['rol']);

        // Cleanup expired sessions before creating a new one (prevents table bloat)
        $this->repository->cleanupExpiredSessions();

        // Create API session token
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+8 hours'));
        $this->repository->createSession($user['id'], $token, $expiration);

        // Set PHP session
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_rol'] = $user['rol'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['email'] = $email;

        // Clear any pending 2FA data
        unset($_SESSION['pending_2fa_user_id']);
        unset($_SESSION['pending_2fa_email']);
        unset($_SESSION['pending_2fa_time']);

        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'redirect' => 'dashboard',
            'user' => [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'rol' => $user['rol']
            ]
        ];
    }

    /**
     * Verify 2FA code and complete login
     */
    public function verify2FA($code) {
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            throw new Exception('No hay una verificación 2FA pendiente.');
        }

        // Check timeout (5 minutes)
        if (time() - ($_SESSION['pending_2fa_time'] ?? 0) > 300) {
            unset($_SESSION['pending_2fa_user_id']);
            unset($_SESSION['pending_2fa_email']);
            unset($_SESSION['pending_2fa_time']);
            throw new Exception('La verificación 2FA ha expirado. Inicia sesión nuevamente.');
        }

        $userId = $_SESSION['pending_2fa_user_id'];
        $email = $_SESSION['pending_2fa_email'];

        require_once __DIR__ . '/TwoFactorService.php';
        $twoFactorService = new TwoFactorService();

        if (!$twoFactorService->validateLogin($userId, $code)) {
            throw new Exception('Código de verificación incorrecto.');
        }

        // 2FA verified, complete login
        $user = $this->repository->findUserById($userId);
        if (!$user) {
            throw new Exception('Usuario no encontrado.');
        }

        return $this->completeLogin($user, $email);
    }


    private function notifyAdminsOfBlock($email) {
        try {
            require_once __DIR__ . '/../../notification_functions.php';
            $pdo = $this->repository->getPdo();
            $admins = $this->repository->getAdminUsers();
            foreach ($admins as $admin) {
                $msg = "Usuario $email bloqueado tras 10 intentos fallidos.";
                createNotification($pdo, $admin['id'], 'security', 'Usuario Bloqueado', $msg, 'admin');
            }
        } catch (Exception $e) {
            error_log("Error notifying admins: " . $e->getMessage());
        }
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword($email) {
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Correo electrónico inválido.');
        }

        $user = $this->repository->findUserByEmail($email);

        // Security: always show same message
        if (!$user) {
            usleep(500000);
            return ['success' => true, 'message' => 'Si el correo está registrado, recibirás un enlace de recuperación.'];
        }

        if ($user['estado'] === 'bloqueado' || $user['estado'] === 'inactivo') {
            throw new Exception('Esta cuenta está ' . $user['estado'] . '. Contacta al administrador.');
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->repository->invalidatePreviousTokens($user['id']);
        $this->repository->createPasswordResetToken($user['id'], $token, $expiration);

        // Send email
        try {
            $this->sendPasswordResetEmail($user, $token);
        } catch (Exception $e) {
            error_log("Error sending password reset email: " . $e->getMessage());
            // Still return success for security (don't reveal if email failed)
        }

        return ['success' => true, 'message' => 'Si el correo está registrado, recibirás un enlace de recuperación.'];
    }

    private function sendPasswordResetEmail($user, $token) {
        $resetLink = app_url('reset_password') . '?token=' . $token;
        $mail = new PHPMailer(true);
        $config = getMailerConfig();

        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = $config['secure'];
        $mail->Port = $config['port'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($user['email'], $user['nombre']);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - BUZZ Data Manager';
        $mail->Body = $this->getPasswordResetEmailBody($user['nombre'], $resetLink);
        $mail->AltBody = "Hola {$user['nombre']}, visita {$resetLink} para restablecer tu contraseña.";

        $mail->send();
    }

    private function getPasswordResetEmailBody($nombre, $link) {
        return "
        <html><body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #1a1f28, #2a3040); color: #FFA000; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1>🔐 Recuperación de Contraseña</h1>
            </div>
            <div style='background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;'>
                <p>Hola <strong>$nombre</strong>,</p>
                <p>Haz clic en el botón para restablecer tu contraseña:</p>
                <div style='text-align: center;'>
                    <a href='$link' style='display: inline-block; padding: 15px 30px; background: #FFA000; color: #1a1f28; text-decoration: none; border-radius: 5px; font-weight: bold;'>Restablecer Contraseña</a>
                </div>
                <p style='margin-top: 20px;'><strong>⚠️ Este enlace expira en 1 hora.</strong></p>
            </div>
        </div>
        </body></html>";
    }

    /**
     * Validate a password reset token (check if valid/not expired)
     */
    public function validateResetToken($token) {
        if (!$token) {
            return ['valid' => false, 'message' => 'Token no proporcionado.'];
        }

        $tokenData = $this->repository->getTokenData($token);

        if (!$tokenData) {
            return ['valid' => false, 'message' => 'Enlace inválido o ya utilizado.'];
        }

        if ($tokenData['usado'] == 1) {
            return ['valid' => false, 'message' => 'Este enlace ya fue utilizado.'];
        }

        $now = new DateTime();
        $expiration = new DateTime($tokenData['expiracion']);
        if ($now > $expiration) {
            return ['valid' => false, 'message' => 'El enlace ha expirado. Solicita uno nuevo.'];
        }

        return ['valid' => true, 'message' => 'Token válido.'];
    }

    /**
     * Handle password reset
     */
    public function resetPassword($token, $password, $confirmPassword) {
        if (!$token) throw new Exception('Token no proporcionado.');
        if (empty($password)) throw new Exception('Contraseña requerida.');
        if (strlen($password) < 8) throw new Exception('Mínimo 8 caracteres.');
        if (!preg_match('/[A-Z]/', $password)) throw new Exception('Debe contener mayúscula.');
        if (!preg_match('/[a-z]/', $password)) throw new Exception('Debe contener minúscula.');
        if (!preg_match('/[0-9]/', $password)) throw new Exception('Debe contener número.');
        if ($password !== $confirmPassword) throw new Exception('Las contraseñas no coinciden.');

        $tokenData = $this->repository->getTokenData($token);

        if (!$tokenData) throw new Exception('Enlace inválido.');
        if ($tokenData['usado'] == 1) throw new Exception('Enlace ya utilizado.');

        $now = new DateTime();
        $expiration = new DateTime($tokenData['expiracion']);
        if ($now > $expiration) throw new Exception('Enlace expirado.');

        if ($tokenData['estado'] === 'bloqueado' || $tokenData['estado'] === 'inactivo') {
            throw new Exception('Cuenta ' . $tokenData['estado'] . '. Contacta al administrador.');
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $pdo = $this->repository->getPdo();
        $pdo->beginTransaction();

        try {
            $this->repository->updatePassword($tokenData['usuario_id'], $hashedPassword);
            $this->repository->markTokenUsed($tokenData['id']);
            $this->repository->invalidatePreviousTokens($tokenData['usuario_id']);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }

        return ['success' => true, 'message' => '¡Contraseña actualizada! Serás redirigido al login...'];
    }

    /**
     * Handle logout
     */
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada.', 'redirect' => 'login'];
    }
}
