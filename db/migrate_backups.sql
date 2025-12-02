-- Script de migración para actualizar la tabla backups
-- Ejecutar este script en tu base de datos dbuzz existente

USE dbuzz;

-- Agregar nuevas columnas a la tabla backups
ALTER TABLE `backups`
ADD COLUMN `ruta_archivo` varchar(500) DEFAULT NULL COMMENT 'Ruta completa del archivo' AFTER `archivo`,
ADD COLUMN `resultado` varchar(500) DEFAULT NULL COMMENT 'Mensaje de resultado' AFTER `estado`,
ADD COLUMN `detalles` text DEFAULT NULL COMMENT 'Detalles en formato JSON: tablas, duración, etc.' AFTER `resultado`,
ADD COLUMN `usuario_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que creó el backup manual' AFTER `detalles`;

-- Modificar columnas existentes para usar ENUM
ALTER TABLE `backups`
MODIFY COLUMN `tipo` enum('manual','automatico') DEFAULT 'manual' COMMENT 'Tipo de backup: manual o automático',
MODIFY COLUMN `estado` enum('completado','error','en_proceso') DEFAULT 'completado' COMMENT 'Estado del backup';

-- Agregar foreign key para usuario_id
ALTER TABLE `backups`
ADD KEY `FK_backups_usuarios` (`usuario_id`),
ADD CONSTRAINT `FK_backups_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Agregar índices para mejorar rendimiento
ALTER TABLE `backups`
ADD KEY `fecha` (`fecha`),
ADD KEY `tipo` (`tipo`);

-- Mensaje de confirmación
SELECT 'Tabla backups actualizada correctamente!' AS mensaje;
