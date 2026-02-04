<?php
// reports.php - Overhauled for aesthetic refinements and consolidated multi-reports

require_once 'sesion.php';
require_once 'db.php';
require_once '../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

try {
    $pdo = getDbConnection();

    // Verify Admin Password
    $password = $_GET['password'] ?? '';
    if (empty($password)) {
        die("<h1 style='color:#003366; font-family:sans-serif;'>Acceso Denegado</h1><p>Se requiere contraseña de administrador.</p>");
    }

    $stmtAuth = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmtAuth->execute([$_SESSION['usuario_id']]);
    $user = $stmtAuth->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        die("<h1 style='color:#003366; font-family:sans-serif;'>Acceso Denegado</h1><p>Contraseña incorrecta.</p>");
    }

    // DomPDF Options
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('isPhpEnabled', true); // Enable PHP for dynamic page numbering
    $dompdf = new Dompdf($options);

    // Get Company Data
    $stmtEmpresa = $pdo->query("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa LIMIT 1");
    $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

    // Setup URLs for assets
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptRoot = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
    $baseUrl = $protocol . "://" . $host . rtrim($scriptRoot, '/') . '/';

    // Logo path - use company logo if available, otherwise default
    $logoFile = (!empty($empresa['logo_svg'])) ? $empresa['logo_svg'] : 'assets/img/logov1.png';
    $rutaLogo = $baseUrl . $logoFile;

    // Handle multiple report types
    $tiposRaw = $_GET['tipo'] ?? '';
    $tiposArr = array_filter(explode(',', $tiposRaw));

    if (empty($tiposArr)) {
        die("No se especificaron tipos de reporte.");
    }

    // Build consolidated HTML
    $fullHtml = '<html><head><style>' . getReportStyles() . '</style></head><body>';

    foreach ($tiposArr as $index => $tipo) {
        if ($index > 0) {
            $fullHtml .= '<div style="page-break-before: always;"></div>';
        }

        $reportData = fetchReportData($pdo, $tipo);
        $fullHtml .= generateReportSection($tipo, $reportData, $empresa, $rutaLogo);
    }

    // Add footer script ONCE at the end (not per report section)
    $generationDateTime = date('d/m/Y H:i:s');
    $fullHtml .= '
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("Helvetica", "normal");
            $size = 8;
            $pageText = "pág " . $PAGE_NUM . " de " . $PAGE_COUNT;
            $x = 520;
            $y = 815;
            $pdf->page_text($x, $y, $pageText, $font, $size, array(0.4, 0.4, 0.4));
            $pdf->page_text(50, $y, "Generado: ' . $generationDateTime . ' | BuzzDM", $font, $size, array(0.4, 0.4, 0.4));
        }
    </script>';

    $fullHtml .= '</body></html>';

    $dompdf->loadHtml($fullHtml);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $fileName = "Reporte_BuzzDM_" . date('Ymd_His') . ".pdf";
    $dompdf->stream($fileName, ["Attachment" => false]);

} catch (Exception $e) {
    error_log("Report Error: " . $e->getMessage());
    die("<h1 style='color:red;'>Error al generar el reporte</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>");
}

// --- Helper Functions ---

function getReportStyles() {
    return "
        @page { margin: 1.5cm 1cm 1.5cm 1cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.4; font-size: 10pt; }
        .header { border-bottom: 2px solid #003366; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; border: none; }
        .logo { max-height: 60px; }
        .company-info { text-align: right; font-size: 9pt; color: #666; }
        .report-hd { background-color: #003366; color: white; padding: 10px; margin-bottom: 15px; border-left: 5px solid #FFA000; }
        .report-hd h1 { margin: 0; font-size: 16pt; text-transform: uppercase; letter-spacing: 1px; }
        .report-hd .date { font-size: 8pt; opacity: 0.8; }

        table.data { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        table.data th { background-color: #f4f4f4; color: #003366; font-weight: bold; text-align: left; padding: 8px; border-bottom: 2px solid #003366; font-size: 9pt; }
        table.data td { padding: 7px; border-bottom: 1px solid #eee; word-wrap: break-word; font-size: 8.5pt; vertical-align: top; }
        tr:nth-child(even) { background-color: #fafafa; }

        .summary-box { background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .summary-item { display: inline-block; width: 30%; font-size: 11pt; }
        .summary-label { display: block; font-size: 8pt; color: #666; text-transform: uppercase; margin-bottom: 2px; }
        .text-right { text-align: right; }
        .paid-detail-title { color: #003366; font-size: 12pt; border-bottom: 1px solid #003366; margin-top: 20px; padding-bottom: 5px; }
    ";
}

function fetchReportData($pdo, $tipo) {
    switch ($tipo) {
        case 'historial_acciones':
            return $pdo->query("SELECT u.nombre AS usuario, l.accion, l.detalles, l.fecha FROM logs l LEFT JOIN usuarios u ON l.usuario_id = u.id ORDER BY l.fecha DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

        case 'historial_importaciones':
            return $pdo->query("SELECT registro_id, operacion, detalles, fecha, usuario FROM v_op_import ORDER BY fecha DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

        case 'pedidos':
            return $pdo->query("SELECT p.referencia, c.nombre AS cliente, p.fecha_creacion, p.total, p.estado_pago, p.estado_envio FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id ORDER BY p.fecha_creacion DESC LIMIT 150")->fetchAll(PDO::FETCH_ASSOC);

        case 'clientes':
            return $pdo->query("SELECT id, nombre, email, telefono, vip FROM clientes ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        case 'inventario':
            return $pdo->query("SELECT sku, nombre, precio, stock, estado FROM productos ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

        case 'historial_login':
            return $pdo->query("SELECT u.nombre AS usuario, ll.accion, ll.detalles, ll.fecha FROM logs_login ll LEFT JOIN usuarios u ON ll.usuario_id = u.id ORDER BY ll.fecha DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

        case 'inventario_critico':
            return $pdo->query("SELECT sku, nombre, stock, precio FROM productos WHERE stock <= 10 AND estado = 'activo' ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);

        case 'resumen_financiero':
            $data = [];
            $data['totales'] = $pdo->query("SELECT estado_pago, COUNT(*) as cantidad, SUM(total) as monto FROM pedidos GROUP BY estado_pago")->fetchAll(PDO::FETCH_ASSOC);
            $data['pagados'] = $pdo->query("SELECT p.id, p.fecha_creacion, p.total FROM pedidos p WHERE p.estado_pago = 'pagado' ORDER BY p.fecha_creacion DESC LIMIT 30")->fetchAll(PDO::FETCH_ASSOC);
            $data['pendientes'] = $pdo->query("SELECT p.referencia, c.nombre as cliente, p.total, p.fecha_creacion FROM pedidos p JOIN clientes c ON p.cliente_id = c.id WHERE p.estado_pago = 'pendiente' ORDER BY p.fecha_creacion DESC LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
            return $data;

        default:
            return [];
    }
}

function generateReportSection($tipo, $datos, $empresa, $rutaLogo) {
    $titulos = [
        'historial_acciones' => ['title' => 'Historial de Acciones', 'icon' => 'history'],
        'historial_importaciones' => ['title' => 'Historial de Importaciones', 'icon' => 'file-import'],
        'pedidos' => ['title' => 'Reporte de Pedidos', 'icon' => 'shopping-cart'],
        'clientes' => ['title' => 'Directorio de Clientes', 'icon' => 'users'],
        'inventario' => ['title' => 'Inventario de Productos', 'icon' => 'boxes'],
        'historial_login' => ['title' => 'Inicios de Sesión', 'icon' => 'sign-in-alt'],
        'inventario_critico' => ['title' => 'Alerta de Inventario Crítico', 'icon' => 'exclamation-triangle'],
        'resumen_financiero' => ['title' => 'Resumen Financiero', 'icon' => 'file-invoice-dollar']
    ];

    $info = $titulos[$tipo] ?? ['title' => 'Reporte', 'icon' => 'file'];
    $html = '
    <div class="header">
        <table>
            <tr>
                <td><img src="'.$rutaLogo.'" class="logo"></td>
                <td class="company-info">
                    <strong>'.htmlspecialchars($empresa['nombre'] ?? 'BUZZ DATA MANAGER').'</strong><br>
                    '.htmlspecialchars($empresa['rif'] ?? '').'<br>
                    '.htmlspecialchars($empresa['direccion'] ?? '').'<br>
                    '.htmlspecialchars($empresa['email'] ?? '').' | '.htmlspecialchars($empresa['telefono'] ?? '').'
                </td>
            </tr>
        </table>
    </div>

    <div class="report-hd">
        <h1>'.$info['title'].'</h1>
        <span class="date">Fecha de generación: '.date('d/m/Y H:i').'</span>
    </div>';

    if ($tipo === 'resumen_financiero') {
        $html .= generateFinancialLayout($datos);
    } else {
        $html .= generateDefaultTable($tipo, $datos);
    }
    // Dynamic page counter removed - now handled globally in reports.php main loop

    return $html;
}

function generateDefaultTable($tipo, $datos) {
    if (empty($datos)) return '<p style="text-align:center; padding: 20px;">No se encontraron registros para este periodo.</p>';

    $cols = array_keys($datos[0]);
    $html = '<table class="data"><thead><tr>';
    foreach ($cols as $col) {
        $html .= '<th>' . normalizeHeader($col, $tipo) . '</th>';
    }
    $html .= '</tr></thead><tbody>';

    foreach ($datos as $fila) {
        $html .= '<tr>';
        foreach ($fila as $key => $val) {
            $cellContent = normalizeValue($key, $val, $tipo);
            $html .= '<td>' . $cellContent . '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';
    return $html;
}

function normalizeHeader($key, $tipo) {
    $map = [
        'id' => ($tipo === 'clientes' ? 'CÉDULA/RIF' : 'ID'),
        'referencia' => 'REFERENCIA',
        'cliente' => 'CLIENTE',
        'fecha_creacion' => 'FECHA',
        'total' => 'MONTO TOTAL',
        'estado_pago' => 'ESTADO PAGO',
        'estado_envio' => 'ESTADO ENVÍO',
        'nombre' => 'NOMBRE',
        'email' => 'EMAIL',
        'telefono' => 'TELÉFONO',
        'vip' => 'VIP',
        'sku' => 'SKU',
        'precio' => 'PRECIO',
        'stock' => 'EXISTENCIA',
        'estado' => 'ESTADO',
        'usuario' => 'USUARIO',
        'accion' => 'ACCIÓN',
        'detalles' => 'DETALLES',
        'fecha' => 'FECHA/HORA',
        'registro_id' => 'ID REG.',
        'operacion' => 'OPERACIÓN'
    ];
    return $map[strtolower($key)] ?? strtoupper(str_replace('_', ' ', $key));
}

function normalizeValue($key, $val, $tipo) {
    if ($key === 'vip') {
        return $val ? '<strong>SÍ</strong>' : 'NO';
    }
    if (in_array($key, ['estado_pago', 'estado_envio', 'estado'])) {
        return ucfirst(str_replace('_', ' ', $val));
    }
    if (in_array($key, ['precio', 'total', 'monto'])) {
        return '$' . number_format((float)$val, 2);
    }
    if ($key === 'detalles' && strpos($val, '{') === 0) {
        return formatLogDetails($val);
    }
    return htmlspecialchars($val);
}

function formatLogDetails($json) {
    $d = json_decode($json, true);
    if (!$d) return htmlspecialchars($json);
    $parts = [];
    foreach ($d as $k => $v) {
        $label = ucfirst(str_replace(['_', 'ms', 'mb'], [' ', ' ms', ' MB'], $k));
        $val = is_array($v) ? implode(', ', $v) : $v;
        if ($k === 'tamanio_mb') $val .= ' MB';
        if ($k === 'duracion_ms') $val .= ' ms';
        $parts[] = "<strong>$label:</strong> $val";
    }
    return implode(' | ', $parts);
}

function generateFinancialLayout($datos) {
    $html = '<div class="summary-box">';
    $totalGeneral = 0;
    foreach ($datos['totales'] as $t) {
        $totalGeneral += $t['monto'];
        $html .= '<div class="summary-item">
            <span class="summary-label">' . normalizeValue('estado_pago', $t['estado_pago'], 'resumen') . '</span>
            <strong>$' . number_format($t['monto'], 2) . '</strong> (' . $t['cantidad'] . ')
        </div>';
    }
    $html .= '<div class="summary-item text-right">
        <span class="summary-label">TOTAL FACTURADO</span>
        <strong style="color:#003366; font-size: 14pt;">$' . number_format($totalGeneral, 2) . '</strong>
    </div>';
    $html .= '</div>';

    if (!empty($datos['pagados'])) {
        $html .= '<h3 class="paid-detail-title">Detalle de Ventas Cobradas (ID, Fecha, Monto)</h3>';
        $html .= generateDefaultTable('pedidos_pagados', $datos['pagados']);
    }

    $html .= '<h3 class="paid-detail-title">Próximos Cobros (Pedidos Pendientes)</h3>';
    $html .= generateDefaultTable('pedidos_mini', $datos['pendientes']);

    return $html;
}
