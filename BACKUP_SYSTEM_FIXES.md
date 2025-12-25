# 📋 Documentación: Corrección del Sistema de Backups

## Resumen Ejecutivo

Se reinventó el sistema de backups para soportar **entornos de hosting** (no solo local) con un sistema dual: **Cron Job** como primario y **Fallback automático** como respaldo.

---

## 🔴 Problemas del Sistema Anterior

| Problema | Causa Raíz |
|----------|------------|
| Solo funcionaba localmente | Dependía exclusivamente de Windows Task Scheduler |
| Descarga de backups fallaba | El frontend usaba GET pero el backend esperaba POST con JSON |
| No había indicador visual | Sin forma de saber si los backups automáticos funcionaban |

---

## 🟢 Sistema Nuevo vs Anterior

```
┌─────────────────────────────────────────────────────────────┐
│            SISTEMA ANTERIOR (Solo Local)                    │
├─────────────────────────────────────────────────────────────┤
│  Windows Task Scheduler → auto_backup.php (CLI only)        │
│  ❌ No funciona en hosting                                  │
│  ❌ Sin indicador visual de estado                          │
│  ❌ Sin fallback si falla                                   │
└─────────────────────────────────────────────────────────────┘

                         ⬇️ MEJORADO A ⬇️

┌─────────────────────────────────────────────────────────────┐
│            SISTEMA NUEVO (Hosting Compatible)               │
├─────────────────────────────────────────────────────────────┤
│  PRIMARIO: Cron Job del hosting → auto_backup.php?token=X   │
│  FALLBACK: Poor Man's Cron → verifica en cada carga         │
│  ✅ Funciona en cualquier hosting                           │
│  ✅ Indicador visual con colores (verde/amarillo/rojo)      │
│  ✅ Toggle para activar/desactivar desde UI                 │
│  ✅ Configuración guardada en base de datos                 │
└─────────────────────────────────────────────────────────────┘
```

---

## 🐛 Errores Específicos Corregidos

### 1. Error: `Table 'configuraciones' doesn't exist`

**Problema:** El toggle de auto-backup intentaba guardar la configuración en una tabla que no existía.

**Solución:** Crear tabla `configuraciones`:
```sql
CREATE TABLE configuraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT DEFAULT NULL
);
```

---

### 2. Error: `backup_status.php` devolvía HTML en vez de JSON

**Problema:** El endpoint no estaba en la lista de scripts API permitidos en `sesion.php`, causando redirección.

**Solución:** Agregar a dos listas en `sesion.php`:
```php
// Lista de API scripts
$api_scripts = [..., 'backup_status.php'];

// Lista de páginas permitidas para admin
'admin' => [..., 'backup_status.php'],
```

---

### 3. Error: UI se quedaba en "VERIFICANDO..." permanentemente

**Problema:** Caché del navegador cargaba JavaScript antiguo.

**Solución:** Incrementar versión del cache buster:
```html
<!-- Antes -->
<script src="assets/js/admin.js?v=6"></script>

<!-- Después -->
<script src="assets/js/admin.js?v=7"></script>
```

---

### 4. Error: El fallback no detectaba la conexión PDO

**Problema:** La función `checkAndRunAutoBackup()` usaba `require_once` dentro de funciones, causando conflictos con la conexión ya existente.

**Solución:** Usar `global $pdo` en vez de crear nueva conexión:
```php
function checkAndRunAutoBackup() {
    global $pdo;  // ← Usar conexión existente
    // ...
}
```

---

## 📁 Archivos Creados/Modificados

| Archivo | Acción | Propósito |
|---------|--------|-----------|
| `php/backup_config.php` | NUEVO | Token, intervalos, configuración |
| `php/backup_check.php` | NUEVO | Sistema fallback "Poor Man's Cron" |
| `php/backup_status.php` | NUEVO | API para estado de backups |
| `php/auto_backup.php` | MODIFICADO | Soporta CLI + HTTP con token |
| `php/sesion.php` | MODIFICADO | Integra fallback + permisos |
| `admin.php` | MODIFICADO | UI responsive con toggle |
| `assets/js/admin.js` | MODIFICADO | Manejo de estado y toggle |
| `assets/css/styles.css` | MODIFICADO | Estilos para indicador |
| `db/configuraciones.sql` | NUEVO | Script SQL para tabla config |

---

## ⚙️ Cómo Configurar

### Para Hosting (Cron Job):
```bash
# En cPanel/Plesk, agregar cron:
0 2 * * * curl "https://tudominio.com/BuzzDM/php/auto_backup.php?token=TU_TOKEN"
```

### Para Local (Windows Task Scheduler):
```
php C:\xampp\htdocs\BuzzDM\php\auto_backup.php
```

### Fallback (Automático):
No requiere configuración. Si pasan 24+ horas sin backup, se crea uno cuando un admin visita la página.

---

## 🔐 Seguridad

- **Token secreto** en `backup_config.php` - cambiar en producción
- **Solo admins** pueden activar el fallback
- **Cooldown de 5 minutos** entre checks para evitar múltiples backups simultáneos
