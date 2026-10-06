-- =====================================================
-- MIGRACIÓN 006: quitar el DEFAULT 1 de empresa_id
-- =====================================================
--
-- Esta es la parte irreversible de §1.0 del plan. La 004 puso `empresa_id` en
-- NOT NULL en ocho tablas, pero dejó el DEFAULT 1 en las diecisiete restantes.
-- Ese default es la causa de la fuga: no produce ningún error, solo escribe la
-- fila en la empresa 1.
--
-- Demostración (con rollback, sin dejar rastro):
--     SET @empresa = 14;
--     INSERT INTO pedidos (referencia) VALUES ('PRUEBA-LEAK');
--     SELECT empresa_id FROM pedidos ORDER BY id DESC LIMIT 1;  --> 1
-- El INSERT se ejecutó bajo el tenant 14 y la fila quedó en la empresa 1.
--
-- Por qué el error es mejor que el default:
-- Con DEFAULT 1, un INSERT que olvide la columna es un bug invisible. Sin
-- default, es un INSERT que MySQL rechaza con el error 1364
-- ("Field 'empresa_id' doesn't have a default value"). Es visible, aparece en
-- los logs y no se puede pasar por alto al hacer el commit. Ese es el objetivo:
-- que un olvido futuro falle en desarrollo y no en producción.
--
-- Alcance: solo se endurecen las tablas cuyos caminos de escritura en PHP
-- pasan empresa_id de forma explícita, verificado con tests:
--
--   pedidos              OrderRepository::createHeader() -> insertTenant()
--                        php/generate_mocks.php (artefacto SQL, ya lo emite)
--   usuarios             UserRepository::create() -> insertTenant()
--                        CompanyService (alta de demo, parámetro ligado)
--   productos            ProductRepository, ImportService::insertRow()
--   clientes             ClienteRepository, ImportService::insertRow()
--   proveedores          SupplierRepository, ImportService::insertRow()
--   categorias           CompanyService, ImportService::insertRow()
--   marcas               ImportService::insertRow()
--   importaciones        ImportRepository::logStart() -> insertTenant()
--   movimientos_inventario  ImportService, triggers de la migración 005
--
-- Quedan fuera ocho tablas con DEFAULT 1 porque sus repositorios todavía no
-- están migrados a BaseRepository y sus INSERTs siguen omitiendo la columna:
--
--   almacenes, cargos_existencias, notas_kanban, precios_proveedor,
--   requisiciones, stock_ubicacion, transferencias_almacen, ubicaciones
--
-- Es preferible dejarlas con el default (y por tanto con la fuga conocida y
-- acotada) que endurecerlas antes de arreglar su código y romper funciones en
-- producción. db/tools/_verify_multitenant.php sigue listándolas como fallo,
-- así que no quedan invisibles.
--
-- Idempotente: ALTER TABLE ... ALTER COLUMN ... DROP DEFAULT puede ejecutarse
-- más de una vez sin efecto adicional. MariaDB hace auto-commit del DDL, por lo
-- que esta migración no se puede deshacer con un ROLLBACK; el respaldo previo
-- es la única vuelta atrás. Ejecutar php php/auto_backup.php antes de aplicarla.

-- ---- Núcleo transaccional ----
ALTER TABLE `pedidos` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `usuarios` ALTER COLUMN `empresa_id` DROP DEFAULT;

-- ---- Catálogo: escrituras cubiertas por ProductRepository, ClienteRepository,
--      SupplierRepository e ImportService/ImportRepository ----
ALTER TABLE `productos` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `clientes` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `proveedores` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `categorias` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `marcas` ALTER COLUMN `empresa_id` DROP DEFAULT;

-- ---- Trazabilidad e inventario ----
-- El trigger trg_log_importacion (migración 005) lee NEW.empresa_id de esta tabla:
-- mientras la columna tuviera DEFAULT 1, una importación sin empresa_id explícita
-- creaba una fila de importación en la empresa 1 y su trigger escribía el log con
-- esa empresa_id equivocada, de forma coherente pero falsa.
ALTER TABLE `importaciones` ALTER COLUMN `empresa_id` DROP DEFAULT;

-- Los triggers trg_cargo_anulado y trg_cargo_confirmado escriben aquí con
-- NEW.empresa_id. Con el DEFAULT 1, un movimiento generado por un trigger que no
-- lo propagara se guardaba en la empresa 1 sin error.
ALTER TABLE `movimientos_inventario` ALTER COLUMN `empresa_id` DROP DEFAULT;

-- Verificación: no debe quedar ninguna de estas nueve con DEFAULT 1.
SELECT TABLE_NAME, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME = 'empresa_id'
  AND TABLE_NAME IN (
    'pedidos', 'usuarios', 'productos', 'clientes', 'proveedores',
    'categorias', 'marcas', 'importaciones', 'movimientos_inventario'
  )
  AND COLUMN_DEFAULT IS NOT NULL;
