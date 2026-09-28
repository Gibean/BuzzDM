<?php
/**
 * DeviceFingerprintService
 * 
 * Genera y valida la huella digital del dispositivo para evitar abusos
 * en el registro de cuentas de demostración gratuitas (15 días).
 */
class DeviceFingerprintService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Genera un hash SHA-256 combinando:
     * 1. Hash del cliente (FingerprintJS de frontend) si se proporciona
     * 2. IP pública del visitante (considerando proxies inversos de Hostinger/Cloudflare)
     * 3. User-Agent del navegador
     */
    public function generateFingerprint(?string $clientFingerprint = null): string {
        $ip = $this->getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown_agent';
        $acceptLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';

        $components = [
            'client_fp' => trim((string)$clientFingerprint),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'lang' => substr($acceptLanguage, 0, 10)
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Comprueba si el dispositivo o el correo de administración ya registraron una prueba
     */
    public function isDeviceOrEmailBlocked(string $fingerprint, string $email): bool {
        $stmt = $this->pdo->prepare("
            SELECT id, status, trial_ends_at 
            FROM empresa 
            WHERE (device_fingerprint = :fingerprint OR admin_email = :email)
              AND is_demo = 1
            LIMIT 1
        ");
        $stmt->execute([
            ':fingerprint' => $fingerprint,
            ':email' => strtolower(trim($email))
        ]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la IP real considerando proxies inversos
     */
    private function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_REAL_IP',        // Nginx proxy
            'HTTP_X_FORWARDED_FOR',  // Standard proxy
            'REMOTE_ADDR'            // Direct IP
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
