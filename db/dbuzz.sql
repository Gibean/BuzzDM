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
  `tamaño_bytes` bigint(20) DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  `tipo` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.backups: ~0 rows (aproximadamente)
DELETE FROM `backups`;

-- Volcando estructura para tabla dbuzz.categorias
DROP TABLE IF EXISTS `categorias`;
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla dbuzz.categorias: ~0 rows (aproximadamente)
DELETE FROM `categorias`;

-- Volcando estructura para tabla dbuzz.marcas
DROP TABLE IF EXISTS `marcas`;
CREATE TABLE IF NOT EXISTS `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.clientes: ~1 rows (aproximadamente)
DELETE FROM `clientes`;
INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `notas`, `vip`, `creado_at`) VALUES
	('V-30535128', 'Gisvel Aray', '', '+584128759068', 'Calle Luisa Caceres de Arismendi', '<3', 0, '2025-11-21 00:56:28');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.detalle_pedido: ~0 rows (aproximadamente)
DELETE FROM `detalle_pedido`;

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

-- Volcando datos para la tabla dbuzz.empresa: ~0 rows (aproximadamente)
DELETE FROM `empresa`;

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
	(1, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:07:47', 'error', '{"Productos":{"status":"failed","message":"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'."]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(2, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:07:47', 'error', '{"Productos":{"status":"failed","message":"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.","Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'."]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(3, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:11:17', 'error', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(4, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 22:11:17', 'error', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"failed","message":"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.","processed":25,"inserted":0,"updated":0,"failed":25,"errors":["Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.","Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido."]}}'),
	(5, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 23:39:24', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[],"messages":["Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c7483d1ed2f468dc","Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1655dc2152002121","Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2d7e26b26152637c","Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 128fa5e7f2027c83","Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 59cffbb6c192f532","Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d0157d0cf549a93b","Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 76f6ce738b8dd30f","Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 17d6ce4f3231b357","Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 66c191dccef7f035","Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 6263a2a65e768024","Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a03905da871c1b54","Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 5f4ee1fa722a7b9f","Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ad6eb20da6a65471","Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 0e725d16f0bb9147","Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9ca034dabb1f3f99","Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: acb90565f0caa3e2","Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 203e374836947579","Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e3ad77b0141a685f","Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a22051e99dd145e3","Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2a0a5937b19aae86","Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a5b5b78eab46fa6b","Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b2345d3dc52d39fd","Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d4a4ae07311ad69","Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 02002faa208628a5","Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9b2fca23509294c2"]}}'),
	(6, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-13 23:39:28', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]}}'),
	(7, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-14 01:43:35', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":25,"updated":0,"failed":0,"errors":[],"messages":["Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d3920f73202a5251","Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b59ca94455e4d25f","Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: f2d64fe574c81acf","Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 26663e7177c18acf","Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: dbb7e77c99b543e8","Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 18e42493816cb018","Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 275ff56985f70e46","Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d6bc4a710d29f14","Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ed7314d2a217e0eb","Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d27b7a2a6b2a48f4","Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ee6dbcb1f43751b7","Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9d7b573ab7f6e90f","Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e9b18c122a506151","Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 91b5c5a39e2edf1b","Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: bd9843613ae3c1c3","Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 8396f94382d8a5e7","Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4e5bd2c31c409629","Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 571c3ca40d272cc2","Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: aa665e8c558f3160","Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a9f9249620561c50","Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 71326e217cfdb7c6","Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 42f9f90c5e297ce9","Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c45acec4924ee81c","Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4cfcca5e94244f79","Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1cdb3a33f1e4344c"]}}'),
	(8, NULL, 'general', 'Importacion_Agosto.xlsx', '2025-08-14 01:43:38', 'procesado', '{"Productos":{"status":"success","message":"Importaci\\u00f3n de \'Productos\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]},"Clientes":{"status":"success","message":"Importaci\\u00f3n de \'Clientes\' completada exitosamente.","processed":25,"inserted":0,"updated":25,"failed":0,"errors":[]}}');

-- Volcando estructura para tabla dbuzz.logs
DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs: ~0 rows (aproximadamente)
DELETE FROM `logs`;

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.logs_login: ~29 rows (aproximadamente)
DELETE FROM `logs_login`;
INSERT INTO `logs_login` (`id`, `usuario_id`, `accion`, `detalles`, `fecha`) VALUES
	(1, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-13 19:20:33'),
	(2, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-13 19:20:38'),
	(3, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-13 19:20:39'),
	(4, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 19:24:40'),
	(5, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 20:19:11'),
	(6, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-13 20:41:09'),
	(7, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 20:41:24'),
	(8, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 22:08:48'),
	(9, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 23:07:27'),
	(10, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-13 23:35:17'),
	(11, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 00:03:16'),
	(12, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 00:03:16'),
	(13, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 00:03:16'),
	(14, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 01:29:04'),
	(15, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 01:40:17'),
	(16, 36, 'Exitoso', 'Inicio de sesión de supervisor', '2025-08-14 01:53:10'),
	(17, 37, 'Exitoso', 'Inicio de sesión de operador', '2025-08-14 01:53:52'),
	(18, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-14 01:54:54'),
	(19, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-14 01:54:55'),
	(20, 1, 'Denegado', 'Intento de inicio de sesión con contraseña incorrecta', '2025-08-14 01:54:55'),
	(21, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 01:55:04'),
	(22, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 01:55:04'),
	(23, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-08-14 01:55:04'),
	(24, NULL, 'Denegado', 'Intento de inicio de sesión con email no existente: admin@bnnuy.com', '2025-11-17 23:41:06'),
	(25, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-17 23:41:18'),
	(26, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-18 23:55:38'),
	(27, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-20 01:09:23'),
	(28, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-20 01:16:14'),
	(29, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-20 01:29:17'),
	(30, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-21 00:35:55'),
	(31, 1, 'Exitoso', 'Inicio de sesión de admin', '2025-11-22 01:57:04');

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
  PRIMARY KEY (`id`),
  KEY `creado_por` (`creado_por`),
  KEY `fecha_creacion` (`fecha_creacion`),
  KEY `estado_envio` (`estado_envio`),
  KEY `pedidos_ibfk_1` (`cliente_id`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.pedidos: ~0 rows (aproximadamente)
DELETE FROM `pedidos`;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `nombre` (`nombre`),
  KEY `sku_2` (`sku`),
  KEY `fk_productos_categoria` (`categoria`),
  KEY `fk_productos_marca` (`marca_id`),
  KEY `fk_productos_proveedor` (`proveedor_id`),
  CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_marca` FOREIGN KEY (`marca_id`) REFERENCES `marcas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.productos: ~0 rows (aproximadamente)
DELETE FROM `productos`;

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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.sesiones_api: ~21 rows (aproximadamente)
DELETE FROM `sesiones_api`;
INSERT INTO `sesiones_api` (`id`, `usuario_id`, `token`, `expiracion`, `creado_en`) VALUES
	(1, 1, 'ac38177a1cd843a4ee543724f8cba4ff07ac5d8e183ba870edf5f80b82075f20', '2025-08-14 09:24:40', '2025-08-13 19:24:40'),
	(2, 1, '92c566e3e3e3692ae0d509a450fa3a2bbcbf4aca83843d3870bbb1dc6c548f7f', '2025-08-14 10:19:11', '2025-08-13 20:19:11'),
	(3, 1, '219e2a32c46e89daa1310d846e9b2858f2940fa9e90c87d2d50d4222fa4df1ee', '2025-08-14 10:41:24', '2025-08-13 20:41:24'),
	(4, 1, '97faac3123829e737dadc750e38d450a7950bb2addb02287b52c41b13720a848', '2025-08-14 12:08:48', '2025-08-13 22:08:48'),
	(5, 1, '63044a777eb6801cbda743e815bfe6d2740eb7ecc294de7be0287aee0ec43460', '2025-08-14 13:07:27', '2025-08-13 23:07:27'),
	(6, 1, 'dfa29e9521abd797a6294b5b3bc518b9eb6ee963e60b50d123d1abd439d4b934', '2025-08-14 13:35:17', '2025-08-13 23:35:17'),
	(7, 1, 'd92637b0cb1ed745c031d3f76727991cddbb630289080b1d996b8421f6ccc6f4', '2025-08-14 14:03:16', '2025-08-14 00:03:16'),
	(8, 1, '5168f4967dac99bd6423f3d42b4ef5eada8ff77f80274ad077965896319997ee', '2025-08-14 14:03:16', '2025-08-14 00:03:16'),
	(9, 1, 'c7a8ee2b33fb1330ad55179003f9ec98d66f9880098dbdfb377c452f0f8f526b', '2025-08-14 14:03:16', '2025-08-14 00:03:16'),
	(10, 1, '8f9811df2223e691cf8f7dd784fa707a969940be57d943b50a8709ab3b456a43', '2025-08-14 15:29:04', '2025-08-14 01:29:04'),
	(11, 1, 'bfe64136ed8315d3141cd068f308050f94fb650e2189ff327f2027b8bdc4d6f3', '2025-08-14 15:40:17', '2025-08-14 01:40:17'),
	(12, 36, '0e1b7e0fa857124c3dbfcc0980bd0415a05acddc3190fcc1c9a93521c8f128d6', '2025-08-14 15:53:10', '2025-08-14 01:53:10'),
	(13, 37, 'd2271f027a122180995f300c4cdf61373fa38c4b86f2f95be8897ebf9241812e', '2025-08-14 15:53:52', '2025-08-14 01:53:52'),
	(14, 1, '04bc78acee60f71db4707be8a9f0d512d93db219cae17a31a1d8881df7bedc98', '2025-08-14 15:55:04', '2025-08-14 01:55:04'),
	(15, 1, '062a9f9ba96888d03a0e28fa36d2258267f7cb792b58a32c9785e4276fdc22a5', '2025-08-14 15:55:04', '2025-08-14 01:55:04'),
	(16, 1, 'b4525d3be41de6344a6dc66e6921b554f244743a956cd92a59fc8e2641e6cea6', '2025-08-14 15:55:04', '2025-08-14 01:55:04'),
	(17, 1, '99e355c042ee2d00423e4dffdc36acaa6dc718d823cf380460084ed6064a8724', '2025-11-18 12:41:18', '2025-11-17 23:41:18'),
	(18, 1, '307b5eea9ca0ca8adc36ba342d83ab42ea12d2ae4d5b4b40722abf16b065f128', '2025-11-19 12:55:38', '2025-11-18 23:55:38'),
	(19, 1, '85d49f0c3e00bab4bb1a07c5b3b377a993a29d11029eb2f895adc6127637d156', '2025-11-20 14:09:23', '2025-11-20 01:09:23'),
	(20, 1, 'c002f31d6ed4c1f6586f994217675f4633e39f344a6a3ecd24bb940789e10252', '2025-11-20 14:16:14', '2025-11-20 01:16:14'),
	(21, 1, '9ea5d45e9534be26310f7720a7ae64356e928520ae2b89b1599c48fe02336167', '2025-11-20 14:29:17', '2025-11-20 01:29:17'),
	(22, 1, 'e1fa6803cdd4edd2c96c2569872c413605e6964f6cb86a4fd214cc72514e2468', '2025-11-21 13:35:55', '2025-11-21 00:35:55'),
	(23, 1, '06f7fc772fe19caa0dec4665078d5609011c4832d73eebd56ef4bb382bdaaf83', '2025-11-22 14:57:04', '2025-11-22 01:57:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Volcando datos para la tabla dbuzz.usuarios: ~3 rows (aproximadamente)
DELETE FROM `usuarios`;
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `creado_at`, `actualizado_at`, `intentos_fallidos`) VALUES
	(1, 'Givi', 'admin@buzz.com', '$2y$10$mTbTUbyhMZKLQGb0IERVIOe5P4q0ifwNA4Q2QzUtHUINs8HvF3Ck.', 'admin', 'activo', '2025-08-09 17:43:16', '2025-08-14 01:55:04', 0),
	(36, 'GiviSupervisor', 'supervisor@buzz.com', '$2y$10$m389cp0CKSWLAODHmzL1Z.zyB8XnGt8BCDFmtoqI781PZcD2EjZna', 'supervisor', 'activo', '2025-08-14 01:32:53', NULL, 0),
	(37, 'GiviOperador', 'operador@buzz.com', '$2y$10$7ofF41nU06Ct9hl90TdzguI8eSxA90RCq4K/i7yWSpic01oTiLCLC', 'operador', 'activo', '2025-08-14 01:33:00', NULL, 0);

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
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_inventario` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.stock,
    COUNT(d.id) AS veces_vendido,
    SUM(d.cantidad) AS total_vendido,
    SUM(d.cantidad * d.precio_unitario) AS ingresos_totales
FROM
    productos p
LEFT JOIN
    detalle_pedido d ON p.id = d.producto_id
GROUP BY
    p.id
ORDER BY
    total_vendido DESC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_low_stock`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_low_stock` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.stock,
    p.min_stock,
    p.estado,
    p.creado_at,
    p.actualizado_at,
    (p.stock <= p.min_stock) AS bajo_minimo
FROM
    productos p
WHERE
    p.stock <= p.min_stock
    AND p.estado != 'agotado'
ORDER BY
    p.stock ASC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_op_backup`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_op_backup` AS SELECT
    id AS registro_id,
    'Backup realizado' AS operacion,
    CONCAT('Archivo: ', archivo, ' | Tamaño: ',
           FORMAT(tamaño_bytes/1048576, 2), ' MB') AS detalles,
    fecha
FROM backups
ORDER BY fecha DESC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_op_import`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_op_import` AS SELECT
    i.id AS registro_id,
    'Importación CSV' AS operacion,
    CONCAT('Archivo: ', i.archivo_nombre, ' | Estado: ', i.estado) AS detalles,
    i.fecha,
    u.nombre AS usuario
FROM importaciones i
LEFT JOIN usuarios u ON i.usuario_id = u.id
WHERE i.tipo = 'csv'
ORDER BY i.fecha DESC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_pedidos_pendientes`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_pedidos_pendientes` AS SELECT
    ped.id,
    ped.referencia,
    ped.fecha_creacion,
    ped.total,
    ped.estado_pago,
    ped.estado_envio,
    ped.direccion_envio,
    ped.notas_envio,
    ped.actualizado_at,
    c.nombre AS cliente_nombre,
    c.email AS cliente_email,
    u.nombre AS creado_por_nombre
FROM
    pedidos ped
JOIN
    clientes c ON ped.cliente_id = c.id
LEFT JOIN
    usuarios u ON ped.creado_por = u.id
WHERE
    ped.estado_pago = 'no_pagado'
    OR ped.estado_envio IN ('pendiente', 'enviado')
ORDER BY
    ped.fecha_creacion DESC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_rotacion_inventario`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_rotacion_inventario` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.stock,
    p.min_stock,
    IFNULL(SUM(d.cantidad), 0) AS total_vendido,
    ROUND(IFNULL(SUM(d.cantidad), 0) / GREATEST(p.stock, 1) * 100, 2) AS porcentaje_rotacion,
    CASE
        WHEN p.stock <= p.min_stock THEN 'CRÍTICO'
        WHEN IFNULL(SUM(d.cantidad), 0) = 0 THEN 'ESTANCADO'
        ELSE 'NORMAL'
    END AS estado_rotacion
FROM
    productos p
LEFT JOIN
    detalle_pedido d ON p.id = d.producto_id
GROUP BY
    p.id
ORDER BY
    porcentaje_rotacion ASC
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_top5_down`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_top5_down` AS SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.stock,
    p.estado
FROM
    productos p
WHERE
    p.id NOT IN (SELECT DISTINCT producto_id FROM detalle_pedido)
ORDER BY
    p.nombre
;

-- Eliminando tabla temporal y crear estructura final de VIEW
DROP TABLE IF EXISTS `v_top5_up`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_top5_up` AS SELECT p.id, p.sku, p.nombre, SUM(d.cantidad) as total_vendido
FROM detalle_pedido d
JOIN productos p ON p.id = d.producto_id
GROUP BY p.id
ORDER BY total_vendido DESC
;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
