<?php
require_once __DIR__ . '/BaseRepository.php';

class SupplierRepository extends BaseRepository
{
    public function findAll()
    {
        $where = ['deleted_at IS NULL'];
        $params = [];
        $this->scopeTenant($where, $params, '', 'SupplierRepository::findAll');

        $stmt = $this->pdo->prepare("
            SELECT id, empresa_id, nombre, rif, telefono, email, direccion, notas, creado_at
            FROM proveedores
            WHERE " . implode(' AND ', $where) . "
            ORDER BY nombre ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search($query, $limit = 10)
    {
        $where = ['deleted_at IS NULL', '(nombre LIKE ? OR contacto_nombre LIKE ?)'];
        $params = ['%' . $query . '%', '%' . $query . '%'];
        $this->scopeTenant($where, $params, '', 'SupplierRepository::search');

        $stmt = $this->pdo->prepare("
            SELECT id, nombre, contacto_nombre, telefono
            FROM proveedores
            WHERE " . implode(' AND ', $where) . "
            ORDER BY nombre ASC
            LIMIT " . (int)$limit . "
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $where = ['id = ?', 'deleted_at IS NULL'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', "SupplierRepository::findById($id)");

        $stmt = $this->pdo->prepare(
            'SELECT * FROM proveedores WHERE ' . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }

    public function create(array $data)
    {
        return $this->insertTenant('proveedores', [
            'nombre' => $data['nombre'],
            'rif' => $data['rif'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'notas' => $data['notas'] ?? null,
        ], 'SupplierRepository::create');
    }

    public function update($id, array $data)
    {
        return $this->applyTenantUpdate('proveedores', [
            'nombre' => $data['nombre'],
            'rif' => $data['rif'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'email' => $data['email'] ?? null,
            'direccion' => $data['direccion'] ?? null,
            'notas' => $data['notas'] ?? null,
            'actualizado_at' => date('Y-m-d H:i:s'),
        ], (int)$id, "SupplierRepository::update($id)");
    }

    public function delete($id)
    {
        return $this->applyTenantUpdate(
            'proveedores',
            ['deleted_at' => date('Y-m-d H:i:s')],
            (int)$id,
            "SupplierRepository::delete($id)"
        );
    }
}
