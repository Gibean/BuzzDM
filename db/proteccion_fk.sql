-- ============================================================================
-- BuzzDM - Protección de Integridad Referencial
-- Version: 1.0
-- Date: 2026-01-07
-- Description: Cambia ON DELETE SET NULL a RESTRICT para proteger datos históricos
-- ============================================================================

-- Desactivar verificación de FK temporalmente
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. PROTEGER CLIENTES EN PEDIDOS
-- Evita borrar un cliente que tiene pedidos
-- ============================================================================

-- Eliminar FK existente
ALTER TABLE `pedidos` DROP FOREIGN KEY IF EXISTS `pedidos_ibfk_1`;

-- Crear nueva FK con RESTRICT
ALTER TABLE `pedidos`
    ADD CONSTRAINT `fk_pedido_cliente`
    FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================================================
-- 2. PROTEGER PRODUCTOS EN DETALLE_PEDIDO
-- Evita borrar un producto que está en algún pedido
-- ============================================================================

-- Verificar y eliminar FK existente (puede tener diferentes nombres)
ALTER TABLE `detalle_pedido` DROP FOREIGN KEY IF EXISTS `detalle_pedido_ibfk_2`;
ALTER TABLE `detalle_pedido` DROP FOREIGN KEY IF EXISTS `fk_detalle_producto`;

-- Crear nueva FK con RESTRICT
ALTER TABLE `detalle_pedido`
    ADD CONSTRAINT `fk_detalle_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================================================
-- 3. PROTEGER PRODUCTOS EN MOVIMIENTOS_INVENTARIO
-- Evita borrar un producto que tiene historial de movimientos
-- ============================================================================

-- Verificar y eliminar FK existente
ALTER TABLE `movimientos_inventario` DROP FOREIGN KEY IF EXISTS `movimientos_inventario_ibfk_1`;
ALTER TABLE `movimientos_inventario` DROP FOREIGN KEY IF EXISTS `fk_mov_producto`;

-- Crear nueva FK con RESTRICT
ALTER TABLE `movimientos_inventario`
    ADD CONSTRAINT `fk_mov_producto`
    FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================================================
-- 4. PROTEGER PROVEEDORES EN PRODUCTOS
-- Evita borrar un proveedor que tiene productos asociados
-- ============================================================================

-- Verificar y eliminar FK existente
ALTER TABLE `productos` DROP FOREIGN KEY IF EXISTS `productos_ibfk_3`;
ALTER TABLE `productos` DROP FOREIGN KEY IF EXISTS `fk_producto_proveedor`;

-- Crear nueva FK con RESTRICT (o SET NULL si prefieres mantener productos sin proveedor)
ALTER TABLE `productos`
    ADD CONSTRAINT `fk_producto_proveedor`
    FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- ============================================================================
-- 5. PROTEGER CATEGORÍAS EN PRODUCTOS
-- Evita borrar una categoría que tiene productos
-- ============================================================================

ALTER TABLE `productos` DROP FOREIGN KEY IF EXISTS `productos_ibfk_1`;
ALTER TABLE `productos` DROP FOREIGN KEY IF EXISTS `fk_producto_categoria`;

ALTER TABLE `productos`
    ADD CONSTRAINT `fk_producto_categoria`
    FOREIGN KEY (`categoria`) REFERENCES `categorias`(`id`)
    ON DELETE RESTRICT ON UPDATE CASCADE;

-- Reactivar verificación de FK
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICACIÓN
-- Ejecuta estas consultas para confirmar que las FK se crearon correctamente
-- ============================================================================
-- SELECT TABLE_NAME, CONSTRAINT_NAME, DELETE_RULE
-- FROM information_schema.REFERENTIAL_CONSTRAINTS
-- WHERE CONSTRAINT_SCHEMA = 'dbuzz'
-- AND DELETE_RULE = 'RESTRICT';

SELECT 'Protección FK aplicada correctamente' AS resultado;
