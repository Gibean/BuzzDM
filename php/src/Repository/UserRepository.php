<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * UserRepository
 *
 * Antes de la migración este repositorio no filtraba por empresa en ningún
 * método: findAll() devolvía los usuarios de todas las empresas, create() omía
 * empresa_id (y la columna tiene DEFAULT 1, así que el usuario de la empresa 14
 * se creaba en la 1) y get2FASecret() devolvía el secreto de doble factor de
 * cualquier usuario que se le pidiera por id.
 *
 * El alcance NO es uniforme en este repositorio, y es a propósito:
 *
 *   - La gestión de usuarios (findAll, findById, update, delete, 2FA) es por
 *     tenant: un administrador solo debe gestionar su propia empresa.
 *   - findByEmail() sigue siendo global a propósito. `usuarios.email` tiene
 *     indice UNIQUE global, no (empresa_id, email), asi que la comprobacion de
 *     unicidad que hace UserService al crear un usuario tiene que ser global.
 *     Si se filtrara por tenant, un tenant podria reutilizar el correo de otro
 *     y el INSERT fallaria con duplicate key en lugar de dar un error claro.
 *   - countAdmins() es por tenant. Antes contaba los admins de todas las
 *     empresas, y como UserService lo usa para no permitir borrar al ultimo
 *     administrador, un tenant sin admins se quedaba bloqueado por el admin de
 *     otra empresa.
 */
class UserRepository extends BaseRepository
{
    public function findAll() {
        $where = ['1=1'];
        $params = [];
        $this->scopeTenant($where, $params, 'u', 'UserRepository::findAll');

        $stmt = $this->pdo->prepare("
            SELECT u.id, u.empresa_id, u.nombre, u.email, u.rol, u.estado,
                   MAX(ll.fecha) AS ultimo_acceso
            FROM usuarios u
            LEFT JOIN logs_login ll ON u.id = ll.usuario_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY u.id
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $where = ['id = ?'];
        $params = [$id];
        $this->scopeTenant($where, $params, '', "UserRepository::findById($id)");

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return $this->assertTenantRow($stmt->fetch(PDO::FETCH_ASSOC) ?: null);
    }

    /**
     * Busca por correo en TODAS las empresas.
     *
     * No aplicar scopeTenant aqui a proposito: el indice unico de email es
     * global y la comprobacion de unicidad de UserService depende de que esta
     * consulta vea tambien los correos de otros tenants.
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data) {
        // empresa_id lo inyecta insertTenant. Antes se omitia y el usuario
        // acababa en la empresa 1 por el DEFAULT de la columna.
        return $this->insertTenant('usuarios', [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'password' => $data['password'],
            'rol' => $data['rol'],
            'estado' => $data['estado'],
        ], 'UserRepository::create');
    }

    public function update($id, array $data) {
        // No se toca password: tiene su propio metodo. applyTenantUpdate en vez
        // de un UPDATE directo para que un id de otro tenant no se reporte como
        // guardado.
        return $this->applyTenantUpdate('usuarios', [
            'nombre' => $data['nombre'],
            'email' => $data['email'],
            'rol' => $data['rol'],
        ], (int)$id, "UserRepository::update($id)");
    }

    public function updateStatus($id, $estado) {
        return $this->applyTenantUpdate('usuarios', ['estado' => $estado], (int)$id, "UserRepository::updateStatus($id)");
    }

    public function updatePassword($id, $hashedPassword) {
        return $this->applyTenantUpdate('usuarios', ['password' => $hashedPassword], (int)$id, "UserRepository::updatePassword($id)");
    }

    public function delete($id) {
        return $this->applyTenantUpdate('usuarios', [
            'estado' => 'inactivo',
            'email' => $this->emailDeEliminado((int)$id),
        ], (int)$id, "UserRepository::delete($id)");
    }

    /**
     * Email liberador de un usuario dado de baja.
     *
     * El original usaba CONCAT('eliminado_', id, '_', email) en la propia
     * sentencia, lo que dejaba el email original dentro del nuevo valor: la
     * columna deja de ser unico-safe si dos bajas se reiteran y, sobre todo,
     * depende de que MySQL evalue email antes de sobrescribirlo. Se compone
     * en PHP con el valor leido.
     */
    private function emailDeEliminado(int $id): string
    {
        $stmt = $this->pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $email = (string)$stmt->fetchColumn();

        return 'eliminado_' . $id . '_' . $email;
    }

    public function countAdmins() {
        $where = ["rol = 'admin'"];
        $params = [];
        $this->scopeTenant($where, $params, '', 'UserRepository::countAdmins');

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE " . implode(' AND ', $where));
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    /**
     * Sesiones activas hoy, ya solo las de este tenant.
     *
     * Los intentos de login con correo inexistente quedan en logs_login con
     * empresa_id NULL y no se cuentan para ningun tenant, que es lo correcto:
     * no se pueden atribuir a una empresa.
     */
    public function getActiveSessionsCount() {
        $where = ['DATE(ll.fecha) = CURDATE()'];
        $params = [];
        $this->scopeTenant($where, $params, 'll', 'UserRepository::getActiveSessionsCount');

        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) AS session_count FROM logs_login ll WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['session_count'];
    }

    // =========================================
    // Two-Factor Authentication Methods
    // =========================================

    /**
     * Enable 2FA for a user
     * @param int $userId User ID
     * @param string $secret Secret key to store
     * @return bool Success
     */
    public function enable2FA(int $userId, string $secret): bool {
        return $this->applyTenantUpdate('usuarios', [
            'two_factor_secret' => $secret,
            'two_factor_enabled' => 1,
            'two_factor_confirmed_at' => date('Y-m-d H:i:s'),
        ], $userId, "UserRepository::enable2FA($userId)");
    }

    /**
     * Disable 2FA for a user
     * @param int $userId User ID
     * @return bool Success
     */
    public function disable2FA(int $userId): bool {
        return $this->applyTenantUpdate('usuarios', [
            'two_factor_secret' => null,
            'two_factor_enabled' => 0,
            'two_factor_confirmed_at' => null,
        ], $userId, "UserRepository::disable2FA($userId)");
    }

    /**
     * Get user's 2FA secret
     * @param int $userId User ID
     * @return string|null Secret or null if not set
     */
    public function get2FASecret(int $userId): ?string {
        $where = ['id = ?'];
        $params = [$userId];
        $this->scopeTenant($where, $params, '', "UserRepository::get2FASecret($userId)");

        // empresa_id va en el SELECT a proposito: assertTenantRow() compara
        // contra esa columna, asi que sin ella devolveria null siempre, tambien
        // para el propio usuario del tenant.
        $stmt = $this->pdo->prepare(
            "SELECT empresa_id, two_factor_secret FROM usuarios WHERE " . implode(' AND ', $where)
        );
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$this->assertTenantRow($row ?: null)) {
            return null;
        }

        return $row['two_factor_secret'] ?? null;
    }
}
