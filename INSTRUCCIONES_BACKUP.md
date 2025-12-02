# Configuración de Backups Automáticos - BuzzDM

Este documento explica cómo configurar backups automáticos cada 24 horas en Windows.

## 📋 Requisitos Previos

- XAMPP instalado en `c:\xampp\`
- Base de datos `dbuzz` funcionando
- Permisos de administrador en Windows

---

## 🔧 Configuración en Windows Task Scheduler

### Paso 1: Abrir el Programador de Tareas

1. Click en **Inicio** y buscar "Programador de tareas" o "Task Scheduler"
2. Click derecho en **Biblioteca del Programador de Tareas**
3. Seleccionar **Crear tarea básica...**

### Paso 2: Configurar la Tarea

**Nombre de la tarea:**
```
BuzzDM - Backup Automático Diario
```

**Descripción:**
```
Backup automático de la base de datos dbuzz cada 24 horas
```

Click en **Siguiente**

### Paso 3: Desencadenador (Trigger)

1. Seleccionar **Diariamente**
2. Click en **Siguiente**
3. Configurar hora deseada (recomendado: **2:00 AM** cuando hay menos actividad)
4. Recurrencia: **Cada 1 día**
5. Click en **Siguiente**

### Paso 4: Acción

1. Seleccionar **Iniciar un programa**
2. Click en **Siguiente**
3. En "Programa o script", ingresar:
   ```
   c:\xampp\htdocs\BuzzDM\ejecutar_backup_auto.bat
   ```
4. Dejar "Agregar argumentos" y "Iniciar en" vacíos
5. Click en **Siguiente**

### Paso 5: Finalizar

1. Marcar la casilla **Abrir el cuadro de diálogo Propiedades al hacer clic en Finalizar**
2. Click en **Finalizar**

### Paso 6: Configuración Avanzada

En la ventana de **Propiedades** que se abre:

**Pestaña General:**
- Marcar **Ejecutar con los privilegios más altos**
- Marcar **Ejecutar tanto si el usuario inició sesión como si no**

**Pestaña Condiciones:**
- Desmarcar **Iniciar la tarea solo si el equipo está conectado a la energía de CA**
- Marcar **Iniciar la tarea aunque el equipo trabaje con batería**

**Pestaña Configuración:**
- Marcar **Permitir ejecutar la tarea a petición**
- Marcar **Si la tarea no se ejecuta, ejecutarla lo antes posible**
- Tiempo máximo de ejecución: **1 hora**

Click en **Aceptar**

---

## ✅ Probar el Backup Automático

### Opción 1: Ejecutar manualmente desde Task Scheduler

1. Abrir el Programador de tareas
2. Buscar la tarea "BuzzDM - Backup Automático Diario"
3. Click derecho → **Ejecutar**
4. Esperar unos segundos
5. Verificar en `c:\xampp\htdocs\BuzzDM\backups\` que se creó un nuevo archivo

### Opción 2: Ejecutar desde línea de comandos

1. Abrir CMD o PowerShell como **Administrador**
2. Navegar al directorio:
   ```
   cd c:\xampp\htdocs\BuzzDM
   ```
3. Ejecutar el script:
   ```
   ejecutar_backup_auto.bat
   ```
4. Ver el output en consola
5. Verificar que se creó el archivo de backup

### Opción 3: Ejecutar directamente con PHP

```
cd c:\xampp\htdocs\BuzzDM
c:\xampp\php\php.exe php\auto_backup.php
```

---

## 📂 Ubicación de los Backups

Los backups se guardan en:
```
c:\xampp\htdocs\BuzzDM\backups\
```

**Formato de nombres:**
- Manuales: `backup_dbuzz_YYYY-MM-DD_HH-mm-ss.sql`
- Automáticos: `backup_auto_dbuzz_YYYY-MM-DD_HH-mm-ss.sql`

---

## 🔄 Rotación Automática

El script automático mantiene solo los **últimos 30 backups automáticos**. Los backups más antiguos se eliminan automáticamente para ahorrar espacio.

Los backups manuales **NO** se eliminan automáticamente.

---

## 📊 Verificar que Funciona

1. **Revisar el archivo de log:**
   ```
   c:\xampp\htdocs\BuzzDM\backups\auto_backup_log.txt
   ```

2. **Ver en la interfaz web:**
   - Ir a http://localhost/BuzzDM/admin.php
   - Click en pestaña **Backups**
   - Verificar que aparecen los backups automáticos con tipo "automático"

3. **Revisar tabla logs en base de datos:**
   - Abrir phpMyAdmin: http://localhost/phpmyadmin
   - Base de datos: `dbuzz`
   - Tabla: `logs`
   - Buscar registros con acción "Backup Automático"

---

## 🐛 Solución de Problemas

### El backup no se ejecuta

1. Verificar que XAMPP esté corriendo (MySQL debe estar activo)
2. Revisar las credenciales en `php\auto_backup.php` (líneas 31-34)
3. Verificar permisos de escritura en carpeta `backups/`
4. Revisar el Visor de eventos de Windows para errores del Task Scheduler

### Error "mysqldump not found"

Verificar que existe el archivo:
```
c:\xampp\mysql\bin\mysqldump.exe
```

Si no existe, reinstalar MySQL en XAMPP.

### Backup very large (>100 MB)

Considerar:
- Limpiar tablas de logs antiguas manualmente
- Ajustar la rotación para mantener menos backups
- Comprimir backups con 7-Zip o WinRAR

---

## 📧 Notificaciones (Opcional)

Para recibir notificaciones por email cuando falle un backup, puedes modificar el archivo `auto_backup.php` para enviar un correo usando PHPMailer u otra librería.

---

## 🔐 Seguridad

**IMPORTANTE:**
- Los backups contienen TODA la información de la base de datos
- Mantener la carpeta `backups/` segura
- No compartir archivos de backup públicamente
- Considerar almacenar copias en otra ubicación (disco externo, nube, etc.)
- Los archivos `.sql` pueden contener información sensible (contraseñas hasheadas, datos personales)

---

## ✨ ¡Listo!

Los backups automáticos ya están configurados y funcionando. El sistema creará un backup cada 24 horas a la hora configurada.
