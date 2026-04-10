-- ============================================================================
-- SCRIPT DE DATOS MOCK ADICIONALES - BUZZ Data Manager
-- Incorpora Almacenes adicionales, Transferencias entre almacenes y Notas Kanban
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Añadir 3 almacenes adicionales (El ID 1 ya es ALM-PRINCIPAL)
-- Solo se insertan si no existen, para no romper nada
INSERT IGNORE INTO `almacenes` (`id`, `codigo`, `nombre`, `direccion`, `tipo`, `es_principal`, `activo`, `creado_at`) VALUES
(2, 'ALM-SECUNDARIO', 'Almacén Secundario', 'Calle 10, Edif. Comercial, PB', 'bodega', 0, 1, NOW()),
(3, 'TDA-CENTRO', 'Tienda del Centro', 'Av. Bolívar, Centro Comercial, Local 42', 'tienda', 0, 1, NOW()),
(4, 'BOD-EXTERNA', 'Bodega Externa Zona In', 'Zona Industrial Sur, Galpón 5', 'deposito', 0, 1, NOW());

-- 2. Añadir Transferencias de Almacén
TRUNCATE TABLE `detalle_transferencia`;
TRUNCATE TABLE `transferencias_almacen`;

INSERT INTO `transferencias_almacen` (`id`, `codigo`, `almacen_origen_id`, `almacen_destino_id`, `fecha_solicitud`, `fecha_envio`, `fecha_recepcion`, `estado`, `motivo`, `solicitado_por`, `creado_at`) VALUES
(1, 'TRF-20260401-001', 1, 2, '2026-04-01', '2026-04-02', '2026-04-03', 'recibido', 'Reabastecimiento regular semanal', 1, '2026-04-01 10:00:00'),
(2, 'TRF-20260405-002', 1, 3, '2026-04-05', '2026-04-05', NULL, 'en_transito', 'Pedido de reposición para Tienda', 1, '2026-04-05 09:30:00'),
(3, 'TRF-20260408-003', 2, 4, '2026-04-08', NULL, NULL, 'borrador', 'Reubicación de material de baja rotación', 1, '2026-04-08 14:15:00'),
(4, 'TRF-20260409-004', 1, 3, '2026-04-09', '2026-04-10', NULL, 'en_transito', 'Requisición de emergencia por falta de stock', 1, '2026-04-09 16:45:00'),
(5, 'TRF-20260410-005', 3, 1, '2026-04-10', NULL, NULL, 'cancelado', 'Devolución de excedente (Cancelada por error técnico)', 1, '2026-04-10 08:20:00');

-- 3. Añadir Detalle a las Transferencias de Almacén
-- Asumiendo que los productos con ID del 1 al 50 existen gracias al mock de productos
INSERT INTO `detalle_transferencia` (`transferencia_id`, `producto_id`, `cantidad_solicitada`, `cantidad_enviada`, `cantidad_recibida`) VALUES
(1, 4, 50, 50, 50),
(1, 12, 20, 20, 20),
(1, 5, 100, 100, 100),
(2, 10, 100, 100, NULL),
(2, 11, 40, 40, NULL),
(3, 15, 30, NULL, NULL),
(3, 20, 15, NULL, NULL),
(4, 30, 10, 10, NULL),
(5, 42, 5, NULL, NULL);

-- 4. Añadir Notas Kanban
TRUNCATE TABLE `notas_kanban`;

INSERT INTO `notas_kanban` (`id`, `titulo`, `cliente_nombre`, `telefono`, `email`, `contenido`, `estado`, `prioridad`, `orden`, `usuario_id`, `creado_at`) VALUES
(1, 'Llamar a Cliente VIP', 'Maria Fernandez', '+584165428076', 'maria.fernandez79@gmail.com', 'El cliente solicitó un descuento especial por compras al mayor para su oficina de contabilidad. Confirmar condiciones con gerencia.', 'pendiente', 'alta', 1, 1, NOW()),
(2, 'Seguimiento de envío demorado', 'Jose Martinez', '+584148329350', 'jose.martinez17@gmail.com', 'El envío TRF-20260405-002 parece estar atascado en tránsito hacia la Tienda del Centro. Llamar al transportista para verificar.', 'seguimiento', 'media', 1, 1, NOW()),
(3, 'Cotización Papelería Colegio', 'Liceo San Jose (Director)', '+582121234567', 'colegio.sj@edu.ve.com', 'Preparar propuesta integral de papelería para el nuevo año escolar con 10% de descuento en marcadores y material didáctico.', 'pendiente', 'media', 2, 1, NOW()),
(4, 'Revisar Factura #2948', 'Andrea Ramirez', '+584165893349', 'andrea.ramirez14@gmail.com', 'La cliente indicó que se facturó un producto de más. Se validó con almacén, se resolvió y se hizo la nota de crédito correspondiente.', 'resuelto', 'baja', 1, 1, NOW()),
(5, 'Evaluar cambio de proveedor de resmas', 'Distribuidora Norma', '+582418556677', 'ventas@norma.com.ve', 'Los costos de envío han subido un 15%. Evaluar proveedores locales alternativos para la zona centro.', 'seguimiento', 'alta', 2, 1, NOW());

SET FOREIGN_KEY_CHECKS = 1;
