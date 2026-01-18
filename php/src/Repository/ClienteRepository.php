<?php
require_once __DIR__ . '/../../db.php';

class ClienteRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getConnection();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll($filters = []) {
        $sql = "SELECT * FROM clientes WHERE (id LIKE ? OR nombre LIKE ? OR email LIKE ?)";
        $params = [
            $filters['search'] ?? '%',
            $filters['search'] ?? '%',
            $filters['search'] ?? '%'
        ];

        if (empty($filters['show_deleted'])) {
            $sql .= " AND deleted_at IS NULL";
        }

        if (!empty($filters['vip'])) {
            $sql .= " AND vip = 1";
        } elseif (isset($filters['vip']) && $filters['vip'] === '0') {
            $sql .= " AND vip = 0";
        }

        // Add Pagination limits if needed here
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= " LIMIT " . (int)$filters['limit'] . " OFFSET " . (int)$filters['offset'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findFrequent($limit = 5) {
        $stmt = $this->pdo->prepare("
            SELECT
                c.id,
                c.nombre,
                c.email,
                COUNT(p.id) AS total_orders
            FROM
                clientes c
            JOIN
                pedidos p ON c.id = p.cliente_id
            WHERE
                c.deleted_at IS NULL
            GROUP BY
                c.id, c.nombre, c.email
            ORDER BY
                total_orders DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO clientes (id, nombre, email, telefono, direccion, notas, vip) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['id'],
            $data['nombre'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['notas'] ?? null,
            $data['vip'] ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ?, direccion = ?, notas = ?, vip = ? WHERE id = ?");
        return $stmt->execute([
            $data['nombre'],
            $data['email'] ?? null,
            $data['telefono'] ?? null,
            $data['direccion'] ?? null,
            $data['notas'] ?? null,
            $data['vip'] ? 1 : 0,
            $id
        ]);
    }

    public function softDelete($id) {
        $stmt = $this->pdo->prepare("UPDATE clientes SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function restore($id) {
        $stmt = $this->pdo->prepare("UPDATE clientes SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
