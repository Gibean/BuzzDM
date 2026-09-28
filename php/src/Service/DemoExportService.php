<?php
/**
 * DemoExportService
 * 
 * Genera un archivo Excel consolidado (.xlsx) con los datos del tenant
 * (Productos, Clientes, Proveedores, Inventario) cuando su período de prueba finaliza.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DemoExportService {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? Database::getConnection();
    }

    /**
     * Genera el libro Excel consolidado para la empresa y lo guarda en disco
     * @return string Ruta absoluta del archivo generado
     */
    public function generateCompanyDataExport(int $companyId, ?string $destinationPath = null): string {
        $spreadsheet = new Spreadsheet();

        // 1. Pestaña de Productos
        $this->buildProductsSheet($spreadsheet, $companyId);

        // 2. Pestaña de Clientes
        $this->buildClientsSheet($spreadsheet, $companyId);

        // 3. Pestaña de Proveedores
        $this->buildSuppliersSheet($spreadsheet, $companyId);

        // Activar la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        if (!$destinationPath) {
            $tempDir = __DIR__ . '/../../../backups/exports/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $destinationPath = $tempDir . "BuzzDM_Respaldo_Empresa_{$companyId}_" . date('Ymd_His') . ".xlsx";
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($destinationPath);

        return $destinationPath;
    }

    private function buildProductsSheet(Spreadsheet $spreadsheet, int $companyId): void {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos');

        $headers = ['ID', 'SKU', 'Nombre', 'Descripción', 'Precio ($)', 'Stock', 'Stock Mínimo', 'Estado', 'Tipo'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 'A1:I1');

        $stmt = $this->pdo->prepare("
            SELECT id, sku, nombre, descripcion, precio, stock, min_stock, estado, tipo_producto
            FROM productos
            WHERE empresa_id = :empresa_id AND deleted_at IS NULL
            ORDER BY id ASC
        ");
        $stmt->execute([':empresa_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildClientsSheet(Spreadsheet $spreadsheet, int $companyId): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Clientes');

        $headers = ['Cédula/RIF', 'Nombre o Razón Social', 'Email', 'Teléfono', 'Dirección', 'VIP'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 'A1:F1');

        $stmt = $this->pdo->prepare("
            SELECT id, nombre, email, telefono, direccion, IF(vip=1, 'SÍ', 'NO')
            FROM clientes
            WHERE empresa_id = :empresa_id AND deleted_at IS NULL
            ORDER BY nombre ASC
        ");
        $stmt->execute([':empresa_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildSuppliersSheet(Spreadsheet $spreadsheet, int $companyId): void {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Proveedores');

        $headers = ['ID', 'RIF', 'Razón Social', 'Contacto', 'Email', 'Teléfono', 'Dirección'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 'A1:G1');

        $stmt = $this->pdo->prepare("
            SELECT id, rif, nombre, contacto_nombre, email, telefono, direccion
            FROM proveedores
            WHERE empresa_id = :empresa_id AND deleted_at IS NULL
            ORDER BY nombre ASC
        ");
        $stmt->execute([':empresa_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_NUM);

        if (!empty($rows)) {
            $sheet->fromArray($rows, null, 'A2');
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function styleHeader($sheet, string $range): void {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D97706'] // Tono ámbar/buzz
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => 'B45309']]
            ]
        ]);
    }
}
