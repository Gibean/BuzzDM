<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sesion.php'; // Include sesion.php to access session variables

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Busca un registro por nombre en una tabla. Si no existe, lo crea.
 * Devuelve el ID del registro (existente o nuevo).
 */
function findOrCreate(PDO $pdo, string $tableName, ?string $name): ?int {
    if (empty(trim($name))) return null;
    $stmt = $pdo->prepare("SELECT id FROM {$tableName} WHERE LOWER(nombre) = LOWER(?)");
    $stmt->execute([trim($name)]);
    if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$result['id'];
    $stmt = $pdo->prepare("INSERT INTO {$tableName} (nombre) VALUES (?)");
    $stmt->execute([trim($name)]);
    return (int)$pdo->lastInsertId();
}

header('Content-Type: application/json');
error_log("--- Starting Import Process ---");

$response = [
    'success' => false,
    'message' => '',
    'results' => [],
    'summary' => ['created' => []]
];

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Error al subir el archivo. Código de error: ' . ($_FILES['import_file']['error'] ?? 'N/A');
    error_log("File upload error: " . $response['message']);
    echo json_encode($response);
    exit;
}

$fileTmpPath = $_FILES['import_file']['tmp_name'];
$fileName = $_FILES['import_file']['name'];
error_log("Processing file: {$fileName}");

$pdo = null;
$importId = null;

try {
    $pdo = getDbConnection();
    error_log("Database connection successful.");

    $userId = $_SESSION['usuario_id'] ?? null;
    error_log("User ID from session: {$userId}");

    $stmtInsertImport = $pdo->prepare(
        "INSERT INTO importaciones (usuario_id, tipo, archivo_nombre, estado, detalles) VALUES (?, ?, ?, ?, ?)"
    );
    $stmtInsertImport->execute([$userId, 'general', $fileName, 'pendiente', json_encode(['message' => 'Importación iniciada.'])]);
    $importId = $pdo->lastInsertId();
    error_log("Created import record with ID: {$importId}");

    $spreadsheet = IOFactory::load($fileTmpPath);

    $mappings = [
        'Productos' => [
            'sheet_name' => 'prueba', // o el nombre de tu hoja de productos
            'table' => 'productos',
            'unique_key' => 'sku',
            'columns' => [
                'SKU' => ['db_col' => 'sku', 'type' => 'string'],
                'Producto' => ['db_col' => 'nombre', 'type' => 'string'],
                'Descripción' => ['db_col' => 'descripcion', 'type' => 'string'],
                'Precio' => ['db_col' => 'precio', 'type' => 'float', 'default' => 0.0],
                'Stock' => ['db_col' => 'stock', 'type' => 'integer', 'default' => 0],
                'MinStock' => ['db_col' => 'min_stock', 'type' => 'integer', 'default' => 10],
                'Categoría' => ['db_col' => 'categoria', 'type' => 'fk', 'target_table' => 'categorias'],
                'Marca' => ['db_col' => 'marca_id', 'type' => 'fk', 'target_table' => 'marcas'],
                'Proveedor' => ['db_col' => 'proveedor_id', 'type' => 'fk', 'target_table' => 'proveedores'],
            ],
        ],
        'Clientes' => [
            'sheet_name' => 'Clientes',
            'table' => 'clientes',
            'unique_key' => 'email',
            'columns' => [
                'Nombre' => ['db_col' => 'nombre', 'type' => 'string'],
                'Email' => ['db_col' => 'email', 'type' => 'string'],
                'Telefono' => ['db_col' => 'telefono', 'type' => 'string'],
                'Direccion' => ['db_col' => 'direccion', 'type' => 'string'],
                'Notas' => ['db_col' => 'notas', 'type' => 'string'],
                'VIP' => ['db_col' => 'vip', 'type' => 'boolean'],
            ],
        ],
        'Pedidos' => [
            'sheet_name' => 'Pedidos',
            'table' => 'pedidos',
            'unique_key' => 'referencia',
            'columns' => [
                 'Referencia' => ['db_col' => 'referencia', 'type' => 'string'],
                 'ClienteEmail' => ['db_col' => 'cliente_email', 'type' => 'string'],
                 'Total' => ['db_col' => 'total', 'type' => 'float'],
                 'EstadoPago' => ['db_col' => 'estado_pago', 'type' => 'string'],
                 'EstadoEnvio' => ['db_col' => 'estado_envio', 'type' => 'string'],
                 'DireccionEnvio' => ['db_col' => 'direccion_envio', 'type' => 'string'],
                 'NotasEnvio' => ['db_col' => 'notas_envio', 'type' => 'string'],
                 'CreadoPorEmail' => ['db_col' => 'creado_por_email', 'type' => 'string'],
            ],
        ],
    ];

    $overallStatus = 'procesado';
    $importResults = [];
    foreach ($mappings as $sheetType => $sheetConfig) {
        error_log("--- Processing Sheet Type: {$sheetType} ---");
        $sheetName = $sheetConfig['sheet_name'];
        $table = $sheetConfig['table'];
        error_log("Target Sheet: '{$sheetName}', Target Table: '{$table}'");

        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (!$sheet) {
            error_log("Sheet '{$sheetName}' not found in the Excel file. Skipping.");
            continue;
        }

        $importResults[$sheetType] = ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => [], 'status' => 'success'];
        $headerRow = $sheet->getRowIterator(1, 1)->current();
        $headerCells = [];
        foreach ($headerRow->getCellIterator() as $cell) {
            $headerCells[trim($cell->getColumn())] = trim($cell->getValue());
        }

        // Invert header map for easy lookup: Excel Header => Column Letter
        $headerMap = array_flip($headerCells);

        $pdo->beginTransaction();

        $rowIndex = 0;
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            if ($rowIndex == 1) continue; // Skip header

            $rowData = [];
            foreach ($sheetConfig['columns'] as $excelHeader => $colConfig) {
                $colLetter = $headerMap[$excelHeader] ?? null;
                $cellValue = $colLetter ? $sheet->getCell($colLetter . $rowIndex)->getValue() : null;

                if ($colConfig['type'] === 'fk') {
                    $rowData[$colConfig['db_col']] = findOrCreate($pdo, $colConfig['target_table'], $cellValue);
                } else {
                    $value = $cellValue ?? $colConfig['default'] ?? null;
                    switch ($colConfig['type']) {
                        case 'integer': $value = (int)$value; break;
                        case 'float':   $value = (float)$value; break;
                        case 'boolean': $value = filter_var($value, FILTER_VALIDATE_BOOLEAN); break;
                    }
                    $rowData[$colConfig['db_col']] = $value;
                }
            }

            error_log("Row {$rowIndex} in '{$sheetName}' | Data: " . json_encode($rowData));

            try {
                $uniqueKey = $sheetConfig['unique_key'];
                $uniqueValue = $rowData[$uniqueKey];

                $stmtCheck = $pdo->prepare("SELECT id FROM {$table} WHERE {$uniqueKey} = ?");
                $stmtCheck->execute([$uniqueValue]);
                $exists = $stmtCheck->fetch();

                if ($exists) { // UPDATE
                    $updateFields = [];
                    foreach ($rowData as $key => $value) {
                        if ($key !== $uniqueKey) $updateFields[] = "{$key} = :{$key}";
                    }
                    $sql = "UPDATE {$table} SET " . implode(', ', $updateFields) . " WHERE {$uniqueKey} = :{$uniqueKey}";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($rowData);
                    $importResults[$sheetType]['updated']++;
                } else { // INSERT
                    $columns = implode(', ', array_keys($rowData));
                    $placeholders = ':' . implode(', :', array_keys($rowData));
                    $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($rowData);
                    $importResults[$sheetType]['inserted']++;

                    // If a new product with stock is inserted, create an initial inventory movement
                    if ($sheetType === 'Productos' && $rowData['stock'] > 0) {
                        $productId = $pdo->lastInsertId();
                        $movStmt = $pdo->prepare(
                            "INSERT INTO movimientos_inventario (producto_id, usuario_id, tipo_movimiento, cantidad, justificacion, stock_anterior, stock_actual)
                             VALUES (?, ?, 'entrada', ?, 'Stock inicial de importación', 0, ?)"
                        );
                        $movStmt->execute([$productId, $userId, $rowData['stock'], $rowData['stock']]);
                    }
                }
                $importResults[$sheetType]['processed']++;
            } catch (PDOException $e) {
                $importResults[$sheetType]['failed']++;
                $errorMsg = "Fila {$rowIndex}: " . $e->getMessage();
                $importResults[$sheetType]['errors'][] = $errorMsg;
                error_log("DB Error on '{$sheetName}', Row {$rowIndex}: " . $e->getMessage());
                $overallStatus = 'error';
                $importResults[$sheetType]['status'] = 'failed';
            }
        }

        if (empty($importResults[$sheetType]['errors'])) {
            $pdo->commit();
            error_log("SUCCESS: Committed changes for sheet '{$sheetName}'.");
        } else {
            $pdo->rollBack();
            error_log("FAILURE: Rolled back changes for sheet '{$sheetName}' due to errors.");
            $importResults[$sheetType]['message'] = "Importación de '{$sheetType}' fallida. Se encontraron errores.";
        }
    }

    $response['success'] = ($overallStatus === 'procesado');
    $response['message'] = 'Proceso de importación finalizado.';
    $response['results'] = $importResults;

} catch (Exception $e) {
    $response['message'] = 'Ocurrió un error inesperado: ' . $e->getMessage();
    error_log("FATAL ERROR during import: " . $e->getMessage());
    $overallStatus = 'error';
}

if ($importId) {
    $stmtUpdateImport = $pdo->prepare("UPDATE importaciones SET estado = ?, detalles = ? WHERE id = ?");
    $stmtUpdateImport->execute([$overallStatus, json_encode($response), $importId]);
}

error_log("--- Finished Import Process ---");
echo json_encode($response);
exit;