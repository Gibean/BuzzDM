-- =====================================================
-- MIGRACIÓN: Agregar permisos para módulo Transferencias
-- =====================================================

-- Agregar permisos para admin
INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES
    ('admin', 'transferencias.php'),
    ('admin', 'api_transferencias.php');

-- Agregar permisos para supervisor
INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES
    ('supervisor', 'transferencias.php'),
    ('supervisor', 'api_transferencias.php');

-- Verificar
SELECT rol, pagina FROM permisos_rol WHERE pagina LIKE '%transferencia%' ORDER BY rol;
