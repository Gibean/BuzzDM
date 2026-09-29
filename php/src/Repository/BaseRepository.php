<?php
/**
 * BaseRepository
 *
 * Punto de extensión común para los repositorios con soporte multi-tenant.
 *
 * Concentra las tres operaciones que antes se olvidaban de forma sistemática y
 * que formaban la fuga de datos entre empresas:
 *
 *   1. Leer     -> toda consulta se filtra por empresa_id del TenantContext.
 *   2. Escribir-> todo INSERT incluye empresa_id explícitamente, sin depender
 *                 del DEFAULT 1 de la columna.
 *   3. Acceder por id -> findById/update/delete verifican que la fila pertenezca
 *                 al tenant antes de operar, de modo que un id adivinado de
 *                 otra empresa se comporte como "no encontrado" y no como un
 *                 acceso válido.
 *
 * Los repositorios de tablas globales (permisos_rol, tasas, etc.) pueden
 * declarar protected bool $usesTenant = false; para omitir el filtrado.
 */

require_once __DIR__ . '/../Tenant/TenantContext.php';
require_once __DIR__ . '/../../db.php';

abstract class BaseRepository
{
    protected PDO $pdo;

    /** Poner en false en repositorios de tablas que no tienen columna empresa_id. */
    protected bool $usesTenant = true;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \Database::getConnection();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Tenant activo. Prioriza el contexto explícito (CLI, cronjob, test) y cae
     * a la sesión como puente durante la transición, de modo que ninguna ruta
     * quede sin tenant por un desajuste de orden de arranque.
     */
    protected function tenantId(string $detalle = ''): int
    {
        if (!$this->usesTenant) {
            return TenantContext::GLOBAL_SCOPE;
        }

        if (TenantContext::isSet()) {
            return TenantContext::require($detalle);
        }

        if (!empty($_SESSION['empresa_id'])) {
            return (int)$_SESSION['empresa_id'];
        }

        throw new TenantRequiredException($detalle !== '' ? $detalle : static::class);
    }

    /**
     * Añade el predicado de empresa a un array de condiciones WHERE.
     *
     * @param array  $where  Condiciones tipo ['p.estado = ?', ...]
     * @param array  $params Parámetros posicionales que las acompañan
     * @param string $alias   Alias de tabla, o '' si la consulta no usa alias
     */
    protected function scopeTenant(array &$where, array &$params, string $alias = '', string $detalle = ''): void
    {
        if (!$this->usesTenant) {
            return;
        }

        $tenantId = $this->tenantId($detalle);
        if ($tenantId === TenantContext::GLOBAL_SCOPE) {
            return;
        }

        $column = $alias !== '' ? $alias . '.empresa_id' : 'empresa_id';
        $where[] = $column . ' = ?';
        $params[] = $tenantId;
    }

    /**
     * Prepara un INSERT inyectando empresa_id. Devuelve el id generado.
     *
     * @param array $data Columna => valor (el nombre de columna se escapa)
     */
    protected function insertTenant(string $table, array $data, string $detalle = ''): int
    {
        if ($this->usesTenant && !array_key_exists('empresa_id', $data)) {
            $tenantId = $this->tenantId($detalle);
            if ($tenantId === TenantContext::GLOBAL_SCOPE) {
                throw new TenantRequiredException(
                    "INSERT en $table sin empresa_id y sin tenant activo. "
                    . 'Las operaciones globales deben pasar empresa_id explícitamente.'
                );
            }
            $data = ['empresa_id' => $tenantId] + $data;
        }

        $columns = array_map(
            static fn(string $col): string => '`' . str_replace('`', '', $col) . '`',
            array_keys($data)
        );
        $placeholders = array_fill(0, count($data), '?');

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            str_replace('`', '', $table),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->pdo->prepare($sql)->execute(array_values($data));
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Ejecuta la consulta y normaliza el resultado a array o null.
     * PDOStatement::fetch() devuelve false cuando no hay filas, lo que obliga a
     * comprobar contra null en todos los repositorios; aquí se centraliza.
     */
    protected function fetchRow(PDOStatement $stmt, array $params = []): ?array
    {
        if ($params) {
            $stmt->execute($params);
        } elseif (!$stmt->execute()) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Verifica que una fila recuperada pertenezca al tenant activo.
     * Devuelve la fila, o null si no existe o es de otra empresa.
     *
     * Que el acceso indebido se comporte igual que un id inexistente evita
     * además que un atacante pueda enumerar ids de otros tenants comparando
     * "no encontrado" con "sin permiso".
     */
    protected function assertTenantRow(mixed $row): ?array
    {
        if (!is_array($row) || !$this->usesTenant) {
            return is_array($row) ? $row : null;
        }

        $tenantId = $this->tenantId();
        if ($tenantId === TenantContext::GLOBAL_SCOPE) {
            return $row;
        }

        if (!isset($row['empresa_id']) || (int)$row['empresa_id'] !== $tenantId) {
            return null;
        }

        return $row;
    }

    /**
     * UPDATE con filtro de tenant. Devuelve el número de filas afectadas, que
     * será 0 si el id no existe o pertenece a otra empresa.
     */
    protected function updateTenant(string $table, array $data, int $id, string $detalle = ''): int
    {
        if (empty($data)) {
            return 0;
        }

        $setParts = [];
        $params = [];
        foreach ($data as $col => $val) {
            $setParts[] = '`' . str_replace('`', '', $col) . '` = ?';
            $params[] = $val;
        }

        $where = ['`id` = ?'];
        $params[] = $id;
        $this->scopeTenant($where, $params, '', $detalle);

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE %s',
            str_replace('`', '', $table),
            implode(', ', $setParts),
            implode(' AND ', $where)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * UPDATE con filtro de tenant, con semántica de "¿la operación se aplicó?".
     *
     * No devuelve rowCount() a propósito. MySQL informa del número de filas
     * *cambiadas*, así que un guardado sin modificaciones reales daría 0 y la
     * interfaz mostraría un error falso ("no se pudo guardar") sobre un registro
     * que sí pertenece al tenant. Lo que importa responder es si la fila existe
     * dentro del tenant, lo que además hace que un id ajeno devuelva false.
     *
     * @return bool true si la fila existía en el tenant (se haya o no cambiado valores)
     */
    protected function applyTenantUpdate(string $table, array $data, int $id, string $detalle = ''): bool
    {
        if (!$this->rowExists($table, $id, $detalle)) {
            return false;
        }

        $this->updateTenant($table, $data, $id, $detalle);
        return true;
    }

    /**
     * ¿Existe una fila con este id dentro del tenant activo?
     *
     * Se usa donde el valor de retorno es un booleano de "operación realizada".
     * rowCount() no sirve para eso: MySQL devuelve el número de filas *cambiadas*,
     * así que un UPDATE que reescribe el mismo valor informaría 0 aunque la fila
     * exista y sea del tenant correcto.
     */
    protected function rowExists(string $table, int $id, string $detalle = ''): bool
    {
        $where = ['`id` = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', $detalle);

        $sql = sprintf(
            'SELECT 1 FROM `%s` WHERE %s LIMIT 1',
            str_replace('`', '', $table),
            implode(' AND ', $where)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * DELETE con filtro de tenant. Devuelve las filas afectadas.
     */
    protected function deleteTenant(string $table, int $id, string $detalle = ''): int
    {
        $where = ['`id` = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', $detalle);

        $sql = sprintf(
            'DELETE FROM `%s` WHERE %s',
            str_replace('`', '', $table),
            implode(' AND ', $where)
        );

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
