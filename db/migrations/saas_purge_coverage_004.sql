-- ============================================================================
-- MIGRACIÓN 004: Backfill de empresa_id y FK de purga en tablas sin cobertura
-- BuzzDM
--
-- HALLAZGO
--   La migración 002 añadió la columna empresa_id a 28 tablas, pero sólo 18
--   quedaron con clave foránea hacia empresa.id. Las 10 restantes permiten que
--   el borrado de una empresa deje filas huérfanas, y eso rompe el ciclo de vida
--   de la demo de dos maneras:
--
--     1. FUGA: al ejecutar demo_cleanup.php (DELETE FROM empresa WHERE id = ?),
--        las filas de esas tablas sobreviven. Una demo purgada deja datos
--        visibles si luego se registra un empresa que reutilice el id.
--
--     2. CORRUPCIÓN DE LOGS: logs.usuario_id y backups.usuario_id están en
--        ON DELETE SET NULL. Al borrarse la empresa, el usuario se va y el
--        registro queda con usuario_id NULL y empresa_id NULL: se pierde la
--        trazabilidad justo en el evento que más la necesita.
--
--   Además, configuraciones tenía las 4 filas con empresa_id NULL. Con el
--   índice único (empresa_id, clave) de la migración 003, MySQL considera
--   siempre distintos los NULL, así que la unicidad no aplicaba y, sobre todo,
--   esas filas eran invisibles a cualquier consulta filtrada por empresa: los
--   ajustes de la empresa principal (store_name, currency, tax_percent)
--   habían dejado de ser legibles.
--
-- NOTA SOBRE LAS TABLAS "HUÉRFANAS" QUE NO SE TOCAN
--   detalle_cargo_existencias, detalle_pedido, detalle_requisicion,
--   detalle_transferencia, producto_componentes, notas_adjuntos,
--   notas_checklists y notas_items_inventario NO tienen empresa_id, y es
--   intencionado: son tablas hijas cuyo padre (pedido, cargo, nota, producto)
--   ya está acotado por empresa y su id es único globalmente. Se alcanza el
--   borrado en cascada desde el padre, así que no necesitan columna propia.
--   Duplicar empresa_id en ellas sería desnormalización sin beneficio.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- 1. Backfill. Todas las filas existentes pertenecen a la empresa 1: es la
--    única empresa con datos catalogs, ajustes o historial. La empresa 14 se
--    creó como demo vacía.
-- ---------------------------------------------------------------------------

-- Ajustes de la empresa principal (store_name, currency, tax_percent,
-- backup_fallback_enabled).
UPDATE `configuraciones` SET `empresa_id` = 1 WHERE `empresa_id` IS NULL;

-- Tabla de tallas. Catálogo global sin uso en el código actual, pero se deja
-- coherente con la decisión de tratar los catálogos como datos por empresa.
UPDATE `tallas_franela` SET `empresa_id` = 1 WHERE `empresa_id` IS NULL;

-- Autenticación. Las entradas con usuario_id NULL son intentos fallidos con
-- un email inexistente: no se puede deducir la empresa y es correcto que sigan
-- sin empresa (registrar "empresa desconocida" sería inventar el dato).
UPDATE `logs_login` l
    JOIN `usuarios` u ON u.id = l.usuario_id
    SET l.empresa_id = u.empresa_id
    WHERE l.empresa_id IS NULL AND l.usuario_id IS NOT NULL;

-- Los accesos exitosos con usuario_id NULL son un fallo del logger de login
-- (no guarda el usuario), no un dato ausente: se sabe que son de la empresa 1.
UPDATE `logs_login`
    SET `empresa_id` = 1
    WHERE `empresa_id` IS NULL
      AND `usuario_id` IS NULL
      AND `accion` = 'Exitoso';

-- Bitácora y respaldos (SET NULL en usuario_id los dejaría huérfanos).
UPDATE `logs` l
    JOIN `usuarios` u ON u.id = l.usuario_id
    SET l.empresa_id = u.empresa_id
    WHERE l.empresa_id IS NULL AND l.usuario_id IS NOT NULL;

UPDATE `logs`
    SET `empresa_id` = 1
    WHERE `empresa_id` IS NULL;

UPDATE `backups` b
    JOIN `usuarios` u ON u.id = b.usuario_id
    SET b.empresa_id = u.empresa_id
    WHERE b.empresa_id IS NULL AND b.usuario_id IS NOT NULL;

-- ---------------------------------------------------------------------------
-- 2. Claves foráneas hacia empresa.id
--
--   ON DELETE CASCADE porque el borrado de la empresa DEBE arrastrar sus datos:
--   es lo que hace que demo_cleanup.php sea una única sentencia y no una
--   lista de 30 DELETE que se olvidarían de alguna.
--
--   NO se usa RESTRICT a propósito: con borrado en cascada multinivel,
--   MySQL evalúa RESTRICT durante la misma sentencia y el borrado de una
--   empresa fallaría al intentar quitar un usuario que aún está referenciado
--   por un documento que la propia cascada está borrando. SET NULL dejaría
--   documentos huérfanos. Para un dato de tenant, cascada es lo correcto.
-- ---------------------------------------------------------------------------
ALTER TABLE `configuraciones`
    ADD CONSTRAINT `fk_configuraciones_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `logs`
    ADD CONSTRAINT `fk_logs_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `logs_login`
    ADD CONSTRAINT `fk_logs_login_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `backups`
    ADD CONSTRAINT `fk_backups_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notificaciones`
    ADD CONSTRAINT `fk_notificaciones_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `colores`
    ADD CONSTRAINT `fk_colores_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `materiales`
    ADD CONSTRAINT `fk_materiales_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `tallas_franela`
    ADD CONSTRAINT `fk_tallas_franela_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------
-- 3. NOT NULL
--
--   Se cierra la columna donde toda fila puede y debe tener empresa. Quedan
--   fuera las dos tablas de autenticación, que son globales por naturaleza:
--   password_reset_tokens y sesiones_api se resuelven por token, no por
--   empresa, y forzarles un tenant inventaría una relación que no existe.
--   logs_login también queda nullable a propósito: un intento de login con un
--   email que no existe no pertenece a ninguna empresa.
-- ---------------------------------------------------------------------------
ALTER TABLE `configuraciones` MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `logs`            MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `backups`         MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `notificaciones`  MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `colores`         MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `materiales`      MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `tallas_franela`  MODIFY `empresa_id` INT(10) UNSIGNED NOT NULL;
