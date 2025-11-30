# 🚀 Guía Rápida de Instalación - Recuperación de Contraseña

## Paso 1: Instalar PHPMailer

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
composer update
```

Esto instalará PHPMailer que se agregó al `composer.json`.

## Paso 2: Crear la Tabla en la Base de Datos

Opción A - Usando MySQL desde terminal:
```bash
mysql -u root -p35128 dbuzz < db/password_reset_tokens.sql
```

Opción B - Usando HeidiSQL:
1. Abre HeidiSQL
2. Conecta a la base de datos `dbuzz`
3. Abre el archivo `db/password_reset_tokens.sql`
4. Ejecuta el script (F9)

Opción C - Usando phpMyAdmin:
1. Abre phpMyAdmin
2. Selecciona la base de datos `dbuzz`
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `db/password_reset_tokens.sql`
5. Haz clic en "Continuar"

## Paso 3: Configurar el Correo Electrónico

Edita el archivo `php/email_config.php`:

### Para Gmail (Recomendado para pruebas):

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'tu-email@gmail.com');          // ← Cambia esto
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx');          // ← App Password (ver abajo)
define('MAIL_FROM_EMAIL', 'tu-email@gmail.com');        // ← Cambia esto
define('MAIL_FROM_NAME', 'BUZZ Data Manager');
define('APP_BASE_URL', 'http://localhost/BuzzDM');      // ← Verifica la ruta
```

### Cómo obtener un App Password de Gmail:

1. Ve a https://myaccount.google.com/security
2. Busca "Contraseñas de aplicaciones" (debes tener verificación en 2 pasos activada)
3. Selecciona "Correo" y "Windows Computer" (o el dispositivo que uses)
4. Gmail generará una contraseña de 16 caracteres como: `abcd efgh ijkl mnop`
5. Copia esa contraseña y pégala en `SMTP_PASSWORD`

**Importante:** No uses tu contraseña normal de Gmail, usa el App Password.

## Paso 4: Verificar la Instalación

Ejecuta el script de verificación:

```bash
php php/verify_password_recovery.php
```

Esto te dirá si todo está correctamente configurado.

## Paso 5: Probar el Sistema

1. Ve a: `http://localhost/BuzzDM/login.php`
2. Haz clic en "¿Olvidaste tu contraseña?"
3. Ingresa un email de usuario existente
4. Revisa tu bandeja de entrada (y spam)
5. Haz clic en el enlace del correo
6. Ingresa tu nueva contraseña

## ¿Problemas?

### No recibo el correo

- **Revisa spam/correo no deseado**
- Verifica que `SMTP_USERNAME` y `SMTP_PASSWORD` sean correctos
- Asegúrate de usar App Password de Gmail, no tu contraseña normal
- Verifica que el usuario exista en la base de datos

### Error al enviar correo

- Verifica la configuración en `php/email_config.php`
- Comprueba que composer haya instalado PHPMailer: `composer show phpmailer/phpmailer`
- Revisa el archivo de logs de PHP para ver errores específicos

### El enlace no funciona

- Verifica que `APP_BASE_URL` en `email_config.php` sea correcto
- Asegúrate de que la tabla `password_reset_tokens` exista
- Verifica que el token no haya expirado (válido por 1 hora)

### El token ha expirado

- Los tokens expiran después de 1 hora
- Solicita un nuevo enlace de recuperación

## Configuración para Producción

Cuando subas a producción, recuerda:

1. Cambiar `APP_BASE_URL` a tu dominio real:
   ```php
   define('APP_BASE_URL', 'https://tudominio.com');
   ```

2. Usar credenciales SMTP de tu servidor/proveedor

3. Configurar un cron job para limpiar tokens:
   ```
   0 2 * * * php /ruta/completa/php/cleanup_tokens.php
   ```

## 📋 Checklist de Instalación

- [ ] Ejecutar `composer update`
- [ ] Crear tabla `password_reset_tokens` en la base de datos
- [ ] Configurar credenciales SMTP en `email_config.php`
- [ ] Actualizar `APP_BASE_URL` con la URL correcta
- [ ] Ejecutar `php/verify_password_recovery.php` sin errores
- [ ] Probar envío de correo de prueba
- [ ] Probar flujo completo: solicitar → recibir email → resetear contraseña

## ✅ ¡Listo!

Una vez completados estos pasos, tu sistema de recuperación de contraseña estará funcionando.

## 🆘 Soporte

Si encuentras algún problema:
1. Ejecuta `php php/verify_password_recovery.php` para diagnóstico
2. Revisa los logs de PHP
3. Verifica la consola del navegador (F12) para errores JavaScript

