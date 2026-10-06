<?php
require_once __DIR__ . '/php/security.php';

// Las cabeceras deben emitirse antes de cualquier salida HTML.
sendSecurityHeaders();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Login</title>
    <meta name="description" content="Accede a BUZZ Data Manager, tu sistema profesional de gestión empresarial para inventario, pedidos y reportes.">

    <!-- Open Graph (Facebook, WhatsApp, Discord, etc.) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://buzzdatamanager.com/login">
    <meta property="og:title" content="BUZZ Data Manager - Iniciar Sesión">
    <meta property="og:description" content="Accede a tu sistema de gestión empresarial. Control de inventario, pedidos, clientes y reportes avanzados.">
    <meta property="og:image" content="https://buzzdatamanager.com/assets/img/logov1_long.png">
    <meta property="og:site_name" content="BUZZ Data Manager">
    <meta property="og:locale" content="es_ES">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="BUZZ Data Manager - Iniciar Sesión">
    <meta name="twitter:description" content="Accede a tu sistema de gestión empresarial profesional.">
    <meta name="twitter:image" content="https://buzzdatamanager.com/assets/img/logov1_long.png">

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
            overflow: hidden;
        }

        /* Subtle hexagon background */
        .hexagon-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.15;
            z-index: 1;
        }

        /* Back to home button */
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

        .back-home a i {
            font-size: 0.85rem;
        }

        /* Layout Side to Side */
        .login-wrapper {
            display: flex;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* Left Panel - Branding */
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
            overflow: hidden;
        }

        /* Abstract Tech Background in left panel */
        .brand-bg-graphics {
            display: none; /* Eliminando fuentes de luz random */
        }

        .brand-content {
            z-index: 10;
            text-align: center;
        }

        .brand-content img {
            max-width: 400px;
            width: 100%;
            margin-bottom: 2rem;
            filter: drop-shadow(0 0 20px rgba(255, 160, 0, 0.3));
        }

        .brand-tagline {
            font-family: var(--font-primary);
            color: var(--buzz-primary-color);
            font-size: 1.5rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .brand-description {
            color: var(--buzz-text-muted);
            font-size: 1.1rem;
            max-width: 80%;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Right Panel - Form */
        .login-form-panel {
            flex: 1;
            background: var(--buzz-dark-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            background: transparent;
            padding: 2.5rem 2rem;
            border: none;
            backdrop-filter: none;
            box-shadow: none;
        }

        .login-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .login-header h2 {
            font-family: var(--font-primary);
            color: var(--buzz-text-color);
            font-size: 2.2rem;
            margin-bottom: 0.8rem;
            letter-spacing: 1px;
        }

        .login-header p {
            color: var(--buzz-text-muted);
            font-size: 1.1rem;
        }

        .mobile-logo {
            display: none;
            max-width: 250px;
            margin: 0 auto 2rem;
        }

        /* Form styling */
        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 2.2rem;
            position: relative;
        }

        .form-label {
            color: var(--buzz-text-muted);
            font-family: var(--font-monospace);
            font-weight: 500;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-label i {
            color: var(--buzz-primary-color);
            font-size: 0.95rem;
            opacity: 0.8;
        }

        .form-control {
            background-color: rgba(16, 20, 26, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--buzz-text-color) !important;
            padding: 1.2rem 1.5rem;
            border-radius: 8px;
            font-family: var(--font-monospace);
            font-size: 1.15rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(16, 20, 26, 0.95);
            border-color: var(--buzz-primary-color);
            box-shadow: 0 0 0 3px rgba(255, 160, 0, 0.15);
            color: var(--buzz-text-color) !important;
            outline: none;
        }

        /* Password field with toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 3rem;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--buzz-text-muted);
            cursor: pointer;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--buzz-primary-color);
        }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 1.25rem;
            background-color: var(--buzz-primary-color);
            color: var(--buzz-dark-bg);
            border: none;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-weight: 700;
            font-size: 1.15rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
        }

        .btn-login:hover {
            background-color: #e6900a;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 160, 0, 0.2);
        }

        .btn-login:active{
            transform: translateY(0);
        }

        /* Alert */
        .alert-login {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            border-left: 4px solid var(--buzz-danger);
            color: var(--buzz-danger);
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Forgot password link */
        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: var(--buzz-text-muted);
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--buzz-primary-color);
            text-decoration: underline;
        }

        /* Responsive Breakpoints & Adjustments */
        
        /* Large Laptops / Small Desktops (max-width: 1200px) */
        @media (max-width: 1200px) {
            .login-brand-panel {
                padding: 2rem;
            }
            .brand-content img {
                max-width: 320px;
            }
            .brand-tagline {
                font-size: 1.3rem;
            }
            .brand-description {
                font-size: 1rem;
                max-width: 90%;
            }
            .login-container {
                max-width: 420px;
                padding: 2rem;
            }
            .login-header h2 {
                font-size: 2rem;
            }
            .form-control {
                padding: 1rem 1.2rem;
                font-size: 1.1rem;
            }
            .btn-login {
                padding: 1.1rem;
                font-size: 1.1rem;
            }
        }

        /* Tablets / iPads (max-width: 992px) */
        @media (max-width: 992px) {
            .login-brand-panel {
                flex: 0.8; /* Dar menos espacio al branding */
            }
            .login-form-panel {
                flex: 1.2;
            }
            .login-container {
                padding: 1.5rem;
                max-width: 380px;
            }
        }

        /* Small Tablets / Large Phones (max-width: 768px) */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                height: auto;
                min-height: 100vh;
            }
            .login-brand-panel {
                display: none; /* Ocultar panel visual en formatos estrechos */
            }
            .mobile-logo {
                display: block; /* Mostrar logo solo arriba del formulario */
                max-width: 280px;
                margin: 0 auto 2.5rem;
            }
            .login-form-panel {
                padding: 2rem 1.5rem;
                align-items: center; /* Centrar verticalmente */
                background: transparent; /* Dejar ver los hexágonos del body */
            }
            .login-container {
                max-width: 450px;
                width: 100%;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 1.5rem;
            }
            .login-header {
                margin-bottom: 2.5rem;
            }
            .login-header h2 {
                font-size: 2.2rem;
            }
            .login-header p {
                font-size: 1.1rem;
            }
            .form-group {
                margin-bottom: 2rem;
            }
            .form-label {
                font-size: 1.1rem;
            }
            .form-control {
                padding: 1.2rem;
                font-size: 1.15rem;
            }
            .btn-login {
                padding: 1.25rem;
                font-size: 1.2rem;
            }
        }
        
        /* Small Phones (max-width: 480px) */
        @media (max-width: 480px) {
            .mobile-logo {
                max-width: 240px;
                margin-bottom: 2rem;
            }
            .login-form-panel {
                padding: 1.5rem 1rem;
            }
            .login-container {
                padding: 1rem;
            }
            .login-header h2 {
                font-size: 2rem;
                margin-bottom: 0.5rem;
            }
            .login-header p {
                font-size: 1.05rem;
            }
            .back-home {
                top: 1rem;
                left: 1rem;
            }
            .back-home a span {
                display: none; /* Ocultar texto "Volver" */
            }
            .form-label {
                font-size: 1.05rem;
            }
            .form-control {
                padding: 1.1rem;
                font-size: 1.1rem;
            }
            .btn-login {
                padding: 1.15rem;
                font-size: 1.15rem;
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

    <!-- Login Wrapper (Side to Side Grid) -->
    <div class="login-wrapper">
        
        <!-- Left Panel: Branding / Visuals -->
        <div class="login-brand-panel">
            <div class="brand-bg-graphics"></div>
            
            <div class="brand-content">
                <img src="assets/img/logov1.png" alt="BUZZ Logo">
                <h1 class="brand-tagline">Sistema de Gestión</h1>
                <p class="brand-description">
                    Control absoluto de inventario, pedidos, clientes y reportes.
                </p>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="login-form-panel">
            <div class="login-container">
                
                <img src="assets/img/logov1.png" alt="BUZZ Logo" class="mobile-logo">
                
                <div class="login-header">
                    <h2>Bienvenido</h2>
                    <p>Por favor, ingrese sus credenciales.</p>
                </div>

                <!-- Alert -->
                <?php if (isset($_GET['error'])): ?>
                <div class="alert-login">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>
                        <?php
                            if ($_GET['error'] === 'invalid') {
                                echo 'Credenciales inválidas. Por favor, verifica tu email y contraseña.';
                            } else {
                                echo 'Ha ocurrido un error. Por favor, intenta nuevamente.';
                            }
                        ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form class="login-form" id="loginForm" method="POST">
                    <!-- Email Field -->
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope"></i>
                            Correo
                        </label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="correo@empresa.com"
                        >
                    </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i>
                            Contraseña
                        </label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            >
                            <button type="button" class="password-toggle" id="passwordToggle">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-login">
                        <i class="fas fa-power-off"></i> Iniciar Sesión
                    </button>
                </form>

                <!-- Forgot Password Link -->
                <a href="forgot_password" class="forgot-password">
                    &iquest;Olvidaste tu contrase&ntilde;a?
                </a>

                <!-- Legal Copyright -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: var(--buzz-text-muted); font-size: 0.68rem; line-height: 1.6;">
                    <img src="assets/img/recursosinv/Logo_Uptjaa.svg" alt="UPTJAA" style="width: 36px; opacity: 0.8; display: block; margin: 0 auto 0.5rem;">
                    <p style="margin: 0;">&copy; 2025-2026 <strong style="color: var(--buzz-text-color);">Gisvel Aray y Yeicson Acosta</strong></p>
                    <p style="margin: 0;">Licencia: <strong style="color: var(--buzz-text-color);">CC BY-NC-SA 4.0</strong></p>
                    <p style="margin: 0; font-size: 0.6rem; opacity: 0.6; margin-top: 2px;">Proyecto académico demostrativo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?= cspNonce() ?>">
        // Hexagon canvas animation
        const canvas = document.getElementById('hexagonCanvas');
        const ctx = canvas.getContext('2d');
        let hexagons = [];

        function resizeCanvas() {
            // Ajustar el canvas al panel izquierdo si existe, de lo contrario a la ventana
            const panel = document.querySelector('.login-brand-panel');
            if (panel && window.innerWidth > 900) {
                canvas.width = panel.offsetWidth;
                canvas.height = panel.offsetHeight;
                canvas.style.position = 'absolute';
                canvas.style.zIndex = '1';
                panel.insertBefore(canvas, panel.firstChild);
            } else {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                canvas.style.position = 'fixed';
                document.body.insertBefore(canvas, document.body.firstChild);
            }
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
                    if (i === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
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

        // Password toggle
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const toggleIcon = document.getElementById('toggleIcon');

        if(passwordToggle){
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            });
        }

        // Login form submission
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.querySelector('.btn-login');

        if (loginForm && loginButton) {
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Disable button and show loading state
                loginButton.disabled = true;
                const originalText = loginButton.innerHTML;
                loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

                // Hide any previous alert
                const existingAlert = document.querySelector('.alert-login');
                if(existingAlert) existingAlert.style.display = 'none';

                const formData = new FormData(loginForm);

                try {
                    const response = await fetch('php/api_auth.php?action=login', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.requires_2fa) {
                             // Show 2FA Modal
                             const modal = document.getElementById('twoFactorModal');
                             modal.style.display = 'flex';
                             setTimeout(() => document.getElementById('twoFactorCode').focus(), 100);

                             // Restore login button
                             loginButton.disabled = false;
                             loginButton.innerHTML = originalText;
                        } else {
                            // Success - redirect to dashboard
                            loginButton.innerHTML = '<i class="fas fa-check"></i> ¡Éxito!';
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 300);
                        }
                    } else {
                        // Error - show message and re-enable button
                        showAlert(data.message);
                        // Clear password field
                        document.getElementById('password').value = '';
                        loginButton.disabled = false;
                        loginButton.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Ha ocurrido un error de conexión. Por favor, intenta nuevamente.');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalText;
                }

                return false;
            });

            // Helper to show alert
            function showAlert(msg) {
                let alertDiv = document.querySelector('.alert-login');
                if (!alertDiv) {
                    alertDiv = document.createElement('div');
                    alertDiv.className = 'alert-login';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i><span></span>';
                    const headerDiv = document.querySelector('.login-header');
                    headerDiv.after(alertDiv);
                } else {
                    alertDiv.style.display = 'flex';
                }
                alertDiv.querySelector('span').textContent = msg;
            }
        }

        // 2FA Handling
        function close2FAModal() {
            document.getElementById('twoFactorModal').style.display = 'none';
            document.getElementById('twoFactorCode').value = '';
            document.getElementById('twoFactorError').style.display = 'none';
        }

        // Initialize 2FA listeners when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const verifyBtn = document.getElementById('verify2FABtn');
            if(verifyBtn) {
                verifyBtn.addEventListener('click', verify2FA);
            }

            // Allow Enter key in 2FA input
            const codeInput = document.getElementById('twoFactorCode');
            if(codeInput) {
                codeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') verify2FA();
                });

                // Auto-submit on 6 digits
                codeInput.addEventListener('input', function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if(this.value.length === 6) verify2FA();
                });
            }
        });

        async function verify2FA() {
            const codeInput = document.getElementById('twoFactorCode');
            const btn = document.getElementById('verify2FABtn');
            const errorDiv = document.getElementById('twoFactorError');
            const code = codeInput.value.trim();

            if (code.length !== 6) {
                errorDiv.textContent = 'Ingresa un código válido de 6 dígitos';
                errorDiv.style.display = 'block';
                return;
            }

            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            errorDiv.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('code', code);

                const response = await fetch('php/api_auth.php?action=verify_2fa', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Success! Redirect
                    window.location.href = data.redirect || 'dashboard';
                } else {
                    errorDiv.textContent = data.message || 'Código incorrecto';
                    errorDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    codeInput.value = '';
                    codeInput.focus();
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión';
                errorDiv.style.display = 'block';
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>

    <!-- 2FA Modal HTML -->
    <div id="twoFactorModal" class="login-2fa-overlay" style="display: none;">
        <div class="login-2fa-content" style="max-width: 400px; width: 90%;">
            <div class="login-2fa-header">
                <h5 class="font-primary text-primary mb-0">
                    <i class="fas fa-shield-alt"></i> Verificación de Dos Pasos
                </h5>
                <button type="button" class="login-2fa-close" onclick="close2FAModal()">&times;</button>
            </div>
            <div class="login-2fa-body text-center">
                <p class="text-muted mb-4">
                    Ingresa el código de 6 dígitos de tu aplicación autenticadora para continuar.
                </p>

                <div class="mb-4">
                    <input type="text" id="twoFactorCode" class="form-control text-center"
                           maxlength="6" pattern="[0-9]{6}" placeholder="000000" autocomplete="one-time-code"
                           style="font-size: 1.5rem; letter-spacing: 0.5rem; background: rgba(0,0,0,0.3); color: var(--buzz-primary-color) !important; border-color: var(--buzz-primary-color); height: 60px;">
                </div>

                <div id="twoFactorError" class="alert alert-danger py-2 mb-3" style="display: none; font-size: 0.9rem;"></div>

                <button type="button" class="btn btn-buzz w-100" id="verify2FABtn">
                    <i class="fas fa-check"></i> Verificar
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Modal Styles */
        .modal-buzz-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(5px);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .modal-buzz {
            background: rgba(16, 20, 26, 0.95);
            border: 1px solid rgba(255, 160, 0, 0.2);
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: slideUp 0.3s ease;
            backdrop-filter: blur(15px);
        }

        .modal-header-buzz {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 160, 0, 0.05);
        }

        .modal-body-buzz {
            padding: 2rem;
        }

        .close-buzz {
            background: none;
            border: none;
            color: var(--buzz-text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
        }

        .close-buzz:hover {
            color: #fff;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
    <style>
        /* Scoped 2FA Styles */
        .login-2fa-overlay {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9) !important;
            backdrop-filter: blur(5px);
            z-index: 10000 !important;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        .login-2fa-content {
            background: rgba(16, 20, 26, 0.95);
            border: 1px solid rgba(255, 160, 0, 0.3);
            border-radius: 16px;
            box-shadow: 0 0 50px rgba(255, 160, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.3s ease;
            max-width: 400px;
            width: 90%;
            position: relative;
            display: block !important;
            backdrop-filter: blur(15px);
        }

        .login-2fa-header {
            padding: 1.2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 160, 0, 0.05);
        }

        .login-2fa-body {
            padding: 2rem;
        }

        .login-2fa-close {
            background: none;
            border: none;
            color: var(--buzz-text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.2s;
            line-height: 1;
            padding: 0;
            margin: 0;
        }

        .login-2fa-close:hover {
            color: #fff;
        }

        #twoFactorCode {
            background: rgba(0, 0, 0, 0.3) !important;
            color: var(--buzz-primary-color) !important;
            border-color: var(--buzz-primary-color) !important;
        }
    </style>
</body>
</html>
