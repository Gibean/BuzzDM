<?php
require_once 'php/sesion.php';
require_once 'php/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - BUZZ Data Manager</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Driver.js CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.css"/>
    <!-- Tippy.js CSS (via styles usually, but for theme we might need custom CSS) -->

    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Interactive Manual Specific Styles */
        .manual-hero {
            background: linear-gradient(135deg, rgba(255, 160, 0, 0.1) 0%, rgba(33, 37, 41, 1) 100%);
            border-radius: 12px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid var(--buzz-border-color);
        }

        .manual-hero-content {
            z-index: 2;
            max-width: 60%;
        }

        .manual-hero-visual {
            z-index: 2;
            width: 300px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .manual-card {
            background: var(--buzz-card-bg);
            border: 1px solid var(--buzz-border-color);
            border-radius: 12px;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .manual-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            border-color: var(--buzz-primary-color);
        }

        .manual-card-icon {
            font-size: 2.5rem;
            color: var(--buzz-primary-color);
            margin-bottom: 1rem;
            background: rgba(255, 160, 0, 0.1);
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .manual-card h3 {
            font-family: var(--font-primary);
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--buzz-text-color);
        }

        .manual-card p {
            font-size: 0.9rem;
            color: var(--buzz-text-muted);
            margin-bottom: 1rem;
        }

        .manual-card .card-link {
            font-size: 0.85rem;
            color: var(--buzz-primary-color);
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .manual-card .card-link i {
            margin-left: 5px;
            transition: transform 0.2s;
        }

        .manual-card:hover .card-link i {
            transform: translateX(5px);
        }

        .search-container {
            margin-bottom: 2rem;
            position: relative;
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--buzz-text-muted);
        }

        .search-container input {
            padding-left: 45px;
            height: 50px;
            border-radius: 25px;
            background: var(--buzz-dark-bg);
            border: 1px solid var(--buzz-border-color);
            color: white;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }

        .search-container input:focus {
            background: var(--buzz-card-bg);
            border-color: var(--buzz-primary-color);
            box-shadow: 0 0 0 4px rgba(255,160,0,0.1);
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            display: none;
        }

        /* Tippy Theme Customization */
        .tippy-box[data-theme~='buzz'] {
            background-color: var(--buzz-primary-color);
            color: var(--buzz-dark-bg);
            font-family: var(--font-primary);
            font-weight: 600;
            border-radius: 4px;
        }
        .tippy-box[data-theme~='buzz'][data-placement^='top'] > .tippy-arrow::before {
            border-top-color: var(--buzz-primary-color);
        }

        /* Driver.js Customization */
        .driver-popover.driverjs-theme {
            background-color: var(--buzz-card-bg);
            color: var(--buzz-text-color);
            border: 1px solid var(--buzz-primary-color);
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.5);
        }

        .driver-popover.driverjs-theme .driver-popover-title {
            font-family: var(--font-primary);
            font-size: 1.2rem;
            color: var(--buzz-primary-color);
        }

        .driver-popover.driverjs-theme .driver-popover-description {
            font-family: var(--font-monospace);
            font-size: 0.9rem;
            color: var(--buzz-text-muted);
        }

        .driver-popover.driverjs-theme button {
            background-color: var(--buzz-primary-color);
            color: #000;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-weight: bold;
            font-family: var(--font-primary);
            text-shadow: none;
        }

        .driver-popover.driverjs-theme button:hover {
            background-color: #ffb300;
        }

        /* Fix for black screen / stacking context issues */
        body.driver-active .manual-hero {
            z-index: 100000 !important; /* Ensure it pops out */
            position: relative;
        }
    </style>
</head>

<body>
    <main class="main-content">
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">CENTRO DE AYUDA</h1>
                <span class="text-muted ms-2 font-mono">/ MANUAL INTERACTIVO</span>
            </div>
        </nav>

        <div class="container-fluid py-4">

<!-- Master View: Module Grid -->
            <div id="master-view">
                <!-- Hero Section -->
                <div id="hero-section" class="manual-hero">
                    <div class="manual-hero-content">
                        <h2 class="display-5 fw-bold text-white mb-3">Hola, <span class="text-primary"><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></span></h2>
                        <p class="lead text-muted mb-4">¿En qué podemos ayudarte hoy? Explora las guías por módulo o realiza una visita guiada por el sistema.</p>
                        <button id="start-tour-btn" class="btn btn-buzz btn-lg pulse-animation" data-tippy-content="Inicia un recorrido rápido por la interfaz">
                            <i class="fas fa-play-circle me-2"></i>Iniciar Tour de Bienvenida
                        </button>
                    </div>
                    <div class="manual-hero-visual d-none d-md-flex">
                        <lottie-player src="https://assets2.lottiefiles.com/packages/lf20_M9p23l.json"  background="transparent"  speed="1"  style="width: 300px; height: 300px;"  loop  autoplay></lottie-player>
                    </div>
                </div>

                <!-- Search -->
                <div id="search-container" class="search-container">
                    <i class="fas fa-search fa-lg"></i>
                    <input type="text" id="manualSearch" class="form-control" placeholder="Buscar ayuda... (ej. cómo crear pedido, importar excel, stock bajo)">
                </div>

                <!-- Modules Grid -->
                <div id="module-grid" class="row g-4">
                    <!-- Dashboard -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="graficos, metricas, ventas, resumen" onclick="showSection('dashboard')">
                            <div class="manual-card-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <h3>Dashboard</h3>
                            <p>Métricas clave, gráficos de ventas y alertas en tiempo real.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Inventario -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="productos, stock, existencias, precios" onclick="showSection('inventario')">
                            <div class="manual-card-icon"><i class="fas fa-boxes"></i></div>
                            <h3>Inventario</h3>
                            <p>Gestión de productos, ajustes de stock y control de almacenes.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Pedidos -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="ordenes, ventas, facturacion" onclick="showSection('pedidos')">
                            <div class="manual-card-icon"><i class="fas fa-shopping-cart"></i></div>
                            <h3>Pedidos</h3>
                            <p>Procesamiento de ventas, gestión de pagos y despachos.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Clientes -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="personas, contactos, crm" onclick="showSection('clientes')">
                            <div class="manual-card-icon"><i class="fas fa-users"></i></div>
                            <h3>Clientes</h3>
                            <p>Base de datos de compradores, historial y cuenta VIP.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Envíos -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="despacho, transporte, guia" onclick="showSection('envios')">
                            <div class="manual-card-icon"><i class="fas fa-truck"></i></div>
                            <h3>Envíos</h3>
                            <p>Logística de entrega, tracking y estados de envío.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Importación -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="excel, carga masiva, csv" onclick="showSection('importacion')">
                            <div class="manual-card-icon"><i class="fas fa-file-upload"></i></div>
                            <h3>Importación</h3>
                            <p>Carga masiva de datos mediante plantillas Excel.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                    <!-- Admin -->
                    <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="usuarios, roles, permisos" onclick="showSection('admin')">
                            <div class="manual-card-icon"><i class="fas fa-user-shield"></i></div>
                            <h3>Admin & Logs</h3>
                            <p>Gestión de usuarios y auditoría de seguridad.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                     <!-- Cargos -->
                     <div class="col-md-6 col-lg-4">
                        <div class="manual-card" data-keywords="entradas, compras, recepcion" onclick="showSection('cargos')">
                            <div class="manual-card-icon"><i class="fas fa-dolly"></i></div>
                            <h3>Cargos (Entradas)</h3>
                            <p>Recepción de mercancía y compras a proveedores.</p>
                            <span class="card-link">Ver guía <i class="fas fa-arrow-right"></i></span>
                        </div>
                    </div>
                </div>

                <!-- No Results -->
                <div id="noResults" class="no-results">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No encontramos guías para esa búsqueda.</p>
                    <button class="btn btn-outline-primary btn-sm" onclick="clearSearch()">Ver todo</button>
                </div>
            </div>

            <!-- Detail View: Documentation Container -->
            <div id="detail-view" style="display: none;">
                <div class="mb-4">
                    <button class="btn btn-outline-secondary" onclick="showMaster()"><i class="fas fa-arrow-left me-2"></i>Volver al Índice</button>
                </div>

                <div class="row">
                    <!-- Left: Content -->
                    <div class="col-lg-9">
                        <div class="buzz-card p-4" id="doc-content">
                            <!-- Content Injected via JS -->
                        </div>
                    </div>
                    <!-- Right: Sticky TOC (Optional for future) -->
                    <div class="col-lg-3 d-none d-lg-block">
                        <div class="sticky-top" style="top: 20px;">
                            <div class="buzz-card p-3">
                                <h6>En esta página</h6>
                                <hr class="my-2">
                                <ul class="list-unstyled text-muted font-mono small" id="doc-toc">
                                    <!-- TOC Generated via JS -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hidden Content Templates -->
            <div id="content-templates" style="display: none;">

                <!-- Dashboard Content -->
                <div id="tpl-dashboard">
                    <h1 class="font-primary text-primary mb-4"><i class="fas fa-tachometer-alt me-2"></i>Manual de Dashboard</h1>
                    <p class="lead">El Dashboard es su centro de comando. Ofrece una vista panorámica del estado de su negocio con métricas en tiempo real.</p>
                    <hr>
                    <h3 class="mt-4">Vista General</h3>
                    <div class="mermaid">
                        flowchart TB
                        subgraph Dashboard["🏠 Dashboard"]
                            subgraph KPIs["📈 Métricas Principales"]
                                TotalProd[Total Productos]
                                TotalPed[Pedidos Activos]
                                StockBajo[Alertas Stock]
                            end
                            subgraph Charts["📊 Gráficos"]
                                Ventas[Ventas por Mes]
                                Pagos[Estado de Pagos]
                            end
                        end
                    </div>
                    <h3 class="mt-4">Métricas Clave (KPIs)</h3>
                    <table class="table table-bordered table-dark mt-3">
                        <tr><th>KPI</th><th>Descripción</th></tr>
                        <tr><td><strong>Total Productos</strong></td><td>Cantidad de SKUs activos en su inventario.</td></tr>
                        <tr><td><strong>Pedidos Activos</strong></td><td>Suma de pedidos pendientes y en proceso.</td></tr>
                        <tr><td><strong>Alertas Stock</strong></td><td>Productos que han alcanzado su nivel mínimo.</td></tr>
                    </table>
                </div>

                <!-- Inventario Content -->
                <div id="tpl-inventario">
                    <h1 class="font-primary text-primary mb-4"><i class="fas fa-boxes me-2"></i>Manual de Inventario</h1>
                    <p class="lead">Gestión centralizada de su catálogo de productos, control de existencias y precios.</p>
                    <hr>
                    <h3 class="mt-4">Flujo de Creación de Producto</h3>
                    <div class="mermaid">
                        flowchart TD
                            A[Click '+ Nuevo Producto'] --> B[Abrir modal]
                            B --> C[Completar campos obligatorios]
                            C --> D{¿Datos válidos?}
                            D -->|No| E[Mostrar errores]
                            E --> C
                            D -->|Sí| F[Click 'Guardar']
                            F --> G[✅ Producto creado]
                    </div>
                    <h3 class="mt-4">Campos Importantes</h3>
                    <ul>
                        <li><strong>SKU:</strong> Código único e irrepetible del producto.</li>
                        <li><strong>Stock Mínimo:</strong> Nivel que activa la alerta amarilla de reabastecimiento.</li>
                        <li><strong>Precio Venta:</strong> Valor final para el cliente.</li>
                    </ul>
                </div>

                <!-- Pedidos Content -->
                <div id="tpl-pedidos">
                    <h1 class="font-primary text-primary mb-4"><i class="fas fa-shopping-cart me-2"></i>Manual de Pedidos</h1>
                    <p class="lead">Procese las órdenes de compra desde la creación hasta la entrega final.</p>
                    <hr>
                    <h3 class="mt-4">Ciclo de Vida del Pedido</h3>
                    <div class="mermaid">
                        stateDiagram-v2
                            [*] --> Pendiente
                            Pendiente --> Pagado: Pago confirmado
                            Pagado --> Enviado: Despacho realizado
                            Enviado --> Entregado: Confirmación cliente
                    </div>
                    <h3 class="mt-4">Crear un Pedido</h3>
                    <ol>
                        <li>Click en <strong>+ Nuevo Pedido</strong>.</li>
                        <li>Seleccione un Cliente existente o cree uno nuevo.</li>
                        <li>Busque y agregue productos al carrito.</li>
                        <li>Defina la dirección de envío y guarde.</li>
                    </ol>
                </div>

                <!-- Clientes Content -->
                <div id="tpl-clientes">
                    <h1 class="font-primary text-primary mb-4"><i class="fas fa-users me-2"></i>Manual de Clientes</h1>
                    <p class="lead">Administre la relación con sus compradores (CRM Básico).</p>
                    <hr>
                    <h3 class="mt-4">Gestión de Clientes</h3>
                    <div class="mermaid">
                        flowchart LR
                        subgraph CRM["👥 Gestión"]
                            Nuevo[➕ Nuevo cliente]
                            Buscar[🔍 Buscar]
                            Historial[📦 Historial pedidos]
                            VIP[⭐ Marcar VIP]
                        end
                    </div>
                    <h3 class="mt-4">Cliente VIP</h3>
                    <p>Marcar un cliente como VIP permite filtrarlos rápidamente y darles atención prioritaria. Para hacerlo, edite el cliente y marque la casilla "VIP".</p>
                </div>

            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10.6.1/dist/mermaid.min.js"></script>

    <!-- Custom Logic -->
    <script src="assets/js/common.js"></script>
    <script src="assets/js/manual.js"></script>
</body>
</html>

    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    .manual-header {
        background: linear-gradient(135deg, var(--buzz-dark-bg) 0%, var(--buzz-card-bg) 100%);
        border-bottom: 2px solid var(--buzz-primary-color);
        padding: 2rem;
        border-radius: 8px 8px 0 0;
    }

    .search-box {
        position: relative;
        margin-bottom: 2rem;
    }

    .search-box input {
        padding-left: 3rem;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--buzz-text-muted);
        pointer-events: none;
    }

    .accordion-item {
        background-color: var(--buzz-card-bg);
        border: 1px solid var(--buzz-border-color);
        margin-bottom: 0.5rem;
        border-radius: 4px !important;
        overflow: hidden;
    }

    .accordion-button {
        background-color: var(--buzz-card-bg);
        color: var(--buzz-text-color);
        font-family: var(--font-primary);
        font-weight: 600;
        border: none;
        padding: 1.25rem 1.5rem;
        transition: all 0.3s ease;
    }

    .accordion-button:not(.collapsed) {
        background-color: var(--buzz-primary-color);
        color: var(--buzz-dark-bg);
        box-shadow: none;
    }

    .accordion-button:hover {
        background-color: var(--buzz-primary-color);
        color: var(--buzz-dark-bg);
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 2px rgba(255, 160, 0, 0.2);
        border-color: var(--buzz-primary-color);
    }

    .accordion-button::after {
        filter: brightness(0) invert(1);
    }

    .accordion-button:not(.collapsed)::after {
        filter: brightness(0);
    }

    .accordion-body {
        background-color: var(--buzz-card-bg);
        color: var(--buzz-text-color);
        padding: 1.5rem;
        border-top: 1px solid var(--buzz-border-color);
    }

    .manual-list-item {
        background-color: transparent;
        border: none;
        border-left: 3px solid var(--buzz-primary-color);
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        color: var(--buzz-text-color);
        font-family: var(--font-monospace);
        transition: all 0.3s ease;
    }

    .manual-list-item:hover {
        background-color: rgba(255, 160, 0, 0.1);
        border-left-color: var(--buzz-primary-color);
        transform: translateX(5px);
    }

    .manual-list-item i {
        color: var(--buzz-primary-color);
        margin-right: 0.75rem;
        width: 20px;
    }

    .no-results {
        text-align: center;
        padding: 3rem;
        color: var(--buzz-text-muted);
    }

    .no-results i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    </style>
</head>

<body>
    <main class="main-content">
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">MANUAL DE USUARIO</h1>
                <span class="text-muted ms-2 font-mono">/ GUÍA INTERACTIVA</span>
            </div>
        </nav>

        <div class="buzz-card">
            <div class="manual-header">
                <h2 class="font-primary text-primary mb-3">
                    <i class="fas fa-book-open me-2"></i>Bienvenido al Manual de BUZZ Data Manager
                </h2>
                <p class="font-mono text-muted mb-0">
                    Guía completa para utilizar todas las funcionalidades del sistema de gestión
                </p>
            </div>

            <div class="p-4">
                <!-- Search Box -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control form-control-buzz" id="manualSearch"
                           placeholder="Buscar en el manual... (ej: productos, clientes, importar)">
                </div>

                <!-- Accordion Container -->
                <div class="accordion" id="manualAccordion">

                        <!-- Sección Dashboard -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>El <strong>Dashboard</strong> es la pantalla principal al iniciar sesión. Ofrece
                                        una vista general del estado de su negocio a través de varias tarjetas y
                                        gráficos.</p>
                                    <ul class="list-unstyled">
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-line me-2"></i><strong>Gráfico de
                                                Ventas:</strong> Muestra las tendencias de ventas a lo largo del tiempo.
                                        </li>
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-pie me-2"></i><strong>Ventas por
                                                Estado:</strong> Un gráfico circular que desglosa los pedidos según su
                                            estado actual (ej. Pendiente, Completado, Cancelado).</li>
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-bar me-2"></i><strong>Estadísticas
                                                Clave:</strong> Tarjetas con métricas importantes como total de ventas,
                                            número de clientes y cantidad de pedidos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Pedidos -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-box-seam me-2"></i>Gestión de Pedidos
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>La sección de <strong>Pedidos</strong> le permite administrar todas las órdenes
                                        de sus clientes.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-plus-circle-fill me-2"></i><strong>Crear Pedido:</strong>
                                            Puede registrar nuevos pedidos asociándolos a un cliente y agregando
                                            productos del inventario.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-pencil-square me-2"></i><strong>Editar Pedido:</strong>
                                            Modificar la información de un pedido existente, como su estado (pendiente,
                                            en proceso, enviado, etc.).</li>
                                        <li class="list-group-item"><i class="bi bi-search me-2"></i><strong>Buscar y
                                                Filtrar:</strong> Localice pedidos rápidamente usando la barra de
                                            búsqueda o los filtros disponibles.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-file-earmark-pdf-fill me-2"></i><strong>Generar
                                                Reportes:</strong> Es posible que desde aquí o la sección de reportes
                                            pueda generar un PDF con los detalles del pedido.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Clientes -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="bi bi-people-fill me-2"></i>Gestión de Clientes
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Administre la información de todos sus clientes de forma centralizada.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-person-plus-fill me-2"></i><strong>Añadir Cliente:</strong>
                                            Registre nuevos clientes con su información de contacto y fiscal.</li>
                                        <li class="list-group-item"><i class="bi bi-pencil-fill me-2"></i><strong>Editar
                                                Cliente:</strong> Actualice los datos de cualquier cliente registrado.
                                        </li>
                                        <li class="list-group-item"><i
                                                class="bi bi-trash-fill me-2"></i><strong>Eliminar Cliente:</strong>
                                            Borre clientes que ya no estén activos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Inventario -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="bi bi-bookshelf me-2"></i>Inventario
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Controle todos los productos de su negocio.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-plus-square-fill me-2"></i><strong>Añadir
                                                Producto:</strong> Ingrese nuevos productos al sistema con detalles como
                                            SKU, nombre, descripción y stock inicial.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-pencil-square me-2"></i><strong>Editar Producto:</strong>
                                            Modifique la información y el stock de los productos existentes.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Importación -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Importación de Datos
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Esta potente herramienta le permite cargar datos de forma masiva utilizando
                                        archivos de Excel.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i class="bi bi-download me-2"></i><strong>Descargar
                                                Plantilla:</strong> Obtenga el archivo <code>template_buzz.xlsx</code>
                                            que sirve como guía para la importación.</li>
                                        <li class="list-group-item"><i class="bi bi-upload me-2"></i><strong>Procesar
                                                Importación:</strong> Suba el archivo Excel con sus datos para cargarlos
                                            automáticamente en el sistema (ej. nuevos productos, clientes o pedidos).
                                        </li>
                                        <li class="list-group-item"><i
                                                class="bi bi-clock-history me-2"></i><strong>Historial de
                                                Importaciones:</strong> Revise las importaciones que se han realizado
                                            anteriormente.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Administración -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    <i class="bi bi-person-badge-fill me-2"></i>Administración
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Sección dedicada a la configuración y gestión de alto nivel del sistema.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-person-fill-gear me-2"></i><strong>Gestión de
                                                Usuarios:</strong> Permite crear, editar y eliminar los usuarios que
                                            tienen acceso al sistema.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-building-fill-gear me-2"></i><strong>Configuración de la
                                                Empresa:</strong> Actualice los datos de su empresa, como el nombre, RIF
                                            y el logo que aparece en los reportes.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-shield-lock-fill me-2"></i><strong>Backups:</strong> Genere
                                            y descargue copias de seguridad de la base de datos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="no-results" style="display: none;">
                    <i class="fas fa-search"></i>
                    <p class="font-mono">No se encontraron resultados para su búsqueda</p>
                    <small class="text-muted">Intente con otros términos de búsqueda</small>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script>
        // Search functionality for manual
        const searchInput = document.getElementById('manualSearch');
        const accordionItems = document.querySelectorAll('.accordion-item');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            accordionItems.forEach(item => {
                const button = item.querySelector('.accordion-button');
                const body = item.querySelector('.accordion-body');
                const buttonText = button.textContent.toLowerCase();
                const bodyText = body.textContent.toLowerCase();

                // Check if search term is in title or content
                if (searchTerm === '' || buttonText.includes(searchTerm) || bodyText.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;

                    // If searching and found, expand the section
                    if (searchTerm !== '' && (buttonText.includes(searchTerm) || bodyText.includes(searchTerm))) {
                        const collapse = item.querySelector('.accordion-collapse');
                        if (!collapse.classList.contains('show')) {
                            button.click();
                        }
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm !== '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });

        // Smooth scroll for better UX
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 350);
            });
        });
    </script>
</body>

</html>
