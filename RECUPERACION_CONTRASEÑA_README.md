# Sistema de Recuperación de Contraseña - BUZZ Data Manager

## 📋 Descripción

Sistema completo de recuperación de contraseña por correo electrónico para BUZZ Data Manager.

## 🚀 Archivos Creados

1. **db/password_reset_tokens.sql** - Script SQL para crear la tabla de tokens
2. **forgot_password.php** - Página para solicitar recuperación de contraseña
3. **reset_password.php** - Página para restablecer la contraseña
4. **php/email_config.php** - Configuración del servidor de correo
5. **php/forgot_password_action.php** - Backend para procesar solicitudes
6. **php/validate_token.php** - Backend para validar tokens
7. **php/reset_password_action.php** - Backend para actualizar contraseñas

## 📦 Instalación

### 1. Crear la Tabla en la Base de Datos

Ejecuta el script SQL en tu base de datos:

```bash
mysql -u root -p dbuzz < db/password_reset_tokens.sql
```

O desde phpMyAdmin/HeidiSQL, ejecuta el contenido del archivo `db/password_reset_tokens.sql`.

### 2. Configurar el Servidor de Correo

Edita el archivo `php/email_config.php` y configura los siguientes valores:

#### Para Gmail:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu-email@gmail.com');
define('SMTP_PASSWORD', 'tu-app-password'); // Ver instrucciones abajo
define('MAIL_FROM_EMAIL', 'tu-email@gmail.com');
define('MAIL_FROM_NAME', 'BUZZ Data Manager');
define('APP_BASE_URL', 'http://localhost/BuzzDM'); // Cambiar según tu URL
```

#### Obtener App Password de Gmail:

1. Ve a tu cuenta de Google → Seguridad
2. Activa la verificación en 2 pasos
3. Busca "Contraseñas de aplicaciones"
4. Genera una nueva contraseña para "Correo"
5. Usa esa contraseña de 16 caracteres en `SMTP_PASSWORD`

#### Para otros proveedores:

**Outlook/Hotmail:**
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

**Yahoo:**
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

**Servidor SMTP personalizado:**
Consulta con tu proveedor de hosting los valores correctos.

### 3. Actualizar URL Base

En `php/email_config.php`, actualiza `APP_BASE_URL`:

- Desarrollo local: `http://localhost/BuzzDM`
- Producción: `https://tudominio.com`

## 🔧 Uso del Sistema

### Para Usuarios:

1. **Olvidé mi contraseña:**
   - En la página de login, clic en "¿Olvidaste tu contraseña?"
   - Ingresa tu correo electrónico
   - Recibirás un email con un enlace de recuperación

2. **Restablecer contraseña:**
   - Haz clic en el enlace del correo (válido por 1 hora)
   - Ingresa tu nueva contraseña (mínimo 8 caracteres, con mayúsculas, minúsculas y números)
   - Confirma la contraseña
   - Inicia sesión con tu nueva contraseña

### Requisitos de Contraseña:

- Mínimo 8 caracteres
- Al menos una letra mayúscula
- Al menos una letra minúscula
- Al menos un número
- Recomendado: caracteres especiales

## 🔒 Seguridad

El sistema implementa las siguientes medidas de seguridad:

1. **Tokens únicos y seguros:** 64 caracteres hexadecimales generados con `random_bytes()`
2. **Expiración de tokens:** 1 hora de validez
3. **Un solo uso:** Los tokens se marcan como usados después de utilizarse
4. **Invalidación automática:** Tokens antiguos se invalidan al solicitar uno nuevo
5. **No revelación de información:** El sistema no indica si un email está registrado
6. **Validación de contraseñas:** Requisitos mínimos de complejidad
7. **Protección de cuentas:** Verificación del estado de la cuenta (activo/bloqueado)
8. **Logs de actividad:** Se registran los cambios de contraseña

## 📊 Base de Datos

### Tabla: password_reset_tokens

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | int(10) unsigned | ID autoincremental |
| usuario_id | int(10) unsigned | ID del usuario (FK) |
| token | char(64) | Token único de recuperación |
| expiracion | timestamp | Fecha y hora de expiración |
| usado | tinyint(1) | Indica si el token ya fue usado |
| creado_at | timestamp | Fecha de creación |

## 🧪 Pruebas

### Probar el Sistema:

1. **Solicitar recuperación:**
   ```
   URL: http://localhost/BuzzDM/forgot_password.php
   Email: usuario@ejemplo.com
   ```

2. **Verificar el correo:**
   - Revisa tu bandeja de entrada
   - Haz clic en el enlace de recuperación

3. **Restablecer contraseña:**
   - Ingresa nueva contraseña
   - Confirma la contraseña
   - Inicia sesión

### Solución de Problemas:

**No recibo el correo:**
- Verifica la configuración SMTP en `php/email_config.php`
- Revisa la carpeta de spam
- Verifica que el usuario existe en la base de datos
- Revisa los logs de PHP para errores de SMTP

**Error al enviar correo:**
- Verifica las credenciales SMTP
- Para Gmail, asegúrate de usar App Password
- Verifica que el puerto SMTP no esté bloqueado por firewall
- Habilita la extensión OpenSSL en PHP

**Token inválido o expirado:**
- Los tokens expiran después de 1 hora
- Solicita un nuevo enlace de recuperación
- Verifica que la URL del token esté completa

## 📝 Personalización

### Modificar tiempo de expiración:

En `php/forgot_password_action.php`, línea ~54:

```php
// Cambiar de 1 hora a 2 horas
$expiracion = date('Y-m-d H:i:s', strtotime('+2 hours'));
```

### Personalizar plantilla de email:

Edita el contenido HTML en `php/forgot_password_action.php`, línea ~94.

### Cambiar requisitos de contraseña:

Edita las validaciones en:
- `php/reset_password_action.php` (backend)
- `reset_password.php` (frontend)

## 🔄 Mantenimiento

### Limpiar tokens expirados:

Ejecuta periódicamente (cron job recomendado):

```sql
DELETE FROM password_reset_tokens
WHERE expiracion < NOW() OR usado = 1;
```

### Script de limpieza automática:

Crea un archivo `php/cleanup_tokens.php`:

```php
<?php
require_once 'db.php';
$pdo = getDbConnection();
$stmt = $pdo->prepare("DELETE FROM password_reset_tokens WHERE expiracion < NOW() OR usado = 1");
$stmt->execute();
echo "Tokens limpiados: " . $stmt->rowCount();
?>
```

Configura un cron job para ejecutarlo diariamente:
```
0 2 * * * php /ruta/a/BuzzDM/php/cleanup_tokens.php
```

## 📞 Soporte

Para problemas o consultas:
- Revisa los logs de PHP: `error_log`
- Verifica la configuración de la base de datos
- Asegúrate de que PHPMailer esté instalado correctamente

## ✅ Checklist de Implementación

- [ ] Ejecutar script SQL para crear tabla
- [ ] Configurar credenciales SMTP en `email_config.php`
- [ ] Actualizar `APP_BASE_URL` con la URL correcta
- [ ] Probar envío de correos
- [ ] Probar flujo completo de recuperación
- [ ] Configurar limpieza automática de tokens
- [ ] Verificar que los correos no vayan a spam
- [ ] Documentar credenciales SMTP de forma segura

## 🎉 ¡Listo!

El sistema de recuperación de contraseña está completamente implementado y listo para usar.

