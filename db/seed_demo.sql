-- ============================================================================
-- BuzzDM - Datos de demostracion (OPCIONAL)
-- ============================================================================
-- Este archivo NO es necesario para instalar la aplicacion. db/schema.sql ya
-- deja el sistema arrancable y vacio. Carga este script solo si quieres una
-- empresa de ejemplo para trastear sin teclear datos a mano.
--
--   mysql -u USUARIO -p BASE < db/seed_demo.sql
--
-- Todos los datos son inventados. Los correos usan example.com, un dominio
-- reservado por la RFC 2606 que nunca existio, de modo que no se puede escribir
-- por accidente a una direccion real. El RIF cumple el formato venezolano
-- (J-00000000-0) pero no corresponde a ninguna empresa registrada.
--
-- Que crea:
--   - 1 empresa demo (is_demo = 1) que caduca en 15 dias.
--   - 1 almacen, 3 categorias, 5 colores, 5 materiales.
--   - 1 usuario admin y 1 operador.
--   - 3 clientes y 3 proveedores de ejemplo.
--   - 4 productos.
--
-- credenciales: admin@demo.example.com / Demo1234!
-- ============================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------------
-- Empresa. Se crea con el admin todavia pendiente de verificar: el mismo estado
-- que deja el registro real, para que puedas recorrer el flujo de verificacion
-- por correo sin saltartelo.
-- ---------------------------------------------------------------------------
INSERT INTO `empresa` (`tipo_rif`, `rif`, `nombre`, `direccion`, `telefono`, `email`,
                       `admin_email`, `is_demo`, `status`, `trial_ends_at`)
VALUES ('J', 'J-00000001-0', 'Comercial Demo',
        'Avenida Principal 123, Ciudad Ejemplo', '+58 000 000 0000',
        'contacto@demo.example.com', 'admin@demo.example.com',
        1, 'pending_verification', DATE_ADD(NOW(), INTERVAL 15 DAY));

SET @demo_id := LAST_INSERT_ID();

-- El trial demo se agota solo a los 15 dias; el cron lo marcara como expirado.
-- Para usarse sin limite, pon la fecha muy adelante:
--   UPDATE empresa SET trial_ends_at = '2099-12-31 23:59:59' WHERE id = @demo_id;

-- ---------------------------------------------------------------------------
-- Usuario. La contrasena es un hash bcrypt de "Demo1234!".
-- Cámbiala antes de usar esto en cualquier sitio publico.
-- ---------------------------------------------------------------------------
INSERT INTO `usuarios` (`empresa_id`, `nombre`, `email`, `password`, `rol`, `estado`)
VALUES (@demo_id, 'Administrador Demo', 'admin@demo.example.com',
        '$2y$10$TPp4CMpiX9v4DR/ANMOa..mZuYg3HNCVvwJyKzq4B76rY67.OQewC',
        'admin', 'activo'),
       (@demo_id, 'Operador Demo', 'operador@demo.example.com',
        '$2y$10$TPp4CMpiX9v4DR/ANMOa..mZuYg3HNCVvwJyKzq4B76rY67.OQewC',
        'operador', 'activo');

-- ---------------------------------------------------------------------------
-- Catalogo minimo
-- ---------------------------------------------------------------------------
INSERT INTO `almacenes` (`empresa_id`, `codigo`, `nombre`, `direccion`, `tipo`, `es_principal`)
VALUES (@demo_id, 'ALM-001', 'Almacen Principal', 'Avenida Principal 123', 'almacen', 1);

INSERT INTO `categorias` (`empresa_id`, `nombre`) VALUES
  (@demo_id, 'General'),
  (@demo_id, 'Papeleria'),
  (@demo_id, 'Limpieza');

INSERT INTO `colores` (`empresa_id`, `nombre`, `codigo_hex`) VALUES
  (@demo_id, 'Negro',   '#000000'),
  (@demo_id, 'Blanco',  '#FFFFFF'),
  (@demo_id, 'Azul',    '#1E5AA8'),
  (@demo_id, 'Rojo',    '#C62828'),
  (@demo_id, 'Verde',   '#2E7D32');

INSERT INTO `materiales` (`empresa_id`, `nombre`) VALUES
  (@demo_id, 'Papel'), (@demo_id, 'Plastico'), (@demo_id, 'Metal'),
  (@demo_id, 'Tela'), (@demo_id, 'Vidrio');

-- ---------------------------------------------------------------------------
-- Terceros
-- ---------------------------------------------------------------------------
INSERT INTO `clientes` (`id`, `empresa_id`, `nombre`, `email`, `telefono`, `vip`) VALUES
  ('CLI-001', @demo_id, 'Cliente Demo Uno', 'cliente1@ejemplo.example.com', '+58 000 000 0001', 0),
  ('CLI-002', @demo_id, 'Cliente Demo Dos', 'cliente2@ejemplo.example.com', '+58 000 000 0002', 1),
  ('CLI-003', @demo_id, 'Consumidor Final',  'cliente3@ejemplo.example.com', '+58 000 000 0003', 0);

INSERT INTO `proveedores` (`empresa_id`, `nombre`, `rif`, `email`, `contacto_nombre`) VALUES
  (@demo_id, 'Proveedor Demo Uno', 'J-00000005', 'ventas@proveedor1.example.com', 'Contacto Uno'),
  (@demo_id, 'Proveedor Demo Dos', 'J-00000006', 'ventas@proveedor2.example.com', 'Contacto Dos'),
  (@demo_id, 'Distribuidora Demo', 'J-00000007', 'compras@distribuidora.example.com', 'Contacto Tres');

-- ---------------------------------------------------------------------------
-- Productos
-- ---------------------------------------------------------------------------
INSERT INTO `productos` (`empresa_id`, `sku`, `nombre`, `descripcion`, `precio`, `stock`, `min_stock`) VALUES
  (@demo_id, 'SKU-001', 'Cuaderno A4',     'Cuaderno de 100 hojas tamano A4',  4.50, 120,  10),
  (@demo_id, 'SKU-002', 'Boligrafo Azul', 'Boligrafo de punta fina',          1.25, 300,  25),
  (@demo_id, 'SKU-003', 'Resma A4',       'Resma de 500 hojas',              12.00,  40,  10),
  (@demo_id, 'SKU-004', 'Folders x10',    'Carpeta folder',                   6.75,  85,  15);

-- ---------------------------------------------------------------------------
-- Como quitar la demo cuando termines de probarlo
-- ---------------------------------------------------------------------------
-- DELETE FROM empresa WHERE id = @demo_id;   -- las ON DELETE CASCADE borran
--                                            -- usuarios, productos y catalogos.
--
-- Fin de los datos de demostracion.
