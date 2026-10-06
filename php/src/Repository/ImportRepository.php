<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * ImportRepository
 *
 * Antes de migrar a BaseRepository este repositorio escribía en `importaciones`
 * sin empresa_id. Esa columna tiene DEFAULT 1, así que el historial de
 * importaciones de la empresa 14 se guardaba en la 1 sin error visible, y
 * getHistory() devolvía las importaciones de todas las empresas.
 *
 * findOrCreateCatalog() tenía el mismo problema en categorias, marcas y
 * proveedores: el SELECT por nombre no filtraba por empresa, de modo que al
 * importar un producto con la categoría "Papelería" el tenant 14 reutilizaba la
 * categoría de la empresa 1 (o creaba un duplicado si el nombre no coincidía
 * exactamente). Los índices compuestos uk_proveedores_empresa_nombre e
 * idx_clientes_tenant_nombre confirman que el nombre es único por empresa, no
 * global: la búsqueda debe llevar empresa_id.
 */
class ImportRepository extends BaseRepository
{
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * Empresa activa, expuesta para el servicio.
     *
     * ImportService escribe el movimiento de stock inicial con un INSERT propio
     * (no con insertRow) y necesita el mismo empresa_id que el resto de la
     * importación. Se delega en tenantId() para que el valor salga del contexto
     * explícito y no de un DEFAULT.
     */
    public function tenantIdParaImport(string $detalle = ''): int
    {
        return $this->tenantId($detalle !== '' ? $detalle : 'ImportRepository::tenantIdParaImport');
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
        return $this->insertTenant('importaciones', [
            'usuario_id' => $userId,
            'tipo' => 'general',
            'archivo_nombre' => $fileName,
            'estado' => 'pendiente',
            'detalles' => json_encode($details),
        ], 'ImportRepository::logStart');
    }

    public function logFinish($importId, $status, $details) {
        return $this->applyTenantUpdate('importaciones', [
            'estado' => $status,
            'detalles' => json_encode($details),
        ], (int)$importId, "ImportRepository::logFinish($importId)");
    }

    public function getHistory($limit = 50) {
        $where = ['1=1'];
        $params = [];
        $this->scopeTenant($where, $params, 'i', 'ImportRepository::getHistory');

        // El JOIN con usuarios se acota a la misma empresa: sin esta condición
        // un id de usuario coincidente mostraría el nombre de un administrador de
        // otra empresa junto a la importación.
        $sql = "SELECT i.id, i.fecha, i.tipo, i.archivo_nombre, i.estado, i.detalles,
                       u.nombre as usuario_nombre
                FROM importaciones i
                LEFT JOIN usuarios u ON i.usuario_id = u.id AND u.empresa_id = i.empresa_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY i.fecha DESC
                LIMIT " . (int)$limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper to find or create catalogs (Categories, Brands, Suppliers) via Service delegation or direct call
    // Supports both numeric ID lookups and text name lookups
    public function findOrCreateCatalog($tableName, $name) {
        if ($name === null || (is_string($name) && trim($name) === '')) return null;

        $value = (is_string($name)) ? trim($name) : $name;
        $tenantId = $this->tenantId("ImportRepository::findOrCreateCatalog($tableName)");

        $tableName = str_replace('`', '', $tableName);

        // 1. Si el valor es numérico, buscar primero por ID dentro del tenant.
        // Sin el filtro, un SKU de catálogo de otra empresa se reutilizaba tal cual.
        if (is_numeric($value)) {
            $stmt = $this->pdo->prepare("SELECT id FROM `{$tableName}` WHERE id = ? AND empresa_id = ? LIMIT 1");
            $stmt->execute([(int)$value, $tenantId]);
            if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$result['id'];
        }

        // 2. Buscar por nombre dentro del tenant. El nombre es único por empresa
        // (uk_proveedores_empresa_nombre), nunca global.
        $stmt = $this->pdo->prepare("SELECT id FROM `{$tableName}` WHERE LOWER(nombre) = LOWER(?) AND empresa_id = ? LIMIT 1");
        $stmt->execute([(string)$value, $tenantId]);
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$result['id'];

        // 3. Si el valor es numérico y no se encontró ni por ID ni por nombre, no crear registro basura
        if (is_numeric($value)) return null;

        // 4. Solo crear registro nuevo si el valor es texto (comportamiento original)
        try {
            return $this->insertTenant($tableName, ['nombre' => (string)$value], "ImportRepository::findOrCreateCatalog($tableName)");
        } catch (PDOException $e) {
            // Race condition fallback or error
            return null;
        }
    }

    /**
     * Inserción de una fila de catálogo de negocio (productos, clientes, ...)
     * hecha por el importador, con empresa_id explícita.
     *
     * ImportService montaba el INSERT por concatenación con las columnas que
     * venían del Excel, sin empresa_id. Con el DEFAULT 1, todo lo que importaba
     * el tenant 14 acababa en la empresa 1. Se mantiene la firma por columnas
     * porque las columnas sí vienen de un mapeo fijo del código, no del archivo.
     */
    public function insertRow(string $tableName, array $data, string $detalle = '')
    {
        $columns = [];
        $params = [];
        foreach ($data as $col => $val) {
            $columns[] = '`' . str_replace('`', '', (string)$col) . '`';
            $params[] = $val;
        }

        $sql = sprintf(
            'INSERT INTO `%s` (empresa_id, %s) VALUES (?, %s)',
            str_replace('`', '', $tableName),
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );

        $this->pdo->prepare($sql)->execute(array_merge([$this->tenantId($detalle)], $params));
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * ¿Existe una fila con esta clave única dentro del tenant?
     *
     * El importador consultaba "SELECT id FROM tabla WHERE clave = ?" sin
     * empresa_id, así que el mismo SKU de dos empresas colisionaba: el tenant 14
     * actualizaba el producto del tenant 1 en vez de crear el suyo.
     */
    public function rowExistsByKey(string $tableName, string $keyColumn, $value): bool
    {
        $tableName = str_replace('`', '', $tableName);
        $keyColumn = str_replace('`', '', $keyColumn);

        $stmt = $this->pdo->prepare(
            "SELECT 1 FROM `{$tableName}` WHERE `{$keyColumn}` = ? AND empresa_id = ? LIMIT 1"
        );
        $stmt->execute([$value, $this->tenantId("ImportRepository::rowExistsByKey($tableName)")]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * UPDATE por clave única dentro del tenant.
     */
    public function updateRowByKey(string $tableName, string $keyColumn, $value, array $data, string $detalle = ''): bool
    {
        $tableName = str_replace('`', '', $tableName);
        $keyColumn = str_replace('`', '', $keyColumn);

        $setParts = [];
        $params = [];
        foreach ($data as $col => $val) {
            $setParts[] = '`' . str_replace('`', '', (string)$col) . '` = ?';
            $params[] = $val;
        }

        if (empty($setParts)) {
            return false;
        }

        $tenantId = $this->tenantId($detalle);
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = ? AND empresa_id = ?',
            $tableName,
            implode(', ', $setParts),
            $keyColumn
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(array_merge($params, [$value, $tenantId]));
    }
}
