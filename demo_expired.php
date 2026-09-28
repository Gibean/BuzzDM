<?php
/**
 * Pantalla de Expiración de Prueba Demo
 * demo_expired.php
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/db.php';
require_once __DIR__ . '/php/src/Service/DemoExportService.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$companyId = (int)($_SESSION['empresa_id'] ?? 0);
$companyName = $_SESSION['empresa_nombre'] ?? 'Tu Empresa';

// Procesar solicitud de descarga del respaldo Excel
if (isset($_GET['action']) && $_GET['action'] === 'export_excel' && $companyId > 0) {
    try {
        $exportService = new DemoExportService();
        $filePath = $exportService->generateCompanyDataExport($companyId);

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            @unlink($filePath);
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = "Error al generar la exportación: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Período de Prueba Finalizado - BuzzDM</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .expired-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            max-width: 600px;
            width: 90%;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.4);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: #f59e0b;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="expired-card">
        <div class="icon-circle">
            <i class="fas fa-hourglass-end"></i>
        </div>

        <h3 class="fw-bold mb-2">Período de Prueba Finalizado</h3>
        <p class="text-secondary mb-4">
            El período de 15 días de demostración para <strong><?php echo htmlspecialchars($companyName); ?></strong> ha concluido.
        </p>

        <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger py-2 mb-3 font-monospace small"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <div class="p-3 rounded mb-4 text-start" style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255,255,255,0.06); font-size: 0.88rem;">
            <div class="d-flex align-items-center gap-2 mb-2 text-warning fw-semibold">
                <i class="fas fa-shield-alt"></i>
                <span>Período de Gracia: 3 Días</span>
            </div>
            <p class="text-secondary mb-0">
                Tus datos registrados (productos, clientes, proveedores) se conservarán durante 3 días para que puedas descargarlos libremente antes de la limpieza automática del servidor.
            </p>
        </div>

        <div class="d-grid gap-2 mb-4">
            <a href="demo_expired.php?action=export_excel" class="btn btn-warning py-2 fw-semibold text-dark">
                <i class="fas fa-file-excel me-2"></i> Descargar Respaldo Completo (Excel)
            </a>
            <a href="https://forms.gle/" target="_blank" class="btn btn-outline-secondary py-2 text-light">
                <i class="fas fa-poll-h me-2"></i> Completar Encuesta de Feedback (2 min)
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 small text-secondary">
            <span>BuzzDM SaaS Demo</span>
            <a href="php/logout.php" class="text-decoration-none text-warning">
                <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</body>
</html>
