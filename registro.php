<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Registro de Prueba Demo</title>
    <meta name="description" content="Registra tu empresa y comienza tu prueba de 15 días en BUZZ Data Manager.">

    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #10141a 0%, #1a1f28 50%, #10141a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-monospace);
            position: relative;
            overflow-x: hidden;
        }

        .hexagon-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.15;
            z-index: 1;
            pointer-events: none;
        }

        .back-home {
            position: fixed;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
        }

        .back-home a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--buzz-text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .back-home a:hover {
            color: var(--buzz-primary-color);
            background-color: rgba(255, 160, 0, 0.05);
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
            z-index: 10;
        }

        .login-brand-panel {
            flex: 1;
            background: linear-gradient(135deg, rgba(16, 20, 26, 0.9) 0%, rgba(13, 17, 23, 0.95) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
            position: relative;
            border-right: 1px solid rgba(255, 160, 0, 0.2);
        }

        .brand-content {
            z-index: 10;
            text-align: center;
        }

        .brand-content img {
            max-width: 360px;
            width: 100%;
            margin-bottom: 2rem;
            filter: drop-shadow(0 0 20px rgba(255, 160, 0, 0.3));
        }

        .brand-tagline {
            font-family: var(--font-primary);
            color: var(--buzz-primary-color);
            font-size: 1.4rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .brand-description {
            color: var(--buzz-text-muted);
            font-size: 1.05rem;
            max-width: 85%;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }

        .demo-perks {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            text-align: left;
            margin-top: 1rem;
            padding: 1.2rem;
            background: rgba(255, 160, 0, 0.04);
            border: 1px solid rgba(255, 160, 0, 0.15);
            border-radius: 6px;
            width: 85%;
        }

        .demo-perk-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--buzz-text-color);
            font-size: 0.92rem;
        }

        .demo-perk-item i {
            color: var(--buzz-primary-color);
            font-size: 1rem;
        }

        .login-form-panel {
            flex: 1.1;
            background: var(--buzz-dark-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem 2rem;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 500px;
            background: transparent;
            padding: 1rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-family: var(--font-primary);
            color: var(--buzz-text-color);
            font-size: 2rem;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }

        .login-header p {
            color: var(--buzz-text-muted);
            font-size: 0.95rem;
        }

        .mobile-logo {
            display: none;
            max-width: 220px;
            margin: 0 auto 1.5rem;
        }

        .form-group {
            margin-bottom: 1.4rem;
            position: relative;
        }

        .form-label {
            color: var(--buzz-text-muted);
            font-family: var(--font-monospace);
            font-weight: 500;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.92rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .form-label i {
            color: var(--buzz-primary-color);
            font-size: 0.85rem;
        }

        .form-control {
            background-color: rgba(16, 20, 26, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: var(--buzz-text-color) !important;
            padding: 0.85rem 1.1rem;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-size: 0.98rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(16, 20, 26, 0.98);
            border-color: var(--buzz-primary-color);
            box-shadow: 0 0 0 3px rgba(255, 160, 0, 0.18);
            outline: none;
        }

        .form-help {
            display: block;
            color: var(--buzz-text-muted);
            font-size: 0.78rem;
            margin-top: 0.35rem;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 3rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--buzz-text-muted);
            cursor: pointer;
            padding: 0.4rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--buzz-primary-color);
        }

        .btn-register-submit {
            width: 100%;
            padding: 1.1rem;
            background-color: var(--buzz-primary-color);
            color: var(--buzz-dark-bg);
            border: none;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-weight: 700;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .btn-register-submit:hover:not(:disabled) {
            background-color: #e6900a;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 160, 0, 0.25);
        }

        .btn-register-submit:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .alert-box {
            padding: 0.9rem 1.1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            line-height: 1.4;
        }

        .alert-box.error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-left: 4px solid var(--buzz-danger);
            color: var(--buzz-danger);
        }

        .alert-box.success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.3);
            border-left: 4px solid #28a745;
            color: #5cd677;
        }

        .alert-box.info {
            background-color: rgba(255, 160, 0, 0.1);
            border: 1px solid rgba(255, 160, 0, 0.3);
            border-left: 4px solid var(--buzz-primary-color);
            color: var(--buzz-primary-color);
        }

        .switch-auth-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--buzz-text-muted);
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .switch-auth-link:hover {
            color: var(--buzz-primary-color);
            text-decoration: underline;
        }

        /* OTP Input Styling */
        .otp-input-container {
            display: flex;
            justify-content: center;
            gap: 0.6rem;
            margin: 1.8rem 0;
        }

        .otp-digit {
            width: 52px;
            height: 60px;
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            background-color: rgba(16, 20, 26, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: 6px;
            color: var(--buzz-primary-color) !important;
            font-family: var(--font-monospace);
            transition: all 0.2s ease;
        }

        .otp-digit:focus {
            border-color: var(--buzz-primary-color);
            box-shadow: 0 0 10px rgba(255, 160, 0, 0.3);
            background-color: rgba(16, 20, 26, 1);
            outline: none;
        }

        .resend-container {
            text-align: center;
            margin-top: 1.2rem;
            font-size: 0.88rem;
            color: var(--buzz-text-muted);
        }

        .btn-resend {
            background: none;
            border: none;
            color: var(--buzz-primary-color);
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
            font-family: var(--font-monospace);
            font-size: 0.88rem;
        }

        .btn-resend:disabled {
            color: var(--buzz-text-muted);
            text-decoration: none;
            cursor: not-allowed;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .login-wrapper {
                flex-direction: column;
                min-height: 100vh;
            }
            .login-brand-panel {
                display: none;
            }
            .mobile-logo {
                display: block;
            }
            .login-form-panel {
                padding: 3rem 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .otp-digit {
                width: 42px;
                height: 52px;
                font-size: 1.5rem;
            }
            .back-home span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Hexagon Background Canvas -->
    <canvas id="hexagonCanvas" class="hexagon-bg"></canvas>

    <!-- Back to Home Button -->
    <div class="back-home">
        <a href="index.html">
            <i class="fas fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <!-- Layout Side to Side -->
    <div class="login-wrapper">
        
        <!-- Left Panel: Branding / Visuals -->
        <div class="login-brand-panel">
            <div class="brand-content">
                <img src="assets/img/logov1.png" alt="BUZZ Logo">
                <h1 class="brand-tagline">Prueba Demo 15 Días</h1>
                <p class="brand-description">
                    Aprovisionamiento automático de tu propia empresa con aislamiento total de datos (Multi-tenant).
                </p>

                <div class="demo-perks">
                    <div class="demo-perk-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Acceso completo a Inventario, Clientes y POS</span>
                    </div>
                    <div class="demo-perk-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Almacén principal aprovisionado</span>
                    </div>
                    <div class="demo-perk-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Validación de correo electrónico segura (OTP)</span>
                    </div>
                    <div class="demo-perk-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Exportación a Excel de respaldo al culminar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel: Registration & Verification Forms -->
        <div class="login-form-panel">
            <div class="login-container">
                
                <img src="assets/img/logov1.png" alt="BUZZ Logo" class="mobile-logo">

                <!-- Alert Message Box -->
                <div id="alertBox" class="alert-box" style="display: none;"></div>

                <!-- STEP 1: Registration Form -->
                <div id="stepRegisterContainer">
                    <div class="login-header">
                        <h2>Crear Cuenta Demo</h2>
                        <p>Completa los datos para habilitar tu entorno de prueba.</p>
                    </div>

                    <form id="registerForm" novalidate>
                        <input type="hidden" id="device_fingerprint" name="device_fingerprint" value="">

                        <!-- Company Name -->
                        <div class="form-group">
                            <label for="company_name" class="form-label">
                                <i class="fas fa-building"></i> Nombre de la Empresa
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="company_name"
                                name="company_name"
                                required
                                minlength="3"
                                placeholder="Ej: Distribuidora Oriente C.A."
                            >
                        </div>

                        <!-- Admin Name -->
                        <div class="form-group">
                            <label for="admin_name" class="form-label">
                                <i class="fas fa-user"></i> Nombre del Administrador
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="admin_name"
                                name="admin_name"
                                required
                                placeholder="Ej: Gisvel Aray"
                            >
                        </div>

                        <!-- Admin Email -->
                        <div class="form-group">
                            <label for="admin_email" class="form-label">
                                <i class="fas fa-envelope"></i> Correo Electrónico
                            </label>
                            <input
                                type="email"
                                class="form-control"
                                id="admin_email"
                                name="admin_email"
                                required
                                autocomplete="email"
                                placeholder="admin@tuempresa.com"
                            >
                            <span class="form-help">Te enviaremos un código de verificación de 6 dígitos.</span>
                        </div>

                        <!-- Admin Password -->
                        <div class="form-group">
                            <label for="admin_password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <div class="password-wrapper">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="admin_password"
                                    name="admin_password"
                                    required
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="Mínimo 8 caracteres"
                                >
                                <button type="button" class="password-toggle" id="passwordToggle" aria-label="Mostrar u ocultar contraseña">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-register-submit" id="btnSubmitRegister">
                            <span class="btn-text"><i class="fas fa-rocket"></i> Solicitar Código de Activación</span>
                            <span class="btn-spinner" style="display: none;"><i class="fas fa-circle-notch fa-spin"></i> Aprovisionando...</span>
                        </button>
                    </form>

                    <!-- Link to Login -->
                    <a href="login" class="switch-auth-link">
                        ¿Ya tienes una cuenta registrada? Inicia sesión aquí
                    </a>
                </div>

                <!-- STEP 2: Email OTP Verification Form (Hidden by default) -->
                <div id="stepVerifyContainer" style="display: none;">
                    <div class="login-header">
                        <h2>Verificar Correo</h2>
                        <p>Hemos enviado un código de 6 dígitos a <strong id="verifyTargetEmail" style="color: var(--buzz-primary-color);"></strong></p>
                    </div>

                    <form id="verifyForm">
                        <div class="otp-input-container">
                            <input type="text" maxlength="1" class="otp-digit" data-index="0" autofocus inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-digit" data-index="1" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-digit" data-index="2" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-digit" data-index="3" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-digit" data-index="4" inputmode="numeric">
                            <input type="text" maxlength="1" class="otp-digit" data-index="5" inputmode="numeric">
                        </div>

                        <button type="submit" class="btn-register-submit" id="btnSubmitVerify">
                            <span class="btn-text"><i class="fas fa-check-circle"></i> Verificar y Entrar</span>
                            <span class="btn-spinner" style="display: none;"><i class="fas fa-circle-notch fa-spin"></i> Verificando...</span>
                        </button>
                    </form>

                    <div class="resend-container">
                        ¿No recibiste el código? 
                        <button type="button" class="btn-resend" id="btnResendCode" disabled>
                            Reenviar (<span id="resendCountdown">60</span>s)
                        </button>
                    </div>

                    <a href="#" id="btnBackToRegister" class="switch-auth-link" style="margin-top: 1rem;">
                        <i class="fas fa-arrow-left"></i> Modificar datos de registro
                    </a>
                </div>

                <!-- Legal Copyright -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: var(--buzz-text-muted); font-size: 0.68rem; line-height: 1.6;">
                    <img src="assets/img/recursosinv/Logo_Uptjaa.svg" alt="UPTJAA" style="width: 36px; opacity: 0.8; display: block; margin: 0 auto 0.5rem;">
                    <p style="margin: 0;">&copy; 2025-2026 <strong style="color: var(--buzz-text-color);">Gisvel Aray y Yeicson Acosta</strong></p>
                    <p style="margin: 0;">Licencia de uso: <strong style="color: var(--buzz-text-color);">Librería Txiki C.A.</strong></p>
                    <p style="margin: 0; font-size: 0.6rem; opacity: 0.6; margin-top: 2px;">Derechos institucionales: UPTJAA</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://openfpcdn.io/fingerprintjs/v4"></script>
    <script>
        // Hexagon Canvas Background
        const canvas = document.getElementById('hexagonCanvas');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            let hexagons = [];

            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            class Hexagon {
                constructor(x, y, size) {
                    this.x = x;
                    this.y = y;
                    this.size = size;
                    this.opacity = Math.random() * 0.45;
                    this.fadeDirection = Math.random() > 0.5 ? 1 : -1;
                    this.fadeSpeed = 0.003 + Math.random() * 0.006;
                }

                draw() {
                    ctx.beginPath();
                    for (let i = 0; i < 6; i++) {
                        const angle = (Math.PI / 3) * i;
                        const x = this.x + this.size * Math.cos(angle);
                        const y = this.y + this.size * Math.sin(angle);
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    ctx.strokeStyle = `rgba(255, 160, 0, ${this.opacity})`;
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                }

                update() {
                    this.opacity += this.fadeSpeed * this.fadeDirection;
                    if (this.opacity > 0.45 || this.opacity < 0.02) {
                        this.fadeDirection *= -1;
                    }
                    this.draw();
                }
            }

            function createHexagons() {
                hexagons = [];
                const size = 50;
                const spacing = size * 1.8;
                for (let y = -size; y < canvas.height + size; y += spacing * 1.5) {
                    for (let x = -size; x < canvas.width + size; x += spacing * Math.sqrt(3)) {
                        const offsetX = (y / (spacing * 1.5)) % 2 === 0 ? 0 : spacing * Math.sqrt(3) / 2;
                        hexagons.push(new Hexagon(x + offsetX, y, size));
                    }
                }
            }
            createHexagons();
            window.addEventListener('resize', createHexagons);

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                hexagons.forEach(hex => hex.update());
                requestAnimationFrame(animate);
            }
            animate();
        }

        // FingerprintJS Integration
        let clientFingerprint = null;
        if (window.FingerprintJS) {
            FingerprintJS.load()
                .then(fp => fp.get())
                .then(result => {
                    clientFingerprint = result.visitorId;
                    const inputFp = document.getElementById('device_fingerprint');
                    if (inputFp) inputFp.value = clientFingerprint;
                })
                .catch(err => console.warn('FingerprintJS fallback to backend generation:', err));
        }

        // Password visibility toggle
        const passwordToggle = document.getElementById('passwordToggle');
        const passwordInput = document.getElementById('admin_password');
        const toggleIcon = document.getElementById('toggleIcon');
        if (passwordToggle && passwordInput && toggleIcon) {
            passwordToggle.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        }

        // State & DOM elements
        const stepRegister = document.getElementById('stepRegisterContainer');
        const stepVerify = document.getElementById('stepVerifyContainer');
        const registerForm = document.getElementById('registerForm');
        const verifyForm = document.getElementById('verifyForm');
        const alertBox = document.getElementById('alertBox');
        const btnSubmitRegister = document.getElementById('btnSubmitRegister');
        const btnSubmitVerify = document.getElementById('btnSubmitVerify');
        const btnResendCode = document.getElementById('btnResendCode');
        const resendCountdown = document.getElementById('resendCountdown');
        const verifyTargetEmail = document.getElementById('verifyTargetEmail');
        const btnBackToRegister = document.getElementById('btnBackToRegister');
        const otpDigits = document.querySelectorAll('.otp-digit');

        let registeredEmail = '';
        let countdownTimer = null;

        function showAlert(message, type = 'error') {
            alertBox.className = `alert-box ${type}`;
            let icon = 'fas fa-exclamation-triangle';
            if (type === 'success') icon = 'fas fa-check-circle';
            if (type === 'info') icon = 'fas fa-info-circle';

            alertBox.innerHTML = `<i class="${icon}"></i> <span>${message}</span>`;
            alertBox.style.display = 'flex';
        }

        function clearAlert() {
            alertBox.style.display = 'none';
            alertBox.innerHTML = '';
        }

        function setButtonLoading(button, isLoading) {
            button.disabled = isLoading;
            const textSpan = button.querySelector('.btn-text');
            const spinnerSpan = button.querySelector('.btn-spinner');
            if (textSpan && spinnerSpan) {
                textSpan.style.display = isLoading ? 'none' : 'inline-block';
                spinnerSpan.style.display = isLoading ? 'inline-block' : 'none';
            }
        }

        function startCountdown(seconds = 60) {
            let left = seconds;
            btnResendCode.disabled = true;
            resendCountdown.textContent = left;
            if (countdownTimer) clearInterval(countdownTimer);

            countdownTimer = setInterval(() => {
                left--;
                if (left <= 0) {
                    clearInterval(countdownTimer);
                    btnResendCode.disabled = false;
                    btnResendCode.innerHTML = 'Reenviar código';
                } else {
                    resendCountdown.textContent = left;
                }
            }, 1000);
        }

        // Step 1: Submit Register Form
        if (registerForm) {
            registerForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearAlert();

                const companyName = document.getElementById('company_name').value.trim();
                const adminName = document.getElementById('admin_name').value.trim();
                const adminEmail = document.getElementById('admin_email').value.trim();
                const adminPassword = document.getElementById('admin_password').value;
                const fingerprint = document.getElementById('device_fingerprint').value || clientFingerprint;

                if (companyName.length < 3) {
                    showAlert('El nombre de la empresa debe tener al menos 3 caracteres.');
                    return;
                }
                if (!adminEmail.includes('@') || !adminEmail.includes('.')) {
                    showAlert('Por favor ingresa un correo electrónico válido.');
                    return;
                }
                if (adminPassword.length < 8) {
                    showAlert('La contraseña debe tener al menos 8 caracteres.');
                    return;
                }

                setButtonLoading(btnSubmitRegister, true);

                try {
                    const response = await fetch('php/api_register_demo.php?action=register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            company_name: companyName,
                            admin_name: adminName,
                            admin_email: adminEmail,
                            admin_password: adminPassword,
                            device_fingerprint: fingerprint
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        registeredEmail = adminEmail;
                        verifyTargetEmail.textContent = registeredEmail;
                        stepRegister.style.display = 'none';
                        stepVerify.style.display = 'block';
                        showAlert(data.message || 'Código enviado. Revisa tu bandeja de entrada o spam.', 'info');
                        startCountdown(60);
                        if (otpDigits[0]) otpDigits[0].focus();
                    } else {
                        showAlert(data.message || 'No fue posible registrar la empresa.');
                    }
                } catch (err) {
                    console.error('Error:', err);
                    showAlert('Error de comunicación con el servidor. Verifica tu conexión.');
                } finally {
                    setButtonLoading(btnSubmitRegister, false);
                }
            });
        }

        // OTP inputs interaction (auto-focus next, backspace handling, paste handling)
        otpDigits.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val ? val[0] : '';
                if (val && index < otpDigits.length - 1) {
                    otpDigits[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpDigits[index - 1].focus();
                }
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                if (pasted.length === 6) {
                    otpDigits.forEach((digitInput, idx) => {
                        digitInput.value = pasted[idx] || '';
                    });
                    otpDigits[5].focus();
                }
            });
        });

        // Step 2: Submit OTP Verification Form
        if (verifyForm) {
            verifyForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                clearAlert();

                let code = '';
                otpDigits.forEach(input => code += input.value.trim());

                if (code.length !== 6) {
                    showAlert('Por favor ingresa el código completo de 6 dígitos.');
                    return;
                }

                setButtonLoading(btnSubmitVerify, true);

                try {
                    const response = await fetch('php/api_register_demo.php?action=verify_code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            email: registeredEmail,
                            code: code
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showAlert(data.message + '<br><small>Redirigiendo a tu panel...</small>', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect_url || 'dashboard';
                        }, 1200);
                    } else {
                        showAlert(data.message || 'Código de verificación incorrecto.');
                    }
                } catch (err) {
                    console.error('Error:', err);
                    showAlert('Error al procesar la verificación.');
                } finally {
                    setButtonLoading(btnSubmitVerify, false);
                }
            });
        }

        // Resend Code Action
        if (btnResendCode) {
            btnResendCode.addEventListener('click', async function() {
                clearAlert();
                btnResendCode.disabled = true;

                try {
                    const response = await fetch('php/api_register_demo.php?action=resend_code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ email: registeredEmail })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        showAlert(data.message, 'info');
                        btnResendCode.innerHTML = 'Reenviar (<span id="resendCountdown">60</span>s)';
                        startCountdown(60);
                    } else {
                        showAlert(data.message || 'No se pudo reenviar el código.');
                        btnResendCode.disabled = false;
                    }
                } catch (err) {
                    console.error('Error:', err);
                    showAlert('Error al solicitar reenvío de código.');
                    btnResendCode.disabled = false;
                }
            });
        }

        // Back to registration button
        if (btnBackToRegister) {
            btnBackToRegister.addEventListener('click', function(e) {
                e.preventDefault();
                clearAlert();
                stepVerify.style.display = 'none';
                stepRegister.style.display = 'block';
            });
        }
    </script>
</body>
</html>
