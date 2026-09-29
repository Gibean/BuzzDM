<?php
require_once __DIR__ . '/BaseRepository.php';

class ClienteRepository extends BaseRepository
{
    public function findById($id)
    {
        $where = ['id = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', "ClienteRepository::findById($id)");

        $stmt = $this->pdo->prepare("SELECT * FROM clientes WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }

    public function findAll($filters = [])
    {
        $searchParam = isset($filters['search']) && $filters['search'] !== '' ? '%' . $filters['search'] . '%' : '%';
        $where = ["(id LIKE ? OR nombre LIKE ? OR email LIKE ? OR telefono LIKE ?)"];
        $params = [$searchParam, $searchParam, $searchParam, $searchParam];

        $this->scopeTenant($where, $params, '', 'ClienteRepository::findAll');

        if (empty($filters['show_deleted'])) {
            $where[] = 'deleted_at IS NULL';
        }

        // Type filter: 'vip' or 'regular'
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'vip') {
                $where[] = 'vip = 1';
            } elseif ($filters['type'] === 'regular') {
                $where[] = 'vip = 0';
            }
        }

        // Date filters
        if (!empty($filters['dateFrom'])) {
            $where[] = 'DATE(creado_at) >= ?';
            $params[] = $filters['dateFrom'];
        }

        if (!empty($filters['dateTo'])) {
            $where[] = 'DATE(creado_at) <= ?';
            $params[] = $filters['dateTo'];
        }

        $sql = 'SELECT * FROM clientes WHERE ' . implode(' AND ', $where) . ' ORDER BY creado_at DESC';

        // Add Pagination limits if needed here
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $sql .= ' LIMIT ' . (int)$filters['limit'] . ' OFFSET ' . (int)$filters['offset'];
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findFrequent($limit = 5)
    {
        // Ambos lados del JOIN se acotan al tenant. Filtrar sólo c no basta:
        // el COUNT(p.id) seguiría contando pedidos de otra empresa si algún
        // cliente compartiera id, y además aparecerían clientes ajenos.
        $where = ['c.deleted_at IS NULL', 'p.empresa_id = c.empresa_id'];
        $params = [];
        $this->scopeTenant($where, $params, 'c', 'ClienteRepository::findFrequent');

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
            WHERE " . implode(' AND ', $where) . "
            GROUP BY
                c.id, c.nombre, c.email
            ORDER BY
                total_orders DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        // El id lo aporta el llamante (numeración compartida con otros módulos),
        // por eso se pasa dentro de $data en lugar de dejar que lo genere MySQL.
        return $this->insertTenant('clientes', [
            'id' => $data['id'],
            'nombre' => $data['nombre'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'notas' => $data['notas'] ?? null,
            'vip' => $data['vip'] ? 1 : 0,
        ], 'ClienteRepository::create') > 0;
    }

    public function update($id, $data)
    {
        return $this->applyTenantUpdate('clientes', [
            'nombre' => $data['nombre'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'notas' => $data['notas'] ?? null,
            'vip' => $data['vip'] ? 1 : 0,
        ], (int)$id, "ClienteRepository::update($id)");
    }

    public function softDelete($id)
    {
        return $this->applyTenantUpdate(
            'clientes',
            ['deleted_at' => date('Y-m-d H:i:s')],
            (int)$id,
            "ClienteRepository::softDelete($id)"
        );
    }

    public function restore($id)
    {
        return $this->applyTenantUpdate(
            'clientes',
            ['deleted_at' => null],
            (int)$id,
            "ClienteRepository::restore($id)"
        );
    }
}
