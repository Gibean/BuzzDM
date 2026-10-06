-- =====================================================
-- MIGRACIÓN 008: quitar el DEFAULT 1 de empresa_id (segundo bloque)
-- =====================================================
--
-- Cierra lo que la 006 dejó abierto a propósito. Aquella migración endureció
-- nueve tablas y dejó ocho con DEFAULT 1, documentando el motivo: sus
-- repositorios todavía omitían empresa_id, así que quitar el default habría
-- roto esas funciones en producción.
--
-- Ese motivo ya no se cumple. Los ocho repositorios se corrigieron y ahora
-- fail-closed con TenantContext::require():
--
--   almacenes               WarehouseRepository::create()      -> :56
--   ubicaciones             WarehouseRepository::createLocation() -> :159
--                           LocationRepository::create()      -> :59
--   transferencias_almacen  TransferenciaRepository::create()  -> :162
--   stock_ubicacion         TransferenciaService               -> :320
--   requisiciones           RequisicionRepository::create()    -> :198
--   notas_kanban            NoteRepository                     -> :120
--   cargos_existencias      CargoRepository::createHeader()    -> :161
--   precios_proveedor       CargoRepository::registrarPrecios()-> :288
--
-- Verificado que no queda ningún INSERT vivo a estas ocho tablas sin la
-- columna, con included las sentencias partidas en varias líneas y el alta de
-- almacén de CompanyService::provision() -> :74, que liga :empresa_id al
-- id de la empresa recién creada.
--
-- Efecto: un INSERT futuro que olvide empresa_id pasa de escribir en
-- silencio en la empresa 1 a fallar con el error 1364. Para estas ocho tablas
-- ya no es posible esa fuga silenciosa.
--
-- Idempotente: DROP DEFAULT puede repetirse sin efecto adicional. MariaDB hace
-- auto-commit del DDL, así que no hay ROLLBACK; el respaldo previo es la única
-- vuelta atrás. Ejecutar php php/auto_backup.php antes de aplicarla.

ALTER TABLE `almacenes` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `ubicaciones` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `transferencias_almacen` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `stock_ubicacion` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `requisiciones` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `notas_kanban` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `cargos_existencias` ALTER COLUMN `empresa_id` DROP DEFAULT;
ALTER TABLE `precios_proveedor` ALTER COLUMN `empresa_id` DROP DEFAULT;

-- Verificación: no debe quedar ninguna tabla con empresa_id y DEFAULT 1.
SELECT TABLE_NAME, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME = 'empresa_id'
  AND COLUMN_DEFAULT IS NOT NULL;