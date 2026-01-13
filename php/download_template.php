<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/sesion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Validate permissions (admin or supervisor)
if (!in_array($_SESSION['rol'], ['admin', 'supervisor'])) {
    die("Acceso denegado");
}

$type = $_GET['type'] ?? 'productos';

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // Remove default sheet

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C3E50']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];

function createSheet($spreadsheet, $title, $headers, $style) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($title);

    // Set Headers
    $col = 'A';
    foreach ($headers as $header => $desc) {
        $sheet->setCellValue($col . '1', $header);

        // Add comment/tooltip if description exists
        if (!empty($desc)) {
            $sheet->getComment($col . '1')->getText()->createTextRun($desc);
        }

        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }

    // Apply Style
    $lastCol = chr(ord('A') + count($headers) - 1);
    $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($style);

    return $sheet;
}

switch ($type) {
    case 'productos':
        $headers = [
            'SKU' => 'Identificador único (Obligatorio)',
            'Nombre Producto' => 'Nombre del producto (Obligatorio)',
            'Descripción' => 'Detalle del producto',
            'Precio Venta' => 'Precio final (Obligatorio)',
            'Costo Unitario' => 'Costo promedio',
            'Stock Inicial' => 'Cantidad actual',
            'Stock Mínimo' => 'Alerta de stock bajo',
            'Categoría' => 'Categoría (Se crea si no existe)',
            'Marca' => 'Marca (Se crea si no existe)',
            'Proveedor' => 'Proveedor principal',
            'Modelo' => 'Modelo o línea',
            'Código Barras' => 'Código EAN/UPC',
            'Peso (Kg)' => 'Peso unitario',
            'Medidas' => 'Dimensiones (LxWxH)',
            'Color' => 'Color principal',
            'Material' => 'Material principal'
        ];
        createSheet($spreadsheet, 'Productos', $headers, $headerStyle);
        $filename = 'Plantilla_Productos_BuzzDM.xlsx';
        break;

    case 'clientes':
        $headers = [
            'Documento ID (RIF/CI)' => 'Cédula o RIF (Obligatorio, Único)',
            'Nombre / Razón Social' => 'Nombre completo (Obligatorio)',
            'Email' => 'Correo electrónico',
            'Teléfono' => 'Número de contacto',
            'Dirección' => 'Dirección fiscal o envío',
            'Es VIP' => '1 = Sí, 0 = No'
        ];
        createSheet($spreadsheet, 'Clientes', $headers, $headerStyle);
        $filename = 'Plantilla_Clientes_BuzzDM.xlsx';
        break;

    case 'proveedores':
        $headers = [
            'Nombre Empresa' => 'Nombre único (Obligatorio)',
            'RIF / Documento' => 'Documento fiscal',
            'Persona Contacto' => 'Nombre del contacto',
            'Email' => 'Correo corporativo',
            'Teléfono' => 'Teléfono principal',
            'Dirección' => 'Dirección fiscal'
        ];
        createSheet($spreadsheet, 'Proveedores', $headers, $headerStyle);
        $filename = 'Plantilla_Proveedores_BuzzDM.xlsx';
        break;

    case 'catalogos':
        // Categorias Sheet
        $catHeaders = [
            'Nombre Categoría' => 'Nombre único (Obligatorio)',
            'Descripción' => 'Descripción detallada'
        ];
        createSheet($spreadsheet, 'Categorias', $catHeaders, $headerStyle);

        // Marcas Sheet
        $brandHeaders = [
            'Nombre Marca' => 'Nombre único (Obligatorio)',
            'Sitio Web' => 'URL completa',
            'Descripción' => 'Descripción detallada'
        ];
        createSheet($spreadsheet, 'Marcas', $brandHeaders, $headerStyle);

        $filename = 'Plantilla_Catalogos_BuzzDM.xlsx';
        break;

    default:
        die("Tipo de plantilla no válido.");
}

// Redirect output to a client’s web browser (Xlsx)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
