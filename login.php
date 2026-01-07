<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Login</title>
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

        /* Login container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 2rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 3rem;
        }

        .login-logo img {
            max-width: 350px;
            width: 100%;
            height: auto;
            filter: drop-shadow(0 0 15px rgba(255, 160, 0, 0.15));
        }

        /* Form styling */
        .login-form {
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            color: var(--buzz-text-color);
            font-family: var(--font-monospace);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .form-label i {
            color: var(--buzz-primary-color);
            font-size: 0.85rem;
        }

        .form-control {
            background-color: rgba(16, 20, 26, 0.8);
            border: 1px solid rgba(255, 160, 0, 0.2);
            color: var(--buzz-text-color) !important;
            padding: 0.9rem 1rem;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(16, 20, 26, 0.95);
            border-color: var(--buzz-primary-color);
            box-shadow: 0 0 0 3px rgba(255, 160, 0, 0.1);
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
            padding: 1rem;
            background-color: var(--buzz-primary-color);
            color: var(--buzz-dark-bg);
            border: none;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
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
            margin-top: 1.5rem;
            color: var(--buzz-primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #e6900a;
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .login-container {
                padding: 1.5rem 1rem;
            }

            .login-logo {
                margin-bottom: 2rem;
            }

            .login-logo img {
                max-width: 280px;
            }

            .back-home {
                top: 1rem;
                left: 1rem;
            }

            .back-home a span {
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
            <span>Inicio</span>
        </a>
    </div>

    <!-- Login Container -->
    <div class="login-container">
        <!-- Logo -->
        <div class="login-logo">
            <img src="assets/img/logov1.png" alt="BUZZ Logo">
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
                    Email
                </label>
                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    required
                    autocomplete="email"
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
                    >
                    <button type="button" class="password-toggle" id="passwordToggle">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
            </button>
        </form>

        <!-- Forgot Password Link -->
        <a href="forgot_password.php" class="forgot-password">
            ¿Olvidaste tu contraseña?
        </a>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hexagon canvas animation
        const canvas = document.getElementById('hexagonCanvas');
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
                this.opacity = Math.random() * 0.25;
                this.fadeDirection = Math.random() > 0.5 ? 1 : -1;
                this.fadeSpeed = 0.001 + Math.random() * 0.002;
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
                ctx.lineWidth = 1;
                ctx.stroke();
            }

            update() {
                this.opacity += this.fadeSpeed * this.fadeDirection;
                if (this.opacity > 0.25 || this.opacity < 0.05) {
                    this.fadeDirection *= -1;
                }
                this.draw();
            }
        }

        function createHexagons() {
            hexagons = [];
            const size = 25;
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

                const formData = new FormData(loginForm);

                try {
                    const response = await fetch('php/login_guard.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Success - redirect to dashboard
                        loginButton.innerHTML = '<i class="fas fa-check"></i> ¡Éxito!';
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 300);
                    } else {
                        // Error - show message and re-enable button
                        alert(data.message);
                        // Clear fields on incorrect login
                        document.getElementById('email').value = '';
                        document.getElementById('password').value = '';
                        loginButton.disabled = false;
                        loginButton.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ha ocurrido un error al intentar iniciar sesión. Por favor, intenta nuevamente.');
                    loginButton.disabled = false;
                    loginButton.innerHTML = originalText;
                }

                return false;
            });

            // Extra prevention
            loginForm.onsubmit = () => false;
        }
    </script>
</body>
</html>
