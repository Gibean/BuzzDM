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

-- Volcando estructura para tabla dbuzz.almacenes
DROP TABLE IF EXISTS `almacenes`;
CREATE TABLE IF NOT EXISTS `almacenes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_activo` (`activo`),
  KEY `idx_tipo` (`tipo`),
  KEY `fk_almacen_responsable` (`responsable_id`),
  CONSTRAINT `fk_almacen_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Almacenes/Bodegas/Tiendas del sistema';

-- Volcando datos para la tabla dbuzz.almacenes: ~1 rows (aproximadamente)
DELETE FROM `almacenes`;
INSERT INTO `almacenes` (`id`, `codigo`, `nombre`, `direccion`, `telefono`, `tipo`, `es_principal`, `activo`, `responsable_id`, `notas`, `creado_at`, `actualizado_at`) VALUES
	(1, 'ALM-PRINCIPAL', 'Almacén Principal', NULL, NULL, 'almacen', 1, 1, NULL, NULL, '2026-01-05 18:09:00', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.backups: ~3 rows (aproximadamente)
DELETE FROM `backups`;
INSERT INTO `backups` (`id`, `archivo`, `ruta_archivo`, `tamaño_bytes`, `fecha`, `tipo`, `estado`, `resultado`, `detalles`, `usuario_id`) VALUES
	(14, 'dbuzzbackup_2025-12-25_02-06-16.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/../backups/dbuzzbackup_2025-12-25_02-06-16.sql', 75226, '2025-12-25 01:06:17', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":13,"duracion_ms":1276,"tamanio_mb":0.07,"tipo_ejecucion":"http","metodo":"fallback"}', NULL),
	(15, 'dbuzzbackup_2026-01-05_17-49-56.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/../backups/dbuzzbackup_2026-01-05_17-49-56.sql', 99431, '2026-01-05 16:49:58', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":10,"duracion_ms":1789,"tamanio_mb":0.09,"tipo_ejecucion":"http","metodo":"fallback"}', NULL),
	(16, 'dbuzzbackup_2026-01-07_00-15-27.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/../backups/dbuzzbackup_2026-01-07_00-15-27.sql', 132626, '2026-01-06 23:15:30', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":11,"duracion_ms":2285,"tamanio_mb":0.13,"tipo_ejecucion":"http","metodo":"fallback"}', NULL);

-- Volcando estructura para tabla dbuzz.cargos_existencias
DROP TABLE IF EXISTS `cargos_existencias`;
CREATE TABLE IF NOT EXISTS `cargos_existencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_estado` (`estado`),
  KEY `idx_concepto` (`concepto`),
  KEY `fk_cargo_proveedor` (`proveedor_id`),
  KEY `fk_cargo_registrado` (`registrado_por`),
  KEY `fk_cargo_confirmado` (`confirmado_por`),
  KEY `fk_cargo_anulado` (`anulado_por`),
  KEY `fk_cargo_almacen` (`almacen_id`),
  CONSTRAINT `fk_cargo_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_anulado` FOREIGN KEY (`anulado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_confirmado` FOREIGN KEY (`confirmado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de cargos masivos de existencias';

-- Volcando datos para la tabla dbuzz.cargos_existencias: ~0 rows (aproximadamente)
DELETE FROM `cargos_existencias`;

-- Volcando estructura para tabla dbuzz.categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.categorias: ~6 rows (aproximadamente)
DELETE FROM `categorias`;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
	(1, 'Papelería', 'Artículos de papelería generales: cuadernos, bolígrafos, goma, etc.'),
	(2, 'Útiles escolares', 'Artículos pensados para estudiantes: lápices, reglas, estuches'),
	(3, 'Oficina', 'Papel, resmas, archivadores y suministros de oficina'),
	(4, 'Artículos de arte', 'Material para dibujo y pintura: acuarelas, cartulinas, marcadores'),
	(5, 'Adhesivos y pegamentos', 'Pegamento en barra, líquido, cintas adhesivas'),
	(6, 'Cajas y archivadores', 'Archivadores, carpetas y organizadores');

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

-- Volcando datos para la tabla dbuzz.clientes: ~6 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `notas`, `vip`, `creado_at`, `deleted_at`) VALUES
	('E-30003000', 'Juan Almeida', 'misogino.300@gmail.com', '+34900 861 494', 'Municipio Santiago', 'Epale Juan Almeida misógino yo no conozco de puntas la única punta que conozco es esta… la punta de la teta porque como verás estoy amamantando desde la izquierda cómoda es bien depinga verdad?? hablar y criticar a los demás y como tú me hablabas de cacao yo no tengo cacao para ti tengo patadas y te patearé el culo donde te vea #TePateareElCuloDondeTeVea y no por lo que has dicho respecto al municipio Santiago de Mariño sino por meterte conmigo en mi condición de mujer', 0, '2025-12-02 05:14:09', '2025-12-24 15:36:41'),
	('V-10000001', 'María López', 'm.lopez@example.com', '+58 412 345 6789', 'Calle Comercio 123, Caracas', '', 0, '2025-12-01 13:00:00', NULL),
	('V-10000002', 'José Pérez', 'j.perez@example.com', '+58 414 987 6543', 'Av. Bolívar 45, Valencia', 'Cliente frecuente', 1, '2025-11-20 15:30:00', NULL),
	('V-10000003', 'Librería La esquina', 'contabilidad@laesquina.com', '+58 212 999 8888', 'C.C. Centro, Local 12', 'Mayorista', 0, '2025-10-05 18:22:00', NULL),
	('V-30535128', 'Gisvel Aray', '', '+584128759068', 'Calle Luisa Caceres de Arismendi', '<3', 1, '2025-11-21 00:56:28', NULL),
	('V-99999999', 'Test Restore', 'test@restore.com', '+581234567890', '', '', 0, '2025-12-02 05:18:09', '2025-12-02 05:20:58');

-- Volcando estructura para tabla dbuzz.colores
DROP TABLE IF EXISTS `colores`;
CREATE TABLE IF NOT EXISTS `colores` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `codigo_hex` char(7) DEFAULT NULL COMMENT 'Código hex del color (#RRGGBB)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_color_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Colores predefinidos para productos';

-- Volcando datos para la tabla dbuzz.colores: ~14 rows (aproximadamente)
DELETE FROM `colores`;
INSERT INTO `colores` (`id`, `nombre`, `codigo_hex`) VALUES
	(1, 'Negro', '#000000'),
	(2, 'Blanco', '#FFFFFF'),
	(3, 'Rojo', '#FF0000'),
	(4, 'Azul', '#0000FF'),
	(5, 'Verde', '#00FF00'),
	(6, 'Amarillo', '#FFFF00'),
	(7, 'Naranja', '#FFA500'),
	(8, 'Rosa', '#FFC0CB'),
	(9, 'Morado', '#800080'),
	(10, 'Gris', '#808080'),
	(11, 'Marrón', '#8B4513'),
	(12, 'Beige', '#F5F5DC'),
	(13, 'Transparente', NULL),
	(14, 'Multicolor', NULL);

-- Volcando estructura para tabla dbuzz.configuraciones
DROP TABLE IF EXISTS `configuraciones`;
CREATE TABLE IF NOT EXISTS `configuraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.configuraciones: ~4 rows (aproximadamente)
DELETE FROM `configuraciones`;
INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `fecha_actualizacion`) VALUES
	(1, 'backup_fallback_enabled', '1', 'Habilitar sistema de backup automático fallback', '2025-12-25 01:04:32'),
	(5, 'store_name', 'Librería El Estudiante', 'Nombre mostrado en factura y sitio', '2025-12-25 15:31:03'),
	(6, 'currency', 'VES', 'Moneda del sistema', '2025-12-25 15:31:03'),
	(7, 'tax_percent', '12.00', 'Porcentaje de IVA aplicado a ventas', '2025-12-25 15:31:03');

-- Volcando estructura para tabla dbuzz.detalle_cargo_existencias
DROP TABLE IF EXISTS `detalle_cargo_existencias`;
CREATE TABLE IF NOT EXISTS `detalle_cargo_existencias` (
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
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de productos en cada cargo de existencias';

-- Volcando datos para la tabla dbuzz.detalle_cargo_existencias: ~0 rows (aproximadamente)
DELETE FROM `detalle_cargo_existencias`;

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
  KEY `idx_pedido_producto` (`pedido_id`,`producto_id`),
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.detalle_pedido: ~22 rows (aproximadamente)
DELETE FROM `detalle_pedido`;
INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
	(3, 1, 1, 1, 10.00),
	(9, 2, 2, 2, 2.50),
	(10, 2, 4, 3, 1.20),
	(11, 2, 8, 1, 1.10),
	(12, 3, 5, 1, 8.50),
	(13, 3, 7, 2, 0.80),
	(14, 4, 11, 10, 6.50),
	(15, 4, 2, 20, 2.50),
	(16, 5, 4, 5, 1.20),
	(17, 5, 6, 2, 3.75),
	(18, 6, 3, 2, 5.00),
	(19, 6, 7, 1, 0.80),
	(20, 7, 9, 1, 15.00),
	(21, 7, 8, 2, 1.10),
	(22, 8, 12, 1, 22.00),
	(23, 8, 5, 1, 8.50),
	(24, 9, 10, 1, 4.25),
	(25, 9, 2, 3, 2.50),
	(26, 10, 4, 10, 1.20),
	(27, 10, 13, 2, 2.00),
	(28, 11, 3, 5, 5.00),
	(29, 11, 11, 2, 6.50);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.empresa: ~1 rows (aproximadamente)
DELETE FROM `empresa`;
INSERT INTO `empresa` (`id`, `tipo_rif`, `rif`, `nombre`, `logo_path`, `direccion`, `telefono`, `email`, `creado_at`, `actualizado_at`, `logo_svg`) VALUES
	(1, 'J', 'J-12345678-9', 'Librería Papelería El Estudiante', NULL, 'Av. Principal 100, Caracas', '+58 212 555 0000', 'info@elestudiante.com', '2025-12-25 15:31:03', NULL, NULL);

-- Volcando estructura para función dbuzz.fn_generar_codigo_cargo
DROP FUNCTION IF EXISTS `fn_generar_codigo_cargo`;
DELIMITER //
CREATE FUNCTION `fn_generar_codigo_cargo`() RETURNS varchar(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
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
END//
DELIMITER ;

-- Volcando estructura para función dbuzz.fn_stock_kit_virtual
DROP FUNCTION IF EXISTS `fn_stock_kit_virtual`;
DELIMITER //
CREATE FUNCTION `fn_stock_kit_virtual`(p_kit_id INT UNSIGNED) RETURNS int(11)
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

        -- Si el componente es un kit virtual, calcular recursivamente
        IF v_comp_es_kit THEN
            SET v_comp_stock = fn_stock_kit_virtual(v_comp_id);
        ELSE
            SELECT stock INTO v_comp_stock FROM productos WHERE id = v_comp_id;
        END IF;

        -- Calcular cuántos kits se pueden armar con este componente
        IF v_comp_cantidad > 0 THEN
            SET v_comp_stock = FLOOR(v_comp_stock / v_comp_cantidad);
        END IF;

        -- El mínimo determina cuántos kits se pueden armar
        IF v_min_stock IS NULL OR v_comp_stock < v_min_stock THEN
            SET v_min_stock = v_comp_stock;
        END IF;
    END LOOP;

    CLOSE cur_componentes;

    RETURN COALESCE(v_min_stock, 0);
END//
DELIMITER ;

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

-- Volcando datos para la tabla dbuzz.importaciones: ~8 rows (aproximadamente)
DELETE FROM `importaciones`;
INSERT INTO `importaciones` (`id`, `usuario_id`, `tipo`, `archivo_nombre`, `fecha`, `estado`, `detalles`) VALUES
	(1, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:07:47', 'error', '{"Productos":{"status":"failed","message":"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'."]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(2, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:07:47', 'error', '{"Productos":{"status":"failed","message":"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'."]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(3, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:11:17', 'error', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(4, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:11:17', 'error', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(5, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 23:39:24', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[],"messages":["Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c7483d1ed2f468dc","Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1655dc2152002121","Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2d7e26b26152637c","Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 128fa5e7f2027c83","Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 59cffbb6c192f532","Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d0157d0cf549a93b","Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 76f6ce738b8dd30f","Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 17d6ce4f3231b357","Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 66c191dccef7f035","Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 6263a2a65e768024","Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a03905da871c1b54","Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 5f4ee1fa722a7b9f","Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ad6eb20da6a65471","Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 0e725d16f0bb9147","Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9ca034dabb1f3f99","Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: acb90565f0caa3e2","Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 203e374836947579","Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e3ad77b0141a685f","Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a22051e99dd145e3","Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2a0a5937b19aae86","Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a5b5b78eab46fa6b","Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b2345d3dc52d39fd","Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d4a4ae07311ad69","Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 02002faa208628a5","Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9b2fca23509294c2"]}}'),
	(6, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 23:39:28', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]}}'),
	(7, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-14 01:43:35', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[],"messages":["Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d3920f73202a5251","Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b59ca94455e4d25f","Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: f2d64fe574c81acf","Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 26663e7177c18acf","Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: dbb7e77c99b543e8","Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 18e42493816cb018","Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 275ff56985f70e46","Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d6bc4a710d29f14","Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ed7314d2a217e0eb","Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d27b7a2a6b2a48f4","Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ee6dbcb1f43751b7","Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9d7b573ab7f6e90f","Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e9b18c122a506151","Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 91b5c5a39e2edf1b","Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: bd9843613ae3c1c3","Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 8396f94382d8a5e7","Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4e5bd2c31c409629","Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 571c3ca40d272cc2","Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: aa665e8c558f3160","Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a9f9249620561c50","Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 71326e217cfdb7c6","Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 42f9f90c5e297ce9","Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c45acec4924ee81c","Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4cfcca5e94244f79","Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1cdb3a33f1e4344c"]}}'),
	(8, 1, 'general', 'Importacion_Agosto.xlsx', '2025-08-14 01:43:38', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]}}');

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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs: ~39 rows (aproximadamente)
DELETE FROM `logs`;
INSERT INTO `logs` (`id`, `usuario_id`, `accion`, `detalles`, `fecha`) VALUES
	(1, 1, 'Solicitud de recuperación de contraseña', 'Email: admin@buzz.com', '2025-11-29 02:18:49'),
	(2, 1, 'Contraseña cambiada', 'Mediante recuperación de contraseña', '2025-11-29 02:19:17'),
	(3, NULL, 'Backup realizado', 'Archivo: backup_dbuzz_2025-12-02_05-54-21.sql | Tamaño: 0.06 MB', '2025-12-02 04:54:22'),
	(4, 1, 'Backup Manual', '{"archivo":"backup_dbuzz_2025-12-02_05-54-21.sql","tamanio_mb":0.06,"duracion_ms":770,"tablas":16}', '2025-12-02 04:54:22'),
	(5, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_06-03-19.sql | Tamaño: 0.06 MB', '2025-12-02 05:03:20'),
	(6, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_06-03-19.sql","tamanio_mb":0.06,"duracion_ms":531,"tablas":16}', '2025-12-02 05:03:20'),
	(7, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_06-04-28.sql | Tamaño: 0.07 MB', '2025-12-02 05:04:43'),
	(8, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_06-04-28.sql","tamanio_mb":0.07,"duracion_ms":15079,"tablas":16}', '2025-12-02 05:04:43'),
	(9, 1, 'Restauración de Backup', '{"archivo":"dbuzzbackup_2025-12-02_06-07-56.sql","duracion_ms":800,"resultado":"Exitoso"}', '2025-12-02 05:08:54'),
	(10, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_06-11-25.sql | Tamaño: 0.07 MB', '2025-12-02 05:11:26'),
	(11, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_06-11-25.sql","tamanio_mb":0.07,"duracion_ms":481,"tablas":16}', '2025-12-02 05:11:26'),
	(12, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_06-14-15.sql | Tamaño: 0.07 MB', '2025-12-02 05:14:16'),
	(13, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_06-14-15.sql","tamanio_mb":0.07,"duracion_ms":459,"tablas":15}', '2025-12-02 05:14:16'),
	(14, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_08-01-41.sql | Tamaño: 0.07 MB', '2025-12-02 07:01:42'),
	(15, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_08-01-41.sql","tamanio_mb":0.07,"duracion_ms":766,"tablas":14}', '2025-12-02 07:01:42'),
	(16, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_08-05-58.sql | Tamaño: 0.07 MB', '2025-12-02 07:05:59'),
	(17, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_08-05-58.sql","tamanio_mb":0.07,"duracion_ms":520,"tablas":14}', '2025-12-02 07:05:59'),
	(18, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_08-06-53.sql | Tamaño: 0.07 MB', '2025-12-02 07:06:54'),
	(19, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_08-06-53.sql","tamanio_mb":0.07,"duracion_ms":521,"tablas":14}', '2025-12-02 07:06:54'),
	(20, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-02_08-08-01.sql | Tamaño: 0.07 MB', '2025-12-02 07:08:01'),
	(21, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-02_08-08-01.sql","tamanio_mb":0.07,"duracion_ms":493,"tablas":14}', '2025-12-02 07:08:01'),
	(22, 1, 'Restauración de Backup', '{"archivo":"dbuzzbackup_2025-12-02_08-10-45.sql","duracion_ms":1158,"resultado":"Exitoso"}', '2025-12-24 02:11:04'),
	(23, 1, 'Restauración de Backup', '{"archivo":"dbuzzbackup_2025-12-24_16-29-00.sql","duracion_ms":1291,"resultado":"Exitoso"}', '2025-12-24 15:29:31'),
	(24, 1, 'Restauración de Backup', '{"archivo":"dbuzzbackup_2025-12-24_23-08-48.sql","duracion_ms":894,"resultado":"Exitoso"}', '2025-12-24 22:09:13'),
	(25, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-24_23-54-15.sql | Tamaño: 0.07 MB', '2025-12-24 22:54:17'),
	(26, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2025-12-24_23-54-15.sql","tamanio_mb":0.07,"duracion_ms":1791,"tablas":13}', '2025-12-24 22:54:17'),
	(27, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-25_01-25-25.sql | Tamaño: 0.07 MB', '2025-12-25 00:25:29'),
	(28, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2025-12-25_01-25-25.sql","tamanio_mb":0.07,"duracion_ms":4550,"tablas":14,"metodo":"fallback"}', '2025-12-25 00:25:29'),
	(29, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-25_01-30-29.sql | Tamaño: 0.07 MB', '2025-12-25 00:30:30'),
	(30, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2025-12-25_01-30-29.sql","tamanio_mb":0.07,"duracion_ms":1186,"tablas":13,"metodo":"fallback"}', '2025-12-25 00:30:30'),
	(31, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-25_01-49-40.sql | Tamaño: 0.07 MB', '2025-12-25 00:49:41'),
	(32, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2025-12-25_01-49-40.sql","tamanio_mb":0.07,"duracion_ms":910,"tablas":13,"metodo":"fallback"}', '2025-12-25 00:49:41'),
	(33, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2025-12-25_02-06-16.sql | Tamaño: 0.07 MB', '2025-12-25 01:06:17'),
	(34, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2025-12-25_02-06-16.sql","tamanio_mb":0.07,"duracion_ms":1276,"tablas":13,"metodo":"fallback"}', '2025-12-25 01:06:17'),
	(35, 1, 'Restauración de Backup', '{"archivo":"dbuzzbackup_2025-12-25_16-34-40.sql","duracion_ms":2214,"resultado":"Exitoso"}', '2025-12-25 15:34:50'),
	(36, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-05_17-49-56.sql | Tamaño: 0.09 MB', '2026-01-05 16:49:58'),
	(37, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-05_17-49-56.sql","tamanio_mb":0.09,"duracion_ms":1789,"tablas":10,"metodo":"fallback"}', '2026-01-05 16:49:58'),
	(38, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-07_00-15-27.sql | Tamaño: 0.13 MB', '2026-01-06 23:15:30'),
	(39, NULL, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-07_00-15-27.sql","tamanio_mb":0.13,"duracion_ms":2285,"tablas":11,"metodo":"fallback"}', '2026-01-06 23:15:30');

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
  KEY `fecha` (`fecha`),
  CONSTRAINT `FK_logs_login_usuarios` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs_login: ~51 rows (aproximadamente)
DELETE FROM `logs_login`;
INSERT INTO `logs_login` (`id`, `usuario_id`, `accion`, `detalles`, `fecha`) VALUES
	(1, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: test@noexiste.com', '2025-11-30 19:43:12'),
	(2, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: test@noexiste.com', '2025-11-30 19:49:14'),
	(3, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: test@noexiste.com', '2025-11-30 19:49:15'),
	(4, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: test@noexiste.com', '2025-11-30 19:49:15'),
	(5, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-30 19:55:01'),
	(6, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-11-30 20:40:09'),
	(7, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-30 20:40:11'),
	(8, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-11-30 21:16:10'),
	(9, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-11-30 21:16:12'),
	(10, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-30 21:16:14'),
	(11, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-11-30 21:31:30'),
	(12, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-30 21:31:31'),
	(13, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-11-30 21:39:12'),
	(14, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-30 21:39:16'),
	(15, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-12-02 01:38:20'),
	(16, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 01:39:23'),
	(17, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 02:34:27'),
	(18, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 03:01:51'),
	(19, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@example.com', '2025-12-02 03:54:03'),
	(20, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@example.com', '2025-12-02 03:56:19'),
	(21, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@admin.com', '2025-12-02 04:00:55'),
	(22, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@admin.com', '2025-12-02 04:01:07'),
	(23, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 04:02:31'),
	(24, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 04:04:53'),
	(25, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 04:07:09'),
	(26, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-02 04:10:30'),
	(27, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-24 15:24:08'),
	(28, 36, 'Exitoso', 'Inicio de sesión de supervisor', '2025-12-24 15:34:58'),
	(29, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-24 20:45:00'),
	(30, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-24 22:07:28'),
	(31, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@admin.com', '2025-12-25 00:23:35'),
	(32, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-25 00:25:24'),
	(33, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-25 12:56:24'),
	(34, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-25 15:04:24'),
	(35, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-25 15:33:54'),
	(36, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-12-25 15:43:21'),
	(37, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-05 16:49:55'),
	(38, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-05 17:33:35'),
	(39, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-05 17:33:51'),
	(40, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-05 20:43:26'),
	(41, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-06 02:37:16'),
	(42, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-06 02:37:24'),
	(43, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-06 23:15:19'),
	(44, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-06 23:15:26'),
	(45, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-06 23:43:17'),
	(46, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-06 23:52:17'),
	(47, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 00:30:15'),
	(48, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 00:37:28'),
	(49, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 00:53:32'),
	(50, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 03:06:34'),
	(51, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 03:09:41');

-- Volcando estructura para tabla dbuzz.marcas
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.marcas: ~6 rows (aproximadamente)
DELETE FROM `marcas`;
INSERT INTO `marcas` (`id`, `nombre`, `descripcion`, `sitio_web`) VALUES
	(1, 'Pilot', 'Bolígrafos y roller de calidad', 'https://www.pilot.com'),
	(2, 'Stabilo', 'Marcadores y resaltadores', 'https://www.stabilo.com'),
	(3, 'Faber-Castell', 'Lápices y material artístico', 'https://www.faber-castell.com'),
	(4, 'Staedtler', 'Gomas y herramientas de dibujo', 'https://www.staedtler.com'),
	(5, 'Canson', 'Papeles y cartulinas para arte', 'https://www.canson.com'),
	(6, 'Tombow', 'Material para pintura y acuarela', 'https://www.tombow.com');

-- Volcando estructura para tabla dbuzz.materiales
DROP TABLE IF EXISTS `materiales`;
CREATE TABLE IF NOT EXISTS `materiales` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_material_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Materiales predefinidos para productos';

-- Volcando datos para la tabla dbuzz.materiales: ~14 rows (aproximadamente)
DELETE FROM `materiales`;
INSERT INTO `materiales` (`id`, `nombre`) VALUES
	(1, 'Plástico'),
	(2, 'Madera'),
	(3, 'Metal'),
	(4, 'Vidrio'),
	(5, 'Papel'),
	(6, 'Cartón'),
	(7, 'Tela'),
	(8, 'Cuero'),
	(9, 'Goma'),
	(10, 'Cerámica'),
	(11, 'Aluminio'),
	(12, 'Acero'),
	(13, 'PVC'),
	(14, 'Silicona');

-- Volcando estructura para tabla dbuzz.movimientos_inventario
DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `fk_mov_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_mov_ubicacion` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.movimientos_inventario: ~12 rows (aproximadamente)
DELETE FROM `movimientos_inventario`;
INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `usuario_id`, `tipo_movimiento`, `cantidad`, `justificacion`, `cargo_id`, `almacen_id`, `ubicacion_id`, `stock_anterior`, `stock_actual`, `fecha`) VALUES
	(13, 2, 100, 'entrada', 120, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 120, '2025-12-25 14:00:00'),
	(14, 3, 100, 'entrada', 60, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 60, '2025-12-25 14:01:00'),
	(15, 4, 100, 'entrada', 200, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 200, '2025-12-25 14:02:00'),
	(16, 5, 100, 'entrada', 40, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 40, '2025-12-25 14:03:00'),
	(17, 6, 100, 'entrada', 150, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 150, '2025-12-25 14:04:00'),
	(18, 7, 100, 'entrada', 300, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 300, '2025-12-25 14:05:00'),
	(19, 8, 100, 'entrada', 80, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 80, '2025-12-25 14:06:00'),
	(20, 9, 100, 'entrada', 25, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 25, '2025-12-25 14:07:00'),
	(21, 10, 100, 'entrada', 10, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 10, '2025-12-25 14:08:00'),
	(22, 11, 100, 'entrada', 50, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 50, '2025-12-25 14:09:00'),
	(23, 12, 100, 'entrada', 15, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 15, '2025-12-25 14:10:00'),
	(24, 13, 100, 'entrada', 0, 'Sin stock al momento del mock', NULL, NULL, NULL, 0, 0, '2025-12-25 14:11:00');

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

-- Volcando datos para la tabla dbuzz.password_reset_tokens: ~2 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;
INSERT INTO `password_reset_tokens` (`id`, `usuario_id`, `token`, `expiracion`, `usado`, `creado_at`) VALUES
	(1, 4, '225c2612cebf18e9a3f6ccab9c2b9fafd657b29f8acbf14f5c9806293e9863c0', '2025-12-01 00:13:38', 1, '2025-11-30 18:13:38'),
	(2, 4, 'b13ab88f4462d972cb81b66e2fd60d06470bc9a21ddd72fd288cafa595836255', '2025-12-01 01:55:10', 0, '2025-11-30 19:55:10');

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
  KEY `idx_estado_pago` (`estado_pago`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.pedidos: ~11 rows (aproximadamente)
DELETE FROM `pedidos`;
INSERT INTO `pedidos` (`id`, `cliente_id`, `referencia`, `fecha_creacion`, `total`, `estado_pago`, `estado_envio`, `direccion_envio`, `notas_envio`, `creado_por`, `actualizado_at`, `tracking_number`, `estimated_delivery_date`, `deleted_at`) VALUES
	(1, 'V-30535128', '4633556592', '2025-12-02 06:30:40', 10.00, 'pagado', 'pendiente', 'afeafe', 'sfaefd', NULL, '2025-12-24 22:08:12', 'TRK542867550', NULL, NULL),
	(2, 'V-10000001', 'REF-LBR-0002', '2025-12-25 15:00:00', 9.70, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', 'Entrega mañana', 100, '2025-12-25 15:34:22', 'TRK676862010', NULL, NULL),
	(3, 'V-10000002', 'REF-LBR-0003', '2025-12-24 19:30:00', 10.10, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(4, 'V-10000003', 'REF-LBR-0004', '2025-10-05 14:15:00', 115.00, 'pagado', 'recibido', 'C.C. Centro, Local 12', 'Pedido mayorista', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(5, 'V-10000001', 'REF-LBR-0005', '2025-10-20 13:00:00', 13.50, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(6, 'V-10000002', 'REF-LBR-0006', '2025-11-11 18:45:00', 10.80, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(7, 'V-10000001', 'REF-LBR-0007', '2025-11-25 20:10:00', 17.20, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(8, 'V-10000003', 'REF-LBR-0008', '2025-12-01 16:00:00', 30.50, 'pagado', 'pendiente', 'C.C. Centro, Local 12', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(9, 'V-10000002', 'REF-LBR-0009', '2025-12-05 12:30:00', 11.75, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(10, 'V-10000001', 'REF-LBR-0010', '2025-12-10 22:20:00', 16.00, 'pagado', 'pendiente', 'Calle Comercio 123, Caracas', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL),
	(11, 'V-10000003', 'REF-LBR-0011', '2025-12-20 13:05:00', 38.00, 'pagado', 'enviado', 'C.C. Centro, Local 12', '', 100, '2025-12-25 15:31:03', NULL, NULL, NULL);

-- Volcando estructura para tabla dbuzz.permisos_rol
DROP TABLE IF EXISTS `permisos_rol`;
CREATE TABLE IF NOT EXISTS `permisos_rol` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rol` enum('admin','supervisor','operador') NOT NULL,
  `pagina` varchar(100) NOT NULL COMMENT 'Nombre del archivo PHP permitido',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rol_pagina` (`rol`,`pagina`),
  KEY `idx_rol` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de acceso por rol a páginas del sistema';

-- Volcando datos para la tabla dbuzz.permisos_rol: ~93 rows (aproximadamente)
DELETE FROM `permisos_rol`;
INSERT INTO `permisos_rol` (`id`, `rol`, `pagina`, `creado_at`) VALUES
	(1, 'admin', 'dashboard.php', '2026-01-07 02:44:22'),
	(2, 'admin', 'pedidos.php', '2026-01-07 02:44:22'),
	(3, 'admin', 'envios.php', '2026-01-07 02:44:22'),
	(4, 'admin', 'inventario.php', '2026-01-07 02:44:22'),
	(5, 'admin', 'importacion.php', '2026-01-07 02:44:22'),
	(6, 'admin', 'admin.php', '2026-01-07 02:44:22'),
	(7, 'admin', 'about.php', '2026-01-07 02:44:22'),
	(8, 'admin', 'manual.php', '2026-01-07 02:44:22'),
	(9, 'admin', 'login.php', '2026-01-07 02:44:22'),
	(10, 'admin', 'logout.php', '2026-01-07 02:44:22'),
	(11, 'admin', 'api_sales_chart.php', '2026-01-07 02:44:22'),
	(12, 'admin', 'create_admin.php', '2026-01-07 02:44:22'),
	(13, 'admin', 'get_import_history.php', '2026-01-07 02:44:22'),
	(14, 'admin', 'product_actions.php', '2026-01-07 02:44:22'),
	(15, 'admin', 'users.php', '2026-01-07 02:44:22'),
	(16, 'admin', 'sesion.php', '2026-01-07 02:44:22'),
	(17, 'admin', 'db.php', '2026-01-07 02:44:22'),
	(18, 'admin', 'sidebar.php', '2026-01-07 02:44:22'),
	(19, 'admin', 'reports.php', '2026-01-07 02:44:22'),
	(20, 'admin', 'pedidos_actions.php', '2026-01-07 02:44:22'),
	(21, 'admin', 'search_api.php', '2026-01-07 02:44:22'),
	(22, 'admin', 'clientes.php', '2026-01-07 02:44:22'),
	(23, 'admin', 'clientes_actions.php', '2026-01-07 02:44:22'),
	(24, 'admin', 'api_sales_by_status_chart.php', '2026-01-07 02:44:22'),
	(25, 'admin', 'envios_actions.php', '2026-01-07 02:44:22'),
	(26, 'admin', 'company_settings.php', '2026-01-07 02:44:22'),
	(27, 'admin', 'company_settings_actions.php', '2026-01-07 02:44:22'),
	(28, 'admin', 'backup.php', '2026-01-07 02:44:22'),
	(29, 'admin', 'backups.php', '2026-01-07 02:44:22'),
	(30, 'admin', 'backup_status.php', '2026-01-07 02:44:22'),
	(31, 'admin', 'logs.php', '2026-01-07 02:44:22'),
	(32, 'admin', 'process_import.php', '2026-01-07 02:44:22'),
	(33, 'admin', 'settings_actions.php', '2026-01-07 02:44:22'),
	(34, 'admin', 'cargos_existencias.php', '2026-01-07 02:44:22'),
	(35, 'admin', 'almacenes.php', '2026-01-07 02:44:22'),
	(36, 'admin', 'cargos_actions.php', '2026-01-07 02:44:22'),
	(37, 'admin', 'productos_search.php', '2026-01-07 02:44:22'),
	(38, 'admin', 'catalogos_actions.php', '2026-01-07 02:44:22'),
	(39, 'admin', 'kit_actions.php', '2026-01-07 02:44:22'),
	(40, 'admin', 'almacenes_actions.php', '2026-01-07 02:44:22'),
	(41, 'admin', 'ubicaciones_actions.php', '2026-01-07 02:44:22'),
	(42, 'admin', 'proveedores.php', '2026-01-07 02:44:22'),
	(43, 'admin', 'proveedores_actions.php', '2026-01-07 02:44:22'),
	(44, 'supervisor', 'dashboard.php', '2026-01-07 02:44:23'),
	(45, 'supervisor', 'pedidos.php', '2026-01-07 02:44:23'),
	(46, 'supervisor', 'envios.php', '2026-01-07 02:44:23'),
	(47, 'supervisor', 'inventario.php', '2026-01-07 02:44:23'),
	(48, 'supervisor', 'importacion.php', '2026-01-07 02:44:23'),
	(49, 'supervisor', 'about.php', '2026-01-07 02:44:23'),
	(50, 'supervisor', 'manual.php', '2026-01-07 02:44:23'),
	(51, 'supervisor', 'login.php', '2026-01-07 02:44:23'),
	(52, 'supervisor', 'logout.php', '2026-01-07 02:44:23'),
	(53, 'supervisor', 'api_sales_chart.php', '2026-01-07 02:44:23'),
	(54, 'supervisor', 'get_import_history.php', '2026-01-07 02:44:23'),
	(55, 'supervisor', 'product_actions.php', '2026-01-07 02:44:23'),
	(56, 'supervisor', 'users.php', '2026-01-07 02:44:23'),
	(57, 'supervisor', 'sesion.php', '2026-01-07 02:44:23'),
	(58, 'supervisor', 'db.php', '2026-01-07 02:44:23'),
	(59, 'supervisor', 'sidebar.php', '2026-01-07 02:44:23'),
	(60, 'supervisor', 'reports.php', '2026-01-07 02:44:23'),
	(61, 'supervisor', 'pedidos_actions.php', '2026-01-07 02:44:23'),
	(62, 'supervisor', 'search_api.php', '2026-01-07 02:44:23'),
	(63, 'supervisor', 'clientes.php', '2026-01-07 02:44:23'),
	(64, 'supervisor', 'clientes_actions.php', '2026-01-07 02:44:23'),
	(65, 'supervisor', 'api_sales_by_status_chart.php', '2026-01-07 02:44:23'),
	(66, 'supervisor', 'envios_actions.php', '2026-01-07 02:44:23'),
	(67, 'supervisor', 'process_import.php', '2026-01-07 02:44:23'),
	(68, 'supervisor', 'cargos_existencias.php', '2026-01-07 02:44:23'),
	(69, 'supervisor', 'almacenes.php', '2026-01-07 02:44:23'),
	(70, 'supervisor', 'cargos_actions.php', '2026-01-07 02:44:23'),
	(71, 'supervisor', 'productos_search.php', '2026-01-07 02:44:23'),
	(72, 'supervisor', 'catalogos_actions.php', '2026-01-07 02:44:23'),
	(73, 'supervisor', 'kit_actions.php', '2026-01-07 02:44:23'),
	(74, 'supervisor', 'almacenes_actions.php', '2026-01-07 02:44:23'),
	(75, 'supervisor', 'ubicaciones_actions.php', '2026-01-07 02:44:23'),
	(76, 'supervisor', 'proveedores.php', '2026-01-07 02:44:23'),
	(77, 'supervisor', 'proveedores_actions.php', '2026-01-07 02:44:23'),
	(78, 'operador', 'dashboard.php', '2026-01-07 02:44:23'),
	(79, 'operador', 'pedidos.php', '2026-01-07 02:44:23'),
	(80, 'operador', 'envios.php', '2026-01-07 02:44:23'),
	(81, 'operador', 'about.php', '2026-01-07 02:44:23'),
	(82, 'operador', 'manual.php', '2026-01-07 02:44:23'),
	(83, 'operador', 'login.php', '2026-01-07 02:44:23'),
	(84, 'operador', 'logout.php', '2026-01-07 02:44:23'),
	(85, 'operador', 'api_sales_chart.php', '2026-01-07 02:44:23'),
	(86, 'operador', 'sesion.php', '2026-01-07 02:44:23'),
	(87, 'operador', 'db.php', '2026-01-07 02:44:23'),
	(88, 'operador', 'sidebar.php', '2026-01-07 02:44:23'),
	(89, 'operador', 'pedidos_actions.php', '2026-01-07 02:44:23'),
	(90, 'operador', 'search_api.php', '2026-01-07 02:44:23'),
	(91, 'operador', 'api_sales_by_status_chart.php', '2026-01-07 02:44:23'),
	(92, 'operador', 'envios_actions.php', '2026-01-07 02:44:23'),
	(93, 'operador', 'product_actions.php', '2026-01-07 02:44:23');

-- Volcando estructura para tabla dbuzz.productos
DROP TABLE IF EXISTS `productos`;
CREATE TABLE IF NOT EXISTS `productos` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `sku` (`sku`),
  KEY `nombre` (`nombre`),
  KEY `sku_2` (`sku`),
  KEY `fk_productos_categoria` (`categoria`),
  KEY `fk_productos_marca` (`marca_id`),
  KEY `fk_productos_proveedor` (`proveedor_id`),
  KEY `idx_productos_deleted_at` (`deleted_at`),
  KEY `idx_producto_padre` (`producto_padre_id`),
  KEY `idx_estado` (`estado`),
  CONSTRAINT `fk_producto_padre` FOREIGN KEY (`producto_padre_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.productos: ~13 rows (aproximadamente)
DELETE FROM `productos`;
INSERT INTO `productos` (`id`, `sku`, `nombre`, `descripcion`, `precio`, `costo_promedio`, `costo_ultimo`, `stock`, `min_stock`, `estado`, `tipo_producto`, `maneja_stock`, `creado_at`, `actualizado_at`, `categoria`, `marca_id`, `proveedor_id`, `modelo`, `color`, `tamanio`, `medidas`, `peso_kg`, `material`, `unidad_venta`, `cantidad_presentacion`, `deleted_at`, `producto_padre_id`, `es_kit_virtual`, `variante_atributo`) VALUES
	(1, '325324324234', 'blah', 'afesfaf', 10.00, 0.0000, 0.0000, 4, 10, 'activo', 'fisico', 1, '2025-12-02 06:29:28', '2026-01-05 21:05:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, '2026-01-05 21:05:47', NULL, 0, NULL),
	(2, 'LBR-0001', 'Cuaderno universitario 100 hojas', 'Cuaderno raya 100 hojas 21x27 cm', 2.50, 0.0000, 0.0000, 120, 20, 'activo', 'fisico', 1, '2025-12-25 14:00:00', NULL, 1, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(3, 'LBR-0002', 'Cuaderno de dibujo A4 40 hojas', 'Papel para dibujo 120 g/m2, 40 hojas', 5.00, 0.0000, 0.0000, 60, 10, 'activo', 'fisico', 1, '2025-12-25 14:01:00', NULL, 4, 5, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(4, 'LBR-0003', 'Bolígrafo Pilot G2 negro', 'Bolígrafo punta 0.7 mm, tinta gel', 1.20, 0.0000, 0.0000, 200, 30, 'activo', 'fisico', 1, '2025-12-25 14:02:00', NULL, 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(5, 'LBR-0004', 'Set Marcadores Stabilo 6 colores', 'Marcadores punta fina, set x6', 8.50, 0.0000, 0.0000, 40, 10, 'activo', 'fisico', 1, '2025-12-25 14:03:00', NULL, 4, 2, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(6, 'LBR-0005', 'Lápiz HB Faber-Castell (12)', 'Pack de 12 lápices HB', 3.75, 0.0000, 0.0000, 150, 20, 'activo', 'fisico', 1, '2025-12-25 14:04:00', NULL, 2, 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(7, 'LBR-0006', 'Goma de borrar Staedtler', 'Goma blanda sin PVC', 0.80, 0.0000, 0.0000, 300, 50, 'activo', 'fisico', 1, '2025-12-25 14:05:00', NULL, 1, 4, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(8, 'LBR-0007', 'Pegamento en barra 36g', 'Pegamento sólido sin ácido', 1.10, 0.0000, 0.0000, 80, 20, 'activo', 'fisico', 1, '2025-12-25 14:06:00', NULL, 5, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(9, 'LBR-0008', 'Cartulina Canson A4 (pack 50)', 'Cartulina para arte, 180 g/m2, pack 50 hojas', 15.00, 0.0000, 0.0000, 25, 10, 'activo', 'fisico', 1, '2025-12-25 14:07:00', NULL, 4, 5, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(10, 'LBR-0009', 'Archivador A4 plástico', 'Archivador plástico A4 con anillas', 4.25, 0.0000, 0.0000, 10, 20, 'stock_bajo', 'fisico', 1, '2025-12-25 14:08:00', NULL, 6, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(11, 'LBR-0010', 'Resma papel bond A4 80gr (500)', 'Resma de papel bond A4 80 g/m2, 500 hojas', 6.50, 0.0000, 0.0000, 50, 10, 'activo', 'fisico', 1, '2025-12-25 14:09:00', NULL, 3, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(12, 'LBR-0011', 'Set acuarelas Tombow 12 colores', 'Set de acuarelas en pastilla, 12 colores', 22.00, 0.0000, 0.0000, 15, 5, 'activo', 'fisico', 1, '2025-12-25 14:10:00', NULL, 4, 6, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(13, 'LBR-0012', 'Marcador permanente negro', 'Marcador indeleble punta media', 2.00, 0.0000, 0.0000, 0, 10, 'agotado', 'fisico', 1, '2025-12-25 14:11:00', NULL, 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL);

-- Volcando estructura para tabla dbuzz.producto_componentes
DROP TABLE IF EXISTS `producto_componentes`;
CREATE TABLE IF NOT EXISTS `producto_componentes` (
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
  CONSTRAINT `fk_pc_componente` FOREIGN KEY (`componente_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_pc_kit` FOREIGN KEY (`kit_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Componentes de productos tipo kit';

-- Volcando datos para la tabla dbuzz.producto_componentes: ~0 rows (aproximadamente)
DELETE FROM `producto_componentes`;

-- Volcando estructura para tabla dbuzz.proveedores
DROP TABLE IF EXISTS `proveedores`;
CREATE TABLE IF NOT EXISTS `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `nombre` (`nombre`),
  KEY `idx_deleted_at` (`deleted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.proveedores: ~3 rows (aproximadamente)
DELETE FROM `proveedores`;
INSERT INTO `proveedores` (`id`, `nombre`, `rif`, `contacto_nombre`, `email`, `telefono`, `direccion`, `notas`, `creado_at`, `actualizado_at`, `deleted_at`) VALUES
	(1, 'Distribuidora Papel S.A.', NULL, 'María Pérez', 'ventas@distribuidora-papel.com', '+58 212 555 0101', 'Av. Comercio, Caracas', NULL, '2025-12-25 15:31:03', NULL, NULL),
	(2, 'Importadora Útiles S.R.L.', NULL, 'Carlos Rojas', 'contacto@importutiles.com', '+58 241 555 0202', 'Zona Industrial, Valencia', NULL, '2025-12-25 15:31:03', NULL, NULL),
	(3, 'ArtículosCreativos C.A.', NULL, 'Ana Gómez', 'info@articuloscreativos.com', '+58 261 555 0303', 'C.C. Arte, Maracay', NULL, '2025-12-25 15:31:03', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.sesiones_api: ~36 rows (aproximadamente)
DELETE FROM `sesiones_api`;
INSERT INTO `sesiones_api` (`id`, `usuario_id`, `token`, `expiracion`, `creado_en`) VALUES
	(24, 1, '2584334646b54b429e0cb37b0a117cc1c01a1286563e5d6a6ddaa2631344080c', '2025-11-29 15:15:36', '2025-11-29 02:15:36'),
	(25, 1, 'a96a5e9527b24c5b34bdb87096c97d5363c3699e6c2c408129358becc8da0ce8', '2025-11-29 15:19:37', '2025-11-29 02:19:37'),
	(26, 1, '36c9bb7c50be0ebaf493efb91be1e3839510be8d82f59745429f9a9a5c75c01b', '2025-12-01 08:29:10', '2025-11-30 19:29:10'),
	(27, 1, '9fa8b502ffb5bec33e2a07741d0d8ef60633a455e93a9df62ed3e8615e673104', '2025-12-01 08:55:01', '2025-11-30 19:55:01'),
	(28, 1, '55e593fcabeead3d74241170aca64e63fc1fe8cb4b0f73e04382a8b4d8269e6c', '2025-12-01 09:40:11', '2025-11-30 20:40:11'),
	(29, 1, 'df6572b52cb45dbf07511816048b4195f971400b1623b7f3ce00afbd992b521f', '2025-12-01 10:16:14', '2025-11-30 21:16:14'),
	(30, 1, '41b5661a6cee2ec476a24d5b73a5a7454f8f3a8f980195819b162163914798af', '2025-12-01 10:31:31', '2025-11-30 21:31:31'),
	(31, 1, '4e632767a6a363498e57886270f432bba108f81a9821647312c4c8a383bbef95', '2025-12-01 10:39:16', '2025-11-30 21:39:16'),
	(32, 1, '0db331ff41c96615efa9ebb86de8528beed6cea8ee58dd3776965794827cf8cc', '2025-12-02 14:39:23', '2025-12-02 01:39:23'),
	(33, 1, '4533fcf5e2577a372329f735bb373a06425398c6b8804356c8cd3795ab5261fb', '2025-12-02 15:34:27', '2025-12-02 02:34:27'),
	(34, 1, '17278a17b2939bd41cfba3b6e3d24ff9e75d624cee96baf6fe055617646903d8', '2025-12-02 16:01:51', '2025-12-02 03:01:51'),
	(35, 1, '0e2e4f2d2bad1e3f77048757b916f90d47877bad8348a3740515cb8c3a8e8ba0', '2025-12-02 17:02:31', '2025-12-02 04:02:31'),
	(36, 1, '653bf563210c405b6bf8c4507b5ce6b66ace6e9bbe1b2fb21b2f259af76dcdc9', '2025-12-02 17:04:53', '2025-12-02 04:04:53'),
	(37, 1, '37630fef1296bac2118d16d898cc9fed09c78e99a45d1725d6fabd53890c7fbb', '2025-12-02 17:07:09', '2025-12-02 04:07:09'),
	(38, 1, '5c231789d443949d86e534e0099e99996480fa5a0bb60723581ae55693c6568e', '2025-12-02 17:10:30', '2025-12-02 04:10:30'),
	(39, 1, '4c4f17cf49152c0015ae8bca4a0601ed96960f9846cac1be111ee3e4438c6f4b', '2025-12-25 04:24:08', '2025-12-24 15:24:08'),
	(40, 36, '0f27a12af252cff6c5b522a6eec580ac02b1e8902e31c7390e65f37dc842cbae', '2025-12-25 04:34:58', '2025-12-24 15:34:58'),
	(41, 1, '92d66929359557e770d3e16351214832bb9f9d6d254c29413db361a272180d87', '2025-12-25 09:45:00', '2025-12-24 20:45:00'),
	(42, 1, '34b4c7eaa7d90a550e90b4be5f6862043cbea1b542ca0e760bdd0d50b7a2f633', '2025-12-25 11:07:28', '2025-12-24 22:07:28'),
	(43, 1, 'dc0c4d5145af56435b5bfd4637fcb1aa196f8b2dd8e7bd087d8d12f4244df1b7', '2025-12-25 13:25:24', '2025-12-25 00:25:24'),
	(44, 1, '9fda354b6010c9581164c04e56715afcd7045f9bae98e6df7d565fa6ef48685a', '2025-12-26 01:56:24', '2025-12-25 12:56:24'),
	(45, 1, '98e6469c7274168207b52d7de22335a66418a30e1692b47e7a3d44b44044b64c', '2025-12-26 04:04:24', '2025-12-25 15:04:24'),
	(46, 1, 'ac9fadc2fb8592ebad64390f580d6c481d0423665c70118b2d859857cd8659dd', '2025-12-26 04:33:54', '2025-12-25 15:33:54'),
	(47, 1, '83606cbcd9b21bf70adb60e0c5f02f8d3178f191304fef543370ff527d8af75f', '2025-12-26 04:43:21', '2025-12-25 15:43:21'),
	(48, 1, 'e2f6f1bde2f7a16327b7c99716bc2c49f2ffb9f739921180ae16b435682217a1', '2026-01-06 05:49:55', '2026-01-05 16:49:55'),
	(49, 1, '7ec2228967d1b797d6b6d30568b946a2188418232e4b9a0a4fdd276584e1bf11', '2026-01-06 06:33:51', '2026-01-05 17:33:51'),
	(50, 1, '204257285129225fe1906c0e1d33b8be4b76da2ca577be5e6399828db9f87cf8', '2026-01-06 09:43:26', '2026-01-05 20:43:26'),
	(51, 1, '89e02598eecf4ebcd97db94a5bfe059c6a7559467b6c57ca531318d0e49824e2', '2026-01-06 15:37:24', '2026-01-06 02:37:24'),
	(52, 1, '0cf30bc08eb8f0ac7ec7329c8ff535830bc26b41433d7d3ebc26b1f3887a397a', '2026-01-07 12:15:26', '2026-01-06 23:15:26'),
	(53, 1, 'f027332c19532a595a3773ec22dbd8d7d75d39c375d600cf3246a15e6832f295', '2026-01-07 12:43:17', '2026-01-06 23:43:17'),
	(54, 1, 'f8a228ca9344d9a46a8336f4f8d3152e9961ac6db2f59d29514df848f977386d', '2026-01-07 12:52:17', '2026-01-06 23:52:17'),
	(55, 1, 'b1d670b84c1cbe75e922bf009798211f29ca83411207300e6c75b1fd7cacb7b2', '2026-01-07 13:30:15', '2026-01-07 00:30:15'),
	(56, 1, 'eb2b9052a6ac09a28e242566acb7b9e1de7f74ab56552b415f953fa609d8696c', '2026-01-07 13:37:28', '2026-01-07 00:37:28'),
	(57, 1, '12a2a6b854d1ea73807cdb15f6486826547246fe8d11cb76f31b57fdfbe8b01c', '2026-01-07 13:53:32', '2026-01-07 00:53:32'),
	(58, 1, 'b280ffc3828f7560a6ee0ee1c6a7859be9ad9dfabb8aba5f9b95e0b6475d9a86', '2026-01-07 16:06:34', '2026-01-07 03:06:34'),
	(59, 1, '4f590296ba314fce3b3d27b4ee248c499b7a4c8da3647eeeaa6a79b72041a3bf', '2026-01-07 16:09:41', '2026-01-07 03:09:41');

-- Volcando estructura para tabla dbuzz.stock_ubicacion
DROP TABLE IF EXISTS `stock_ubicacion`;
CREATE TABLE IF NOT EXISTS `stock_ubicacion` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `fk_stock_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_ubicacion` FOREIGN KEY (`ubicacion_id`) REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stock de productos por ubicación';

-- Volcando datos para la tabla dbuzz.stock_ubicacion: ~12 rows (aproximadamente)
DELETE FROM `stock_ubicacion`;
INSERT INTO `stock_ubicacion` (`id`, `producto_id`, `almacen_id`, `ubicacion_id`, `cantidad`, `stock_minimo`, `stock_maximo`, `actualizado_at`) VALUES
	(1, 1, 1, NULL, 4, 0, NULL, '2026-01-05 18:09:00'),
	(2, 2, 1, NULL, 120, 0, NULL, '2026-01-05 18:09:00'),
	(3, 3, 1, NULL, 60, 0, NULL, '2026-01-05 18:09:00'),
	(4, 4, 1, NULL, 200, 0, NULL, '2026-01-05 18:09:00'),
	(5, 5, 1, NULL, 40, 0, NULL, '2026-01-05 18:09:00'),
	(6, 6, 1, NULL, 150, 0, NULL, '2026-01-05 18:09:00'),
	(7, 7, 1, NULL, 300, 0, NULL, '2026-01-05 18:09:00'),
	(8, 8, 1, NULL, 80, 0, NULL, '2026-01-05 18:09:00'),
	(9, 9, 1, NULL, 25, 0, NULL, '2026-01-05 18:09:00'),
	(10, 10, 1, NULL, 10, 0, NULL, '2026-01-05 18:09:00'),
	(11, 11, 1, NULL, 50, 0, NULL, '2026-01-05 18:09:00'),
	(12, 12, 1, NULL, 15, 0, NULL, '2026-01-05 18:09:00');

-- Volcando estructura para tabla dbuzz.tasas_cambio
DROP TABLE IF EXISTS `tasas_cambio`;
CREATE TABLE IF NOT EXISTS `tasas_cambio` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tasa` decimal(18,4) NOT NULL COMMENT 'Valor de 1 USD en moneda local (VES)',
  `fuente` varchar(50) DEFAULT 'manual' COMMENT 'Fuente: manual, bcv, paralelo, etc.',
  `fecha` date NOT NULL COMMENT 'Fecha para la cual aplica esta tasa',
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  `usuario_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario que registró la tasa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_fecha` (`fecha`),
  KEY `idx_fecha` (`fecha`),
  KEY `FK_tasa_usuario` (`usuario_id`),
  CONSTRAINT `FK_tasa_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tasa de cambio diaria USD para referencia de precios';

-- Volcando datos para la tabla dbuzz.tasas_cambio: ~0 rows (aproximadamente)
DELETE FROM `tasas_cambio`;

-- Volcando estructura para tabla dbuzz.ubicaciones
DROP TABLE IF EXISTS `ubicaciones`;
CREATE TABLE IF NOT EXISTS `ubicaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `fk_ubicacion_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ubicaciones físicas dentro de cada almacén';

-- Volcando datos para la tabla dbuzz.ubicaciones: ~0 rows (aproximadamente)
DELETE FROM `ubicaciones`;

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
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.usuarios: ~6 rows (aproximadamente)
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `creado_at`, `actualizado_at`, `intentos_fallidos`) VALUES
	(1, 'Givi', 'admin@buzz.com', '$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G', 'admin', 'activo', '2025-08-09 17:43:16', '2026-01-06 23:15:26', 0),
	(4, 'Gisvel', 'gisvelaray@gmail.com', '$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G', 'operador', 'activo', '2025-11-30 18:13:22', '2025-11-30 18:13:28', 0),
	(36, 'GiviSupervisor', 'supervisor@buzz.com', '$2y$10$m389cp0CKSWLAODHmzL1Z.zyB8XnGt8BCDFmtoqI781PZcD2EjZna', 'supervisor', 'activo', '2025-08-14 01:32:53', NULL, 0),
	(37, 'GiviOperador', 'operador@buzz.com', '$2y$10$7ofF41nU06Ct9hl90TdzguI8eSxA90RCq4K/i7yWSpic01oTiLCLC', 'operador', 'activo', '2025-08-14 01:33:00', NULL, 0),
	(63, 'Juan Luis Chaurant', 'juanluischaurant@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'activo', '2025-11-30 21:18:35', NULL, 0),
	(100, 'Operador Libreria', 'operador.libreria@example.com', '$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G', 'operador', 'activo', '2025-12-01 12:00:00', NULL, 0);

-- Volcando estructura para vista dbuzz.v_almacenes_resumen
DROP VIEW IF EXISTS `v_almacenes_resumen`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_almacenes_resumen` (
	`id` INT(10) UNSIGNED NOT NULL,
	`codigo` VARCHAR(1) NOT NULL COMMENT 'Ej: ALM-001, BOD-CEN, TDA-01' COLLATE 'utf8mb4_unicode_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`tipo` ENUM('almacen','bodega','tienda','deposito','otro') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`es_principal` TINYINT(1) NULL COMMENT 'Almacén principal/default',
	`activo` TINYINT(1) NULL,
	`direccion` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`telefono` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`responsable_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`total_ubicaciones` BIGINT(21) NULL,
	`productos_en_stock` BIGINT(21) NULL,
	`unidades_totales` DECIMAL(32,0) NULL,
	`creado_at` TIMESTAMP NOT NULL
);

-- Volcando estructura para vista dbuzz.v_cargos_detalle_completo
DROP VIEW IF EXISTS `v_cargos_detalle_completo`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_cargos_detalle_completo` (
	`cargo_id` INT(10) UNSIGNED NOT NULL,
	`cargo_codigo` VARCHAR(1) NOT NULL COMMENT 'Formato: CGO-YYYY-NNNN' COLLATE 'utf8mb4_unicode_ci',
	`cargo_fecha` DATE NOT NULL,
	`cargo_estado` ENUM('borrador','confirmado','anulado') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`detalle_id` INT(10) UNSIGNED NOT NULL,
	`producto_id` INT(10) UNSIGNED NOT NULL,
	`producto_sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`producto_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`cantidad` INT(10) UNSIGNED NOT NULL,
	`costo_unitario` DECIMAL(12,2) NOT NULL,
	`subtotal` DECIMAL(14,2) NULL,
	`detalle_notas` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci'
);

-- Volcando estructura para vista dbuzz.v_cargos_resumen
DROP VIEW IF EXISTS `v_cargos_resumen`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_cargos_resumen` (
	`id` INT(10) UNSIGNED NOT NULL,
	`codigo` VARCHAR(1) NOT NULL COMMENT 'Formato: CGO-YYYY-NNNN' COLLATE 'utf8mb4_unicode_ci',
	`fecha` DATE NOT NULL,
	`concepto` ENUM('compra','donacion','ajuste_positivo','devolucion_cliente','inventario_inicial','otro') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`concepto_texto` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`proveedor_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`referencia_externa` VARCHAR(1) NULL COMMENT 'Nro. Factura, Guía, etc.' COLLATE 'utf8mb4_unicode_ci',
	`total_productos` INT(10) UNSIGNED NULL COMMENT 'Cantidad de líneas/productos distintos',
	`total_unidades` INT(10) UNSIGNED NULL COMMENT 'Suma de todas las cantidades',
	`valor_total` DECIMAL(14,2) NULL COMMENT 'Suma de subtotales',
	`estado` ENUM('borrador','confirmado','anulado') NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`estado_texto` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`registrado_por_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`creado_at` TIMESTAMP NOT NULL,
	`confirmado_por_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`fecha_confirmacion` TIMESTAMP NULL,
	`anulado_por_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`fecha_anulacion` TIMESTAMP NULL,
	`motivo_anulacion` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci'
);

-- Volcando estructura para vista dbuzz.v_inventario
DROP VIEW IF EXISTS `v_inventario`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_inventario` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`precio` DECIMAL(12,2) NOT NULL,
	`costo_promedio` DECIMAL(12,4) NULL,
	`costo_ultimo` DECIMAL(12,4) NULL,
	`stock` INT(11) NOT NULL,
	`min_stock` INT(11) NOT NULL,
	`estado` ENUM('activo','stock_bajo','agotado') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`categoria_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`marca_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`proveedor_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`modelo` VARCHAR(1) NULL COMMENT 'Modelo o línea del producto' COLLATE 'utf8mb4_uca1400_ai_ci',
	`color` VARCHAR(1) NULL COMMENT 'Color principal del producto' COLLATE 'utf8mb4_uca1400_ai_ci',
	`tamanio` VARCHAR(1) NULL COMMENT 'Tamaño, talla o formato' COLLATE 'utf8mb4_uca1400_ai_ci',
	`medidas` VARCHAR(1) NULL COMMENT 'Dimensiones físicas del producto' COLLATE 'utf8mb4_uca1400_ai_ci',
	`peso_kg` DECIMAL(10,3) NULL COMMENT 'Peso en kilogramos',
	`material` VARCHAR(1) NULL COMMENT 'Material principal del producto' COLLATE 'utf8mb4_uca1400_ai_ci',
	`unidad_venta` ENUM('UND','KG','LT','MT','M2','PACK','CAJA','PAR') NULL COMMENT 'Unidad de medida para venta' COLLATE 'utf8mb4_uca1400_ai_ci',
	`cantidad_presentacion` INT(10) UNSIGNED NULL COMMENT 'Cantidad de unidades por presentación (si es pack/caja)',
	`creado_at` TIMESTAMP NOT NULL,
	`actualizado_at` TIMESTAMP NULL,
	`deleted_at` TIMESTAMP NULL
);

-- Volcando estructura para vista dbuzz.v_kit_componentes
DROP VIEW IF EXISTS `v_kit_componentes`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_kit_componentes` (
	`id` INT(10) UNSIGNED NOT NULL,
	`kit_id` INT(10) UNSIGNED NOT NULL COMMENT 'Producto tipo kit',
	`kit_sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`kit_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`componente_id` INT(10) UNSIGNED NOT NULL COMMENT 'Producto/kit componente',
	`componente_sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`componente_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`componente_tipo` ENUM('fisico','servicio','kit') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`cantidad` DECIMAL(10,3) NOT NULL COMMENT 'Cantidad del componente por kit',
	`componente_precio` DECIMAL(12,2) NOT NULL,
	`componente_stock` INT(11) NULL,
	`subtotal` DECIMAL(22,5) NOT NULL,
	`orden` INT(11) NULL COMMENT 'Orden de visualización'
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

-- Volcando estructura para vista dbuzz.v_productos_completo
DROP VIEW IF EXISTS `v_productos_completo`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_productos_completo` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`descripcion` TEXT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`precio` DECIMAL(12,2) NOT NULL,
	`tipo_producto` ENUM('fisico','servicio','kit') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`maneja_stock` TINYINT(1) NULL,
	`es_kit_virtual` TINYINT(1) NULL COMMENT 'Si true, descuenta componentes; si false, tiene stock propio',
	`variante_atributo` VARCHAR(1) NULL COMMENT 'Ej: Talla M, Color Rojo' COLLATE 'utf8mb4_uca1400_ai_ci',
	`producto_padre_id` INT(10) UNSIGNED NULL COMMENT 'Para variantes: ID del producto padre',
	`producto_padre_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`producto_padre_sku` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`stock_efectivo` INT(11) NULL,
	`stock_fisico` INT(11) NOT NULL,
	`min_stock` INT(11) NOT NULL,
	`estado` ENUM('activo','stock_bajo','agotado') NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`marca_id` INT(11) NULL,
	`marca_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`categoria` INT(11) NULL,
	`categoria_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`total_variantes` BIGINT(21) NULL,
	`total_componentes` BIGINT(21) NULL,
	`creado_at` TIMESTAMP NOT NULL,
	`deleted_at` TIMESTAMP NULL
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

-- Volcando estructura para vista dbuzz.v_stock_detallado
DROP VIEW IF EXISTS `v_stock_detallado`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_stock_detallado` (
	`id` INT(10) UNSIGNED NOT NULL,
	`almacen_id` INT(10) UNSIGNED NOT NULL,
	`almacen_codigo` VARCHAR(1) NOT NULL COMMENT 'Ej: ALM-001, BOD-CEN, TDA-01' COLLATE 'utf8mb4_unicode_ci',
	`almacen_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`ubicacion_id` INT(10) UNSIGNED NULL,
	`ubicacion_codigo` VARCHAR(1) NULL COMMENT 'Ej: A-01-E1-N2 (Pasillo-Estante-Nivel)' COLLATE 'utf8mb4_unicode_ci',
	`ubicacion_full` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`producto_id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`producto_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`cantidad` INT(11) NOT NULL,
	`stock_minimo` INT(11) NULL COMMENT 'Stock mínimo para esta ubicación',
	`stock_maximo` INT(11) NULL COMMENT 'Capacidad máxima',
	`estado_stock` VARCHAR(1) NULL COLLATE 'utf8mb4_uca1400_ai_ci'
);

-- Volcando estructura para vista dbuzz.v_stock_por_almacen
DROP VIEW IF EXISTS `v_stock_por_almacen`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_stock_por_almacen` (
	`almacen_id` INT(10) UNSIGNED NOT NULL,
	`almacen_codigo` VARCHAR(1) NOT NULL COMMENT 'Ej: ALM-001, BOD-CEN, TDA-01' COLLATE 'utf8mb4_unicode_ci',
	`almacen_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`producto_id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`producto_nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`stock_almacen` DECIMAL(32,0) NULL,
	`num_ubicaciones` BIGINT(21) NOT NULL
);

-- Volcando estructura para vista dbuzz.v_tasa_hoy
DROP VIEW IF EXISTS `v_tasa_hoy`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `v_tasa_hoy` (
	`tasa_actual` DECIMAL(18,4) NULL,
	`fecha_tasa` DATE NULL,
	`fuente` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci'
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

-- Volcando estructura para disparador dbuzz.trg_auto_maneja_stock
DROP TRIGGER IF EXISTS `trg_auto_maneja_stock`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_auto_maneja_stock
BEFORE INSERT ON productos
FOR EACH ROW
BEGIN
    IF NEW.tipo_producto = 'servicio' THEN
        SET NEW.maneja_stock = FALSE;
        SET NEW.stock = 0;
    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN
        SET NEW.maneja_stock = FALSE;
        -- Stock se calcula dinámicamente
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador dbuzz.trg_auto_maneja_stock_update
DROP TRIGGER IF EXISTS `trg_auto_maneja_stock_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_auto_maneja_stock_update
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    IF NEW.tipo_producto = 'servicio' THEN
        SET NEW.maneja_stock = FALSE;
        SET NEW.stock = 0;
    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN
        SET NEW.maneja_stock = FALSE;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador dbuzz.trg_cargo_anulado
DROP TRIGGER IF EXISTS `trg_cargo_anulado`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_cargo_anulado`
AFTER UPDATE ON `cargos_existencias`
FOR EACH ROW
BEGIN
    -- Solo cuando cambia de 'confirmado' a 'anulado'
    IF NEW.estado = 'anulado' AND OLD.estado = 'confirmado' THEN

        -- Revertir stock de cada producto
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = GREATEST(0, p.stock - d.cantidad),
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        -- Registrar movimientos de inventario (reversión)
        INSERT INTO movimientos_inventario
            (producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
            d.producto_id,
            NEW.anulado_por,
            'ajuste_negativo',
            d.cantidad,
            CONCAT('ANULACIÓN Cargo ', NEW.codigo,
                   CASE WHEN NEW.motivo_anulacion IS NOT NULL
                        THEN CONCAT(' - ', NEW.motivo_anulacion)
                        ELSE '' END),
            (SELECT stock + d.cantidad FROM productos WHERE id = d.producto_id),
            (SELECT stock FROM productos WHERE id = d.producto_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Volcando estructura para disparador dbuzz.trg_cargo_confirmado
DROP TRIGGER IF EXISTS `trg_cargo_confirmado`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_cargo_confirmado`
AFTER UPDATE ON `cargos_existencias`
FOR EACH ROW
BEGIN
    -- Solo cuando cambia de 'borrador' a 'confirmado'
    IF NEW.estado = 'confirmado' AND OLD.estado = 'borrador' THEN

        -- Actualizar stock de cada producto en el cargo
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = p.stock + d.cantidad,
            p.costo_ultimo = d.costo_unitario,
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        -- Registrar movimientos de inventario para cada producto
        INSERT INTO movimientos_inventario
            (producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
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
            (SELECT stock - d.cantidad FROM productos WHERE id = d.producto_id),
            (SELECT stock FROM productos WHERE id = d.producto_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

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

-- Volcando estructura para disparador dbuzz.trg_validar_componente_kit
DROP TRIGGER IF EXISTS `trg_validar_componente_kit`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_validar_componente_kit
BEFORE INSERT ON producto_componentes
FOR EACH ROW
BEGIN
    DECLARE v_tipo VARCHAR(20);

    -- Verificar que el kit sea realmente un kit
    SELECT tipo_producto INTO v_tipo FROM productos WHERE id = NEW.kit_id;
    IF v_tipo != 'kit' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El producto padre debe ser de tipo kit';
    END IF;

    -- Evitar que un kit sea componente de sí mismo
    IF NEW.kit_id = NEW.componente_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un kit no puede ser componente de sí mismo';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_almacenes_resumen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_almacenes_resumen` AS SELECT
    a.id,
    a.codigo,
    a.nombre,
    a.tipo,
    a.es_principal,
    a.activo,
    a.direccion,
    a.telefono,
    u.nombre AS responsable_nombre,
    (SELECT COUNT(*) FROM ubicaciones WHERE almacen_id = a.id AND activo = 1) AS total_ubicaciones,
    (SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE almacen_id = a.id AND cantidad > 0) AS productos_en_stock,
    (SELECT COALESCE(SUM(cantidad), 0) FROM stock_ubicacion WHERE almacen_id = a.id) AS unidades_totales,
    a.creado_at
FROM almacenes a
LEFT JOIN usuarios u ON a.responsable_id = u.id
ORDER BY a.es_principal DESC, a.nombre 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_cargos_detalle_completo`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_cargos_detalle_completo` AS SELECT
    c.id AS cargo_id,
    c.codigo AS cargo_codigo,
    c.fecha AS cargo_fecha,
    c.estado AS cargo_estado,
    d.id AS detalle_id,
    d.producto_id,
    pr.sku AS producto_sku,
    pr.nombre AS producto_nombre,
    d.cantidad,
    d.costo_unitario,
    d.subtotal,
    d.notas AS detalle_notas
FROM cargos_existencias c
INNER JOIN detalle_cargo_existencias d ON c.id = d.cargo_id
INNER JOIN productos pr ON d.producto_id = pr.id
ORDER BY c.id DESC, d.id ASC 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_cargos_resumen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_cargos_resumen` AS SELECT
    c.id,
    c.codigo,
    c.fecha,
    c.concepto,
    CASE c.concepto
        WHEN 'compra' THEN 'Compra'
        WHEN 'donacion' THEN 'Donación'
        WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
        WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
        WHEN 'inventario_inicial' THEN 'Inventario Inicial'
        WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
    END AS concepto_texto,
    p.nombre AS proveedor_nombre,
    c.referencia_externa,
    c.total_productos,
    c.total_unidades,
    c.valor_total,
    c.estado,
    CASE c.estado
        WHEN 'borrador' THEN 'Borrador'
        WHEN 'confirmado' THEN 'Confirmado'
        WHEN 'anulado' THEN 'Anulado'
    END AS estado_texto,
    u.nombre AS registrado_por_nombre,
    c.creado_at,
    uc.nombre AS confirmado_por_nombre,
    c.fecha_confirmacion,
    ua.nombre AS anulado_por_nombre,
    c.fecha_anulacion,
    c.motivo_anulacion
FROM cargos_existencias c
LEFT JOIN proveedores p ON c.proveedor_id = p.id
LEFT JOIN usuarios u ON c.registrado_por = u.id
LEFT JOIN usuarios uc ON c.confirmado_por = uc.id
LEFT JOIN usuarios ua ON c.anulado_por = ua.id
ORDER BY c.creado_at DESC 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_inventario` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.costo_promedio,
    p.costo_ultimo,
    p.stock,
    p.min_stock,
    p.estado,
    c.nombre AS categoria_nombre,
    m.nombre AS marca_nombre,
    pr.nombre AS proveedor_nombre,
    p.modelo,
    p.color,
    p.tamanio,
    p.medidas,
    p.peso_kg,
    p.material,
    p.unidad_venta,
    p.cantidad_presentacion,
    p.creado_at,
    p.actualizado_at,
    p.deleted_at
FROM productos p
LEFT JOIN categorias c ON p.categoria = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
WHERE p.deleted_at IS NULL 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_kit_componentes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_kit_componentes` AS SELECT
    pc.id,
    pc.kit_id,
    pk.sku AS kit_sku,
    pk.nombre AS kit_nombre,
    pc.componente_id,
    p.sku AS componente_sku,
    p.nombre AS componente_nombre,
    p.tipo_producto AS componente_tipo,
    pc.cantidad,
    p.precio AS componente_precio,
    CASE
        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual THEN fn_stock_kit_virtual(p.id)
        WHEN p.tipo_producto = 'servicio' THEN 999999
        ELSE p.stock
    END AS componente_stock,
    pc.cantidad * p.precio AS subtotal,
    pc.orden
FROM producto_componentes pc
INNER JOIN productos p ON p.id = pc.componente_id
INNER JOIN productos pk ON pk.id = pc.kit_id
ORDER BY pc.kit_id, pc.orden, pc.id 
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
DROP TABLE IF EXISTS `v_productos_completo`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_productos_completo` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.tipo_producto,
    p.maneja_stock,
    p.es_kit_virtual,
    p.variante_atributo,
    p.producto_padre_id,
    pp.nombre AS producto_padre_nombre,
    pp.sku AS producto_padre_sku,
    CASE
        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual THEN fn_stock_kit_virtual(p.id)
        WHEN p.tipo_producto = 'servicio' THEN NULL
        ELSE p.stock
    END AS stock_efectivo,
    p.stock AS stock_fisico,
    p.min_stock,
    p.estado,
    p.marca_id,
    m.nombre AS marca_nombre,
    p.categoria,
    c.nombre AS categoria_nombre,
    (SELECT COUNT(*) FROM productos v WHERE v.producto_padre_id = p.id) AS total_variantes,
    (SELECT COUNT(*) FROM producto_componentes pc WHERE pc.kit_id = p.id) AS total_componentes,
    p.creado_at,
    p.deleted_at
FROM productos p
LEFT JOIN productos pp ON pp.id = p.producto_padre_id
LEFT JOIN marcas m ON m.id = p.marca_id
LEFT JOIN categorias c ON c.id = p.categoria 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_rotacion_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_rotacion_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,ifnull(sum(`d`.`cantidad`),0) AS `total_vendido`,round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) AS `porcentaje_rotacion`,case when `p`.`stock` <= `p`.`min_stock` then 'CRÍTICO' when ifnull(sum(`d`.`cantidad`),0) = 0 then 'ESTANCADO' else 'NORMAL' end AS `estado_rotacion` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_stock_detallado`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_stock_detallado` AS SELECT
    su.id,
    a.id AS almacen_id,
    a.codigo AS almacen_codigo,
    a.nombre AS almacen_nombre,
    u.id AS ubicacion_id,
    u.codigo AS ubicacion_codigo,
    CONCAT(COALESCE(u.pasillo, ''), '-', COALESCE(u.estante, ''), '-', COALESCE(u.nivel, '')) AS ubicacion_full,
    p.id AS producto_id,
    p.sku,
    p.nombre AS producto_nombre,
    su.cantidad,
    su.stock_minimo,
    su.stock_maximo,
    CASE
        WHEN su.cantidad <= su.stock_minimo THEN 'bajo'
        WHEN su.stock_maximo IS NOT NULL AND su.cantidad >= su.stock_maximo THEN 'lleno'
        ELSE 'normal'
    END AS estado_stock
FROM stock_ubicacion su
INNER JOIN almacenes a ON a.id = su.almacen_id
INNER JOIN productos p ON p.id = su.producto_id
LEFT JOIN ubicaciones u ON u.id = su.ubicacion_id
WHERE a.activo = 1 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_stock_por_almacen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_stock_por_almacen` AS SELECT
    a.id AS almacen_id,
    a.codigo AS almacen_codigo,
    a.nombre AS almacen_nombre,
    p.id AS producto_id,
    p.sku,
    p.nombre AS producto_nombre,
    COALESCE(SUM(su.cantidad), 0) AS stock_almacen,
    COUNT(DISTINCT su.ubicacion_id) AS num_ubicaciones
FROM almacenes a
CROSS JOIN productos p
LEFT JOIN stock_ubicacion su ON su.almacen_id = a.id AND su.producto_id = p.id
WHERE a.activo = 1 AND p.deleted_at IS NULL
GROUP BY a.id, p.id
ORDER BY a.nombre, p.nombre 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_tasa_hoy`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_tasa_hoy` AS SELECT
    COALESCE(
        (SELECT tasa FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT tasa FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        1
    ) AS tasa_actual,
    COALESCE(
        (SELECT fecha FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT fecha FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        CURDATE()
    ) AS fecha_tasa,
    COALESCE(
        (SELECT fuente FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT fuente FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        'no_configurada'
    ) AS fuente 
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
