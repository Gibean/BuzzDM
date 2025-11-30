-- Script para crear usuario admin con correo juanluischaurant@gmail.com
-- Contraseña por defecto: admin123 (debe cambiarse después del primer login)
-- eliminar despues, este script es solo para crear el usuario admin durante pruebas

-- Verificar si el usuario ya existe y eliminarlo si es necesario
DELETE FROM usuarios WHERE email = 'juanluischaurant@gmail.com';

-- Insertar el nuevo usuario admin
-- Password hash para: admin123
INSERT INTO usuarios (nombre, email, password, rol, estado, creado_at, intentos_fallidos)
VALUES (
    'Juan Luis Chaurant',
    'juanluischaurant@gmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin',
    'activo',
    NOW(),
    0
);

-- Mostrar el resultado
SELECT id, nombre, email, rol, estado, creado_at
FROM usuarios
WHERE email = 'juanluischaurant@gmail.com';

