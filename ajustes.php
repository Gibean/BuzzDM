<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Ajustes</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <style>
        /* System Info Styles (migrated from about.php) */
        .sys-info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sys-info-row:last-child {
            border-bottom: none;
        }

        .sys-info-row .label {
            color: var(--buzz-text-muted);
            font-size: 0.85rem;
        }

        .sys-info-row .value {
            font-family: var(--font-monospace);
            font-size: 0.85rem;
            color: var(--buzz-text-color);
            text-align: right;
        }

        /* Stat Cards */
        .stat-card {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--buzz-border-color);
            border-radius: 6px;
            padding: 1rem;
            text-align: center;
        }

        .stat-card .stat-label {
            font-family: var(--font-primary);
            font-size: 0.7rem;
            color: var(--buzz-primary-color);
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-value {
            font-family: var(--font-monospace);
            font-size: 1.1rem;
            color: var(--buzz-text-color);
        }

        .stat-card .stat-sub {
            font-size: 0.75rem;
            color: var(--buzz-text-muted);
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <?php
        $nav_title = 'AJUSTES DEL SISTEMA';
        $nav_subtitle = 'CONFIGURACIÓN';
        require_once 'php/top_navbar.php';
        ?>

        <div class="row">
            <!-- Security Settings Column -->
            <div class="col-lg-7 mb-4">
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">
                        <i class="fas fa-shield-alt"></i> SEGURIDAD DE CUENTA
                    </h5>

                    <!-- 2FA Section -->
                    <div class="security-item mb-4 pb-3 border-bottom border-secondary">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1 text-light">Autenticación de 2 Factores (2FA)</h6>
                                <small class="text-muted">Añade una capa extra de seguridad usando Google Authenticator</small>
                            </div>
                            <span id="twoFactorStatus" class="badge bg-secondary">Cargando...</span>
                        </div>

                        <!-- 2FA Disabled - Show button to start setup -->
                        <div id="twoFactorDisabledArea" style="display: none;">
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle"></i> La verificación de dos pasos no está activada.
                                Al activarla, necesitarás un código de tu app autenticadora además de tu contraseña.
                            </p>
                            <button class="btn btn-outline-buzz" id="start2FASetupBtn" type="button">
                                <i class="fas fa-qrcode me-2"></i>Configurar Autenticador
                            </button>
                        </div>

                        <!-- 2FA Setup Area (shown only when configuring) -->
                        <div id="twoFactorSetupArea" style="display: none;">
                            <div class="bg-dark p-4 rounded border border-secondary mb-4">
                                <h5 class="mb-4 text-warning">Configuración de Verificación de Dos Pasos</h5>

                                <!-- Step 1 -->
                                <div class="mb-4">
                                    <h6 class="text-light mb-2"><span class="badge bg-primary me-2">Paso 1</span> Descarga la aplicación</h6>
                                    <p class="text-muted small ms-4">
                                        Descarga e instala <strong>Google Authenticator</strong> o cualquier aplicación similar (Authy, Microsoft Authenticator) en su dispositivo móvil.
                                    </p>
                                </div>

                                <!-- Step 2 -->
                                <div class="mb-4">
                                    <h6 class="text-light mb-2"><span class="badge bg-primary me-2">Paso 2</span> Escanea el código QR</h6>
                                    <div class="ms-4">
                                        <p class="text-muted small mb-2">Abre la aplicación y escanea el siguiente código:</p>
                                        <div id="twoFactorQR" class="bg-white p-3 rounded d-inline-block mb-2 text-center" style="min-width: 200px; min-height: 200px;">
                                            <i class="fas fa-spinner fa-spin fa-2x text-muted mt-5"></i>
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-link btn-sm text-decoration-none p-0" id="showManualCode">
                                                <i class="fas fa-keyboard me-1"></i> No puedo escanear el código
                                            </button>
                                            <div id="manualCodeContainer" class="d-none mt-2">
                                                <p class="small text-muted mb-1">Ingresa este código manualmente en la app:</p>
                                                <code id="twoFactorSecretCode" class="d-block bg-black p-2 rounded text-warning text-center fw-bold letter-spacing-2"></code>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3 -->
                                <div class="mb-4">
                                    <h6 class="text-light mb-2"><span class="badge bg-primary me-2">Paso 3</span> Verifica el código</h6>
                                    <div class="ms-4" style="max-width: 300px;">
                                        <p class="text-muted small mb-2">Ingresa el código de 6 dígitos que muestra tu aplicación:</p>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text bg-dark border-secondary text-secondary"><i class="fas fa-shield-alt"></i></span>
                                            <input type="text" id="twoFactorVerifyCode" class="form-control form-control-buzz text-center fs-4 letter-spacing-2"
                                                   maxlength="6" pattern="[0-9]{6}" placeholder="000000" autocomplete="off">
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success" id="enable2FABtn" type="button">
                                                <i class="fas fa-check me-2"></i>Verificar y Activar
                                            </button>
                                            <button class="btn btn-outline-secondary" id="cancel2FASetupBtn" type="button">
                                                Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2FA Enabled Area -->
                        <div id="twoFactorEnabledArea" style="display: none;">
                            <div class="d-flex align-items-center mb-0 p-3 rounded" style="background: rgba(25, 135, 84, 0.15); border: 1px solid rgba(25, 135, 84, 0.4);">
                                <div class="me-3 text-success">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 text-success">2FA Activado</h6>
                                    <p class="mb-0 small text-muted">Tu cuenta está protegida con verificación de dos pasos.</p>
                                </div>
                                <button class="btn btn-danger btn-sm" id="disable2FABtn">
                                    <i class="fas fa-trash-alt me-1"></i> Desactivar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Session Timeout -->
                    <div class="security-item mb-4 pb-3 border-bottom border-secondary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-light">Timeout de Sesión</h6>
                                <small class="text-muted">Cierre automático por inactividad</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                            </div>
                        </div>
                        <div id="sessionTimeoutConfig" class="mt-3">
                            <label class="small text-muted">Minutos de inactividad:</label>
                            <select class="form-select form-control-buzz form-select-sm w-auto" id="sessionTimeoutMinutes">
                                <option value="15">15 minutos</option>
                                <option value="30" selected>30 minutos</option>
                                <option value="60">1 hora</option>
                                <option value="120">2 horas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Login Logs Info -->
                    <div class="security-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-light">Logs de Inicio de Sesión</h6>
                                <small class="text-muted">Registro de accesos al sistema</small>
                            </div>
                            <span class="badge bg-success"><i class="fas fa-check"></i> Siempre activo</span>
                        </div>
                        <p class="small text-muted mt-2 mb-0">
                            <i class="fas fa-info-circle"></i>
                            Los logs de sesión se registran automáticamente por seguridad.
                            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                            Puedes verlos en la sección de <a href="admin" class="text-primary">Administración</a> → Logs.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- System Info Column -->
            <div class="col-lg-5 mb-4">
                <!-- Server Information -->
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">
                        <i class="fas fa-server"></i> INFORMACIÓN DEL SERVIDOR
                    </h5>
                    <div class="sys-info-row">
                        <span class="label">Servidor</span>
                        <span class="value"><?php echo php_uname('n'); ?></span>
                    </div>
                    <div class="sys-info-row">
                        <span class="label">Sistema Operativo</span>
                        <span class="value"><?php echo PHP_OS; ?></span>
                    </div>
                    <div class="sys-info-row">
                        <span class="label">Memoria PHP (Peak)</span>
                        <span class="value"><?php echo round(memory_get_peak_usage() / (1024 * 1024), 2) . ' MB'; ?></span>
                    </div>
                    <div class="sys-info-row">
                        <span class="label">Espacio Libre</span>
                        <span class="value"><?php echo round(disk_free_space('/') / (1024 * 1024 * 1024), 2) . ' GB'; ?></span>
                    </div>
                    <div class="sys-info-row">
                        <span class="label">Espacio Total</span>
                        <span class="value"><?php echo round(disk_total_space('/') / (1024 * 1024 * 1024), 2) . ' GB'; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/ajustes.js"></script>
</body>

</html>
