-- =====================================================
-- MIGRACIÓN: Transferencias entre Almacenes
-- Sistema de transferencia de stock entre ubicaciones
-- =====================================================

-- Tabla principal de transferencias
CREATE TABLE IF NOT EXISTS transferencias_almacen (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Código único (TRF-YYYYMMDD-NNN)',

    -- Almacenes origen y destino (INT UNSIGNED para coincidir con almacenes.id)
    almacen_origen_id INT UNSIGNED NOT NULL,
    almacen_destino_id INT UNSIGNED NOT NULL,

    -- Fechas
    fecha_solicitud DATE NOT NULL,
    fecha_envio DATE DEFAULT NULL,
    fecha_recepcion DATE DEFAULT NULL,

    -- Estado: borrador, en_transito, recibido, cancelado
    estado ENUM('borrador', 'en_transito', 'recibido', 'cancelado') DEFAULT 'borrador',

    -- Información adicional
    motivo VARCHAR(255) DEFAULT NULL COMMENT 'Motivo de la transferencia',
    notas TEXT DEFAULT NULL,
    referencia_externa VARCHAR(100) DEFAULT NULL COMMENT 'Guía de envío u otro ref',

    -- Auditoría (INT UNSIGNED para coincidir con usuarios.id)
    solicitado_por INT UNSIGNED NOT NULL COMMENT 'Usuario que solicita',
    enviado_por INT UNSIGNED DEFAULT NULL COMMENT 'Usuario que envía',
    recibido_por INT UNSIGNED DEFAULT NULL COMMENT 'Usuario que recibe',

    -- Timestamps
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_solicitud, fecha_envio),
    INDEX idx_almacenes (almacen_origen_id, almacen_destino_id),
    INDEX idx_almacen_origen (almacen_origen_id),
    INDEX idx_almacen_destino (almacen_destino_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Detalle de productos en la transferencia
CREATE TABLE IF NOT EXISTS detalle_transferencia (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transferencia_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,

    -- Cantidades
    cantidad_solicitada INT NOT NULL DEFAULT 0 COMMENT 'Cantidad a transferir',
    cantidad_enviada INT DEFAULT NULL COMMENT 'Cantidad que se envía (puede diferir)',
    cantidad_recibida INT DEFAULT NULL COMMENT 'Cantidad recibida (verificación)',

    -- Notas por línea
    notas VARCHAR(255) DEFAULT NULL,

    -- Evitar duplicados de producto en misma transferencia
    UNIQUE KEY uk_transferencia_producto (transferencia_id, producto_id),
    INDEX idx_producto (producto_id),
    INDEX idx_transferencia (transferencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FOREIGN KEYS (separados para mayor control)
-- Si alguno falla, comenta esa línea específica
-- =====================================================

-- FK para almacenes
ALTER TABLE transferencias_almacen
    ADD CONSTRAINT fk_trf_almacen_origen FOREIGN KEY (almacen_origen_id) REFERENCES almacenes(id) ON DELETE RESTRICT,
    ADD CONSTRAINT fk_trf_almacen_destino FOREIGN KEY (almacen_destino_id) REFERENCES almacenes(id) ON DELETE RESTRICT;

-- FK para usuarios (opcional - comenta si da error)
-- ALTER TABLE transferencias_almacen
--     ADD CONSTRAINT fk_trf_solicitado_por FOREIGN KEY (solicitado_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
--     ADD CONSTRAINT fk_trf_enviado_por FOREIGN KEY (enviado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
--     ADD CONSTRAINT fk_trf_recibido_por FOREIGN KEY (recibido_por) REFERENCES usuarios(id) ON DELETE SET NULL;

-- FK para detalle
ALTER TABLE detalle_transferencia
    ADD CONSTRAINT fk_dt_transferencia FOREIGN KEY (transferencia_id) REFERENCES transferencias_almacen(id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_dt_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE RESTRICT;

-- =====================================================
-- TRIGGER: Generar código automático
-- =====================================================

DROP TRIGGER IF EXISTS trg_transferencia_codigo;
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

-- =====================================================
-- COMENTARIO DE MIGRACIÓN
-- =====================================================
-- Ejecutar con: SOURCE c:/xampp/htdocs/BuzzDM/db/migrations/transferencias_almacen.sql;
-- O importar desde phpMyAdmin
-- Si hay errores de FK, comenta las líneas de ALTER TABLE que fallan
