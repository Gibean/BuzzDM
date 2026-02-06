-- =====================================================
-- MIGRACIÓN: Auto-generar códigos de almacén (ALM-001)
-- =====================================================

-- Crear trigger para auto-generar códigos
DROP TRIGGER IF EXISTS trg_almacen_codigo;
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

-- Actualizar almacenes existentes que no tengan código o tengan código vacío
UPDATE almacenes
SET codigo = CONCAT('ALM-', LPAD(id, 3, '0'))
WHERE codigo IS NULL OR codigo = '';

-- Verificar
SELECT id, codigo, nombre FROM almacenes;
