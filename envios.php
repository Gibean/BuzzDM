<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Envíos</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
</head>
<body>
    <!--  Sidebar (same as other pages) -->
    <?php require_once 'php/sidebar.php'; ?>

     <!-- Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'GESTIÓN DE ENVÍOS';
        $nav_subtitle = 'LOGÍSTICA';
        require_once 'php/top_navbar.php';
        ?>

        <!-- Stats Removed -->

            <!-- Filtros -->
            <div class="buzz-card mb-4 pb-3">
                <div class="filter-wrapper">
                    <!-- Basic Filters (Always Visible) -->
                    <div class="filter-group filter-search">
                        <label class="text-muted">Buscar</label>
                        <input type="text" class="form-control form-control-buzz" id="searchInput" placeholder="ID pedido, cliente...">
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Estado de envío</label>
                        <select class="form-control form-control-buzz" id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregado">Entregado</option>
                        </select>
                    </div>

                    <div class="filter-group filter-actions">
                        <button class="btn btn-outline-buzz btn-filter-action" id="toggleAdvancedFilters" title="Más filtros">
                            <i class="fas fa-filter"></i>
                            <span class="btn-text">Filtros Avanzados</span>
                            <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                        </button>
                        <button class="btn btn-outline-buzz btn-filter-action" onclick="clearFilters()" title="Limpiar">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advancedFiltersCollapse" class="advanced-filters-section mt-3 d-none">
                    <hr class="border-secondary opacity-25">
                    <div class="filter-wrapper">
                         <div class="filter-group filter-select">
                            <label class="text-muted">Ordenar por</label>
                            <select class="form-control form-control-buzz" id="sortBy">
                                <option value="date">Fecha</option>
                                <option value="status">Estado</option>
                                <option value="customer">Cliente</option>
                                <option value="amount">Monto</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

         <!-- Shipments Cards  -->
        <div id="shipmentsContainer">
             Shipments will be populated by JavaScript
        </div>
    </main>

    <!-- System Footer (Includes Tutorial Scripts) -->
    <?php require_once 'php/system_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/envios.js"></script>
</body>
</html>
