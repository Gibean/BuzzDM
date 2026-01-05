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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` VALUES (14,'dbuzzbackup_2025-12-25_02-06-16.sql','C:\\xampp\\htdocs\\BuzzDM\\php/../backups/dbuzzbackup_2025-12-25_02-06-16.sql',75226,'2025-12-25 01:06:17','automatico','completado','Backup automático creado exitosamente (fallback)','{\"tablas_exportadas\":13,\"duracion_ms\":1276,\"tamanio_mb\":0.07,\"tipo_ejecucion\":\"http\",\"metodo\":\"fallback\"}',NULL);
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
-- Table structure for table `cargos_existencias`
--

DROP TABLE IF EXISTS `cargos_existencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cargos_existencias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL COMMENT 'Formato: CGO-YYYY-NNNN',
  `fecha` date NOT NULL,
  `concepto` enum('compra','donacion','ajuste_positivo','devolucion_cliente','inventario_inicial','otro') NOT NULL DEFAULT 'compra',
  `concepto_otro` varchar(100) DEFAULT NULL COMMENT 'Descripción si concepto=otro',
  `proveedor_id` int(11) DEFAULT NULL,
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
  CONSTRAINT `fk_cargo_anulado` FOREIGN KEY (`anulado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_confirmado` FOREIGN KEY (`confirmado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_cargo_registrado` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Encabezado de cargos masivos de existencias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargos_existencias`
--

LOCK TABLES `cargos_existencias` WRITE;
/*!40000 ALTER TABLE `cargos_existencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `cargos_existencias` ENABLE KEYS */;
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
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_cargo_confirmado`
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
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_uca1400_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_cargo_anulado`
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'Papelería','Artículos de papelería generales: cuadernos, bolígrafos, goma, etc.'),(2,'Útiles escolares','Artículos pensados para estudiantes: lápices, reglas, estuches'),(3,'Oficina','Papel, resmas, archivadores y suministros de oficina'),(4,'Artículos de arte','Material para dibujo y pintura: acuarelas, cartulinas, marcadores'),(5,'Adhesivos y pegamentos','Pegamento en barra, líquido, cintas adhesivas'),(6,'Cajas y archivadores','Archivadores, carpetas y organizadores');
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
INSERT INTO `clientes` VALUES ('E-30003000','Juan Almeida','misogino.300@gmail.com','+34900 861 494','Municipio Santiago','Epale Juan Almeida misógino yo no conozco de puntas la única punta que conozco es esta… la punta de la teta porque como verás estoy amamantando desde la izquierda cómoda es bien depinga verdad?? hablar y criticar a los demás y como tú me hablabas de cacao yo no tengo cacao para ti tengo patadas y te patearé el culo donde te vea #TePateareElCuloDondeTeVea y no por lo que has dicho respecto al municipio Santiago de Mariño sino por meterte conmigo en mi condición de mujer',0,'2025-12-02 05:14:09','2025-12-24 15:36:41'),('V-10000001','María López','m.lopez@example.com','+58 412 345 6789','Calle Comercio 123, Caracas','',0,'2025-12-01 13:00:00',NULL),('V-10000002','José Pérez','j.perez@example.com','+58 414 987 6543','Av. Bolívar 45, Valencia','Cliente frecuente',1,'2025-11-20 15:30:00',NULL),('V-10000003','Librería La esquina','contabilidad@laesquina.com','+58 212 999 8888','C.C. Centro, Local 12','Mayorista',0,'2025-10-05 18:22:00',NULL),('V-30535128','Gisvel Aray','','+584128759068','Calle Luisa Caceres de Arismendi','<3',1,'2025-11-21 00:56:28',NULL),('V-99999999','Test Restore','test@restore.com','+581234567890','','',0,'2025-12-02 05:18:09','2025-12-02 05:20:58');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuraciones`
--

DROP TABLE IF EXISTS `configuraciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuraciones`
--

LOCK TABLES `configuraciones` WRITE;
/*!40000 ALTER TABLE `configuraciones` DISABLE KEYS */;
INSERT INTO `configuraciones` VALUES (1,'backup_fallback_enabled','1','Habilitar sistema de backup automático fallback','2025-12-25 01:04:32'),(5,'store_name','Librería El Estudiante','Nombre mostrado en factura y sitio','2025-12-25 15:31:03'),(6,'currency','VES','Moneda del sistema','2025-12-25 15:31:03'),(7,'tax_percent','12.00','Porcentaje de IVA aplicado a ventas','2025-12-25 15:31:03');
/*!40000 ALTER TABLE `configuraciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_cargo_existencias`
--

DROP TABLE IF EXISTS `detalle_cargo_existencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Líneas de productos en cada cargo de existencias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_cargo_existencias`
--

LOCK TABLES `detalle_cargo_existencias` WRITE;
/*!40000 ALTER TABLE `detalle_cargo_existencias` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_cargo_existencias` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_pedido`
--

LOCK TABLES `detalle_pedido` WRITE;
/*!40000 ALTER TABLE `detalle_pedido` DISABLE KEYS */;
INSERT INTO `detalle_pedido` VALUES (3,1,1,1,10.00,10.00),(9,2,2,2,2.50,5.00),(10,2,4,3,1.20,3.60),(11,2,8,1,1.10,1.10),(12,3,5,1,8.50,8.50),(13,3,7,2,0.80,1.60),(14,4,11,10,6.50,65.00),(15,4,2,20,2.50,50.00),(16,5,4,5,1.20,6.00),(17,5,6,2,3.75,7.50),(18,6,3,2,5.00,10.00),(19,6,7,1,0.80,0.80),(20,7,9,1,15.00,15.00),(21,7,8,2,1.10,2.20),(22,8,12,1,22.00,22.00),(23,8,5,1,8.50,8.50),(24,9,10,1,4.25,4.25),(25,9,2,3,2.50,7.50),(26,10,4,10,1.20,12.00),(27,10,13,2,2.00,4.00),(28,11,3,5,5.00,25.00),(29,11,11,2,6.50,13.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa`
--

LOCK TABLES `empresa` WRITE;
/*!40000 ALTER TABLE `empresa` DISABLE KEYS */;
INSERT INTO `empresa` VALUES (1,'J','J-12345678-9','Librería Papelería El Estudiante',NULL,'Av. Principal 100, Caracas','+58 212 555 0000','info@elestudiante.com','2025-12-25 15:31:03',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,1,'Solicitud de recuperación de contraseña','Email: admin@buzz.com','2025-11-29 02:18:49'),(2,1,'Contraseña cambiada','Mediante recuperación de contraseña','2025-11-29 02:19:17'),(3,NULL,'Backup realizado','Archivo: backup_dbuzz_2025-12-02_05-54-21.sql | Tamaño: 0.06 MB','2025-12-02 04:54:22'),(4,1,'Backup Manual','{\"archivo\":\"backup_dbuzz_2025-12-02_05-54-21.sql\",\"tamanio_mb\":0.06,\"duracion_ms\":770,\"tablas\":16}','2025-12-02 04:54:22'),(5,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-03-19.sql | Tamaño: 0.06 MB','2025-12-02 05:03:20'),(6,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-03-19.sql\",\"tamanio_mb\":0.06,\"duracion_ms\":531,\"tablas\":16}','2025-12-02 05:03:20'),(7,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-04-28.sql | Tamaño: 0.07 MB','2025-12-02 05:04:43'),(8,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-04-28.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":15079,\"tablas\":16}','2025-12-02 05:04:43'),(9,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-02_06-07-56.sql\",\"duracion_ms\":800,\"resultado\":\"Exitoso\"}','2025-12-02 05:08:54'),(10,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-11-25.sql | Tamaño: 0.07 MB','2025-12-02 05:11:26'),(11,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-11-25.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":481,\"tablas\":16}','2025-12-02 05:11:26'),(12,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_06-14-15.sql | Tamaño: 0.07 MB','2025-12-02 05:14:16'),(13,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_06-14-15.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":459,\"tablas\":15}','2025-12-02 05:14:16'),(14,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_08-01-41.sql | Tamaño: 0.07 MB','2025-12-02 07:01:42'),(15,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_08-01-41.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":766,\"tablas\":14}','2025-12-02 07:01:42'),(16,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_08-05-58.sql | Tamaño: 0.07 MB','2025-12-02 07:05:59'),(17,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_08-05-58.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":520,\"tablas\":14}','2025-12-02 07:05:59'),(18,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_08-06-53.sql | Tamaño: 0.07 MB','2025-12-02 07:06:54'),(19,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_08-06-53.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":521,\"tablas\":14}','2025-12-02 07:06:54'),(20,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-02_08-08-01.sql | Tamaño: 0.07 MB','2025-12-02 07:08:01'),(21,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-02_08-08-01.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":493,\"tablas\":14}','2025-12-02 07:08:01'),(22,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-02_08-10-45.sql\",\"duracion_ms\":1158,\"resultado\":\"Exitoso\"}','2025-12-24 02:11:04'),(23,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-24_16-29-00.sql\",\"duracion_ms\":1291,\"resultado\":\"Exitoso\"}','2025-12-24 15:29:31'),(24,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-24_23-08-48.sql\",\"duracion_ms\":894,\"resultado\":\"Exitoso\"}','2025-12-24 22:09:13'),(25,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-24_23-54-15.sql | Tamaño: 0.07 MB','2025-12-24 22:54:17'),(26,1,'Backup Manual','{\"archivo\":\"dbuzzbackup_2025-12-24_23-54-15.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":1791,\"tablas\":13}','2025-12-24 22:54:17'),(27,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-25_01-25-25.sql | Tamaño: 0.07 MB','2025-12-25 00:25:29'),(28,NULL,'Backup Automático (Fallback)','{\"archivo\":\"dbuzzbackup_2025-12-25_01-25-25.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":4550,\"tablas\":14,\"metodo\":\"fallback\"}','2025-12-25 00:25:29'),(29,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-25_01-30-29.sql | Tamaño: 0.07 MB','2025-12-25 00:30:30'),(30,NULL,'Backup Automático (Fallback)','{\"archivo\":\"dbuzzbackup_2025-12-25_01-30-29.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":1186,\"tablas\":13,\"metodo\":\"fallback\"}','2025-12-25 00:30:30'),(31,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-25_01-49-40.sql | Tamaño: 0.07 MB','2025-12-25 00:49:41'),(32,NULL,'Backup Automático (Fallback)','{\"archivo\":\"dbuzzbackup_2025-12-25_01-49-40.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":910,\"tablas\":13,\"metodo\":\"fallback\"}','2025-12-25 00:49:41'),(33,NULL,'Backup realizado','Archivo: dbuzzbackup_2025-12-25_02-06-16.sql | Tamaño: 0.07 MB','2025-12-25 01:06:17'),(34,NULL,'Backup Automático (Fallback)','{\"archivo\":\"dbuzzbackup_2025-12-25_02-06-16.sql\",\"tamanio_mb\":0.07,\"duracion_ms\":1276,\"tablas\":13,\"metodo\":\"fallback\"}','2025-12-25 01:06:17'),(35,1,'Restauración de Backup','{\"archivo\":\"dbuzzbackup_2025-12-25_16-34-40.sql\",\"duracion_ms\":2214,\"resultado\":\"Exitoso\"}','2025-12-25 15:34:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs_login`
--

LOCK TABLES `logs_login` WRITE;
/*!40000 ALTER TABLE `logs_login` DISABLE KEYS */;
INSERT INTO `logs_login` VALUES (1,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:43:12'),(2,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:14'),(3,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:15'),(4,NULL,'Denegado','Intento de inicio de sesión con email no existente: test@noexiste.com','2025-11-30 19:49:15'),(5,1,'Exitoso','Inicio de sesión de admin','2025-11-30 19:55:01'),(6,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 20:40:09'),(7,1,'Exitoso','Inicio de sesión de admin','2025-11-30 20:40:11'),(8,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:16:10'),(9,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:16:12'),(10,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:16:14'),(11,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:31:30'),(12,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:31:31'),(13,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-11-30 21:39:12'),(14,1,'Exitoso','Inicio de sesión de admin','2025-11-30 21:39:16'),(15,1,'Denegado','Intento de inicio de sesión con contraseña incorrecta','2025-12-02 01:38:20'),(16,1,'Exitoso','Inicio de sesión de admin','2025-12-02 01:39:23'),(17,1,'Exitoso','Inicio de sesión de admin','2025-12-02 02:34:27'),(18,1,'Exitoso','Inicio de sesión de admin','2025-12-02 03:01:51'),(19,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@example.com','2025-12-02 03:54:03'),(20,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@example.com','2025-12-02 03:56:19'),(21,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@admin.com','2025-12-02 04:00:55'),(22,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@admin.com','2025-12-02 04:01:07'),(23,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:02:31'),(24,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:04:53'),(25,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:07:09'),(26,1,'Exitoso','Inicio de sesión de admin','2025-12-02 04:10:30'),(27,1,'Exitoso','Inicio de sesión de admin','2025-12-24 15:24:08'),(28,36,'Exitoso','Inicio de sesión de supervisor','2025-12-24 15:34:58'),(29,1,'Exitoso','Inicio de sesión de admin','2025-12-24 20:45:00'),(30,1,'Exitoso','Inicio de sesión de admin','2025-12-24 22:07:28'),(31,NULL,'Denegado','Intento de inicio de sesión con email no existente: admin@admin.com','2025-12-25 00:23:35'),(32,1,'Exitoso','Inicio de sesión de admin','2025-12-25 00:25:24'),(33,1,'Exitoso','Inicio de sesión de admin','2025-12-25 12:56:24'),(34,1,'Exitoso','Inicio de sesión de admin','2025-12-25 15:04:24'),(35,1,'Exitoso','Inicio de sesión de admin','2025-12-25 15:33:54'),(36,1,'Exitoso','Inicio de sesión de admin','2025-12-25 15:43:21'),(37,1,'Exitoso','Inicio de sesión de admin','2026-01-05 16:49:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `marcas`
--

LOCK TABLES `marcas` WRITE;
/*!40000 ALTER TABLE `marcas` DISABLE KEYS */;
INSERT INTO `marcas` VALUES (1,'Pilot','Bolígrafos y roller de calidad','https://www.pilot.com'),(2,'Stabilo','Marcadores y resaltadores','https://www.stabilo.com'),(3,'Faber-Castell','Lápices y material artístico','https://www.faber-castell.com'),(4,'Staedtler','Gomas y herramientas de dibujo','https://www.staedtler.com'),(5,'Canson','Papeles y cartulinas para arte','https://www.canson.com'),(6,'Tombow','Material para pintura y acuarela','https://www.tombow.com');
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
  `cargo_id` int(10) unsigned DEFAULT NULL,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `producto_id` (`producto_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `fk_mov_cargo` (`cargo_id`),
  CONSTRAINT `fk_mov_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (13,2,100,'entrada',120,'Ingreso inicial de stock',NULL,0,120,'2025-12-25 14:00:00'),(14,3,100,'entrada',60,'Ingreso inicial de stock',NULL,0,60,'2025-12-25 14:01:00'),(15,4,100,'entrada',200,'Ingreso inicial de stock',NULL,0,200,'2025-12-25 14:02:00'),(16,5,100,'entrada',40,'Ingreso inicial de stock',NULL,0,40,'2025-12-25 14:03:00'),(17,6,100,'entrada',150,'Ingreso inicial de stock',NULL,0,150,'2025-12-25 14:04:00'),(18,7,100,'entrada',300,'Ingreso inicial de stock',NULL,0,300,'2025-12-25 14:05:00'),(19,8,100,'entrada',80,'Ingreso inicial de stock',NULL,0,80,'2025-12-25 14:06:00'),(20,9,100,'entrada',25,'Ingreso inicial de stock',NULL,0,25,'2025-12-25 14:07:00'),(21,10,100,'entrada',10,'Ingreso inicial de stock',NULL,0,10,'2025-12-25 14:08:00'),(22,11,100,'entrada',50,'Ingreso inicial de stock',NULL,0,50,'2025-12-25 14:09:00'),(23,12,100,'entrada',15,'Ingreso inicial de stock',NULL,0,15,'2025-12-25 14:10:00'),(24,13,100,'entrada',0,'Sin stock al momento del mock',NULL,0,0,'2025-12-25 14:11:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (1,'V-30535128','4633556592','2025-12-02 06:30:40',10.00,'pagado','pendiente','afeafe','sfaefd',NULL,'2025-12-24 22:08:12','TRK542867550',NULL,NULL),(2,'V-10000001','REF-LBR-0002','2025-12-25 15:00:00',9.70,'pagado','enviado','Calle Comercio 123, Caracas','Entrega mañana',100,'2025-12-25 15:34:22','TRK676862010',NULL,NULL),(3,'V-10000002','REF-LBR-0003','2025-12-24 19:30:00',10.10,'no_pagado','pendiente','Av. Bolívar 45, Valencia','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(4,'V-10000003','REF-LBR-0004','2025-10-05 14:15:00',115.00,'pagado','recibido','C.C. Centro, Local 12','Pedido mayorista',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(5,'V-10000001','REF-LBR-0005','2025-10-20 13:00:00',13.50,'pagado','enviado','Calle Comercio 123, Caracas','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(6,'V-10000002','REF-LBR-0006','2025-11-11 18:45:00',10.80,'no_pagado','pendiente','Av. Bolívar 45, Valencia','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(7,'V-10000001','REF-LBR-0007','2025-11-25 20:10:00',17.20,'pagado','enviado','Calle Comercio 123, Caracas','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(8,'V-10000003','REF-LBR-0008','2025-12-01 16:00:00',30.50,'pagado','pendiente','C.C. Centro, Local 12','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(9,'V-10000002','REF-LBR-0009','2025-12-05 12:30:00',11.75,'no_pagado','pendiente','Av. Bolívar 45, Valencia','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(10,'V-10000001','REF-LBR-0010','2025-12-10 22:20:00',16.00,'pagado','pendiente','Calle Comercio 123, Caracas','',100,'2025-12-25 15:31:03',NULL,NULL,NULL),(11,'V-10000003','REF-LBR-0011','2025-12-20 13:05:00',38.00,'pagado','enviado','C.C. Centro, Local 12','',100,'2025-12-25 15:31:03',NULL,NULL,NULL);
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
  `costo_promedio` decimal(12,4) DEFAULT 0.0000,
  `costo_ultimo` decimal(12,4) DEFAULT 0.0000,
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'325324324234','blah','afesfaf',10.00,0.0000,0.0000,4,10,'activo','2025-12-02 06:29:28','2025-12-24 22:08:12',NULL,NULL,NULL,NULL),(2,'LBR-0001','Cuaderno universitario 100 hojas','Cuaderno raya 100 hojas 21x27 cm',2.50,0.0000,0.0000,120,20,'activo','2025-12-25 14:00:00',NULL,1,NULL,1,NULL),(3,'LBR-0002','Cuaderno de dibujo A4 40 hojas','Papel para dibujo 120 g/m2, 40 hojas',5.00,0.0000,0.0000,60,10,'activo','2025-12-25 14:01:00',NULL,4,5,3,NULL),(4,'LBR-0003','Bolígrafo Pilot G2 negro','Bolígrafo punta 0.7 mm, tinta gel',1.20,0.0000,0.0000,200,30,'activo','2025-12-25 14:02:00',NULL,1,1,1,NULL),(5,'LBR-0004','Set Marcadores Stabilo 6 colores','Marcadores punta fina, set x6',8.50,0.0000,0.0000,40,10,'activo','2025-12-25 14:03:00',NULL,4,2,3,NULL),(6,'LBR-0005','Lápiz HB Faber-Castell (12)','Pack de 12 lápices HB',3.75,0.0000,0.0000,150,20,'activo','2025-12-25 14:04:00',NULL,2,3,2,NULL),(7,'LBR-0006','Goma de borrar Staedtler','Goma blanda sin PVC',0.80,0.0000,0.0000,300,50,'activo','2025-12-25 14:05:00',NULL,1,4,2,NULL),(8,'LBR-0007','Pegamento en barra 36g','Pegamento sólido sin ácido',1.10,0.0000,0.0000,80,20,'activo','2025-12-25 14:06:00',NULL,5,NULL,1,NULL),(9,'LBR-0008','Cartulina Canson A4 (pack 50)','Cartulina para arte, 180 g/m2, pack 50 hojas',15.00,0.0000,0.0000,25,10,'activo','2025-12-25 14:07:00',NULL,4,5,3,NULL),(10,'LBR-0009','Archivador A4 plástico','Archivador plástico A4 con anillas',4.25,0.0000,0.0000,10,20,'stock_bajo','2025-12-25 14:08:00',NULL,6,NULL,1,NULL),(11,'LBR-0010','Resma papel bond A4 80gr (500)','Resma de papel bond A4 80 g/m2, 500 hojas',6.50,0.0000,0.0000,50,10,'activo','2025-12-25 14:09:00',NULL,3,NULL,1,NULL),(12,'LBR-0011','Set acuarelas Tombow 12 colores','Set de acuarelas en pastilla, 12 colores',22.00,0.0000,0.0000,15,5,'activo','2025-12-25 14:10:00',NULL,4,6,3,NULL),(13,'LBR-0012','Marcador permanente negro','Marcador indeleble punta media',2.00,0.0000,0.0000,0,10,'agotado','2025-12-25 14:11:00',NULL,1,NULL,2,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'Distribuidora Papel S.A.','María Pérez','ventas@distribuidora-papel.com','+58 212 555 0101','Av. Comercio, Caracas','2025-12-25 15:31:03'),(2,'Importadora Útiles S.R.L.','Carlos Rojas','contacto@importutiles.com','+58 241 555 0202','Zona Industrial, Valencia','2025-12-25 15:31:03'),(3,'ArtículosCreativos C.A.','Ana Gómez','info@articuloscreativos.com','+58 261 555 0303','C.C. Arte, Maracay','2025-12-25 15:31:03');
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesiones_api`
--

LOCK TABLES `sesiones_api` WRITE;
/*!40000 ALTER TABLE `sesiones_api` DISABLE KEYS */;
INSERT INTO `sesiones_api` VALUES (24,1,'2584334646b54b429e0cb37b0a117cc1c01a1286563e5d6a6ddaa2631344080c','2025-11-29 15:15:36','2025-11-29 02:15:36'),(25,1,'a96a5e9527b24c5b34bdb87096c97d5363c3699e6c2c408129358becc8da0ce8','2025-11-29 15:19:37','2025-11-29 02:19:37'),(26,1,'36c9bb7c50be0ebaf493efb91be1e3839510be8d82f59745429f9a9a5c75c01b','2025-12-01 08:29:10','2025-11-30 19:29:10'),(27,1,'9fa8b502ffb5bec33e2a07741d0d8ef60633a455e93a9df62ed3e8615e673104','2025-12-01 08:55:01','2025-11-30 19:55:01'),(28,1,'55e593fcabeead3d74241170aca64e63fc1fe8cb4b0f73e04382a8b4d8269e6c','2025-12-01 09:40:11','2025-11-30 20:40:11'),(29,1,'df6572b52cb45dbf07511816048b4195f971400b1623b7f3ce00afbd992b521f','2025-12-01 10:16:14','2025-11-30 21:16:14'),(30,1,'41b5661a6cee2ec476a24d5b73a5a7454f8f3a8f980195819b162163914798af','2025-12-01 10:31:31','2025-11-30 21:31:31'),(31,1,'4e632767a6a363498e57886270f432bba108f81a9821647312c4c8a383bbef95','2025-12-01 10:39:16','2025-11-30 21:39:16'),(32,1,'0db331ff41c96615efa9ebb86de8528beed6cea8ee58dd3776965794827cf8cc','2025-12-02 14:39:23','2025-12-02 01:39:23'),(33,1,'4533fcf5e2577a372329f735bb373a06425398c6b8804356c8cd3795ab5261fb','2025-12-02 15:34:27','2025-12-02 02:34:27'),(34,1,'17278a17b2939bd41cfba3b6e3d24ff9e75d624cee96baf6fe055617646903d8','2025-12-02 16:01:51','2025-12-02 03:01:51'),(35,1,'0e2e4f2d2bad1e3f77048757b916f90d47877bad8348a3740515cb8c3a8e8ba0','2025-12-02 17:02:31','2025-12-02 04:02:31'),(36,1,'653bf563210c405b6bf8c4507b5ce6b66ace6e9bbe1b2fb21b2f259af76dcdc9','2025-12-02 17:04:53','2025-12-02 04:04:53'),(37,1,'37630fef1296bac2118d16d898cc9fed09c78e99a45d1725d6fabd53890c7fbb','2025-12-02 17:07:09','2025-12-02 04:07:09'),(38,1,'5c231789d443949d86e534e0099e99996480fa5a0bb60723581ae55693c6568e','2025-12-02 17:10:30','2025-12-02 04:10:30'),(39,1,'4c4f17cf49152c0015ae8bca4a0601ed96960f9846cac1be111ee3e4438c6f4b','2025-12-25 04:24:08','2025-12-24 15:24:08'),(40,36,'0f27a12af252cff6c5b522a6eec580ac02b1e8902e31c7390e65f37dc842cbae','2025-12-25 04:34:58','2025-12-24 15:34:58'),(41,1,'92d66929359557e770d3e16351214832bb9f9d6d254c29413db361a272180d87','2025-12-25 09:45:00','2025-12-24 20:45:00'),(42,1,'34b4c7eaa7d90a550e90b4be5f6862043cbea1b542ca0e760bdd0d50b7a2f633','2025-12-25 11:07:28','2025-12-24 22:07:28'),(43,1,'dc0c4d5145af56435b5bfd4637fcb1aa196f8b2dd8e7bd087d8d12f4244df1b7','2025-12-25 13:25:24','2025-12-25 00:25:24'),(44,1,'9fda354b6010c9581164c04e56715afcd7045f9bae98e6df7d565fa6ef48685a','2025-12-26 01:56:24','2025-12-25 12:56:24'),(45,1,'98e6469c7274168207b52d7de22335a66418a30e1692b47e7a3d44b44044b64c','2025-12-26 04:04:24','2025-12-25 15:04:24'),(46,1,'ac9fadc2fb8592ebad64390f580d6c481d0423665c70118b2d859857cd8659dd','2025-12-26 04:33:54','2025-12-25 15:33:54'),(47,1,'83606cbcd9b21bf70adb60e0c5f02f8d3178f191304fef543370ff527d8af75f','2025-12-26 04:43:21','2025-12-25 15:43:21'),(48,1,'e2f6f1bde2f7a16327b7c99716bc2c49f2ffb9f739921180ae16b435682217a1','2026-01-06 05:49:55','2026-01-05 16:49:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Givi','admin@buzz.com','$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G','admin','activo','2025-08-09 17:43:16','2025-12-02 01:39:23',0),(4,'Gisvel','gisvelaray@gmail.com','$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G','operador','activo','2025-11-30 18:13:22','2025-11-30 18:13:28',0),(36,'GiviSupervisor','supervisor@buzz.com','$2y$10$m389cp0CKSWLAODHmzL1Z.zyB8XnGt8BCDFmtoqI781PZcD2EjZna','supervisor','activo','2025-08-14 01:32:53',NULL,0),(37,'GiviOperador','operador@buzz.com','$2y$10$7ofF41nU06Ct9hl90TdzguI8eSxA90RCq4K/i7yWSpic01oTiLCLC','operador','activo','2025-08-14 01:33:00',NULL,0),(63,'Juan Luis Chaurant','juanluischaurant@gmail.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','activo','2025-11-30 21:18:35',NULL,0),(100,'Operador Libreria','operador.libreria@example.com','$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G','operador','activo','2025-12-01 12:00:00',NULL,0);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `v_cargos_detalle_completo`
--

DROP TABLE IF EXISTS `v_cargos_detalle_completo`;
/*!50001 DROP VIEW IF EXISTS `v_cargos_detalle_completo`*/;
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

--
-- Temporary table structure for view `v_cargos_resumen`
--

DROP TABLE IF EXISTS `v_cargos_resumen`;
/*!50001 DROP VIEW IF EXISTS `v_cargos_resumen`*/;
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
-- Final view structure for view `v_cargos_detalle_completo`
--

/*!50001 DROP VIEW IF EXISTS `v_cargos_detalle_completo`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cargos_detalle_completo` AS select `c`.`id` AS `cargo_id`,`c`.`codigo` AS `cargo_codigo`,`c`.`fecha` AS `cargo_fecha`,`c`.`estado` AS `cargo_estado`,`d`.`id` AS `detalle_id`,`d`.`producto_id` AS `producto_id`,`pr`.`sku` AS `producto_sku`,`pr`.`nombre` AS `producto_nombre`,`d`.`cantidad` AS `cantidad`,`d`.`costo_unitario` AS `costo_unitario`,`d`.`subtotal` AS `subtotal`,`d`.`notas` AS `detalle_notas` from ((`cargos_existencias` `c` join `detalle_cargo_existencias` `d` on(`c`.`id` = `d`.`cargo_id`)) join `productos` `pr` on(`d`.`producto_id` = `pr`.`id`)) order by `c`.`id` desc,`d`.`id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `v_cargos_resumen`
--

/*!50001 DROP VIEW IF EXISTS `v_cargos_resumen`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_uca1400_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `v_cargos_resumen` AS select `c`.`id` AS `id`,`c`.`codigo` AS `codigo`,`c`.`fecha` AS `fecha`,`c`.`concepto` AS `concepto`,case `c`.`concepto` when 'compra' then 'Compra' when 'donacion' then 'Donación' when 'ajuste_positivo' then 'Ajuste Positivo' when 'devolucion_cliente' then 'Devolución Cliente' when 'inventario_inicial' then 'Inventario Inicial' when 'otro' then coalesce(`c`.`concepto_otro`,'Otro') end AS `concepto_texto`,`p`.`nombre` AS `proveedor_nombre`,`c`.`referencia_externa` AS `referencia_externa`,`c`.`total_productos` AS `total_productos`,`c`.`total_unidades` AS `total_unidades`,`c`.`valor_total` AS `valor_total`,`c`.`estado` AS `estado`,case `c`.`estado` when 'borrador' then 'Borrador' when 'confirmado' then 'Confirmado' when 'anulado' then 'Anulado' end AS `estado_texto`,`u`.`nombre` AS `registrado_por_nombre`,`c`.`creado_at` AS `creado_at`,`uc`.`nombre` AS `confirmado_por_nombre`,`c`.`fecha_confirmacion` AS `fecha_confirmacion`,`ua`.`nombre` AS `anulado_por_nombre`,`c`.`fecha_anulacion` AS `fecha_anulacion`,`c`.`motivo_anulacion` AS `motivo_anulacion` from ((((`cargos_existencias` `c` left join `proveedores` `p` on(`c`.`proveedor_id` = `p`.`id`)) left join `usuarios` `u` on(`c`.`registrado_por` = `u`.`id`)) left join `usuarios` `uc` on(`c`.`confirmado_por` = `uc`.`id`)) left join `usuarios` `ua` on(`c`.`anulado_por` = `ua`.`id`)) order by `c`.`creado_at` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

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

-- Dump completed on 2026-01-05 12:49:58
