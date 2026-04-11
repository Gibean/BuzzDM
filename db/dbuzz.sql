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

-- Volcando estructura para tabla dbuzz.acciones_usuario
DROP TABLE IF EXISTS `acciones_usuario`;
CREATE TABLE IF NOT EXISTS `acciones_usuario` (
  `id` bigint(20) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` varchar(16) NOT NULL COMMENT 'venta | liberacion | edicion',
  `asiento_codigo` varchar(16) NOT NULL,
  `detalle` longtext DEFAULT NULL COMMENT 'JSON opcional',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.acciones_usuario: ~61 rows (aproximadamente)
DELETE FROM `acciones_usuario`;
INSERT INTO `acciones_usuario` (`id`, `usuario_id`, `tipo`, `asiento_codigo`, `detalle`, `creado_en`) VALUES
	(13, 15, 'liberacion', 'a007', '{"fila":"A","columna":7,"entradas_eliminadas":1}', '2026-03-27 08:46:31'),
	(14, 15, 'venta', 'a007', '{"entrada_id":299,"nombre":"Juan Chaurant","telefono":"+583055009199","paquete":"basico","fecha":"2026-03-27","referencia_pago":null}', '2026-03-27 08:46:48'),
	(16, 15, 'liberacion', 'a007', '{"fila":"A","columna":7,"entradas_eliminadas":1}', '2026-03-27 08:47:32'),
	(20, 30, 'venta', 'a007', '{"entrada_id":300,"nombre":"Juan Chaurant","telefono":"+583055009199","paquete":"basico","fecha":"2026-03-27","referencia_pago":null}', '2026-03-27 09:19:51'),
	(21, 30, 'liberacion', 'a007', '{"fila":"A","columna":7,"entradas_eliminadas":1}', '2026-03-27 09:20:04'),
	(22, 30, 'venta', 'a006', '{"nombre":"Juan Chaurant"}', '2026-03-27 09:20:59'),
	(23, 30, 'liberacion', 'a006', '{"nombre":"Juan Chaurant"}', '2026-03-27 09:21:24'),
	(25, 28, 'venta', 'f004', '{"nombre":"José Cordova"}', '2026-03-27 12:58:48'),
	(26, 28, 'venta', 'f005', '{"nombre":"Sebastian Cordova"}', '2026-03-27 12:59:17'),
	(28, 28, 'venta', 'g014', '{"nombre":"Rubén Psas"}', '2026-03-27 17:36:24'),
	(29, 28, 'venta', 'd011', '{"nombre":"Johan carrión"}', '2026-03-27 17:55:23'),
	(30, 28, 'venta', 'd012', '{"nombre":"Johan carrion"}', '2026-03-27 17:55:53'),
	(31, 28, 'venta', 'd013', '{"nombre":"Johan carrion"}', '2026-03-27 17:57:01'),
	(32, 28, 'edicion', 'f011', '{"nombre":"Elias Arismendi"}', '2026-03-27 18:02:21'),
	(33, 28, 'liberacion', 'f011', '{"nombre":"Elias Arismendi"}', '2026-03-27 18:02:24'),
	(34, 28, 'edicion', 'f012', '{"nombre":"Maria Villarroel"}', '2026-03-27 18:02:30'),
	(35, 28, 'liberacion', 'f012', '{"nombre":"Maria Villarroel"}', '2026-03-27 18:02:39'),
	(36, 28, 'venta', 'f011', '{"nombre":"Elías Arismendi"}', '2026-03-27 18:03:22'),
	(37, 28, 'venta', 'f012', '{"nombre":"María Villarroel"}', '2026-03-27 18:03:58'),
	(38, 24, 'venta', 'h011', '{"nombre":"Ana Isabel Velasquez"}', '2026-03-27 19:55:08'),
	(39, 24, 'venta', 'j008', '{"nombre":"Guillermo aguilera"}', '2026-03-27 19:58:39'),
	(40, 24, 'venta', 'e015', '{"nombre":"Sergio Belisario"}', '2026-03-27 21:30:35'),
	(41, 24, 'venta', 'e014', '{"nombre":"Mar"}', '2026-03-27 21:31:33'),
	(42, 24, 'venta', 'e013', '{"nombre":"Sergio Fabriccio"}', '2026-03-27 21:32:12'),
	(43, 24, 'venta', 'j002', '{"nombre":"Leobaldo Gil"}', '2026-03-27 21:33:22'),
	(44, 24, 'venta', 'j003', '{"nombre":"Leobaldo Gil"}', '2026-03-27 21:33:55'),
	(45, 24, 'edicion', 'e014', '{"nombre":"Mar"}', '2026-03-27 21:35:38'),
	(46, 24, 'liberacion', 'e014', '{"nombre":"Mar"}', '2026-03-27 21:35:47'),
	(47, 24, 'venta', 'e014', '{"nombre":"Mayloris Colmenares"}', '2026-03-27 21:36:15'),
	(48, 24, 'venta', 'l011', '{"nombre":"Dam Rojas"}', '2026-03-27 21:49:03'),
	(49, 24, 'venta', 'l003', '{"nombre":"Leobaldo Gil"}', '2026-03-27 21:51:10'),
	(50, 24, 'venta', 'l004', '{"nombre":"Leobaldo Gil"}', '2026-03-27 21:51:43'),
	(51, 24, 'venta', 'l005', '{"nombre":"Leobaldo Gil"}', '2026-03-27 21:51:52'),
	(52, 24, 'venta', 'h015', '{"nombre":"Yainira Gamboa"}', '2026-03-27 22:00:02'),
	(53, 24, 'venta', 'h016', '{"nombre":"Yainira Gamboa"}', '2026-03-27 22:00:54'),
	(54, 24, 'venta', 'l008', '{"nombre":"Leobaldo Gil"}', '2026-03-27 22:02:16'),
	(55, 24, 'venta', 'k004', '{"nombre":"Leobaldo Gil"}', '2026-03-27 22:02:24'),
	(56, 24, 'venta', 'c004', '{"nombre":"Andrea Estanga"}', '2026-03-27 22:07:31'),
	(57, 24, 'venta', 'c005', '{"nombre":"Andrea Estanga"}', '2026-03-27 22:07:52'),
	(58, 24, 'edicion', 'j016', '{"nombre":"Pedro Pinto"}', '2026-03-27 22:31:43'),
	(59, 28, 'edicion', 'h004', '{"nombre":"Mariana Wansa"}', '2026-03-27 22:44:03'),
	(60, 28, 'edicion', 'h005', '{"nombre":"Mariana Wansa"}', '2026-03-27 22:44:18'),
	(61, 28, 'edicion', 'h006', '{"nombre":"Mariana Wansa"}', '2026-03-27 22:44:28'),
	(62, 28, 'edicion', 'h006', '{"nombre":"Mariana Wansa"}', '2026-03-27 22:44:58'),
	(63, 24, 'liberacion', 'l008', '{"nombre":"Leobaldo Gil"}', '2026-03-27 23:08:50'),
	(64, 24, 'liberacion', 'k004', '{"nombre":"Leobaldo Gil"}', '2026-03-27 23:08:56'),
	(65, 24, 'venta', 'h001', '{"nombre":"Javier Diaz"}', '2026-03-27 23:12:35'),
	(66, 24, 'edicion', 'l003', '{"nombre":"Leobaldo Gil"}', '2026-03-27 23:19:50'),
	(67, 24, 'edicion', 'l004', '{"nombre":"Leobaldo Gil"}', '2026-03-27 23:19:57'),
	(68, 24, 'edicion', 'l005', '{"nombre":"Leobaldo Gil"}', '2026-03-27 23:20:02'),
	(69, 24, 'edicion', 'f013', '{"nombre":"Susej Rodriguez"}', '2026-03-27 23:25:12'),
	(70, 24, 'edicion', 'f014', '{"nombre":"Jesus Freitez"}', '2026-03-27 23:25:18'),
	(71, 28, 'edicion', 'f004', '{"nombre":"José Cordova"}', '2026-03-27 23:58:07'),
	(72, 28, 'edicion', 'f005', '{"nombre":"Sebastian Cordova"}', '2026-03-27 23:58:15'),
	(73, 28, 'venta', 'f016', '{"nombre":"Ramses Lira"}', '2026-03-28 00:30:59'),
	(74, 28, 'venta', 'f017', '{"nombre":"Luis Narváez"}', '2026-03-28 00:43:02'),
	(75, 28, 'venta', 'f018', '{"nombre":"Luis Narváez"}', '2026-03-28 00:43:24'),
	(76, 28, 'venta', 'e011', '{"nombre":"Samuel Corona"}', '2026-03-28 00:44:40'),
	(77, 28, 'venta', 'e018', '{"nombre":"José Sosa"}', '2026-03-28 02:12:22'),
	(78, 28, 'venta', 'e017', '{"nombre":"Roynel"}', '2026-03-28 02:13:00'),
	(79, 28, 'edicion', 'e017', '{"nombre":"Roynel"}', '2026-03-28 02:13:21');

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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Almacenes/Bodegas/Tiendas del sistema';

-- Volcando datos para la tabla dbuzz.almacenes: ~0 rows (aproximadamente)
DELETE FROM `almacenes`;
INSERT INTO `almacenes` (`id`, `codigo`, `nombre`, `direccion`, `telefono`, `tipo`, `es_principal`, `activo`, `responsable_id`, `notas`, `creado_at`, `actualizado_at`) VALUES
	(1, 'ALM-PRINCIPAL', 'DEPÓSITO 1', 'Calle Bolívar Cruce con, nro. 17 Sector C. Miranda, El Tigre 6050, Anzoátegui', '', 'almacen', 1, 1, NULL, '', '2026-01-07 20:59:51', '2026-03-26 04:54:45'),
	(2, 'ALM-001', 'DEPÓSITO 2', '', '', 'almacen', 0, 1, NULL, 'Vitrina de exhibición interna', '2026-02-04 19:30:29', '2026-03-26 04:55:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.backups: ~6 rows (aproximadamente)
DELETE FROM `backups`;
INSERT INTO `backups` (`id`, `archivo`, `ruta_archivo`, `tamaño_bytes`, `fecha`, `tipo`, `estado`, `resultado`, `detalles`, `usuario_id`) VALUES
	(7, 'dbuzzbackup_2026-01-21_00-27-05.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-01-21_00-27-05.sql', 125915, '2026-01-20 23:27:09', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":22,"duracion_ms":3325,"tamanio_mb":0.12,"metodo":"fallback"}', 1),
	(8, 'dbuzzbackup_2026-01-23_02-55-24.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-01-23_02-55-24.sql', 125274, '2026-01-23 01:55:31', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":23,"duracion_ms":6388,"tamanio_mb":0.12,"metodo":"fallback"}', 1),
	(9, 'dbuzzbackup_2026-01-28_01-44-00.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-01-28_01-44-00.sql', 126293, '2026-01-28 00:44:10', 'automatico', 'completado', 'Backup automático creado exitosamente (fallback)', '{"tablas_exportadas":23,"duracion_ms":9775,"tamanio_mb":0.12,"metodo":"fallback"}', 1),
	(10, 'dbuzzbackup_2026-01-28_08-06-34.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-01-28_08-06-34.sql', 122294, '2026-01-28 07:06:36', 'manual', 'completado', 'Backup creado exitosamente', '{"tablas_exportadas":22,"duracion_ms":2294,"tamanio_mb":0.12,"metodo":"standard"}', 1),
	(11, 'dbuzzbackup_2026-02-04_05-05-50.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-02-04_05-05-50.sql', 124364, '2026-02-04 04:05:52', 'manual', 'completado', 'Backup creado exitosamente', '{"tablas_exportadas":21,"duracion_ms":2206,"tamanio_mb":0.12,"metodo":"standard"}', 1),
	(12, 'dbuzzbackup_2026-02-07_05-49-54.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-02-07_05-49-54.sql', 141618, '2026-02-07 04:49:57', 'manual', 'completado', 'Backup creado exitosamente', '{"tablas_exportadas":17,"duracion_ms":3244,"tamanio_mb":0.14,"metodo":"standard"}', 1),
	(13, 'dbuzzbackup_2026-03-26_04-41-16.sql', 'C:\\xampp\\htdocs\\BuzzDM\\php/backups/dbuzzbackup_2026-03-26_04-41-16.sql', 0, '2026-03-26 03:41:17', 'automatico', 'error', 'Error al crear backup: ', '{"error_output":[],"return_code":2}', 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de cargos masivos de existencias';

-- Volcando datos para la tabla dbuzz.cargos_existencias: ~1 rows (aproximadamente)
DELETE FROM `cargos_existencias`;
INSERT INTO `cargos_existencias` (`id`, `codigo`, `fecha`, `concepto`, `concepto_otro`, `proveedor_id`, `almacen_id`, `referencia_externa`, `notas`, `total_productos`, `total_unidades`, `valor_total`, `estado`, `registrado_por`, `confirmado_por`, `fecha_confirmacion`, `anulado_por`, `fecha_anulacion`, `motivo_anulacion`, `creado_at`, `actualizado_at`) VALUES
	(1, 'CGO-2026-0001', '2026-01-18', 'ajuste_positivo', '', NULL, 1, '5151351684541', 'blah', 1, 20, 440.00, 'confirmado', 1, 1, '2026-01-18 00:38:06', NULL, NULL, NULL, '2026-01-18 00:38:06', '2026-01-18 00:38:06');

-- Volcando estructura para tabla dbuzz.categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.categorias: ~7 rows (aproximadamente)
DELETE FROM `categorias`;
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
	(1, 'Papelería', 'Artículos de papelería generales: cuadernos, bolígrafos, goma, etc.'),
	(2, 'Útiles escolares', 'Artículos pensados para estudiantes: lápices, reglas, estuches'),
	(3, 'Oficina', 'Papel, resmas, archivadores y suministros de oficina'),
	(4, 'Artículos de arte', 'Material para dibujo y pintura: acuarelas, cartulinas, marcadores'),
	(5, 'Adhesivos y pegamentos', 'Pegamento en barra, líquido, cintas adhesivas'),
	(6, 'Cajas y archivadores', 'Archivadores, carpetas y organizadores'),
	(7, 'Literatura', '');

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
	('V-10000001', 'María López', 'm.lopez@example.com', '+58 412 345 6789', 'Calle Comercio 123, Caracas', '', 0, '2025-12-01 13:00:00', NULL),
	('V-10000002', 'José Pérez', 'j.perez@example.com', '+58 414 987 6543', 'Av. Bolívar 45, Valencia', 'Cliente frecuente', 1, '2025-11-20 15:30:00', NULL),
	('V-10000003', 'Librería La esquina', 'contabilidad@laesquina.com', '+58 212 999 8888', 'C.C. Centro, Local 12', 'Mayorista', 0, '2025-10-05 18:22:00', NULL),
	('V-30535128', 'Gisvel Aray', 'gisvelaray@gmail.com', '+584128759068', 'Calle Luisa Caceres de Arismendi', 'Cliente frecuente en compra de libros de fantasía, mitología, material de dibujo y trinkets', 0, '2026-02-07 01:43:18', NULL),
	('V-99999999', 'Cliente Test Integridad', 'test_integridad@buzzdm.com', '555-0000', 'Dirección de Prueba', NULL, 0, '2026-02-07 05:00:55', NULL),
	('V12987499', 'Jonas Laboff', 'jonaslaboff@gmail.com', '0416899745', 'El tigre estado anzoátegui', NULL, 1, '2026-02-07 02:50:13', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.configuraciones: ~4 rows (aproximadamente)
DELETE FROM `configuraciones`;
INSERT INTO `configuraciones` (`id`, `clave`, `valor`, `descripcion`, `fecha_actualizacion`) VALUES
	(1, 'backup_fallback_enabled', '0', 'Habilitar sistema de backup automático fallback', '2026-03-26 03:41:40'),
	(8, 'store_name', 'Librería El Estudiante', 'Nombre mostrado en factura y sitio', '2026-01-07 21:01:09'),
	(9, 'currency', 'VES', 'Moneda del sistema', '2026-01-07 21:01:09'),
	(10, 'tax_percent', '12.00', 'Porcentaje de IVA aplicado a ventas', '2026-01-07 21:01:09');

-- Volcando estructura para tabla dbuzz.data_entradas
DROP TABLE IF EXISTS `data_entradas`;
CREATE TABLE IF NOT EXISTS `data_entradas` (
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

-- Volcando datos para la tabla dbuzz.data_entradas: ~0 rows (aproximadamente)
DELETE FROM `data_entradas`;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de productos en cada cargo de existencias';

-- Volcando datos para la tabla dbuzz.detalle_cargo_existencias: ~0 rows (aproximadamente)
DELETE FROM `detalle_cargo_existencias`;
INSERT INTO `detalle_cargo_existencias` (`id`, `cargo_id`, `producto_id`, `cantidad`, `costo_unitario`, `notas`) VALUES
	(1, 1, 12, 20, 22.00, NULL);

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
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.detalle_pedido: ~21 rows (aproximadamente)
DELETE FROM `detalle_pedido`;
INSERT INTO `detalle_pedido` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
	(1, 2, 2, 2, 2.50),
	(2, 2, 4, 3, 1.20),
	(3, 2, 8, 1, 1.10),
	(4, 3, 5, 1, 8.50),
	(5, 3, 7, 2, 0.80),
	(6, 4, 11, 10, 6.50),
	(7, 4, 2, 20, 2.50),
	(8, 5, 4, 5, 1.20),
	(9, 5, 6, 2, 3.75),
	(10, 6, 3, 2, 5.00),
	(11, 6, 7, 1, 0.80),
	(12, 7, 9, 1, 15.00),
	(13, 7, 8, 2, 1.10),
	(14, 8, 12, 1, 22.00),
	(15, 8, 5, 1, 8.50),
	(16, 9, 10, 1, 4.25),
	(17, 9, 2, 3, 2.50),
	(18, 10, 4, 10, 1.20),
	(19, 10, 13, 2, 2.00),
	(20, 11, 3, 5, 5.00),
	(21, 11, 11, 2, 6.50),
	(22, 12, 2, 20, 2.50),
	(23, 13, 14, 3, 129.00),
	(24, 14, 14, 1, 129.00),
	(25, 18, 2, 1, 2.50),
	(26, 18, 6, 1, 3.75),
	(27, 18, 7, 1, 0.80);

-- Volcando estructura para tabla dbuzz.detalle_requisicion
DROP TABLE IF EXISTS `detalle_requisicion`;
CREATE TABLE IF NOT EXISTS `detalle_requisicion` (
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
  CONSTRAINT `fk_detreq_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_prov_seleccionado` FOREIGN KEY (`proveedor_seleccionado_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_prov_sugerido` FOREIGN KEY (`proveedor_sugerido_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_detreq_requisicion` FOREIGN KEY (`requisicion_id`) REFERENCES `requisiciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en requisición';

-- Volcando datos para la tabla dbuzz.detalle_requisicion: ~5 rows (aproximadamente)
DELETE FROM `detalle_requisicion`;
INSERT INTO `detalle_requisicion` (`id`, `requisicion_id`, `producto_id`, `cantidad_solicitada`, `proveedor_sugerido_id`, `precio_sugerido`, `proveedor_seleccionado_id`, `notas`) VALUES
	(4, 1, 10, 10, 1, 0.0000, NULL, NULL),
	(5, 1, 13, 10, 2, 0.0000, NULL, NULL),
	(6, 1, 14, 1, 5, 0.0000, NULL, NULL),
	(7, 2, 14, 1, 5, 0.0000, NULL, NULL),
	(8, 2, 11, 1, 1, 0.0000, NULL, NULL);

-- Volcando estructura para tabla dbuzz.detalle_transferencia
DROP TABLE IF EXISTS `detalle_transferencia`;
CREATE TABLE IF NOT EXISTS `detalle_transferencia` (
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
  CONSTRAINT `fk_dt_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `fk_dt_transferencia` FOREIGN KEY (`transferencia_id`) REFERENCES `transferencias_almacen` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.detalle_transferencia: ~1 rows (aproximadamente)
DELETE FROM `detalle_transferencia`;
INSERT INTO `detalle_transferencia` (`id`, `transferencia_id`, `producto_id`, `cantidad_solicitada`, `cantidad_enviada`, `cantidad_recibida`, `notas`) VALUES
	(7, 1, 10, 4, NULL, NULL, NULL);

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

-- Volcando datos para la tabla dbuzz.empresa: ~0 rows (aproximadamente)
DELETE FROM `empresa`;
INSERT INTO `empresa` (`id`, `tipo_rif`, `rif`, `nombre`, `logo_path`, `direccion`, `telefono`, `email`, `creado_at`, `actualizado_at`, `logo_svg`) VALUES
	(1, 'J', 'J08089545', 'Librería Txiki, C.A.', NULL, 'Calle Bolívar Cruce con, nro. 17 Sector C. Miranda, El Tigre 6050, Anzoátegui', '+58 82832353851', 'libreriatxiki@gmail.com', '2026-01-07 21:01:09', '2026-03-26 03:23:05', 'assets/img/uploads/J08089545.webp');

-- Volcando estructura para tabla dbuzz.entradas
DROP TABLE IF EXISTS `entradas`;
CREATE TABLE IF NOT EXISTS `entradas` (
  `id` int(10) unsigned NOT NULL,
  `persona_id` int(10) unsigned NOT NULL,
  `paquete_id` smallint(5) unsigned NOT NULL,
  `talla_franela_id` smallint(5) unsigned DEFAULT NULL,
  `fecha` date NOT NULL,
  `asiento_codigo` varchar(16) NOT NULL COMMENT 'Ej: k7, l11',
  `referencia_pago` varchar(64) NOT NULL COMMENT 'Texto para conservar ceros a la izquierda'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.entradas: ~124 rows (aproximadamente)
DELETE FROM `entradas`;
INSERT INTO `entradas` (`id`, `persona_id`, `paquete_id`, `talla_franela_id`, `fecha`, `asiento_codigo`, `referencia_pago`) VALUES
	(198, 65, 1, 3, '2026-03-25', 'b005', '926742'),
	(199, 66, 2, NULL, '2026-03-25', 'd005', '000680833574'),
	(200, 66, 2, NULL, '2026-03-25', 'd006', '234938269859'),
	(201, 67, 2, NULL, '2026-03-26', 'e004', '682901189'),
	(202, 67, 1, 2, '2026-03-25', 'e005', '000680277090'),
	(203, 68, 3, NULL, '2026-03-25', 'e006', '000680277091'),
	(204, 69, 2, NULL, '2026-03-26', 'e012', '682508921'),
	(205, 70, 2, NULL, '2026-03-26', 'e016', '60857780535'),
	(206, 71, 1, 4, '2026-03-26', 'f006', '000682299498'),
	(207, 72, 2, NULL, '2026-03-26', 'f007', '000682299498'),
	(208, 73, 2, NULL, '2026-03-24', 'f008', '408353983923'),
	(211, 76, 2, NULL, '2026-03-25', 'f013', '684567643'),
	(212, 77, 2, NULL, '2026-03-25', 'f014', '684567643'),
	(213, 78, 2, NULL, '2026-03-25', 'f015', '000681056427'),
	(214, 79, 2, NULL, '2026-03-26', 'g001', '10755'),
	(215, 79, 2, NULL, '2026-03-26', 'g002', '10755'),
	(216, 79, 2, NULL, '2026-03-26', 'g003', '10755'),
	(217, 80, 1, 7, '2026-03-25', 'g004', '408454100622'),
	(218, 80, 2, NULL, '2026-03-25', 'g005', '408454100622'),
	(219, 80, 2, NULL, '2026-03-25', 'g006', '408454100622'),
	(220, 81, 1, 5, '2026-03-26', 'g007', '683406742'),
	(221, 82, 1, 6, '2026-03-26', 'g008', '683406742'),
	(222, 83, 2, NULL, '2026-03-26', 'g011', '683319838'),
	(223, 84, 2, NULL, '2026-03-26', 'g012', '683319838'),
	(224, 85, 2, NULL, '2026-03-25', 'g013', '135249447281'),
	(225, 86, 2, NULL, '2026-03-26', 'g015', '682564956'),
	(226, 86, 2, NULL, '2026-03-26', 'g016', '000682074837'),
	(227, 87, 2, NULL, '2026-03-26', 'g017', '000682074837'),
	(228, 88, 2, NULL, '2026-03-26', 'g018', '000682074837'),
	(229, 89, 2, NULL, '2026-03-25', 'h002', '000681410128'),
	(230, 90, 2, NULL, '2026-03-25', 'h003', '000681410128'),
	(231, 91, 2, NULL, '2026-03-26', 'h004', '684653210'),
	(232, 91, 2, NULL, '2026-03-26', 'h005', '684653210'),
	(233, 91, 2, NULL, '2026-03-26', 'h006', '684653210'),
	(234, 92, 1, 9, '2026-03-25', 'h007', '000681122228'),
	(235, 93, 1, 7, '2026-03-25', 'h008', '59079359'),
	(236, 94, 2, NULL, '2026-03-25', 'h012', '000681110096'),
	(237, 94, 2, NULL, '2026-03-25', 'h013', '000681110096'),
	(238, 95, 1, 3, '2026-03-25', 'h014', '00990850'),
	(239, 96, 2, NULL, '2026-03-25', 'h017', '729189'),
	(240, 97, 2, NULL, '2026-03-25', 'h018', '729189'),
	(241, 98, 1, 3, '2026-03-25', 'i002', '000000184106'),
	(242, 99, 1, 4, '2026-03-25', 'i003', '000000184107'),
	(243, 100, 2, NULL, '2026-03-25', 'i004', '000680886592'),
	(244, 100, 2, NULL, '2026-03-25', 'i005', '000680886592'),
	(245, 100, 2, NULL, '2026-03-25', 'i006', '000680886592'),
	(246, 100, 2, NULL, '2026-03-25', 'i007', '000680886592'),
	(247, 101, 2, NULL, '2026-03-26', 'i008', '408554507682'),
	(248, 102, 2, NULL, '2026-03-25', 'i011', '3701192935274'),
	(249, 102, 2, NULL, '2026-03-25', 'i012', '3701192935274'),
	(250, 102, 2, NULL, '2026-03-25', 'i013', '3701192935274'),
	(251, 102, 2, NULL, '2026-03-25', 'i014', '3701192935274'),
	(252, 103, 2, NULL, '2026-03-26', 'i015', '715846633'),
	(253, 103, 2, NULL, '2026-03-26', 'i016', '715846633'),
	(254, 103, 2, NULL, '2026-03-26', 'i017', '715846633'),
	(255, 104, 2, NULL, '2026-03-26', 'j001', '715656265'),
	(256, 105, 2, NULL, '2026-03-25', 'j004', '000680183058'),
	(257, 105, 2, NULL, '2026-03-25', 'j005', '000680183058'),
	(258, 106, 2, NULL, '2026-03-26', 'j006', '682759563'),
	(259, 107, 2, NULL, '2026-03-26', 'j007', '682759563'),
	(260, 108, 2, NULL, '2026-03-25', 'j011', ''),
	(261, 108, 2, NULL, '2026-03-25', 'j012', ''),
	(262, 108, 2, NULL, '2026-03-25', 'j013', ''),
	(263, 108, 2, NULL, '2026-03-25', 'j014', ''),
	(264, 108, 2, NULL, '2026-03-25', 'j015', ''),
	(265, 108, 2, NULL, '2026-03-25', 'j016', '685148737'),
	(266, 108, 2, NULL, '2026-03-25', 'j017', ''),
	(267, 108, 2, NULL, '2026-03-25', 'j018', ''),
	(268, 109, 2, NULL, '2027-03-24', 'k001', '679788818'),
	(269, 110, 2, NULL, '2028-03-24', 'k002', '679788818'),
	(270, 111, 2, NULL, '2029-03-24', 'k003', '679788818'),
	(271, 112, 1, 10, '2026-03-25', 'k005', '000681658149'),
	(272, 113, 1, 6, '2026-03-25', 'k006', '000681658149'),
	(273, 114, 1, 8, '2026-03-25', 'k007', '000681658149'),
	(274, 112, 2, NULL, '2026-03-25', 'k008', '000681658149'),
	(275, 108, 2, NULL, '2026-03-25', 'k011', ''),
	(276, 108, 2, NULL, '2026-03-25', 'k012', ''),
	(277, 108, 2, NULL, '2026-03-25', 'k013', ''),
	(278, 108, 2, NULL, '2026-03-25', 'k014', ''),
	(279, 108, 2, NULL, '2026-03-25', 'k015', ''),
	(280, 108, 2, NULL, '2026-03-25', 'k016', ''),
	(281, 108, 2, NULL, '2026-03-25', 'k017', ''),
	(282, 108, 2, NULL, '2026-03-25', 'k018', ''),
	(283, 115, 2, NULL, '2026-03-25', 'l001', '000008527'),
	(284, 115, 2, NULL, '2026-03-25', 'l002', '000008527'),
	(285, 116, 2, NULL, '2026-03-25', 'l006', '00068036543'),
	(286, 117, 2, NULL, '2026-03-25', 'l007', '00068036543'),
	(287, 118, 2, NULL, '2026-03-25', 'l012', '060843990270'),
	(288, 119, 2, NULL, '2026-03-25', 'l013', '060843990270'),
	(289, 120, 2, NULL, '2026-03-25', 'l014', '143147469447'),
	(290, 121, 2, NULL, '2026-03-25', 'l015', '000681588360'),
	(291, 122, 2, NULL, '2026-03-25', 'l016', '060845418715'),
	(292, 123, 2, NULL, '2026-03-26', 'l017', '060856676157'),
	(293, 124, 1, 3, '2026-03-25', 'l018', '74099074'),
	(302, 128, 2, NULL, '2026-03-27', 'f004', '685344471'),
	(303, 129, 2, NULL, '2026-03-27', 'f005', '685344471'),
	(304, 130, 2, NULL, '2026-03-27', 'g014', '684231303'),
	(305, 131, 1, NULL, '2026-03-27', 'd011', ''),
	(306, 131, 1, NULL, '2026-03-27', 'd012', ''),
	(307, 131, 2, NULL, '2026-03-27', 'd013', ''),
	(308, 74, 2, NULL, '2026-03-27', 'f011', '135134556177'),
	(309, 75, 2, NULL, '2026-03-27', 'f012', '135134556177'),
	(310, 132, 2, NULL, '2026-03-27', 'h011', '684695567'),
	(311, 133, 2, NULL, '2026-03-27', 'j008', '716982387'),
	(312, 134, 2, NULL, '2026-03-27', 'e015', '684718723'),
	(314, 136, 1, 2, '2026-03-27', 'e013', '684718723'),
	(315, 137, 2, NULL, '2026-03-27', 'j002', '408654712468'),
	(316, 137, 2, NULL, '2026-03-27', 'j003', '408654712468'),
	(317, 138, 2, NULL, '2026-03-27', 'e014', '716982387'),
	(318, 139, 2, NULL, '2026-03-27', 'l011', '685030587'),
	(319, 137, 2, NULL, '2026-03-27', 'l003', '408654777603'),
	(320, 137, 2, NULL, '2026-03-27', 'l004', '408654777603'),
	(321, 137, 2, NULL, '2026-03-27', 'l005', '408654777603'),
	(322, 101, 2, NULL, '2026-03-27', 'h015', '408554507682'),
	(323, 101, 2, NULL, '2026-03-27', 'h016', '408654762774'),
	(326, 140, 2, NULL, '2026-03-27', 'c004', ''),
	(327, 141, 2, NULL, '2026-03-27', 'c005', ''),
	(328, 142, 2, NULL, '2026-03-27', 'h001', '685123748'),
	(329, 143, 2, NULL, '2026-03-27', 'f016', '685453818'),
	(330, 144, 2, NULL, '2026-03-27', 'f017', '60861879022'),
	(331, 144, 2, NULL, '2026-03-27', 'f018', '60861879022'),
	(332, 145, 2, NULL, '2026-03-27', 'e011', '408654793441'),
	(333, 146, 2, NULL, '2026-03-28', 'e018', '685575729'),
	(334, 147, 2, NULL, '2026-03-28', 'e017', '685698037');

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

-- Volcando estructura para función dbuzz.fn_generar_codigo_requisicion
DROP FUNCTION IF EXISTS `fn_generar_codigo_requisicion`;
DELIMITER //
CREATE FUNCTION `fn_generar_codigo_requisicion`() RETURNS varchar(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
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
  `detalles` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `importaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.importaciones: ~0 rows (aproximadamente)
DELETE FROM `importaciones`;

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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs: ~54 rows (aproximadamente)
DELETE FROM `logs`;
INSERT INTO `logs` (`id`, `usuario_id`, `accion`, `detalles`, `fecha`) VALUES
	(1, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-07_22-01-17.sql | Tamaño: 0.11 MB', '2026-01-07 21:01:20'),
	(2, 1, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-07_22-01-17.sql","tamanio_mb":0.11,"duracion_ms":2800,"tablas":23,"metodo":"fallback"}', '2026-01-07 21:01:20'),
	(3, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-12_21-34-13.sql | Tamaño: 0.11 MB', '2026-01-12 20:34:19'),
	(4, 1, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-12_21-34-13.sql","tamanio_mb":0.11,"duracion_ms":6101,"tablas":23,"metodo":"fallback"}', '2026-01-12 20:34:19'),
	(5, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-13_22-17-33.sql | Tamaño: 0.11 MB', '2026-01-13 21:17:35'),
	(6, 1, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-13_22-17-33.sql","tamanio_mb":0.11,"duracion_ms":2371,"tablas":24,"metodo":"fallback"}', '2026-01-13 21:17:35'),
	(7, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-18_01-18-25.sql | Tamaño: 0.11 MB', '2026-01-18 00:18:26'),
	(8, 1, 'Backup Automático (Fallback)', '{"archivo":"dbuzzbackup_2026-01-18_01-18-25.sql","tamanio_mb":0.11,"duracion_ms":1715,"tablas":23,"metodo":"fallback"}', '2026-01-18 00:18:26'),
	(9, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-19_01-38-29.sql | Tamaño: 0.12 MB', '2026-01-19 00:38:34'),
	(10, 1, 'Backup Automático', '{"archivo":"dbuzzbackup_2026-01-19_01-38-29.sql","tamanio_mb":0.12,"duracion_ms":4733}', '2026-01-19 00:38:34'),
	(11, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-19_23-43-10.sql | Tamaño: 0.12 MB', '2026-01-19 22:43:17'),
	(12, 1, 'Backup Automático', '{"archivo":"dbuzzbackup_2026-01-19_23-43-10.sql","tamanio_mb":0.12,"duracion_ms":5900}', '2026-01-19 22:43:17'),
	(13, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-21_00-27-05.sql | Tamaño: 0.12 MB', '2026-01-20 23:27:09'),
	(14, 1, 'Backup Automático', '{"archivo":"dbuzzbackup_2026-01-21_00-27-05.sql","tamanio_mb":0.12,"duracion_ms":3325}', '2026-01-20 23:27:09'),
	(15, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-23_02-55-24.sql | Tamaño: 0.12 MB', '2026-01-23 01:55:31'),
	(16, 1, 'Backup Automático', '{"archivo":"dbuzzbackup_2026-01-23_02-55-24.sql","tamanio_mb":0.12,"duracion_ms":6388}', '2026-01-23 01:55:31'),
	(17, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-28_01-44-00.sql | Tamaño: 0.12 MB', '2026-01-28 00:44:10'),
	(18, 1, 'Backup Automático', '{"archivo":"dbuzzbackup_2026-01-28_01-44-00.sql","tamanio_mb":0.12,"duracion_ms":9775}', '2026-01-28 00:44:10'),
	(19, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-01-28_08-06-34.sql | Tamaño: 0.12 MB', '2026-01-28 07:06:36'),
	(20, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2026-01-28_08-06-34.sql","tamanio_mb":0.12,"duracion_ms":2294}', '2026-01-28 07:06:36'),
	(21, 1, 'Producto eliminado', '{"id":"2","sku":"LBR-0001","nombre":""}', '2026-02-04 03:58:32'),
	(22, 1, 'Producto restaurado', '{"id":2}', '2026-02-04 03:58:38'),
	(23, 1, 'Producto eliminado', 'LBR-0003 - ', '2026-02-04 04:02:53'),
	(24, 1, 'Producto eliminado', 'LBR-0007 - ', '2026-02-04 04:03:00'),
	(25, 1, 'Producto restaurado', 'LBR-0003 - ', '2026-02-04 04:03:08'),
	(26, 1, 'Producto restaurado', 'LBR-0007 - ', '2026-02-04 04:03:11'),
	(27, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-02-04_05-05-50.sql | Tamaño: 0.12 MB', '2026-02-04 04:05:52'),
	(28, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2026-02-04_05-05-50.sql","tamanio_mb":0.12,"duracion_ms":2206}', '2026-02-04 04:05:52'),
	(29, 1, 'Usuario creado', '#Array | Yeicson Acosta (acostajeiison@gmail.com) | Rol: admin', '2026-02-07 01:02:12'),
	(30, 1, 'Usuario actualizado', '#4 | Gisvel Aray 2 (gisvelaray@gmail.com)', '2026-02-07 01:08:26'),
	(31, 1, 'Usuario actualizado', '#37 | GiviOperador (operador@buzz.com)', '2026-02-07 01:29:41'),
	(32, 1, 'Producto creado', '#14 | 9788445018538 - ', '2026-02-07 01:40:05'),
	(33, 1, 'Cliente creado', '#V-30535128 | S/C - Gisvel Aray', '2026-02-07 01:43:18'),
	(34, 1, 'Pedido creado', '#13 | Ref:  | Total: $387.00', '2026-02-07 01:45:45'),
	(35, 1, 'Pedido eliminado', '#13 | Ref: 4810541356', '2026-02-07 01:54:33'),
	(36, 1, 'Producto actualizado', '#14 | 9788445018538 - ', '2026-02-07 01:58:09'),
	(37, 1, 'Producto actualizado', '#14 | 9788445018538 - ', '2026-02-07 02:00:01'),
	(38, 1, 'Producto actualizado', '#14 | 9788445018538 - ', '2026-02-07 02:00:34'),
	(39, 1, 'Pedido creado', '#14 | Ref:  | Total: $129.00', '2026-02-07 02:05:17'),
	(40, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-02-07_05-49-54.sql | Tamaño: 0.14 MB', '2026-02-07 04:49:57'),
	(41, 1, 'Backup Manual', '{"archivo":"dbuzzbackup_2026-02-07_05-49-54.sql","tamanio_mb":0.14,"duracion_ms":3244}', '2026-02-07 04:49:57'),
	(42, 1, 'Producto actualizado', '#14 | 9788445018538 - ', '2026-02-07 05:50:26'),
	(43, 1, 'Requisición creada (auto stock bajo)', 'REQ-2026-0001', '2026-02-07 06:45:43'),
	(44, 1, 'Requisición actualizada', '#1', '2026-02-07 06:46:20'),
	(45, 1, 'Requisición creada', 'REQ-2026-0002 | 2 productos', '2026-02-07 06:46:49'),
	(46, 1, 'Requisición aprobada', '#1', '2026-02-07 16:26:46'),
	(47, 1, 'Producto actualizado', '#14 | 9788445018538 - ', '2026-02-14 02:32:02'),
	(48, 1, 'Usuario eliminado', '#63 | Juan Luis Chaurant (juanluischaurant@gmail.com)', '2026-03-26 03:16:17'),
	(49, 1, 'Usuario eliminado', '#63 | Juan Luis Chaurant (juanluischaurant@gmail.com)', '2026-03-26 03:16:49'),
	(50, 1, 'Usuario actualizado', '#63 | Juan Luis Chaurant (juanluischaurant@gmail.com)', '2026-03-26 03:17:35'),
	(51, 1, 'Usuario eliminado', '#63 | Juan Luis Chaurant (juanluischaurant@gmail.com)', '2026-03-26 03:17:44'),
	(52, 1, 'Pedido creado', '#18 | Ref:  | Total: $7.05', '2026-03-26 03:27:25'),
	(53, NULL, 'Backup realizado', 'Archivo: dbuzzbackup_2026-03-26_04-41-16.sql | Tamaño: 0.00 MB', '2026-03-26 03:41:17'),
	(54, 1, 'Usuario eliminado', '#4 | Gisvel Aray (gisvelaray@gmail.com)', '2026-03-26 03:42:11'),
	(55, 1, 'Usuario eliminado', '#36 | GiviSupervisor (supervisor@buzz.com)', '2026-03-26 03:42:13'),
	(56, 1, 'Usuario eliminado', '#37 | GiviOperador (operador@buzz.com)', '2026-03-26 03:42:15'),
	(57, 1, 'Usuario creado', '#103 | Tester Admin (testeradmin@gmail.com) | Rol: admin', '2026-03-26 03:43:38'),
	(58, 1, 'Usuario creado', '#104 | Tester Supervisor (testersup@gmail.com) | Rol: supervisor', '2026-03-26 03:44:07'),
	(59, 1, 'Usuario creado', '#105 | Tester Operador (testerop@buzz.com) | Rol: operador', '2026-03-26 03:44:45'),
	(60, 1, 'Usuario actualizado', '#104 | Tester Supervisor (testersup@gmail.com)', '2026-03-26 03:44:58'),
	(61, 1, 'Usuario actualizado', '#103 | Tester Admin (testeradmin@gmail.com)', '2026-03-26 03:45:03');

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
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs_login: ~108 rows (aproximadamente)
DELETE FROM `logs_login`;
INSERT INTO `logs_login` (`id`, `usuario_id`, `accion`, `detalles`, `fecha`) VALUES
	(1, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin', '2026-01-07 21:57:27'),
	(2, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin', '2026-01-07 21:58:11'),
	(3, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin', '2026-01-07 21:59:27'),
	(4, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin', '2026-01-07 22:01:35'),
	(5, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin', '2026-01-07 22:04:37'),
	(6, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:29:47'),
	(7, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:30:38'),
	(8, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:31:49'),
	(9, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:33:27'),
	(10, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:33:54'),
	(11, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:34:18'),
	(12, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:34:33'),
	(13, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:35:01'),
	(14, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:36:56'),
	(15, 1, 'Denegado', 'Máximo intento de contraseña, usuario bloqueado', '2026-01-07 22:37:50'),
	(16, 4, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-07 22:38:15'),
	(17, 1, 'Denegado', 'Intento de inicio de sesión con usuario bloqueado', '2026-01-07 22:42:57'),
	(18, 1, 'Denegado', 'Intento de inicio de sesión con usuario bloqueado', '2026-01-07 22:43:48'),
	(19, 1, 'Denegado', 'Intento de inicio de sesión con usuario bloqueado', '2026-01-07 22:43:58'),
	(20, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 22:44:18'),
	(21, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-07 23:58:51'),
	(22, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-12 20:34:12'),
	(23, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-13 01:22:10'),
	(24, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2026-01-13 21:17:25'),
	(25, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-13 21:17:32'),
	(26, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-18 00:18:24'),
	(27, 1, 'Exitoso', 'Inicio de sesión de admin', '2026-01-18 16:48:58'),
	(28, 1, 'Exitoso', 'Login de admin', '2026-01-18 17:50:35'),
	(29, 1, 'Exitoso', 'Login de admin', '2026-01-18 17:54:21'),
	(30, 1, 'Exitoso', 'Login de admin', '2026-01-18 18:15:06'),
	(31, 1, 'Exitoso', 'Login de admin', '2026-01-19 22:43:08'),
	(32, 1, 'Exitoso', 'Login de admin', '2026-01-20 23:27:04'),
	(33, 1, 'Exitoso', 'Login de admin', '2026-01-21 01:15:26'),
	(34, 1, 'Exitoso', 'Login de admin', '2026-01-23 01:55:23'),
	(35, 1, 'Exitoso', 'Login de admin', '2026-01-28 00:43:58'),
	(36, 1, 'Exitoso', 'Login de admin', '2026-01-28 00:52:25'),
	(37, 1, 'Exitoso', 'Login de admin', '2026-01-28 02:04:23'),
	(38, 4, 'Exitoso', 'Login de operador', '2026-01-28 02:08:48'),
	(39, 1, 'Exitoso', 'Login de admin', '2026-01-28 02:17:27'),
	(40, 1, 'Exitoso', 'Login de admin', '2026-01-28 02:56:14'),
	(41, 1, 'Exitoso', 'Login de admin', '2026-01-28 15:30:53'),
	(42, 1, 'Exitoso', 'Login de admin', '2026-01-28 16:40:20'),
	(43, 1, 'Denegado', 'Contraseña incorrecta', '2026-01-28 18:02:46'),
	(44, 1, 'Exitoso', 'Login de admin', '2026-01-28 18:02:59'),
	(45, 1, 'Exitoso', 'Login de admin', '2026-01-28 18:23:53'),
	(46, 1, 'Exitoso', 'Login de admin', '2026-02-02 20:52:10'),
	(47, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-04 19:16:52'),
	(48, 1, 'Exitoso', 'Login de admin', '2026-02-04 19:17:17'),
	(49, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-04 20:51:25'),
	(50, 1, 'Exitoso', 'Login de admin', '2026-02-04 20:51:42'),
	(51, 1, 'Exitoso', 'Login de admin', '2026-02-04 20:59:36'),
	(52, 1, 'Exitoso', 'Login de admin', '2026-02-05 22:26:41'),
	(53, 1, 'Exitoso', 'Login de admin', '2026-02-06 02:06:18'),
	(54, 1, 'Exitoso', 'Login de admin', '2026-02-07 00:35:44'),
	(55, NULL, 'Denegado', 'Intento con email no existente: sahory.gaaj@gmail.com', '2026-02-07 01:23:37'),
	(56, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:25:53'),
	(57, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:25:55'),
	(58, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:25:56'),
	(59, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:25:57'),
	(60, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:25:59'),
	(61, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:26:00'),
	(62, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:26:01'),
	(63, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:26:02'),
	(64, 37, 'Denegado', 'Contraseña incorrecta', '2026-02-07 01:26:04'),
	(65, 37, 'Denegado', 'Usuario bloqueado por máximos intentos', '2026-02-07 01:26:05'),
	(66, 1, 'Exitoso', 'Login de admin', '2026-02-07 01:28:38'),
	(67, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-07 03:10:35'),
	(68, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-07 03:10:38'),
	(69, 1, 'Exitoso', 'Login de admin', '2026-02-07 04:49:31'),
	(70, 1, 'Exitoso', 'Login de admin', '2026-02-07 05:49:54'),
	(71, 1, 'Exitoso', 'Login de admin', '2026-02-07 14:13:06'),
	(72, NULL, 'Exitoso', 'Login de operador', '2026-02-07 15:32:00'),
	(73, NULL, 'Denegado', 'Usuario desactivado', '2026-02-07 15:32:00'),
	(74, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-07 19:24:16'),
	(75, 1, 'Exitoso', 'Login de admin', '2026-02-07 19:24:37'),
	(76, 1, 'Exitoso', 'Login de admin', '2026-02-10 22:51:59'),
	(77, 1, 'Exitoso', 'Login de admin', '2026-02-11 00:52:21'),
	(78, 1, 'Exitoso', 'Login de admin', '2026-02-12 02:28:16'),
	(79, 1, 'Exitoso', 'Login de admin', '2026-02-13 23:23:10'),
	(80, 1, 'Exitoso', 'Login de admin', '2026-02-15 02:12:28'),
	(81, 1, 'Exitoso', 'Login de admin', '2026-02-19 15:14:07'),
	(82, 1, 'Exitoso', 'Login de admin', '2026-02-19 15:45:08'),
	(83, 1, 'Exitoso', 'Login de admin', '2026-02-19 18:18:55'),
	(84, 1, 'Exitoso', 'Login de admin', '2026-02-19 20:22:52'),
	(85, 1, 'Exitoso', 'Login de admin', '2026-02-20 05:44:47'),
	(86, 1, 'Exitoso', 'Login de admin', '2026-02-20 05:51:07'),
	(87, 37, 'Exitoso', 'Login de operador', '2026-02-20 05:51:29'),
	(88, 36, 'Exitoso', 'Login de supervisor', '2026-02-20 05:51:49'),
	(89, 1, 'Exitoso', 'Login de admin', '2026-02-20 05:58:08'),
	(90, 1, 'Exitoso', 'Login de admin', '2026-02-21 00:41:27'),
	(91, 1, 'Exitoso', 'Login de admin', '2026-02-21 00:42:36'),
	(92, 1, 'Exitoso', 'Login de admin', '2026-02-21 01:27:36'),
	(93, 1, 'Denegado', 'Contraseña incorrecta', '2026-02-25 02:20:38'),
	(94, 1, 'Exitoso', 'Login de admin', '2026-02-25 02:21:37'),
	(95, 1, 'Exitoso', 'Login de admin', '2026-02-25 02:23:23'),
	(96, 1, 'Exitoso', 'Login de admin', '2026-03-02 03:31:15'),
	(97, 1, 'Exitoso', 'Login de admin', '2026-03-17 21:51:57'),
	(98, NULL, 'Denegado', 'Intento con email no existente: admin@buzzdm.com', '2026-03-17 22:29:26'),
	(99, NULL, 'Denegado', 'Intento con email no existente: admin@gmail.com', '2026-03-17 22:29:59'),
	(100, 1, 'Exitoso', 'Login de admin', '2026-03-17 23:38:35'),
	(101, 1, 'Exitoso', 'Login de admin', '2026-03-18 00:01:41'),
	(102, 1, 'Exitoso', 'Login de admin', '2026-03-18 01:28:37'),
	(103, 1, 'Exitoso', 'Login de admin', '2026-03-18 01:51:28'),
	(104, 1, 'Exitoso', 'Login de admin', '2026-03-19 01:13:34'),
	(105, NULL, 'Denegado', 'Intento con email no existente: esfaesafe@cm.com', '2026-03-19 01:16:27'),
	(106, NULL, 'Denegado', 'Intento con email no existente: sahefory.gaaj@gmail.com', '2026-03-19 01:33:03'),
	(107, 1, 'Exitoso', 'Login de admin', '2026-03-19 01:33:36'),
	(108, NULL, 'Denegado', 'Intento con email no existente: givioperador@gmail.com', '2026-03-19 01:36:45'),
	(109, 37, 'Exitoso', 'Login de operador', '2026-03-19 01:37:04'),
	(110, 36, 'Exitoso', 'Login de supervisor', '2026-03-19 01:40:49'),
	(111, 37, 'Exitoso', 'Login de operador', '2026-03-19 01:47:43'),
	(112, 36, 'Exitoso', 'Login de supervisor', '2026-03-20 02:02:24'),
	(113, 37, 'Exitoso', 'Login de operador', '2026-03-20 02:02:50'),
	(114, 1, 'Exitoso', 'Login de admin', '2026-03-20 02:06:18'),
	(115, 1, 'Denegado', 'Contraseña incorrecta', '2026-03-20 02:19:17'),
	(116, 1, 'Exitoso', 'Login de admin', '2026-03-23 03:40:43'),
	(117, 37, 'Exitoso', 'Login de operador', '2026-03-24 01:53:57'),
	(118, 36, 'Exitoso', 'Login de supervisor', '2026-03-24 01:58:43'),
	(119, 37, 'Exitoso', 'Login de operador', '2026-03-24 02:43:01'),
	(120, 1, 'Exitoso', 'Login de admin', '2026-03-24 02:43:35'),
	(121, 1, 'Exitoso', 'Login de admin', '2026-03-24 21:30:49'),
	(122, 37, 'Exitoso', 'Login de operador', '2026-03-25 20:36:58'),
	(123, NULL, 'Denegado', 'Intento con email no existente: test@test.com', '2026-03-25 20:37:41'),
	(124, 1, 'Exitoso', 'Login de admin', '2026-03-26 03:14:55'),
	(125, 1, 'Exitoso', 'Login de admin', '2026-03-26 11:59:07'),
	(126, 1, 'Exitoso', 'Login de admin', '2026-03-29 00:54:35'),
	(127, 1, 'Exitoso', 'Login de admin', '2026-03-29 01:19:35'),
	(128, NULL, 'Denegado', 'Intento con email no existente: testeradmin@gmail.com', '2026-03-29 01:38:51'),
	(129, NULL, 'Denegado', 'Intento con email no existente: esteradmin@gmail.com', '2026-03-29 01:39:59'),
	(130, 103, 'Exitoso', 'Login de admin', '2026-03-29 01:40:32');

-- Volcando estructura para tabla dbuzz.marcas
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.marcas: ~8 rows (aproximadamente)
DELETE FROM `marcas`;
INSERT INTO `marcas` (`id`, `nombre`, `descripcion`, `sitio_web`) VALUES
	(1, 'Pilot', 'Bolígrafos y roller de calidad', 'https://www.pilot.com'),
	(2, 'Stabilo', 'Marcadores y resaltadores', 'https://www.stabilo.com'),
	(3, 'Faber-Castell', 'Lápices y material artístico', 'https://www.faber-castell.com'),
	(4, 'Staedtler', 'Gomas y herramientas de dibujo', 'https://www.staedtler.com'),
	(5, 'Cansons', 'Papeles y cartulinas para arte', 'https://www.canson.com'),
	(6, 'Tombow', 'Material para pintura y acuarela', 'https://www.tombow.com'),
	(7, 'Ediciones Minotauro', 'Minotauro es el sello de referencia de literatura fantástica, de terror y ciencia ficción. Destaca por autores como J.R.R. Tolkien y el Premio Minotauro.', 'http://www.planetadelibros.com/editorial/minotauro/21'),
	(9, 'Morgenstern Books', NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.movimientos_inventario: ~12 rows (aproximadamente)
DELETE FROM `movimientos_inventario`;
INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `usuario_id`, `tipo_movimiento`, `cantidad`, `justificacion`, `cargo_id`, `almacen_id`, `ubicacion_id`, `stock_anterior`, `stock_actual`, `fecha`) VALUES
	(1, 2, 100, 'entrada', 120, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 120, '2025-12-25 14:00:00'),
	(2, 3, 100, 'entrada', 60, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 60, '2025-12-25 14:01:00'),
	(3, 4, 100, 'entrada', 200, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 200, '2025-12-25 14:02:00'),
	(4, 5, 100, 'entrada', 40, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 40, '2025-12-25 14:03:00'),
	(5, 6, 100, 'entrada', 150, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 150, '2025-12-25 14:04:00'),
	(6, 7, 100, 'entrada', 300, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 300, '2025-12-25 14:05:00'),
	(7, 8, 100, 'entrada', 80, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 80, '2025-12-25 14:06:00'),
	(8, 9, 100, 'entrada', 25, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 25, '2025-12-25 14:07:00'),
	(9, 10, 100, 'entrada', 10, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 10, '2025-12-25 14:08:00'),
	(10, 11, 100, 'entrada', 50, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 50, '2025-12-25 14:09:00'),
	(11, 12, 100, 'entrada', 15, 'Ingreso inicial de stock', NULL, NULL, NULL, 0, 15, '2025-12-25 14:10:00'),
	(12, 13, 100, 'entrada', 0, 'Sin stock al momento del mock', NULL, NULL, NULL, 0, 0, '2025-12-25 14:11:00');

-- Volcando estructura para tabla dbuzz.notas_adjuntos
DROP TABLE IF EXISTS `notas_adjuntos`;
CREATE TABLE IF NOT EXISTS `notas_adjuntos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `url` text NOT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  CONSTRAINT `notas_adjuntos_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.notas_adjuntos: ~3 rows (aproximadamente)
DELETE FROM `notas_adjuntos`;
INSERT INTO `notas_adjuntos` (`id`, `nota_id`, `nombre`, `url`, `creado_at`) VALUES
	(7, 10, 'Requerimiento_Colegio.pdf', 'https://example.com/files/req_colegio_2026.pdf', '2026-03-19 01:41:25'),
	(8, 11, 'Guia_Despacho_123.jpg', 'https://example.com/img/guia_123.jpg', '2026-03-19 01:41:38');

-- Volcando estructura para tabla dbuzz.notas_checklists
DROP TABLE IF EXISTS `notas_checklists`;
CREATE TABLE IF NOT EXISTS `notas_checklists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `tarea` varchar(255) NOT NULL,
  `completada` tinyint(1) DEFAULT 0,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  CONSTRAINT `notas_checklists_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.notas_checklists: ~5 rows (aproximadamente)
DELETE FROM `notas_checklists`;
INSERT INTO `notas_checklists` (`id`, `nota_id`, `tarea`, `completada`, `orden`) VALUES
	(15, 10, 'Contar stock de resmas disponible', 1, 0),
	(16, 10, 'Pedir cotización especial a Distribuidora Papel', 0, 1),
	(17, 10, 'Enviar PDF al cliente', 0, 2),
	(18, 11, 'Llamar a MRW', 1, 0),
	(19, 11, 'Confirmar recepción con el cliente', 0, 1);

-- Volcando estructura para tabla dbuzz.notas_items_inventario
DROP TABLE IF EXISTS `notas_items_inventario`;
CREATE TABLE IF NOT EXISTS `notas_items_inventario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nota_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL,
  `cantidad` int(10) unsigned DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `nota_id` (`nota_id`),
  KEY `producto_id` (`producto_id`),
  CONSTRAINT `notas_items_inventario_ibfk_1` FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_items_inventario_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.notas_items_inventario: ~3 rows (aproximadamente)
DELETE FROM `notas_items_inventario`;
INSERT INTO `notas_items_inventario` (`id`, `nota_id`, `producto_id`, `cantidad`) VALUES
	(3, 12, 4, 100),
	(5, 10, 11, 50),
	(6, 10, 2, 100);

-- Volcando estructura para tabla dbuzz.notas_kanban
DROP TABLE IF EXISTS `notas_kanban`;
CREATE TABLE IF NOT EXISTS `notas_kanban` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  CONSTRAINT `notas_kanban_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notas_kanban_ibfk_2` FOREIGN KEY (`pedido_vinculado_id`) REFERENCES `pedidos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.notas_kanban: ~3 rows (aproximadamente)
DELETE FROM `notas_kanban`;
INSERT INTO `notas_kanban` (`id`, `titulo`, `cliente_nombre`, `telefono`, `email`, `contenido`, `estado`, `prioridad`, `orden`, `pedido_vinculado_id`, `fecha_entrega`, `etiquetas`, `miembros`, `usuario_id`, `creado_at`, `actualizado_at`) VALUES
	(10, 'Presupuesto para Colegio San Ignacio', 'Colegio San Ignacio', '', '', 'Solicitan presupuesto de 50 resmas y 100 cuadernos para el inicio de clases.', 'pendiente', 'alta', 1, NULL, NULL, '[]', '[{"id":37,"name":"GiviOperador"}]', 1, '2026-01-07 12:30:00', '2026-03-19 01:41:25'),
	(11, 'Seguimiento de pedido V-10000002', 'José Pérez', '', '', 'El cliente llamó preguntando por su pedido de marcadores. Revisar con el transportista.', 'seguimiento', 'media', 1, NULL, NULL, '[]', '[]', 1, '2026-01-07 13:15:00', '2026-03-19 01:39:14'),
	(12, 'Reabastecer stock de Pilot G2', NULL, NULL, NULL, 'Estamos quedando bajos en stock del Bolígrafo Pilot G2 negro. Contactar a Distribuidora Papel.', 'resuelto', 'baja', 1, NULL, NULL, NULL, NULL, 1, '2026-01-07 14:00:00', NULL);

-- Volcando estructura para tabla dbuzz.notificaciones
DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE IF NOT EXISTS `notificaciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `tipo` enum('info','success','warning','error','task_assign','low_stock','security') DEFAULT 'info',
  `titulo` varchar(100) NOT NULL,
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `creado_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.notificaciones: ~9 rows (aproximadamente)
DELETE FROM `notificaciones`;
INSERT INTO `notificaciones` (`id`, `usuario_id`, `tipo`, `titulo`, `mensaje`, `enlace`, `leido`, `creado_at`) VALUES
	(9, 63, 'security', 'Usuario Bloqueado', 'Usuario operador@buzz.com bloqueado tras 10 intentos fallidos.', 'admin', 0, '2026-02-06 21:26:05'),
	(10, 101, 'security', 'Usuario Bloqueado', 'Usuario operador@buzz.com bloqueado tras 10 intentos fallidos.', 'admin', 0, '2026-02-06 21:26:05'),
	(12, 36, 'low_stock', 'Stock Crítico Detectado', 'Hay 2 productos con existencias críticas. Se recomienda reabastecer.', 'inventario.php?filter=low_stock', 0, '2026-03-23 21:58:44'),
	(13, 37, 'task_assign', 'Nueva Tarea Asignada', 'Te han asignado la tarea: sfasesfesfaes', 'todo.php?id=14', 1, '2026-03-18 21:35:15'),
	(14, 37, 'task_assign', 'Tarea Actualizada', 'Se ha actualizado una tarea asignada: Presupuesto para Colegio San Ignacio', 'todo.php?id=10', 1, '2026-03-18 21:41:25'),
	(16, 1, 'low_stock', 'Stock Crítico Detectado', 'Hay 3 productos con existencias críticas. Se recomienda reabastecer.', 'inventario.php?filter=low_stock', 0, '2026-03-28 21:19:36'),
	(17, 101, 'task_assign', 'Nueva Tarea Asignada', 'Te han asignado la tarea: sobrino de tarek', 'todo.php?id=15', 0, '2026-03-24 18:20:35'),
	(18, 101, 'task_assign', 'Tarea Actualizada', 'Se ha actualizado una tarea asignada: sobrino de tarek', 'todo.php?id=15', 0, '2026-03-24 18:21:49'),
	(19, 103, 'low_stock', 'Stock Crítico Detectado', 'Hay 3 productos con existencias críticas. Se recomienda reabastecer.', 'inventario.php?filter=low_stock', 0, '2026-03-28 21:40:33');

-- Volcando estructura para tabla dbuzz.paquetes
DROP TABLE IF EXISTS `paquetes`;
CREATE TABLE IF NOT EXISTS `paquetes` (
  `id` smallint(5) unsigned NOT NULL,
  `codigo` varchar(32) NOT NULL COMMENT 'Clave estable en minúsculas: deluxe, basico, cosplayer',
  `nombre` varchar(64) NOT NULL COMMENT 'Etiqueta para reportes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.paquetes: ~3 rows (aproximadamente)
DELETE FROM `paquetes`;
INSERT INTO `paquetes` (`id`, `codigo`, `nombre`) VALUES
	(1, 'deluxe', 'Deluxe'),
	(2, 'basico', 'Básico'),
	(3, 'cosplayer', 'Cosplayer');

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.password_reset_tokens: ~5 rows (aproximadamente)
DELETE FROM `password_reset_tokens`;
INSERT INTO `password_reset_tokens` (`id`, `usuario_id`, `token`, `expiracion`, `usado`, `creado_at`) VALUES
	(1, 4, 'b5fcd2e5eb0989c84763f2407250fbedf7b942f56d63e91291cdffcc45b7b6c2', '2026-01-18 23:50:55', 1, '2026-01-18 17:50:55'),
	(2, 4, '3c0bf0e9c6f6a558ca3dbe50d07bd5c4040c6cb2b1ab4a4274f47e000f2b59d3', '2026-01-18 23:51:47', 1, '2026-01-18 17:51:47'),
	(3, 4, '885f0d23f663469025159ed6b248e72da8b84f0fa19b3b7cca17ba51fb2f422d', '2026-01-18 23:53:20', 1, '2026-01-18 17:53:20'),
	(4, 4, '73398ea8a2b017f1192b9e74cf737c1b38f138ebd920d9de02a35ad245ef7d5b', '2026-01-28 08:04:36', 1, '2026-01-28 02:04:36'),
	(5, 4, 'bd887400d8af4cb634ed09cdc605e0123c836493ba7695931ca43b7332159494', '2026-01-28 08:07:59', 1, '2026-01-28 02:07:59');

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
  CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.pedidos: ~14 rows (aproximadamente)
DELETE FROM `pedidos`;
INSERT INTO `pedidos` (`id`, `cliente_id`, `referencia`, `fecha_creacion`, `total`, `estado_pago`, `estado_envio`, `direccion_envio`, `notas_envio`, `creado_por`, `actualizado_at`, `tracking_number`, `estimated_delivery_date`, `deleted_at`) VALUES
	(2, 'V-10000001', 'REF-LBR-0002', '2025-12-25 15:00:00', 9.70, 'pagado', 'pendiente', 'Calle Comercio 123, Caracas', 'Entrega mañana', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(3, 'V-10000002', 'REF-LBR-0003', '2025-12-24 19:30:00', 10.10, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(4, 'V-10000003', 'REF-LBR-0004', '2025-10-05 14:15:00', 115.00, 'pagado', 'recibido', 'C.C. Centro, Local 12', 'Pedido mayorista', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(5, 'V-10000001', 'REF-LBR-0005', '2025-10-20 13:00:00', 13.50, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(6, 'V-10000002', 'REF-LBR-0006', '2025-11-11 18:45:00', 10.80, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(7, 'V-10000001', 'REF-LBR-0007', '2025-11-25 20:10:00', 17.20, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(8, 'V-10000003', 'REF-LBR-0008', '2025-12-01 16:00:00', 30.50, 'pagado', 'pendiente', 'C.C. Centro, Local 12', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(9, 'V-10000002', 'REF-LBR-0009', '2025-12-05 12:30:00', 11.75, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(10, 'V-10000001', 'REF-LBR-0010', '2025-12-10 22:20:00', 16.00, 'pagado', 'pendiente', 'Calle Comercio 123, Caracas', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(11, 'V-10000003', 'REF-LBR-0011', '2025-12-20 13:05:00', 38.00, 'pagado', 'enviado', 'C.C. Centro, Local 12', '', 100, '2026-01-07 21:01:09', NULL, NULL, NULL),
	(12, 'V-10000002', '5113929463', '2026-01-18 05:46:00', 50.00, 'pagado', 'enviado', 'wafesafsa', 'faefsfaes', NULL, '2026-01-18 18:06:53', 'TRK759613029', NULL, NULL),
	(13, 'V-30535128', '4810541356', '2026-02-07 06:43:00', 387.00, 'pagado', 'pendiente', 'UPTJAA sede Halliburton', 'Requiere empaque aprueba de agua para que el libro no se dañe', NULL, '2026-02-07 01:54:33', NULL, NULL, '2026-02-07 01:54:33'),
	(14, 'V-30535128', '9408851176', '2026-02-06 07:00:00', 129.00, 'pagado', 'pendiente', 'UPTJAA sede Halliburton', 'El cliente requiere que el empaque del libro sea plástico para evitar daños por agua al libro debido al clima', NULL, NULL, NULL, NULL, NULL),
	(18, 'V-30535128', '8363873304', '2026-03-26 08:26:00', 7.05, 'pagado', 'pendiente', 'luisa caceres c/c arismendi', 'wa', NULL, NULL, NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=285 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos de acceso por rol a páginas del sistema';

-- Volcando datos para la tabla dbuzz.permisos_rol: ~132 rows (aproximadamente)
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
	(93, 'operador', 'product_actions.php', '2026-01-07 02:44:23'),
	(94, 'admin', 'todo.php', '2026-01-07 03:49:24'),
	(95, 'admin', 'notas_actions.php', '2026-01-07 03:49:24'),
	(96, 'supervisor', 'todo.php', '2026-01-07 03:49:24'),
	(97, 'supervisor', 'notas_actions.php', '2026-01-07 03:49:24'),
	(98, 'operador', 'todo.php', '2026-01-07 03:49:24'),
	(99, 'operador', 'notas_actions.php', '2026-01-07 03:49:24'),
	(124, 'admin', 'catalogos.php', '2026-01-07 21:37:49'),
	(125, 'supervisor', 'catalogos.php', '2026-01-07 21:37:49'),
	(126, 'admin', 'download_template.php', '2026-01-12 21:41:12'),
	(127, 'supervisor', 'download_template.php', '2026-01-12 21:41:12'),
	(128, 'admin', 'proveedores_search.php', '2026-01-13 02:00:59'),
	(129, 'supervisor', 'proveedores_search.php', '2026-01-13 02:00:59'),
	(130, 'admin', 'api_products.php', '2026-01-18 00:25:57'),
	(131, 'admin', 'api_clientes.php', '2026-01-18 00:25:57'),
	(132, 'admin', 'api_cargos.php', '2026-01-18 00:25:57'),
	(133, 'supervisor', 'api_products.php', '2026-01-18 00:25:57'),
	(134, 'supervisor', 'api_clientes.php', '2026-01-18 00:25:57'),
	(135, 'supervisor', 'api_cargos.php', '2026-01-18 00:25:57'),
	(183, 'admin', 'api_notifications.php', '2026-01-18 00:34:42'),
	(184, 'admin', 'api_stats.php', '2026-01-18 00:34:42'),
	(225, 'supervisor', 'api_notifications.php', '2026-01-18 00:34:42'),
	(226, 'supervisor', 'api_stats.php', '2026-01-18 00:34:42'),
	(244, 'operador', 'api_notifications.php', '2026-01-18 00:34:42'),
	(245, 'operador', 'api_stats.php', '2026-01-18 00:34:42'),
	(246, 'admin', 'api_orders.php', '2026-01-18 00:46:01'),
	(247, 'supervisor', 'api_orders.php', '2026-01-18 00:46:01'),
	(248, 'operador', 'api_orders.php', '2026-01-18 00:46:01'),
	(249, 'admin', 'api_warehouses.php', '2026-01-18 02:48:53'),
	(250, 'supervisor', 'api_warehouses.php', '2026-01-18 02:48:53'),
	(251, 'operador', 'api_warehouses.php', '2026-01-18 02:48:53'),
	(252, 'admin', 'api_import.php', '2026-01-18 02:53:52'),
	(253, 'supervisor', 'api_import.php', '2026-01-18 02:53:52'),
	(254, 'admin', 'api_notes.php', '2026-01-18 03:06:12'),
	(255, 'supervisor', 'api_notes.php', '2026-01-18 03:06:12'),
	(256, 'operador', 'api_notes.php', '2026-01-18 03:06:12'),
	(257, 'admin', 'api_suppliers.php', '2026-01-18 03:19:57'),
	(258, 'supervisor', 'api_suppliers.php', '2026-01-18 03:19:57'),
	(259, 'admin', 'api_settings.php', '2026-01-18 03:34:55'),
	(260, 'admin', 'api_backups.php', '2026-01-18 03:40:43'),
	(261, 'admin', 'api_users.php', '2026-01-18 16:47:34'),
	(262, 'admin', 'api_catalogs.php', '2026-01-18 17:34:31'),
	(263, 'supervisor', 'api_catalogs.php', '2026-01-18 17:34:31'),
	(264, 'admin', 'api_shipments.php', '2026-01-18 18:05:42'),
	(265, 'admin', 'api_locations.php', '2026-01-18 18:05:42'),
	(266, 'admin', 'api_kits.php', '2026-01-18 18:05:42'),
	(267, 'supervisor', 'api_shipments.php', '2026-01-18 18:05:42'),
	(268, 'supervisor', 'api_locations.php', '2026-01-18 18:05:42'),
	(269, 'supervisor', 'api_kits.php', '2026-01-18 18:05:42'),
	(270, 'operador', 'api_shipments.php', '2026-01-18 18:05:42'),
	(271, 'admin', 'transferencias.php', '2026-02-04 19:19:51'),
	(272, 'admin', 'api_transferencias.php', '2026-02-04 19:19:51'),
	(273, 'supervisor', 'transferencias.php', '2026-02-04 19:19:51'),
	(274, 'supervisor', 'api_transferencias.php', '2026-02-04 19:19:51'),
	(279, 'admin', 'requisiciones.php', '2026-02-07 06:30:24'),
	(280, 'admin', 'api_requisiciones.php', '2026-02-07 06:30:24'),
	(281, 'supervisor', 'requisiciones.php', '2026-02-07 06:30:24'),
	(282, 'supervisor', 'api_requisiciones.php', '2026-02-07 06:30:24'),
	(283, 'operador', 'requisiciones.php', '2026-02-07 06:30:24'),
	(284, 'operador', 'api_requisiciones.php', '2026-02-07 06:30:24');

-- Volcando estructura para tabla dbuzz.personas
DROP TABLE IF EXISTS `personas`;
CREATE TABLE IF NOT EXISTS `personas` (
  `id` int(10) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `telefono` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.personas: ~83 rows (aproximadamente)
DELETE FROM `personas`;
INSERT INTO `personas` (`id`, `nombre`, `telefono`) VALUES
	(65, 'Angélica Guerra', '04248986743'),
	(66, 'Yuliana medina', '04248000991'),
	(67, 'Fabiana Prado', '04147858059'),
	(68, 'Fabiana Prado', '04147858060'),
	(69, 'Diego Sebastian', '04128589045'),
	(70, 'Victor Bueno', '04248601714'),
	(71, 'Manuel Delvalle', '04148224957'),
	(72, 'Gilsy Romero', '04148224957'),
	(73, 'juan Rodríguez', '04124714004'),
	(74, 'Elias Arismendi', '04248201263'),
	(75, 'Maria Villarroel', '04248288176'),
	(76, 'Susej Rodriguez', '04248804628'),
	(77, 'Jesus Freitez', '04248526364'),
	(78, 'Manuel Barcelo', '04145956125'),
	(79, 'Gabriela Alvarez', '04249075944'),
	(80, 'Marcos Andrade', '04148282727'),
	(81, 'Argdenis Loreto', '04128764761'),
	(82, 'Mathias Loreto', '04128764761'),
	(83, 'Noeluis Santaella', '04248200759'),
	(84, 'Izzmar Fermin', '04248541368'),
	(85, 'Jacinto Sanchez', '04248741554'),
	(86, 'Luzmar Villarroel', '04248778757'),
	(87, 'Arianna Torres', '04248778757'),
	(88, 'Angel Araguatamay', '04248778757'),
	(89, 'Rohinner', '04124033961'),
	(90, 'Isberit', '04124033961'),
	(91, 'Mariana Wansa', '04248538958'),
	(92, 'Karla Quiaro', '04141855255'),
	(93, 'Gabriela Ramos', '04167821796'),
	(94, 'Abel Mauricio', '04123932958'),
	(95, 'Oliver José González', '04248752033'),
	(96, 'Sebastian Ramos', '04121915568'),
	(97, 'Lucian Pérez', '04121095715'),
	(98, 'Natán Finol', '04248220744'),
	(99, 'Natán Finol', '04248220745'),
	(100, 'Alexander Rodríguez', '04126982098'),
	(101, 'Yainira Gamboa', '04248635312'),
	(102, 'Isaac Rendón', '04127341984'),
	(103, 'Ismarys Marcano', '04248933600'),
	(104, 'Scarlet Guedez', '04248698974'),
	(105, 'Maria Bellorin', '04148011141'),
	(106, 'Ruth Aguilera', '04148502999'),
	(107, 'Victor Perez', '04147987491'),
	(108, 'Pedro Pinto', '04248219510'),
	(109, 'gabrielis basanta', '04122101669'),
	(110, 'gabrielis basanta', '04122101670'),
	(111, 'gabrielis basanta', '04122101671'),
	(112, 'Julud Refaie', '04248856352'),
	(113, 'Mohamed Al Refaie', '04248856352'),
	(114, 'Amin Al Refaie', '04248856352'),
	(115, 'Miliangel Morillo', '04128798380'),
	(116, 'Gabriela Castillo', '04121904074'),
	(117, 'José Castillo', '04121094774'),
	(118, 'Nicole Reinoza', '04248910440'),
	(119, 'Leonardo Feng', '04248502299'),
	(120, 'Argenis Urbaneja', '04160605490'),
	(121, 'Anthony', '04161873105'),
	(122, 'Lucciano Lista', '04248541841'),
	(123, 'Sofía Lugo', '04248102007'),
	(124, 'Daniel Yelamo', '04248649982'),
	(125, 'testeando 1', '04248900938'),
	(126, 'testo', '04248883344'),
	(127, 'Juan Chaurant', '+583055009199'),
	(128, 'José Cordova', '04248673827'),
	(129, 'Sebastian Cordova', '04248673827'),
	(130, 'Rubén Psas', '04262890158'),
	(131, 'Johan carrión', '04248439375'),
	(132, 'Ana Isabel Velasquez', '04248885727'),
	(133, 'Guillermo aguilera', '04147987491'),
	(134, 'Sergio Belisario', '04248008228'),
	(135, 'Mar', '04248008228'),
	(136, 'Sergio Fabriccio', '04248008228'),
	(137, 'Leobaldo Gil', '04248849139'),
	(138, 'Mayloris Colmenares', '04248008228'),
	(139, 'Dam Rojas', '04120812112'),
	(140, 'Andrea Estanga', '04121810021'),
	(141, 'Andrea Estanga', '04127810021'),
	(142, 'Javier Diaz', '04248194550'),
	(143, 'Ramses Lira', '04147955686'),
	(144, 'Luis Narváez', '04248552765'),
	(145, 'Samuel Corona', '04123624832'),
	(146, 'José Sosa', '04248586998'),
	(147, 'Roynel', '04148288940');

-- Volcando estructura para tabla dbuzz.precios_proveedor
DROP TABLE IF EXISTS `precios_proveedor`;
CREATE TABLE IF NOT EXISTS `precios_proveedor` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `producto_id` int(20) unsigned NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `precio_compra` decimal(12,4) NOT NULL,
  `fecha_registro` date NOT NULL,
  `cargo_id` int(10) unsigned DEFAULT NULL COMMENT 'Cargo donde se registró la compra',
  `es_ultimo` tinyint(1) DEFAULT 1 COMMENT 'Indica si es el precio más reciente para este producto/proveedor',
  `notas` varchar(255) DEFAULT NULL,
  `creado_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_producto_proveedor_fecha` (`producto_id`,`proveedor_id`,`fecha_registro`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_registro`),
  KEY `idx_es_ultimo` (`es_ultimo`),
  KEY `fk_precio_cargo` (`cargo_id`),
  CONSTRAINT `fk_precio_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_precio_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_precio_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de precios de compra por producto y proveedor';

-- Volcando datos para la tabla dbuzz.precios_proveedor: ~0 rows (aproximadamente)
DELETE FROM `precios_proveedor`;

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.productos: ~14 rows (aproximadamente)
DELETE FROM `productos`;
INSERT INTO `productos` (`id`, `sku`, `nombre`, `descripcion`, `precio`, `costo_promedio`, `costo_ultimo`, `stock`, `min_stock`, `estado`, `tipo_producto`, `maneja_stock`, `creado_at`, `actualizado_at`, `categoria`, `marca_id`, `proveedor_id`, `modelo`, `color`, `tamanio`, `medidas`, `peso_kg`, `material`, `unidad_venta`, `cantidad_presentacion`, `deleted_at`, `producto_padre_id`, `es_kit_virtual`, `variante_atributo`) VALUES
	(2, 'LBR-0001', 'Cuaderno universitario 100 hojas', 'Cuaderno raya 100 hojas 21x27 cm', 2.50, 0.0000, 0.0000, 99, 20, 'activo', 'fisico', 1, '2025-12-25 14:00:00', '2026-03-26 03:27:25', 1, 5, 1, '', '', '', '', NULL, '', 'UND', 1, NULL, NULL, 0, ''),
	(3, 'LBR-0002', 'Cuaderno de dibujo A4 40 hojas', 'Papel para dibujo 120 g/m2, 40 hojas', 5.00, 0.0000, 0.0000, 60, 10, 'activo', 'fisico', 1, '2025-12-25 14:01:00', NULL, 4, 5, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(4, 'LBR-0003', 'Bolígrafo Pilot G2 negro', 'Bolígrafo punta 0.7 mm, tinta gel', 1.20, 0.0000, 0.0000, 200, 30, 'activo', 'fisico', 1, '2025-12-25 14:02:00', '2026-02-04 04:03:08', 1, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(5, 'LBR-0004', 'Set Marcadores Stabilo 6 colores', 'Marcadores punta fina, set x6', 8.50, 0.0000, 0.0000, 40, 10, 'activo', 'fisico', 1, '2025-12-25 14:03:00', NULL, 4, 2, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(6, 'LBR-0005', 'Lápiz HB Faber-Castell (12)', 'Pack de 12 lápices HB', 3.75, 0.0000, 0.0000, 149, 20, 'activo', 'fisico', 1, '2025-12-25 14:04:00', '2026-03-26 03:27:25', 2, 3, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(7, 'LBR-0006', 'Goma de borrar Staedtler', 'Goma blanda sin PVC', 0.80, 0.0000, 0.0000, 299, 50, 'activo', 'fisico', 1, '2025-12-25 14:05:00', '2026-03-26 03:27:25', 1, 4, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(8, 'LBR-0007', 'Pegamento en barra 36g', 'Pegamento sólido sin ácido', 1.10, 0.0000, 0.0000, 80, 20, 'activo', 'fisico', 1, '2025-12-25 14:06:00', '2026-02-04 04:03:11', 5, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(9, 'LBR-0008', 'Cartulina Canson A4 (pack 50)', 'Cartulina para arte, 180 g/m2, pack 50 hojas', 15.00, 0.0000, 0.0000, 25, 10, 'activo', 'fisico', 1, '2025-12-25 14:07:00', NULL, 4, 5, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(10, 'LBR-0009', 'Archivador A4 plástico', 'Archivador plástico A4 con anillas', 4.25, 0.0000, 0.0000, 10, 20, 'stock_bajo', 'fisico', 1, '2025-12-25 14:08:00', '2026-02-04 20:24:49', 6, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(11, 'LBR-0010', 'Resma papel bond A4 80gr (500)', 'Resma de papel bond A4 80 g/m2, 500 hojas', 6.50, 0.0000, 0.0000, 50, 10, 'activo', 'fisico', 1, '2025-12-25 14:09:00', NULL, 3, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(12, 'LBR-0011', 'Set acuarelas Tombow 12 colores', 'Set de acuarelas en pastilla, 12 colores', 22.00, 0.0000, 0.0000, 15, 5, 'activo', 'fisico', 1, '2025-12-25 14:10:00', NULL, 4, 6, 3, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(13, 'LBR-0012', 'Marcador permanente negro', 'Marcador indeleble punta media', 2.00, 0.0000, 0.0000, 0, 10, 'agotado', 'fisico', 1, '2025-12-25 14:11:00', NULL, 1, NULL, 2, NULL, NULL, NULL, NULL, NULL, NULL, 'UND', 1, NULL, NULL, 0, NULL),
	(14, '9788445018538', 'El Señor de los Anillos (NE revisada). Ilustrado por Alan Lee', 'ISBN 10: 8445018531 / ISBN 13: 9788445018538\nEditorial: Minotauro, 2024\nIdioma: Español', 129.00, 0.0000, 0.0000, 0, 1, 'activo', 'fisico', 1, '2026-02-07 01:40:05', '2026-02-14 02:32:02', 7, 7, 5, 'Tapa dura coffee table', '', '', '16.5x24.5x8.4cm', 2.400, '', 'UND', 1, NULL, NULL, 1, ''),
	(15, '9788467701982', 'diario de unicornios', NULL, 200.00, 190.0000, 0.0000, 0, 10, 'activo', 'fisico', 1, '2026-03-26 04:28:07', NULL, NULL, NULL, 7, NULL, NULL, NULL, NULL, 0.000, NULL, 'UND', 1, NULL, NULL, 0, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.proveedores: ~6 rows (aproximadamente)
DELETE FROM `proveedores`;
INSERT INTO `proveedores` (`id`, `nombre`, `rif`, `contacto_nombre`, `email`, `telefono`, `direccion`, `notas`, `creado_at`, `actualizado_at`, `deleted_at`) VALUES
	(1, 'Distribuidora Papel S.A.', NULL, 'María Pérez', 'ventas@distribuidora-papel.com', '+58 212 555 0101', 'Av. Comercio, Caracas', NULL, '2026-01-07 21:01:08', NULL, NULL),
	(2, 'Importadora Útiles S.R.L.', '', 'Carlos Rojas', 'contacto@importutiles.com', '+58 241 555 0202', 'Zona Industrial, Valencia,wah', '', '2026-01-07 21:01:08', '2026-01-18 03:37:11', '2026-01-18 03:37:11'),
	(3, 'ArtículosCreativos C.A.', NULL, 'Ana Gómez', 'info@articuloscreativos.com', '+58 261 555 0303', 'C.C. Arte, Maracay', NULL, '2026-01-07 21:01:08', NULL, NULL),
	(4, 'Capi', 'J-1886868', NULL, 'contacto@importutiles.com', '+58 5151161565', 'srdsg', 'sgdsrg', '2026-01-18 03:37:07', NULL, NULL),
	(5, 'Grupo Planeta, S.A.', 'A-08186249', NULL, 'info@planetadelibros.es', '+3493 492 80 00', 'Avenida Diagonal, 662-664. 08034 Barcelona.', 'Proveedor de libros', '2026-02-07 01:57:39', NULL, NULL),
	(6, 'Grupo Planeta S.A.', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-07 02:44:32', '2026-02-11 00:52:42', '2026-02-11 00:52:42'),
	(7, '5', NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 04:28:07', NULL, NULL);

-- Volcando estructura para tabla dbuzz.requisiciones
DROP TABLE IF EXISTS `requisiciones`;
CREATE TABLE IF NOT EXISTS `requisiciones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fecha` (`fecha_solicitud`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `fk_req_usuario` (`usuario_id`),
  KEY `fk_req_aprobador` (`aprobado_por`),
  CONSTRAINT `fk_req_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_req_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de requisiciones de compra';

-- Volcando datos para la tabla dbuzz.requisiciones: ~2 rows (aproximadamente)
DELETE FROM `requisiciones`;
INSERT INTO `requisiciones` (`id`, `codigo`, `fecha_solicitud`, `estado`, `prioridad`, `notas`, `usuario_id`, `aprobado_por`, `fecha_aprobacion`, `creado_at`, `actualizado_at`) VALUES
	(1, 'REQ-2026-0001', '2026-02-07', 'aprobada', 'alta', 'Requisición automática por stock bajo (3 productos)', 1, 1, '2026-02-07 16:26:46', '2026-02-07 06:45:43', '2026-02-07 16:26:46'),
	(2, 'REQ-2026-0002', '2026-02-07', 'pendiente', 'media', '', 1, NULL, NULL, '2026-02-07 06:46:49', NULL);

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
  KEY `idx_expiracion` (`expiracion`),
  CONSTRAINT `sesiones_api_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.sesiones_api: ~3 rows (aproximadamente)
DELETE FROM `sesiones_api`;
INSERT INTO `sesiones_api` (`id`, `usuario_id`, `token`, `expiracion`, `creado_en`) VALUES
	(142, 1, 'fa380ea6909787ec96570a9d417dc7809185dcd83a2782e708ed62b1e5c1d163', '2026-03-29 13:54:35', '2026-03-29 00:54:35'),
	(143, 1, '8673c2432df36dd133e0794cb6942fc61d14c07cfcafa0008bb2c2f5bc2044fb', '2026-03-29 15:19:35', '2026-03-29 01:19:35'),
	(144, 103, 'cf389df41d26702e357ad1486f55b2c7cb8330ccc47a1d9c0347da3a376bfeca', '2026-03-29 15:40:32', '2026-03-29 01:40:32');

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stock de productos por ubicación';

-- Volcando datos para la tabla dbuzz.stock_ubicacion: ~13 rows (aproximadamente)
DELETE FROM `stock_ubicacion`;
INSERT INTO `stock_ubicacion` (`id`, `producto_id`, `almacen_id`, `ubicacion_id`, `cantidad`, `stock_minimo`, `stock_maximo`, `actualizado_at`) VALUES
	(1, 1, 1, NULL, 4, 0, NULL, '2026-01-05 18:09:00'),
	(2, 2, 1, NULL, 100, 0, NULL, '2026-02-04 19:57:50'),
	(3, 3, 1, NULL, 60, 0, NULL, '2026-01-05 18:09:00'),
	(4, 4, 1, NULL, 200, 0, NULL, '2026-01-05 18:09:00'),
	(5, 5, 1, NULL, 40, 0, NULL, '2026-01-05 18:09:00'),
	(6, 6, 1, NULL, 150, 0, NULL, '2026-01-05 18:09:00'),
	(7, 7, 1, NULL, 300, 0, NULL, '2026-01-05 18:09:00'),
	(8, 8, 1, NULL, 80, 0, NULL, '2026-01-05 18:09:00'),
	(9, 9, 1, NULL, 25, 0, NULL, '2026-01-05 18:09:00'),
	(10, 10, 1, NULL, 6, 0, NULL, '2026-02-04 20:24:41'),
	(11, 11, 1, NULL, 50, 0, NULL, '2026-01-05 18:09:00'),
	(12, 12, 1, NULL, 15, 0, NULL, '2026-01-05 18:09:00'),
	(73, 10, 2, NULL, 4, 0, NULL, '2026-02-04 20:24:49');

-- Volcando estructura para tabla dbuzz.tallas_franela
DROP TABLE IF EXISTS `tallas_franela`;
CREATE TABLE IF NOT EXISTS `tallas_franela` (
  `id` smallint(5) unsigned NOT NULL,
  `codigo` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.tallas_franela: ~10 rows (aproximadamente)
DELETE FROM `tallas_franela`;
INSERT INTO `tallas_franela` (`id`, `codigo`) VALUES
	(6, '12'),
	(7, '14'),
	(8, '16'),
	(9, '18'),
	(10, '8'),
	(4, 'L'),
	(3, 'M'),
	(2, 'S'),
	(5, 'XL'),
	(1, 'XS');

-- Volcando estructura para tabla dbuzz.tasas
DROP TABLE IF EXISTS `tasas`;
CREATE TABLE IF NOT EXISTS `tasas` (
  `id` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `tasa` decimal(12,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.tasas: ~0 rows (aproximadamente)
DELETE FROM `tasas`;

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
  UNIQUE KEY `uk_fecha_fuente` (`fecha`,`fuente`),
  KEY `idx_fecha` (`fecha`),
  KEY `FK_tasa_usuario` (`usuario_id`),
  CONSTRAINT `FK_tasa_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tasa de cambio diaria USD para referencia de precios';

-- Volcando datos para la tabla dbuzz.tasas_cambio: ~86 rows (aproximadamente)
DELETE FROM `tasas_cambio`;
INSERT INTO `tasas_cambio` (`id`, `tasa`, `fuente`, `fecha`, `creado_at`, `usuario_id`) VALUES
	(1, 311.8800, 'bcv', '2026-01-07', '2026-01-08 01:40:29', NULL),
	(3, 733.8500, 'paralelo', '2026-01-07', '2026-01-08 01:40:29', NULL),
	(4, 364.3458, 'bcv_euro', '2026-01-07', '2026-01-08 01:40:30', NULL),
	(5, 730.1808, 'binance_usdt', '2026-01-07', '2026-01-08 01:40:30', NULL),
	(22, 330.3800, 'bcv', '2026-01-12', '2026-01-12 20:34:15', NULL),
	(23, 528.8900, 'paralelo', '2026-01-12', '2026-01-12 20:34:18', NULL),
	(24, 384.1628, 'bcv_euro', '2026-01-12', '2026-01-12 20:34:18', NULL),
	(25, 526.2456, 'binance_usdt', '2026-01-12', '2026-01-12 20:34:19', NULL),
	(26, 330.3800, 'bcv', '2026-01-13', '2026-01-13 21:17:34', NULL),
	(27, 598.2700, 'paralelo', '2026-01-13', '2026-01-13 21:17:34', NULL),
	(28, 385.5076, 'bcv_euro', '2026-01-13', '2026-01-13 21:17:35', NULL),
	(29, 595.2787, 'binance_usdt', '2026-01-13', '2026-01-13 21:17:35', NULL),
	(30, 344.5100, 'bcv', '2026-01-17', '2026-01-18 00:18:26', NULL),
	(31, 450.7500, 'paralelo', '2026-01-17', '2026-01-18 00:18:26', NULL),
	(32, 399.6636, 'bcv_euro', '2026-01-17', '2026-01-18 00:18:26', NULL),
	(33, 448.4963, 'binance_usdt', '2026-01-17', '2026-01-18 00:18:26', NULL),
	(34, 344.5100, 'bcv', '2026-01-18', '2026-01-18 16:49:00', NULL),
	(35, 455.3400, 'paralelo', '2026-01-18', '2026-01-18 16:49:00', NULL),
	(36, 399.6636, 'bcv_euro', '2026-01-18', '2026-01-18 16:49:00', NULL),
	(37, 453.0633, 'binance_usdt', '2026-01-18', '2026-01-18 16:49:00', NULL),
	(38, 344.5100, 'bcv', '2026-01-19', '2026-01-19 22:43:11', NULL),
	(39, 423.7500, 'paralelo', '2026-01-19', '2026-01-19 22:43:13', NULL),
	(40, 400.1278, 'bcv_euro', '2026-01-19', '2026-01-19 22:43:14', NULL),
	(41, 421.6313, 'binance_usdt', '2026-01-19', '2026-01-19 22:43:14', NULL),
	(42, 344.5100, 'bcv', '2026-01-20', '2026-01-20 23:27:06', NULL),
	(43, 435.7800, 'paralelo', '2026-01-20', '2026-01-20 23:27:07', NULL),
	(44, 401.0594, 'bcv_euro', '2026-01-20', '2026-01-20 23:27:08', NULL),
	(45, 433.6011, 'binance_usdt', '2026-01-20', '2026-01-20 23:27:08', NULL),
	(46, 344.5100, 'bcv', '2026-01-22', '2026-01-23 01:55:25', NULL),
	(47, 435.7800, 'paralelo', '2026-01-22', '2026-01-23 01:55:26', NULL),
	(48, 404.3545, 'bcv_euro', '2026-01-22', '2026-01-23 01:55:27', NULL),
	(49, 433.6011, 'binance_usdt', '2026-01-22', '2026-01-23 01:55:27', NULL),
	(50, 358.9247, 'bcv', '2026-01-27', '2026-01-28 00:44:00', NULL),
	(51, 541.5600, 'paralelo', '2026-01-27', '2026-01-28 00:44:08', NULL),
	(52, 429.3358, 'bcv_euro', '2026-01-27', '2026-01-28 00:44:09', NULL),
	(53, 538.8522, 'binance_usdt', '2026-01-27', '2026-01-28 00:44:09', NULL),
	(54, 358.9247, 'bcv', '2026-01-28', '2026-01-28 04:47:51', NULL),
	(55, 541.5600, 'paralelo', '2026-01-28', '2026-01-28 04:47:51', NULL),
	(56, 429.3358, 'bcv_euro', '2026-01-28', '2026-01-28 04:47:52', NULL),
	(57, 538.8522, 'binance_usdt', '2026-01-28', '2026-01-28 04:47:52', NULL),
	(58, 370.2544, 'bcv', '2026-02-02', '2026-02-02 20:52:16', NULL),
	(59, 516.3800, 'paralelo', '2026-02-02', '2026-02-02 20:52:16', NULL),
	(60, 439.2104, 'bcv_euro', '2026-02-02', '2026-02-02 20:52:16', NULL),
	(61, 513.7981, 'binance_usdt', '2026-02-02', '2026-02-02 20:52:16', NULL),
	(62, 372.1057, 'bcv', '2026-02-03', '2026-02-04 02:29:26', NULL),
	(63, 525.0800, 'paralelo', '2026-02-03', '2026-02-04 02:29:26', NULL),
	(64, 439.3220, 'bcv_euro', '2026-02-03', '2026-02-04 02:29:27', NULL),
	(65, 522.4546, 'binance_usdt', '2026-02-03', '2026-02-04 02:29:27', NULL),
	(66, 375.0825, 'bcv', '2026-02-04', '2026-02-04 19:17:27', NULL),
	(67, 527.0800, 'paralelo', '2026-02-04', '2026-02-04 19:17:27', NULL),
	(68, 442.8365, 'bcv_euro', '2026-02-04', '2026-02-04 19:17:28', NULL),
	(69, 524.4446, 'binance_usdt', '2026-02-04', '2026-02-04 19:17:28', NULL),
	(70, 378.4582, 'bcv', '2026-02-05', '2026-02-05 22:26:46', NULL),
	(71, 537.3400, 'paralelo', '2026-02-05', '2026-02-05 22:26:46', NULL),
	(72, 446.8220, 'bcv_euro', '2026-02-05', '2026-02-05 22:26:47', NULL),
	(73, 534.6533, 'binance_usdt', '2026-02-05', '2026-02-05 22:26:47', NULL),
	(74, 382.6318, 'bcv', '2026-02-06', '2026-02-07 00:35:46', NULL),
	(75, 545.6300, 'paralelo', '2026-02-06', '2026-02-07 00:35:46', NULL),
	(76, 451.7495, 'bcv_euro', '2026-02-06', '2026-02-07 00:35:47', NULL),
	(77, 542.9019, 'binance_usdt', '2026-02-06', '2026-02-07 00:35:47', NULL),
	(78, 382.6318, 'bcv', '2026-02-07', '2026-02-07 04:49:32', NULL),
	(79, 545.3300, 'paralelo', '2026-02-07', '2026-02-07 04:49:32', NULL),
	(80, 451.7495, 'bcv_euro', '2026-02-07', '2026-02-07 04:49:33', NULL),
	(81, 542.6034, 'binance_usdt', '2026-02-07', '2026-02-07 04:49:33', NULL),
	(82, 385.2720, 'bcv', '2026-02-10', '2026-02-10 22:52:00', NULL),
	(83, 547.6900, 'paralelo', '2026-02-10', '2026-02-10 22:52:00', NULL),
	(84, 458.1118, 'bcv_euro', '2026-02-10', '2026-02-10 22:52:01', NULL),
	(85, 544.9516, 'binance_usdt', '2026-02-10', '2026-02-10 22:52:01', NULL),
	(86, 388.7394, 'bcv', '2026-02-11', '2026-02-12 02:28:18', NULL),
	(87, 547.5800, 'paralelo', '2026-02-11', '2026-02-12 02:28:18', NULL),
	(88, 461.6857, 'bcv_euro', '2026-02-11', '2026-02-12 02:28:19', NULL),
	(89, 544.8421, 'binance_usdt', '2026-02-11', '2026-02-12 02:28:19', NULL),
	(90, 396.3674, 'bcv', '2026-02-13', '2026-02-13 23:23:11', NULL),
	(91, 530.8900, 'paralelo', '2026-02-13', '2026-02-13 23:23:11', NULL),
	(92, 470.7451, 'bcv_euro', '2026-02-13', '2026-02-13 23:23:12', NULL),
	(93, 528.2356, 'binance_usdt', '2026-02-13', '2026-02-13 23:23:12', NULL),
	(94, 396.3674, 'bcv', '2026-02-14', '2026-02-15 02:12:29', NULL),
	(95, 531.1400, 'paralelo', '2026-02-14', '2026-02-15 02:12:29', NULL),
	(96, 470.1867, 'bcv_euro', '2026-02-14', '2026-02-15 02:12:29', NULL),
	(97, 528.4843, 'binance_usdt', '2026-02-14', '2026-02-15 02:12:29', NULL),
	(98, 398.7456, 'bcv', '2026-02-19', '2026-02-19 15:14:08', NULL),
	(99, 572.0000, 'paralelo', '2026-02-19', '2026-02-19 15:14:08', NULL),
	(100, 470.7740, 'bcv_euro', '2026-02-19', '2026-02-19 15:14:09', NULL),
	(101, 569.1400, 'binance_usdt', '2026-02-19', '2026-02-19 15:14:09', NULL),
	(102, 451.5072, 'bcv', '2026-03-17', '2026-03-17 22:24:44', NULL),
	(103, 520.6420, 'bcv_euro', '2026-03-17', '2026-03-17 22:24:44', NULL),
	(108, 662.8100, 'paralelo', '2026-03-17', '2026-03-17 22:24:45', NULL),
	(109, 659.4960, 'binance_usdt', '2026-03-17', '2026-03-17 22:24:45', NULL),
	(110, 459.4525, 'bcv', '2026-03-23', '2026-03-24 01:54:02', NULL),
	(111, 533.4565, 'bcv_euro', '2026-03-23', '2026-03-24 01:54:02', NULL),
	(112, 659.6500, 'paralelo', '2026-03-23', '2026-03-24 01:54:03', NULL),
	(113, 656.3518, 'binance_usdt', '2026-03-23', '2026-03-24 01:54:03', NULL),
	(114, 459.4525, 'bcv', '2026-03-24', '2026-03-24 21:30:51', NULL),
	(115, 533.4565, 'bcv_euro', '2026-03-24', '2026-03-24 21:30:51', NULL),
	(116, 662.9900, 'paralelo', '2026-03-24', '2026-03-24 21:30:52', NULL),
	(117, 659.6751, 'binance_usdt', '2026-03-24', '2026-03-24 21:30:52', NULL),
	(118, 462.6687, 'bcv', '2026-03-25', '2026-03-25 20:37:00', NULL),
	(119, 536.0942, 'bcv_euro', '2026-03-25', '2026-03-25 20:37:00', NULL),
	(120, 677.2000, 'paralelo', '2026-03-25', '2026-03-25 20:37:01', NULL),
	(121, 673.8140, 'binance_usdt', '2026-03-25', '2026-03-25 20:37:01', NULL),
	(122, 466.6014, 'bcv', '2026-03-26', '2026-03-26 11:59:09', NULL),
	(123, 540.1704, 'bcv_euro', '2026-03-26', '2026-03-26 11:59:09', NULL),
	(124, 671.9300, 'paralelo', '2026-03-26', '2026-03-26 11:59:10', NULL),
	(125, 668.5704, 'binance_usdt', '2026-03-26', '2026-03-26 11:59:10', NULL),
	(126, 471.7004, 'bcv', '2026-03-28', '2026-03-29 00:54:42', NULL),
	(127, 543.9413, 'bcv_euro', '2026-03-28', '2026-03-29 00:54:42', NULL),
	(128, 666.7800, 'paralelo', '2026-03-28', '2026-03-29 00:54:42', NULL),
	(129, 663.4461, 'binance_usdt', '2026-03-28', '2026-03-29 00:54:42', NULL);

-- Volcando estructura para tabla dbuzz.transferencias_almacen
DROP TABLE IF EXISTS `transferencias_almacen`;
CREATE TABLE IF NOT EXISTS `transferencias_almacen` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_fechas` (`fecha_solicitud`,`fecha_envio`),
  KEY `idx_almacenes` (`almacen_origen_id`,`almacen_destino_id`),
  KEY `idx_almacen_origen` (`almacen_origen_id`),
  KEY `idx_almacen_destino` (`almacen_destino_id`),
  CONSTRAINT `fk_trf_almacen_destino` FOREIGN KEY (`almacen_destino_id`) REFERENCES `almacenes` (`id`),
  CONSTRAINT `fk_trf_almacen_origen` FOREIGN KEY (`almacen_origen_id`) REFERENCES `almacenes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.transferencias_almacen: ~1 rows (aproximadamente)
DELETE FROM `transferencias_almacen`;
INSERT INTO `transferencias_almacen` (`id`, `codigo`, `almacen_origen_id`, `almacen_destino_id`, `fecha_solicitud`, `fecha_envio`, `fecha_recepcion`, `estado`, `motivo`, `notas`, `referencia_externa`, `solicitado_por`, `enviado_por`, `recibido_por`, `creado_at`, `actualizado_at`) VALUES
	(1, 'TRF-20260203-001', 1, 2, '2026-02-03', '2026-02-04', '2026-02-04', 'recibido', 'Reemplazo decoración febrero', 'Exhibición de oficina', '', 1, 1, 1, '2026-02-04 19:51:31', '2026-02-04 20:24:49');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ubicaciones físicas dentro de cada almacén';

-- Volcando datos para la tabla dbuzz.ubicaciones: ~0 rows (aproximadamente)
DELETE FROM `ubicaciones`;
INSERT INTO `ubicaciones` (`id`, `almacen_id`, `codigo`, `nombre`, `pasillo`, `estante`, `nivel`, `capacidad_maxima`, `activo`, `creado_at`) VALUES
	(1, 1, 'A-01', 'ESTANTE DE LIBROS INTERNO', '4', '3', '1', 550, 1, '2026-03-26 04:04:02');

-- Volcando estructura para tabla dbuzz.usuarios
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
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
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.usuarios: ~9 rows (aproximadamente)
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `two_factor_secret`, `rol`, `estado`, `creado_at`, `actualizado_at`, `intentos_fallidos`, `two_factor_enabled`, `two_factor_confirmed_at`) VALUES
	(1, 'Givi', 'admin@buzz.com', '$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G', '7VRZAXCNBDRIICAI6LTE7R7BUP3LOYCU', 'admin', 'activo', '2025-08-09 17:43:16', '2026-03-23 03:40:38', 0, 1, '2026-01-28 18:02:18'),
	(4, 'Gisvel Aray', 'gisvelaray@gmail.com', '$2y$10$yjYWVs.8ODLr7CzI95RNresCNi9gJDOMKyviSoqb1gWfJxLTGjNiG', NULL, 'operador', 'inactivo', '2025-11-30 18:13:22', '2026-03-26 03:42:11', 0, 0, NULL),
	(36, 'GiviSupervisor', 'supervisor@buzz.com', '$2y$10$m389cp0CKSWLAODHmzL1Z.zyB8XnGt8BCDFmtoqI781PZcD2EjZna', NULL, 'supervisor', 'inactivo', '2025-08-14 01:32:53', '2026-03-26 03:42:13', 0, 0, NULL),
	(37, 'GiviOperador', 'operador@buzz.com', '$2y$10$7ofF41nU06Ct9hl90TdzguI8eSxA90RCq4K/i7yWSpic01oTiLCLC', NULL, 'operador', 'inactivo', '2025-08-14 01:33:00', '2026-03-26 03:42:15', 0, 0, NULL),
	(63, 'Juan Luis Chaurant', 'juanluischaurant@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'operador', 'inactivo', '2025-11-30 21:18:35', '2026-03-26 03:17:35', 0, 0, NULL),
	(101, 'Yeicson Acosta', 'acostajeiison@gmail.com', '$2y$10$eWaKLST8XfAOCcDSluyq6OaeP3ncNkSliDrtxFPjFwkdNwn1DL53K', NULL, 'admin', 'activo', '2026-02-07 01:02:12', NULL, 0, 0, NULL),
	(103, 'Tester Admin', 'testeradmin@buzz.com', '$2y$10$ro78YQN9hp7YYRswSC5.me3i2BpC6bce8DpbqkmE.FScf.GL6UJ9O', NULL, 'admin', 'activo', '2026-03-26 03:43:38', '2026-03-26 03:45:03', 0, 0, NULL),
	(104, 'Tester Supervisor', 'testersup@buzz.com', '$2y$10$NgiYAiudpmpbD5YIrZpvu.R5CuozOoXlB3VXJxCe4kkqeBdlHFGb2', NULL, 'supervisor', 'activo', '2026-03-26 03:44:07', '2026-03-26 03:44:58', 0, 0, NULL),
	(105, 'Tester Operador', 'testerop@buzz.com', '$2y$10$ew27Ka22/Z/c5AS1a1GRpu/5DmjQ5HnixZxaYjNqlLXYt2vuwRCyK', NULL, 'operador', 'activo', '2026-03-26 03:44:45', NULL, 0, 0, NULL);

-- Volcando estructura para vista dbuzz.vw_productos_stock_bajo_activos
DROP VIEW IF EXISTS `vw_productos_stock_bajo_activos`;
-- Creando tabla temporal para superar errores de dependencia de VIEW
CREATE TABLE `vw_productos_stock_bajo_activos` (
	`id` INT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`nombre` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`stock` INT(11) NOT NULL,
	`min_stock` INT(11) NOT NULL,
	`costo_ultimo` DECIMAL(12,4) NULL,
	`proveedor_default_id` INT(11) NULL,
	`proveedor_default_nombre` VARCHAR(1) NULL COLLATE 'utf8mb4_unicode_ci',
	`nivel_stock` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_uca1400_ai_ci',
	`cantidad_faltante` BIGINT(12) NOT NULL
);

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

-- Volcando estructura para disparador dbuzz.trg_almacen_codigo
DROP TRIGGER IF EXISTS `trg_almacen_codigo`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_almacen_codigo
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
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

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
    
    IF NEW.estado = 'anulado' AND OLD.estado = 'confirmado' THEN

        
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = GREATEST(0, p.stock - d.cantidad),
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        
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
    
    IF NEW.estado = 'confirmado' AND OLD.estado = 'borrador' THEN

        
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = p.stock + d.cantidad,
            p.costo_ultimo = d.costo_unitario,
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        
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

-- Volcando estructura para disparador dbuzz.trg_transferencia_codigo
DROP TRIGGER IF EXISTS `trg_transferencia_codigo`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER trg_transferencia_codigo
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

    
    SELECT tipo_producto INTO v_tipo FROM productos WHERE id = NEW.kit_id;
    IF v_tipo != 'kit' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El producto padre debe ser de tipo kit';
    END IF;

    
    IF NEW.kit_id = NEW.componente_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un kit no puede ser componente de sí mismo';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `vw_productos_stock_bajo_activos`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_productos_stock_bajo_activos` AS SELECT
            p.id,
            p.sku,
            p.nombre,
            p.stock,
            p.min_stock,
            p.costo_ultimo,
            p.proveedor_id AS proveedor_default_id,
            prov.nombre AS proveedor_default_nombre,
            CASE
                WHEN p.stock = 0 THEN 'agotado'
                WHEN p.stock <= p.min_stock THEN 'bajo'
                ELSE 'normal'
            END AS nivel_stock,
            (p.min_stock - p.stock) AS cantidad_faltante
        FROM productos p
        LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
        WHERE p.estado != 'inactivo'
          AND p.stock <= p.min_stock
        ORDER BY
            CASE WHEN p.stock = 0 THEN 0 ELSE 1 END,
            (p.min_stock - p.stock) DESC 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_almacenes_resumen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_almacenes_resumen` AS select `a`.`id` AS `id`,`a`.`codigo` AS `codigo`,`a`.`nombre` AS `nombre`,`a`.`tipo` AS `tipo`,`a`.`es_principal` AS `es_principal`,`a`.`activo` AS `activo`,`a`.`direccion` AS `direccion`,`a`.`telefono` AS `telefono`,`u`.`nombre` AS `responsable_nombre`,(select count(0) from `ubicaciones` where `ubicaciones`.`almacen_id` = `a`.`id` and `ubicaciones`.`activo` = 1) AS `total_ubicaciones`,(select count(distinct `stock_ubicacion`.`producto_id`) from `stock_ubicacion` where `stock_ubicacion`.`almacen_id` = `a`.`id` and `stock_ubicacion`.`cantidad` > 0) AS `productos_en_stock`,(select coalesce(sum(`stock_ubicacion`.`cantidad`),0) from `stock_ubicacion` where `stock_ubicacion`.`almacen_id` = `a`.`id`) AS `unidades_totales`,`a`.`creado_at` AS `creado_at` from (`almacenes` `a` left join `usuarios` `u` on(`a`.`responsable_id` = `u`.`id`)) order by `a`.`es_principal` desc,`a`.`nombre` 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_cargos_detalle_completo`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_cargos_detalle_completo` AS select `c`.`id` AS `cargo_id`,`c`.`codigo` AS `cargo_codigo`,`c`.`fecha` AS `cargo_fecha`,`c`.`estado` AS `cargo_estado`,`d`.`id` AS `detalle_id`,`d`.`producto_id` AS `producto_id`,`pr`.`sku` AS `producto_sku`,`pr`.`nombre` AS `producto_nombre`,`d`.`cantidad` AS `cantidad`,`d`.`costo_unitario` AS `costo_unitario`,`d`.`subtotal` AS `subtotal`,`d`.`notas` AS `detalle_notas` from ((`cargos_existencias` `c` join `detalle_cargo_existencias` `d` on(`c`.`id` = `d`.`cargo_id`)) join `productos` `pr` on(`d`.`producto_id` = `pr`.`id`)) order by `c`.`id` desc,`d`.`id` 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_cargos_resumen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_cargos_resumen` AS select `c`.`id` AS `id`,`c`.`codigo` AS `codigo`,`c`.`fecha` AS `fecha`,`c`.`concepto` AS `concepto`,case `c`.`concepto` when 'compra' then 'Compra' when 'donacion' then 'Donación' when 'ajuste_positivo' then 'Ajuste Positivo' when 'devolucion_cliente' then 'Devolución Cliente' when 'inventario_inicial' then 'Inventario Inicial' when 'otro' then coalesce(`c`.`concepto_otro`,'Otro') end AS `concepto_texto`,`p`.`nombre` AS `proveedor_nombre`,`c`.`referencia_externa` AS `referencia_externa`,`c`.`total_productos` AS `total_productos`,`c`.`total_unidades` AS `total_unidades`,`c`.`valor_total` AS `valor_total`,`c`.`estado` AS `estado`,case `c`.`estado` when 'borrador' then 'Borrador' when 'confirmado' then 'Confirmado' when 'anulado' then 'Anulado' end AS `estado_texto`,`u`.`nombre` AS `registrado_por_nombre`,`c`.`creado_at` AS `creado_at`,`uc`.`nombre` AS `confirmado_por_nombre`,`c`.`fecha_confirmacion` AS `fecha_confirmacion`,`ua`.`nombre` AS `anulado_por_nombre`,`c`.`fecha_anulacion` AS `fecha_anulacion`,`c`.`motivo_anulacion` AS `motivo_anulacion` from ((((`cargos_existencias` `c` left join `proveedores` `p` on(`c`.`proveedor_id` = `p`.`id`)) left join `usuarios` `u` on(`c`.`registrado_por` = `u`.`id`)) left join `usuarios` `uc` on(`c`.`confirmado_por` = `uc`.`id`)) left join `usuarios` `ua` on(`c`.`anulado_por` = `ua`.`id`)) order by `c`.`creado_at` desc 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`costo_promedio` AS `costo_promedio`,`p`.`costo_ultimo` AS `costo_ultimo`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`c`.`nombre` AS `categoria_nombre`,`m`.`nombre` AS `marca_nombre`,`pr`.`nombre` AS `proveedor_nombre`,`p`.`modelo` AS `modelo`,`p`.`color` AS `color`,`p`.`tamanio` AS `tamanio`,`p`.`medidas` AS `medidas`,`p`.`peso_kg` AS `peso_kg`,`p`.`material` AS `material`,`p`.`unidad_venta` AS `unidad_venta`,`p`.`cantidad_presentacion` AS `cantidad_presentacion`,`p`.`creado_at` AS `creado_at`,`p`.`actualizado_at` AS `actualizado_at`,`p`.`deleted_at` AS `deleted_at` from (((`productos` `p` left join `categorias` `c` on(`p`.`categoria` = `c`.`id`)) left join `marcas` `m` on(`p`.`marca_id` = `m`.`id`)) left join `proveedores` `pr` on(`p`.`proveedor_id` = `pr`.`id`)) where `p`.`deleted_at` is null 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_kit_componentes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_kit_componentes` AS select `pc`.`id` AS `id`,`pc`.`kit_id` AS `kit_id`,`pk`.`sku` AS `kit_sku`,`pk`.`nombre` AS `kit_nombre`,`pc`.`componente_id` AS `componente_id`,`p`.`sku` AS `componente_sku`,`p`.`nombre` AS `componente_nombre`,`p`.`tipo_producto` AS `componente_tipo`,`pc`.`cantidad` AS `cantidad`,`p`.`precio` AS `componente_precio`,case when `p`.`tipo_producto` = 'kit' and `p`.`es_kit_virtual` <> 0 then `fn_stock_kit_virtual`(`p`.`id`) when `p`.`tipo_producto` = 'servicio' then 999999 else `p`.`stock` end AS `componente_stock`,`pc`.`cantidad` * `p`.`precio` AS `subtotal`,`pc`.`orden` AS `orden` from ((`producto_componentes` `pc` join `productos` `p` on(`p`.`id` = `pc`.`componente_id`)) join `productos` `pk` on(`pk`.`id` = `pc`.`kit_id`)) order by `pc`.`kit_id`,`pc`.`orden`,`pc`.`id` 
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
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_productos_completo` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`tipo_producto` AS `tipo_producto`,`p`.`maneja_stock` AS `maneja_stock`,`p`.`es_kit_virtual` AS `es_kit_virtual`,`p`.`variante_atributo` AS `variante_atributo`,`p`.`producto_padre_id` AS `producto_padre_id`,`pp`.`nombre` AS `producto_padre_nombre`,`pp`.`sku` AS `producto_padre_sku`,case when `p`.`tipo_producto` = 'kit' and `p`.`es_kit_virtual` <> 0 then `fn_stock_kit_virtual`(`p`.`id`) when `p`.`tipo_producto` = 'servicio' then NULL else `p`.`stock` end AS `stock_efectivo`,`p`.`stock` AS `stock_fisico`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`p`.`marca_id` AS `marca_id`,`m`.`nombre` AS `marca_nombre`,`p`.`categoria` AS `categoria`,`c`.`nombre` AS `categoria_nombre`,(select count(0) from `productos` `v` where `v`.`producto_padre_id` = `p`.`id`) AS `total_variantes`,(select count(0) from `producto_componentes` `pc` where `pc`.`kit_id` = `p`.`id`) AS `total_componentes`,`p`.`creado_at` AS `creado_at`,`p`.`deleted_at` AS `deleted_at` from (((`productos` `p` left join `productos` `pp` on(`pp`.`id` = `p`.`producto_padre_id`)) left join `marcas` `m` on(`m`.`id` = `p`.`marca_id`)) left join `categorias` `c` on(`c`.`id` = `p`.`categoria`)) 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_rotacion_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_rotacion_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,ifnull(sum(`d`.`cantidad`),0) AS `total_vendido`,round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) AS `porcentaje_rotacion`,case when `p`.`stock` <= `p`.`min_stock` then 'CRÍTICO' when ifnull(sum(`d`.`cantidad`),0) = 0 then 'ESTANCADO' else 'NORMAL' end AS `estado_rotacion` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_stock_detallado`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_stock_detallado` AS select `su`.`id` AS `id`,`a`.`id` AS `almacen_id`,`a`.`codigo` AS `almacen_codigo`,`a`.`nombre` AS `almacen_nombre`,`u`.`id` AS `ubicacion_id`,`u`.`codigo` AS `ubicacion_codigo`,concat(coalesce(`u`.`pasillo`,''),'-',coalesce(`u`.`estante`,''),'-',coalesce(`u`.`nivel`,'')) AS `ubicacion_full`,`p`.`id` AS `producto_id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `producto_nombre`,`su`.`cantidad` AS `cantidad`,`su`.`stock_minimo` AS `stock_minimo`,`su`.`stock_maximo` AS `stock_maximo`,case when `su`.`cantidad` <= `su`.`stock_minimo` then 'bajo' when `su`.`stock_maximo` is not null and `su`.`cantidad` >= `su`.`stock_maximo` then 'lleno' else 'normal' end AS `estado_stock` from (((`stock_ubicacion` `su` join `almacenes` `a` on(`a`.`id` = `su`.`almacen_id`)) join `productos` `p` on(`p`.`id` = `su`.`producto_id`)) left join `ubicaciones` `u` on(`u`.`id` = `su`.`ubicacion_id`)) where `a`.`activo` = 1 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_stock_por_almacen`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_stock_por_almacen` AS select `a`.`id` AS `almacen_id`,`a`.`codigo` AS `almacen_codigo`,`a`.`nombre` AS `almacen_nombre`,`p`.`id` AS `producto_id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `producto_nombre`,coalesce(sum(`su`.`cantidad`),0) AS `stock_almacen`,count(distinct `su`.`ubicacion_id`) AS `num_ubicaciones` from ((`almacenes` `a` join `productos` `p`) left join `stock_ubicacion` `su` on(`su`.`almacen_id` = `a`.`id` and `su`.`producto_id` = `p`.`id`)) where `a`.`activo` = 1 and `p`.`deleted_at` is null group by `a`.`id`,`p`.`id` order by `a`.`nombre`,`p`.`nombre` 
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_tasa_hoy`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_tasa_hoy` AS select coalesce((select `tasas_cambio`.`tasa` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`tasa` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),1) AS `tasa_actual`,coalesce((select `tasas_cambio`.`fecha` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`fecha` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),curdate()) AS `fecha_tasa`,coalesce((select `tasas_cambio`.`fuente` from `tasas_cambio` where `tasas_cambio`.`fecha` = curdate() limit 1),(select `tasas_cambio`.`fuente` from `tasas_cambio` where `tasas_cambio`.`fecha` < curdate() order by `tasas_cambio`.`fecha` desc limit 1),'no_configurada') AS `fuente` 
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
