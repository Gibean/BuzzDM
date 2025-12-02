<?php
// reports.php

// Autenticación y sesión
require_once 'sesion.php'; // Usa sesion.php para validar la sesión
require_once 'db.php';          // Conexión a la base de datos
require_once '../vendor/autoload.php'; // Carga DomPDF

use Dompdf\Dompdf;
use Dompdf\Exception as DomException;
use Dompdf\Options;

try {
    // Initialize PDO connection
    $pdo = getDbConnection();

    // Verify Admin Password
    $password = isset($_GET['password']) ? $_GET['password'] : '';
    if (empty($password)) {
        die("<h1>Acceso Denegado</h1><p>Se requiere contraseña de administrador.</p>");
    }

    $stmtAuth = $pdo->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmtAuth->execute([$_SESSION['usuario_id']]);
    $user = $stmtAuth->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        die("<h1>Acceso Denegado</h1><p>Contraseña incorrecta.</p>");
    }

    // Configuración de DomPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Permite cargar imágenes desde URLs/rutas
    $dompdf = new Dompdf($options);

    // --- Obtener datos de la empresa para el reporte ---
    $stmtEmpresa = $pdo->query("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa LIMIT 1");
    $empresa = $stmtEmpresa->fetch(PDO::FETCH_ASSOC);

    // Rutas a las imágenes - dynamic base URL detection
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'])); // Get parent directory of the script
    $baseUrl = $protocol . "://" . $host . $scriptPath . '/';
    $rutaLogo = $empresa && !empty($empresa['logo_svg']) ? $baseUrl . 'uploads/' . $empresa['logo_svg'] : $baseUrl . 'assets/img/default_logo.png';
    $rutaBanner = $baseUrl . 'assets/img/banner.png';

    // Función para generar el HTML del reporte
    function generarReporteHTML($tipoReporte, $datos, $empresa, $rutaLogo, $rutaBanner) {
        $titulos = [
            'historial_acciones' => 'Historial de Acciones del Sistema',
            'historial_importaciones' => 'Historial de Importaciones',
            'pedidos' => 'Reporte de Pedidos',
            'clientes' => 'Directorio de Clientes',
            'inventario' => 'Inventario de Productos',
            'historial_login' => 'Historial de Inicios de Sesión'
        ];

        $encabezados = [
            'historial_acciones' => ['Usuario', 'Acción', 'Detalles', 'Fecha'],
            'historial_importaciones' => ['ID', 'Operación', 'Detalles', 'Fecha', 'Usuario'],
            'pedidos' => ['ID', 'Referencia', 'Cliente', 'Fecha', 'Total', 'Estado Pago', 'Estado Envío'],
            'clientes' => ['ID', 'Nombre', 'Email', 'Teléfono', 'Dirección', 'VIP'],
            'inventario' => ['SKU', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Estado'],
            'historial_login' => ['ID', 'Usuario', 'Acción', 'Detalles', 'Fecha']
        ];

        if (!isset($titulos[$tipoReporte])) {
            throw new Exception("Tipo de reporte no válido.");
        }

        $tituloReporte = htmlspecialchars($titulos[$tipoReporte]);
        $fechaGeneracion = date('d/m/Y H:i:s');
        $nombreEmpresa = htmlspecialchars($empresa['nombre'] ?? 'Nombre de la Empresa');
        $direccionEmpresa = htmlspecialchars($empresa['direccion'] ?? 'Dirección');
        $telefonoEmpresa = htmlspecialchars($empresa['telefono'] ?? 'N/A');
        $emailEmpresa = htmlspecialchars($empresa['email'] ?? 'N/A');

        $tablaEncabezados = '';
        foreach ($encabezados[$tipoReporte] as $encabezado) {
            $tablaEncabezados .= '<th>' . htmlspecialchars($encabezado) . '</th>';
        }

        $tablaFilas = '';
        if (empty($datos)) {
            $tablaFilas .= '<tr><td colspan="' . count($encabezados[$tipoReporte]) . '" style="text-align:center;">No hay datos disponibles para este reporte.</td></tr>';
        } else {
            foreach ($datos as $fila) {
                $tablaFilas .= '<tr>';
                foreach ($fila as $key => $valor) {
                    if ($key === 'vip') {
                        $valor = $valor ? 'Sí' : 'No';
                    }
                    $tablaFilas .= '<td>' . htmlspecialchars($valor) . '</td>';
                }
                $tablaFilas .= '</tr>';
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$tituloReporte}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <!-- Added Font Awesome CSS -->
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            position: fixed;
            top: -40px;
            left: 0px;
            right: 0px;
            height: 100px;
            text-align: center;
        }
        .logo {
            position: absolute;
            top: 0;
            left: 0;
            width: 80px;
            height: auto;
        }
        .banner {
            width: 100%;
            height: 80px;
            background-image: url('{$rutaBanner}');
            background-repeat: repeat-x;
            background-size: contain;
            opacity: 0.5;
        }
        .title {
            text-align: center;
            color: #003366;
            margin-top: 80px;
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: bold;
        }
        .title i { /* Style for the icon next to the title */
            margin-right: 10px;
            color: #FFD700; /* Match the yellow theme */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            word-wrap: break-word;
        }
        th {
            background-color: #FFD700;
            color: #003366;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .footer .company-info {
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{$rutaLogo}" alt="Logo Empresa" class="logo">
        <div class="banner"></div>
    </div>

    <h1 class="title">{$tituloReporte}</h1> <!-- Added Font Awesome icon -->

    <table>
        <thead>
            <tr>{$tablaEncabezados}</tr>
        </thead>
        <tbody>{$tablaFilas}</tbody>
    </table>

    <div class="footer">
        Reporte generado el {$fechaGeneracion} | Sistema dbuzz <br>
        <div class="company-info">
            {$nombreEmpresa} | {$direccionEmpresa} | Tel: {$telefonoEmpresa} | Email: {$emailEmpresa}
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    $tipoReporte = isset($_GET['tipo']) ? $_GET['tipo'] : '';
    $reportesPermitidos = ['historial_acciones', 'historial_importaciones', 'pedidos', 'clientes', 'inventario', 'historial_login'];
    if (!in_array($tipoReporte, $reportesPermitidos)) {
        die("Tipo de reporte no especificado o no válido.");
    }

    switch ($tipoReporte) {
        case 'historial_acciones':
            $sql = "SELECT u.nombre AS nombre_usuario, l.accion, l.detalles, l.fecha FROM logs l LEFT JOIN usuarios u ON l.usuario_id = u.id ORDER BY l.fecha DESC";
            break;
        case 'historial_importaciones':
            $sql = "SELECT registro_id, operacion, detalles, fecha, usuario FROM v_op_import ORDER BY fecha DESC";
            break;
        case 'pedidos':
            $sql = "SELECT p.id, p.referencia, c.nombre AS cliente_nombre, p.fecha_creacion, p.total, p.estado_pago, p.estado_envio FROM pedidos p LEFT JOIN clientes c ON p.cliente_id = c.id ORDER BY p.fecha_creacion DESC";
            break;
        case 'clientes':
            $sql = "SELECT id, nombre, email, telefono, direccion, vip FROM clientes ORDER BY nombre";
            break;
        case 'inventario':
            $sql = "SELECT sku, nombre, descripcion, precio, stock, estado FROM productos ORDER BY nombre";
            break;
        case 'historial_login':
            $sql = "SELECT ll.id, u.nombre AS nombre_usuario, ll.accion, ll.detalles, ll.fecha FROM logs_login ll LEFT JOIN usuarios u ON ll.usuario_id = u.id ORDER BY ll.fecha DESC";
            break;
    }

    $stmt = $pdo->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = generarReporteHTML($tipoReporte, $datos, $empresa, $rutaLogo, $rutaBanner);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $nombreArchivo = "reporte_{$tipoReporte}_". date('YmdHis') . ".pdf";
    $dompdf->stream($nombreArchivo, ["Attachment" => false]);

} catch (DomException $e) {
    error_log("Error de DomPDF: " . $e->getMessage());
    die("<h1>Error al generar el PDF</h1><p>Detalles: " . htmlspecialchars($e->getMessage()) . "</p>");
} catch (PDOException $e) {
    error_log("Error de Base de Datos: " . $e->getMessage());
    die("<h1>Error de Base de Datos</h1><p>Detalles: " . htmlspecialchars($e->getMessage()) . "</p>");
} catch (Exception $e) {
    error_log("Error Inesperado: " . $e->getMessage());
    die("<h1>Error Inesperado</h1><p>Detalles: " . htmlspecialchars($e->getMessage()) . "</p>");
}
?>
