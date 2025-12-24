-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         11.8.2-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para dbuzz
DROP DATABASE IF EXISTS `dbuzz`;
CREATE DATABASE IF NOT EXISTS `dbuzz` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `dbuzz`;

-- Volcando estructura para tabla dbuzz.backups
DROP TABLE IF EXISTS `backups`;
CREATE TABLE IF NOT EXISTS `backups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `FK_backups_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.clientes
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` varchar(50) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `vip` tinyint(1) DEFAULT 0,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clientes_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.detalle_pedido
DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE IF NOT EXISTS `detalle_pedido` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED,
  PRIMARY KEY (`id`),
  KEY `pedido_id` (`pedido_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.empresa
DROP TABLE IF EXISTS `empresa`;
CREATE TABLE IF NOT EXISTS `empresa` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_rif` varchar(10) NOT NULL DEFAULT 'J',
  `rif` varchar(20) NOT NULL COMMENT 'Formato: J-12345678-9 o G-98765432-1',
  `nombre` varchar(150) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo del logo (PNG o SVG)',
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `logo_svg` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.importaciones
DROP TABLE IF EXISTS `importaciones`;
CREATE TABLE IF NOT EXISTS `importaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `archivo_nombre` varchar(255) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','procesado','error') NOT NULL DEFAULT 'pendiente',
  `detalles` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `importaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.logs
DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.logs_login
DROP TABLE IF EXISTS `logs_login`;
CREATE TABLE IF NOT EXISTS `logs_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(50) NOT NULL DEFAULT '',
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.marcas
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.movimientos_inventario
DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(20) unsigned NOT NULL,
  `usuario_id` int(20) unsigned DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida','ajuste_positivo','ajuste_negativo') NOT NULL,
  `cantidad` int(11) unsigned NOT NULL,
  `justificacion` varchar(255) DEFAULT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.password_recovery
DROP TABLE IF EXISTS `password_recovery`;
CREATE TABLE IF NOT EXISTS `password_recovery` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(20) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiracion` timestamp NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  KEY `expiracion` (`expiracion`),
  CONSTRAINT `password_recovery_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `token` char(64) NOT NULL,
  `expiracion` timestamp NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  KEY `expiracion` (`expiracion`),
  CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.pedidos
DROP TABLE IF EXISTS `pedidos`;
CREATE TABLE IF NOT EXISTS `pedidos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.productos
DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `sku` varchar(80) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 10,
  `estado` enum('activo','stock_bajo','agotado') NOT NULL DEFAULT 'activo',
  `creado_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `categoria` int(11) DEFAULT NULL,
  `marca_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `nombre` (`nombre`),
  KEY `sku_2` (`sku`),
  KEY `fk_productos_categoria` (`categoria`),
  KEY `fk_productos_marca` (`marca_id`),
  KEY `fk_productos_proveedor` (`proveedor_id`),
  KEY `idx_productos_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.proveedores
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(150) NOT NULL,
  `contacto_nombre` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.sesiones_api
DROP TABLE IF EXISTS `sesiones_api`;
CREATE TABLE IF NOT EXISTS `sesiones_api` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `token` char(64) NOT NULL,
  `expiracion` timestamp NOT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `usuario_id` (`usuario_id`),
  KEY `sesiones_api_ibfk_2` (`creado_en`),
  CONSTRAINT `sesiones_api_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla dbuzz.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','operador','supervisor') NOT NULL DEFAULT 'operador',
  `estado` enum('activo','inactivo','bloqueado') NOT NULL DEFAULT 'activo',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `actualizado_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `intentos_fallidos` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para vista dbuzz.v_inventario
DROP VIEW IF EXISTS `v_inventario`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_inventario` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`precio` DECIMAL(12,2) NOT NULL,
	`stock` INT(11) NOT NULL,
	`veces_vendido` BIGINT(21) NOT NULL,
	`total_vendido` DECIMAL(32,0) NULL,
	`ingresos_totales` DECIMAL(44,2) NULL
);

-- Volcando estructura para vista dbuzz.v_low_stock
DROP VIEW IF EXISTS `v_low_stock`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_low_stock` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`precio` DECIMAL(12,2) NOT NULL,
	`stock` INT(11) NOT NULL,
	`min_stock` INT(11) NOT NULL,
	`estado` ENUM('activo','stock_bajo','agotado') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`creado_at` TIMESTAMP NOT NULL,
	`actualizado_at` TIMESTAMP NULL,
	`bajo_minimo` INT(1) NOT NULL
);

-- Volcando estructura para vista dbuzz.v_op_backup
DROP VIEW IF EXISTS `v_op_backup`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_op_backup` (
	`registro_id` INT(10) UNSIGNED NOT NULL,
	`operacion` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`detalles` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`fecha` TIMESTAMP NULL
);

-- Volcando estructura para vista dbuzz.v_op_import
DROP VIEW IF EXISTS `v_op_import`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_op_import` (
	`registro_id` INT(10) UNSIGNED NOT NULL,
	`operacion` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`detalles` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`fecha` TIMESTAMP NULL,
	`usuario` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci'
);

-- Volcando estructura para vista dbuzz.v_pedidos_pendientes
DROP VIEW IF EXISTS `v_pedidos_pendientes`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_pedidos_pendientes` (
	`id` INT(10) UNSIGNED NOT NULL,
	`referencia` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`fecha_creacion` TIMESTAMP NULL,
	`total` DECIMAL(12,2) NOT NULL,
	`estado_pago` ENUM('no_pagado','pagado') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`estado_envio` ENUM('pendiente','enviado','recibido') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`direccion_envio` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`notas_envio` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`actualizado_at` TIMESTAMP NULL,
	`cliente_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`cliente_email` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`creado_por_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci'
);

-- Volcando estructura para vista dbuzz.v_rotacion_inventario
DROP VIEW IF EXISTS `v_rotacion_inventario`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_rotacion_inventario` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`stock` INT(11) NOT NULL,
	`min_stock` INT(11) NOT NULL,
	`total_vendido` DECIMAL(32,0) NOT NULL,
	`porcentaje_rotacion` DECIMAL(38,2) NULL,
	`estado_rotacion` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci'
);

-- Volcando estructura para vista dbuzz.v_top5_down
DROP VIEW IF EXISTS `v_top5_down`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_top5_down` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`precio` DECIMAL(12,2) NOT NULL,
	`stock` INT(11) NOT NULL,
	`estado` ENUM('activo','stock_bajo','agotado') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci'
);

-- Volcando estructura para vista dbuzz.v_top5_up
DROP VIEW IF EXISTS `v_top5_up`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_top5_up` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`total_vendido` DECIMAL(32,0) NULL
);

-- Volcando estructura para disparador dbuzz.trg_log_backup
DROP TRIGGER IF EXISTS `trg_log_backup`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_log_backup
AFTER INSERT ON backups
FOR EACH ROW
BEGIN
    INSERT INTO logs (accion, detalles, fecha)
    VALUES (
        'Backup realizado',
        CONCAT('Archivo: ', NEW.archivo, ' | Tamaño: ',
               FORMAT(NEW.tamaño_bytes/1048576, 2), ' MB'),
        NEW.fecha
    );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador dbuzz.trg_log_importacion
DROP TRIGGER IF EXISTS `trg_log_importacion`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_log_importacion
AFTER UPDATE ON importaciones
FOR EACH ROW
BEGIN
    IF NEW.estado = 'procesado' AND NEW.tipo = 'csv' THEN
        INSERT INTO logs (usuario_id, accion, detalles, fecha)
        VALUES (
            NEW.usuario_id,
            'Importación CSV',
            CONCAT('Archivo: ', NEW.archivo_nombre, ' | Estado: ', NEW.estado),
            NEW.fecha
        );
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,count(`d`.`id`) AS `veces_vendido`,sum(`d`.`cantidad`) AS `total_vendido`,sum(`d`.`cantidad` * `d`.`precio_unitario`) AS `ingresos_totales` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by sum(`d`.`cantidad`) desc 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_low_stock`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_low_stock` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`p`.`creado_at` AS `creado_at`,`p`.`actualizado_at` AS `actualizado_at`,`p`.`stock` <= `p`.`min_stock` AS `bajo_minimo` from `productos` `p` where `p`.`stock` <= `p`.`min_stock` and `p`.`estado` <> 'agotado' order by `p`.`stock` 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_op_backup`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_op_backup` AS select `backups`.`id` AS `registro_id`,'Backup realizado' AS `operacion`,concat('Archivo: ',`backups`.`archivo`,' | Tamaño: ',format(`backups`.`tamaño_bytes` / 1048576,2),' MB') AS `detalles`,`backups`.`fecha` AS `fecha` from `backups` order by `backups`.`fecha` desc 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_op_import`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_op_import` AS select `i`.`id` AS `registro_id`,'Importación CSV' AS `operacion`,concat('Archivo: ',`i`.`archivo_nombre`,' | Estado: ',`i`.`estado`) AS `detalles`,`i`.`fecha` AS `fecha`,`u`.`nombre` AS `usuario` from (`importaciones` `i` left join `usuarios` `u` on(`i`.`usuario_id` = `u`.`id`)) where `i`.`tipo` = 'csv' order by `i`.`fecha` desc 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_pedidos_pendientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_pedidos_pendientes` AS select `ped`.`id` AS `id`,`ped`.`referencia` AS `referencia`,`ped`.`fecha_creacion` AS `fecha_creacion`,`ped`.`total` AS `total`,`ped`.`estado_pago` AS `estado_pago`,`ped`.`estado_envio` AS `estado_envio`,`ped`.`direccion_envio` AS `direccion_envio`,`ped`.`notas_envio` AS `notas_envio`,`ped`.`actualizado_at` AS `actualizado_at`,`c`.`nombre` AS `cliente_nombre`,`c`.`email` AS `cliente_email`,`u`.`nombre` AS `creado_por_nombre` from ((`pedidos` `ped` join `clientes` `c` on(`ped`.`cliente_id` = `c`.`id`)) left join `usuarios` `u` on(`ped`.`creado_por` = `u`.`id`)) where `ped`.`estado_pago` = 'no_pagado' or `ped`.`estado_envio` in ('pendiente','enviado') order by `ped`.`fecha_creacion` desc 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_rotacion_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_rotacion_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,ifnull(sum(`d`.`cantidad`),0) AS `total_vendido`,round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) AS `porcentaje_rotacion`,case when `p`.`stock` <= `p`.`min_stock` then 'CRÍTICO' when ifnull(sum(`d`.`cantidad`),0) = 0 then 'ESTANCADO' else 'NORMAL' end AS `estado_rotacion` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_top5_down`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_top5_down` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`estado` AS `estado` from `productos` `p` where !(`p`.`id` in (select distinct `detalle_pedido`.`producto_id` from `detalle_pedido`)) order by `p`.`nombre` 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_top5_up`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_top5_up` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,sum(`d`.`cantidad`) AS `total_vendido` from (`detalle_pedido` `d` join `productos` `p` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by sum(`d`.`cantidad`) desc 
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
