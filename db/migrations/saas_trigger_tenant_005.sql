-- =====================================================
-- MIGRACIÓN 005: empresa_id en los triggers que escriben en tablas tenant
-- =====================================================
--
-- Un trigger escribe en la base igual que un repositorio, pero ningún análisis
-- del código PHP lo muestra. La migración 004 puso `logs` y
-- `movimientos_inventario` con empresa_id NOT NULL, y los triggers seguían
-- insertando sin la columna. Eso produjo dos fallos distintos:
--
--   1. ERROR DURO (rompe la operación):
--      trg_log_backup inserta en `logs` sin empresa_id, y `logs` no tiene
--      default. Como el trigger corre dentro del INSERT sobre `backups`, el
--      error tumba el respaldo entero:
--        "Field 'empresa_id' doesn't have a default value"
--      Lo mismo ocurre con trg_log_importacion al procesar un CSV.
--      Efecto observado: la tabla `backups` tiene 0 filas, los respaldos
--      automáticos no se registraban desde que se aplicó la 004.
--
--   2. FUGA SILENCIOSA (no da error, escribe en la empresa equivocada):
--      trg_cargo_confirmado y trg_cargo_anulado insertan en
--      `movimientos_inventario`, que sí tiene DEFAULT 1. Al confirmar o anular
--      un cargo de la empresa 14, el movimiento de inventario se guardaba en
--      la empresa 1 sin ningún error. Esto es la misma fuga de §1.0 del plan,
--      pero por un camino invisible al análisis de repositorios.
--
-- La corrección es atribuir el movimiento al tenant de la fila que dispara el
-- trigger (NEW.empresa_id), que es el único dato correcto disponible: el
-- movimiento pertenece al cargo, y el cargo pertenece a esa empresa.
--
-- Se añade además el filtro de empresa en el UPDATE de productos. Antes el
-- alcance venia solo del JOIN con detalle_cargo_existencias, lo que es
-- implícito: si una linea de detalle quedara desalineada, el trigger ajustaria
-- el stock de un producto de otra empresa.
--
-- Idempotente: cada trigger se elimina antes de recrearse.

-- ---------------------------------------------------------------
-- trg_log_backup: hereda el empresa_id del respaldo
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_log_backup;
DELIMITER //
CREATE TRIGGER trg_log_backup
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
END//
DELIMITER ;

-- ---------------------------------------------------------------
-- trg_log_importacion: hereda el empresa_id de la importacion
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_log_importacion;
DELIMITER //
CREATE TRIGGER trg_log_importacion
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
END//
DELIMITER ;

-- ---------------------------------------------------------------
-- trg_cargo_anulado: el ajuste negativo pertenece al cargo que lo origina
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_cargo_anulado;
DELIMITER //
CREATE TRIGGER trg_cargo_anulado
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
END//
DELIMITER ;

-- ---------------------------------------------------------------
-- trg_cargo_confirmado: la entrada de stock pertenece al cargo
-- ---------------------------------------------------------------
DROP TRIGGER IF EXISTS trg_cargo_confirmado;
DELIMITER //
CREATE TRIGGER trg_cargo_confirmado
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
END//
DELIMITER ;

-- Verificacion: los cuatro triggers deben existir y sus cuerpos mencionar
-- empresa_id. El SELECT es de solo lectura y el runner lo ejecuta aparte.
SELECT TRIGGER_NAME
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = DATABASE()
  AND TRIGGER_NAME IN (
      'trg_log_backup', 'trg_log_importacion',
      'trg_cargo_anulado', 'trg_cargo_confirmado'
  )
ORDER BY TRIGGER_NAME;
