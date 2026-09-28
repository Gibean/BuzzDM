<?php
/**
 * CompanyService
 * 
 * Gestiona el ciclo de vida de los tenants (empresas) y el aprovisionamiento
 * del entorno de prueba de 15 días (SaaS Demo Multi-tenant).
 */

require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/DeviceFingerprintService.php';

class CompanyService {
    private PDO $pdo;
    private DeviceFingerprintService $fingerprintService;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
        $this->fingerprintService = new DeviceFingerprintService($this->pdo);
    }

    /**
     * Registra y aprovisiona un entorno de prueba de 15 días
     */
    public function createTrialEnvironment(array $data): array {
        $companyName = trim($data['company_name'] ?? '');
        $adminEmail = strtolower(trim($data['admin_email'] ?? ''));
        $adminPassword = $data['admin_password'] ?? '';
        $adminName = trim($data['admin_name'] ?? 'Administrador');
        $clientFp = $data['device_fingerprint'] ?? null;

        // Validaciones básicas
        if (empty($companyName) || strlen($companyName) < 3) {
            throw new InvalidArgumentException("El nombre de la empresa debe tener al menos 3 caracteres.");
        }
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("El correo electrónico del administrador no es válido.");
        }
        if (strlen($adminPassword) < 8) {
            throw new InvalidArgumentException("La contraseña debe tener al menos 8 caracteres.");
        }

        // Generar y validar huella digital
        $fingerprint = $this->fingerprintService->generateFingerprint($clientFp);
        if ($this->fingerprintService->isDeviceOrEmailBlocked($fingerprint, $adminEmail)) {
            throw new RuntimeException("Ya se ha registrado una prueba de demostración desde este dispositivo o correo electrónico.");
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Crear Empresa (15 días de prueba)
            $expiresAt = (new DateTime())->modify('+15 days')->format('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare("
                INSERT INTO empresa (
                    nombre, tipo_rif, rif, email, admin_email, device_fingerprint,
                    trial_ends_at, is_demo, status, creado_at
                ) VALUES (
                    :nombre, 'J', :rif, :email, :admin_email, :fingerprint,
                    :expires_at, 1, 'active', NOW()
                )
            ");
            $demoRif = 'J-' . str_pad((string)mt_rand(10000000, 99999999), 9, '0', STR_PAD_LEFT);
            $stmt->execute([
                ':nombre' => $companyName,
                ':rif' => $demoRif,
                ':email' => $adminEmail,
                ':admin_email' => $adminEmail,
                ':fingerprint' => $fingerprint,
                ':expires_at' => $expiresAt
            ]);
            $companyId = (int)$this->pdo->lastInsertId();

            // 2. Crear Almacén Principal Predeterminado para la empresa
            $stmtAlm = $this->pdo->prepare("
                INSERT INTO almacenes (
                    empresa_id, codigo, nombre, tipo, es_principal, activo, creado_at
                ) VALUES (
                    :empresa_id, 'ALM-001', 'Almacén Principal', 'almacen', 1, 1, NOW()
                )
            ");
            $stmtAlm->execute([':empresa_id' => $companyId]);

            // 3. Crear Usuario Administrador de la empresa
            $passwordHash = password_hash($adminPassword, PASSWORD_BCRYPT);
            $stmtUser = $this->pdo->prepare("
                INSERT INTO usuarios (
                    empresa_id, nombre, email, password, rol, estado, creado_at
                ) VALUES (
                    :empresa_id, :nombre, :email, :password, 'admin', 'activo', NOW()
                )
            ");
            $stmtUser->execute([
                ':empresa_id' => $companyId,
                ':nombre' => $adminName,
                ':email' => $adminEmail,
                ':password' => $passwordHash
            ]);
            $userId = (int)$this->pdo->lastInsertId();

            // 4. Inyectar categorías demo esenciales
            $demoCategories = ['General', 'Papelería y Oficina', 'Tecnología'];
            $stmtCat = $this->pdo->prepare("
                INSERT INTO categorias (empresa_id, nombre, descripcion) 
                VALUES (:empresa_id, :nombre, :desc)
            ");
            foreach ($demoCategories as $cat) {
                $stmtCat->execute([
                    ':empresa_id' => $companyId,
                    ':nombre' => $cat,
                    ':desc' => "Categoría inicial $cat"
                ]);
            }

            // 5. Crear directorio de uploads aislado para la empresa
            $uploadDir = __DIR__ . "/../../../assets/img/uploads/companies/{$companyId}/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'company_id' => $companyId,
                'user_id' => $userId,
                'company_name' => $companyName,
                'admin_email' => $adminEmail,
                'expires_at' => $expiresAt,
                'days_remaining' => 15
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Obtiene el estado actual del trial de una empresa
     */
    public function getCompanyTrialStatus(int $companyId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, admin_email, is_demo, status, trial_ends_at,
                   TIMESTAMPDIFF(DAY, NOW(), trial_ends_at) as days_remaining,
                   TIMESTAMPDIFF(HOUR, NOW(), trial_ends_at) as hours_remaining
            FROM empresa
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$company) {
            return ['exists' => false, 'is_expired' => true];
        }

        $isDemo = (bool)$company['is_demo'];
        $isExpired = false;

        if ($isDemo && !empty($company['trial_ends_at'])) {
            $expiresAt = new DateTime($company['trial_ends_at']);
            $now = new DateTime();
            if ($now > $expiresAt || $company['status'] === 'expired') {
                $isExpired = true;
            }
        }

        return [
            'exists' => true,
            'company_id' => (int)$company['id'],
            'company_name' => $company['nombre'],
            'is_demo' => $isDemo,
            'is_expired' => $isExpired,
            'status' => $company['status'],
            'trial_ends_at' => $company['trial_ends_at'],
            'days_remaining' => max(0, (int)($company['days_remaining'] ?? 0)),
            'hours_remaining' => max(0, (int)($company['hours_remaining'] ?? 0))
        ];
    }
}
