-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: dbuzz
-- ------------------------------------------------------
-- Server version	11.8.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backups` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` VALUES (4,'dbuzzbackup_2025-12-02_06-11-25.sql','C:\\xampp\\htdocs\\BuzzDM\\php/../backups/dbuzzbackup_2025-12-02_06-11-25.sql',68600,'2025-12-02 05:11:26','manual','completado','Backup creado exitosamente','{\"tablas_exportadas\":16,\"duracion_ms\":481,\"tamanio_mb\":0.07}',1);
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_log_backup
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES ('E-30003000','Juan Almeida','misogino.300@gmail.com','+34900 861 494','Municipio Santiago','Epale Juan Almeida misógino yo no conozco de puntas la única punta que conozco es esta… la punta de la teta porque como verás estoy amamantando desde la izquierda cómoda es bien depinga verdad?? hablar y criticar a los demás y como tú me hablabas de cacao yo no tengo cacao para ti tengo patadas y te patearé el culo donde te vea #TePateareElCuloDondeTeVea y no por lo que has dicho respecto al municipio Santiago de Mariño sino por meterte conmigo en mi condición de mujer',0,'2025-12-02 05:14:09',NULL),('V-30535128','Gisvel Aray','','+584128759068','Calle Luisa Caceres de Arismendi','<3',1,'2025-11-21 00:56:28',NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

LOCK TABLES `detalle_pedido` WRITE;
/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa`
--

DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresa` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa`
--

LOCK TABLES `empresa` WRITE;
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
/*!40000 ALTER TABLE `empresa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `importaciones`
--

DROP TABLE IF EXISTS `importaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `importaciones` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `importaciones`
--

LOCK TABLES `importaciones` WRITE;
/*!40000 ALTER TABLE `importaciones` DISABLE KEYS */;
INSERT INTO `importaciones` VALUES (1,1,'general','Importacion_Agosto.xlsx','2025-08-13 22:07:47','error','{\"Productos\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\"]},\"Clientes\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\"]}}'),(2,1,'general','Importacion_Agosto.xlsx','2025-08-13 22:07:47','error','{\"Productos\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Productos\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 4: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 5: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 6: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 7: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 8: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 9: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 10: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 11: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 12: El valor \'2\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 13: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 14: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 15: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 16: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 17: El valor \'4\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 18: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 19: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 20: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 21: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 22: El valor \'3\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 23: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 24: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 25: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 26: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\",\"Fila 27: El valor \'1\' para \'categoria\' no existe en la tabla \'categorias\'.\"]},\"Clientes\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\"]}}'),(3,1,'general','Importacion_Agosto.xlsx','2025-08-13 22:11:17','error','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":25,\"updated\":0,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\"]}}'),(4,1,'general','Importacion_Agosto.xlsx','2025-08-13 22:11:17','error','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":0,\"updated\":25,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"failed\",\"message\":\"Importaci\\u00f3n de \'Clientes\' fallida. Se encontraron errores.\",\"processed\":25,\"inserted\":0,\"updated\":0,\"failed\":25,\"errors\":[\"Fila 3: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 4: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 5: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 6: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 7: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 8: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 9: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 10: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 11: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 12: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 13: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 14: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 15: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 16: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 17: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 18: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 19: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 20: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 21: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 22: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 23: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 24: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 25: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 26: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\",\"Fila 27: Columna \'CI\' (campo requerido: id) no encontrada en el archivo.; El campo \'id\' es requerido.\"]}}'),(5,1,'general','Importacion_Agosto.xlsx','2025-08-13 23:39:24','procesado','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":25,\"updated\":0,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Clientes\' completada exitosamente.\",\"processed\":25,\"inserted\":25,\"updated\":0,\"failed\":0,\"errors\":[],\"messages\":[\"Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c7483d1ed2f468dc\",\"Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1655dc2152002121\",\"Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2d7e26b26152637c\",\"Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 128fa5e7f2027c83\",\"Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 59cffbb6c192f532\",\"Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d0157d0cf549a93b\",\"Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 76f6ce738b8dd30f\",\"Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 17d6ce4f3231b357\",\"Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 66c191dccef7f035\",\"Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 6263a2a65e768024\",\"Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a03905da871c1b54\",\"Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 5f4ee1fa722a7b9f\",\"Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ad6eb20da6a65471\",\"Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 0e725d16f0bb9147\",\"Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9ca034dabb1f3f99\",\"Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: acb90565f0caa3e2\",\"Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 203e374836947579\",\"Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e3ad77b0141a685f\",\"Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a22051e99dd145e3\",\"Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 2a0a5937b19aae86\",\"Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a5b5b78eab46fa6b\",\"Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b2345d3dc52d39fd\",\"Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d4a4ae07311ad69\",\"Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 02002faa208628a5\",\"Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9b2fca23509294c2\"]}}'),(6,1,'general','Importacion_Agosto.xlsx','2025-08-13 23:39:28','procesado','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":0,\"updated\":25,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Clientes\' completada exitosamente.\",\"processed\":25,\"inserted\":0,\"updated\":25,\"failed\":0,\"errors\":[]}}'),(7,1,'general','Importacion_Agosto.xlsx','2025-08-14 01:43:35','procesado','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":25,\"updated\":0,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Clientes\' completada exitosamente.\",\"processed\":25,\"inserted\":25,\"updated\":0,\"failed\":0,\"errors\":[],\"messages\":[\"Usuario \'laura.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d3920f73202a5251\",\"Usuario \'carlos.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: b59ca94455e4d25f\",\"Usuario \'sofia.l@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: f2d64fe574c81acf\",\"Usuario \'miguel.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 26663e7177c18acf\",\"Usuario \'elena.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: dbb7e77c99b543e8\",\"Usuario \'andres.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 18e42493816cb018\",\"Usuario \'daniela.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 275ff56985f70e46\",\"Usuario \'ricardo.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 7d6bc4a710d29f14\",\"Usuario \'camila.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ed7314d2a217e0eb\",\"Usuario \'javier.t@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: d27b7a2a6b2a48f4\",\"Usuario \'valentina.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: ee6dbcb1f43751b7\",\"Usuario \'luis.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 9d7b573ab7f6e90f\",\"Usuario \'mariana.o@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: e9b18c122a506151\",\"Usuario \'diego.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 91b5c5a39e2edf1b\",\"Usuario \'isabella.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: bd9843613ae3c1c3\",\"Usuario \'pedro.v@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 8396f94382d8a5e7\",\"Usuario \'ana.j@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4e5bd2c31c409629\",\"Usuario \'gabriel.s@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 571c3ca40d272cc2\",\"Usuario \'lucia.m@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: aa665e8c558f3160\",\"Usuario \'pablo.r@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: a9f9249620561c50\",\"Usuario \'natalia.h@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 71326e217cfdb7c6\",\"Usuario \'fernando.g@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 42f9f90c5e297ce9\",\"Usuario \'carmen.f@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: c45acec4924ee81c\",\"Usuario \'alejandro.d@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 4cfcca5e94244f79\",\"Usuario \'patricia.c@example.com\' creado con rol \'operador\'. Contrase\\u00f1a temporal: 1cdb3a33f1e4344c\"]}}'),(8,1,'general','Importacion_Agosto.xlsx','2025-08-14 01:43:38','procesado','{\"Productos\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Productos\' completada exitosamente.\",\"processed\":25,\"inserted\":0,\"updated\":25,\"failed\":0,\"errors\":[]},\"Clientes\":{\"status\":\"success\",\"message\":\"Importaci\\u00f3n de \'Clientes\' completada exitosamente.\",\"processed\":25,\"inserted\":0,\"updated\":25,\"failed\":0,\"errors\":[]}}');
/*!40000 ALTER TABLE `importaciones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER trg_log_importacion
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE,
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`),
  CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,1,'Solicitud de recuperación de contraseña','Email: admin@buzz.com','2025-11-29 02:18:49'),(2,1,'Contraseña cambiada','Mediante recuperación de contraseña','2025-11-29 02:19:17'),(3,NULL,'Backup realizado','Archivo: backup_dbuzz_2025-12-02_05-54-21.sql | Tamaño: 0.06 MB','2025-12-02 04:54:22'),(4,1,'Backup Manual','{\"archivo\":\"backup_dbuzz_2025-12-02_05-54-21.sql\",\"tamanio_mb\":0.06,\"duracion_ms\":770,\"tablas\":16}','2025-12-02 04:54:22'),(5,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-03-19.sql | Tamaño: 0.06 MB','2025-12-02 05:03:20'),(6,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-03-19.sql\",\"tamanio_mb\":0.06,\"duracion_ms\":531,\"tablas\":16}','2025-12-02 05:03:20'),(7,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-04-28.sql | Tamaño: 0.07 MB','2025-12-02 05:04:43'),(8,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-04-28.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":15079,\"tablas\":16}','2025-12-02 05:04:43'),(9,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-02_06-07-56.sql\",\"duracion_ms\":800,\"resultado\":\"Exitoso\"}','2025-12-02 05:08:54'),(10,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-11-25.sql | Tamaño: 0.07 MB','2025-12-02 05:11:26'),(11,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-11-25.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":481,\"tablas\":16}','2025-12-02 05:11:26');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs_login`
--

DROP TABLE IF EXISTS `logs_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned DEFAULT NULL,
  `accion` varchar(50) NOT NULL DEFAULT '',
  `detalles` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_login`
--

LOCK TABLES `logs_login` WRITE;
/*!40000 ALTER TABLE `logs_login` DISABLE KEYS */;
INSERT INTO `logs_login` VALUES (1,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:43:12'),(2,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:14'),(3,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:15'),(4,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:15'),(5,1,'Exitoso','Inicio de sesión de admin','2025-11-30 19:55:01'),(6,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 20:40:09'),(7,1,'Exitoso','Inicio de sesión de admin','2025-11-30 20:40:11'),(8,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:16:10'),(9,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:16:12'),(10,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:16:14'),(11,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:31:30'),(12,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:31:31'),(13,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:39:12'),(14,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:39:16'),(15,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-12-02 01:38:20'),(16,1,'Exitoso','Inicio de sesión de admin','2025-12-02 01:39:23'),(17,1,'Exitoso','Inicio de sesión de admin','2025-12-02 02:34:27'),(18,1,'Exitoso','Inicio de sesión de admin','2025-12-02 03:01:51'),(19,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@example.com','2025-12-02 03:54:03'),(20,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@example.com','2025-12-02 03:56:19'),(21,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@admin.com','2025-12-02 04:00:55'),(22,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@admin.com','2025-12-02 04:01:07'),(23,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:02:31'),(24,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:04:53'),(25,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:07:09'),(26,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:10:30');
/*!40000 ALTER TABLE `logs_login` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `marcas`
--

DROP TABLE IF EXISTS `marcas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `marcas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
/*!40000 ALTER TABLE `marcas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movimientos_inventario` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_recovery`
--

DROP TABLE IF EXISTS `password_recovery`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_recovery` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_recovery`
--

LOCK TABLES `password_recovery` WRITE;
/*!40000 ALTER TABLE `password_recovery` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_recovery` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES (1,4,'225c2612cebf18e9a3f6ccab9c2b9fafd657b29f8acbf14f5c9806293e9863c0','2025-12-01 00:13:38',1,'2025-11-30 18:13:38'),(2,4,'b13ab88f4462d972cb81b66e2fd60d06470bc9a21ddd72fd288cafa595836255','2025-12-01 01:55:10',0,'2025-11-30 19:55:10');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesiones_api`
--

DROP TABLE IF EXISTS `sesiones_api`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sesiones_api` (
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_api`
--

LOCK TABLES `sesiones_api` WRITE;
/*!40000 ALTER TABLE `sesiones_api` DISABLE KEYS */;
INSERT INTO `sesiones_api` VALUES (24,1,'2584334646b54b429e0cb37b0a117cc1c01a1286563e5d6a6ddaa2631344080c','2025-11-29 15:15:36','2025-11-29 02:15:36'),(25,1,'a96a5e9527b24c5b34bdb87096c97d5363c3699e6c2c408129358becc8da0ce8','2025-11-29 15:19:37','2025-11-29 02:19:37'),(26,1,'36c9bb7c50be0ebaf493efb91be1e3839510be8d82f59745429f9a9a5c75c01b','2025-12-01 08:29:10','2025-11-30 19:29:10'),(27,1,'9fa8b502ffb5bec33e2a07741d0d8ef60633a455e93a9df62ed3e8615e673104','2025-12-01 08:55:01','2025-11-30 19:55:01'),(28,1,'55e593fcabeead3d74241170aca64e63fc1fe8cb4b0f73e04382a8b4d8269e6c','2025-12-01 09:40:11','2025-11-30 20:40:11'),(29,1,'df6572b52cb45dbf07511816048b4195f971400b1623b7f3ce00afbd992b521f','2025-12-01 10:16:14','2025-11-30 21:16:14'),(30,1,'41b5661a6cee2ec476a24d5b73a5a7454f8f3a8f980195819b162163914798af','2025-12-01 10:31:31','2025-11-30 21:31:31'),(31,1,'4e632767a6a363498e57886270f432bba108f81a9821647312c4c8a383bbef95','2025-12-01 10:39:16','2025-11-30 21:39:16'),(32,1,'0db331ff41c96615efa9ebb86de8528beed6cea8ee58dd3776965794827cf8cc','2025-12-02 14:39:23','2025-12-02 01:39:23'),(33,1,'4533fcf5e2577a372329f735bb373a06425398c6b8804356c8cd3795ab5261fb','2025-12-02 15:34:27','2025-12-02 02:34:27'),(34,1,'17278a17b2939bd41cfba3b6e3d24ff9e75d624cee96baf6fe055617646903d8','2025-12-02 16:01:51','2025-12-02 03:01:51'),(35,1,'0e2e4f2d2bad1e3f77048757b916f90d47877bad8348a3740515cb8c3a8e8ba0','2025-12-02 17:02:31','2025-12-02 04:02:31'),(36,1,'653bf563210c405b6bf8c4507b5ce6b66ace6e9bbe1b2fb21b2f259af76dcdc9','2025-12-02 17:04:53','2025-12-02 04:04:53'),(37,1,'37630fef1296bac2118d16d898cc9fed09c78e99a45d1725d6fabd53890c7fbb','2025-12-02 17:07:09','2025-12-02 04:07:09'),(38,1,'5c231789d443949d86e534e0099e99996480fa5a0bb60723581ae55693c6568e','2025-12-02 17:10:30','2025-12-02 04:10:30');
/*!40000 ALTER TABLE `sesiones_api` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Givi','admin@buzz.com','$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G','admin','activo','2025-08-09 17:43:16','2025-12-02 01:39:23',0),(4,'Gisvel','gisvelaray@gmail.com','$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G','operador','activo','2025-11-30 18:13:22','2025-11-30 18:13:28',0),(36,'GiviSupervisor','supervisor@buzz.com','$2y$10$m389cp0CKSWLAODHmzL1Z.zyB8XnGt8BCDFmtoqI781PZcD2EjZna','supervisor','activo','2025-08-14 01:32:53',NULL,0),(37,'GiviOperador','operador@buzz.com','$2y$10$7ofF41nU06Ct9hl90TdzguI8eSxA90RCq4K/i7yWSpic01oTiLCLC','operador','activo','2025-08-14 01:33:00',NULL,0),(63,'Juan Luis Chaurant','juanluischaurant@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','activo','2025-11-30 21:18:35',NULL,0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_inventario`
--

DROP TABLE IF EXISTS `v_inventario`;
/*!50001 DROP VIEW IF EXISTS `v_inventario`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_inventario` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `descripcion`,
  1 AS `precio`,
  1 AS `stock`,
  1 AS `veces_vendido`,
  1 AS `total_vendido`,
  1 AS `ingresos_totales` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_low_stock`
--

DROP TABLE IF EXISTS `v_low_stock`;
/*!50001 DROP VIEW IF EXISTS `v_low_stock`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_low_stock` AS SELECT
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

--
-- Temporary table structure for view `v_op_backup`
--

DROP TABLE IF EXISTS `v_op_backup`;
/*!50001 DROP VIEW IF EXISTS `v_op_backup`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_op_backup` AS SELECT
 1 AS `registro_id`,
  1 AS `operacion`,
  1 AS `detalles`,
  1 AS `fecha` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_op_import`
--

DROP TABLE IF EXISTS `v_op_import`;
/*!50001 DROP VIEW IF EXISTS `v_op_import`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_op_import` AS SELECT
 1 AS `registro_id`,
  1 AS `operacion`,
  1 AS `detalles`,
  1 AS `fecha`,
  1 AS `usuario` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `v_pedidos_pendientes`
--

DROP TABLE IF EXISTS `v_pedidos_pendientes`;
/*!50001 DROP VIEW IF EXISTS `v_pedidos_pendientes`*/;
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

--
-- Temporary table structure for view `v_rotacion_inventario`
--

DROP TABLE IF EXISTS `v_rotacion_inventario`;
/*!50001 DROP VIEW IF EXISTS `v_rotacion_inventario`*/;
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

--
-- Temporary table structure for view `v_top5_down`
--

DROP TABLE IF EXISTS `v_top5_down`;
/*!50001 DROP VIEW IF EXISTS `v_top5_down`*/;
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

--
-- Temporary table structure for view `v_top5_up`
--

DROP TABLE IF EXISTS `v_top5_up`;
/*!50001 DROP VIEW IF EXISTS `v_top5_up`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `v_top5_up` AS SELECT
 1 AS `id`,
  1 AS `sku`,
  1 AS `nombre`,
  1 AS `total_vendido` */;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `v_inventario`
--

/*!50001 DROP VIEW IF EXISTS `v_inventario`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,count(`d`.`id`) AS `veces_vendido`,sum(`d`.`cantidad`) AS `total_vendido`,sum(`d`.`cantidad` * `d`.`precio_unitario`) AS `ingresos_totales` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by sum(`d`.`cantidad`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_low_stock`
--

/*!50001 DROP VIEW IF EXISTS `v_low_stock`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_low_stock` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,`p`.`estado` AS `estado`,`p`.`creado_at` AS `creado_at`,`p`.`actualizado_at` AS `actualizado_at`,`p`.`stock` <= `p`.`min_stock` AS `bajo_minimo` from `productos` `p` where `p`.`stock` <= `p`.`min_stock` and `p`.`estado` <> 'agotado' order by `p`.`stock` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_op_backup`
--

/*!50001 DROP VIEW IF EXISTS `v_op_backup`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_op_backup` AS select `backups`.`id` AS `registro_id`,'Backup realizado' AS `operacion`,concat('Archivo: ',`backups`.`archivo`,' | Tamaño: ',format(`backups`.`tamaño_bytes` / 1048576,2),' MB') AS `detalles`,`backups`.`fecha` AS `fecha` from `backups` order by `backups`.`fecha` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_op_import`
--

/*!50001 DROP VIEW IF EXISTS `v_op_import`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_op_import` AS select `i`.`id` AS `registro_id`,'Importación CSV' AS `operacion`,concat('Archivo: ',`i`.`archivo_nombre`,' | Estado: ',`i`.`estado`) AS `detalles`,`i`.`fecha` AS `fecha`,`u`.`nombre` AS `usuario` from (`importaciones` `i` left join `usuarios` `u` on(`i`.`usuario_id` = `u`.`id`)) where `i`.`tipo` = 'csv' order by `i`.`fecha` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_pedidos_pendientes`
--

/*!50001 DROP VIEW IF EXISTS `v_pedidos_pendientes`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_pedidos_pendientes` AS select `ped`.`id` AS `id`,`ped`.`referencia` AS `referencia`,`ped`.`fecha_creacion` AS `fecha_creacion`,`ped`.`total` AS `total`,`ped`.`estado_pago` AS `estado_pago`,`ped`.`estado_envio` AS `estado_envio`,`ped`.`direccion_envio` AS `direccion_envio`,`ped`.`notas_envio` AS `notas_envio`,`ped`.`actualizado_at` AS `actualizado_at`,`c`.`nombre` AS `cliente_nombre`,`c`.`email` AS `cliente_email`,`u`.`nombre` AS `creado_por_nombre` from ((`pedidos` `ped` join `clientes` `c` on(`ped`.`cliente_id` = `c`.`id`)) left join `usuarios` `u` on(`ped`.`creado_por` = `u`.`id`)) where `ped`.`estado_pago` = 'no_pagado' or `ped`.`estado_envio` in ('pendiente','enviado') order by `ped`.`fecha_creacion` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_rotacion_inventario`
--

/*!50001 DROP VIEW IF EXISTS `v_rotacion_inventario`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_rotacion_inventario` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`stock` AS `stock`,`p`.`min_stock` AS `min_stock`,ifnull(sum(`d`.`cantidad`),0) AS `total_vendido`,round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) AS `porcentaje_rotacion`,case when `p`.`stock` <= `p`.`min_stock` then 'CRÍTICO' when ifnull(sum(`d`.`cantidad`),0) = 0 then 'ESTANCADO' else 'NORMAL' end AS `estado_rotacion` from (`productos` `p` left join `detalle_pedido` `d` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by round(ifnull(sum(`d`.`cantidad`),0) / greatest(`p`.`stock`,1) * 100,2) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_top5_down`
--

/*!50001 DROP VIEW IF EXISTS `v_top5_down`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_top5_down` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,`p`.`descripcion` AS `descripcion`,`p`.`precio` AS `precio`,`p`.`stock` AS `stock`,`p`.`estado` AS `estado` from `productos` `p` where !(`p`.`id` in (select distinct `detalle_pedido`.`producto_id` from `detalle_pedido`)) order by `p`.`nombre` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_top5_up`
--

/*!50001 DROP VIEW IF EXISTS `v_top5_up`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_top5_up` AS select `p`.`id` AS `id`,`p`.`sku` AS `sku`,`p`.`nombre` AS `nombre`,sum(`d`.`cantidad`) AS `total_vendido` from (`detalle_pedido` `d` join `productos` `p` on(`p`.`id` = `d`.`producto_id`)) group by `p`.`id` order by sum(`d`.`cantidad`) desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-02  1:14:16
