<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sesion.php'; // Include sesion.php to access session variables

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

header('Content-Type: application/json');
error_log("--- Starting Import Process ---");

$response = [
    'success' => false,
    'message' => '',
    'results' => []
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
            'sheet_name' => 'prueba',
            'table' => 'productos',
            'unique_key' => 'sku',
            'columns' => [
                'SKU' => ['db_col' => 'sku', 'type' => 'string'],
                'Producto' => ['db_col' => 'nombre', 'type' => 'string'],
                'Descripción' => ['db_col' => 'descripcion', 'type' => 'string'],
                'Precio' => ['db_col' => 'precio', 'type' => 'float'],
                'Valores' => ['db_col' => 'stock', 'type' => 'integer'],
                'MinStock' => ['db_col' => 'min_stock', 'type' => 'integer'],
                'Categoría' => ['db_col' => 'categoria', 'type' => 'integer'],
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

        $importResults[$sheetType] = ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];
        $headerRow = $sheet->getRowIterator(1, 1)->current();
        $headerCells = [];
        foreach ($headerRow->getCellIterator() as $cell) {
            $headerCells[] = $cell->getValue();
        }

        $dbColumns = [];
        $placeholders = [];
        $updateSet = [];
        foreach ($sheetConfig['columns'] as $excelHeader => $colConfig) {
            $dbCol = $colConfig['db_col'];
            $dbColumns[] = $dbCol;
            $placeholders[] = '?';
            if ($dbCol !== $sheetConfig['unique_key']) {
                $updateSet[] = "{$dbCol} = VALUES({$dbCol})";
            }
        }

        $upsertSql = "INSERT INTO {$table} (" . implode(', ', $dbColumns) . ") VALUES (" . implode(', ', $placeholders) . ") ON DUPLICATE KEY UPDATE " . implode(', ', $updateSet);
        error_log("Generated SQL for '{$sheetName}': {$upsertSql}");

        $pdo->beginTransaction();
        $stmtInsert = $pdo->prepare($upsertSql);

        $rowIndex = 0;
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            if ($rowIndex == 1) continue; // Skip header

            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }

            $mappedData = [];
            // Associate row data with headers
            for ($i = 0; $i < count($headerCells); $i++) {
                $mappedData[$headerCells[$i]] = $rowData[$i] ?? null;
            }

            $params = [];
            foreach ($sheetConfig['columns'] as $excelHeader => $colConfig) {
                $params[] = $mappedData[$excelHeader] ?? null;
            }

            error_log("Row {$rowIndex} in '{$sheetName}' | Data: " . json_encode($mappedData));
            error_log("Executing query with params: " . json_encode($params));

            try {
                $stmtInsert->execute($params);
                if ($stmtInsert->rowCount() == 1) {
                    $importResults[$sheetType]['inserted']++;
                } elseif ($stmtInsert->rowCount() > 1) {
                    $importResults[$sheetType]['updated']++;
                }
                $importResults[$sheetType]['processed']++;
            } catch (PDOException $e) {
                $importResults[$sheetType]['failed']++;
                $errorMsg = "Fila {$rowIndex}: " . $e->getMessage();
                $importResults[$sheetType]['errors'][] = $errorMsg;
                error_log("DB Error on '{$sheetName}', Row {$rowIndex}: " . $e->getMessage());
                $overallStatus = 'error';
            }
        }

        if (empty($importResults[$sheetType]['errors'])) {
            $pdo->commit();
            error_log("SUCCESS: Committed changes for sheet '{$sheetName}'.");
        } else {
            $pdo->rollBack();
            error_log("FAILURE: Rolled back changes for sheet '{$sheetName}' due to errors.");
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