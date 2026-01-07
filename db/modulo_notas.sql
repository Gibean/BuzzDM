-- ============================================================================

-- Limpieza previa para re-instalación limpia
DROP TABLE IF EXISTS `notas_checklists`;
DROP TABLE IF EXISTS `notas_adjuntos`;
DROP TABLE IF EXISTS `notas_items_inventario`;
DROP TABLE IF EXISTS `notas_kanban`;

-- Tabla principal de notas (Tarjetas del Kanban)
CREATE TABLE IF NOT EXISTS `notas_kanban` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `titulo` VARCHAR(150) NOT NULL COMMENT 'Título de la tarjeta',
    `cliente_nombre` VARCHAR(150) DEFAULT NULL COMMENT 'Nombre del cliente',
    `telefono` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,

    -- Contenido enriquecido
    `contenido` TEXT COMMENT 'Descripción libre detallada',

    -- Estado = Columna del Kanban
    `estado` ENUM('pendiente', 'seguimiento', 'resuelto', 'cancelado') NOT NULL DEFAULT 'pendiente',

    -- Prioridad para orden visual
    `prioridad` ENUM('baja', 'media', 'alta') NOT NULL DEFAULT 'media',
    `orden` INT DEFAULT 0 COMMENT 'Orden dentro de la misma columna',

    -- Historial del pedido si aplica
    `pedido_vinculado_id` INT UNSIGNED DEFAULT NULL COMMENT 'Link opcional a un pedido real si se concreta',

    -- Nuevos campos para dinamismo tipo Trello
    `fecha_entrega` DATETIME DEFAULT NULL,
    `etiquetas` VARCHAR(255) DEFAULT NULL COMMENT 'JSON o lista separada por comas',
    `miembros` TEXT DEFAULT NULL COMMENT 'JSON de IDs de usuarios asignados',

    -- Auditoría
    `usuario_id` INT UNSIGNED NOT NULL COMMENT 'Quién creó la nota',
    `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pedido_vinculado_id`) REFERENCES `pedidos`(`id`) ON DELETE SET NULL,
    INDEX `idx_estado_orden` (`estado`, `orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Productos consultados en la nota (Autocomplete)
CREATE TABLE IF NOT EXISTS `notas_items_inventario` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nota_id` INT UNSIGNED NOT NULL,
    `producto_id` INT UNSIGNED NOT NULL,
    `cantidad` INT UNSIGNED DEFAULT 1,

    FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Checklists dentro de la nota
CREATE TABLE IF NOT EXISTS `notas_checklists` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nota_id` INT UNSIGNED NOT NULL,
    `tarea` VARCHAR(255) NOT NULL,
    `completada` TINYINT(1) DEFAULT 0,
    `orden` INT DEFAULT 0,
    FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para Adjuntos (Links de archivos)
CREATE TABLE IF NOT EXISTS `notas_adjuntos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nota_id` INT UNSIGNED NOT NULL,
    `nombre` VARCHAR(150) NOT NULL,
    `url` TEXT NOT NULL,
    `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`nota_id`) REFERENCES `notas_kanban`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos permisos básicos para el nuevo módulo
-- (Asumiendo que los roles son 'admin', 'supervisor', 'operador')
INSERT IGNORE INTO `permisos_rol` (`rol`, `pagina`) VALUES
('admin', 'todo.php'),
('admin', 'notas_actions.php'),
('supervisor', 'todo.php'),
('supervisor', 'notas_actions.php'),
('operador', 'todo.php'),
('operador', 'notas_actions.php');

