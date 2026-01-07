-- Migración: Renombrar notas.php a todo.php en permisos
-- Ejecutar este archivo para actualizar los permisos en la base de datos

-- Actualizar permisos existentes de notas.php a todo.php
UPDATE `permisos_rol` SET `pagina` = 'todo.php' WHERE `pagina` = 'notas.php';

-- Verificar cambios
SELECT * FROM `permisos_rol` WHERE `pagina` = 'todo.php';
