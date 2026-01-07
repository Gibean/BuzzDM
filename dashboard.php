<?php
require_once 'php/sesion.php';
require_once 'php/db.php'; // Provides $pdo

// Initialize arrays to prevent errors in HTML if queries fail
$top5Products = [];
$lowStockProducts = [];
$activityLog = [];

try {
    // 1. Consultar TOP 5 PRODUCTOS MÁS VENDIDOS
    $queryTop5 = "SELECT nombre FROM v_top5_up LIMIT 5";
    $stmtTop5 = $pdo->query($queryTop5);
    $top5Products = $stmtTop5->fetchAll(PDO::FETCH_COLUMN, 0);

    // 2. Consultar PRODUCTOS CON STOCK BAJO
    $queryLowStock = "SELECT sku, nombre, stock FROM v_low_stock";
    $stmtLowStock = $pdo->query($queryLowStock);
    $lowStockProducts = $stmtLowStock->fetchAll(PDO::FETCH_ASSOC);

    // 3. Consultar REGISTRO DE ACTIVIDAD (desde la tabla de logs)
    $queryActivity = "SELECT detalles FROM logs ORDER BY fecha DESC LIMIT 10";
    $stmtActivity = $pdo->query($queryActivity);
    $activityLog = $stmtActivity->fetchAll(PDO::FETCH_COLUMN, 0);

} catch (PDOException $e) {
    // Log the error or handle it gracefully
    // For now, we'll just let the arrays be empty, so the page doesn't break.
    error_log("Database error in dashboard.php: " . $e->getMessage());
}

// No need to close PDO connection like mysqli
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Dashboard</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard-effects.css">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar --->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">CENTRO DE COMANDO</h1>
                <span class="text-muted ms-2 font-mono">/ DASHBOARD</span>
            </div>
        </nav>


        <!-- Statistics Section -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3 dashboard-item">
                <div class="stats-card">
                    <span class="stats-number" id="totalProducts">0</span>
                    <span class="stats-label">Productos Activos</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 dashboard-item">
                <div class="stats-card">
                    <span class="stats-number" id="totalOrders">0</span>
                    <span class="stats-label">Pedidos Totales</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 dashboard-item">
                <div class="stats-card">
                    <span class="stats-number" id="pendingShipments">0</span>
                    <span class="stats-label">Envíos Pendientes</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3 dashboard-item">
                <div class="stats-card">
                    <span class="stats-number" id="monthlyRevenue">$0</span>
                    <span class="stats-label">Ingresos del Mes</span>
                </div>
            </div>
        </div>


       <!---  Charts and Activity -->
        <div class="row">
            <div class="col-lg-8 mb-4 dashboard-item">
                <div class="buzz-card chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-primary text-primary mb-0 section-header">ACTIVIDAD DE VENTAS</h5>
                        <div class="real-time-indicator">
                            <div class="real-time-dot"></div>
                            <span class="font-mono text-muted" style="font-size: 0.8rem;">TIEMPO REAL</span>
                        </div>
                    </div>
                    <div style="position: relative; height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card chart-container payment-chart-card">
                    <div class="chart-card-header">
                        <h5 class="font-primary text-primary mb-3 section-header">
                            <i class="fas fa-wallet me-2"></i>VENTAS POR ESTADO DE PAGO
                        </h5>
                    </div>
                    <div style="position: relative; height: 320px;">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                </div>
            </div>


            </div>

        <div class="row">
            <!-- Top Products -->
            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card tech-effect h-100">
                    <div class="card-glow"></div>
                    <h5 class="font-primary text-primary mb-3 section-header">
                        <i class="fas fa-trophy me-2"></i>TOP 5 PRODUCTOS
                    </h5>
                    <div class="premium-list" id="topProducts">
                        <?php if (!empty($top5Products)): ?>
                            <?php foreach ($top5Products as $index => $productName): ?>
                                <div class="premium-list-item">
                                    <div class="item-rank"><?php echo $index + 1; ?></div>
                                    <div class="item-info">
                                        <span class="item-name font-secondary"><?php echo htmlspecialchars($productName); ?></span>
                                    </div>
                                    <div class="item-trend"><i class="fas fa-chevron-up text-success"></i></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted p-3">No hay datos disponibles.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card tech-effect h-100">
                    <div class="card-glow"></div>
                    <h5 class="font-primary text-warning mb-3 section-header border-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>STOCK CRÍTICO
                    </h5>
                    <div class="premium-list" id="lowStockProducts">
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <div class="premium-list-item warning">
                                    <div class="item-icon"><i class="fas fa-box"></i></div>
                                    <div class="item-info">
                                        <span class="item-sku font-mono"><?php echo htmlspecialchars($product['sku']); ?></span>
                                        <span class="item-name font-secondary d-block"><?php echo htmlspecialchars($product['nombre']); ?></span>
                                    </div>
                                    <div class="item-value font-mono danger"><?php echo htmlspecialchars($product['stock']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted p-3">Todo el stock está normal.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Currency Monitor (Placeholder) -->
            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card tech-effect currency-monitor h-100">
                    <div class="card-glow"></div>
                    <h5 class="font-primary text-info mb-3 section-header border-info">
                        <i class="fas fa-chart-line me-2"></i>MONITOR DE DIVISAS
                    </h5>
                    <div class="currency-content empty-state">
                        <div class="empty-icon"><i class="fas fa-dollar-sign"></i></div>
                        <p class="font-mono text-muted mb-0">Esperando conexión con API...</p>
                        <div class="loading-bar">
                            <div class="loading-progress"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
