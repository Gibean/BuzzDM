-- ============================================================
-- MIGRACIÓN: Añadir columnas faltantes a cargos_existencias
-- Fecha: 2026-04-10
-- Descripción: Agrega las columnas concepto_otro, notas,
--              anulado_por, fecha_anulacion y motivo_anulacion
--              si no existen. Es seguro ejecutar más de una vez.
-- ============================================================

-- Añade 'concepto_otro' después de 'concepto'
ALTER TABLE `cargos_existencias`
    ADD COLUMN IF NOT EXISTS `concepto_otro` VARCHAR(100) DEFAULT NULL
        COMMENT 'Descripción si concepto=otro'
        AFTER `concepto`;

-- Añade 'notas' después de 'referencia_externa'
ALTER TABLE `cargos_existencias`
    ADD COLUMN IF NOT EXISTS `notas` TEXT DEFAULT NULL
        AFTER `referencia_externa`;

-- Añade 'anulado_por' después de 'fecha_confirmacion'
ALTER TABLE `cargos_existencias`
    ADD COLUMN IF NOT EXISTS `anulado_por` INT(10) UNSIGNED DEFAULT NULL
        AFTER `fecha_confirmacion`;

-- Añade 'fecha_anulacion' después de 'anulado_por'
ALTER TABLE `cargos_existencias`
    ADD COLUMN IF NOT EXISTS `fecha_anulacion` TIMESTAMP NULL DEFAULT NULL
        AFTER `anulado_por`;

-- Añade 'motivo_anulacion' después de 'fecha_anulacion'
ALTER TABLE `cargos_existencias`
    ADD COLUMN IF NOT EXISTS `motivo_anulacion` VARCHAR(255) DEFAULT NULL
        AFTER `fecha_anulacion`;

-- Añade las foreign keys solo si no existen ya
-- (usamos un bloque procedural para evitar errores en duplicados)
DROP PROCEDURE IF EXISTS _add_fk_anulado;
DELIMITER //
CREATE PROCEDURE _add_fk_anulado()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE CONSTRAINT_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cargos_existencias'
          AND CONSTRAINT_NAME = 'fk_cargo_anulado'
    ) THEN
        ALTER TABLE `cargos_existencias`
            ADD CONSTRAINT `fk_cargo_anulado`
            FOREIGN KEY (`anulado_por`)
            REFERENCES `usuarios` (`id`)
            ON DELETE SET NULL ON UPDATE CASCADE;
    END IF;
END //
DELIMITER ;
CALL _add_fk_anulado();
DROP PROCEDURE IF EXISTS _add_fk_anulado;

-- Verificación final: muestra la estructura actualizada
SELECT
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'cargos_existencias'
ORDER BY ORDINAL_POSITION;
