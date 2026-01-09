# Solución al Error 400 en Recuperación de Contraseña

## 🔍 Diagnóstico del Problema

El error **400 (Bad Request)** que estás experimentando al intentar recuperar la contraseña se debe a que falta configurar el sistema de correo electrónico SMTP.

## 🛠️ Solución Paso a Paso

### Paso 1: Verificar el Estado Actual

Primero, verifica qué está causando el problema exactamente:

1. Abre tu navegador y visita:

   ```
   http://localhost/BuzzDM/php/check_forgot_password_config.php
   ```

2. Este script te mostrará:
   - ✓/✗ Conexión a la base de datos
   - ✓/✗ Tabla `password_reset_tokens`
   - ✓/✗ Configuración SMTP
   - ✓/✗ Librería PHPMailer
   - ✓/✗ URL base

### Paso 2: Crear/Verificar la Tabla en la Base de Datos

Si la tabla `password_reset_tokens` no existe:

#### Opción A: Usar el script automático

```cmd
cd c:\xampp\htdocs\BuzzDM\db
verificar_password_reset.bat
```

#### Opción B: Manualmente en phpMyAdmin

1. Abre http://localhost/phpmyadmin
2. Selecciona la base de datos `dbuzz`
3. Ve a la pestaña SQL
4. Ejecuta el contenido del archivo `db/verificar_password_reset.sql`

### Paso 3: Configurar las Credenciales de Correo

#### 3.1. Crear el archivo `.env`

Si no existe el archivo `.env` en la raíz del proyecto:

1. Crea un archivo llamado `.env` en `c:\xampp\htdocs\BuzzDM\.env`
2. Copia el siguiente contenido:

```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=dbuzz
DB_USER=root
DB_PASS=

# Configuración SMTP para Gmail
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=tu-email@gmail.com
SMTP_PASSWORD=tu-contraseña-de-aplicacion
SMTP_FROM_EMAIL=tu-email@gmail.com
SMTP_FROM_NAME=BuzzDM

# URL de la aplicación
APP_BASE_URL=http://localhost/BuzzDM

# Debug
APP_DEBUG=false
```

#### 3.2. Obtener Contraseña de Aplicación de Gmail

**IMPORTANTE:** NO uses tu contraseña normal de Gmail. Debes crear una "Contraseña de aplicación":

1. Inicia sesión en tu cuenta de Google
2. Ve a: https://myaccount.google.com/security
3. Activa la **verificación en dos pasos** si no la tienes
4. Luego ve a: https://myaccount.google.com/apppasswords
5. En "Seleccionar aplicación", elige **"Otra (nombre personalizado)"**
6. Escribe: **"BuzzDM"**
7. Haz clic en **"Generar"**
8. Copia la contraseña de **16 caracteres** que aparece (sin espacios)
9. Pégala en `SMTP_PASSWORD` en tu archivo `.env`

**Ejemplo de cómo se ve:**

```env
SMTP_USERNAME=miempresa@gmail.com
SMTP_PASSWORD=abcd efgh ijkl mnop  ← Copia sin espacios: abcdefghijklmnop
```

#### 3.3. Reemplazar con tus datos reales

Edita el archivo `.env` y cambia:

- `tu-email@gmail.com` → Tu correo real de Gmail
- `tu-contraseña-de-aplicacion` → La contraseña de 16 caracteres que generaste

### Paso 4: Verificar la Configuración

Para confirmar que todo está bien:

1. Abre: http://localhost/BuzzDM/php/test_env.php
2. Verifica que todas las variables muestren "OK" o "Configurado"
3. **IMPORTANTE:** Después de verificar, **elimina** el archivo `test_env.php` por seguridad

### Paso 5: Reiniciar Apache

Para que los cambios surtan efecto:

1. Abre el Panel de Control de XAMPP
2. Detén Apache (Stop)
3. Espera 2 segundos
4. Inicia Apache nuevamente (Start)

### Paso 6: Probar la Recuperación de Contraseña

1. Ve a: http://localhost/BuzzDM/forgot_password.php
2. Ingresa un correo electrónico registrado en el sistema
3. Haz clic en "Enviar Enlace de Recuperación"
4. Revisa tu bandeja de entrada (y spam)

## 🎯 Alternativas a Gmail

### Si usas Outlook/Hotmail:

```env
SMTP_HOST=smtp.office365.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=tu-email@outlook.com
SMTP_PASSWORD=tu-contraseña
```

### Si usas otro servidor SMTP:

```env
SMTP_HOST=mail.tudominio.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=correo@tudominio.com
SMTP_PASSWORD=tu-contraseña
```

## ❌ Problemas Comunes

### Error: "El sistema de recuperación de contraseña no está configurado"

- **Causa:** El archivo `.env` no existe o las variables `SMTP_USERNAME` y `SMTP_PASSWORD` están vacías
- **Solución:** Sigue el Paso 3 de esta guía

### Error: "SMTP connect() failed"

- **Causa:** No puede conectarse al servidor SMTP
- **Solución:**
  - Verifica tu conexión a internet
  - Verifica que el puerto 587 no esté bloqueado por tu firewall
  - Prueba cambiar a SSL (puerto 465):
    ```env
    SMTP_PORT=465
    SMTP_SECURE=ssl
    ```

### Error: "Authentication failed"

- **Causa:** Credenciales incorrectas
- **Solución:**
  - Verifica que estés usando una **contraseña de aplicación** (no tu contraseña normal)
  - Verifica que no haya espacios en la contraseña
  - Verifica que el correo esté bien escrito

### El correo no llega

- Revisa la carpeta de **Spam**
- Espera unos minutos (puede tardar)
- Verifica en `c:\xampp\php\logs\php_error_log` si hay errores

## 📚 Documentación Completa

Para más información detallada, consulta:

- `DOCS/CONFIGURACION_EMAIL.md` - Guía completa de configuración
- `DOCS/RECUPERACION_CONTRASENA.md` - Documentación del sistema

## ✅ Checklist de Verificación

Antes de volver a intentar, verifica:

- [ ] El archivo `.env` existe en `c:\xampp\htdocs\BuzzDM\.env`
- [ ] `SMTP_USERNAME` tiene tu correo de Gmail
- [ ] `SMTP_PASSWORD` tiene la contraseña de aplicación de 16 caracteres
- [ ] La tabla `password_reset_tokens` existe en la base de datos
- [ ] PHPMailer está instalado (carpeta `vendor/phpmailer` existe)
- [ ] Apache fue reiniciado después de crear/modificar el `.env`
- [ ] El diagnóstico (`check_forgot_password_config.php`) muestra todo en verde

---

**Si sigues teniendo problemas**, revisa los logs de error:

- Logs de PHP: `c:\xampp\php\logs\php_error_log`
- Logs de Apache: `c:\xampp\apache\logs\error.log`

O contáctame con el mensaje de error específico que aparezca.
