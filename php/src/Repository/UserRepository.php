<?php
require_once __DIR__ . '/../../db.php';

class UserRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.nombre, u.email, u.rol, u.estado, MAX(ll.fecha) AS ultimo_acceso
            FROM usuarios u
            LEFT JOIN logs_login ll ON u.id = ll.usuario_id
            GROUP BY u.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $data['password'],
            $data['rol'],
            $data['estado']
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data) {
        // Build dynamic update to avoid wiping password if not provided or fields missing
        // But for strict service usage, usually we pass all fields or Specific methods.
        // Let's use a standard full update for basic fields (except password which is separate usually)
        $stmt = $this->pdo->prepare("
            UPDATE usuarios
            SET nombre = ?, email = ?, rol = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['email'],
            $data['rol'],
            $id
        ]);
    }

    public function updateStatus($id, $estado) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = ? WHERE id = ?");
        return $stmt->execute([$estado, $id]);
    }

    public function updatePassword($id, $hashedPassword) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET estado = 'inactivo', email = CONCAT('eliminado_', id, '_', email) WHERE id = ?");
        return $stmt->execute([$id]);
    }


    public function countAdmins() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = 'admin'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getActiveSessionsCount() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS session_count FROM logs_login WHERE DATE(fecha) = CURDATE()");
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // =========================================
    // Two-Factor Authentication Methods
    // =========================================

    /**
     * Enable 2FA for a user
     * @param int $userId User ID
     * @param string $secret Base32 encoded secret
     * @return bool Success
     */
    public function enable2FA(int $userId, string $secret): bool {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios SET
                two_factor_secret = ?,
                two_factor_enabled = 1,
                two_factor_confirmed_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$secret, $userId]);
    }

    /**
     * Disable 2FA for a user
     * @param int $userId User ID
     * @return bool Success
     */
    public function disable2FA(int $userId): bool {
        $stmt = $this->pdo->prepare("
            UPDATE usuarios SET
                two_factor_secret = NULL,
                two_factor_enabled = 0,
                two_factor_confirmed_at = NULL
            WHERE id = ?
        ");
        return $stmt->execute([$userId]);
    }

    /**
     * Get user's 2FA secret
     * @param int $userId User ID
     * @return string|null Secret or null if not set
     */
    public function get2FASecret(int $userId): ?string {
        $stmt = $this->pdo->prepare("SELECT two_factor_secret FROM usuarios WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['two_factor_secret'] ?? null;
    }
}
