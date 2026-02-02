-- Check existing permissions
SELECT * FROM permisos_rol WHERE pagina IN ('api_products.php', 'api_clientes.php', 'product_actions.php', 'clientes_actions.php');

-- Insert new permissions if they don't exist
INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES
('admin', 'api_products.php'),
('admin', 'api_clientes.php'),
('supervisor', 'api_products.php'),
('supervisor', 'api_clientes.php');

-- Verify insertion
SELECT * FROM permisos_rol WHERE pagina IN ('api_products.php', 'api_clientes.php');
