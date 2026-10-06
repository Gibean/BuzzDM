-- ============================================================================
-- BuzzDM - Esquema de instalacion
-- ============================================================================
-- Uso:
--   1. Crear la base de datos vacia en el panel del hosting.
--   2. Importar este archivo (phpMyAdmin > Importar, o por consola).
--   3. Copiar .env.example a .env y rellenar las credenciales.
--
-- Que contiene:
--   - Estructura completa (48 tablas, 17 vistas, 3 funciones, 9 triggers).
--   - Datos base minimos e indispensables para arrancar:
--       paquetes           (3)  catalogo de planes del registro
--       permisos_rol       (112) matriz de permisos de admin/supervisor/operador
--       schema_migrations  (21) registro de migraciones ya aplicadas
--
-- Que NO contiene: ninguna empresa, usuario, cliente, producto ni transaccion.
-- Tras importarlo la aplicacion arranca vacia y se registra el primer tenant.
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE TABLE `_syncdero_meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version_hash` varchar(64) NOT NULL,
  `migration_name` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'applied',
  `applied_by` varchar(100) DEFAULT 'system',
  `git_commit` varchar(40) DEFAULT NULL,
  `git_branch` varchar(100) DEFAULT NULL,
  `execution_time_ms` int(11) DEFAULT 0,
  `applied_at` datetime DEFAULT current_timestamp(),
  `migration_content` mediumtext DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_name` (`migration_name`),
  KEY `idx_syncdero_batch` (`batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `acciones_usuario` (
  `id` bigint(20) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` varchar(16) NOT NULL COMMENT 'venta | liberacion | edicion',
  `asiento_codigo` varchar(16) NOT NULL,
  `detalle` longtext DEFAULT NULL COMMENT 'JSON opcional',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `almacenes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `codigo` varchar(20) NOT NULL COMMENT 'Ej: ALM-001, BOD-CEN, TDA-01',
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipo` enum('almacen','bodega','tienda','deposito','otro') NOT NULL DEFAULT 'almacen',
  `es_principal` tinyint(1) DEFAULT 0 COMMENT 'Almacén principal/default',
  `activo` tinyint(1) DEFAULT 1,
  `responsable_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario responsable',
  `notas` text DEFAULT NULL,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_almacenes_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `idx_activo` (`activo`),
  KEY `idx_tipo` (`tipo`),
  KEY `fk_almacen_responsable` (`responsable_id`),
  KEY `idx_almacenes_empresa` (`empresa_id`),
  CONSTRAINT `fk_almacen_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_almacenes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Almacenes/Bodegas/Tiendas del sistema';
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_almacen_codigo

BEFORE INSERT ON almacenes

FOR EACH ROW

BEGIN

    DECLARE next_num INT;



    IF NEW.codigo IS NULL OR NEW.codigo = '' THEN

        SELECT COALESCE(MAX(

            CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)

        ), 0) + 1

        INTO next_num

        FROM almacenes

        WHERE codigo LIKE 'ALM-%';



        SET NEW.codigo = CONCAT('ALM-', LPAD(next_num, 3, '0'));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `backups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) DEFAULT NULL COMMENT 'Ruta completa del archivo',
  `tamaño_bytes` bigint(20) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `tipo` enum('manual','automatico') DEFAULT 'manual' COMMENT 'Tipo de backup: manual o autom├ítico',
  `estado` enum('completado','error','en_proceso') DEFAULT 'completado' COMMENT 'Estado del backup',
  `resultado` varchar(500) DEFAULT NULL COMMENT 'Mensaje de resultado',
  `detalles` text DEFAULT NULL COMMENT 'Detalles en formato JSON: tablas, duraci├│n, etc.',
  `usuario_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que cre├│ el backup manual',
  PRIMARY KEY (`id`),
  KEY `FK_backups_usuarios` (`usuario_id`),
  KEY `fecha` (`fecha`),
  KEY `tipo` (`tipo`),
  KEY `idx_backups_empresa` (`empresa_id`),
  CONSTRAINT `FK_backups_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_backups_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_log_backup
AFTER INSERT ON backups
FOR EACH ROW
BEGIN
    INSERT INTO logs (empresa_id, accion, detalles, fecha)
    VALUES (
        NEW.empresa_id,
        'Backup realizado',
        CONCAT('Archivo: ', NEW.archivo, ' | Tamaño: ',
               FORMAT(NEW.tamaño_bytes/1048576, 2), ' MB'),
        NEW.fecha
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `cargos_existencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `codigo` varchar(20) NOT NULL COMMENT 'Formato: CGO-YYYY-NNNN',
  `fecha` date NOT NULL,
  `concepto` enum('compra','donacion','ajuste_positivo','devolucion_cliente','inventario_inicial','otro') NOT NULL DEFAULT 'compra',
  `concepto_otro` varchar(100) DEFAULT NULL COMMENT 'Descripción si concepto=otro',
  `proveedor_id` int(11) DEFAULT NULL,
  `almacen_id` int(10) unsigned DEFAULT NULL,
  `referencia_externa` varchar(100) DEFAULT NULL COMMENT 'Nro. Factura, Guía, etc.',
  `notas` text DEFAULT NULL,
  `total_productos` int(10) unsigned DEFAULT 0 COMMENT 'Cantidad de líneas/productos distintos',
  `total_unidades` int(10) unsigned DEFAULT 0 COMMENT 'Suma de todas las cantidades',
  `valor_total` decimal(14,2) DEFAULT 0.00 COMMENT 'Suma de subtotales',
  `estado` enum('borrador','confirmado','anulado') NOT NULL DEFAULT 'borrador',
  `registrado_por` int(10) unsigned NOT NULL COMMENT 'Usuario que creó el cargo',
  `confirmado_por` int(10) unsigned DEFAULT NULL,
  `fecha_confirmacion` timestamp NULL DEFAULT NULL,
  `anulado_por` int(10) unsigned DEFAULT NULL,
  `fecha_anulacion` timestamp NULL DEFAULT NULL,
  `motivo_anulacion` varchar(255) DEFAULT NULL,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cargos_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_estado` (`estado`),
  KEY `idx_concepto` (`concepto`),
  KEY `fk_cargo_proveedor` (`proveedor_id`),
  KEY `fk_cargo_registrado` (`registrado_por`),
  KEY `fk_cargo_confirmado` (`confirmado_por`),
  KEY `fk_cargo_anulado` (`anulado_por`),
  KEY `fk_cargo_almacen` (`almacen_id`),
  KEY `idx_cargos_empresa` (`empresa_id`),
  KEY `idx_cargos_tenant_fecha` (`empresa_id`,`fecha`),
  CONSTRAINT `fk_cargo_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_anulado` FOREIGN KEY (`anulado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_confirmado` FOREIGN KEY (`confirmado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_registrado_casc` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cargos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de cargos masivos de existencias';
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_cargo_anulado
AFTER UPDATE ON cargos_existencias
FOR EACH ROW
BEGIN
    IF NEW.estado = 'anulado' AND OLD.estado = 'confirmado' THEN

        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = GREATEST(0, p.stock - d.cantidad),
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id
          AND p.empresa_id = NEW.empresa_id;

        INSERT INTO movimientos_inventario
            (empresa_id, producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
            NEW.empresa_id,
            d.producto_id,
            NEW.anulado_por,
            'ajuste_negativo',
            d.cantidad,
            CONCAT('ANULACIÓN Cargo ', NEW.codigo,
                   CASE WHEN NEW.motivo_anulacion IS NOT NULL
                        THEN CONCAT(' - ', NEW.motivo_anulacion)
                        ELSE '' END),
            (SELECT stock + d.cantidad FROM productos
              WHERE id = d.producto_id AND empresa_id = NEW.empresa_id),
            (SELECT stock FROM productos
              WHERE id = d.producto_id AND empresa_id = NEW.empresa_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_cargo_confirmado
AFTER UPDATE ON cargos_existencias
FOR EACH ROW
BEGIN
    IF NEW.estado = 'confirmado' AND OLD.estado = 'borrador' THEN

        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = p.stock + d.cantidad,
            p.costo_ultimo = d.costo_unitario,
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id
          AND p.empresa_id = NEW.empresa_id;

        INSERT INTO movimientos_inventario
            (empresa_id, producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
            NEW.empresa_id,
            d.producto_id,
            NEW.confirmado_por,
            'entrada',
            d.cantidad,
            CONCAT('Cargo ', NEW.codigo, ' - ',
                   CASE NEW.concepto
                       WHEN 'compra' THEN 'Compra'
                       WHEN 'donacion' THEN 'Donación'
                       WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                       WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                       WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                       ELSE COALESCE(NEW.concepto_otro, 'Otro')
                   END),
            (SELECT stock - d.cantidad FROM productos
              WHERE id = d.producto_id AND empresa_id = NEW.empresa_id),
            (SELECT stock FROM productos
              WHERE id = d.producto_id AND empresa_id = NEW.empresa_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categorias_empresa` (`empresa_id`),
  CONSTRAINT `fk_categorias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `clientes` (
  `id` varchar(50) NOT NULL,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `vip` tinyint(1) DEFAULT 0,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clientes_deleted_at` (`deleted_at`),
  KEY `idx_clientes_empresa` (`empresa_id`),
  KEY `idx_clientes_tenant_nombre` (`empresa_id`,`nombre`),
  CONSTRAINT `fk_clientes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `colores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `codigo_hex` char(7) DEFAULT NULL COMMENT 'Código hex del color (#RRGGBB)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_colores_empresa_nombre` (`empresa_id`,`nombre`),
  KEY `idx_colores_empresa` (`empresa_id`),
  CONSTRAINT `fk_colores_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Colores predefinidos para productos';
CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_configuraciones_empresa_clave` (`empresa_id`,`clave`),
  KEY `idx_configuraciones_empresa` (`empresa_id`),
  CONSTRAINT `fk_configuraciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `data_entradas` (
  `id` int(10) unsigned DEFAULT NULL,
  `asiento` varchar(16) DEFAULT NULL,
  `paquete` varchar(32) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `referencia_pago` varchar(64) DEFAULT NULL,
  `tasa` decimal(12,6) DEFAULT NULL,
  `telefono` varchar(32) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `talla_franela` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `detalle_cargo_existencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cargo_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned NOT NULL DEFAULT 1,
  `costo_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) GENERATED ALWAYS AS (`cantidad` * `costo_unitario`) STORED,
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cargo_producto` (`cargo_id`,`producto_id`) COMMENT 'Un producto solo una vez por cargo',
  KEY `idx_cargo` (`cargo_id`),
  KEY `idx_producto` (`producto_id`),
  CONSTRAINT `fk_detalle_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_producto_casc` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de productos en cada cargo de existencias';
CREATE TABLE `detalle_pedido` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  KEY `idx_pedido_producto` (`pedido_id`,`producto_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `detalle_requisicion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `requisicion_id` int(10) unsigned NOT NULL,
  `producto_id` int(20) unsigned NOT NULL,
  `cantidad_solicitada` int(10) unsigned NOT NULL DEFAULT 1,
  `proveedor_sugerido_id` int(11) DEFAULT NULL COMMENT 'IA: mejor precio histórico',
  `precio_sugerido` decimal(12,4) DEFAULT NULL COMMENT 'Último precio conocido',
  `proveedor_seleccionado_id` int(11) DEFAULT NULL COMMENT 'Override manual usuario',
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_req_producto` (`requisicion_id`,`producto_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_prov_sugerido` (`proveedor_sugerido_id`),
  KEY `idx_prov_seleccionado` (`proveedor_seleccionado_id`),
  CONSTRAINT `fk_detreq_producto_casc` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_prov_seleccionado` FOREIGN KEY (`proveedor_seleccionado_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_prov_sugerido` FOREIGN KEY (`proveedor_sugerido_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_requisicion` FOREIGN KEY (`requisicion_id`) REFERENCES `requisiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en requisición';
CREATE TABLE `detalle_transferencia` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `transferencia_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad_solicitada` int(11) NOT NULL DEFAULT 0 COMMENT 'Cantidad a transferir',
  `cantidad_enviada` int(11) DEFAULT NULL COMMENT 'Cantidad que se envía (puede diferir)',
  `cantidad_recibida` int(11) DEFAULT NULL COMMENT 'Cantidad recibida (verificación)',
  `notas` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transferencia_producto` (`transferencia_id`,`producto_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_transferencia` (`transferencia_id`),
  CONSTRAINT `fk_dt_producto_casc` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dt_transferencia` FOREIGN KEY (`transferencia_id`) REFERENCES `transferencias_almacen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `email_verification_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `email` varchar(150) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `expiracion` timestamp NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_code` (`email`,`codigo`,`usado`),
  KEY `idx_expiracion` (`expiracion`),
  KEY `fk_verif_empresa` (`empresa_id`),
  CONSTRAINT `fk_verif_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `empresa` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_rif` varchar(10) NOT NULL DEFAULT 'J',
  `rif` varchar(20) NOT NULL COMMENT 'Formato: J-12345678-9 o G-98765432-1',
  `nombre` varchar(150) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo del logo (PNG o SVG)',
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `admin_email` varchar(150) DEFAULT NULL,
  `device_fingerprint` varchar(64) DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `is_demo` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','expired','suspended','pending_verification') NOT NULL DEFAULT 'pending_verification',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `logo_svg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_empresa_fingerprint` (`device_fingerprint`),
  UNIQUE KEY `uk_empresa_admin_email` (`admin_email`),
  KEY `idx_empresa_purga` (`is_demo`,`status`,`trial_ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `entradas` (
  `id` int(10) unsigned NOT NULL,
  `persona_id` int(10) unsigned NOT NULL,
  `paquete_id` smallint(5) unsigned NOT NULL,
  `talla_franela_id` smallint(5) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `asiento_codigo` varchar(16) NOT NULL COMMENT 'Ej: k7, l11',
  `referencia_pago` varchar(64) NOT NULL COMMENT 'Texto para conservar ceros a la izquierda'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `importaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','procesado','error') NOT NULL DEFAULT 'pendiente',
  `detalles` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_import_empresa` (`empresa_id`),
  CONSTRAINT `fk_import_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `importaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_log_importacion
AFTER UPDATE ON importaciones
FOR EACH ROW
BEGIN
    IF NEW.estado = 'procesado' AND NEW.tipo = 'csv' THEN
        INSERT INTO logs (empresa_id, usuario_id, accion, detalles, fecha)
        VALUES (
            NEW.empresa_id,
            NEW.usuario_id,
            'Importación CSV',
            CONCAT('Archivo: ', NEW.archivo_nombre, ' | Estado: ', NEW.estado),
            NEW.fecha
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`),
  KEY `idx_logs_empresa` (`empresa_id`),
  CONSTRAINT `fk_logs_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `logs_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(50) NOT NULL DEFAULT '',
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`),
  KEY `idx_logs_login_empresa` (`empresa_id`),
  CONSTRAINT `FK_logs_login_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_logs_login_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_marcas_empresa` (`empresa_id`),
  CONSTRAINT `fk_marcas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `materiales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_materiales_empresa_nombre` (`empresa_id`,`nombre`),
  KEY `idx_materiales_empresa` (`empresa_id`),
  CONSTRAINT `fk_materiales_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Materiales predefinidos para productos';
CREATE TABLE `movimientos_inventario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `producto_id` int(20) unsigned NOT NULL,
  `usuario_id` int(20) unsigned DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste_positivo','ajuste_negativo') NOT NULL,
  `cantidad` int(11) unsigned NOT NULL,
  `justificacion` varchar(255) DEFAULT NULL,
  `cargo_id` int(10) unsigned DEFAULT NULL,
  `almacen_id` int(10) unsigned DEFAULT NULL,
  `ubicacion_id` int(10) unsigned DEFAULT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fk_mov_cargo` (`cargo_id`),
  KEY `fk_mov_almacen` (`almacen_id`),
  KEY `fk_mov_ubicacion` (`ubicacion_id`),
  KEY `idx_tipo_movimiento` (`tipo_movimiento`),
  KEY `idx_mov_inv_empresa` (`empresa_id`),
  CONSTRAINT `fk_mov_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_inv_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_ubicacion` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `notas_adjuntos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `url` text NOT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  CONSTRAINT `notas_adjuntos_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `notas_checklists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `tarea` varchar(255) NOT NULL,
  `completada` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  CONSTRAINT `notas_checklists_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `notas_items_inventario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `notas_items_inventario_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_items_inventario_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `notas_kanban` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `titulo` varchar(150) NOT NULL COMMENT 'Título de la tarjeta',
  `cliente_nombre` varchar(150) DEFAULT NULL COMMENT 'Nombre del cliente',
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contenido` text DEFAULT NULL COMMENT 'Descripción libre detallada',
  `estado` enum('pendiente','seguimiento','resuelto','cancelado') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta') NOT NULL DEFAULT 'media',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden dentro de la misma columna',
  `pedido_vinculado_id` int(10) unsigned DEFAULT NULL COMMENT 'Link opcional a un pedido real si se concreta',
  `fecha_entrega` datetime DEFAULT NULL,
  `etiquetas` varchar(255) DEFAULT NULL COMMENT 'JSON o lista separada por comas',
  `miembros` text DEFAULT NULL COMMENT 'JSON de IDs de usuarios asignados',
  `usuario_id` int(10) unsigned NOT NULL COMMENT 'Quién creó la nota',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `pedido_vinculado_id` (`pedido_vinculado_id`),
  KEY `idx_estado_orden` (`estado`,`orden`),
  KEY `idx_notas_kanban_empresa` (`empresa_id`),
  KEY `idx_notas_tenant_estado` (`empresa_id`,`estado`),
  CONSTRAINT `fk_notas_kanban_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notas_kanban_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_kanban_ibfk_2` FOREIGN KEY (`pedido_vinculado_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `notificaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` enum('info','success','warning','error','task_assign','low_stock','security') DEFAULT 'info',
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `creado_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_notificaciones_empresa` (`empresa_id`),
  CONSTRAINT `fk_notificaciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `paquetes` (
  `id` smallint(5) unsigned NOT NULL,
  `codigo` varchar(32) NOT NULL COMMENT 'Clave estable en minúsculas: deluxe, basico, cosplayer',
  `nombre` varchar(64) NOT NULL COMMENT 'Etiqueta para reportes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `password_reset_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `token` char(64) DEFAULT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `expiracion` timestamp NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `usuario_id` (`usuario_id`),
  KEY `expiracion` (`expiracion`),
  KEY `idx_pwd_reset_empresa` (`empresa_id`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `cliente_id` varchar(50) DEFAULT NULL,
  `referencia` varchar(80) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado_pago` enum('no_pagado','pagado') NOT NULL DEFAULT 'no_pagado',
  `estado_envio` enum('pendiente','enviado','recibido') NOT NULL DEFAULT 'pendiente',
  `direccion_envio` text DEFAULT NULL,
  `notas_envio` text DEFAULT NULL,
  `creado_por` int(10) unsigned DEFAULT NULL,
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `tracking_number` varchar(50) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `creado_por` (`creado_por`),
  KEY `fecha_creacion` (`fecha_creacion`),
  KEY `estado_envio` (`estado_envio`),
  KEY `pedidos_ibfk_1` (`cliente_id`),
  KEY `idx_pedidos_deleted_at` (`deleted_at`),
  KEY `idx_estado_pago` (`estado_pago`),
  KEY `idx_pedidos_empresa` (`empresa_id`),
  KEY `idx_pedidos_tenant_fecha` (`empresa_id`,`fecha_creacion`),
  CONSTRAINT `fk_pedido_cliente_casc` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pedidos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `permisos_rol` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rol` enum('admin','supervisor','operador') NOT NULL,
  `pagina` varchar(100) NOT NULL COMMENT 'Nombre del archivo PHP permitido',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rol_pagina` (`rol`,`pagina`),
  KEY `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de acceso por rol a páginas del sistema';
CREATE TABLE `personas` (
  `id` int(10) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `precios_proveedor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `producto_id` int(20) unsigned NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `precio_compra` decimal(12,4) NOT NULL,
  `fecha_registro` date NOT NULL,
  `cargo_id` int(10) unsigned DEFAULT NULL COMMENT 'Cargo donde se registró la compra',
  `es_ultimo` tinyint(1) DEFAULT 1 COMMENT 'Indica si es el precio más reciente para este producto/proveedor',
  `notas` varchar(255) DEFAULT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_precios_empresa_prov_fecha` (`empresa_id`,`producto_id`,`proveedor_id`,`fecha_registro`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_registro`),
  KEY `idx_es_ultimo` (`es_ultimo`),
  KEY `fk_precio_cargo` (`cargo_id`),
  KEY `idx_precios_prov_empresa` (`empresa_id`),
  CONSTRAINT `fk_precio_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_precio_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precio_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precios_prov_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de precios de compra por producto y proveedor';
CREATE TABLE `producto_componentes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kit_id` int(10) unsigned NOT NULL COMMENT 'Producto tipo kit',
  `componente_id` int(10) unsigned NOT NULL COMMENT 'Producto/kit componente',
  `cantidad` decimal(10,3) NOT NULL DEFAULT 1.000 COMMENT 'Cantidad del componente por kit',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_kit_componente` (`kit_id`,`componente_id`),
  KEY `idx_kit` (`kit_id`),
  KEY `idx_componente` (`componente_id`),
  CONSTRAINT `fk_pc_componente_casc` FOREIGN KEY (`componente_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pc_kit` FOREIGN KEY (`kit_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Componentes de productos tipo kit';
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_validar_componente_kit

BEFORE INSERT ON producto_componentes

FOR EACH ROW

BEGIN

    DECLARE v_tipo VARCHAR(20);



    

    SELECT tipo_producto INTO v_tipo FROM productos WHERE id = NEW.kit_id;

    IF v_tipo != 'kit' THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'El producto padre debe ser de tipo kit';

    END IF;



    

    IF NEW.kit_id = NEW.componente_id THEN

        SIGNAL SQLSTATE '45000'

        SET MESSAGE_TEXT = 'Un kit no puede ser componente de sí mismo';

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `productos` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `sku` varchar(80) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `costo_promedio` decimal(12,4) DEFAULT 0.0000,
  `costo_ultimo` decimal(12,4) DEFAULT 0.0000,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `estado` enum('activo','stock_bajo','agotado') NOT NULL DEFAULT 'activo',
  `tipo_producto` enum('fisico','servicio','kit') NOT NULL DEFAULT 'fisico',
  `maneja_stock` tinyint(1) DEFAULT 1,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `categoria` int(11) DEFAULT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL COMMENT 'Modelo o línea del producto',
  `color` varchar(50) DEFAULT NULL COMMENT 'Color principal del producto',
  `tamanio` varchar(50) DEFAULT NULL COMMENT 'Tamaño, talla o formato',
  `medidas` varchar(100) DEFAULT NULL COMMENT 'Dimensiones físicas del producto',
  `peso_kg` decimal(10,3) DEFAULT NULL COMMENT 'Peso en kilogramos',
  `material` varchar(100) DEFAULT NULL COMMENT 'Material principal del producto',
  `unidad_venta` enum('UND','KG','LT','MT','M2','PACK','CAJA','PAR') DEFAULT 'UND' COMMENT 'Unidad de medida para venta',
  `cantidad_presentacion` int(10) unsigned DEFAULT 1 COMMENT 'Cantidad de unidades por presentación (si es pack/caja)',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `producto_padre_id` int(10) unsigned DEFAULT NULL COMMENT 'Para variantes: ID del producto padre',
  `es_kit_virtual` tinyint(1) DEFAULT 0 COMMENT 'Si true, descuenta componentes; si false, tiene stock propio',
  `variante_atributo` varchar(100) DEFAULT NULL COMMENT 'Ej: Talla M, Color Rojo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_productos_empresa_sku` (`empresa_id`,`sku`),
  KEY `nombre` (`nombre`),
  KEY `sku_2` (`sku`),
  KEY `fk_productos_categoria` (`categoria`),
  KEY `fk_productos_marca` (`marca_id`),
  KEY `fk_productos_proveedor` (`proveedor_id`),
  KEY `idx_productos_deleted_at` (`deleted_at`),
  KEY `idx_producto_padre` (`producto_padre_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_productos_empresa` (`empresa_id`),
  KEY `idx_productos_tenant_estado` (`empresa_id`,`estado`),
  CONSTRAINT `fk_producto_padre` FOREIGN KEY (`producto_padre_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_auto_maneja_stock

BEFORE INSERT ON productos

FOR EACH ROW

BEGIN

    IF NEW.tipo_producto = 'servicio' THEN

        SET NEW.maneja_stock = FALSE;

        SET NEW.stock = 0;

    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN

        SET NEW.maneja_stock = FALSE;

        

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_auto_maneja_stock_update

BEFORE UPDATE ON productos

FOR EACH ROW

BEGIN

    IF NEW.tipo_producto = 'servicio' THEN

        SET NEW.maneja_stock = FALSE;

        SET NEW.stock = 0;

    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN

        SET NEW.maneja_stock = FALSE;

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `rif` varchar(50) DEFAULT NULL,
  `contacto_nombre` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_proveedores_empresa_nombre` (`empresa_id`,`nombre`),
  KEY `idx_deleted_at` (`deleted_at`),
  KEY `idx_proveedores_empresa` (`empresa_id`),
  KEY `idx_proveedores_tenant_nombre` (`empresa_id`,`nombre`),
  CONSTRAINT `fk_proveedores_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `rate_limits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clave` char(64) NOT NULL,
  `accion` varchar(40) NOT NULL,
  `intentos` int(10) unsigned NOT NULL DEFAULT 0,
  `ventana_inicio` datetime NOT NULL,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `creado_at` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_clave` (`clave`),
  KEY `idx_expira` (`bloqueado_hasta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `requisiciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `codigo` varchar(20) NOT NULL COMMENT 'Formato: REQ-YYYY-NNNN',
  `fecha_solicitud` date NOT NULL,
  `estado` enum('pendiente','aprobada','rechazada','procesada') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `notas` text DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL COMMENT 'Solicitante',
  `aprobado_por` int(10) unsigned DEFAULT NULL,
  `fecha_aprobacion` timestamp NULL DEFAULT NULL,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_requisiciones_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_solicitud`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `fk_req_usuario` (`usuario_id`),
  KEY `fk_req_aprobador` (`aprobado_por`),
  KEY `idx_req_empresa` (`empresa_id`),
  CONSTRAINT `fk_req_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_req_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_req_usuario_casc` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de requisiciones de compra';
CREATE TABLE `schema_migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(190) NOT NULL,
  `checksum` char(64) NOT NULL,
  `ejecutada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_schema_migration` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `sesiones_api` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned DEFAULT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `token` char(64) DEFAULT NULL,
  `token_hash` char(64) DEFAULT NULL,
  `expiracion` timestamp NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  UNIQUE KEY `uniq_token_hash` (`token_hash`),
  KEY `usuario_id` (`usuario_id`),
  KEY `sesiones_api_ibfk_2` (`creado_en`),
  KEY `idx_expiracion` (`expiracion`),
  KEY `idx_sesiones_empresa` (`empresa_id`),
  CONSTRAINT `sesiones_api_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
CREATE TABLE `stock_ubicacion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `almacen_id` int(10) unsigned NOT NULL,
  `ubicacion_id` int(10) unsigned DEFAULT NULL COMMENT 'NULL = En tránsito/sin ubicar',
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) DEFAULT 0 COMMENT 'Stock mínimo para esta ubicación',
  `stock_maximo` int(11) DEFAULT NULL COMMENT 'Capacidad máxima',
  `actualizado_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_producto_ubicacion` (`producto_id`,`almacen_id`,`ubicacion_id`),
  KEY `idx_almacen` (`almacen_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `fk_stock_ubicacion` (`ubicacion_id`),
  KEY `idx_stock_ub_empresa` (`empresa_id`),
  CONSTRAINT `fk_stock_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_ub_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_ubicacion` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stock de productos por ubicación';
CREATE TABLE `tallas_franela` (
  `id` smallint(5) unsigned NOT NULL,
  `empresa_id` int(10) unsigned NOT NULL,
  `codigo` varchar(16) NOT NULL,
  KEY `idx_tallas_empresa` (`empresa_id`),
  CONSTRAINT `fk_tallas_franela_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `tasas` (
  `id` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `tasa` decimal(12,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `tasas_cambio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tasa` decimal(18,4) NOT NULL COMMENT 'Valor de 1 USD en moneda local (VES)',
  `fuente` varchar(50) DEFAULT 'manual' COMMENT 'Fuente: manual, bcv, paralelo, etc.',
  `fecha` date NOT NULL COMMENT 'Fecha para la cual aplica esta tasa',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `usuario_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que registró la tasa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fecha_fuente` (`fecha`,`fuente`),
  KEY `idx_fecha` (`fecha`),
  KEY `FK_tasa_usuario` (`usuario_id`),
  CONSTRAINT `FK_tasa_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tasa de cambio diaria USD para referencia de precios';
CREATE TABLE `transferencias_almacen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `codigo` varchar(20) NOT NULL COMMENT 'Código único (TRF-YYYYMMDD-NNN)',
  `almacen_origen_id` int(10) unsigned NOT NULL,
  `almacen_destino_id` int(10) unsigned NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_envio` date DEFAULT NULL,
  `fecha_recepcion` date DEFAULT NULL,
  `estado` enum('borrador','en_transito','recibido','cancelado') DEFAULT 'borrador',
  `motivo` varchar(255) DEFAULT NULL COMMENT 'Motivo de la transferencia',
  `notas` text DEFAULT NULL,
  `referencia_externa` varchar(100) DEFAULT NULL COMMENT 'Guía de envío u otro ref',
  `solicitado_por` int(10) unsigned NOT NULL COMMENT 'Usuario que solicita',
  `enviado_por` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que envía',
  `recibido_por` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que recibe',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_transf_empresa_codigo` (`empresa_id`,`codigo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fechas` (`fecha_solicitud`,`fecha_envio`),
  KEY `idx_almacenes` (`almacen_origen_id`,`almacen_destino_id`),
  KEY `idx_almacen_origen` (`almacen_origen_id`),
  KEY `idx_almacen_destino` (`almacen_destino_id`),
  KEY `idx_transf_empresa` (`empresa_id`),
  CONSTRAINT `fk_transf_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_trf_almacen_destino_casc` FOREIGN KEY (`almacen_destino_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_trf_almacen_origen_casc` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=CURRENT_USER*/ /*!50003 TRIGGER trg_transferencia_codigo

BEFORE INSERT ON transferencias_almacen

FOR EACH ROW

BEGIN

    DECLARE next_num INT;

    DECLARE fecha_str VARCHAR(8);



    IF NEW.codigo IS NULL OR NEW.codigo = '' THEN

        SET fecha_str = DATE_FORMAT(NEW.fecha_solicitud, '%Y%m%d');



        SELECT COALESCE(MAX(

            CAST(SUBSTRING_INDEX(codigo, '-', -1) AS UNSIGNED)

        ), 0) + 1

        INTO next_num

        FROM transferencias_almacen

        WHERE codigo LIKE CONCAT('TRF-', fecha_str, '-%');



        SET NEW.codigo = CONCAT('TRF-', fecha_str, '-', LPAD(next_num, 3, '0'));

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
CREATE TABLE `ubicaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `almacen_id` int(10) unsigned NOT NULL,
  `codigo` varchar(30) NOT NULL COMMENT 'Ej: A-01-E1-N2 (Pasillo-Estante-Nivel)',
  `nombre` varchar(100) DEFAULT NULL,
  `pasillo` varchar(10) DEFAULT NULL,
  `estante` varchar(10) DEFAULT NULL,
  `nivel` varchar(10) DEFAULT NULL,
  `capacidad_maxima` int(11) DEFAULT NULL COMMENT 'Unidades máximas',
  `activo` tinyint(1) DEFAULT 1,
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_almacen_codigo` (`almacen_id`,`codigo`),
  KEY `idx_activo` (`activo`),
  KEY `idx_ubicaciones_empresa` (`empresa_id`),
  CONSTRAINT `fk_ubicacion_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ubicaciones_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ubicaciones físicas dentro de cada almacén';
CREATE TABLE `usuarios` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` int(10) unsigned NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `two_factor_secret` varchar(64) DEFAULT NULL,
  `rol` enum('admin','operador','supervisor') NOT NULL DEFAULT 'operador',
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `intentos_fallidos` int(11) DEFAULT 0,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuarios_empresa` (`empresa_id`),
  CONSTRAINT `fk_usuarios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_almacenes_resumen` AS SELECT
 1 AS `id`,
  1 AS `codigo`,
  1 AS `nombre`,
  1 AS `tipo`,
  1 AS `es_principal`,
  1 AS `activo`,
  1 AS `direccion`,
  1 AS `telefono`,
  1 AS `responsable_nombre`,
  1 AS `total_ubicaciones`,
  1 AS `productos_en_stock`,
  1 AS `unidades_totales`,
  1 AS `creado_at` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_cargos_detalle_completo` AS SELECT
 1 AS `cargo_id`,
  1 AS `cargo_codigo`,
  1 AS `cargo_fecha`,
  1 AS `cargo_estado`,
  1 AS `detalle_id`,
  1 AS `producto_id`,
  1 AS `producto_sku`,
  1 AS `producto_nombre`,
  1 AS `cantidad`,
  1 AS `costo_unitario`,
  1 AS `subtotal`,
  1 AS `detalle_notas` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_cargos_resumen` AS SELECT
 1 AS `id`,
  1 AS `codigo`,
  1 AS `fecha`,
  1 AS `concepto`,
  1 AS `concepto_texto`,
  1 AS `proveedor_nombre`,
  1 AS `referencia_externa`,
  1 AS `total_productos`,
  1 AS `total_unidades`,
  1 AS `valor_total`,
  1 AS `estado`,
  1 AS `estado_texto`,
  1 AS `registrado_por_nombre`,
  1 AS `creado_at`,
  1 AS `confirmado_por_nombre`,
  1 AS `fecha_confirmacion`,
  1 AS `anulado_por_nombre`,
  1 AS `fecha_anulacion`,
  1 AS `motivo_anulacion` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_inventario` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `descripcion`,
  1 AS `precio`,
  1 AS `costo_promedio`,
  1 AS `costo_ultimo`,
  1 AS `stock`,
  1 AS `min_stock`,
  1 AS `estado`,
  1 AS `categoria_nombre`,
  1 AS `marca_nombre`,
  1 AS `proveedor_nombre`,
  1 AS `modelo`,
  1 AS `color`,
  1 AS `tamanio`,
  1 AS `medidas`,
  1 AS `peso_kg`,
  1 AS `material`,
  1 AS `unidad_venta`,
  1 AS `cantidad_presentacion`,
  1 AS `creado_at`,
  1 AS `actualizado_at`,
  1 AS `deleted_at` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_kit_componentes` AS SELECT
 1 AS `id`,
  1 AS `kit_id`,
  1 AS `kit_sku`,
  1 AS `kit_nombre`,
  1 AS `componente_id`,
  1 AS `componente_sku`,
  1 AS `componente_nombre`,
  1 AS `componente_tipo`,
  1 AS `cantidad`,
  1 AS `componente_precio`,
  1 AS `componente_stock`,
  1 AS `subtotal`,
  1 AS `orden` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_low_stock` AS SELECT
 1 AS `empresa_id`,
  1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `descripcion`,
  1 AS `precio`,
  1 AS `stock`,
  1 AS `min_stock`,
  1 AS `estado`,
  1 AS `creado_at`,
  1 AS `actualizado_at`,
  1 AS `bajo_minimo` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_op_backup` AS SELECT
 1 AS `registro_id`,
  1 AS `operacion`,
  1 AS `detalles`,
  1 AS `fecha` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_op_import` AS SELECT
 1 AS `empresa_id`,
  1 AS `registro_id`,
  1 AS `operacion`,
  1 AS `detalles`,
  1 AS `fecha`,
  1 AS `usuario` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_pedidos_pendientes` AS SELECT
 1 AS `id`,
  1 AS `referencia`,
  1 AS `fecha_creacion`,
  1 AS `total`,
  1 AS `estado_pago`,
  1 AS `estado_envio`,
  1 AS `direccion_envio`,
  1 AS `notas_envio`,
  1 AS `actualizado_at`,
  1 AS `cliente_nombre`,
  1 AS `cliente_email`,
  1 AS `creado_por_nombre` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_productos_completo` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `descripcion`,
  1 AS `precio`,
  1 AS `tipo_producto`,
  1 AS `maneja_stock`,
  1 AS `es_kit_virtual`,
  1 AS `variante_atributo`,
  1 AS `producto_padre_id`,
  1 AS `producto_padre_nombre`,
  1 AS `producto_padre_sku`,
  1 AS `stock_efectivo`,
  1 AS `stock_fisico`,
  1 AS `min_stock`,
  1 AS `estado`,
  1 AS `marca_id`,
  1 AS `marca_nombre`,
  1 AS `categoria`,
  1 AS `categoria_nombre`,
  1 AS `total_variantes`,
  1 AS `total_componentes`,
  1 AS `creado_at`,
  1 AS `deleted_at` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_rotacion_inventario` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `stock`,
  1 AS `min_stock`,
  1 AS `total_vendido`,
  1 AS `porcentaje_rotacion`,
  1 AS `estado_rotacion` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_stock_detallado` AS SELECT
 1 AS `id`,
  1 AS `almacen_id`,
  1 AS `almacen_codigo`,
  1 AS `almacen_nombre`,
  1 AS `ubicacion_id`,
  1 AS `ubicacion_codigo`,
  1 AS `ubicacion_full`,
  1 AS `producto_id`,
  1 AS `sku`,
  1 AS `producto_nombre`,
  1 AS `cantidad`,
  1 AS `stock_minimo`,
  1 AS `stock_maximo`,
  1 AS `estado_stock` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_stock_por_almacen` AS SELECT
 1 AS `almacen_id`,
  1 AS `almacen_codigo`,
  1 AS `almacen_nombre`,
  1 AS `producto_id`,
  1 AS `sku`,
  1 AS `producto_nombre`,
  1 AS `stock_almacen`,
  1 AS `num_ubicaciones` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_tasa_hoy` AS SELECT
 1 AS `tasa_actual`,
  1 AS `fecha_tasa`,
  1 AS `fuente` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_top5_down` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `descripcion`,
  1 AS `precio`,
  1 AS `stock`,
  1 AS `estado` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_top5_up` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `total_vendido` */;
SET character_set_client = @saved_cs_client;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `vw_productos_stock_bajo_activos` AS SELECT
 1 AS `empresa_id`,
  1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `stock`,
  1 AS `min_stock`,
  1 AS `costo_ultimo`,
  1 AS `proveedor_default_id`,
  1 AS `proveedor_default_nombre`,
  1 AS `nivel_stock`,
  1 AS `cantidad_faltante` */;
SET character_set_client = @saved_cs_client;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER FUNCTION `fn_generar_codigo_cargo`() RETURNS varchar(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    READS SQL DATA
    DETERMINISTIC
BEGIN

    DECLARE v_year CHAR(4);

    DECLARE v_next_num INT;

    DECLARE v_codigo VARCHAR(20);



    SET v_year = YEAR(CURDATE());



    SELECT COALESCE(MAX(

        CAST(SUBSTRING(codigo, 10) AS UNSIGNED)

    ), 0) + 1

    INTO v_next_num

    FROM cargos_existencias

    WHERE codigo LIKE CONCAT('CGO-', v_year, '-%');



    SET v_codigo = CONCAT('CGO-', v_year, '-', LPAD(v_next_num, 4, '0'));



    RETURN v_codigo;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER FUNCTION `fn_generar_codigo_requisicion`() RETURNS varchar(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
    READS SQL DATA
    DETERMINISTIC
BEGIN

    DECLARE v_year CHAR(4);

    DECLARE v_next_num INT;



    SET v_year = YEAR(CURDATE());



    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 10) AS UNSIGNED)), 0) + 1

    INTO v_next_num

    FROM requisiciones

    WHERE codigo LIKE CONCAT('REQ-', v_year, '-%');



    RETURN CONCAT('REQ-', v_year, '-', LPAD(v_next_num, 4, '0'));

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
DELIMITER ;;
CREATE DEFINER=CURRENT_USER FUNCTION `fn_stock_kit_virtual`(p_kit_id INT UNSIGNED) RETURNS int(11)
    READS SQL DATA
    DETERMINISTIC
BEGIN

    DECLARE v_min_stock INT DEFAULT NULL;

    DECLARE v_comp_stock INT;

    DECLARE v_comp_cantidad DECIMAL(10,3);

    DECLARE v_comp_es_kit BOOLEAN;

    DECLARE v_comp_id INT;

    DECLARE v_done INT DEFAULT 0;



    DECLARE cur_componentes CURSOR FOR

        SELECT pc.componente_id, pc.cantidad, p.tipo_producto = 'kit' AND p.es_kit_virtual

        FROM producto_componentes pc

        INNER JOIN productos p ON p.id = pc.componente_id

        WHERE pc.kit_id = p_kit_id;



    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;



    OPEN cur_componentes;



    read_loop: LOOP

        FETCH cur_componentes INTO v_comp_id, v_comp_cantidad, v_comp_es_kit;

        IF v_done THEN

            LEAVE read_loop;

        END IF;



        
        IF v_comp_es_kit THEN

            SET v_comp_stock = fn_stock_kit_virtual(v_comp_id);

        ELSE

            SELECT stock INTO v_comp_stock FROM productos WHERE id = v_comp_id;

        END IF;



        
        IF v_comp_cantidad > 0 THEN

            SET v_comp_stock = FLOOR(v_comp_stock / v_comp_cantidad);

        END IF;



        
        IF v_min_stock IS NULL OR v_comp_stock < v_min_stock THEN

            SET v_min_stock = v_comp_stock;

        END IF;

    END LOOP;



    CLOSE cur_componentes;



    RETURN COALESCE(v_min_stock, 0);

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50001 DROP VIEW IF EXISTS `v_almacenes_resumen`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_almacenes_resumen` AS select `a`.`id` AS `id`,`a`.`codigo` AS `codigo`,`a`.`nombre` AS `nombre`,`a`.`tipo` AS `tipo`,`a`.`es_principal` AS `es_principal`,`a`.`activo` AS `activo`,`a`.`direccion` AS `direccion`,`a`.`telefono` AS `telefono`,`u`.`nombre` AS `responsable_nombre`,(select count(0) from `ubicaciones` where `ubicaciones`.`almacen_id` = `a`.`id` and `ubicaciones`.`activo` = 1) AS `total_ubicaciones`,(select count(distinct `stock_ubicacion`.`producto_id`) from `stock_ubicacion` where `stock_ubicacion`.`almacen_id` = `a`.`id` and `stock_ubicacion`.`cantidad` > 0) AS `productos_en_stock`,(select coalesce(sum(`stock_ubicacion`.`cantidad`),0) from `stock_ubicacion` where `stock_ubicacion`.`almacen_id` = `a`.`id`) AS `unidades_totales`,`a`.`creado_at` AS `creado_at` from (`almacenes` `a` left join `usuarios` `u` on(`a`.`responsable_id` = `u`.`id`)) order by `a`.`es_principal` desc,`a`.`nombre` */;
/*!50001 DROP VIEW IF EXISTS `v_cargos_detalle_completo`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_cargos_detalle_completo` AS select `c`.`id` AS `cargo_id`,`c`.`codigo` AS `cargo_codigo`,`c`.`fecha` AS `cargo_fecha`,`c`.`estado` AS `cargo_estado`,`d`.`id` AS `detalle_id`,`d`.`producto_id` AS `producto_id`,`pr`.`sku` AS `producto_sku`,`pr`.`nombre` AS `producto_nombre`,`d`.`cantidad` AS `cantidad`,`d`.`costo_unitario` AS `costo_unitario`,`d`.`subtotal` AS `subtotal`,`d`.`notas` AS `detalle_notas` from ((`cargos_existencias` `c` join `detalle_cargo_existencias` `d` on(`c`.`id` = `d`.`cargo_id`)) join `productos` `pr` on(`d`.`producto_id` = `pr`.`id`)) order by `c`.`id` desc,`d`.`id` */;
/*!50001 DROP VIEW IF EXISTS `v_cargos_resumen`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_cargos_resumen` AS select `c`.`id` AS `id`,`c`.`codigo` AS `codigo`,`c`.`fecha` AS `fecha`,`c`.`concepto` AS `concepto`,case `c`.`concepto` when 'compra' then 'Compra' when 'donacion' then 'Donación' when 'ajuste_positivo' then 'Ajuste Positivo' when 'devolucion_cliente' then 'Devolución Cliente' when 'inventario_inicial' then 'Inventario Inicial' when 'otro' then coalesce(`c`.`concepto_otro`,'Otro') end AS `concepto_texto`,`p`.`nombre` AS `proveedor_nombre`,`c`.`referencia_externa` AS `referencia_externa`,`c`.`total_productos` AS `total_productos`,`c`.`total_unidades` AS `total_unidades`,`c`.`valor_total` AS `valor_total`,`c`.`estado` AS `estado`,case `c`.`estado` when 'borrador' then 'Borrador' when 'confirmado' then 'Confirmado' when 'anulado' then 'Anulado' end AS `estado_texto`,`u`.`nombre` AS `registrado_por_nombre`,`c`.`creado_at` AS `creado_at`,`uc`.`nombre` AS `confirmado_por_nombre`,`c`.`fecha_confirmacion` AS `fecha_confirmacion`,`ua`.`nombre` AS `anulado_por_nombre`,`c`.`fecha_anulacion` AS `fecha_anulacion`,`c`.`motivo_anulacion` AS `motivo_anulacion` from ((((`cargos_existencias` `c` left join `proveedores` `p` on(`c`.`proveedor_id` = `p`.`id`)) left join `usuarios` `u` on(`c`.`registrado_por` = `u`.`id`)) left join `usuarios` `uc` on(`c`.`confirmado_por` = `uc`.`id`)) left join `usuarios` `ua` on(`c`.`anulado_por` = `ua`.`id`)) order by `c`.`creado_at` desc */;
/*!50001 DROP VIEW IF EXISTS `v_inventario`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`costo_promedio` AS `costo_promedio`,`p`.`costo_ultimo` AS `costo_ultimo`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`c`.`nombre` AS `categoria_nombre`,`m`.`nombre` AS `marca_nombre`,`pr`.`nombre` AS `proveedor_nombre`,`p`.`modelo` AS `modelo`,`p`.`color` AS `color`,`p`.`tamanio` AS `tamanio`,`p`.`medidas` AS `medidas`,`p`.`peso_kg` AS `peso_kg`,`p`.`material` AS `material`,`p`.`unidad_venta` AS `unidad_venta`,`p`.`cantidad_presentacion` AS `cantidad_presentacion`,`p`.`creado_at` AS `creado_at`,`p`.`actualizado_at` AS `actualizado_at`,`p`.`deleted_at` AS `deleted_at` from (((`productos` `p` left join `categorias` `c` on(`p`.`categoria` = `c`.`id`)) left join `marcas` `m` on(`p`.`marca_id` = `m`.`id`)) left join `proveedores` `pr` on(`p`.`proveedor_id` = `pr`.`id`)) where `p`.`deleted_at` is null */;
/*!50001 DROP VIEW IF EXISTS `v_kit_componentes`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_kit_componentes` AS select `pc`.`id` AS `id`,`pc`.`kit_id` AS `kit_id`,`pk`.`sku` AS `kit_sku`,`pk`.`nombre` AS `kit_nombre`,`pc`.`componente_id` AS `componente_id`,`p`.`sku` AS `componente_sku`,`p`.`nombre` AS `componente_nombre`,`p`.`tipo_producto` AS `componente_tipo`,`pc`.`cantidad` AS `cantidad`,`p`.`precio` AS `componente_precio`,case when `p`.`tipo_producto` = 'kit' and `p`.`es_kit_virtual` <> 0 then `fn_stock_kit_virtual`(`p`.`id`) when `p`.`tipo_producto` = 'servicio' then 999999 else `p`.`stock` end AS `componente_stock`,`pc`.`cantidad` * `p`.`precio` AS `subtotal`,`pc`.`orden` AS `orden` from ((`producto_componentes` `pc` join `productos` `p` on(`p`.`id` = `pc`.`componente_id`)) join `productos` `pk` on(`pk`.`id` = `pc`.`kit_id`)) order by `pc`.`kit_id`,`pc`.`orden`,`pc`.`id` */;
/*!50001 DROP VIEW IF EXISTS `v_low_stock`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_low_stock` AS select `p`.`empresa_id` AS `empresa_id`,`p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`p`.`creado_at` AS `creado_at`,`p`.`actualizado_at` AS `actualizado_at`,`p`.`stock` <= `p`.`min_stock` AS `bajo_minimo` from `productos` `p` where `p`.`stock` <= `p`.`min_stock` and `p`.`estado` <> 'agotado' order by `p`.`stock` */;
/*!50001 DROP VIEW IF EXISTS `v_op_backup`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_op_backup` AS select `backups`.`id` AS `registro_id`,'Backup realizado' AS `operacion`,concat('Archivo: ',`backups`.`archivo`,' | Tamaño: ',format(`backups`.`tamaño_bytes` / 1048576,2),' MB') AS `detalles`,`backups`.`fecha` AS `fecha` from `backups` order by `backups`.`fecha` desc */;
/*!50001 DROP VIEW IF EXISTS `v_op_import`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_op_import` AS select `i`.`empresa_id` AS `empresa_id`,`i`.`id` AS `registro_id`,'Importación CSV' AS `operacion`,concat('Archivo: ',`i`.`archivo_nombre`,' | Estado: ',`i`.`estado`) AS `detalles`,`i`.`fecha` AS `fecha`,`u`.`nombre` AS `usuario` from (`importaciones` `i` left join `usuarios` `u` on(`u`.`id` = `i`.`usuario_id` and `u`.`empresa_id` = `i`.`empresa_id`)) where `i`.`tipo` = 'csv' order by `i`.`fecha` desc */;
/*!50001 DROP VIEW IF EXISTS `v_pedidos_pendientes`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_pedidos_pendientes` AS select `ped`.`id` AS `id`,`ped`.`referencia` AS `referencia`,`ped`.`fecha_creacion` AS `fecha_creacion`,`ped`.`total` AS `total`,`ped`.`estado_pago` AS `estado_pago`,`ped`.`estado_envio` AS `estado_envio`,`ped`.`direccion_envio` AS `direccion_envio`,`ped`.`notas_envio` AS `notas_envio`,`ped`.`actualizado_at` AS `actualizado_at`,`c`.`nombre` AS `cliente_nombre`,`c`.`email` AS `cliente_email`,`u`.`nombre` AS `creado_por_nombre` from ((`pedidos` `ped` join `clientes` `c` on(`ped`.`cliente_id` = `c`.`id`)) left join `usuarios` `u` on(`ped`.`creado_por` = `u`.`id`)) where `ped`.`estado_pago` = 'no_pagado' or `ped`.`estado_envio` in ('pendiente','enviado') order by `ped`.`fecha_creacion` desc */;
/*!50001 DROP VIEW IF EXISTS `v_productos_completo`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_productos_completo` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`tipo_producto` AS `tipo_producto`,`p`.`maneja_stock` AS `maneja_stock`,`p`.`es_kit_virtual` AS `es_kit_virtual`,`p`.`variante_atributo` AS `variante_atributo`,`p`.`producto_padre_id` AS `producto_padre_id`,`pp`.`nombre` AS `producto_padre_nombre`,`pp`.`sku` AS `producto_padre_sku`,case when `p`.`tipo_producto` = 'kit' and `p`.`es_kit_virtual` <> 0 then `fn_stock_kit_virtual`(`p`.`id`) when `p`.`tipo_producto` = 'servicio' then NULL else `p`.`stock` end AS `stock_efectivo`,`p`.`stock` AS `stock_fisico`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`p`.`marca_id` AS `marca_id`,`m`.`nombre` AS `marca_nombre`,`p`.`categoria` AS `categoria`,`c`.`nombre` AS `categoria_nombre`,(select count(0) from `productos` `v` where `v`.`producto_padre_id` = `p`.`id`) AS `total_variantes`,(select count(0) from `producto_componentes` `pc` where `pc`.`kit_id` = `p`.`id`) AS `total_componentes`,`p`.`creado_at` AS `creado_at`,`p`.`deleted_at` AS `deleted_at` from (((`productos` `p` left join `productos` `pp` on(`pp`.`id` = `p`.`producto_padre_id`)) left join `marcas` `m` on(`m`.`id` = `p`.`marca_id`)) left join `categorias` `c` on(`c`.`id` = `p`.`categoria`)) */;
/*!50001 DROP VIEW IF EXISTS `v_rotacion_inventario`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_rotacion_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,ifnull(sum(`d`.`cantidad`),0) AS `total_vendido`,round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) AS `porcentaje_rotacion`,case when `p`.`stock` <= `p`.`min_stock` then 'CRÍTICO' when ifnull(sum(`d`.`cantidad`),0) = 0 then 'ESTANCADO' else 'NORMAL' end AS `estado_rotacion` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) */;
/*!50001 DROP VIEW IF EXISTS `v_stock_detallado`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_stock_detallado` AS select `su`.`id` AS `id`,`a`.`id` AS `almacen_id`,`a`.`codigo` AS `almacen_codigo`,`a`.`nombre` AS `almacen_nombre`,`u`.`id` AS `ubicacion_id`,`u`.`codigo` AS `ubicacion_codigo`,concat(coalesce(`u`.`pasillo`,''),'-',coalesce(`u`.`estante`,''),'-',coalesce(`u`.`nivel`,'')) AS `ubicacion_full`,`p`.`id` AS `producto_id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `producto_nombre`,`su`.`cantidad` AS `cantidad`,`su`.`stock_minimo` AS `stock_minimo`,`su`.`stock_maximo` AS `stock_maximo`,case when `su`.`cantidad` <= `su`.`stock_minimo` then 'bajo' when `su`.`stock_maximo` is not null and `su`.`cantidad` >= `su`.`stock_maximo` then 'lleno' else 'normal' end AS `estado_stock` from (((`stock_ubicacion` `su` join `almacenes` `a` on(`a`.`id` = `su`.`almacen_id`)) join `productos` `p` on(`p`.`id` = `su`.`producto_id`)) left join `ubicaciones` `u` on(`u`.`id` = `su`.`ubicacion_id`)) where `a`.`activo` = 1 */;
/*!50001 DROP VIEW IF EXISTS `v_stock_por_almacen`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_stock_por_almacen` AS select `a`.`id` AS `almacen_id`,`a`.`codigo` AS `almacen_codigo`,`a`.`nombre` AS `almacen_nombre`,`p`.`id` AS `producto_id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `producto_nombre`,coalesce(sum(`su`.`cantidad`),0) AS `stock_almacen`,count(distinct `su`.`ubicacion_id`) AS `num_ubicaciones` from ((`almacenes` `a` join `productos` `p`) left join `stock_ubicacion` `su` on(`su`.`almacen_id` = `a`.`id` and `su`.`producto_id` = `p`.`id`)) where `a`.`activo` = 1 and `p`.`deleted_at` is null group by `a`.`id`,`p`.`id` order by `a`.`nombre`,`p`.`nombre` */;
/*!50001 DROP VIEW IF EXISTS `v_tasa_hoy`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_tasa_hoy` AS select coalesce((select `tasas_cambio`.`tasa` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`tasa` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),1) AS `tasa_actual`,coalesce((select `tasas_cambio`.`fecha` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`fecha` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),curdate()) AS `fecha_tasa`,coalesce((select `tasas_cambio`.`fuente` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`fuente` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),'no_configurada') AS `fuente` */;
/*!50001 DROP VIEW IF EXISTS `v_top5_down`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_top5_down` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`estado` AS `estado` from `productos` `p` where !(`p`.`id` in (select distinct `detalle_pedido`.`producto_id` from `detalle_pedido`)) order by `p`.`nombre` */;
/*!50001 DROP VIEW IF EXISTS `v_top5_up`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `v_top5_up` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,sum(`d`.`cantidad`) AS `total_vendido` from (`detalle_pedido` `d` join `productos` `p` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by sum(`d`.`cantidad`) desc */;
/*!50001 DROP VIEW IF EXISTS `vw_productos_stock_bajo_activos`*/;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=CURRENT_USER SQL SECURITY INVOKER */
/*!50001 VIEW `vw_productos_stock_bajo_activos` AS select `p`.`empresa_id` AS `empresa_id`,`p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`costo_ultimo` AS `costo_ultimo`,`p`.`proveedor_id` AS `proveedor_default_id`,`prov`.`nombre` AS `proveedor_default_nombre`,case when `p`.`stock` = 0 then 'agotado' when `p`.`stock` <= `p`.`min_stock` then 'bajo' else 'normal' end AS `nivel_stock`,`p`.`min_stock` - `p`.`stock` AS `cantidad_faltante` from (`productos` `p` left join `proveedores` `prov` on(`prov`.`id` = `p`.`proveedor_id` and `prov`.`empresa_id` = `p`.`empresa_id`)) where `p`.`estado` <> 'inactivo' and `p`.`stock` <= `p`.`min_stock` order by case when `p`.`stock` = 0 then 0 else 1 end,`p`.`min_stock` - `p`.`stock` desc */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Datos base indispensables
-- ============================================================================
-- Sin permisos_rol no habria permisos asignados a ningun rol y la aplicacion
-- responderia 403 en todas las pantallas. Sin paquetes no habria plan por el
-- que crear una cuenta. Son definicion del sistema, no datos de tenants.

INSERT INTO `paquetes` VALUES (1,'deluxe','Deluxe'),(2,'basico','B├ísico'),(3,'cosplayer','Cosplayer');

INSERT INTO `permisos_rol` VALUES (1,'admin','about.php','2026-01-07 02:44:22'),(2,'admin','admin.php','2026-01-07 02:44:22'),(3,'admin','ajustes.php','2026-01-07 02:44:22'),(4,'admin','almacenes.php','2026-01-07 02:44:22'),(5,'admin','api_backups.php','2026-01-18 03:40:43'),(6,'admin','api_cargos.php','2026-01-18 00:25:57'),(7,'admin','api_catalogs.php','2026-01-18 17:34:31'),(8,'admin','api_clientes.php','2026-01-18 00:25:57'),(9,'admin','api_import.php','2026-01-18 02:53:52'),(10,'admin','api_kits.php','2026-01-18 18:05:42'),(11,'admin','api_locations.php','2026-01-18 18:05:42'),(12,'admin','api_notes.php','2026-01-18 03:06:12'),(13,'admin','api_notifications.php','2026-01-18 00:34:42'),(14,'admin','api_orders.php','2026-01-18 00:46:01'),(15,'admin','api_products.php','2026-01-18 00:25:57'),(16,'admin','api_requisiciones.php','2026-02-07 06:30:24'),(17,'admin','api_sales_by_status_chart.php','2026-01-07 02:44:22'),(18,'admin','api_sales_chart.php','2026-01-07 02:44:22'),(19,'admin','api_settings.php','2026-01-18 03:34:55'),(20,'admin','api_shipments.php','2026-01-18 18:05:42'),(21,'admin','api_stats.php','2026-01-18 00:34:42'),(22,'admin','api_suppliers.php','2026-01-18 03:19:57'),(23,'admin','api_transferencias.php','2026-02-04 19:19:51'),(24,'admin','api_users.php','2026-01-18 16:47:34'),(25,'admin','api_warehouses.php','2026-01-18 02:48:53'),(26,'admin','cargos_existencias.php','2026-01-07 02:44:22'),(27,'admin','catalogos.php','2026-01-07 21:37:49'),(28,'admin','clientes.php','2026-01-07 02:44:22'),(29,'admin','company_settings.php','2026-01-07 02:44:22'),(30,'admin','dashboard.php','2026-01-07 02:44:22'),(31,'admin','db.php','2026-01-07 02:44:22'),(32,'admin','download_template.php','2026-01-12 21:41:12'),(33,'admin','envios.php','2026-01-07 02:44:22'),(34,'admin','importacion.php','2026-01-07 02:44:22'),(35,'admin','inventario.php','2026-01-07 02:44:22'),(36,'admin','login.php','2026-01-07 02:44:22'),(37,'admin','logout.php','2026-01-07 02:44:22'),(38,'admin','manual.php','2026-01-07 02:44:22'),(39,'admin','pedidos.php','2026-01-07 02:44:22'),(40,'admin','proveedores.php','2026-01-07 02:44:22'),(41,'admin','reports.php','2026-01-07 02:44:22'),(42,'admin','requisiciones.php','2026-02-07 06:30:24'),(43,'admin','search_api.php','2026-01-07 02:44:22'),(44,'admin','sesion.php','2026-01-07 02:44:22'),(45,'admin','sidebar.php','2026-01-07 02:44:22'),(46,'admin','todo.php','2026-01-07 03:49:24'),(47,'admin','transferencias.php','2026-02-04 19:19:51'),(48,'operador','about.php','2026-01-07 02:44:23'),(49,'operador','ajustes.php','2026-01-07 02:44:22'),(50,'operador','api_notes.php','2026-01-18 03:06:12'),(51,'operador','api_notifications.php','2026-01-18 00:34:42'),(52,'operador','api_orders.php','2026-01-18 00:46:01'),(53,'operador','api_requisiciones.php','2026-02-07 06:30:24'),(54,'operador','api_sales_by_status_chart.php','2026-01-07 02:44:23'),(55,'operador','api_sales_chart.php','2026-01-07 02:44:23'),(56,'operador','api_shipments.php','2026-01-18 18:05:42'),(57,'operador','api_stats.php','2026-01-18 00:34:42'),(58,'operador','api_warehouses.php','2026-01-18 02:48:53'),(59,'operador','dashboard.php','2026-01-07 02:44:23'),(60,'operador','db.php','2026-01-07 02:44:23'),(61,'operador','envios.php','2026-01-07 02:44:23'),(62,'operador','login.php','2026-01-07 02:44:23'),(63,'operador','logout.php','2026-01-07 02:44:23'),(64,'operador','manual.php','2026-01-07 02:44:23'),(65,'operador','pedidos.php','2026-01-07 02:44:23'),(66,'operador','requisiciones.php','2026-02-07 06:30:24'),(67,'operador','search_api.php','2026-01-07 02:44:23'),(68,'operador','sesion.php','2026-01-07 02:44:23'),(69,'operador','sidebar.php','2026-01-07 02:44:23'),(70,'operador','todo.php','2026-01-07 03:49:24'),(71,'supervisor','about.php','2026-01-07 02:44:23'),(72,'supervisor','ajustes.php','2026-01-07 02:44:22'),(73,'supervisor','almacenes.php','2026-01-07 02:44:23'),(74,'supervisor','api_cargos.php','2026-01-18 00:25:57'),(75,'supervisor','api_catalogs.php','2026-01-18 17:34:31'),(76,'supervisor','api_clientes.php','2026-01-18 00:25:57'),(77,'supervisor','api_import.php','2026-01-18 02:53:52'),(78,'supervisor','api_kits.php','2026-01-18 18:05:42'),(79,'supervisor','api_locations.php','2026-01-18 18:05:42'),(80,'supervisor','api_notes.php','2026-01-18 03:06:12'),(81,'supervisor','api_notifications.php','2026-01-18 00:34:42'),(82,'supervisor','api_orders.php','2026-01-18 00:46:01'),(83,'supervisor','api_products.php','2026-01-18 00:25:57'),(84,'supervisor','api_requisiciones.php','2026-02-07 06:30:24'),(85,'supervisor','api_sales_by_status_chart.php','2026-01-07 02:44:23'),(86,'supervisor','api_sales_chart.php','2026-01-07 02:44:23'),(87,'supervisor','api_shipments.php','2026-01-18 18:05:42'),(88,'supervisor','api_stats.php','2026-01-18 00:34:42'),(89,'supervisor','api_suppliers.php','2026-01-18 03:19:57'),(90,'supervisor','api_transferencias.php','2026-02-04 19:19:51'),(91,'supervisor','api_warehouses.php','2026-01-18 02:48:53'),(92,'supervisor','cargos_existencias.php','2026-01-07 02:44:23'),(93,'supervisor','catalogos.php','2026-01-07 21:37:49'),(94,'supervisor','clientes.php','2026-01-07 02:44:23'),(95,'supervisor','dashboard.php','2026-01-07 02:44:23'),(96,'supervisor','db.php','2026-01-07 02:44:23'),(97,'supervisor','download_template.php','2026-01-12 21:41:12'),(98,'supervisor','envios.php','2026-01-07 02:44:23'),(99,'supervisor','importacion.php','2026-01-07 02:44:23'),(100,'supervisor','inventario.php','2026-01-07 02:44:23'),(101,'supervisor','login.php','2026-01-07 02:44:23'),(102,'supervisor','logout.php','2026-01-07 02:44:23'),(103,'supervisor','manual.php','2026-01-07 02:44:23'),(104,'supervisor','pedidos.php','2026-01-07 02:44:23'),(105,'supervisor','proveedores.php','2026-01-07 02:44:23'),(106,'supervisor','reports.php','2026-01-07 02:44:23'),(107,'supervisor','requisiciones.php','2026-02-07 06:30:24'),(108,'supervisor','search_api.php','2026-01-07 02:44:23'),(109,'supervisor','sesion.php','2026-01-07 02:44:23'),(110,'supervisor','sidebar.php','2026-01-07 02:44:23'),(111,'supervisor','todo.php','2026-01-07 03:49:24'),(112,'supervisor','transferencias.php','2026-02-04 19:19:51');

INSERT INTO `schema_migrations` VALUES (1,'add_2fa_columns.sql','61199df27293421dfde3219dc8a289d859f3d164025dfc3e0d1df0bb69f85a09','2026-09-29 01:21:48'),(2,'almacen_codigo_auto.sql','0e32e788f88d2c3f0fd5a94f78789f238431cbe0d3995a63aae61b4015b37655','2026-09-29 01:21:48'),(3,'email_verification_setup.sql','e9e82edd39f83d888b97a3b17b52c0a685fc5af12810cdcf43b837eff808f3d2','2026-09-29 01:21:48'),(4,'fix_cargos_existencias_columns.sql','6afa5f7da660ed5dc493ad7c54ba29a314c8f34d367493c3c88ee56e821141a2','2026-09-29 01:21:48'),(5,'modulo_requisiciones.sql','edd34a38fc18d589fc16fa328d83b8dfa9be3397bdbb0b82ba861e732ae1e246','2026-09-29 01:21:48'),(6,'optimize_sesiones_api.sql','0a3b0e65d8d2d202178d4f7e596bdf626186f8e41db57d2ac5f3056af591633d','2026-09-29 01:21:48'),(7,'permisos_transferencias.sql','c6f0f5220babaf35639bfcbced1e51576618ec68cc29aaad295299a6d5a50406','2026-09-29 01:21:48'),(8,'precios_proveedor.sql','5a5cbf70c8feca2daea7f09043d4b49f67da9cd7aaaeb195190f009f06c18bf7','2026-09-29 01:21:48'),(9,'saas_multitenant_setup.sql','0d3c81c78b2577e29652a17d1514ec7695e24b1c68eae9492e8cf558335788e0','2026-09-29 01:21:48'),(10,'transferencias_almacen.sql','2dec3aa3a62a83b31964bd486bd664be88de32620b0b51bb76c4fb7c307b0f9e','2026-09-29 01:21:48'),(11,'asignar_stock_almacen.sql','7d5134475efe5b4a459845ffde6c6a7538d2e0d42942b13cbf4af381d5d577a0','2026-09-29 01:21:48'),(12,'fix_saas_bomb_001.sql','da4cd02479364c1532cfa5b245ef25e10fd53ac4ef16d5576cdde2d4f2bc9a4f','2026-09-29 01:21:54'),(13,'saas_integrity_002.sql','deeaad1eb20593e4b509258c0fedf921193369e0a6d9aa9e30d429530741bf59','2026-09-29 01:21:55'),(14,'saas_tenant_uniqueness_003.sql','60a140139569323128437f01595a277f13d36876ec7a0dc3e8ecc34b02a7ddc5','2026-09-29 01:26:56'),(15,'saas_purge_coverage_004.sql','867b1cb76b776704605fc53bc9a05d67ddec0e18817bf7e458151f1ae8107a3f','2026-09-29 01:36:33'),(16,'saas_trigger_tenant_005.sql','6a2e25b1f293d73c9c32ba8d044e1ce3533d160716ffbef721978d6d7345223a','2026-09-30 17:04:00'),(17,'saas_write_tenant_006.sql','8dc9e3079b9b877155f485c4105ff54da8809741b07d1a0364f00236c7577799','2026-09-30 17:33:28'),(18,'saas_view_tenant_007.sql','87aed995a220ff0ddb668ee87d692d73cb28a1d081d6bf71455099f5a6630b20','2026-09-30 17:47:00'),(19,'saas_write_tenant_008.sql','49b2e32ab66928f2cf855605527618d499305c4a26cf0a7f0522fa4b0bf8766a','2026-10-06 01:00:26'),(20,'rate_limits_setup.sql','ff2b37ba7a32eb6b0bf440641f812714fe5410985a0950b040c32c2fdfebddaf','2026-10-06 04:19:57'),(21,'password_reset_token_hash.sql','07d4efe901b3589908575ae2099570742b3c195de371d5af626e00f00f2dfe6e','2026-10-06 05:13:03');

-- Fin del esquema. Para cargar datos de demostracion ficticios (opcional):
--   mysql -u USUARIO -p BASE < db/seed_demo.sql