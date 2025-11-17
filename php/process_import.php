<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sesion.php'; // Include sesion.php to access session variables

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'results' => []
];

if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Error al subir el archivo. Código de error: ' . ($_FILES['import_file']['error'] ?? 'N/A');
    echo json_encode($response);
    exit;
}

$fileTmpPath = $_FILES['import_file']['tmp_name'];
$fileName = $_FILES['import_file']['name'];
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

// Define allowed extensions
$allowedFileExtensions = ['csv', 'xlsx', 'xls'];
if (!in_array(strtolower($fileExtension), $allowedFileExtensions)) {
    $response['message'] = 'Tipo de archivo no permitido. Solo se permiten archivos CSV, XLSX y XLS.';
    echo json_encode($response);
    exit;
}

$pdo = null; // Initialize pdo outside try-catch for broader scope
$importId = null; // To store the ID of the import record

try {
    $pdo = getDbConnection();

    // 1. Get usuario_id from session email
    $userId = null;
    if (isset($_SESSION['user_email'])) {
        $stmtUser = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmtUser->execute([$_SESSION['user_email']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $userId = $user['id'];
        }
    }

    // 2. Insert initial import record (estado = 'pendiente')
    $stmtInsertImport = $pdo->prepare(
        "INSERT INTO importaciones (usuario_id, tipo, archivo_nombre, estado, detalles) VALUES (?, ?, ?, ?, ?)"
    );
    $stmtInsertImport->execute([
        $userId,
        'general', // Type can be 'general' or specific like 'productos', 'clientes', 'pedidos'
        $fileName,
        'pendiente',
        json_encode(['message' => 'Importación iniciada.'])
    ]);
    $importId = $pdo->lastInsertId();

    $spreadsheet = IOFactory::load($fileTmpPath);

    $importResults = [];

    // Define mappings for each sheet
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
            'validation_rules' => [
                'sku' => ['required' => true, 'max_length' => 50],
                'nombre' => ['required' => true, 'max_length' => 255],
                'precio' => ['required' => true, 'min' => 0],
                'stock' => ['required' => true, 'min' => 0],
                'min_stock' => ['required' => true, 'min' => 0],
                'categoria' => ['required' => true, 'foreign_key' => ['table' => 'categorias', 'column' => 'id']],
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
            'validation_rules' => [
                'nombre' => ['required' => true, 'max_length' => 255],
                'email' => ['required' => true, 'email' => true, 'max_length' => 255],
                'vip' => ['required' => true, 'boolean' => true],
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
            'validation_rules' => [
                'referencia' => ['required' => true, 'max_length' => 50],
                'cliente_email' => ['required' => true, 'email' => true, 'foreign_key' => ['table' => 'clientes', 'column' => 'email']],
                'total' => ['required' => true, 'min' => 0],
                'estado_pago' => ['required' => true, 'in' => ['no_pagado', 'pagado']],
                'estado_envio' => ['required' => true, 'in' => ['pendiente', 'enviado', 'recibido']],
                'creado_por_email' => ['required' => true, 'email' => true, 'foreign_key' => ['table' => 'usuarios', 'column' => 'email']],
            ],
        ],
    ];

    $overallStatus = 'procesado'; // Assume success unless any sheet fails
    foreach ($mappings as $sheetType => $sheetConfig) {
        $sheetName = $sheetConfig['sheet_name'];
        $table = $sheetConfig['table'];
        $uniqueKey = $sheetConfig['unique_key'];
        $columns = $sheetConfig['columns'];
        $validationRules = $sheetConfig['validation_rules'];

        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (!$sheet) {
            $importResults[$sheetType] = [
                'status' => 'skipped',
                'message' => "Hoja '{$sheetName}' no encontrada en el archivo.",
                'processed' => 0,
                'inserted' => 0,
                'updated' => 0,
                'failed' => 0,
                'errors' => []
            ];
            continue;
        }

        $importResults[$sheetType] = [
            'status' => 'processing',
            'message' => '',
            'processed' => 0,
            'inserted' => 0,
            'updated' => 0,
            'failed' => 0,
            'errors' => []
        ];

        $headerRow = $sheet->getRowIterator(1, 1)->current();
        $headerCells = [];
        foreach ($headerRow->getCellIterator() as $cell) {
            $headerCells[] = $cell->getValue();
        }

        $dbColumns = [];
        $placeholders = [];
        $updateSet = [];
        foreach ($columns as $excelHeader => $colConfig) {
            $dbCol = $colConfig['db_col'];
            $dbColumns[] = $dbCol;
            $placeholders[] = '?';
            if ($dbCol !== $uniqueKey) { // Don't update the unique key itself
                $updateSet[] = "{$dbCol} = VALUES({$dbCol})";
            }
        }

        $insertSql = "INSERT INTO {$table} (" . implode(', ', $dbColumns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $upsertSql = $insertSql . " ON DUPLICATE KEY UPDATE " . implode(', ', $updateSet);

        $pdo->beginTransaction();
        $stmtInsert = $pdo->prepare($upsertSql);

        $rowIndex = 1;
        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            if ($rowIndex === 2) { // Skip header row
                continue;
            }

            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }

            // Map Excel headers to database columns
            $mappedData = [];
            $isValid = true;
            $rowErrors = [];

            foreach ($columns as $excelHeader => $colConfig) {
                $excelColIndex = array_search($excelHeader, $headerCells);
                if ($excelColIndex === false) {
                    $isValid = false;
                    $rowErrors[] = "Columna '{$excelHeader}' no encontrada en el archivo.";
                    continue;
                }

                $value = $rowData[$excelColIndex] ?? null;
                $dbCol = $colConfig['db_col'];
                $type = $colConfig['type'];

                // Type casting and basic validation
                switch ($type) {
                    case 'string':
                        $value = (string)$value;
                        break;
                    case 'integer':
                        $value = (int)$value;
                        break;
                    case 'float':
                        // Handle comma as decimal separator if present
                        if (is_string($value)) {
                            $value = str_replace(',', '.', $value);
                        }
                        $value = (float)$value;
                        break;
                    case 'boolean':
                        if (is_string($value)) {
                            $value = strtolower($value);
                            if (in_array($value, ['1', 'true', 'si'])) {
                                $value = 1;
                            } elseif (in_array($value, ['0', 'false', 'no'])) {
                                $value = 0;
                            } else {
                                $isValid = false;
                                $rowErrors[] = "Valor inválido para '{$excelHeader}'. Se esperaba '1/0', 'true/false' o 'si/no'.";
                            }
                        } else {
                            $value = (bool)$value;
                        }
                        break;
                    // Add other types as needed (e.g., date)
                }
                $mappedData[$dbCol] = $value;
            }

            // Advanced validation based on rules
            foreach ($validationRules as $dbCol => $rules) {
                $value = $mappedData[$dbCol] ?? null;

                if (isset($rules['required']) && $rules['required'] && ($value === null || $value === '')) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' es requerido.";
                }
                if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' excede la longitud máxima de {$rules['max_length']} caracteres.";
                }
                if (isset($rules['min']) && $value < $rules['min']) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' debe ser al menos {$rules['min']}.";
                }
                if (isset($rules['email']) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' no es un email válido.";
                }
                if (isset($rules['boolean']) && !in_array($value, [0, 1])) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' debe ser un valor booleano (0 o 1).";
                }
                if (isset($rules['in']) && !in_array($value, $rules['in'])) {
                    $isValid = false;
                    $rowErrors[] = "El campo '{$dbCol}' tiene un valor inválido. Valores permitidos: " . implode(', ', $rules['in']) . ".";
                }
                if (isset($rules['foreign_key'])) {
                    $fkTable = $rules['foreign_key']['table'];
                    $fkColumn = $rules['foreign_key']['column'];
                    $checkFkSql = "SELECT COUNT(*) FROM {$fkTable} WHERE {$fkColumn} = ?";
                    $stmtFk = $pdo->prepare($checkFkSql);
                    $stmtFk->execute([$value]);
                    if ($stmtFk->fetchColumn() == 0) {
                        $isValid = false;
                        $rowErrors[] = "El valor '{$value}' para '{$dbCol}' no existe en la tabla '{$fkTable}'.";
                    }
                }
            }

            $importResults[$sheetType]['processed']++;

            if ($isValid) {
                try {
                    $params = [];
                    foreach ($dbColumns as $col) {
                        $params[] = $mappedData[$col] ?? null;
                    }
                    $stmtInsert->execute($params);

                    // Check if it was an insert or update
                    if ($stmtInsert->rowCount() > 0) { // rowCount() > 0 for insert, or 1 for update (if changed)
                        // For ON DUPLICATE KEY UPDATE, rowCount() returns 1 for insert, 2 for update (if data changed)
                        // A more robust check might involve a SELECT before the UPSERT, but for simplicity, we'll assume
                        // rowCount() > 0 means something happened.
                        // If rowCount() is 2, it means an update occurred. If 1, it means an insert.
                        if ($stmtInsert->rowCount() == 2) {
                            $importResults[$sheetType]['updated']++;
                        } else {
                            $importResults[$sheetType]['inserted']++;
                        }
                    } else {
                        // This case might happen if an update was attempted but no data actually changed.
                        // We'll count it as an update for reporting purposes, as the intent was to process it.
                        $importResults[$sheetType]['updated']++;
                    }

                } catch (PDOException $e) {
                    $isValid = false;
                    $rowErrors[] = "Error de base de datos: " . $e->getMessage();
                    $importResults[$sheetType]['failed']++;
                    $importResults[$sheetType]['errors'][] = "Fila {$rowIndex}: " . implode('; ', $rowErrors);
                    error_log("Import Error (Sheet: {$sheetName}, Row: {$rowIndex}): " . $e->getMessage());
                }
            } else {
                $importResults[$sheetType]['failed']++;
                $importResults[$sheetType]['errors'][] = "Fila {$rowIndex}: " . implode('; ', $rowErrors);
            }
        }

        if (empty($importResults[$sheetType]['errors'])) {
            $pdo->commit();
            $importResults[$sheetType]['status'] = 'success';
            $importResults[$sheetType]['message'] = "Importación de '{$sheetName}' completada exitosamente.";
        } else {
            $pdo->rollBack();
            $importResults[$sheetType]['status'] = 'failed';
            $importResults[$sheetType]['message'] = "Importación de '{$sheetName}' fallida. Se encontraron errores.";
            $overallStatus = 'error'; // Mark overall status as error if any sheet fails
        }
    }

    $response['success'] = ($overallStatus === 'procesado');
    $response['message'] = ($overallStatus === 'procesado') ? 'Proceso de importación finalizado con éxito.' : 'Proceso de importación finalizado con errores.';
    $response['results'] = $importResults;

} catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
    $response['message'] = 'Error al leer el archivo Excel/CSV: ' . $e->getMessage();
    error_log('PhpSpreadsheet Error: ' . $e->getMessage());
    $overallStatus = 'error';
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos durante la importación: ' . $e->getMessage();
    error_log('PDO Import Error: ' . $e->getMessage());
    $overallStatus = 'error';
} catch (Exception $e) {
    $response['message'] = 'Ocurrió un error inesperado: ' . $e->getMessage();
    error_log('Unexpected Error: ' . $e->getMessage());
    $overallStatus = 'error';
}

// 3. Update import record (estado = 'procesado' or 'error')
if ($importId) {
    try {
        $stmtUpdateImport = $pdo->prepare(
            "UPDATE importaciones SET estado = ?, detalles = ? WHERE id = ?"
        );
        $stmtUpdateImport->execute([
            $overallStatus,
            json_encode($importResults),
            $importId
        ]);
    } catch (PDOException $e) {
        error_log('Error updating import log: ' . $e->getMessage());
        // This error should not prevent the main response from being sent
    }
}

echo json_encode($response);
exit;