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
        .login-container {
            min-height: 100vh;
            background-color: var(--buzz-dark-bg);
        }

        .login-form-section {
            background-color: var(--buzz-card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-image-section {
            background: linear-gradient(45deg, var(--buzz-dark-bg) 0%, #1a1f28 50%, var(--buzz-dark-bg) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .login-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image:
                radial-gradient(circle at 25% 25%, var(--buzz-primary-color) 2px, transparent 2px),
                radial-gradient(circle at 75% 75%, var(--buzz-primary-color) 2px, transparent 2px);
            background-size: 50px 50px;
        }

        .login-logo {
            font-family: var(--font-primary);
            font-size: 3rem;
            font-weight: 900;
            color: var(--buzz-primary-color);
            text-align: center;
            margin-bottom: 2rem;
            text-shadow: 0 0 20px rgba(255, 160, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .login-logo img {
            width: 300px;
            margin-bottom: 0.2rem;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: var(--buzz-text-color);
            font-family: var(--font-monospace);
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: block;
        }

        .login-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .alert-buzz {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--buzz-danger);
            color: var(--buzz-danger);
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid login-container">
        <div class="row h-100">
            <!--                     -->
            <!-- Formulario de Login -->
            <!--                     -->
            <div class="col-lg-6 login-form-section">
                <div class="login-form">
                    <div class="login-logo">
                        <img src="assets/img/logov1.png" alt="BUZZ Logo">
                        <!-- <span>BUZZ</span> -->
                    </div>
                    <!--<h2 class="text-center mb-4 font-mono text-muted">DATA MANAGER</h2>-->

                    <!-- Contenedor para la alerta de login -->
                    <div id="loginAlert" class="alert-buzz" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="loginAlertText"></span>
                    </div>

                    <form id="loginForm" method="POST" action="">
                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control form-control-buzz" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Contraseña
                            </label>
                            <div style="position: relative;">
                                <input type="password" class="form-control form-control-buzz" id="password" name="password" required style="padding-right: 45px;">
                                <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--buzz-text-muted); cursor: pointer; padding: 5px; transition: color 0.3s ease;" aria-label="Toggle password visibility">
                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-buzz login-btn">
                            <i class="fas fa-sign-in-alt"></i> Acceder al Sistema
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="forgot_password.php" class="text-primary" style="text-decoration: none; font-family: var(--font-monospace);">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                </div>
            </div>

            <!--                     -->
            <!-- Imagen patron       -->
            <!--                     -->
            <div class="col-lg-6 login-image-section d-none d-lg-flex">
                <div class="login-pattern"></div>
                <div class="text-center">
                    <h1 class="font-primary" style="font-size: 4rem; color: var(--buzz-primary-color); opacity: 0.8;">
                        SISTEMA DE<br>GESTIÓN
                    </h1>
                    <p class="font-mono text-muted" style="font-size: 1.2rem; margin-top: 2rem;">
                        Control total de inventario y pedidos
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/login.js"></script>
    <script>
        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('togglePasswordIcon');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            if (type === 'text') {
                togglePasswordIcon.classList.remove('fa-eye');
                togglePasswordIcon.classList.add('fa-eye-slash');
            } else {
                togglePasswordIcon.classList.remove('fa-eye-slash');
                togglePasswordIcon.classList.add('fa-eye');
            }
        });

        // Change icon color on hover
        togglePassword.addEventListener('mouseenter', function() {
            this.style.color = 'var(--buzz-primary-color)';
        });
        togglePassword.addEventListener('mouseleave', function() {
            this.style.color = 'var(--buzz-text-muted)';
        });
    </script>
</body>
</html>
