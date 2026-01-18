<?php
// Autoload handled by composer
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportService {
    private $repository;

    public function __construct() {
        $this->repository = new ImportRepository();
    }

    public function getHistory($limit = 50) {
        return $this->repository->getHistory($limit);
    }

    public function processFile($fileTmpPath, $fileName, $userId) {
        $pdo = $this->repository->getPdo();

        // 1. Initial Log
        // Note: Using ['msg' => 'Iniciando...'] to match old structure initially
        $importId = $this->repository->logStart($userId, $fileName, ['msg' => 'Iniciando...']);

        $response = [
            'success' => false,
            'results' => [],
            'message' => ''
        ];

        try {
            // 2. Load Excel
            $spreadsheet = IOFactory::load($fileTmpPath);

            // 3. Define Master Mappings
            // Reusing the logic from process_import.php
            $mappings = $this->getMappings();

            $results = [];

            // 4. Iterate Mappings
            foreach ($mappings as $entity => $config) {
                $sheet = null;
                foreach ($config['sheet_names'] as $name) {
                    $sheet = $spreadsheet->getSheetByName($name);
                    if ($sheet) break;
                }

                if (!$sheet) continue;

                $tableName = $config['table'];
                $uniqueKey = $config['unique_key'];

                $stats = ['processed' => 0, 'inserted' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

                // Headers (Row 1)
                $headerRow = [];
                foreach ($sheet->getRowIterator(1, 1) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    foreach ($cellIterator as $cell) {
                        $rawVal = $cell->getValue();
                        if($rawVal) $headerRow[$cell->getColumn()] = $this->normalizeKey($rawVal);
                    }
                }

                // Map Excel Headers
                $colMap = [];
                foreach ($headerRow as $colLetter => $headerName) {
                    foreach ($config['columns'] as $key => $colCfg) {
                        if ($key === $headerName || strpos($headerName, $key) !== false) {
                            $colMap[$colLetter] = $key;
                        }
                    }
                }

                $this->repository->beginTransaction();

                $rowIndex = 1;
                foreach ($sheet->getRowIterator(2) as $row) {
                    $rowIndex++;
                    $rowData = [];
                    $skipRow = false;
                    $rowErrors = [];

                    // Extract Data
                    foreach ($colMap as $colLetter => $cfgKey) {
                        $cellVal = $sheet->getCell($colLetter . $rowIndex)->getValue();
                        $fieldCfg = $config['columns'][$cfgKey];

                        // Transform
                        if (isset($fieldCfg['type'])) {
                            if ($fieldCfg['type'] === 'float') $cellVal = (float)$cellVal;
                            if ($fieldCfg['type'] === 'int') $cellVal = (int)$cellVal;
                            if ($fieldCfg['type'] === 'bool') $cellVal = (bool)$cellVal;
                            if ($fieldCfg['type'] === 'fk') {
                                $cellVal = $this->repository->findOrCreateCatalog($fieldCfg['table'], $cellVal);
                            }
                        }

                        // Defaults
                        if (($cellVal === null || $cellVal === '') && isset($fieldCfg['default'])) {
                            $cellVal = $fieldCfg['default'];
                        }

                        $rowData[$fieldCfg['db']] = $cellVal;
                    }

                    // Validation
                    foreach ($config['columns'] as $key => $fieldCfg) {
                        if (($fieldCfg['required'] ?? false) && empty($rowData[$fieldCfg['db']])) {
                            $skipRow = true;
                            $rowErrors[] = "Campo requerido faltante: $key";
                        }
                    }

                    if ($skipRow) {
                        $stats['failed']++;
                        $stats['errors'][] = ['row' => $rowIndex, 'msg' => implode(', ', $rowErrors)];
                        continue;
                    }

                    // DB Operation
                    try {
                        // Check Exists
                        $checkVal = $rowData[$config['columns'][$uniqueKey]['db']];
                        $stmtCheck = $pdo->prepare("SELECT id FROM {$tableName} WHERE {$config['columns'][$uniqueKey]['db']} = ?");
                        $stmtCheck->execute([$checkVal]);
                        $exists = $stmtCheck->fetchColumn();

                        if ($exists) {
                            // Update
                            $setParts = [];
                            $params = [];
                            foreach ($rowData as $col => $val) {
                                if ($col !== $config['columns'][$uniqueKey]['db']) {
                                    $setParts[] = "$col = ?";
                                    $params[] = $val;
                                }
                            }
                            if(!empty($setParts)){
                                $params[] = $checkVal;
                                $sql = "UPDATE {$tableName} SET " . implode(', ', $setParts) . " WHERE {$config['columns'][$uniqueKey]['db']} = ?";
                                $pdo->prepare($sql)->execute($params);
                                $stats['updated']++;
                            }
                        } else {
                            // Insert
                            $cols = array_keys($rowData);
                            $placeholders = array_fill(0, count($cols), '?');
                            $sql = "INSERT INTO {$tableName} (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                            $pdo->prepare($sql)->execute(array_values($rowData));

                            $newId = $pdo->lastInsertId();
                            $stats['inserted']++;

                            // Special Logic: Initial Stock
                            if ($entity === 'Productos' && !empty($rowData['stock']) && $rowData['stock'] > 0) {
                                $stmtMov = $pdo->prepare("INSERT INTO movimientos_inventario
                                    (producto_id, usuario_id, tipo_movimiento, cantidad, justificacion, stock_anterior, stock_actual)
                                    VALUES (?, ?, 'entrada', ?, 'Carga Inicial Importación', 0, ?)");
                                $stmtMov->execute([$newId, $userId, $rowData['stock'], $rowData['stock']]);
                            }
                        }
                        $stats['processed']++;

                    } catch (PDOException $e) {
                        $stats['failed']++;
                        $stats['errors'][] = ['row' => $rowIndex, 'msg' => "Error DB: " . $e->getMessage()];
                    }
                }

                $this->repository->commit();
                $results[$entity] = $stats;
            }

            $response['success'] = true;
            $response['message'] = "Procesamiento completado.";
            $response['results'] = $results;

            // Final Log
            $finalStatus = 'procesado';
             foreach ($results as $r) {
                if ($r['failed'] > 0) $finalStatus = 'procesado_errores';
            }
            $this->repository->logFinish($importId, $finalStatus, $response);

        } catch (Exception $e) {
            $this->repository->rollBack();
            $response['success'] = false;
            $response['message'] = "Error general: " . $e->getMessage();
            $this->repository->logFinish($importId, 'error', ['msg' => $e->getMessage()]);
        }

        return $response;
    }

    private function normalizeKey($key) {
        return strtolower(trim(preg_replace('/[\x00-\x1F\x7F]/', '', $key)));
    }

    private function getMappings() {
        return [
            'Productos' => [
                'sheet_names' => ['Productos', 'Product', 'Products'],
                'table' => 'productos',
                'unique_key' => 'sku',
                'columns' => [
                    'sku' => ['db' => 'sku', 'required' => true],
                    'nombreproducto' => ['db' => 'nombre', 'required' => true],
                    'descripcion' => ['db' => 'descripcion'],
                    'precioventa' => ['db' => 'precio', 'type' => 'float', 'default' => 0],
                    'costounitario' => ['db' => 'costo_promedio', 'type' => 'float', 'default' => 0],
                    'stockinicial' => ['db' => 'stock', 'type' => 'int', 'default' => 0],
                    'stockminimo' => ['db' => 'min_stock', 'type' => 'int', 'default' => 10],
                    'categoria' => ['db' => 'categoria', 'type' => 'fk', 'table' => 'categorias'],
                    'marca' => ['db' => 'marca_id', 'type' => 'fk', 'table' => 'marcas'],
                    'proveedor' => ['db' => 'proveedor_id', 'type' => 'fk', 'table' => 'proveedores'],
                    'modelo' => ['db' => 'modelo'],
                    'color' => ['db' => 'color'],
                    'medidas' => ['db' => 'medidas'],
                    'peso' => ['db' => 'peso_kg', 'type' => 'float'],
                    'material' => ['db' => 'material'],
                ]
            ],
            'Clientes' => [
                'sheet_names' => ['Clientes', 'Customers'],
                'table' => 'clientes',
                'unique_key' => 'id',
                'columns' => [
                    'documentoid' => ['db' => 'id', 'required' => true], // Requires ID column in DB
                    'nombrerazon' => ['db' => 'nombre', 'required' => true],
                    'email' => ['db' => 'email'],
                    'telefono' => ['db' => 'telefono'],
                    'direccion' => ['db' => 'direccion'],
                    'esvip' => ['db' => 'vip', 'type' => 'bool']
                ]
            ],
            'Proveedores' => [
                'sheet_names' => ['Proveedores', 'Suppliers'],
                'table' => 'proveedores',
                'unique_key' => 'nombre',
                'columns' => [
                    'nombreempresa' => ['db' => 'nombre', 'required' => true],
                    'rifdocumento' => ['db' => 'rif'],
                    'personacontacto' => ['db' => 'contacto_nombre'],
                    'email' => ['db' => 'email'],
                    'telefono' => ['db' => 'telefono'],
                    'direccion' => ['db' => 'direccion']
                ]
            ],
            'Categorias' => [
                'sheet_names' => ['Categorias', 'Categories'],
                'table' => 'categorias',
                'unique_key' => 'nombre',
                'columns' => [
                    'nombrecategoria' => ['db' => 'nombre', 'required' => true],
                    'descripcion' => ['db' => 'descripcion']
                ]
            ],
            'Marcas' => [
                'sheet_names' => ['Marcas', 'Brands'],
                'table' => 'marcas',
                'unique_key' => 'nombre',
                'columns' => [
                    'nombremarca' => ['db' => 'nombre', 'required' => true],
                    'sitioweb' => ['db' => 'sitio_web'],
                    'descripcion' => ['db' => 'descripcion']
                ]
            ]
        ];
    }
}
