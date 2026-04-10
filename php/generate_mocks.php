<?php
/**
 * BUZZDM MOCK DATA GENERATOR
 * Generates CSV files for import and a massive SQL file for historical transactions.
 */
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$outDir = __DIR__ . '/../db/mock_data/';
if (!is_dir($outDir)) mkdir($outDir, 0777, true);

echo "Iniciando generación de datos Mock...\n";

// 1. INITIALIZE MASTER SPREADSHEET
$spreadsheet = new Spreadsheet();

// --- 1. SPREADSHEET: CATEGORIAS ---
echo "Generando pestaña Categorias...\n";
$sheetCat = $spreadsheet->getActiveSheet();
$sheetCat->setTitle('Categorias');
$sheetCat->fromArray(['NombreCategoria', 'Descripcion'], NULL, 'A1');
$categorias = [
    "Literatura Juvenil", "Ficción y Fantasía", "Literatura Clásica", "Autoayuda", "Desarrollo Personal",
    "Papelería Escolar", "Papelería de Oficina", "Arte y Dibujo", "Materiales Técnicos", "Adhesivos y Cintas",
    "Cuadernos y Libretas", "Escritura", "Marcadores y Resaltadores", "Organizadores",
    "Pinturas y Pinceles", "Geometría", "Calculadoras", "Bolsos y Estuches",
    "Regalos y Novedades", "Didácticos"
];
$row = 2;
foreach ($categorias as $cat) {
    $sheetCat->fromArray([$cat, "Artículos relacionados con $cat"], NULL, 'A' . $row++);
}

// --- 2. SPREADSHEET: MARCAS ---
echo "Generando pestaña Marcas...\n";
$sheetMar = $spreadsheet->createSheet();
$sheetMar->setTitle('Marcas');
$sheetMar->fromArray(['NombreMarca', 'SitioWeb', 'Descripcion'], NULL, 'A1');
$marcas = [
    "Norma", "Mongol", "Kores", "Solita", "Alpes", "Kilométrico", "Scribe",
    "Paper Mate", "Faber-Castell", "Pegaso", "Rescate", "Prismacolor", "UHU",
    "Tilibra", "Cubic", "Ofimak", "Oveja", "Alpino", "Edding", "Artline"
];
$row = 2;
foreach ($marcas as $marca) {
    $sheetMar->fromArray([$marca, "www." . strtolower(str_replace(' ', '', $marca)) . ".com.ve", "Marca de papelería $marca"], NULL, 'A' . $row++);
}

// --- 3. SPREADSHEET: PROVEEDORES (Venezuelan context) ---
echo "Generando pestaña Proveedores...\n";
$proveedores = [];
$sheetProv = $spreadsheet->createSheet();
$sheetProv->setTitle('Proveedores');
$sheetProv->fromArray(['NombreEmpresa', 'RifDocumento', 'PersonaContacto', 'Email', 'Telefono', 'Direccion', 'Notas'], NULL, 'A1');
$nombresProv = [
    "Distribuidora El Juguetón C.A.", "Papelería Mayorista Los Andes", "Comercializadora Sigo", 
    "Inversiones EPA C.A.", "Distribuidora Norma de Venezuela", "Kores de Venezuela", 
    "Suministros Ofimak", "Papeleras Integradas C.A.", "Inversiones Solita", "Distribuidora Central"
];
$row = 2;
for ($i = 0; $i < count($nombresProv); $i++) {
    $nombre = $nombresProv[$i];
    $rif = "J-" . rand(20000000, 49999999) . "-" . rand(0, 9);
    $contacto = ['Juan', 'Maria', 'Carlos', 'Ana', 'Luis'][rand(0, 4)] . ' ' . ['Perez', 'Gomez', 'Rodriguez', 'Fernandez', 'Lopez'][rand(0, 4)];
    $email = "ventas" . rand(10, 99) . "@" . strtolower(str_replace([' ', '.', 'C.A', 'c.a'], '', $nombre)) . ".com.ve";
    $telefono = "04" . ['14', '24', '12', '16', '26'][rand(0, 4)] . "-" . rand(1000000, 9999999);
    
    $proveedores[] = ['id' => $i+1, 'nombre' => $nombre];
    $sheetProv->fromArray([$nombre, $rif, $contacto, $email, $telefono, "Av. Principal, Edificio Comercial Nro " . rand(1, 100) . " Caracas", "Provedor Estratégico de Vzla"], NULL, 'A' . $row++);
}

// Fill up to 30 suppliers
for ($i = count($nombresProv); $i < 30; $i++) {
    $nombre = "Inversiones y Suministros Comercial " . rand(100, 999) . " C.A.";
    $rif = "J-" . rand(30000000, 49999999) . "-" . rand(0, 9);
    $contacto = ['Jose', 'Carmen', 'Jesus', 'Marcos', 'Laura'][rand(0, 4)] . ' ' . ['Mendoza', 'Marquez', 'Sosa', 'Sanchez', 'Castillo'][rand(0, 4)];
    $email = "contacto@" . strtolower(str_replace([' ', '.'], '', $nombre)) . ".com.ve";
    $telefono = "04" . ['14', '24', '12', '16', '26'][rand(0, 4)] . "-" . rand(1000000, 9999999);
    
    $proveedores[] = ['id' => $i+1, 'nombre' => $nombre];
    $sheetProv->fromArray([$nombre, $rif, $contacto, $email, $telefono, "Zona Industrial, Galpón " . rand(1, 40) . " Valencia", "Proveedor regional"], NULL, 'A' . $row++);
}

// --- 4. SPREADSHEET: CLIENTES ---
echo "Generando pestaña Clientes...\n";
$clientes = [];
$sheetCli = $spreadsheet->createSheet();
$sheetCli->setTitle('Clientes');
$sheetCli->fromArray(['DocumentoID', 'NombreRazon', 'Email', 'Telefono', 'Direccion', 'EsVIP', 'Notas'], NULL, 'A1');
$row = 2;
for ($i = 1; $i <= 250; $i++) {
    $nombres = ['Juan', 'Maria', 'Carlos', 'Ana', 'Luis', 'Pedro', 'Laura', 'Sofia', 'Miguel', 'Andrea', 'Diego', 'Valeria', 'Jesus', 'Carmen', 'Jose', 'Daniela'];
    $apellidos = ['Perez', 'Gomez', 'Rodriguez', 'Fernandez', 'Lopez', 'Martinez', 'Garcia', 'Sanchez', 'Romero', 'Sosa', 'Diaz', 'Torres', 'Ramirez', 'Castillo'];
    $nombre = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
    $rif = "V" . rand(10000000, 31999999);
    $email = strtolower(str_replace(' ', '.', $nombre)) . rand(10, 99) . "@gmail.com";
    $telefono = "04" . ['14', '24', '12', '16', '26'][rand(0, 4)] . "-" . rand(1000000, 9999999);
    $vip = (rand(1, 100) > 90) ? 1 : 0; // 10% VIP
    
    $clientes[] = ['id' => $rif, 'nombre' => $nombre];
    $sheetCli->fromArray([$rif, $nombre, $email, $telefono, "Urbanización, Calle " . rand(1, 20) . ", Edo " . ['Miranda', 'Carabobo', 'Zulia', 'Lara', 'Aragua', 'Dtto Capital'][rand(0,5)], $vip, ""], NULL, 'A' . $row++);
}

// --- 5. SPREADSHEET: PRODUCTOS ---
echo "Generando pestaña Productos...\n";
$productos = [];
$sheetProd = $spreadsheet->createSheet();
$sheetProd->setTitle('Productos');
$sheetProd->fromArray(['SKU', 'NombreProducto', 'Descripcion', 'PrecioVenta', 'CostoUnitario', 'StockInicial', 'StockMinimo', 'Categoria', 'Color', 'Marca'], NULL, 'A1');
$rowProd = 2;

$excelFile = __DIR__ . '/../Registro de libros (07-11-25) (1).xlsx';
if (file_exists($excelFile)) {
    echo "Procesando Excel del usuario...\n";
    try {
        $spreadsheetUser = IOFactory::load($excelFile);
        $sheetUser = $spreadsheetUser->getActiveSheet();
        $rowIndex = 1;
        foreach ($sheetUser->getRowIterator(2) as $rowInfo) {
            $rowIndex++;
            $it = $rowInfo->getCellIterator();
            $it->setIterateOnlyExistingCells(false);
            $cells = [];
            foreach ($it as $cell) $cells[] = $cell->getValue();
            
            $titulo = trim((string)($cells[1] ?? ''));
            $autor = trim((string)($cells[2] ?? ''));
            
            if (empty($titulo)) {
                $titulo = trim((string)($cells[0] ?? '')); 
            }
            if (empty($titulo)) continue;

            $sku = "LIB-" . str_pad($rowIndex, 4, '0', STR_PAD_LEFT);
            $costo = rand(5, 15) + (rand(0, 99) / 100);
            $precio = $costo * rand(130, 200) / 100;
            $cat = "Literatura Juvenil";
            if (stripos($titulo, 'magia') !== false || stripos($titulo, 'drag') !== false) $cat = "Ficción y Fantasía";
            
            $productos[] = ['id' => count($productos)+1, 'sku' => $sku, 'precio' => round($precio,2), 'costo' => round($costo,2)];
            $sheetProd->fromArray([$sku, $titulo, "Autor: $autor", round($precio,2), round($costo,2), rand(10, 50), 5, $cat, "", "Oveja"], NULL, 'A' . $rowProd++);
            if (count($productos) >= 150) break;
        }
    } catch (\Exception $e) {
         echo "Error leyendo Excel del usuario: " . $e->getMessage() . "\n";
    }
} else {
    echo "Excel de libros reales no encontrado.\n";
}

// Add Papeleria Mock Products targeting Venezuelan brands
$papeleriaTipos = [
    ['Cuaderno Empastado 100h', 'Cuadernos y Libretas', 2.50, 4.00, 'Norma'],
    ['Bolígrafo Tinta Azul', 'Escritura', 0.20, 0.50, 'Kilométrico'],
    ['Lápiz de Grafito Nro 2', 'Escritura', 0.15, 0.40, 'Mongol'],
    ['Pega en Barra Extrafuerte', 'Adhesivos y Cintas', 1.50, 3.50, 'Solita'],
    ['Resma de Papel Carta', 'Papelería de Oficina', 3.00, 5.50, 'Alpes'],
    ['Marcador Permanente', 'Marcadores y Resaltadores', 0.80, 1.50, 'Kores'],
    ['Corrector Líquido tipo Bolígrafo', 'Escritura', 0.50, 1.20, 'Kores'],
    ['Cartulina Doble Faz', 'Arte y Dibujo', 0.10, 0.30, 'Oveja'],
    ['Caja de Colores x24', 'Arte y Dibujo', 2.50, 5.00, 'Prismacolor'],
    ['Silicón Líquido', 'Adhesivos y Cintas', 1.00, 2.00, 'UHU'],
    ['Borrador Nata', 'Escritura', 0.30, 0.80, 'Pegaso'],
    ['Sacapuntas de Metal', 'Escritura', 0.40, 1.00, 'Norma']
];

$colores = ['Azul', 'Rojo', 'Negro', 'Surtido', 'Transparente', 'Blanco', 'Verde'];

for ($i = count($productos) + 1; $i <= 400; $i++) {
    $base = $papeleriaTipos[array_rand($papeleriaTipos)];
    $sku = "PAP-" . str_pad($i, 4, '0', STR_PAD_LEFT);
    $color = $colores[array_rand($colores)];
    $nombre = $base[0] . ' - ' . $color;
    
    $costo = round($base[2] * (rand(90, 110) / 100), 2);
    $precio = round($base[3] * (rand(90, 110) / 100), 2);
    
    $productos[] = ['id' => $i, 'sku' => $sku, 'precio' => $precio, 'costo' => $costo];
    $sheetProd->fromArray([$sku, $nombre, "Artículo original certificado", $precio, $costo, rand(20, 150), 10, $base[1], $color, $base[4]], NULL, 'A' . $rowProd++);
}

// SAVE THE CONSOLIDATED EXCEL FILE
echo "Guardando archivo unificado Excel...\n";
$writerMain = new Xlsx($spreadsheet);
$writerMain->save($outDir . 'mock_data_venezuela.xlsx');
echo "- mock_data_venezuela.xlsx generado exitosamente con múltiples pestañas.\n";


// 5. GENERATE HISTORICAL SQL
// 60 days of history
$sqlFile = fopen($outDir . 'mock_movements.sql', 'w');
fwrite($sqlFile, "-- SCRIPT GENERADO AUTOMÁTICAMENTE PARA CARGOS Y PEDIDOS HISTÓRICOS\n");
fwrite($sqlFile, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

// A. Cargos (Inicialización de inventario distribuida hace 60 y 30 días)
echo "Generando Cargos de Existencias SQL...\n";
$cargoId = 1;
$detCargoId = 1;
// We assign product IDs sequentially assuming auto-increment starts at 1, but wait!
// BuzzDM import uses database IDs. The import logic WILL assign autoincrement IDs 1 through 400.
// So we can safely assume product IDs represent array index + 1 in $productos.
// Supplier IDs 1..50, Client IDs from CSV.

for ($day = 60; $day >= 5; $day -= 5) {
    $fecha = date('Y-m-d', strtotime("-$day days"));
    $provId = rand(1, 50);
    $cargoCodigo = "CGO-2026-" . str_pad($cargoId, 4, '0', STR_PAD_LEFT);
    
    // Pick 30 distinct products per cargo
    $basket = (array)array_rand($productos, 30);
    $totalValor = 0;
    
    $detallesSql = [];
    foreach ($basket as $pIndex) {
        $p = $productos[$pIndex];
        $cant = rand(20, 100);
        $sub = $cant * $p['costo'];
        $totalValor += $sub;
        $detallesSql[] = "($cargoId, {$p['id']}, $cant, {$p['costo']}, NULL)";
    }
    
    fwrite($sqlFile, "INSERT INTO `cargos_existencias` (`id`, `codigo`, `fecha`, `concepto`, `proveedor_id`, `almacen_id`, `referencia_externa`, `total_productos`, `total_unidades`, `valor_total`, `estado`, `registrado_por`, `confirmado_por`, `fecha_confirmacion`, `creado_at`, `actualizado_at`) VALUES ");
    fwrite($sqlFile, "($cargoId, '$cargoCodigo', '$fecha', 'compra', $provId, 1, 'FAC-$cargoId', 30, ". (30*50) .", $totalValor, 'confirmado', 1, 1, '$fecha 10:00:00', '$fecha 09:00:00', '$fecha 10:00:00');\n");
    
    fwrite($sqlFile, "INSERT INTO `detalle_cargo_existencias` (`cargo_id`, `producto_id`, `cantidad`, `costo_unitario`, `notas`) VALUES " . implode(", ", $detallesSql) . ";\n");
    $cargoId++;
}

// B. Pedidos (600 sales distributed over 60 days)
echo "Generando Pedidos (Ventas) SQL...\n";
$pedidoId = 1;

// Prepare batches of inserts
$pedidosBatches = [];
$detPedidosBatches = [];

for ($i = 0; $i < 600; $i++) {
    // Generate a progressive date
    $daysAgo = rand(0, 60);
    $hour = rand(8, 17);
    $min = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    $sec = str_pad(rand(0, 59), 2, '0', STR_PAD_LEFT);
    
    $timestamp = strtotime("-$daysAgo days $hour:$min:$sec");
    $fecha = date('Y-m-d', $timestamp);
    $creadoAt = date('Y-m-d H:i:s', $timestamp);
    
    $clienteRif = $clientes[array_rand($clientes)]['id'];
    $metodoPago = ['efectivo', 'punto_venta', 'transferencia', 'pago_movil', 'biopago'][rand(0, 4)];
    
    // Shopping basket (1 to 6 items)
    $numItems = rand(1, 6);
    $basket = (array)array_rand($productos, $numItems);
    
    $subtotal = 0;
    
    foreach ($basket as $pIndex) {
        $p = $productos[$pIndex];
        $cant = rand(1, 4);
        $sub = $cant * $p['precio'];
        $subtotal += $sub;
        
        $detPedidosBatches[] = "($pedidoId, {$p['id']}, $cant, {$p['precio']})";
    }
    
    $ivaTotal = round($subtotal * 0.16, 2); // 16% IVA Venezuela
    $total = $subtotal + $ivaTotal;
    
    $referencia = rand(10000000, 99999999);
    $estadoPago = ['completado', 'completado', 'completado', 'pendiente', 'anulado'][rand(0, 4)];
    $estadoEnvio = ['entregado', 'entregado', 'entregado', 'pendiente', 'cancelado'][rand(0, 4)];
    
    $pedidosBatches[] = "($pedidoId, '$clienteRif', '$referencia', '$creadoAt', $total, '$estadoPago', '$estadoEnvio', 'Av. Intercomunal', '')";
    
    $pedidoId++;
}

// Chunk inserts to avoid SQL size limits
foreach (array_chunk($pedidosBatches, 50) as $chunk) {
    fwrite($sqlFile, "INSERT INTO `pedidos` (`id`, `cliente_id`, `referencia`, `fecha_creacion`, `total`, `estado_pago`, `estado_envio`, `direccion_envio`, `notas_envio`) VALUES " . implode(",\n", $chunk) . ";\n");
}
foreach (array_chunk($detPedidosBatches, 100) as $chunk) {
    fwrite($sqlFile, "INSERT INTO `detalle_pedido` (`pedido_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES " . implode(",\n", $chunk) . ";\n");
}

fwrite($sqlFile, "\nSET FOREIGN_KEY_CHECKS = 1;\n");
fclose($sqlFile);
echo "- mock_movements.sql generado.\n";
echo "PROCESO COMPLETADO EXITOSAMENTE.\n";
echo "Ruta de archivos: " . realpath($outDir) . "\n";
