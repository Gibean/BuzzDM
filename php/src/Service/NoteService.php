<?php
require_once __DIR__ . '/../Repository/NoteRepository.php';
require_once __DIR__ . '/../../notification_functions.php';

class NoteService {
    private $repository;

    public function __construct() {
        $this->repository = new NoteRepository();
    }

    public function getAllNotes() {
        $notes = $this->repository->findAllGroupedByStatus();
        foreach ($notes as &$note) {
            $miembros = !empty($note['miembros']) ? json_decode($note['miembros'], true) : [];
            $note['miembros'] = is_array($miembros) ? $miembros : [];

            $etiquetas = !empty($note['etiquetas']) ? json_decode($note['etiquetas'], true) : [];
            $note['etiquetas'] = is_array($etiquetas) ? $etiquetas : [];
        }
        return $notes;
    }

    public function getNoteDetails($id) {
        $note = $this->repository->findById($id);
        if (!$note) return null;

        $miembros = !empty($note['miembros']) ? json_decode($note['miembros'], true) : [];
        $note['miembros'] = is_array($miembros) ? $miembros : [];

        $etiquetas = !empty($note['etiquetas']) ? json_decode($note['etiquetas'], true) : [];
        $note['etiquetas'] = is_array($etiquetas) ? $etiquetas : [];

        $details = $this->repository->findDetails($id);
        return array_merge($note, $details);
    }

    public function createNote($data, $creatorUserId) {
        if (empty($data['titulo'])) {
            throw new Exception("El título es obligatorio");
        }

        $this->repository->beginTransaction();
        try {
            // Prepare main data
            $noteData = [
                'titulo' => $data['titulo'],
                'cliente_nombre' => $data['cliente_nombre'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? null,
                'contenido' => $data['contenido'] ?? '',
                'estado' => $data['estado'] ?? 'pendiente',
                'prioridad' => $data['prioridad'] ?? 'media',
                'usuario_id' => $creatorUserId,
                'fecha_entrega' => !empty($data['fecha_entrega']) ? $data['fecha_entrega'] : null,
                'etiquetas' => $data['etiquetas'] ?? null,
                'miembros' => $data['miembros'] ?? null
            ];

            $notaId = $this->repository->create($noteData);

            // Relations
            if (!empty($data['items'])) $this->repository->addItems($notaId, $data['items']);
            if (!empty($data['checklist'])) $this->repository->addChecklist($notaId, $data['checklist']);
            if (!empty($data['adjuntos'])) $this->repository->addAttachments($notaId, $data['adjuntos']);

            // Notifications
            $this->notifyMembers($notaId, $data['titulo'], $data['miembros'] ?? [], $creatorUserId, 'task_assign', 'Nueva Tarea Asignada', 'Te han asignado la tarea: ');

            $this->repository->commit();
            return $notaId;

        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function updateNote($id, $data, $updaterUserId) {
        if (!$id) throw new Exception("ID de nota no proporcionado");

        // Special case: Move Drag&Drop
        if (isset($data['action']) && $data['action'] === 'move') {
            return $this->repository->moveNote($id, $data['estado'], $data['orden'] ?? 0);
        }

        $this->repository->beginTransaction();
        try {
             $noteData = [
                'titulo' => $data['titulo'],
                'cliente_nombre' => $data['cliente_nombre'] ?? null,
                'telefono' => $data['telefono'] ?? null,
                'email' => $data['email'] ?? null,
                'contenido' => $data['contenido'] ?? '',
                'estado' => $data['estado'] ?? 'pendiente',
                'prioridad' => $data['prioridad'] ?? 'media',
                'fecha_entrega' => !empty($data['fecha_entrega']) ? $data['fecha_entrega'] : null,
                'etiquetas' => $data['etiquetas'] ?? null,
                'miembros' => $data['miembros'] ?? null
            ];

            $this->repository->update($id, $noteData);

            // Re-create relations
            $this->repository->clearRelations($id);
            if (!empty($data['items'])) $this->repository->addItems($id, $data['items']);
            if (!empty($data['checklist'])) $this->repository->addChecklist($id, $data['checklist']);
            if (!empty($data['adjuntos'])) $this->repository->addAttachments($id, $data['adjuntos']);

            // Notifications
            $this->notifyMembers($id, $data['titulo'], $data['miembros'] ?? [], $updaterUserId, 'task_assign', 'Tarea Actualizada', 'Se ha actualizado una tarea asignada: ');

            $this->repository->commit();
            return true;
        } catch (Exception $e) {
            $this->repository->rollBack();
            throw $e;
        }
    }

    public function deleteNote($id) {
        if (!$id) throw new Exception("ID no proporcionado");
        return $this->repository->delete($id);
    }

    private function notifyMembers($notaId, $title, $members, $senderId, $type, $subject, $messagePrefix) {
        // Decode members if it's a JSON string (comes from frontend as JSON.stringify)
        if (is_string($members)) {
            $members = json_decode($members, true);
        }

        if (!empty($members) && is_array($members)) {
            $pdo = getDbConnection();

            foreach ($members as $member) {
                // Each member is an object {id, name}
                $memberId = is_array($member) ? ($member['id'] ?? null) : $member;
                if ($memberId && $memberId != $senderId) {
                    createNotification($pdo, $memberId, $type, $subject, $messagePrefix . $title, 'todo.php?id=' . $notaId);
                }
            }
        }
    }
}
