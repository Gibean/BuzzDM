<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * BackupRepository
 *
 * `backups`, `configuraciones` y `logs` son tablas de instalación, no datos de
 * negocio de un tenant. Un respaldo mysqldump es de toda la base: pertenece a la
 * instalación, no a la empresa que lo disparó por accidente. Por eso
 * este repositorio se declara global (usesTenant = false) y las lecturas no se
 * filtran: los contadores de salud que consume el panel (countAutoBackups,
 * countAutoSuccess, findLastAuto) cuentan respaldos de toda la instalación, y
 * filtrarlos por tenant cambiaría lo que el administrador ve.
 *
 * Aun así el esquema exige empresa_id NOT NULL en las tres tablas, así que las
 * escrituras lo pasan explícitamente y apuntan a la empresa propietaria de la
 * instalación. Es una atribución declarada en código, no el DEFAULT 1 que
 * escondía el bug: si la consulta no puede resolver esa empresa, falla en vez
 * de escribir en la primera fila que encuentre.
 */
class BackupRepository extends BaseRepository
{
    /** Tablas de instalación: sin filtro de tenant en las lecturas. */
    protected bool $usesTenant = false;

    private ?int $sistemaEmpresaId = null;

    /**
     * Empresa propietaria de la instalación, a la que se atribuyen las filas de
     * sistema (respaldos, flags de configuración y bitácora de acciones).
     *
     * Se resuelve por consulta y se cachea. Es la empresa de menor id, que en
     * esta instalación es la primera que se registró. No es un tenant de negocio:
     * usarla solo evita cumplir la columna NOT NULL sin inventar un tenant en
     * cada escritura.
     */
    private function empresaPropietaria(): int
    {
        if ($this->sistemaEmpresaId !== null) {
            return $this->sistemaEmpresaId;
        }

        $id = $this->pdo->query("SELECT MIN(id) FROM empresa")->fetchColumn();

        if ($id === null || (int)$id <= 0) {
            throw new RuntimeException(
                'No hay empresas en la tabla `empresa`: no se puede atribuir el registro de sistema. '
                . 'Revisa la instalación antes de ejecutar un respaldo.'
            );
        }

        return $this->sistemaEmpresaId = (int)$id;
    }

    public function findAll()
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.empresa_id, b.archivo, b.ruta_archivo, b.tamaño_bytes, b.fecha,
                   b.tipo, b.estado, b.resultado, b.detalles,
                   u.nombre as usuario_nombre
            FROM backups b
            LEFT JOIN usuarios u ON b.usuario_id = u.id
            ORDER BY b.fecha DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM backups WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findLastAuto()
    {
        $stmt = $this->pdo->prepare("
            SELECT id, archivo, fecha, estado, tamaño_bytes
            FROM backups
            WHERE tipo = 'automatico'
            ORDER BY fecha DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function countAutoBackups()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countAutoSuccess()
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM backups WHERE tipo = 'automatico' AND estado = 'completado'");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function create(array $data)
    {
        // empresa_id explícito: la tabla es NOT NULL y este INSERT antes la
        // omitía, lo que hacía fallar el respaldo entero con
        // "Field 'empresa_id' doesn't have a default value".
        return $this->insertTenant('backups', [
            'empresa_id' => $this->empresaPropietaria(),
            'archivo' => $data['archivo'],
            'ruta_archivo' => $data['ruta_archivo'],
            'tamaño_bytes' => $data['tamaño_bytes'] ?? 0,
            'tipo' => $data['tipo'],
            'estado' => $data['estado'],
            'resultado' => $data['resultado'],
            'detalles' => $data['detalles'],
            'usuario_id' => $data['usuario_id'] ?? null,
        ], 'BackupRepository::create');
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM backups WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getOldBackups($skip = 10)
    {
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

    public function getFallbackEnabled()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT valor FROM configuraciones WHERE clave = ?");
            $stmt->execute(['backup_fallback_enabled']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['valor'] === '1' : true; // Default true
        } catch (Exception $e) {
            return true;
        }
    }

    public function setFallbackEnabled($enabled)
    {
        $val = $enabled ? '1' : '0';
        $empresaId = $this->empresaPropietaria();

        $select = $this->pdo->prepare("SELECT id FROM configuraciones WHERE empresa_id = ? AND clave = ?");
        $select->execute([$empresaId, 'backup_fallback_enabled']);

        if ($select->fetch()) {
            $update = $this->pdo->prepare("UPDATE configuraciones SET valor = ? WHERE empresa_id = ? AND clave = ?");
            return $update->execute([$val, $empresaId, 'backup_fallback_enabled']);
        }

        $this->insertTenant('configuraciones', [
            'empresa_id' => $empresaId,
            'clave' => 'backup_fallback_enabled',
            'valor' => $val,
        ], 'BackupRepository::setFallbackEnabled');

        return true;
    }

    public function logAction($userId, $action, $details)
    {
        $this->insertTenant('logs', [
            'empresa_id' => $this->empresaPropietaria(),
            'usuario_id' => $userId,
            'accion' => $action,
            'detalles' => json_encode($details),
        ], 'BackupRepository::logAction');
    }
}
