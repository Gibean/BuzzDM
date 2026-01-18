# Documentación de API con Swagger/OpenAPI (BuzzDM)

## 1. Resumen del Proyecto
Se ha implementado una solución robusta para la documentación automática de la API REST del sistema BuzzDM. Utilizando **PHP 8.1 Attributes** y la librería **Swagger-PHP**, el sistema ahora genera un archivo `openapi.yaml` que describe todos los endpoints, parámetros y esquemas de respuesta.

## 2. Tecnologías Implementadas
- **zircote/swagger-php (v4.x/v5.x)**: Motor de generación de documentación.
- **PHP 8.1 Attributes**: Sintaxis nativa (`#[OA\Get]`) para definir metadatos directamente sobre los controladores, reemplazando los antiguos comentarios "DocBlocks".
- **Composer Autoloading**: Carga automática de clases y escaneo eficiente.
- **Swagger UI**: Interfaz visual interactiva para explorar y probar la API.

## 3. Arquitectura de la Solución

### El Archivo de Configuración (`php/api_docs_config.php`)
Define la información global de la API, servidores y esquemas de seguridad.
```php
#[OA\Info(title: "BuzzDM API", version: "1.0.0")]
#[OA\Server(url: "http://localhost/BuzzDM")]
#[OA\SecurityScheme(securityScheme: "cookieAuth", ...)]
class OpenApiConfig {}
```

### Los Controladores
Cada controlador (`php/src/Controller/*`) ha sido refactorizado para incluir atributos.
Implicó:
1. Eliminar `require_once` manuales (ahora usa `vendor/autoload.php`).
2. Añadir atributos `#[OA\Tag]`, `#[OA\Get]`, `#[OA\Post]`, etc.

**Controladores Documentados:**
*   **Core**: `Cliente`, `Supplier`, `User`, `Order`, `Warehouse`, `Location`, `Shipment`.
*   **Inventario**: `Kit`, `Catalog`, `Cargo`, `Note`.
*   **Sistema**: `Auth`, `Log`, `Import`, `Backup`, `Settings`.

### Generación (`generate_docs.php`)
Un script en la raíz que:
1. Inicializa el escaneado de carpetas clave (`php/src/Controller`, `php/src/Repository`).
2. Lee `php/api_docs_config.php`.
3. Compila y valida las anotaciones.
4. Exporta el resultado a `openapi.yaml`.

## 4. Flujo de Trabajo (Cómo usarlo)

### Para Desarrolladores (Cómo actualizar la doc)
1. Modificar el controlador deseado (ej: `ProductController.php`).
2. Añadir o editar los atributos PHP (`#[OA\...]`).
3. Ejecutar el script generador en la terminal:
   ```bash
   php generate_docs.php
   ```
4. Verificar que `openapi.yaml` se ha actualizado.

### Para Usuarios/Testers (Cómo ver la doc)
1. Acceder a **[http://localhost/BuzzDM/api_docs/](http://localhost/BuzzDM/api_docs/)**.
2. Usar el botón "Try it out" para probar endpoints en vivo.
3. El botón "Authorize" utiliza la sesión actual del navegador (Cookies).

## 5. Mantenimiento y Extensión
- **Nuevos Controladores**: Deben seguir la estructura de atributos y residir en `php/src/Controller`.
- **Nuevos Modelos**: Pueden documentarse con `#[OA\Schema]` si se desea tipado estricto en las respuestas.
