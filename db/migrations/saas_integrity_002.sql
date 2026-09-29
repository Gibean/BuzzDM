-- ============================================================================
-- MIGRACIÓN 002: Integridad referencial y cobertura de tenancy
-- BuzzDM - Prepara el esquema para el aislamiento multi-tenant
--
-- OBJETIVO 1: eliminar los 8 bloqueos RESTRICT/NO ACTION que impiden que el
--   borrado en cascada de una empresa se complete. Con ellos activos, el
--   cronjob demo_cleanup.php lanzaba error 1451 y abortaba el lote entero,
--   dejando datos huérfanos (justo lo contrario de lo que se докуmentaba).
--
-- OBJETIVO 2: añadir empresa_id a las tablas que faltaban, para que ninguna
--   fila quede fuera del perímetro de un tenant.
--
-- NOTA: MySQL no soporta transacciones DDL. Ejecutar sobre un respaldo.
-- ============================================================================

-- ---------------------------------------------------------------------------
-- PARTE 1: FKs que bloqueaban la cascada  →  ON DELETE CASCADE / SET NULL
--
-- NOTA SOBRE EL ERROR 121:
--   MariaDB NO admite soltar y recrear una constraint con el MISMO nombre
--   dentro de un mismo ALTER. Valida los nombres antes de aplicar el cambio y
--   aborta con:
--       errno 121  duplicate name, CONSTRAINT `...`
--       errno 121  Can't create table ... (errno: 121 "Duplicate key on write")
--
--   Además, DROP FOREIGN KEY deja huérfano el índice que InnoDB creó para esa
--   FK. La solución adoptada es renombrar cada constraint reconstruida, lo que
--   además deja el esquema más legible: el sufijo declara la nueva regla.
--
--   Los índices huérfanos (fk_cargo_registrado, fk_req_usuario, pedidos_ibfk_1)
--   se conservan y son reutilizados por las nuevas constraints, porque InnoDB
--   reutiliza cualquier índice que cubra la columna de la FK.
-- ---------------------------------------------------------------------------

-- 1.1 pedidos.cliente_id era NO ACTION: impediría borrar un cliente con pedidos.
ALTER TABLE `pedidos`
    DROP FOREIGN KEY `fk_pedido_cliente`,
    ADD CONSTRAINT `fk_pedido_cliente_casc` FOREIGN KEY (`cliente_id`)
        REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.2 El usuario que registró un cargo no puede impedir la purga del tenant.
--     registrado_por es NOT NULL, así que ON DELETE SET NULL sería
--     "incorrectly formed" (errno 150). Se usa CASCADE: un cargo sin su usuario
--     registrador no conserva valor probatorio dentro del mismo tenant.
ALTER TABLE `cargos_existencias`
    DROP FOREIGN KEY `fk_cargo_registrado`,
    ADD CONSTRAINT `fk_cargo_registrado_casc` FOREIGN KEY (`registrado_por`)
        REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.3 El solicitante de una requisición no puede impedir la purga del tenant.
ALTER TABLE `requisiciones`
    DROP FOREIGN KEY `fk_req_usuario`,
    ADD CONSTRAINT `fk_req_usuario_casc` FOREIGN KEY (`usuario_id`)
        REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.4 Detalles de cargo: el producto desaparece con su tenant.
ALTER TABLE `detalle_cargo_existencias`
    DROP FOREIGN KEY `fk_detalle_producto`,
    ADD CONSTRAINT `fk_detalle_producto_casc` FOREIGN KEY (`producto_id`)
        REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.5 Detalles de requisición.
ALTER TABLE `detalle_requisicion`
    DROP FOREIGN KEY `fk_detreq_producto`,
    ADD CONSTRAINT `fk_detreq_producto_casc` FOREIGN KEY (`producto_id`)
        REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.6 Detalles de transferencia.
ALTER TABLE `detalle_transferencia`
    DROP FOREIGN KEY `fk_dt_producto`,
    ADD CONSTRAINT `fk_dt_producto_casc` FOREIGN KEY (`producto_id`)
        REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.7 Componentes de un kit: el componente se borra con su tenant.
ALTER TABLE `producto_componentes`
    DROP FOREIGN KEY `fk_pc_componente`,
    ADD CONSTRAINT `fk_pc_componente_casc` FOREIGN KEY (`componente_id`)
        REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 1.8 Almacenes origen/destino de una transferencia.
ALTER TABLE `transferencias_almacen`
    DROP FOREIGN KEY `fk_trf_almacen_origen`,
    DROP FOREIGN KEY `fk_trf_almacen_destino`,
    ADD CONSTRAINT `fk_trf_almacen_origen_casc` FOREIGN KEY (`almacen_origen_id`)
        REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    ADD CONSTRAINT `fk_trf_almacen_destino_casc` FOREIGN KEY (`almacen_destino_id`)
        REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------
-- PARTE 2: empresa_id en las tablas que se consultan directamente
--
-- En tablas hijas de un padre ya cubierto (detalle_pedido, notas_adjuntos,
-- stock_ubicacion, etc.) el CASCADE del padre basta para la purga y no hace
-- falta columna redundante: filtrar por empresa_id ahí exigiría mantenerla
-- sincronizada sin beneficio.
-- ---------------------------------------------------------------------------

-- 2.1 notas_kanban. La migración original apuntaba a una tabla `notas` que
--     nunca existió en este esquema, por lo que esta tabla quedó fuera del
--     perímetro de tenant.
ALTER TABLE `notas_kanban`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_notas_kanban_empresa` (`empresa_id`);

-- 2.2 Catálogos auxiliares que se ofrecen en los formularios.
ALTER TABLE `colores`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_colores_empresa` (`empresa_id`);

ALTER TABLE `materiales`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_materiales_empresa` (`empresa_id`);

ALTER TABLE `tallas_franela`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_tallas_empresa` (`empresa_id`);

-- 2.3 Ubicaciones: cuelgan de un almacén, pero se listan y exportan por separado.
ALTER TABLE `ubicaciones`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_ubicaciones_empresa` (`empresa_id`);

-- 2.4 Inventario y precios.
ALTER TABLE `movimientos_inventario`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_mov_inv_empresa` (`empresa_id`);

ALTER TABLE `stock_ubicacion`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_stock_ub_empresa` (`empresa_id`);

ALTER TABLE `precios_proveedor`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_precios_prov_empresa` (`empresa_id`);

-- 2.5 Configuración y respaldos: un demo no debe leer ni generar el backup
--     de otra empresa.
ALTER TABLE `configuraciones`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_configuraciones_empresa` (`empresa_id`);

ALTER TABLE `backups`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_backups_empresa` (`empresa_id`);

-- 2.6 Trazabilidad: logs, notificaciones y sesiones.
ALTER TABLE `logs`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_logs_empresa` (`empresa_id`);

ALTER TABLE `logs_login`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_logs_login_empresa` (`empresa_id`);

ALTER TABLE `notificaciones`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_notificaciones_empresa` (`empresa_id`);

ALTER TABLE `sesiones_api`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_sesiones_empresa` (`empresa_id`);

ALTER TABLE `password_reset_tokens`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NULL AFTER `id`,
    ADD KEY `idx_pwd_reset_empresa` (`empresa_id`);

-- ---------------------------------------------------------------------------
-- PARTE 3: Backfill de empresa_id
--
-- Deriva el tenant de la fila a partir de su padre ya seleccionado, de modo que
-- los datos preexistentes queden atribuidos a la empresa principal (id = 1) y
-- no se inventen pertenencias.
-- ---------------------------------------------------------------------------

UPDATE `notas_kanban` n
    JOIN `usuarios` u ON u.id = n.usuario_id
    SET n.empresa_id = u.empresa_id WHERE n.empresa_id IS NULL;

UPDATE `ubicaciones` ub
    JOIN `almacenes` a ON a.id = ub.almacen_id
    SET ub.empresa_id = a.empresa_id WHERE ub.empresa_id IS NULL;

UPDATE `stock_ubicacion` su
    JOIN `almacenes` a ON a.id = su.almacen_id
    SET su.empresa_id = a.empresa_id WHERE su.empresa_id IS NULL;

UPDATE `movimientos_inventario` mi
    JOIN `productos` p ON p.id = mi.producto_id
    SET mi.empresa_id = p.empresa_id WHERE mi.empresa_id IS NULL;

UPDATE `precios_proveedor` pp
    JOIN `productos` p ON p.id = pp.producto_id
    SET pp.empresa_id = p.empresa_id WHERE pp.empresa_id IS NULL;

UPDATE `sesiones_api` s
    JOIN `usuarios` u ON u.id = s.usuario_id
    SET s.empresa_id = u.empresa_id WHERE s.empresa_id IS NULL;

UPDATE `logs` l
    JOIN `usuarios` u ON u.id = l.usuario_id
    SET l.empresa_id = u.empresa_id WHERE l.empresa_id IS NULL;

UPDATE `logs_login` ll
    JOIN `usuarios` u ON u.id = ll.usuario_id
    SET ll.empresa_id = u.empresa_id WHERE ll.usuario_id IS NOT NULL;

-- ---------------------------------------------------------------------------
-- PARTE 4: Backfill tenant-aware de stock por almacén
--
-- Sustituye al script legacy db/migrations/asignar_stock_almacen.sql, que
-- operaba sobre todos los productos del sistema sin filtrar por empresa_id.
-- Se hace por almacén principal de cada empresa, nunca globalmente.
-- ---------------------------------------------------------------------------

INSERT INTO `stock_ubicacion` (empresa_id, producto_id, almacen_id, ubicacion_id, cantidad)
SELECT p.empresa_id, p.id, a.id, NULL, p.stock
FROM `productos` p
JOIN `almacenes` a ON a.empresa_id = p.empresa_id AND a.es_principal = 1
WHERE p.stock > 0
  AND p.deleted_at IS NULL
  AND NOT EXISTS (
      SELECT 1 FROM `stock_ubicacion` su
      WHERE su.producto_id = p.id AND su.almacen_id = a.id
  );

UPDATE `stock_ubicacion` su
JOIN `almacenes` a ON a.id = su.almacen_id
JOIN `productos` p ON p.id = su.producto_id
SET su.empresa_id = a.empresa_id, su.cantidad = p.stock
WHERE su.empresa_id IS NULL;

-- ---------------------------------------------------------------------------
-- PARTE 5: Convertir las columnas en NOT NULL con DEFAULT 1
--
-- Se hace al final y sólo para las tablas ya pobladas, de modo que ningún
-- INSERT futuro pueda omitir el tenant. Las tablas sin filas (catálogos
-- auxiliares) se dejan NULL para no bloquear la creación de catálogos
-- compartidos; el repositorio las rellena siempre.
-- ---------------------------------------------------------------------------

ALTER TABLE `notas_kanban`
    MODIFY COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE `ubicaciones`
    MODIFY COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE `movimientos_inventario`
    MODIFY COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE `stock_ubicacion`
    MODIFY COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE `precios_proveedor`
    MODIFY COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1;

-- ---------------------------------------------------------------------------
-- PARTE 6: Claves foráneas a empresa para las tablas con datos
-- ---------------------------------------------------------------------------

ALTER TABLE `notas_kanban`
    ADD CONSTRAINT `fk_notas_kanban_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `ubicaciones`
    ADD CONSTRAINT `fk_ubicaciones_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `movimientos_inventario`
    ADD CONSTRAINT `fk_mov_inv_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `stock_ubicacion`
    ADD CONSTRAINT `fk_stock_ub_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `precios_proveedor`
    ADD CONSTRAINT `fk_precios_prov_empresa` FOREIGN KEY (`empresa_id`)
        REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ---------------------------------------------------------------------------
-- PARTE 7: Índices compuestos para los filtros de tenant más frecuentes
-- ---------------------------------------------------------------------------

ALTER TABLE `productos`
    ADD KEY `idx_productos_tenant_estado` (`empresa_id`, `estado`);

ALTER TABLE `pedidos`
    ADD KEY `idx_pedidos_tenant_fecha` (`empresa_id`, `fecha_creacion`);

ALTER TABLE `clientes`
    ADD KEY `idx_clientes_tenant_nombre` (`empresa_id`, `nombre`);

ALTER TABLE `proveedores`
    ADD KEY `idx_proveedores_tenant_nombre` (`empresa_id`, `nombre`);

ALTER TABLE `cargos_existencias`
    ADD KEY `idx_cargos_tenant_fecha` (`empresa_id`, `fecha`);

ALTER TABLE `notas_kanban`
    ADD KEY `idx_notas_tenant_estado` (`empresa_id`, `estado`);
