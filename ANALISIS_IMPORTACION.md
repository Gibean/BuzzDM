# Análisis del Sistema de Importación Masiva

Este documento detalla el estado actual del modulo de importación de BuzzDM, identifica brechas críticas y define los requerimientos para soportar la carga masiva de datos completa (Productos, Clientes, Proveedores, Catálogos).

## 1. Estado Actual del Sistema

El sistema utiliza `PhpSpreadsheet` para procesar archivos Excel (.xlsx, .xls) y CSV. La lógica central reside en `php/process_import.php`.

### Funcionalidad Existente
*   **Formatos**: Excel (.xlsx, .xls), CSV.
*   **Entidades Soportadas (Parcialmente)**: Productos, Clientes, Pedidos.
*   **Lógica de Relaciones**: Utiliza una función `findOrCreate` para buscar claves foráneas (Categoría, Marca, Proveedor) por nombre. Si no existen, los crea automáticamente.

---

## 2. Análisis de Brechas (Gap Analysis)

### A. Productos (Critico)
El importador actual solo cubre campos básicos. Para una migración real, faltan múltiples atributos físicos y de costos definidos en la base de datos.

| Campo DB | Estado Importación | Acción Requerida |
| :--- | :--- | :--- |
| `sku` | ✅ Soportado | Mantener como clave única. |
| `nombre` | ✅ Soportado | - |
| `descripcion` | ✅ Soportado | - |
| `precio` | ✅ Soportado | - |
| `stock` | ✅ Soportado | Genera movimiento inv. inicial. |
| `costo_promedio` | ❌ Faltante | Agregar columna "Costo". |
| `modelo` | ❌ Faltante (Campo Texto) | Agregar columna "Modelo". |
| `marca_id` | ⚠️ Parcial (FK Lookup) | Verificar lógica `findOrCreate` para marcas. |
| `proveedor_id` | ⚠️ Parcial (FK Lookup) | Verificar lógica `findOrCreate`. |
| `barcode` | ❌ Faltante | Necesario para escáneres. |
| `peso_kg` | ❌ Faltante | Necesario para envíos. |
| `medidas` | ❌ Faltante | Texto libre (Ej: "20x30cm"). |
| `color` | ❌ Faltante | Texto libre. |
| `material` | ❌ Faltante | Texto libre. |
| `imagen_url` | ❌ Faltante | Permitir URL externa. |

### B. Clientes (Roto)
⚠️ **Fallo Crítico Detectado**: La tabla `clientes` usa un ID alfanumérico (`VARCHAR(50)`, ej: `V-123456`) que **NO es auto-incremental**.
*   **Estado Actual**: El script intenta insertar `nombre`, `email`, etc., pero **no provee el ID**.
*   **Consecuencia**: La importación fallará con error SQL `Field 'id' doesn't have a default value`.
*   **Solución**: El template debe exigir una columna "ID_Fiscal" (Cédula/RIF) o el sistema debe generar uno automáticamente si está vacío.

### C. Proveedores (Inexistente)
No existe lógica de importación para proveedores.
*   **Campos Requeridos**: RIF, Nombre (Único), Contacto, Email, Teléfono, Dirección.

### D. Catálogos (Categorías, Marcas)
Actualmente se crean "al vuelo" al importar productos. No hay forma de importar descripciones o metadatos masivos para estas entidades.

---

## 3. Definición de Plantillas de Importación

Para permitir la carga masiva, se deben generar 4 plantillas estandarizadas.

### Plantilla 1: `plantilla_productos_master.xlsx`
| Cabecera Excel | Campo DB | Tipo | Obligatorio | Nota |
| :--- | :--- | :--- | :--- | :--- |
| **SKU** | `sku` | Texto | SI | Clave única. |
| **Nombre** | `nombre` | Texto | SI | |
| **Descripcion** | `descripcion` | Texto | NO | |
| **Barcode** | `barcode` | Texto | NO | |
| **Categoria** | `categoria` (FK) | Texto | SI | Crea si no existe. |
| **Marca** | `marca_id` (FK) | Texto | NO | Crea si no existe. |
| **Modelo** | `modelo` | Texto | NO | |
| **Color** | `color` | Texto | NO | |
| **Precio_Venta** | `precio` | Decimal | SI | |
| **Costo_Unitario**| `costo_promedio` | Decimal | NO | Default 0. |
| **Stock_Inicial** | `stock` | Entero | NO | Default 0. |
| **Stock_Minimo** | `min_stock` | Entero | NO | Default 10. |
| **Proveedor** | `proveedor_id` (FK)| Texto | NO | Nombre del proveedor. |
| **Peso_KG** | `peso_kg` | Decimal | NO | |

### Plantilla 2: `plantilla_clientes.xlsx`
| Cabecera Excel | Campo DB | Tipo | Obligatorio | Nota |
| :--- | :--- | :--- | :--- | :--- |
| **ID_Fiscal** | `id` | Texto | SI | RIF/Cédula (Clave Primaria). |
| **Nombre** | `nombre` | Texto | SI | |
| **Email** | `email` | Email | NO | |
| **Telefono** | `telefono` | Texto | NO | |
| **Direccion** | `direccion` | Texto | NO | |
| **Es_VIP** | `vip` | Si/No | NO | 1/0 o Si/No. |

### Plantilla 3: `plantilla_proveedores.xlsx`
| Cabecera Excel | Campo DB | Tipo | Obligatorio | Nota |
| :--- | :--- | :--- | :--- | :--- |
| **Nombre** | `nombre` | Texto | SI | Clave única. |
| **RIF** | `rif` | Texto | NO | |
| **Persona_Contacto**| `contacto_nombre` | Texto | NO | |
| **Email** | `email` | Email | NO | |
| **Telefono** | `telefono` | Texto | NO | |
| **Direccion** | `direccion` | Texto | NO | |

### Plantilla 4: `plantilla_catalogos.xlsx`
*Hoja múltiple con pestañas: "Categorias", "Marcas"*

**Pestaña Categorias:**
*   Nombre (Obligatorio)
*   Descripcion

**Pestaña Marcas:**
*   Nombre (Obligatorio)
*   SitioWeb
*   Descripcion

## 4. Mejoras UX Acceso Denegado

Implementar un sistema amigable para manejar errores de permisos (403/401) sin redirecciones bruscas.

### Cambios Propuestos
#### Backend (`php/sesion.php`)
*   Modificar la lógica de redirección. En lugar de enviar a `dashboard.php` silenciosamente, enviar `dashboard.php?error=access_denied&code=403`.
*   Para errores de autenticación, usar `login.php?error=auth_required`.

#### Frontend (`assets/js/common.js`)
*   Implementar un "listener" al cargar la página que revise los parámetros URL.
*   Si detecta `error=access_denied`, mostrar el modal `showModal()` existente con:
    *   **Título**: "Acceso Restringido" (o "Sesión Expirada").
    *   **Mensaje**: Explicación clara según el código (403: Permisos insuficientes, 401: Login requerido).
    *   **Icono/Tipo**: Error/Warning.
*   Limpiar la URL usando `history.replaceState` para que el error no persista al recargar.

## 5. Sistema de Notificaciones Persistentes (Windows-Style)

Transformar el sistema de "Toasts" efímeros en un centro de control de notificaciones apilable y persistente.

### Nuevo Diseño de Notificación
*   **Header**: Barra superior de color (según tipo) con Icono + Texto ("Alerta", "Info", "Éxito") + Botón 'X'.
*   **Body**: Mensaje con timestamp (ej: "Hace 2 min").
*   **Contenedor**: Barra lateral derecha fija, con scroll si excede la altura.

### Funcionalidad (`assets/js/common.js` & CSS)
1.  **Persistencia**: Eliminar `setTimeout` automático. Las notificaciones se quedan hasta que el usuario las cierra.
2.  **Stacking**: Las nuevas notificaciones empujan a las viejas hacia abajo (o arriba).
3.  **Botón "Limpiar Todo"**: Aparece si hay > 2 notificaciones activas.
4.  **Minigrid Visual**: Icono y Título en el header resaltado (bg-success, bg-danger, etc.).

## 6. Próximos Pasos (Recomendados)

1.  **Corregir `process_import.php`**:
    *   Agregar mapeo para `proveedores`.
    *   Expandir mapeo de `productos` con todos los campos nuevos.
    *   Arreglar lógica de `clientes` para mapear la columna "ID_Fiscal" al campo `id`.
2.  **Generador de Plantillas**:
    *   Crear un script PHP (`php/download_template.php`) que genere estos archivos Excel vacíos con las cabeceras correctas para que el usuario los descargue.
3.  **Validación**:
    *   Implementar validación previa para asegurar que el ID de Cliente no duplique uno existente (la lógica actual hace UPDATE, lo cual es correcto, pero requiere el ID).
