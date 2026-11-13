<?php
require_once 'php/sesion.php';
require_once 'php/db.php'; // Provides $pdo
require_once 'php/check_alerts.php'; // System notifications

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

    // 4. Consultar TASAS DE CAMBIO (BCV, EUR, USDT)
    $allTasas = getAllTasasHoy();
    $tasaData = $allTasas['bcv'] ?? null; // Keep compatibility for some parts if needed

    // 5. NUEVAS ESTADÍSTICAS TÁCTICAS
    // Clientes totales
    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes WHERE deleted_at IS NULL");
    $totalCustomers = $stmt->fetchColumn();

    // Productos con bajo stock
    $stmt = $pdo->query("SELECT COUNT(*) FROM v_low_stock");
    $lowStockCount = $stmt->fetchColumn();



    // Pedidos de hoy
    $stmt = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_creacion) = CURDATE()");
    $dailyOrders = $stmt->fetchColumn();

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
        <!-- Top Navbar -->
        <?php
        $nav_title = 'CENTRO DE COMANDO';
        $nav_subtitle = 'DASHBOARD';
        require_once 'php/top_navbar.php';
        ?>


        <!-- Command Strip (High Density Status Bar) -->
        <div class="command-strip dashboard-item">
            <div class="status-node">
                <span class="node-label"><i class="fas fa-microchip me-1"></i> Stock Critico</span>
                <span class="node-value red" id="tcLowStock">0</span>
            </div>
            <div class="status-node">
                <span class="node-label"><i class="fas fa-users me-1"></i> Clientes Activos</span>
                <span class="node-value blue" id="tcCustomers">0</span>
            </div>
            <div class="status-node">
                <span class="node-label"><i class="fas fa-shopping-cart me-1"></i> Pedidos Hoy</span>
                <span class="node-value" id="tcDailyOrders">0</span>
            </div>
            <div class="status-node">
                <span class="node-label"><i class="fas fa-boxes me-1"></i> Productos</span>
                <span class="node-value" id="totalProducts">0</span>
            </div>
            <div class="status-node">
                <span class="node-label"><i class="fas fa-file-invoice me-1"></i> Pedidos Totales</span>
                <span class="node-value" id="totalOrders">0</span>
            </div>
            <div class="status-node">
                <span class="node-label"><i class="fas fa-truck me-1"></i> Pendientes</span>
                <span class="node-value red" id="pendingShipments">0</span>
            </div>

            <div class="status-node">
                <span class="node-label"><i class="fas fa-chart-line me-1"></i> Ingresos Mes</span>
                <span class="node-value" id="monthlyRevenue">$0</span>
            </div>
        </div>


       <!---  Charts and Activity -->
        <div class="row">
            <div class="col-lg-8 mb-4 dashboard-item">
                <div class="buzz-card chart-container tactical-card">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <h5 class="font-primary text-primary mb-0 section-header">FLUJO DE VENTAS</h5>
                        <div class="d-flex align-items-center gap-3">
                            <!-- Period Selector Tabs -->
                            <div class="chart-period-tabs" role="tablist">
                                <button class="period-tab" data-period="7" title="Última semana">7D</button>
                                <button class="period-tab active" data-period="30" title="Último mes">1M</button>
                                <button class="period-tab" data-period="180" title="Últimos 6 meses">6M</button>
                            </div>
                            <!-- Live Indicator -->
                            <div class="live-indicator">
                                <span class="live-dot"></span>
                                <span class="live-text">EN VIVO</span>
                            </div>
                        </div>
                    </div>
                    <div style="position: relative; height: 320px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>


            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card chart-container payment-chart-card tactical-card">
                    <div class="chart-card-header">
                        <h5 class="font-primary text-primary mb-3 section-header">
                            <i class="fas fa-wallet me-2"></i>ESTADO PAGOS
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
                <div class="buzz-card tech-effect tactical-card h-100" id="topProductsCard">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-primary text-primary mb-0 section-header">
                            <i class="fas fa-trophy me-2"></i>TOP 5 PRODUCTOS
                        </h5>
                    </div>
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
                            <div class="text-muted p-2 small">DATOS NO DISPONIBLES</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Low Stock -->
            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card tech-effect tactical-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-primary text-warning mb-0 section-header border-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>ALERTA CRÍTICA
                        </h5>
                        <a href="inventario?status=stock-bajo" class="btn-link text-warning font-mono" style="font-size: 0.7rem; text-decoration: none;">
                            VER TODOS <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="premium-list" id="lowStockProducts">
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <a href="inventario?status=stock-bajo" class="premium-list-item warning text-decoration-none">
                                    <div class="item-icon"><i class="fas fa-box"></i></div>
                                    <div class="item-info">
                                        <span class="item-sku font-mono"><?php echo htmlspecialchars($product['sku']); ?></span>
                                        <span class="item-name font-secondary d-block"><?php echo htmlspecialchars($product['nombre']); ?></span>
                                    </div>
                                    <div class="item-value font-mono danger"><?php echo htmlspecialchars($product['stock']); ?></div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-muted p-2 small">SISTEMA NOMINAL.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Currency Monitor (Placeholder) -->
            <div class="col-lg-4 mb-4 dashboard-item">
                <div class="buzz-card tech-effect tactical-card h-100">
                    <h5 class="font-primary text-info mb-3 section-header border-info">
                        <i class="fas fa-coins me-2"></i>MONITOR DE DIVISAS
                    </h5>
                    <?php if (!empty($allTasas)):
                        $bcv = $allTasas['bcv'] ?? null;
                        $euro = $allTasas['bcv_euro'] ?? null;
                        $usdt = $allTasas['binance_usdt'] ?? null;
                        $paralelo = $allTasas['paralelo'] ?? null;
                    ?>
                        <div class="currency-widget p-3 text-center">
                            <!-- Main Readout: BCV USD -->
                            <?php if ($bcv): ?>
                                <div class="currency-header mb-2">
                                    <span class="badge bg-info-subtle text-info px-3 py-1 font-mono" style="font-size: 0.7rem;">
                                        <i class="fas fa-university me-1"></i> BCV OFICIAL
                                    </span>
                                </div>
                                <div class="currency-value mb-3">
                                    <span class="text-muted d-block small font-mono mb-1">FLUJO PRINCIPAL: VES/USD</span>
                                    <h2 class="font-primary text-white mb-0 digital-readout" style="font-size: 2.2rem; color: #00f2ff;">
                                        Bs. <?php echo number_format($bcv['tasa'], 2, ',', '.'); ?>
                                    </h2>
                                </div>
                            <?php endif; ?>

                            <!-- Secondary Nodes: EUR, USDT, PARALELO -->
                            <div class="currency-grid">
                                <?php if ($euro): ?>
                                    <div class="currency-node euro">
                                        <span class="node-label">BCV EURO</span>
                                        <span class="node-value">Bs. <?php echo number_format($euro['tasa'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($usdt): ?>
                                    <div class="currency-node usdt">
                                        <span class="node-label">BINANCE USDT</span>
                                        <span class="node-value">Bs. <?php echo number_format($usdt['tasa'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($paralelo): ?>
                                    <div class="currency-node paralelo">
                                        <span class="node-label">ESTIMADO PARALELO</span>
                                        <span class="node-value">Bs. <?php echo number_format($paralelo['tasa'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="currency-footer border-top border-secondary-subtle pt-3 mt-3 d-flex justify-content-between align-items-center">
                                <span class="text-muted small font-mono">
                                    <i class="far fa-clock me-1"></i> ÚLTIMA SINCRONIZACIÓN:
                                    <?php
                                        $lastSync = !empty($allTasas) ? reset($allTasas)['creado_at'] : '00:00';
                                        echo date('H:i', strtotime($lastSync));
                                    ?>
                                </span>
                                <button class="btn btn-outline-info btn-sm p-1" onclick="syncBcv()" title="Sincronizar ahora" style="width: 30px; height: 30px;">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="currency-content empty-state">
                            <div class="empty-icon"><i class="fas fa-dollar-sign"></i></div>
                            <p class="font-mono text-muted mb-1">SIN TASAS SINCRONIZADAS.</p>
                            <p class="text-muted small mb-0">Pulsa el botón de sincronización para cargar BCV, Euro y USDT.</p>
                        </div>
                    <?php endif; ?>
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
