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
            <div class="d-flex align-items-center">
                <span class="text-muted font-mono me-3">ÚLTIMA ACTUALIZACIÓN: <span id="lastUpdate"></span></span>
                <div class="bg-success rounded-circle" style="width: 10px; height: 10px;"></div>
                <span class="text-success font-mono ms-2">SISTEMA ONLINE</span>
            </div>
        </nav>


        <!-- Statistics Section -->
        <div class="buzz-card mb-4">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="d-flex gap-2">
                        <span class="text-muted">Productos Activos:</span>
                        <span class="font-mono text-primary" id="totalProducts">0</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="d-flex gap-2">
                        <span class="text-muted">Pedidos Totales:</span>
                        <span class="font-mono text-success" id="totalOrders">0</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="d-flex gap-2">
                        <span class="text-muted">Envíos Pendientes:</span>
                        <span class="font-mono text-warning" id="pendingShipments">0</span>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="d-flex gap-2">
                        <span class="text-muted">Ingresos del Mes:</span>
                        <span class="font-mono text-primary" id="monthlyRevenue">$0</span>
                    </div>
                </div>
            </div>
        </div>


       <!---  Charts and Activity -->
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="buzz-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-primary text-primary mb-0">ACTIVIDAD DE VENTAS</h5>
                        <div class="d-flex align-items-center">
                            <div class="bg-success rounded-circle me-2" style="width: 8px; height: 8px;"></div>
                            <span class="font-mono text-muted" style="font-size: 0.8rem;">TIEMPO REAL</span>
                        </div>
                    </div>
                    <div style="position: relative; height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">VENTAS POR ESTADO DE PAGO</h5>
                    <div style="position: relative; height: 320px;">
                        <canvas id="paymentStatusChart"></canvas>
                    </div>
                </div>
            </div>


            </div>

        <div class="row">

            <div class="col-lg-4 mb-4"> <!-- Top Products -->
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">TOP 5 PRODUCTOS MÁS VENDIDOS</h5>
                    <div id="topProducts">
                        <ul class="list-unstyled">
                            <?php if (!empty($top5Products)): ?>
                                <?php foreach ($top5Products as $productName): ?>
                                    <li class="font-mono text-primary mb-2"><?php echo htmlspecialchars($productName); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-muted">No hay datos disponibles.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4"> <!-- Low Stock -->
                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">PRODUCTOS CON STOCK BAJO</h5>
                    <div id="lowStockProducts">
                        <ul class="list-unstyled">
                            <?php if (!empty($lowStockProducts)): ?>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <li class="font-mono text-warning mb-2"><?php echo htmlspecialchars($product['sku'] . ' - ' . $product['nombre'] . ' (' . $product['stock'] . ' unidades)'); ?></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-muted">No hay productos con stock bajo.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
