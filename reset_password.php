<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - BUZZ Data Manager</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .reset-container {
            min-height: 100vh;
            background-color: var(--buzz-dark-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .reset-card {
            background-color: var(--buzz-card-bg);
            border-radius: 10px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .reset-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .reset-logo img {
            width: 250px;
            margin-bottom: 1rem;
        }

        .reset-title {
            font-family: var(--font-primary);
            color: var(--buzz-primary-color);
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .reset-description {
            color: var(--buzz-text-color);
            font-family: var(--font-monospace);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
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

        .password-input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--buzz-text-muted);
            cursor: pointer;
            padding: 0.5rem;
        }

        .password-toggle:hover {
            color: var(--buzz-primary-color);
        }

        .password-strength {
            margin-top: 0.5rem;
            font-family: var(--font-monospace);
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: var(--buzz-card-bg);
            margin-top: 0.25rem;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }

        .strength-weak {
            background: var(--buzz-danger);
            width: 33%;
        }

        .strength-medium {
            background: #ffc107;
            width: 66%;
        }

        .strength-strong {
            background: var(--buzz-success);
            width: 100%;
        }

        .reset-btn {
            width: 100%;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .alert-buzz {
            padding: 0.75rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            border: 1px solid var(--buzz-success);
            color: var(--buzz-success);
        }

        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid var(--buzz-danger);
            color: var(--buzz-danger);
        }

        .alert-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border: 1px solid #ffc107;
            color: #ffc107;
        }

        .back-to-login {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-to-login a {
            color: var(--buzz-primary-color);
            text-decoration: none;
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .password-requirements {
            background: rgba(255, 160, 0, 0.05);
            border-left: 3px solid var(--buzz-primary-color);
            padding: 1rem;
            margin: 1rem 0;
            font-family: var(--font-monospace);
            font-size: 0.85rem;
            color: var(--buzz-text-color);
        }

        .password-requirements ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }

        .password-requirements li {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-logo">
                <img src="assets/img/logov1.png" alt="BUZZ Logo">
            </div>

            <h1 class="reset-title">
                <i class="fas fa-lock-open"></i> Restablecer Contraseña
            </h1>

            <p class="reset-description">
                Ingresa tu nueva contraseña para acceder al sistema.
            </p>

            <!-- Contenedor para alertas -->
            <div id="alertContainer" style="display: none;"></div>

            <div id="formContainer">
                <form id="resetPasswordForm">
                    <input type="hidden" id="token" name="token" value="">

                    <div class="password-requirements">
                        <strong>Requisitos de la contraseña:</strong>
                        <ul>
                            <li>Mínimo 8 caracteres</li>
                            <li>Al menos una letra mayúscula</li>
                            <li>Al menos una letra minúscula</li>
                            <li>Al menos un número</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <i class="fas fa-key"></i> Nueva Contraseña
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                type="password"
                                class="form-control form-control-buzz"
                                id="password"
                                name="password"
                                placeholder="Ingresa tu nueva contraseña"
                                required
                            >
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <span id="strengthText"></span>
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">
                            <i class="fas fa-check-circle"></i> Confirmar Contraseña
                        </label>
                        <div class="password-input-wrapper">
                            <input
                                type="password"
                                class="form-control form-control-buzz"
                                id="confirmPassword"
                                name="confirmPassword"
                                placeholder="Confirma tu nueva contraseña"
                                required
                            >
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-buzz reset-btn">
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>

            <div class="back-to-login">
                <a href="login.php">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Obtener token de la URL
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            if (!token) {
                showAlert('Enlace inválido. Por favor, solicita un nuevo enlace de recuperación.', 'error');
                document.getElementById('formContainer').style.display = 'none';
                return;
            }

            // Establecer el token en el campo oculto
            document.getElementById('token').value = token;

            // Validar el token
            validateToken(token);

            // Toggle password visibility
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');

            togglePassword.addEventListener('click', () => {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                togglePassword.querySelector('i').classList.toggle('fa-eye');
                togglePassword.querySelector('i').classList.toggle('fa-eye-slash');
            });

            toggleConfirmPassword.addEventListener('click', () => {
                const type = confirmPasswordInput.type === 'password' ? 'text' : 'password';
                confirmPasswordInput.type = type;
                toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye');
                toggleConfirmPassword.querySelector('i').classList.toggle('fa-eye-slash');
            });

            // Password strength indicator
            passwordInput.addEventListener('input', () => {
                const password = passwordInput.value;
                const strength = checkPasswordStrength(password);

                const strengthIndicator = document.getElementById('passwordStrength');
                const strengthText = document.getElementById('strengthText');
                const strengthBar = document.getElementById('strengthBar');

                if (password.length > 0) {
                    strengthIndicator.style.display = 'block';
                    strengthText.textContent = `Seguridad: ${strength.text}`;
                    strengthBar.className = `strength-bar-fill strength-${strength.level}`;
                } else {
                    strengthIndicator.style.display = 'none';
                }
            });

            // Form submission
            const form = document.getElementById('resetPasswordForm');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                // Validaciones
                if (password.length < 8) {
                    showAlert('La contraseña debe tener al menos 8 caracteres', 'error');
                    return;
                }

                if (!/[A-Z]/.test(password)) {
                    showAlert('La contraseña debe contener al menos una letra mayúscula', 'error');
                    return;
                }

                if (!/[a-z]/.test(password)) {
                    showAlert('La contraseña debe contener al menos una letra minúscula', 'error');
                    return;
                }

                if (!/[0-9]/.test(password)) {
                    showAlert('La contraseña debe contener al menos un número', 'error');
                    return;
                }

                if (password !== confirmPassword) {
                    showAlert('Las contraseñas no coinciden', 'error');
                    return;
                }

                // Deshabilitar botón y mostrar loading
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('php/api_auth.php?action=reset_password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            token: token,
                            password: password,
                            confirmPassword: confirmPassword
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert(data.message, 'success');
                        form.reset();

                        // Redirigir al login después de 3 segundos
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 3000);
                    } else {
                        showAlert(data.message || 'Error al restablecer la contraseña', 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            async function validateToken(token) {
                try {
                    const response = await fetch('php/validate_token.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ token })
                    });

                    const data = await response.json();

                    if (!data.valid) {
                        showAlert(data.message || 'El enlace ha expirado o es inválido.', 'error');
                        document.getElementById('formContainer').style.display = 'none';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Error al validar el enlace.', 'error');
                    document.getElementById('formContainer').style.display = 'none';
                }
            }

            function checkPasswordStrength(password) {
                let strength = 0;

                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password)) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;

                if (strength <= 2) {
                    return { level: 'weak', text: 'Débil' };
                } else if (strength <= 4) {
                    return { level: 'medium', text: 'Media' };
                } else {
                    return { level: 'strong', text: 'Fuerte' };
                }
            }

            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-error');
                const icon = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle');

                const alertContainer = document.getElementById('alertContainer');
                alertContainer.innerHTML = `
                    <div class="alert-buzz ${alertClass}">
                        <i class="fas ${icon}"></i> ${message}
                    </div>
                `;
                alertContainer.style.display = 'block';

                // Auto-hide después de 8 segundos (excepto para success que redirige)
                if (type !== 'success') {
                    setTimeout(() => {
                        alertContainer.style.display = 'none';
                    }, 8000);
                }
            }
        });
    </script>
</body>
</html>

