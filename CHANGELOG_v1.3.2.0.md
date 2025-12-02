# Changelog - Versión 1.3.2.0
**Fecha:** 30 de Noviembre de 2025

Esta versión se centra en la implementación de funcionalidades críticas faltantes, mejoras de seguridad y corrección de errores en la interfaz administrativa.

## 🚀 Nuevas Funcionalidades

### 1. Sistema de "Soft Deletes" (Borrado Lógico)
Se implementó un sistema de borrado lógico para prevenir la pérdida accidental de datos. Los registros ya no se eliminan permanentemente de la base de datos, sino que se marcan como eliminados.

*   **Base de Datos:**
    *   Se agregaron columnas `deleted_at` (TIMESTAMP) a las tablas `productos`, `pedidos` y `clientes`.
    *   Se crearon índices para optimizar las consultas.
*   **Backend:**
    *   Actualización de `product_actions.php`, `pedidos_actions.php` y `clientes_actions.php`.
    *   Las operaciones `DELETE` ahora actualizan el campo `deleted_at` con la fecha actual.
    *   Las consultas `GET` filtran automáticamente los registros eliminados (donde `deleted_at` es NULL).
    *   Se agregó una nueva acción `restore` para recuperar registros eliminados.
*   **Frontend:**
    *   **Filtro "Ver eliminados":** Nuevo checkbox en las vistas de Inventario, Pedidos y Clientes para visualizar registros eliminados.
    *   **Indicadores Visuales:** Los registros eliminados se muestran con un estilo distintivo (fondo rojo claro/borde rojo).
    *   **Restauración:** El botón de eliminar se reemplaza por un botón de "Restaurar" para los items eliminados.

### 2. Paginación de Logs de Sesión
Mejora en el rendimiento y usabilidad del visor de logs en el panel de administración.

*   **Backend (`php/logs.php`):**
    *   Soporte para parámetros `page` y `limit`.
    *   Optimización de consultas con `LIMIT` y `OFFSET`.
    *   Respuesta JSON estructurada con metadatos de paginación (`total_records`, `total_pages`, etc.).
*   **Frontend (`admin.php` / `admin.js`):**
    *   Controles de navegación (Anterior, Siguiente, Página actual) debajo de la tabla de logs.
    *   Carga dinámica de logs sin recargar la página.

### 3. Confirmaciones para Acciones Destructivas
Se añadió una capa de seguridad en la interfaz de usuario para prevenir acciones accidentales.

*   **Componente Global (`confirmation_modal.js`):**
    *   Nuevo módulo reutilizable para mostrar modales de confirmación estandarizados.
*   **Implementación:**
    *   Integrado en las acciones de eliminar productos, pedidos y clientes.
    *   Requiere confirmación explícita del usuario antes de proceder.

### 4. Seguridad en Reportes
Protección adicional para la generación de reportes sensibles.

*   **Backend (`php/reports.php`):**
    *   Verificación obligatoria de la contraseña del administrador antes de generar cualquier reporte.
*   **Frontend:**
    *   El formulario de generación de reportes ahora solicita y envía la contraseña del administrador para su validación.

## 🐛 Corrección de Errores y Mejoras Técnicas

### Correcciones en Panel de Administración
*   **Error 404 en Dashboard/Configuración:** Se solucionó un problema de redirección en `php/sesion.php` que causaba errores 404 al intentar acceder a la configuración de la empresa o logs. Se actualizaron las listas de scripts API permitidos y páginas autorizadas.
*   **Errores JSON:** Se corrigieron los encabezados `Content-Type: application/json` faltantes en `php/users.php` y `php/logs.php`, eliminando las advertencias de "Response was not JSON".
*   **Syntax Error en Logs:** Se corrigió un error de sintaxis (variable `$i` mal referenciada) en el bucle de parámetros de `php/logs.php`.

### Otras Mejoras
*   **Limpieza de Código:** Se eliminó código duplicado y redundante en `php/logs.php`.
*   **Manejo de Errores:** Se desactivó `display_errors` en scripts de producción API para evitar que advertencias de PHP rompan el formato JSON.

## 📝 Instrucciones de Actualización

1.  **Base de Datos:** Ejecutar el script de migración `db/migrations/add_soft_deletes.sql` para agregar las columnas necesarias.
2.  **Archivos:** Reemplazar los archivos modificados en el servidor (`php/`, `assets/js/`, `*.php`).
3.  **Caché:** Limpiar la caché del navegador para asegurar la carga de los nuevos scripts JS.
