// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Import", description: "Import Operations")]
class ImportController {
    private $service;

    public function __construct() {
        $this->service = new ImportService();
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $action = $_GET['action'] ?? null;

            if ($method === 'POST') {
                $this->processImport();
            } elseif ($method === 'GET') {
                $this->getHistory();
            } else {
                $this->jsonResponse(false, [], 'Método no permitido.');
            }

        } catch (Exception $e) {
            $this->jsonResponse(false, [], $e->getMessage());
        }
    }

    #[OA\Post(
        path: "/php/api_import.php",
        tags: ["Import"],
        summary: "Process excel import",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["import_file"],
                    properties: [
                        new OA\Property(
                            property: "import_file",
                            type: "string",
                            format: "binary",
                            description: "Excel file to import"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Import result")
        ]
    )]
    private function processImport() {
        // Validate File
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(false, [], 'Error al subir archivo. Verifique que seleccionó un archivo válido.');
        }

        $fileTmpPath = $_FILES['import_file']['tmp_name'];
        $fileName = $_FILES['import_file']['name'];
        $userId = $_SESSION['usuario_id'] ?? null;

        if (!$userId) {
            $this->jsonResponse(false, [], 'Usuario no autenticado.');
        }

        // Process
        $result = $this->service->processFile($fileTmpPath, $fileName, $userId);

        // Return result directly as it matches the standard format
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    #[OA\Get(
        path: "/php/api_import.php",
        tags: ["Import"],
        summary: "Get import history",
        responses: [
            new OA\Response(response: 200, description: "History list")
        ]
    )]
    private function getHistory() {
        $history = $this->service->getHistory();

        // Parse details for frontend friendly format (matching legacy get_import_history logic partially)
        $formattedHistory = [];
        foreach ($history as $row) {
            $records = 0;
            $errors = 0;
            $details = json_decode($row['detalles'], true);

             if (is_array($details) && isset($details['results'])) {
                foreach ($details['results'] as $sheetResult) {
                    $records += ($sheetResult['inserted'] ?? 0) + ($sheetResult['updated'] ?? 0);
                    $errors += ($sheetResult['failed'] ?? 0);
                }
            }

            $formattedHistory[] = [
                'id' => $row['id'],
                'date' => $row['fecha'],
                'type' => $row['tipo'],
                'filename' => $row['archivo_nombre'],
                'status' => $row['estado'],
                'records' => $records,
                'errors' => $errors,
                'user' => $row['usuario_nombre'] ?? 'Desconocido',
                'details' => $details
            ];
        }

        $this->jsonResponse(true, ['history' => $formattedHistory]);
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        $response = ['success' => $success];
        if ($message) $response['message'] = $message;
        if (!empty($data)) $response = array_merge($response, $data);
        echo json_encode($response);
        exit;
    }
}
