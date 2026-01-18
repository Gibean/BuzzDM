<?php
require_once __DIR__ . '/../../db.php';

class ImportRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function beginTransaction() {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function logStart($userId, $fileName, $details = []) {
        $stmt = $this->pdo->prepare("INSERT INTO importaciones (usuario_id, tipo, archivo_nombre, estado, detalles) VALUES (?, 'general', ?, 'pendiente', ?)");
        $stmt->execute([$userId, $fileName, json_encode($details)]);
        return $this->pdo->lastInsertId();
    }

    public function logFinish($importId, $status, $details) {
        $stmt = $this->pdo->prepare("UPDATE importaciones SET estado = ?, detalles = ? WHERE id = ?");
        return $stmt->execute([$status, json_encode($details), $importId]);
    }

    public function getHistory($limit = 50) {
        $sql = "SELECT i.id, i.fecha, i.tipo, i.archivo_nombre, i.estado, i.detalles, u.nombre as usuario_nombre
                FROM importaciones i
                LEFT JOIN usuarios u ON i.usuario_id = u.id
                ORDER BY i.fecha DESC
                LIMIT " . (int)$limit;

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper to find or create catalogs (Categories, Brands, Suppliers) via Service delegation or direct call
    // Logic extracted from original process_import.php findOrCreate
    public function findOrCreateCatalog($tableName, $name) {
        if (empty($name) || trim($name) === '') return null;
        $name = trim($name);

        // Check cache or simple query
        // Note: Table names are trusted from internal config, ensuring no SQL injection risk from user input here if properly managed in Service
        $stmt = $this->pdo->prepare("SELECT id FROM {$tableName} WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        $stmt->execute([$name]);
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$result['id'];

        try {
            $stmt = $this->pdo->prepare("INSERT INTO {$tableName} (nombre) VALUES (?)");
            $stmt->execute([$name]);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            // Race condition fallback or error
            return null;
        }
    }
}
