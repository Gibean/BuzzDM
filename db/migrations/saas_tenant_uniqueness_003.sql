-- ============================================================================
-- MIGRACIÓN 003: Unicidad por tenant en lugar de global
-- BuzzDM - Cada empresa debe poder usar los mismos códigos y nombres
--
-- PROBLEMA:
--   Antes de esta migración había 11 índices UNIQUE globales sobre columnas de
--   negocio (codigo, sku, nombre, clave). En un modelo multi-tenant eso es
--   inviable: dos empresas distintas no pueden tener un producto con SKU
--   "ABC-001" ni un almacén "ALM-001".
--
--   El caso más grave es CompanyService::createTrialEnvironment, que crea
--   el almacén principal con el código literal 'ALM-001'. Con el índice global,
--   el SEGUNDO usuario que se registraba en la demo fallaba con:
--       Integrity constraint violation: 1062 Duplicate entry 'ALM-001'
--
--   Lo que NO se toca (y es correcto que sigan siendo globales):
--     - empresa.uk_empresa_admin_email, empresa.uk_empresa_fingerprint
--       (identidad de la propia empresa)
--     - password_reset_tokens.token, sesiones_api.token (tokens)
--     - permisos_rol.uk_rol_pagina (configuración de roles del sistema)
--     - tasas_cambio.uk_fecha_fuente (cotizaciones de mercado)
--     - schema_migrations, _syncdero_meta (infraestructura)
--   Los índices que ya dependían de un padre globalmente único
--   (detalle_*.uk_*, producto_componentes, stock_ubicacion, ubicaciones)
--   tampoco se tocan: su alcance ya queda determinado por el id del padre.
-- ============================================================================

-- 1. productos: el SKU sólo debe ser único dentro de cada empresa.
ALTER TABLE `productos` DROP INDEX `sku`;
ALTER TABLE `productos` ADD UNIQUE KEY `uk_productos_empresa_sku` (`empresa_id`, `sku`);

-- 2. almacenes: el código 'ALM-001' debe poder repetirse en cada tenant.
ALTER TABLE `almacenes` DROP INDEX `uk_codigo`;
ALTER TABLE `almacenes` ADD UNIQUE KEY `uk_almacenes_empresa_codigo` (`empresa_id`, `codigo`);

-- 3. clientes/proveedores: dos empresas pueden tener el mismo RIF o razón social.
ALTER TABLE `proveedores` DROP INDEX `nombre`;
ALTER TABLE `proveedores` ADD UNIQUE KEY `uk_proveedores_empresa_nombre` (`empresa_id`, `nombre`);

-- 4. Documentos numerados: la serie se reinicia por empresa.
ALTER TABLE `cargos_existencias` DROP INDEX `uk_codigo`;
ALTER TABLE `cargos_existencias` ADD UNIQUE KEY `uk_cargos_empresa_codigo` (`empresa_id`, `codigo`);

ALTER TABLE `requisiciones` DROP INDEX `uk_codigo`;
ALTER TABLE `requisiciones` ADD UNIQUE KEY `uk_requisiciones_empresa_codigo` (`empresa_id`, `codigo`);

ALTER TABLE `transferencias_almacen` DROP INDEX `codigo`;
ALTER TABLE `transferencias_almacen` ADD UNIQUE KEY `uk_transf_empresa_codigo` (`empresa_id`, `codigo`);

-- 5. Catálogos auxiliares: "Azul" es un color distinto en cada empresa.
ALTER TABLE `colores` DROP INDEX `uk_color_nombre`;
ALTER TABLE `colores` ADD UNIQUE KEY `uk_colores_empresa_nombre` (`empresa_id`, `nombre`);

ALTER TABLE `materiales` DROP INDEX `uk_material_nombre`;
ALTER TABLE `materiales` ADD UNIQUE KEY `uk_materiales_empresa_nombre` (`empresa_id`, `nombre`);

-- 6. Configuración: cada empresa tiene sus own claves (impuestos, moneda, etc).
ALTER TABLE `configuraciones` DROP INDEX `clave`;
ALTER TABLE `configuraciones` ADD UNIQUE KEY `uk_configuraciones_empresa_clave` (`empresa_id`, `clave`);

-- 7. Precios de proveedor: la combinación sólo es única dentro del tenant.
ALTER TABLE `precios_proveedor` DROP INDEX `uk_producto_proveedor_fecha`;
ALTER TABLE `precios_proveedor` ADD UNIQUE KEY `uk_precios_empresa_prov_fecha`
    (`empresa_id`, `producto_id`, `proveedor_id`, `fecha_registro`);

-- 8. Deshacer la unicidad global del email de usuario.
--    usuarios.email es UNIQUE a propósito: el login es por correo, y permitir
--    dos cuentas con el mismo email haría ambiguo a qué empresa pertenece la
--    sesión. Se mantiene, pero se documenta aquí junto al resto de decisiones
--    de unicidad para que quede constancia del criterio.
--
--    (Sin ALTER: es una decisión de diseño, no un defecto de esquema.)
