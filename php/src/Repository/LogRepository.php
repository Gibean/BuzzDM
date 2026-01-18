<?php
require_once __DIR__ . '/../../db.php';

class LogRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function getLogs($filters = [], $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;

        $sql = "SELECT ll.id, u.nombre AS nombre_usuario, ll.accion, ll.detalles, ll.fecha
                FROM logs_login ll
                LEFT JOIN usuarios u ON ll.usuario_id = u.id
                WHERE 1=1";
        $countSql = "SELECT COUNT(*) as total
                     FROM logs_login ll
                     LEFT JOIN usuarios u ON ll.usuario_id = u.id
                     WHERE 1=1";

        $params = [];

        // Apply filters
        if (!empty($filters['search'])) {
            $condition = " AND u.nombre LIKE ?";
            $sql .= $condition;
            $countSql .= $condition;
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['dateFrom'])) {
            $condition = " AND DATE(ll.fecha) >= ?";
            $sql .= $condition;
            $countSql .= $condition;
            $params[] = $filters['dateFrom'];
        }

        if (!empty($filters['dateTo'])) {
            $condition = " AND DATE(ll.fecha) <= ?";
            $sql .= $condition;
            $countSql .= $condition;
            $params[] = $filters['dateTo'];
        }

        // Get total count
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $totalPages = ceil($totalRecords / $limit);

        // Get records
        $sql .= " ORDER BY ll.fecha DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $logs,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_records' => $totalRecords,
                'per_page' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ];
    }
}
