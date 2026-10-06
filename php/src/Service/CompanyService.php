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
     * Registra y aprovisiona una empresa nueva.
     *
     * Este método es el único punto donde nace una empresa, y distingue dos
     * tipos que hasta ahora no se distinguían:
     *
     *   - Demo (is_demo = 1): prueba gratuita de 15 días, con huella digital
     *     para impedir que un mismo navegador registre varias, y verificación
     *     de correo antes de activarse.
     *   - Empresa real (is_demo = 0): sin caducidad, sin huella digital y sin
     *     bloqueo por dispositivo.
     *
     * Antes esta clase fijaba is_demo = 1 en el INSERT, así que no existía
     * ninguna forma de crear una empresa que no fuera demo. Todo el resto del
     * sistema (cron de caducidad, DeviceFingerprintService, sesion.php y el
     * banner de top_navbar.php) ya discriminaba por is_demo, de modo que la
     * empresa real no necesita más cambios que esta entrada.
     *
     * @param array $data     company_name, admin_email, admin_password, admin_name,
     *                        y opcionalmente device_fingerprint, rif, email
     * @param array $opciones is_demo, trial_days, status, require_fingerprint,
     *                        require_email_verification
     */
    private function provisionCompany(array $data, array $opciones): array {
        $isDemo = (bool)($opciones['is_demo'] ?? false);
        $trialDays = array_key_exists('trial_days', $opciones) ? $opciones['trial_days'] : null;
        $requireFingerprint = (bool)($opciones['require_fingerprint'] ?? $isDemo);
        $requireEmailVerification = (bool)($opciones['require_email_verification'] ?? true);

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

        // La huella digital solo se calcula y se aplica a las demos: es el
        // mecanismo antia abusedel trial gratuito. Una empresa real no puede
        // quedar bloqueada por un navegador, así que se guarda a NULL.
        $fingerprint = null;
        if ($requireFingerprint) {
            $fingerprint = $this->fingerprintService->generateFingerprint($clientFp);
            if ($this->fingerprintService->isDeviceOrEmailBlocked($fingerprint, $adminEmail)) {
                throw new RuntimeException("Ya se ha registrado una prueba de demostración desde este dispositivo o correo electrónico.");
            }
        }

        $this->pdo->beginTransaction();
        try {
            // 1. Crear Empresa. Las demos nacen pendientes de verificación de
            //    correo; una empresa real nace activa y usable de inmediato.
            $expiresAt = $trialDays !== null
                ? (new DateTime())->modify('+' . (int)$trialDays . ' days')->format('Y-m-d H:i:s')
                : null;
            $initialStatus = $requireEmailVerification ? 'pending_verification' : ($opciones['status'] ?? 'active');

            $stmt = $this->pdo->prepare("
                INSERT INTO empresa (
                    nombre, tipo_rif, rif, email, admin_email, device_fingerprint,
                    trial_ends_at, is_demo, status, creado_at
                ) VALUES (
                    :nombre, 'J', :rif, :email, :admin_email, :fingerprint,
                    :expires_at, :is_demo, :status, NOW()
                )
            ");
            // El RIF solo se genera si no viene dado: una empresa real debe
            // declarar el suyo, y un RIF inventado en una factura es un problema
            // fiscal, no un detalle de maquetación.
            $companyRif = trim((string)($data['rif'] ?? ''));
            if ($companyRif === '') {
                $companyRif = 'J-' . str_pad((string)mt_rand(10000000, 99999999), 9, '0', STR_PAD_LEFT);
            }
            $stmt->execute([
                ':nombre' => $companyName,
                ':rif' => $companyRif,
                ':email' => $data['email'] ?? $adminEmail,
                ':admin_email' => $adminEmail,
                ':fingerprint' => $fingerprint,
                ':expires_at' => $expiresAt,
                ':is_demo' => $isDemo ? 1 : 0,
                ':status' => $initialStatus,
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

// 4. Inyectar categorías base del tenant
            $baseCategories = ['General', 'Papelería y Oficina', 'Tecnología'];
            $stmtCat = $this->pdo->prepare("
                INSERT INTO categorias (empresa_id, nombre, descripcion) 
                VALUES (:empresa_id, :nombre, :desc)
            ");
            foreach ($baseCategories as $cat) {
                $stmtCat->execute([
                    ':empresa_id' => $companyId,
                    ':nombre' => $cat,
                    ':desc' => "Categoría inicial $cat"
                ]);
            }

            // 5. Inyectar ajustes y catálogos base del tenant
            //
            //    Sin esto la empresa nueva nace con los desplegables vacíos y con
            //    los ajustes en NULL: el nombre de la tienda saldría en blanco
            //    en la factura y la moneda sería indeterminada. Los valores son
            //    preferencias de negocio neutras, no datos de ninguna otra
            //    empresa: no hay nada que copiar de ningún tenant.
            $defaultSettings = [
                'store_name' => [$companyName, 'Nombre mostrado en factura y sitio'],
                'currency' => ['VES', 'Moneda del sistema'],
                'tax_percent' => ['16.00', 'Porcentaje de IVA aplicado a ventas'],
                'backup_fallback_enabled' => ['0', 'Habilitar sistema de backup automático fallback'],
            ];
            $stmtCfg = $this->pdo->prepare("
                INSERT INTO configuraciones (empresa_id, clave, valor, descripcion, fecha_actualizacion)
                VALUES (:empresa_id, :clave, :valor, :desc, NOW())
            ");
            foreach ($defaultSettings as $clave => [$valor, $descripcion]) {
                $stmtCfg->execute([
                    ':empresa_id' => $companyId,
                    ':clave' => $clave,
                    ':valor' => $valor,
                    ':desc' => $descripcion,
                ]);
            }

            // Colores y materiales: catálogos por empresa (migración 003), así
            // que toda empresa nueva necesita su propio juego y no puede leer
            // el de ninguna otra.
            $baseColores = [
                ['Negro', '#000000'], ['Blanco', '#FFFFFF'], ['Azul', '#1E5AA8'],
                ['Rojo', '#C62828'], ['Verde', '#2E7D32'], ['Gris', '#757575'],
            ];
            $stmtColor = $this->pdo->prepare("
                INSERT INTO colores (empresa_id, nombre, codigo_hex) VALUES (:empresa_id, :nombre, :hex)
            ");
            foreach ($baseColores as [$nombre, $hex]) {
                $stmtColor->execute([':empresa_id' => $companyId, ':nombre' => $nombre, ':hex' => $hex]);
            }

            $baseMateriales = ['Algodón', 'Poliéster', 'Denim', 'Lana', 'Sintético', 'Lino'];
            $stmtMat = $this->pdo->prepare("
                INSERT INTO materiales (empresa_id, nombre) VALUES (:empresa_id, :nombre)
            ");
            foreach ($baseMateriales as $nombre) {
                $stmtMat->execute([':empresa_id' => $companyId, ':nombre' => $nombre]);
            }

            // 6. Crear directorio de uploads aislado para la empresa
            $uploadDir = __DIR__ . "/../../../assets/img/uploads/companies/{$companyId}/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0750, true);
            }

            // 7. Verificación de correo, solo si el tipo de empresa la exige.
            //    Una empresa real puede querer activarse sin depender del
            //    correo, así que el código se genera únicamente cuando aplica.
            $otpCode = null;
            if ($requireEmailVerification) {
                $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
                $otpExpires = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');

                $stmtOtp = $this->pdo->prepare("
                    INSERT INTO email_verification_codes (empresa_id, email, codigo, expiracion, creado_at)
                    VALUES (:empresa_id, :email, :codigo, :expiracion, NOW())
                ");
                $stmtOtp->execute([
                    ':empresa_id' => $companyId,
                    ':email'      => $adminEmail,
                    ':codigo'     => $otpCode,
                    ':expiracion' => $otpExpires
                ]);

                // Enviar email con PHPMailer
                $this->sendVerificationEmail($adminEmail, $adminName, $companyName, $otpCode);
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'requires_verification' => $requireEmailVerification,
                'company_id' => $companyId,
                'user_id' => $userId,
                'company_name' => $companyName,
                'admin_email' => $adminEmail,
                'is_demo' => $isDemo,
                'expires_at' => $expiresAt,
                'days_remaining' => $trialDays !== null ? (int)$trialDays : null,
                'message' => $requireEmailVerification
                    ? 'Código de verificación enviado a tu correo electrónico.'
                    : 'Empresa creada correctamente.'
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra una prueba de demostración gratuita de 15 días.
     *
     * Es el flujo que usa registro.php: la empresa nace con is_demo = 1, con
     * huella digital (para que un mismo navegador no cree varias) y pendiente
     * de verificación de correo.
     */
    public function createTrialEnvironment(array $data): array {
        return $this->provisionCompany($data, [
            'is_demo' => true,
            'trial_days' => 15,
            'require_fingerprint' => true,
            'require_email_verification' => true,
        ]);
    }

    /**
     * Registra una empresa real: sin caducidad, sin huella digital y sin
     * bloqueo por dispositivo.
     *
     * Es el equivalente del alta tradicional. El resto del sistema ya la
     * distingue de una demo sin tocar nada más: el cron de caducidad, la
     * DeviceFingerprintService y la comprobación de trial de sesion.php solo
     * actúan sobre is_demo = 1.
     *
     * Por defecto exige verificar el correo antes de dejar usarla. Con
     * $skipEmailVerification = true nace activa, para altas made por un
     * administrador del servidor que ya conoce al cliente.
     */
    public function createCompany(array $data, bool $skipEmailVerification = false): array {
        return $this->provisionCompany($data, [
            'is_demo' => false,
            'trial_days' => null,
            'require_fingerprint' => false,
            'require_email_verification' => !$skipEmailVerification,
        ]);
    }

    /**
     * Valida el código OTP de 6 dígitos y activa la empresa demo
     */
    public function verifyEmailCode(string $email, string $code): array {
        $email = strtolower(trim($email));
        $code = trim($code);

        $stmt = $this->pdo->prepare("
            SELECT id, empresa_id, expiracion, usado
            FROM email_verification_codes
            WHERE email = :email AND codigo = :codigo AND usado = 0
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':email' => $email, ':codigo' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new InvalidArgumentException('El código de verificación es incorrecto o ya fue utilizado.');
        }

        if (new DateTime() > new DateTime($row['expiracion'])) {
            throw new RuntimeException('El código de verificación ha expirado. Por favor solicita uno nuevo.');
        }

        // Marcar código como usado
        $this->pdo->prepare("UPDATE email_verification_codes SET usado = 1 WHERE id = ?")->execute([$row['id']]);

        // Activar la empresa
        $empresaId = (int)$row['empresa_id'];
        $this->pdo->prepare("UPDATE empresa SET status = 'active' WHERE id = ?")->execute([$empresaId]);

        // El tipo de empresa se lee de la base, no se supone. Antes se fijaba
        // is_demo = true aquí, lo que convertía en demo cualquier empresa que
        // pasara por la verificación de correo.
        $stmtEmpresa = $this->pdo->prepare("SELECT is_demo, trial_ends_at FROM empresa WHERE id = ?");
        $stmtEmpresa->execute([$empresaId]);
        $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC) ?: ['is_demo' => 0, 'trial_ends_at' => null];
        $isDemo = (bool)$empresa['is_demo'];

        // Obtener usuario administrador de la empresa para sesión
        $stmtUser = $this->pdo->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE empresa_id = ? AND rol = 'admin' LIMIT 1");
        $stmtUser->execute([$empresaId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol'];
            $_SESSION['empresa_id'] = $empresaId;
            $_SESSION['is_demo'] = $isDemo;
            if ($isDemo) {
                $_SESSION['demo_dias_restantes'] = 15;
            }
        }

        return [
            'success' => true,
            'message' => $isDemo
                ? '¡Correo verificado con éxito! Tu prueba demo de 15 días ha sido activada.'
                : '¡Correo verificado con éxito! Tu empresa ha sido activada.',
            'company_id' => $empresaId,
            'is_demo' => $isDemo,
            'redirect_url' => 'dashboard'
        ];
    }

    /**
     * Reenvía un nuevo código de verificación
     */
    public function resendVerificationCode(string $email): array {
        $email = strtolower(trim($email));

        $stmt = $this->pdo->prepare("SELECT id, nombre FROM empresa WHERE admin_email = ? AND status = 'pending_verification' LIMIT 1");
        $stmt->execute([$email]);
        $empresa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$empresa) {
            throw new InvalidArgumentException('No existe ninguna cuenta pendiente de verificación con ese correo.');
        }

        // Invalidar códigos anteriores
        $this->pdo->prepare("UPDATE email_verification_codes SET usado = 1 WHERE email = ?")->execute([$email]);

        // Generar nuevo código
        $newCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $newExpires = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');

        $this->pdo->prepare("
            INSERT INTO email_verification_codes (empresa_id, email, codigo, expiracion, creado_at)
            VALUES (?, ?, ?, ?, NOW())
        ")->execute([(int)$empresa['id'], $email, $newCode, $newExpires]);

        $this->sendVerificationEmail($email, 'Administrador', $empresa['nombre'], $newCode);

        return [
            'success' => true,
            'message' => 'Hemos enviado un nuevo código de verificación a tu correo.'
        ];
    }

    /**
     * Envía el correo con el código OTP usando PHPMailer
     */
    private function sendVerificationEmail(string $email, string $adminName, string $companyName, string $code): void {
        require_once __DIR__ . '/../../../vendor/autoload.php';
        require_once __DIR__ . '/../../email_config.php';

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $config = getMailerConfig();

            // Si no hay configuración SMTP en entorno local, registrar en log y continuar sin error fatal
            if (empty($config['host']) || empty($config['username'])) {
                error_log("Aviso: Configuración SMTP no establecida. Código OTP generado para $email: $code");
                return;
            }

            $mail->isSMTP();
            $mail->Host       = $config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['username'];
            $mail->Password   = $config['password'];
            $mail->SMTPSecure = $config['secure'];
            $mail->Port       = $config['port'];
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($email, $adminName);

            $mail->isHTML(true);
            $mail->Subject = "Código de Verificación: $code - BUZZ Data Manager";
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; background: #10141a; color: #dfe5eb; padding: 30px;'>
                    <div style='max-width: 520px; margin: 0 auto; background: #1a1f28; border: 1px solid #343a40; border-radius: 8px; padding: 25px;'>
                        <h2 style='color: #ffa000; margin-top: 0;'>BUZZ Data Manager</h2>
                        <p>Hola <strong>" . htmlspecialchars($adminName) . "</strong>,</p>
                        <p>Gracias por registrar tu empresa <strong>" . htmlspecialchars($companyName) . "</strong>. Para activar tus 15 días de prueba gratuita, ingresa el siguiente código:</p>
                        <div style='text-align: center; margin: 25px 0;'>
                            <span style='font-family: monospace; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #ffa000; background: #10141a; padding: 10px 25px; border-radius: 6px; border: 1px dashed #ffa000; display: inline-block;'>$code</span>
                        </div>
                        <p style='font-size: 13px; color: #c5ccd5;'>Este código expira en 15 minutos.</p>
                        <hr style='border: 0; border-top: 1px solid #343a40; margin: 20px 0;'>
                        <p style='font-size: 11px; color: #888; margin-bottom: 0;'>Si tú no solicitaste este registro, puedes ignorar este mensaje.</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Tu código de verificación para BUZZ Data Manager es: $code (Válido por 15 minutos).";

            $mail->send();
        } catch (Exception $e) {
            error_log("Error al enviar email de verificación OTP ($code) a $email: " . $e->getMessage());
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
