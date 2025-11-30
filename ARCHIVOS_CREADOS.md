# 📁 Archivos del Sistema de Recuperación de Contraseña

## 📝 Resumen de Cambios

Se ha implementado un sistema completo de recuperación de contraseña por correo electrónico para BUZZ Data Manager.

---

## ✅ Archivos Creados

### 🗄️ Base de Datos
- **`db/password_reset_tokens.sql`** - Script SQL para crear la tabla de tokens de recuperación

### 🌐 Frontend (Páginas Web)
- **`forgot_password.php`** - Formulario para solicitar recuperación de contraseña
- **`reset_password.php`** - Formulario para establecer nueva contraseña

### ⚙️ Backend (PHP)
- **`php/email_config.php`** - Configuración del servidor de correo (SMTP)
- **`php/forgot_password_action.php`** - Procesa solicitudes y envía emails
- **`php/validate_token.php`** - Valida tokens de recuperación
- **`php/reset_password_action.php`** - Actualiza la contraseña del usuario
- **`php/cleanup_tokens.php`** - Limpia tokens expirados (cron job)

### 🔧 Utilidades
- **`php/test_email.php`** - Script para probar envío de correos
- **`php/verify_password_recovery.php`** - Script de verificación del sistema

### 📚 Documentación
- **`INSTALACION_RAPIDA.md`** - Guía rápida de instalación
- **`RECUPERACION_CONTRASEÑA_README.md`** - Documentación completa del sistema
- **`ARCHIVOS_CREADOS.md`** - Este archivo (resumen de archivos)

---

## 🔄 Archivos Modificados

- **`login.php`** - Enlace a "¿Olvidaste tu contraseña?" ahora funcional
- **`composer.json`** - Agregado PHPMailer como dependencia

---

## 📋 Estructura del Sistema

```
BuzzDM/
│
├── forgot_password.php              # Página: Solicitar recuperación
├── reset_password.php               # Página: Restablecer contraseña
├── login.php                        # Modificado: enlace funcional
│
├── db/
│   └── password_reset_tokens.sql   # SQL: Crear tabla
│
├── php/
│   ├── email_config.php            # Configuración SMTP
│   ├── forgot_password_action.php  # Backend: Enviar email
│   ├── validate_token.php          # Backend: Validar token
│   ├── reset_password_action.php   # Backend: Actualizar password
│   ├── cleanup_tokens.php          # Utilidad: Limpiar tokens
│   ├── test_email.php              # Utilidad: Probar envío
│   └── verify_password_recovery.php # Utilidad: Verificar sistema
│
├── composer.json                    # Modificado: PHPMailer
│
└── Documentación:
    ├── INSTALACION_RAPIDA.md
    ├── RECUPERACION_CONTRASEÑA_README.md
    └── ARCHIVOS_CREADOS.md
```

---

## 🎯 Flujo del Sistema

### 1️⃣ Usuario Olvida su Contraseña
```
login.php → Clic "¿Olvidaste tu contraseña?" → forgot_password.php
```

### 2️⃣ Solicita Recuperación
```
forgot_password.php → Ingresa email → forgot_password_action.php
                                    ↓
                              Genera token
                                    ↓
                           Guarda en BD (password_reset_tokens)
                                    ↓
                           Envía email con enlace
```

### 3️⃣ Recibe Email
```
Email → Enlace: reset_password.php?token=xxxxx
```

### 4️⃣ Valida Token
```
reset_password.php → validate_token.php → Verifica token válido
                                        ↓
                                   Si válido: Muestra formulario
                                   Si inválido: Muestra error
```

### 5️⃣ Restablece Contraseña
```
reset_password.php → Ingresa nueva password → reset_password_action.php
                                            ↓
                                   Valida token nuevamente
                                            ↓
                                   Actualiza password en BD
                                            ↓
                                   Marca token como usado
                                            ↓
                                   Redirige a login.php
```

---

## 🔐 Características de Seguridad

✅ Tokens únicos de 64 caracteres (SHA-256)
✅ Expiración automática (1 hora)
✅ Un solo uso por token
✅ Invalidación de tokens antiguos
✅ Validación de contraseñas fuertes
✅ Protección contra enumeración de usuarios
✅ Verificación del estado de la cuenta
✅ Logs de cambios de contraseña

---

## 📊 Tabla: password_reset_tokens

| Campo       | Tipo              | Descripción                    |
|-------------|-------------------|--------------------------------|
| id          | int(10) unsigned  | ID autoincremental             |
| usuario_id  | int(10) unsigned  | FK a tabla usuarios            |
| token       | char(64)          | Token único de recuperación    |
| expiracion  | timestamp         | Fecha/hora de expiración       |
| usado       | tinyint(1)        | 0=no usado, 1=ya usado         |
| creado_at   | timestamp         | Fecha de creación del token    |

---

## 🛠️ Requisitos del Sistema

### PHP:
- Versión: 7.4 o superior
- Extensiones: PDO, pdo_mysql, openssl, mbstring

### Composer:
- PHPMailer 6.9+
- Otras dependencias ya existentes

### Base de Datos:
- MySQL/MariaDB 5.7+
- Base de datos: dbuzz

### Servidor SMTP:
- Gmail (recomendado para pruebas)
- Outlook, Yahoo, o servidor personalizado

---

## 🚀 Instalación Rápida

1. **Instalar PHPMailer:**
   ```bash
   composer update
   ```

2. **Crear tabla:**
   ```bash
   mysql -u root -p35128 dbuzz < db/password_reset_tokens.sql
   ```

3. **Configurar email:**
   Editar `php/email_config.php` con credenciales SMTP

4. **Verificar:**
   ```bash
   php php/verify_password_recovery.php
   ```

5. **Probar:**
   ```bash
   php php/test_email.php
   ```

---

## 📖 Documentación Completa

Para información detallada, consulta:

- **Instalación:** `INSTALACION_RAPIDA.md`
- **Documentación Completa:** `RECUPERACION_CONTRASEÑA_README.md`

---

## ✅ Checklist de Implementación

- [ ] Ejecutar `composer update`
- [ ] Crear tabla `password_reset_tokens`
- [ ] Configurar credenciales SMTP
- [ ] Actualizar `APP_BASE_URL`
- [ ] Ejecutar script de verificación
- [ ] Probar envío de correo
- [ ] Probar flujo completo
- [ ] Configurar limpieza automática (cron)

---

## 🆘 Soporte

Si encuentras problemas:

1. Ejecuta: `php php/verify_password_recovery.php`
2. Prueba email: `php php/test_email.php`
3. Revisa logs de PHP
4. Consulta documentación completa

---

**Fecha de Implementación:** Noviembre 2025
**Versión:** 1.0
**Estado:** ✅ Completo y listo para usar

