<?php
require_once __DIR__ . '/../../db.php';

class SettingsRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function findCompanyInfo() {
        $stmt = $this->pdo->query("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function companyExists() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM empresa");
        return $stmt->fetchColumn() > 0;
    }

    public function createCompanyInfo(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO empresa (rif, nombre, direccion, telefono, email)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['rif'],
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $data['email']
        ]);
    }

    public function updateCompanyInfo(array $data) {
        $stmt = $this->pdo->prepare("
            UPDATE empresa
            SET rif = ?, nombre = ?, direccion = ?, telefono = ?, email = ?, actualizado_at = NOW()
            WHERE id = 1
        ");
        return $stmt->execute([
            $data['rif'],
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $data['email']
        ]);
    }

    public function updateLogo($relativePath) {
        $stmt = $this->pdo->prepare("UPDATE empresa SET logo_svg = ?, actualizado_at = NOW() WHERE id = 1");
        return $stmt->execute([$relativePath]);
    }
}
