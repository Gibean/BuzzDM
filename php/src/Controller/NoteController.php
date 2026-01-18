<?php
// Autoload handled by composer
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Notes", description: "Operations related to Kanban Notes")]
class NoteController {
    private $service;

    public function __construct() {
        $this->service = new NoteService();
    }

    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $input = json_decode(file_get_contents('php://input'), true);

            switch ($method) {
                case 'GET':
                    if (isset($_GET['id'])) {
                        $this->getNote((int)$_GET['id']);
                    } else {
                        $this->getAllNotes();
                    }
                    break;
                case 'POST':
                    $this->createNote($input);
                    break;
                case 'PUT':
                    $this->updateNote($input);
                    break;
                case 'DELETE':
                    $this->deleteNote($_GET['id'] ?? null);
                    break;
                default:
                    $this->jsonResponse(false, null, 'Método no permitido');
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    #[OA\Get(
        path: "/php/api_notes.php",
        tags: ["Notes"],
        summary: "List or get notes",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Note ID", required: false, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Note list or detail")
        ]
    )]
    private function getAllNotes() {
        $data = $this->service->getAllNotes();
        $this->jsonResponse(true, $data);
    }

    private function getNote($id) {
        $data = $this->service->getNoteDetails($id);
        if ($data) {
            $this->jsonResponse(true, $data);
        } else {
            $this->jsonResponse(false, null, "Nota no encontrada");
        }
    }

    #[OA\Post(
        path: "/php/api_notes.php",
        tags: ["Notes"],
        summary: "Create a note",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Note created")
        ]
    )]
    private function createNote($input) {
        $userId = $_SESSION['usuario_id'] ?? 0;
        $newId = $this->service->createNote($input, $userId);
        $this->jsonResponse(true, ['id' => $newId], "Nota creada correctamente");
    }

    #[OA\Put(
        path: "/php/api_notes.php",
        tags: ["Notes"],
        summary: "Update a note",
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: "object")),
        responses: [
            new OA\Response(response: 200, description: "Note updated")
        ]
    )]
    private function updateNote($input) {
        $userId = $_SESSION['usuario_id'] ?? 0;
        $id = $input['id'] ?? null;
        $this->service->updateNote($id, $input, $userId);
        $this->jsonResponse(true, null, "Nota actualizada");
    }

    #[OA\Delete(
        path: "/php/api_notes.php",
        tags: ["Notes"],
        summary: "Delete a note",
        parameters: [
            new OA\Parameter(name: "id", in: "query", description: "Note ID", required: true, schema: new OA\Schema(type: "integer"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Note deleted")
        ]
    )]
    private function deleteNote($id) {
        $this->service->deleteNote($id);
        $this->jsonResponse(true, null, "Nota eliminada");
    }

    private function jsonResponse($success, $data = [], $message = '') {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'message' => $message
        ]);
        exit;
    }
}
