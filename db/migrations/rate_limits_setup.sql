-- ============================================================================
-- MIGRACIÓN: RATE LIMITING POR VENTANA (LOGIN, 2FA, RECUPERACIÓN)
-- ============================================================================
-- Antes el único control anti-abuso era usuarios.intentos_fallidos, que
-- bloquea la cuenta de forma PERMANENTE al llegar a 10 intentos y solo aplica
-- a contraseñas incorrectas de cuentas existentes. No existia ninguna ventana
-- temporal ni limite por IP, de modo que un atacante podia probar ilimitadas
-- contrasenas y codigos TOTP.
--
-- Esta tabla registra los intentos por accion e identificador (email o IP),
-- con una ventana deslizante y un bloqueo temporal. Se aplica al acceso a
-- cuentas existentes; el alta de empresa (api_register_demo.php) NO se limita.
--
-- La clave es un hash SHA-256 de accion+identificador: el email o la IP no se
-- guardan en claro, para que esta tabla no se convierta en un nuevo almacen de
-- datos personales ni de IPs.
-- ============================================================================

CREATE TABLE IF NOT EXISTS `rate_limits` (
    `id`                INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `clave`             CHAR(64)     NOT NULL,
    `accion`            VARCHAR(40)  NOT NULL,
    `intentos`          INT(10) UNSIGNED NOT NULL DEFAULT 0,
    `ventana_inicio`    DATETIME     NOT NULL,
    `bloqueado_hasta`   DATETIME     NULL DEFAULT NULL,
    `creado_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_clave` (`clave`),
    INDEX `idx_expira` (`bloqueado_hasta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;