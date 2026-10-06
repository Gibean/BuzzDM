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
        <!-- Top Navbar -->
        <?php
        $nav_title = 'ACERCA DEL SISTEMA';
        $nav_subtitle = 'INFORMACIÓN';
        require_once 'php/top_navbar.php';
        ?>

        <!-- System Info -->
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
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
                                <div class="stat-value">v1.2</div>
                                <div class="stat-sub">Build 2026.04</div>
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
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <!-- Support -->
                        <div class="buzz-card h-100 d-flex flex-column text-center">
                            <h5 class="font-primary text-primary mb-3">SOPORTE TÉCNICO</h5>
                            <div class="d-flex flex-column align-items-center justify-content-center mb-3 flex-grow-1" style="gap: 10px;">
                                <div class="support-item" style="justify-content: center;">
                                    <i class="fas fa-envelope"></i>
                                    <span>buzzdatamanager@gmail.com</span>
                                </div>
                            </div>
                            
                            <!-- Manual Button - Prominent -->
                            <div class="manual-btn-container mt-auto mb-0" style="padding: 0.8rem;">
                                <a href="manual" class="manual-btn">
                                    <i class="fas fa-book"></i> Manual
                                </a>
                                <button class="tutorial-btn" onclick="tutorialManager.start()">
                                    <i class="fas fa-play-circle"></i> Tutorial
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- License -->
                        <div class="buzz-card h-100 d-flex flex-column text-center">
                            <h5 class="font-primary text-primary mb-2">LICENCIA</h5>
                            <div class="legal-logos d-flex flex-column align-items-center justify-content-center flex-grow-1">
                                <img src="assets/img/recursosinv/Logo_Uptjaa.svg" alt="UPTJAA Logo" class="legal-logo mb-3" style="width: 55px; opacity: 0.9;">
                                
                                <div class="license-text">
                                    <p class="mb-2">© 2025-2026 <b>Desarrollado por:</b><br> Gisvel Aray y Yeicson Acosta.</p>
                                    <p class="mb-2"><b>Uso:</b> Proyecto académico con fines demostrativos y educativos.</p>
                                    <p class="mb-2"><b>Licencia:</b> CC BY-NC-SA 4.0</p>
                                    <p class="mb-0 mt-3" style="font-size: 0.70rem; opacity: 0.8; line-height: 1.4;">Proyecto académico demostrativo. Queda prohibida su comercialización por terceros. El escudo identifica el contexto académico del trabajo: la universidad no es titular del código ni autoriza su redistribución.</p>
                                </div>
                            </div>
                        </div>
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
