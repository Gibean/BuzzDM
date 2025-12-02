<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - BUZZ Data Manager</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .forgot-container {
            min-height: 100vh;
            background-color: var(--buzz-dark-bg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card {
            background-color: var(--buzz-card-bg);
            border-radius: 10px;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .forgot-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .forgot-logo img {
            width: 250px;
            margin-bottom: 1rem;
        }

        .forgot-title {
            font-family: var(--font-primary);
            color: var(--buzz-primary-color);
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
        }

        .forgot-description {
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

        .forgot-btn {
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
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-logo">
                <img src="assets/img/logov1.png" alt="BUZZ Logo">
            </div>

            <h1 class="forgot-title">
                <i class="fas fa-key"></i> Recuperar Contraseña
            </h1>

            <p class="forgot-description">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
            </p>

            <!-- Contenedor para alertas -->
            <div id="alertContainer" style="display: none;"></div>

            <form id="forgotPasswordForm">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input
                        type="email"
                        class="form-control form-control-buzz"
                        id="email"
                        name="email"
                        placeholder="usuario@ejemplo.com"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-buzz forgot-btn">
                    <i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación
                </button>
            </form>

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
            const form = document.getElementById('forgotPasswordForm');
            const alertContainer = document.getElementById('alertContainer');
            const emailInput = document.getElementById('email');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                const email = emailInput.value.trim();

                // Validación básica
                if (!email) {
                    showAlert('Por favor, ingresa tu correo electrónico', 'error');
                    return;
                }

                if (!isValidEmail(email)) {
                    showAlert('Por favor, ingresa un correo electrónico válido', 'error');
                    return;
                }

                // Deshabilitar botón y mostrar loading
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('php/forgot_password_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ email })
                    });

                    const data = await response.json();

                    if (data.success) {
                        showAlert(data.message, 'success');
                        form.reset();

                        // Redirigir al login después de 5 segundos
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 5000);
                    } else {
                        showAlert(data.message || 'Error al procesar la solicitud', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
                } finally {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });

            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

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

            function isValidEmail(email) {
                const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                return emailRegex.test(email);
            }
        });
    </script>
</body>
</html>

