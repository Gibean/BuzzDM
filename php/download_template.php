<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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

// Format constants
$FMT_TEXT = NumberFormat::FORMAT_TEXT;              // @
$FMT_NUM2 = NumberFormat::FORMAT_NUMBER_00;         // 0.00
$FMT_NUM3 = '0.000';
$FMT_INT  = '0';
$FMT_USD  = '$#,##0.00';                            // Dollar currency

function createSheet($spreadsheet, $title, $headers, $style, $formats = []) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($title);

    // Set Headers
    $col = 'A';
    $colIndex = 0;
    foreach ($headers as $header => $desc) {
        $sheet->setCellValue($col . '1', $header);

        // Add comment/tooltip if description exists
        if (!empty($desc)) {
            $sheet->getComment($col . '1')->getText()->createTextRun($desc);
        }

        $sheet->getColumnDimension($col)->setAutoSize(true);

        // Apply column format to data rows (2-1000)
        if (isset($formats[$colIndex])) {
            $sheet->getStyle($col . '2:' . $col . '1000')
                  ->getNumberFormat()
                  ->setFormatCode($formats[$colIndex]);
        }

        $col++;
        $colIndex++;
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
            'Pref. Venta' => 'Precio final en USD (Obligatorio)',
            'Pref. Costo' => 'Costo de compra promedio en USD',
            'Stock Inicial' => 'Cantidad inicial en inventario',
            'Stock Mínimo' => 'Alerta de stock bajo (default: 10)',
            'Categoría' => 'Nombre de categoría (se crea si no existe, ver Hoja de Ref.)',
            'Marca' => 'Nombre de marca (se crea si no existe, ver Hoja de Ref.)',
            'Proveedor' => 'Nombre del proveedor (se crea si no existe, ver Hoja de Ref.)',
            'Modelo' => 'Modelo o línea del producto',
            'Color' => 'Color principal del producto (texto libre)',
            'Material' => 'Material principal del producto (texto libre)',
            'Tamaño' => 'Talla o tamaño (ej: S, M, L, XL)',
            'Peso (Kg)' => 'Peso unitario en kilogramos',
            'Medidas' => 'Dimensiones (LxWxH en cm)',
            'Unidad Venta' => 'UND, KG, LT, MT, M2, PACK, CAJA o PAR (default: UND)',
            'Cantidad Presentación' => 'Unidades por presentación (default: 1)'
        ];
        // Formats:  SKU    Nombre  Desc  Precio  Costo  StockI  StockM  Cat   Marca  Prov   Modelo Color  Mat   Tamaño Peso   Medidas Unidad CantPres
        $formats = [$FMT_TEXT, null, null, $FMT_USD, $FMT_USD, $FMT_INT, $FMT_INT, null, null, null, $FMT_TEXT, null, null, null, $FMT_NUM3, null, null, $FMT_INT];
        createSheet($spreadsheet, 'Productos', $headers, $headerStyle, $formats);
        $filename = 'Plantilla_Productos_BuzzDM.xlsx';
        break;

    case 'clientes':
        $headers = [
            'Documento ID (RIF/CI)' => 'Cédula o RIF (Obligatorio, Único)',
            'Nombre / Razón Social' => 'Nombre completo (Obligatorio)',
            'Email' => 'Correo electrónico',
            'Teléfono' => 'Número de contacto con código de país (ej: +58412...)',
            'Dirección' => 'Dirección fiscal o envío',
            'Es VIP' => '1 = Sí, 0 = No',
            'Notas' => 'Observaciones internas sobre el cliente'
        ];
        // Formats:  DocID      Nombre  Email  Tel        Dir   VIP      Notas
        $formats = [$FMT_TEXT, null,   null,  $FMT_TEXT, null, $FMT_INT, null];
        createSheet($spreadsheet, 'Clientes', $headers, $headerStyle, $formats);
        $filename = 'Plantilla_Clientes_BuzzDM.xlsx';
        break;

    case 'proveedores':
        $headers = [
            'Nombre Empresa' => 'Nombre único (Obligatorio)',
            'RIF / Documento' => 'Documento fiscal (ej: J-12345678-9)',
            'Persona Contacto' => 'Nombre de la persona de contacto',
            'Email' => 'Correo corporativo',
            'Teléfono' => 'Teléfono principal con código de país',
            'Dirección' => 'Dirección fiscal',
            'Notas' => 'Observaciones internas sobre el proveedor'
        ];
        // Formats:  Nombre  RIF        Contacto Email  Tel        Dir  Notas
        $formats = [null, $FMT_TEXT, null,    null,  $FMT_TEXT, null, null];
        createSheet($spreadsheet, 'Proveedores', $headers, $headerStyle, $formats);
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

    case 'referencias':
        $pdo = Database::getConnection();

        $refStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFA000']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];

        $refSheets = [
            'Categorías' => ['query' => 'SELECT id, nombre, descripcion FROM categorias ORDER BY nombre', 'headers' => ['ID', 'Nombre', 'Descripción']],
            'Marcas'     => ['query' => 'SELECT id, nombre, sitio_web, descripcion FROM marcas ORDER BY nombre', 'headers' => ['ID', 'Nombre', 'Sitio Web', 'Descripción']],
            'Proveedores'=> ['query' => 'SELECT id, nombre, rif FROM proveedores WHERE deleted_at IS NULL ORDER BY nombre', 'headers' => ['ID', 'Nombre', 'RIF']],
            'Colores'    => ['query' => 'SELECT id, nombre, codigo_hex FROM colores ORDER BY nombre', 'headers' => ['ID', 'Nombre', 'Código Hex']],
            'Materiales' => ['query' => 'SELECT id, nombre FROM materiales ORDER BY nombre', 'headers' => ['ID', 'Nombre']],
            'Almacenes'  => ['query' => 'SELECT id, codigo, nombre, tipo FROM almacenes WHERE activo = 1 ORDER BY nombre', 'headers' => ['ID', 'Código', 'Nombre', 'Tipo']],
        ];

        foreach ($refSheets as $sheetTitle => $cfg) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetTitle);

            // Headers
            $col = 'A';
            foreach ($cfg['headers'] as $h) {
                $sheet->setCellValue($col . '1', $h);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }
            $lastCol = chr(ord('A') + count($cfg['headers']) - 1);
            $sheet->getStyle('A1:' . $lastCol . '1')->applyFromArray($refStyle);

            // Data
            $rows = $pdo->query($cfg['query'])->fetchAll(PDO::FETCH_NUM);
            $rowNum = 2;
            foreach ($rows as $row) {
                $col = 'A';
                foreach ($row as $val) {
                    $sheet->setCellValue($col . $rowNum, $val);
                    $col++;
                }
                $rowNum++;
            }
        }

        $filename = 'Hoja_Referencias_BuzzDM.xlsx';
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
