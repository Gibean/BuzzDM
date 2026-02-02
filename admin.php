    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Administración</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css?v=3">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
</head>
<body>
     <!-- Sidebar  -->
    <?php require_once 'php/sidebar.php'; ?>

     <!-- Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">ADMINISTRACIÓN DEL SISTEMA</h1>
                <span class="text-muted ms-2 font-mono">/ CONFIGURACIÓN</span>
            </div>
        </nav>

         <!-- Admin Tabs - Material Design Style -->
        <div class="admin-tabs-container">
            <div class="admin-tabs" id="adminTabs" role="tablist">
                <button class="admin-tab active" data-tab="users">
                    <i class="fas fa-users"></i>
                    <span>Usuarios</span>
                </button>
                <button class="admin-tab" data-tab="logs">
                    <i class="fas fa-list-alt"></i>
                    <span>Logs</span>
                </button>
                <button class="admin-tab" data-tab="settings">
                    <i class="fas fa-cog"></i>
                    <span>Config</span>
                </button>
                <button class="admin-tab" data-tab="reports">
                    <i class="fas fa-file-alt"></i>
                    <span>Reportes</span>
                </button>
                <button class="admin-tab" data-tab="backups">
                    <i class="fas fa-database"></i>
                    <span>Backups</span>
                </button>
            </div>
        </div>

         <!-- Users Tab  -->
        <div class="tab-content" id="usersTab">
            <!-- User Management Table - Full Width -->
            <div class="buzz-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-primary text-primary mb-0">GESTIÓN DE USUARIOS</h5>
                    <button class="btn btn-buzz" id="newUserBtn">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                             <!-- Users will be populated by JavaScript  -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Statistics - Below Table -->
            <div class="buzz-card">
                <h6 class="font-primary text-primary mb-3">ESTADÍSTICAS DE USUARIOS</h6>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Total Usuarios:</span>
                            <span class="font-mono text-primary" id="totalUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Usuarios Activos:</span>
                            <span class="font-mono text-success" id="activeUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Administradores:</span>
                            <span class="font-mono text-warning" id="adminUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Sesiones Hoy:</span>
                            <span class="font-mono text-primary" id="todaySessions">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!--   Logs Tab  -->
        <div class="tab-content" id="logsTab" style="display: none;">
            <div class="buzz-card">
                <h5 class="font-primary text-primary mb-3">LOGS DE SESIÓN</h5>
                <div class="filter-wrapper">
                    <!-- Basic Filters (Always Visible) -->
                    <div class="filter-group filter-search" style="flex-grow: 1;">
                        <label class="text-muted">Buscar</label>
                        <input type="text" class="form-control form-control-buzz" id="logSearch" placeholder="Buscar por usuario...">
                    </div>

                    <div class="filter-group filter-actions">
                         <button class="btn btn-outline-buzz btn-filter-action" onclick="filterLogs()" title="Buscar">
                            <i class="fas fa-search"></i>
                            <span class="btn-text">Filtrar</span>
                        </button>
                        <button class="btn btn-outline-buzz btn-filter-action" id="toggleAdvancedFilters" title="Más filtros">
                            <i class="fas fa-filter"></i>
                            <span class="btn-text">Filtros Avanzados</span>
                            <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                        </button>
                        <button class="btn btn-outline-buzz btn-filter-action" onclick="clearLogsFilters()" title="Limpiar">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>

                 <!-- Advanced Filters (Collapsible) -->
                 <div id="advancedFiltersCollapse" class="advanced-filters-section mt-3 d-none">
                    <hr class="border-secondary opacity-25">
                    <div class="filter-wrapper">
                        <div class="filter-group filter-date">
                            <label class="text-muted">Desde</label>
                            <input type="date" class="form-control form-control-buzz" id="logDateFrom">
                        </div>
                        <div class="filter-group filter-date">
                            <label class="text-muted">Hasta</label>
                            <input type="date" class="form-control form-control-buzz" id="logDateTo">
                        </div>
                        <div class="filter-group filter-select">
                            <label class="text-muted">Tipo de Acción</label>
                            <select class="form-control form-control-buzz" id="logActionFilter">
                                <option value="">Todas</option>
                                <option value="exitoso">Login Exitoso</option>
                                <option value="denegado">Login Denegado</option>
                                <option value="backup">Backup</option>
                                <option value="importacion">Importación</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody id="logsTable">
                             <!-- Logs will be populated by JavaScript  -->
                        </tbody>
                    </table>
                </div>
                <!-- Unified Pagination -->
                <div id="logsPagination" class="buzz-pagination"></div>
            </div>
        </div>


    <!--   Reports Tab  -->
        <div class="tab-content" id="reportsTab" style="display: none;">
            <div class="buzz-card">
                <h5 class="font-primary text-primary mb-3">OBTENER REPORTES DE SISTEMA</h5>
                <p class="text-muted mb-4">Para generar un reporte, seleccione el tipo y luego ingrese su contraseña de administrador.</p>

                <div class="row">
                    <!-- Report Selection -->
                    <div class="col-md-8">
                        <h6 class="font-primary text-primary mb-3">1. SELECCIONE EL TIPO DE REPORTE</h6>
                        <div class="row g-3" id="reportOptionsContainer">
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_importaciones">
                                    <i class="fas fa-file-import fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Importaciones</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_login">
                                    <i class="fas fa-sign-in-alt fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Inicios de Sesión</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="pedidos">
                                    <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Pedidos</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="clientes">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Clientes</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="inventario">
                                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Inventario</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_acciones">
                                    <i class="fas fa-history fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Acciones Sistema</p>
                                </div>
                            </div>
                            <!-- New Reports -->
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option border-warning" data-report-type="inventario_critico">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                    <p class="mb-0 text-muted small">Inv. Crítico</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option border-success" data-report-type="resumen_financiero">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-success mb-2"></i>
                                    <p class="mb-0 text-muted small">Resumen Financiero</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Password and Generate Button -->
                    <div class="col-md-4">
                        <h6 class="font-primary text-primary mb-3">2. CONFIRMAR Y GENERAR</h6>
                        <div class="buzz-card p-3">
                            <form id="reportGenerationForm">
                                <input type="text" name="username" autocomplete="username" style="display:none;" aria-hidden="true">
                                <div class="mb-3">
                                    <label for="adminPasswordReport" class="form-label text-muted">Contraseña de Administrador</label>
                                    <div style="position: relative;">
                                        <input type="password" class="form-control form-control-buzz" id="adminPasswordReport" placeholder="Ingrese su contraseña" required autocomplete="current-password" style="padding-right: 45px;">
                                        <button type="button" id="toggleAdminPasswordReport" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--buzz-text-muted); cursor: pointer; padding: 5px; transition: color 0.3s ease;" aria-label="Toggle password visibility">
                                            <i class="fas fa-eye" id="toggleAdminPasswordReportIcon"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-buzz w-100" id="generateReportBtn" disabled>
                                    <i class="fas fa-download"></i> Generar Reporte
                                </button>
                                <p id="reportError" class="text-danger small mt-2"></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!--   Settings Tab  -->
        <div class="tab-content" id="settingsTab" style="display: none;">
            <div class="row">
                <!-- Company Information Form -->
                <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                    <div class="buzz-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-primary text-primary mb-0">DATOS DE LA EMPRESA</h5>
                            <i class="fas fa-building text-primary"></i>
                        </div>
                        <form id="companyInfoForm">
                            <?= csrfField() ?>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Tipo RIF</label>
                                    <select class="form-select form-control-buzz" id="rifType" name="rifType">
                                        <option value="J">J - Jurídico</option>
                                        <option value="V">V - Venezolano</option>
                                        <option value="G">G - Gubernamental</option>
                                        <option value="E">E - Extranjero</option>
                                        <option value="P">P - Pasaporte</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted small">Número de RIF / Cédula</label>
                                    <input type="text" class="form-control form-control-buzz" id="rifNumber" name="rifNumber" placeholder="Ej: 123456780">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Nombre de la Empresa</label>
                                    <input type="text" class="form-control form-control-buzz" id="companyName" name="companyName" placeholder="Nombre Oficial">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Dirección Fiscal / Principal</label>
                                    <textarea class="form-control form-control-buzz" id="companyAddress" name="companyAddress" rows="2" placeholder="Dirección completa..."></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Cód. Área / País</label>
                                    <input type="text" class="form-control form-control-buzz" id="phoneCode" name="phoneCode" placeholder="+58">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted small">Teléfono</label>
                                    <input type="text" class="form-control form-control-buzz" id="phoneNumber" name="phoneNumber" placeholder="4121234567">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Email de Contacto</label>
                                    <input type="email" class="form-control form-control-buzz" id="companyEmail" name="companyEmail" placeholder="admin@empresa.com">
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-buzz btn-lg w-100">
                                    <i class="fas fa-save me-2"></i>GUARDAR CAMBIOS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logo and Security Placeholder -->
                <div class="col-12 col-lg-5">
                    <!-- Logo Management -->
                    <div class="buzz-card mb-4">
                        <h6 class="font-primary text-primary mb-3">LOGO DE LA EMPRESA (SVG)</h6>
                        <div id="currentLogoPreview" class="text-center mb-3 p-3 rounded" style="background: rgba(0,0,0,0.2); min-height: 100px; display: flex; align-items: center; justify-content: center;">
                            <!-- JS will populate current logo here -->
                            <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                        </div>

                        <div class="logo-upload-box p-3 border border-dashed rounded text-center" id="logoUploadArea" style="border-color: rgba(255,160,0,0.3) !important;">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="small text-muted mb-2">Suelte el archivo SVG aquí o</p>
                            <input type="file" id="logoFileInput" accept=".svg" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-buzz" onclick="document.getElementById('logoFileInput').click()">
                                <i class="fas fa-search me-1"></i>Buscar Archivo
                            </button>
                        </div>

                        <div id="logoFileInfo" class="mt-2 small" style="display: none;">
                            <div class="alert alert-info py-1 px-2 d-flex justify-content-between align-items-center">
                                <span id="logoFileName" class="text-truncate me-2"></span>
                                <button type="button" class="btn btn-sm text-danger p-0" id="clearLogoFileBtn">&times;</button>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100 mt-1" id="uploadLogoBtn">
                                <i class="fas fa-upload me-1"></i>SUBIR LOGO
                            </button>
                        </div>
                    </div>

                    <!-- Security Settings - Functional -->
                    <div class="buzz-card">
                        <h6 class="font-primary text-primary mb-3">
                            <i class="fas fa-shield-alt"></i> SEGURIDAD DE CUENTA
                        </h6>

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
                                <div class="alert alert-success d-flex align-items-center mb-0">
                                    <div class="me-3">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading mb-1">2FA Activado</h5>
                                        <p class="mb-0 small">Tu cuenta está protegida con verificación de dos pasos.</p>
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
                                Puedes verlos en la pestaña "Logs de Sesión".
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!--   Backups Tab  -->
        <div class="tab-content" id="backupsTab" style="display: none;">
            <!-- Auto-Backup Status Card - Responsive -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="buzz-card">
                        <div class="row align-items-center">
                            <!-- Status Indicator -->
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="status-dot-large" id="statusDot"></div>
                                    <div>
                                        <span class="font-primary" id="statusLabel">VERIFICANDO...</span>
                                        <p class="text-muted mb-0 small" id="statusMessage">Cargando estado...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats - Responsive Grid -->
                            <div class="col-12 col-md-5">
                                <div class="row text-center text-md-start">
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Último Auto</span>
                                        <span class="font-mono small" id="lastAutoBackup">--</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Próximo</span>
                                        <span class="font-mono small" id="nextAutoBackup">--</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Total</span>
                                        <span class="font-mono text-primary" id="totalAutoBackups">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Toggle Switch -->
                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2">
                                    <span class="text-muted small">Auto-Backup</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="autoBackupToggle" checked>
                                        <label class="form-check-label visually-hidden" for="autoBackupToggle">
                                            Activar backup automático
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Backups Table -->
                <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                    <div class="buzz-card">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                            <h5 class="font-primary text-primary mb-0">HISTORIAL DE BACKUPS</h5>
                            <button class="btn btn-buzz btn-sm" onclick="createBackup()">
                                <i class="fas fa-plus"></i> Nuevo Backup
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-buzz">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Tamaño</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="backupsTable">
                                     <!-- Backups will be populated by JavaScript  -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Info Panel -->
                <div class="col-lg-4 col-12">
                    <div class="buzz-card">
                        <h6 class="font-primary text-primary mb-3">
                            <i class="fas fa-cog"></i> CONFIGURACIÓN
                        </h6>

                        <!-- Auto Backup Toggle Info -->
                        <div class="mb-3 p-2 rounded" style="background: rgba(255,160,0,0.1);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-robot text-primary"></i>
                                <strong class="small">Sistema Fallback</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Si no hay backups en 24h, el sistema crea uno automáticamente cuando un admin visita la página.
                            </p>
                        </div>

                        <!-- Backup Storage Info -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-folder-open text-primary"></i>
                                <strong class="small">Almacenamiento</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Los backups se guardan por defecto en su carpeta de <code class="small">descargas</code> y se retienen por 30 días máximo.
                            </p>
                        </div>

                        <!-- Warning -->
                        <div class="p-2 rounded" style="background: rgba(255,193,7,0.1);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <strong class="small text-warning">Restaurar</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Sobrescribe todos los datos actuales. Acción irreversible.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

 <!--     User Modal  -->
    <div class="modal-buzz" id="userModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="userModalTitle">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </h5>
                <button class="close-buzz" onclick="closeUserModal()" aria-label="Cerrar">&times;</button>
            </div>
            <form id="userForm">
                <?= csrfField() ?>

                <div class="modal-body-buzz">
                    <!-- Basic Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="userName" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control form-control-buzz" id="userEmail" required autocomplete="email">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="modal-section" id="userPasswordGroup">
                        <div class="modal-section-title">
                            <i class="fas fa-lock"></i> SEGURIDAD
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="password" class="form-control form-control-buzz" id="userPassword" required autocomplete="new-password">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirmar Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                                    <input type="password" class="form-control form-control-buzz" id="userPasswordConfirm" required autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-user-shield"></i> PERMISOS Y ESTADO
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                                    <select class="form-control form-control-buzz" id="userRole">
                                        <option value="user">Usuario</option>
                                        <option value="supervisor">Supervisor</option>
                                        <option value="admin">Administrador</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-toggle-on"></i></span>
                                    <select class="form-control form-control-buzz" id="userStatus">
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="userFormAlert" class="alert-buzz" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="userFormAlertText"></span>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeUserModal()">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save me-2"></i>Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js?v=4"></script>
    <script src="assets/js/admin.js?v=7"></script>
</body>
</html>
