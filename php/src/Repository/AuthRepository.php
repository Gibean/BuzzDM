<?php
require_once __DIR__ . '/../../db.php';

/**
 * AuthRepository - Handles DB operations for authentication
 */
class AuthRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Find user by email
     */
    public function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, email, password, rol, estado, intentos_fallidos FROM usuarios WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Find user by ID
     */
    public function findUserById($id) {
        $stmt = $this->pdo->prepare("SELECT id, nombre, email, rol, estado FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update failed login attempts
     */
    public function updateFailedAttempts($userId, $attempts) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?");
        return $stmt->execute([$attempts, $userId]);
    }

    /**
     * Block user
     */
    public function blockUser($userId, $attempts) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = 'bloqueado', intentos_fallidos = ? WHERE id = ?");
        return $stmt->execute([$attempts, $userId]);
    }

    /**
     * Reset failed attempts
     */
    public function resetFailedAttempts($userId) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Create API session
     */
    public function createSession($userId, $token, $expiration) {
        $stmt = $this->pdo->prepare("INSERT INTO sesiones_api (usuario_id, token, expiracion) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $token, $expiration]);
    }

    /**
     * Log login attempt
     */
    public function logLoginAttempt($userId, $action, $details) {
        $stmt = $this->pdo->prepare("INSERT INTO logs_login (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$userId, $action, $details]);
        return $this->pdo->lastInsertId();
    }

    /**
     * Invalidate previous password tokens
     */
    public function invalidatePreviousTokens($userId) {
        $stmt = $this->pdo->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE usuario_id = ? AND usado = 0");
        return $stmt->execute([$userId]);
    }

    /**
     * Create password reset token
     */
    public function createPasswordResetToken($userId, $token, $expiration) {
        $stmt = $this->pdo->prepare("INSERT INTO password_reset_tokens (usuario_id, token, expiracion) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $token, $expiration]);
    }

    /**
     * Get password reset token data
     */
    public function getTokenData($token) {
        $stmt = $this->pdo->prepare("
            SELECT prt.id, prt.usuario_id, prt.expiracion, prt.usado, u.email, u.nombre, u.estado
            FROM password_reset_tokens prt
            INNER JOIN usuarios u ON prt.usuario_id = u.id
            WHERE prt.token = ? LIMIT 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark token as used
     */
    public function markTokenUsed($tokenId) {
        $stmt = $this->pdo->prepare("UPDATE password_reset_tokens SET usado = 1 WHERE id = ?");
        return $stmt->execute([$tokenId]);
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $hashedPassword) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET password = ?, actualizado_at = NOW(), intentos_fallidos = 0 WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }

    /**
     * Get all admin users
     */
    public function getAdminUsers() {
        $stmt = $this->pdo->query("SELECT id FROM usuarios WHERE rol = 'admin'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cleanup expired sessions to prevent table bloat
     * Removes sessions that expired more than 24 hours ago
     */
    public function cleanupExpiredSessions() {
        $stmt = $this->pdo->prepare("DELETE FROM sesiones_api WHERE expiracion < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        return $stmt->execute();
    }
}
