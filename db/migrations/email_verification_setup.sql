-- ============================================================================
-- MIGRACIÓN: VERIFICACIÓN DE CORREO POR CÓDIGO OTP (SAAS DEMO)
-- ============================================================================

-- 1. Ampliar ENUM de status en tabla empresa para incluir 'pending_verification'
ALTER TABLE `empresa` 
    MODIFY COLUMN `status` ENUM('active', 'expired', 'suspended', 'pending_verification') NOT NULL DEFAULT 'pending_verification';

-- 2. Crear tabla para almacenar códigos OTP de 6 dígitos
CREATE TABLE IF NOT EXISTS `email_verification_codes` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `empresa_id` INT(10) UNSIGNED NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `codigo` VARCHAR(6) NOT NULL,
    `expiracion` TIMESTAMP NOT NULL,
    `usado` TINYINT(1) NOT NULL DEFAULT 0,
    `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_code` (`email`, `codigo`, `usado`),
    INDEX `idx_expiracion` (`expiracion`),
    CONSTRAINT `fk_verif_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
