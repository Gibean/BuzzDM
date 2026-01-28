<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Acerca de</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* About Page Specific Styles */
        .about-header {
            text-align: center;
            padding: 1rem 1rem;
            border-bottom: 1px solid var(--buzz-border-color);
            margin-bottom: 1rem;
        }

        .about-header .sidebar-logo {
            max-width: 250px;
            margin: 0 auto 1rem auto;
            display: block;
        }

        .about-header .tagline {
            color: var(--buzz-text-muted);
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }

        /* Manual Button - Prominent */
        .manual-btn-container {
            background: linear-gradient(135deg, rgba(255, 160, 0, 0.15) 0%, rgba(255, 160, 0, 0.05) 100%);
            border: 1px solid rgba(255, 160, 0, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            gap: 10px;
        }

        .manual-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
            padding: 12px 20px;
            background: var(--buzz-primary-color);
            color: var(--buzz-dark-bg);
            border: none;
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .tutorial-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex: 1;
            padding: 12px 20px;
            background: rgba(0, 242, 255, 0.1);
            color: var(--buzz-info-color);
            border: 1px solid var(--buzz-info-color);
            border-radius: 6px;
            font-family: var(--font-monospace);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tutorial-btn:hover {
            background: rgba(0, 242, 255, 0.2);
            box-shadow: 0 0 10px rgba(0, 242, 255, 0.3);
            color: #fff;
        }

        .manual-btn:hover {
            background: #e6900a;
            color: var(--buzz-dark-bg);
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

        /* Tech Stack Accordion */
        .tech-accordion .accordion-item {
            background: transparent;
            border: 1px solid var(--buzz-border-color);
            margin-bottom: 0.5rem;
            border-radius: 6px !important;
        }

        .tech-accordion .accordion-button {
            background: rgba(0, 0, 0, 0.2);
            color: var(--buzz-text-color);
            font-family: var(--font-monospace);
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 6px !important;
        }

        .tech-accordion .accordion-button:not(.collapsed) {
            background: rgba(255, 160, 0, 0.1);
            color: var(--buzz-primary-color);
            box-shadow: none;
        }

        .tech-accordion .accordion-button::after {
            filter: invert(0.7);
        }

        .tech-accordion .accordion-button:focus {
            box-shadow: none;
            border-color: var(--buzz-primary-color);
        }

        .tech-accordion .accordion-body {
            background: var(--buzz-card-bg);
            padding: 1rem;
        }

        .tech-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .tech-item:last-child {
            border-bottom: none;
        }

        .tech-item .tech-name {
            font-family: var(--font-monospace);
            color: var(--buzz-text-color);
        }

        .tech-item .tech-version {
            font-family: var(--font-monospace);
            color: var(--buzz-primary-color);
            font-size: 0.85rem;
        }

        .tech-item .tech-desc {
            font-size: 0.75rem;
            color: var(--buzz-text-muted);
        }

        /* Features Grid */
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 0.5rem 0;
        }

        .feature-item i {
            color: var(--buzz-success);
            margin-top: 3px;
        }

        .feature-item .feature-name {
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }

        .feature-item .feature-desc {
            font-size: 0.75rem;
            color: var(--buzz-text-muted);
        }

        /* System Info */
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

        /* Support Card */
        .support-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem 0;
        }

        .support-item i {
            color: var(--buzz-primary-color);
            width: 20px;
            text-align: center;
        }

        .support-item span {
            font-family: var(--font-monospace);
            font-size: 0.9rem;
        }

        /* License */
        .license-text {
            font-size: 0.8rem;
            color: var(--buzz-text-muted);
            line-height: 1.6;
        }

        .license-text b {
            color: var(--buzz-text-color);
        }

        .developer-logo {
            max-width: 100%;
            height: auto;
            margin-top: 1rem;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">ACERCA DEL SISTEMA</h1>
                <span class="text-muted ms-2 font-mono">/ INFORMACIÓN</span>
            </div>
        </nav>

        <!-- System Info -->
        <div class="row">
            <div class="col-lg-8">
                <!-- Header with Logo -->
                <div class="buzz-card mb-4">
                    <div class="about-header">
                        <img src="assets/img/logov1.png" alt="BUZZ Logo" class="sidebar-logo">
                        <p class="tagline mb-0">Sistema de Gestión de Pedidos e Inventario</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">VERSIÓN</div>
                                <div class="stat-value">v1.3.4.x</div>
                                <div class="stat-sub">Build 2026.01</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">ESTADO</div>
                                <div class="stat-value text-success">OPERATIVO</div>
                                <div class="stat-sub">Sistema estable</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-label">PHP</div>
                                <div class="stat-value"><?php echo phpversion(); ?></div>
                                <div class="stat-sub">Versión del servidor</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">CARACTERÍSTICAS DEL SISTEMA</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Gestión de Inventario</div>
                                    <div class="feature-desc">Control de stock, productos, kits y almacenes múltiples</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Control de Pedidos</div>
                                    <div class="feature-desc">Creación, seguimiento y estados de pedidos</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Seguimiento de Envíos</div>
                                    <div class="feature-desc">Integración con guías y estados de entrega</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Importación Masiva</div>
                                    <div class="feature-desc">Carga de datos desde archivos Excel</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Dashboard Analítico</div>
                                    <div class="feature-desc">Gráficos de ventas, métricas y alertas</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Gestión de Usuarios</div>
                                    <div class="feature-desc">Roles: Admin, Supervisor, Operador</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Autenticación 2FA</div>
                                    <div class="feature-desc">Google Authenticator compatible</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Backups Automáticos</div>
                                    <div class="feature-desc">Sistema fallback + cron job</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Recuperación de Contraseña</div>
                                    <div class="feature-desc">Reset vía correo electrónico</div>
                                </div>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="feature-name">Notas Kanban</div>
                                    <div class="feature-desc">Tablero de tareas asignable a usuarios</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technologies -->
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">TECNOLOGÍAS UTILIZADAS</h5>
                    <div class="accordion tech-accordion" id="techAccordion">
                        <!-- Frontend -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#frontendCollapse">
                                    <i class="fas fa-desktop me-2"></i> Frontend
                                </button>
                            </h2>
                            <div id="frontendCollapse" class="accordion-collapse collapse show" data-bs-parent="#techAccordion">
                                <div class="accordion-body">
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Bootstrap</div>
                                            <div class="tech-desc">Framework CSS responsive</div>
                                        </div>
                                        <div class="tech-version">5.3.0</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Chart.js</div>
                                            <div class="tech-desc">Gráficos interactivos del dashboard</div>
                                        </div>
                                        <div class="tech-version">4.4.7</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Driver.js</div>
                                            <div class="tech-desc">Tours guiados interactivos</div>
                                        </div>
                                        <div class="tech-version">1.3.1</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Tippy.js</div>
                                            <div class="tech-desc">Tooltips avanzados</div>
                                        </div>
                                        <div class="tech-version">6.3.7</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Mermaid.js</div>
                                            <div class="tech-desc">Diagramas de flujo en documentación</div>
                                        </div>
                                        <div class="tech-version">11.x</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Font Awesome</div>
                                            <div class="tech-desc">Librería de iconos</div>
                                        </div>
                                        <div class="tech-version">6.4.0</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Google Fonts</div>
                                            <div class="tech-desc">Orbitron, Roboto Mono, Inter</div>
                                        </div>
                                        <div class="tech-version">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Backend -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#backendCollapse">
                                    <i class="fas fa-server me-2"></i> Backend
                                </button>
                            </h2>
                            <div id="backendCollapse" class="accordion-collapse collapse" data-bs-parent="#techAccordion">
                                <div class="accordion-body">
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">PHP</div>
                                            <div class="tech-desc">Lenguaje del servidor</div>
                                        </div>
                                        <div class="tech-version"><?php echo phpversion(); ?></div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">MariaDB</div>
                                            <div class="tech-desc">Base de datos relacional</div>
                                        </div>
                                        <div class="tech-version">11.8.x</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Composer</div>
                                            <div class="tech-desc">Gestor de dependencias PHP</div>
                                        </div>
                                        <div class="tech-version">2.x</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">PHPSpreadsheet</div>
                                            <div class="tech-desc">Lectura/escritura de archivos Excel</div>
                                        </div>
                                        <div class="tech-version">1.29</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Dompdf</div>
                                            <div class="tech-desc">Generación de PDFs</div>
                                        </div>
                                        <div class="tech-version">2.0</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">PHPMailer</div>
                                            <div class="tech-desc">Envío de correos SMTP</div>
                                        </div>
                                        <div class="tech-version">6.9</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Monolog</div>
                                            <div class="tech-desc">Sistema de logging</div>
                                        </div>
                                        <div class="tech-version">3.x</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">TCPDF</div>
                                            <div class="tech-desc">Generación de PDFs avanzados</div>
                                        </div>
                                        <div class="tech-version">6.7</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- API & Security -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#apiCollapse">
                                    <i class="fas fa-shield-alt me-2"></i> API & Seguridad
                                </button>
                            </h2>
                            <div id="apiCollapse" class="accordion-collapse collapse" data-bs-parent="#techAccordion">
                                <div class="accordion-body">
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">OpenAPI / Swagger</div>
                                            <div class="tech-desc">Documentación de API REST</div>
                                        </div>
                                        <div class="tech-version">3.0</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">TOTP (2FA)</div>
                                            <div class="tech-desc">Autenticación de dos factores</div>
                                        </div>
                                        <div class="tech-version">RFC 6238</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">bcrypt</div>
                                            <div class="tech-desc">Hashing de contraseñas</div>
                                        </div>
                                        <div class="tech-version">-</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">CSRF Protection</div>
                                            <div class="tech-desc">Tokens de seguridad en formularios</div>
                                        </div>
                                        <div class="tech-version">-</div>
                                    </div>
                                    <div class="tech-item">
                                        <div>
                                            <div class="tech-name">Session Management</div>
                                            <div class="tech-desc">Control de sesiones con limpieza automática</div>
                                        </div>
                                        <div class="tech-version">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Manual Button - Prominent -->
                <div class="manual-btn-container">
                    <a href="manual.php" class="manual-btn">
                        <i class="fas fa-book"></i>
                        Manual
                    </a>
                    <button class="tutorial-btn" onclick="tutorialManager.start()">
                        <i class="fas fa-play-circle"></i> Tutorial
                    </button>
                </div>

                <!-- System Info -->
                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">INFORMACIÓN DEL SERVIDOR</h5>
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

                <!-- Support -->
                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">SOPORTE TÉCNICO</h5>
                    <div class="support-item">
                        <i class="fas fa-envelope"></i>
                        <span>gisvelaray@gmail.com</span>
                    </div>
                    <div class="support-item">
                        <i class="fas fa-phone"></i>
                        <span>+58 412 875 9068</span>
                    </div>
                </div>

                <!-- License -->
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">LICENCIA</h5>
                    <p class="license-text">
                        © 2025-2026 <b>Proyecto Socio-Tecnológico BUZZ Data Manager.</b>
                        Este software es para fines educativos y está protegido por derechos de autor.
                        No se permite la distribución sin autorización.
                    </p>

                    <img src="assets/img/harebit.png" alt="HareBit Development Logo" class="developer-logo">
                </div>
                </div>
            </div>
        </div>

        <!-- System Footer (Includes Tutorial Scripts) -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
</body>

</html>
