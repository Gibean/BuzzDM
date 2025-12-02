# 🎨 Flujo Visual del Sistema de Recuperación de Contraseña

## 🔄 Diagrama de Flujo Completo

```
┌─────────────────────────────────────────────────────────────────┐
│                     USUARIO OLVIDA CONTRASEÑA                    │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 1: Página de Login (login.php)                            │
│  ┌───────────────────────────────────────┐                      │
│  │  Email:    [________________]         │                      │
│  │  Password: [________________]         │                      │
│  │  [        Acceder al Sistema       ]  │                      │
│  │                                       │                      │
│  │  ¿Olvidaste tu contraseña? ← CLIC    │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 2: Solicitar Recuperación (forgot_password.php)           │
│  ┌───────────────────────────────────────┐                      │
│  │  🔐 Recuperar Contraseña              │                      │
│  │                                       │                      │
│  │  Correo: [________________]           │                      │
│  │                                       │                      │
│  │  [  Enviar Enlace de Recuperación ]  │                      │
│  │                                       │                      │
│  │  ← Volver al inicio de sesión        │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 3: Backend Procesa (forgot_password_action.php)           │
│                                                                  │
│  1. ✓ Busca usuario en BD                                       │
│  2. ✓ Genera token único (64 caracteres)                        │
│  3. ✓ Guarda token en password_reset_tokens                     │
│  4. ✓ Crea enlace: reset_password.php?token=xxxxx               │
│  5. ✓ Envía email con PHPMailer                                 │
│                                                                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 4: Usuario Recibe Email                                   │
│  ┌───────────────────────────────────────┐                      │
│  │  De: BUZZ Data Manager                │                      │
│  │  Para: usuario@ejemplo.com            │                      │
│  │  Asunto: Recuperación de Contraseña   │                      │
│  │                                       │                      │
│  │  Hola Juan,                           │                      │
│  │                                       │                      │
│  │  Recibimos una solicitud para         │                      │
│  │  restablecer tu contraseña.           │                      │
│  │                                       │                      │
│  │  [ Restablecer Contraseña ] ← CLIC   │                      │
│  │                                       │                      │
│  │  Este enlace expira en 1 hora.        │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 5: Validar Token (validate_token.php)                     │
│                                                                  │
│  1. ✓ Verifica que token existe                                 │
│  2. ✓ Verifica que no esté usado                                │
│  3. ✓ Verifica que no esté expirado                             │
│  4. ✓ Verifica estado de cuenta                                 │
│                                                                  │
│  ┌─────────────────────────────────┐                            │
│  │ ¿Token válido?                  │                            │
│  │                                 │                            │
│  │  SÍ → Mostrar formulario        │                            │
│  │  NO → Mostrar error             │                            │
│  └─────────────────────────────────┘                            │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼ (SI ES VÁLIDO)
┌─────────────────────────────────────────────────────────────────┐
│  PASO 6: Restablecer Contraseña (reset_password.php)            │
│  ┌───────────────────────────────────────┐                      │
│  │  🔓 Restablecer Contraseña            │                      │
│  │                                       │                      │
│  │  Requisitos:                          │                      │
│  │  • Mínimo 8 caracteres                │                      │
│  │  • Una mayúscula, minúscula, número   │                      │
│  │                                       │                      │
│  │  Nueva Password:      [__________] 👁 │                      │
│  │  Confirmar Password:  [__________] 👁 │                      │
│  │                                       │                      │
│  │  Seguridad: ████████░░ Fuerte         │                      │
│  │                                       │                      │
│  │  [    Actualizar Contraseña    ]      │                      │
│  │                                       │                      │
│  │  ← Volver al inicio de sesión        │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 7: Backend Actualiza (reset_password_action.php)          │
│                                                                  │
│  1. ✓ Valida token nuevamente                                   │
│  2. ✓ Valida requisitos de contraseña                           │
│  3. ✓ Hash de la nueva contraseña                               │
│  4. ✓ Actualiza password en tabla usuarios                      │
│  5. ✓ Marca token como usado                                    │
│  6. ✓ Invalida otros tokens del usuario                         │
│  7. ✓ Registra en logs                                          │
│  8. ✓ Restablece intentos_fallidos = 0                          │
│                                                                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 8: Éxito - Redirigir a Login                              │
│  ┌───────────────────────────────────────┐                      │
│  │  ✅ ¡Contraseña actualizada!          │                      │
│  │                                       │                      │
│  │  Tu contraseña ha sido actualizada    │                      │
│  │  exitosamente.                        │                      │
│  │                                       │                      │
│  │  Serás redirigido al login...         │                      │
│  │                                       │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  PASO 9: Login con Nueva Contraseña                             │
│  ┌───────────────────────────────────────┐                      │
│  │  Email:    [usuario@ejemplo.com]      │                      │
│  │  Password: [nueva_contraseña]         │                      │
│  │  [        Acceder al Sistema       ]  │                      │
│  └───────────────────────────────────────┘                      │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
                    ✅ ¡LISTO!
```

---

## 🗃️ Base de Datos - Tabla password_reset_tokens

```
┌────────────────────────────────────────────────────────────┐
│ password_reset_tokens                                      │
├────┬──────────┬──────────┬────────────┬───────┬───────────┤
│ id │usuario_id│  token   │ expiracion │ usado │ creado_at │
├────┼──────────┼──────────┼────────────┼───────┼───────────┤
│ 1  │    5     │ abc123...│ 2025-11-30 │   1   │ 2025-11-29│
│ 2  │    7     │ def456...│ 2025-11-30 │   0   │ 2025-11-29│
│ 3  │    5     │ ghi789...│ 2025-11-30 │   0   │ 2025-11-29│
└────┴──────────┴──────────┴────────────┴───────┴───────────┘
                                ▲
                                │
                    Token expira en 1 hora
```

---

## 📧 Email Enviado (Vista HTML)

```
╔════════════════════════════════════════════════╗
║          🔐 Recuperación de Contraseña         ║
║            BUZZ Data Manager                   ║
╠════════════════════════════════════════════════╣
║                                                ║
║  Hola Juan Pérez,                              ║
║                                                ║
║  Hemos recibido una solicitud para             ║
║  restablecer la contraseña de tu cuenta        ║
║  en BUZZ Data Manager.                         ║
║                                                ║
║  ┌──────────────────────────────────┐          ║
║  │   Restablecer Contraseña         │          ║
║  └──────────────────────────────────┘          ║
║                                                ║
║  ⚠️ Importante:                                 ║
║  • Este enlace es válido por 1 hora            ║
║  • Solo puede ser usado una vez                ║
║  • Si no solicitaste este cambio,              ║
║    ignora este correo                          ║
║                                                ║
║  Saludos,                                      ║
║  El equipo de BUZZ Data Manager                ║
║                                                ║
╚════════════════════════════════════════════════╝
```

---

## 🎯 Características Visuales Implementadas

### forgot_password.php
- ✅ Logo de BUZZ
- ✅ Título y descripción clara
- ✅ Campo de email con validación
- ✅ Botón con animación de carga
- ✅ Alertas de éxito/error
- ✅ Enlace para volver al login
- ✅ Diseño responsive
- ✅ Tema oscuro consistente

### reset_password.php
- ✅ Logo de BUZZ
- ✅ Requisitos de contraseña visibles
- ✅ Campos con botón para mostrar/ocultar
- ✅ Indicador de fuerza de contraseña
- ✅ Validación en tiempo real
- ✅ Alertas de éxito/error
- ✅ Enlace para volver al login
- ✅ Diseño responsive
- ✅ Tema oscuro consistente

---

## 🔐 Seguridad en Cada Paso

```
PASO                    MEDIDA DE SEGURIDAD
────────────────────────────────────────────────
1. Solicitud           • No revela si email existe
                       • Rate limiting (prevenir spam)

2. Generación Token    • 64 caracteres aleatorios
                       • Criptográficamente seguro

3. Almacenamiento      • Hash almacenado en BD
                       • Expiración de 1 hora
                       • Un solo uso

4. Envío Email         • Enlace HTTPS (producción)
                       • No incluye contraseña

5. Validación          • Verifica expiración
                       • Verifica si usado
                       • Verifica estado de cuenta

6. Actualización       • Hash de contraseña (bcrypt)
                       • Validación de requisitos
                       • Transacción de BD

7. Limpieza            • Auto-invalidación
                       • Limpieza periódica (cron)
```

---

## 📱 Responsive Design

```
Desktop (> 992px)       Tablet (768-991px)      Mobile (< 767px)
┌─────────────────┐    ┌──────────────┐       ┌─────────┐
│ [Logo]          │    │   [Logo]     │       │ [Logo]  │
│                 │    │              │       │         │
│ [Form]          │    │   [Form]     │       │ [Form]  │
│                 │    │              │       │         │
│ [Link]          │    │   [Link]     │       │ [Link]  │
└─────────────────┘    └──────────────┘       └─────────┘
```

---

## 🎨 Colores del Sistema

```
Primario:    #FFA000  (Naranja BUZZ)
Fondo:       #0d1117  (Oscuro)
Tarjeta:     #161b22  (Gris oscuro)
Texto:       #c9d1d9  (Gris claro)
Éxito:       #198754  (Verde)
Error:       #dc3545  (Rojo)
Advertencia: #ffc107  (Amarillo)
```

---

## ⏱️ Tiempos del Sistema

```
Evento                         Tiempo
──────────────────────────────────────
Token válido                   1 hora
Auto-hide alertas              5-8 seg
Redirección después éxito      3 seg
Email delivery                 < 1 min
```

---

## 🎉 ¡Sistema Completamente Visual e Intuitivo!

El sistema está diseñado para ser:
- ✅ Fácil de usar
- ✅ Visualmente atractivo
- ✅ Consistente con el diseño de BUZZ
- ✅ Responsive para todos los dispositivos
- ✅ Seguro y confiable

