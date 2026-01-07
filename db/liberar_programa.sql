-- ============================================================================
-- SCRIPT DE LIMPIEZA TOTAL - BUZZ Data Manager
-- ============================================================================
-- Este script elimina todos los datos de transacciones, clientes, productos,
-- logs e historial, dejando el programa "vacío" pero funcional para un nuevo uso.
-- Mantiene: Usuarios (excepto ID=100 si existe), Roles, Permisos y Configuraciones base.

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Limpieza de Ventas y Pedidos
TRUNCATE TABLE `detalle_pedido`;
TRUNCATE TABLE `pedidos`;

-- 2. Limpieza de Inventario y Movimientos
TRUNCATE TABLE `movimientos_inventario`;
TRUNCATE TABLE `detalle_cargo_existencias`;
TRUNCATE TABLE `cargos_existencias`;
TRUNCATE TABLE `productos`;
TRUNCATE TABLE `categorias`;
TRUNCATE TABLE `marcas`;
TRUNCATE TABLE `proveedores`;
TRUNCATE TABLE `almacenes`;
-- Insertar almacén por defecto mínimo
INSERT INTO `almacenes` (`id`, `codigo`, `nombre`, `tipo`, `es_principal`) VALUES (1, 'ALM-PRINCIPAL', 'Almacén Principal', 'almacen', 1);

-- 3. Limpieza de Clientes
TRUNCATE TABLE `clientes`;

-- 4. Limpieza de Sistema y Logs
TRUNCATE TABLE `logs`;
TRUNCATE TABLE `logs_login`;
TRUNCATE TABLE `importaciones`;
TRUNCATE TABLE `backups`;
TRUNCATE TABLE `password_reset_tokens`;

-- 5. Limpieza de Módulo de Tareas (To-Do)
TRUNCATE TABLE `notas_checklists`;
TRUNCATE TABLE `notas_adjuntos`;
TRUNCATE TABLE `notas_items_inventario`;
TRUNCATE TABLE `notas_kanban`;

-- 6. Limpieza de Usuarios
-- Borrar usuarios que no sean el administrador principal (ID=1) o el sistema
DELETE FROM `usuarios` WHERE `id` > 1;
ALTER TABLE `usuarios` AUTO_INCREMENT = 2;

-- 7. Reset de Auto-incrementos (Opcional pero recomendado para limpieza total)
ALTER TABLE `pedidos` AUTO_INCREMENT = 1;
ALTER TABLE `detalle_pedido` AUTO_INCREMENT = 1;
ALTER TABLE `productos` AUTO_INCREMENT = 1;
ALTER TABLE `clientes` AUTO_INCREMENT = 1;
ALTER TABLE `logs` AUTO_INCREMENT = 1;
ALTER TABLE `importaciones` AUTO_INCREMENT = 1;
ALTER TABLE `notas_kanban` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- Limpieza completada exitosamente.
