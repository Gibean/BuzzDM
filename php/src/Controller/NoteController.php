<?php
require_once __DIR__ . '/../Service/NoteService.php';

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

    private function createNote($input) {
        $userId = $_SESSION['usuario_id'] ?? 0;
        $newId = $this->service->createNote($input, $userId);
        $this->jsonResponse(true, ['id' => $newId], "Nota creada correctamente");
    }

    private function updateNote($input) {
        $userId = $_SESSION['usuario_id'] ?? 0;
        $id = $input['id'] ?? null;
        $this->service->updateNote($id, $input, $userId);
        $this->jsonResponse(true, null, "Nota actualizada");
    }

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
