-- Mock data for Librería / Papelería
-- File: db/mock_libreria_inserts.sql
-- Created: 2025-12-25
-- Updated: 2026-01-07 (Added import history and to-do notes)

SET FOREIGN_KEY_CHECKS=0;

-- Cleanup: remove existing mock data for Librería
DELETE FROM `detalle_pedido` WHERE `pedido_id` IN (2,3,4,5,6,7,8,9,10,11);
DELETE FROM `pedidos` WHERE `id` IN (2,3,4,5,6,7,8,9,10,11);
DELETE FROM `movimientos_inventario` WHERE `usuario_id` = 100 OR `producto_id` IN (2,3,4,5,6,7,8,9,10,11,12,13);
DELETE FROM `productos` WHERE `sku` LIKE 'LBR-%' OR `id` IN (2,3,4,5,6,7,8,9,10,11,12,13);
DELETE FROM `categorias` WHERE `id` IN (1,2,3,4,5,6);
DELETE FROM `marcas` WHERE `id` IN (1,2,3,4,5,6);
DELETE FROM `proveedores` WHERE `id` IN (1,2,3);
DELETE FROM `clientes` WHERE `id` IN ('V-10000001','V-10000002','V-10000003');
DELETE FROM `importaciones` WHERE `archivo_nombre` LIKE 'Mock_%';
DELETE FROM `notas_checklists` WHERE `nota_id` IN (10,11,12);
DELETE FROM `notas_adjuntos` WHERE `nota_id` IN (10,11,12);
DELETE FROM `notas_items_inventario` WHERE `nota_id` IN (10,11,12);
DELETE FROM `notas_kanban` WHERE `id` IN (10,11,12);
DELETE FROM `usuarios` WHERE `id` = 100;
DELETE FROM `empresa` WHERE `id` = 1;
DELETE FROM `configuraciones` WHERE `clave` IN ('store_name','currency','tax_percent');

-- Categories
INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
  (1, 'Papelería', 'Artículos de papelería generales: cuadernos, bolígrafos, goma, etc.'),
  (2, 'Útiles escolares', 'Artículos pensados para estudiantes: lápices, reglas, estuches'),
  (3, 'Oficina', 'Papel, resmas, archivadores y suministros de oficina'),
  (4, 'Artículos de arte', 'Material para dibujo y pintura: acuarelas, cartulinas, marcadores'),
  (5, 'Adhesivos y pegamentos', 'Pegamento en barra, líquido, cintas adhesivas'),
  (6, 'Cajas y archivadores', 'Archivadores, carpetas y organizadores');

-- Brands (marcas)
INSERT INTO `marcas` (`id`, `nombre`, `descripcion`, `sitio_web`) VALUES
  (1, 'Pilot', 'Bolígrafos y roller de calidad', 'https://www.pilot.com'),
  (2, 'Stabilo', 'Marcadores y resaltadores', 'https://www.stabilo.com'),
  (3, 'Faber-Castell', 'Lápices y material artístico', 'https://www.faber-castell.com'),
  (4, 'Staedtler', 'Gomas y herramientas de dibujo', 'https://www.staedtler.com'),
  (5, 'Canson', 'Papeles y cartulinas para arte', 'https://www.canson.com'),
  (6, 'Tombow', 'Material para pintura y acuarela', 'https://www.tombow.com');

-- Suppliers (proveedores)
INSERT INTO `proveedores` (`id`, `nombre`, `contacto_nombre`, `email`, `telefono`, `direccion`) VALUES
  (1, 'Distribuidora Papel S.A.', 'María Pérez', 'ventas@distribuidora-papel.com', '+58 212 555 0101', 'Av. Comercio, Caracas'),
  (2, 'Importadora Útiles S.R.L.', 'Carlos Rojas', 'contacto@importutiles.com', '+58 241 555 0202', 'Zona Industrial, Valencia'),
  (3, 'ArtículosCreativos C.A.', 'Ana Gómez', 'info@articuloscreativos.com', '+58 261 555 0303', 'C.C. Arte, Maracay');

-- Products (productos)
INSERT INTO `productos` (`id`, `sku`, `nombre`, `descripcion`, `precio`, `stock`, `min_stock`, `estado`, `creado_at`, `categoria`, `marca_id`, `proveedor_id`) VALUES
  (2, 'LBR-0001', 'Cuaderno universitario 100 hojas', 'Cuaderno raya 100 hojas 21x27 cm', 2.50, 120, 20, 'activo', '2025-12-25 10:00:00', 1, NULL, 1),
  (3, 'LBR-0002', 'Cuaderno de dibujo A4 40 hojas', 'Papel para dibujo 120 g/m2, 40 hojas', 5.00, 60, 10, 'activo', '2025-12-25 10:01:00', 4, 5, 3),
  (4, 'LBR-0003', 'Bolígrafo Pilot G2 negro', 'Bolígrafo punta 0.7 mm, tinta gel', 1.20, 200, 30, 'activo', '2025-12-25 10:02:00', 1, 1, 1),
  (5, 'LBR-0004', 'Set Marcadores Stabilo 6 colores', 'Marcadores punta fina, set x6', 8.50, 40, 10, 'activo', '2025-12-25 10:03:00', 4, 2, 3),
  (6, 'LBR-0005', 'Lápiz HB Faber-Castell (12)', 'Pack de 12 lápices HB', 3.75, 150, 20, 'activo', '2025-12-25 10:04:00', 2, 3, 2),
  (7, 'LBR-0006', 'Goma de borrar Staedtler', 'Goma blanda sin PVC', 0.80, 300, 50, 'activo', '2025-12-25 10:05:00', 1, 4, 2),
  (8, 'LBR-0007', 'Pegamento en barra 36g', 'Pegamento sólido sin ácido', 1.10, 80, 20, 'activo', '2025-12-25 10:06:00', 5, NULL, 1),
  (9, 'LBR-0008', 'Cartulina Canson A4 (pack 50)', 'Cartulina para arte, 180 g/m2, pack 50 hojas', 15.00, 25, 10, 'activo', '2025-12-25 10:07:00', 4, 5, 3),
  (10, 'LBR-0009', 'Archivador A4 plástico', 'Archivador plástico A4 con anillas', 4.25, 10, 20, 'stock_bajo', '2025-12-25 10:08:00', 6, NULL, 1),
  (11, 'LBR-0010', 'Resma papel bond A4 80gr (500)', 'Resma de papel bond A4 80 g/m2, 500 hojas', 6.50, 50, 10, 'activo', '2025-12-25 10:09:00', 3, NULL, 1),
  (12, 'LBR-0011', 'Set acuarelas Tombow 12 colores', 'Set de acuarelas en pastilla, 12 colores', 22.00, 15, 5, 'activo', '2025-12-25 10:10:00', 4, 6, 3),
  (13, 'LBR-0012', 'Marcador permanente negro', 'Marcador indeleble punta media', 2.00, 0, 10, 'agotado', '2025-12-25 10:11:00', 1, NULL, 2);

-- Clients (clientes)
INSERT INTO `clientes` (`id`, `nombre`, `email`, `telefono`, `direccion`, `notas`, `vip`, `creado_at`) VALUES
  ('V-10000001', 'María López', 'm.lopez@example.com', '+58 412 345 6789', 'Calle Comercio 123, Caracas', '', 0, '2025-12-01 09:00:00'),
  ('V-10000002', 'José Pérez', 'j.perez@example.com', '+58 414 987 6543', 'Av. Bolívar 45, Valencia', 'Cliente frecuente', 1, '2025-11-20 11:30:00'),
  ('V-10000003', 'Librería La esquina', 'contabilidad@laesquina.com', '+58 212 999 8888', 'C.C. Centro, Local 12', 'Mayorista', 0, '2025-10-05 14:22:00');

-- Users
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `estado`, `creado_at`) VALUES
  (100, 'Operador Libreria', 'operador.libreria@example.com', '$2y$10$dU7Lj8mU43zV6Rla7Bd0zuZ5hOOGumIErvLPONXvKM9sD7tAXWp3G', 'operador', 'activo', '2025-12-01 08:00:00');

-- Company (empresa)
INSERT INTO `empresa` (`id`, `tipo_rif`, `rif`, `nombre`, `direccion`, `telefono`, `email`) VALUES
  (1, 'J', 'J-12345678-9', 'Librería Papelería El Estudiante', 'Av. Principal 100, Caracas', '+58 212 555 0000', 'info@elestudiante.com');

-- Configurations
INSERT INTO `configuraciones` (`clave`, `valor`, `descripcion`) VALUES
  ('store_name', 'Librería El Estudiante', 'Nombre mostrado en factura y sitio'),
  ('currency', 'VES', 'Moneda del sistema'),
  ('tax_percent', '12.00', 'Porcentaje de IVA aplicado a ventas');

-- Orders (pedidos)
INSERT INTO `pedidos` (`id`, `cliente_id`, `referencia`, `fecha_creacion`, `total`, `estado_pago`, `estado_envio`, `direccion_envio`, `notas_envio`, `creado_por`) VALUES
  (2, 'V-10000001', 'REF-LBR-0002', '2025-12-25 11:00:00', 0.00, 'pagado', 'pendiente', 'Calle Comercio 123, Caracas', 'Entrega mañana', 100),
  (3, 'V-10000002', 'REF-LBR-0003', '2025-12-24 15:30:00', 0.00, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100),
  (4, 'V-10000003', 'REF-LBR-0004', '2025-10-05 10:15:00', 0.00, 'pagado', 'recibido', 'C.C. Centro, Local 12', 'Pedido mayorista', 100),
  (5, 'V-10000001', 'REF-LBR-0005', '2025-10-20 09:00:00', 0.00, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100),
  (6, 'V-10000002', 'REF-LBR-0006', '2025-11-11 14:45:00', 0.00, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100),
  (7, 'V-10000001', 'REF-LBR-0007', '2025-11-25 16:10:00', 0.00, 'pagado', 'enviado', 'Calle Comercio 123, Caracas', '', 100),
  (8, 'V-10000003', 'REF-LBR-0008', '2025-12-01 12:00:00', 0.00, 'pagado', 'pendiente', 'C.C. Centro, Local 12', '', 100),
  (9, 'V-10000002', 'REF-LBR-0009', '2025-12-05 08:30:00', 0.00, 'no_pagado', 'pendiente', 'Av. Bolívar 45, Valencia', '', 100),
  (10, 'V-10000001', 'REF-LBR-0010', '2025-12-10 18:20:00', 0.00, 'pagado', 'pendiente', 'Calle Comercio 123, Caracas', '', 100),
  (11, 'V-10000003', 'REF-LBR-0011', '2025-12-20 09:05:00', 0.00, 'pagado', 'enviado', 'C.C. Centro, Local 12', '', 100);

-- Order lines
INSERT INTO `detalle_pedido` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
  (2, 2, 2, 2.50), (2, 4, 3, 1.20), (2, 8, 1, 1.10),
  (3, 5, 1, 8.50), (3, 7, 2, 0.80),
  (4, 11, 10, 6.50), (4, 2, 20, 2.50),
  (5, 4, 5, 1.20), (5, 6, 2, 3.75),
  (6, 3, 2, 5.00), (6, 7, 1, 0.80),
  (7, 9, 1, 15.00), (7, 8, 2, 1.10),
  (8, 12, 1, 22.00), (8, 5, 1, 8.50),
  (9, 10, 1, 4.25), (9, 2, 3, 2.50),
  (10, 4, 10, 1.20), (10, 13, 2, 2.00),
  (11, 3, 5, 5.00), (11, 11, 2, 6.50);

-- Update pedidos total
UPDATE `pedidos` SET `total` = (
  SELECT IFNULL(SUM(cantidad * precio_unitario), 0) FROM `detalle_pedido` d WHERE d.pedido_id = pedidos.id
) WHERE id IN (2,3,4,5,6,7,8,9,10,11);

-- Inventory movements
INSERT INTO `movimientos_inventario` (`producto_id`, `usuario_id`, `tipo_movimiento`, `cantidad`, `justificacion`, `stock_anterior`, `stock_actual`, `fecha`) VALUES
  (2, 100, 'entrada', 120, 'Ingreso inicial de stock', 0, 120, '2025-12-25 10:00:00'),
  (3, 100, 'entrada', 60, 'Ingreso inicial de stock', 0, 60, '2025-12-25 10:01:00'),
  (4, 100, 'entrada', 200, 'Ingreso inicial de stock', 0, 200, '2025-12-25 10:02:00'),
  (5, 100, 'entrada', 40, 'Ingreso inicial de stock', 0, 40, '2025-12-25 10:03:00'),
  (6, 100, 'entrada', 150, 'Ingreso inicial de stock', 0, 150, '2025-12-25 10:04:00'),
  (7, 100, 'entrada', 300, 'Ingreso inicial de stock', 0, 300, '2025-12-25 10:05:00'),
  (8, 100, 'entrada', 80, 'Ingreso inicial de stock', 0, 80, '2025-12-25 10:06:00'),
  (9, 100, 'entrada', 25, 'Ingreso inicial de stock', 0, 25, '2025-12-25 10:07:00'),
  (10, 100, 'entrada', 10, 'Ingreso inicial de stock', 0, 10, '2025-12-25 10:08:00'),
  (11, 100, 'entrada', 50, 'Ingreso inicial de stock', 0, 50, '2025-12-25 10:09:00'),
  (12, 100, 'entrada', 15, 'Ingreso inicial de stock', 0, 15, '2025-12-25 10:10:00'),
  (13, 100, 'entrada', 0, 'Sin stock al momento del mock', 0, 0, '2025-12-25 10:11:00');

-- Import History
INSERT INTO `importaciones` (`usuario_id`, `tipo`, `archivo_nombre`, `fecha`, `estado`, `detalles`) VALUES
  (1, 'productos', 'Mock_Productos_Enero.xlsx', '2026-01-02 09:00:00', 'procesado', '{"registros":50,"insertados":45,"actualizados":5,"errores":0}'),
  (1, 'clientes', 'Mock_Clientes_Nuevos.xlsx', '2026-01-03 14:30:00', 'procesado', '{"registros":20,"insertados":20,"actualizados":0,"errores":0}'),
  (1, 'general', 'Mock_Inventario_Faltante.csv', '2026-01-05 10:15:00', 'error', '{"error":"Error en la columna SKU fila 12","mensaje":"El SKU LBR-9999 no es válido"}');

-- To-Do Notes
INSERT INTO `notas_kanban` (`id`, `titulo`, `cliente_nombre`, `contenido`, `estado`, `prioridad`, `orden`, `usuario_id`, `creado_at`) VALUES
  (10, 'Presupuesto para Colegio San Ignacio', 'Colegio San Ignacio', 'Solicitan presupuesto de 50 resmas y 100 cuadernos para el inicio de clases.', 'pendiente', 'alta', 1, 1, '2026-01-07 08:30:00'),
  (11, 'Seguimiento de pedido V-10000002', 'José Pérez', 'El cliente llamó preguntando por su pedido de marcadores. Revisar con el transportista.', 'seguimiento', 'media', 1, 1, '2026-01-07 09:15:00'),
  (12, 'Reabastecer stock de Pilot G2', NULL, 'Estamos quedando bajos en stock del Bolígrafo Pilot G2 negro. Contactar a Distribuidora Papel.', 'resuelto', 'baja', 1, 1, '2026-01-07 10:00:00');

-- Checklists
INSERT INTO `notas_checklists` (`nota_id`, `tarea`, `completada`, `orden`) VALUES
  (10, 'Contar stock de resmas disponible', 1, 1),
  (10, 'Pedir cotización especial a Distribuidora Papel', 0, 2),
  (10, 'Enviar PDF al cliente', 0, 3),
  (11, 'Llamar a MRW', 1, 1),
  (11, 'Confirmar recepción con el cliente', 0, 2);

-- Attached Products
INSERT INTO `notas_items_inventario` (`nota_id`, `producto_id`, `cantidad`) VALUES
  (10, 11, 50), (10, 2, 100),
  (12, 4, 100);

-- Attachments
INSERT INTO `notas_adjuntos` (`nota_id`, `nombre`, `url`) VALUES
  (10, 'Requerimiento_Colegio.pdf', 'https://example.com/files/req_colegio_2026.pdf'),
  (11, 'Guia_Despacho_123.jpg', 'https://example.com/img/guia_123.jpg');

SET FOREIGN_KEY_CHECKS=1;
