-- Tabla para tokens de recuperación de contraseña
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
  CONSTRAINT `password_recovery_ibfk_1` FOREIGN KEY (`usuario_id`)
    REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

