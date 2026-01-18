<?php
require_once __DIR__ . '/../../db.php';

class SupplierRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, rif, telefono, email, direccion, notas, creado_at
            FROM proveedores
            WHERE deleted_at IS NULL
            ORDER BY nombre ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM proveedores WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO proveedores (nombre, rif, telefono, email, direccion, notas)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['rif'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['notas'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data) {
        $stmt = $this->pdo->prepare("
            UPDATE proveedores
            SET nombre = ?, rif = ?, telefono = ?, email = ?, direccion = ?, notas = ?, actualizado_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ");
        return $stmt->execute([
            $data['nombre'],
            $data['rif'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['direccion'] ?? null,
            $data['notas'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("UPDATE proveedores SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
