-- Configuraciones table for system settings
-- Run this SQL in phpMyAdmin or MySQL CLI

CREATE TABLE IF NOT EXISTS `configuraciones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `clave` VARCHAR(100) NOT NULL UNIQUE,
    `valor` TEXT DEFAULT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default backup configuration
INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`)
VALUES ('backup_fallback_enabled', '1', 'Habilitar sistema de backup automático fallback')
ON DUPLICATE KEY UPDATE `valor` = VALUES(`valor`);
