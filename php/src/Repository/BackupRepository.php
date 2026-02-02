<?php
require_once __DIR__ . '/../../db.php';

class BackupRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function findAll() {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.archivo, b.ruta_archivo, b.tamaño_bytes, b.fecha,
                   b.tipo, b.estado, b.resultado, b.detalles,
                   u.nombre as usuario_nombre
            FROM backups b
            LEFT JOIN usuarios u ON b.usuario_id = u.id
            ORDER BY b.fecha DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM backups WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findLastAuto() {
        $stmt = $this->pdo->prepare("
            SELECT id, archivo, fecha, estado, tamaño_bytes
            FROM backups
            WHERE tipo = 'automatico'
            ORDER BY fecha DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countAutoBackups() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countAutoSuccess() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico' AND estado = 'completado'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO backups
            (archivo, ruta_archivo, tamaño_bytes, tipo, estado, resultado, detalles, usuario_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['archivo'],
            $data['ruta_archivo'],
            $data['tamaño_bytes'] ?? 0,
            $data['tipo'],
            $data['estado'],
            $data['resultado'],
            $data['detalles'],
            $data['usuario_id'] ?? null
        ]);
        return $this->pdo->lastInsertId();
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM backups WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getOldBackups($skip = 10) {
        $stmt = $this->pdo->prepare("
            SELECT id, archivo, ruta_archivo
            FROM backups
            WHERE tipo = 'automatico'
            ORDER BY fecha DESC
            LIMIT 1000 OFFSET ?
        ");
        $stmt->bindValue(1, (int)$skip, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFallbackEnabled() {
        try {
            $stmt = $this->pdo->query("SELECT valor FROM configuraciones WHERE clave = 'backup_fallback_enabled'");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['valor'] === '1' : true; // Default true
        } catch (Exception $e) {
            return true;
        }
    }

    public function setFallbackEnabled($enabled) {
        $val = $enabled ? '1' : '0';
        // Upsert logic
        $stmt = $this->pdo->prepare("SELECT id FROM configuraciones WHERE clave = 'backup_fallback_enabled'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $update = $this->pdo->prepare("UPDATE configuraciones SET valor = ? WHERE clave = 'backup_fallback_enabled'");
            $update->execute([$val]);
        } else {
            $insert = $this->pdo->prepare("INSERT INTO configuraciones (clave, valor) VALUES ('backup_fallback_enabled', ?)");
            $insert->execute([$val]);
        }
        return true;
    }

    public function logAction($userId, $action, $details) {
         $stmt = $this->pdo->prepare("INSERT INTO logs (usuario_id, accion, detalles) VALUES (?, ?, ?)");
         $stmt->execute([$userId, $action, json_encode($details)]);
    }
}
