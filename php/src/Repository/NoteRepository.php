<?php
require_once __DIR__ . '/../../db.php';

class NoteRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = getDbConnection();
    }

    public function beginTransaction() {
        if (!$this->pdo->inTransaction()) {
            $this->pdo->beginTransaction();
        }
    }

    public function commit() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollBack() {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM notas_kanban WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAllGroupedByStatus() {
        // 1. Get Notes with counts
        $stmt = $this->pdo->prepare("
            SELECT n.*, u.nombre as usuario_nombre,
                   (SELECT COUNT(*) FROM notas_checklists WHERE nota_id = n.id) as total_check,
                   (SELECT COUNT(*) FROM notas_checklists WHERE nota_id = n.id AND completada = 1) as total_completadas,
                   (SELECT COUNT(*) FROM notas_adjuntos WHERE nota_id = n.id) as total_adjuntos,
                   (SELECT COUNT(*) FROM notas_items_inventario WHERE nota_id = n.id) as total_productos
            FROM notas_kanban n
            JOIN usuarios u ON n.usuario_id = u.id
            ORDER BY n.estado, n.orden ASC, n.creado_at DESC
        ");
        $stmt->execute();
        $notas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($notas)) return [];

        // 2. Eager Load Details for all notes to minimize N+1
        $noteIds = array_column($notas, 'id');
        $placeholders = implode(',', array_fill(0, count($noteIds), '?'));

        // Checklists
        $stmtCheck = $this->pdo->prepare("SELECT * FROM notas_checklists WHERE nota_id IN ($placeholders) ORDER BY nota_id, orden ASC");
        $stmtCheck->execute($noteIds);
        $allChecklists = $stmtCheck->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // Items
        $stmtItems = $this->pdo->prepare("
            SELECT i.*, p.nombre as producto_nombre, p.sku, p.sku as producto_sku
            FROM notas_items_inventario i
            JOIN productos p ON i.producto_id = p.id
            WHERE i.nota_id IN ($placeholders)
        ");
        $stmtItems->execute($noteIds);
        $allItems = $stmtItems->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // Adjuntos (Attachments)
        $stmtAdj = $this->pdo->prepare("SELECT * FROM notas_adjuntos WHERE nota_id IN ($placeholders) ORDER BY nota_id, creado_at DESC");
        $stmtAdj->execute($noteIds);
        $allAdjuntos = $stmtAdj->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        // Map back
        foreach ($notas as &$nota) {
            $id = $nota['id'];
            $nota['checklist'] = $allChecklists[$id] ?? [];
            $nota['items'] = $allItems[$id] ?? [];
            $nota['adjuntos'] = $allAdjuntos[$id] ?? [];
        }

        return $notas;
    }

    public function findDetails($notaId) {
        $details = [];

        // Checklists
        $stmtCheck = $this->pdo->prepare("SELECT * FROM notas_checklists WHERE nota_id = ? ORDER BY orden ASC");
        $stmtCheck->execute([$notaId]);
        $details['checklist'] = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

        // Items
        $stmtItems = $this->pdo->prepare("
            SELECT i.*, p.nombre as producto_nombre, p.sku, p.precio
            FROM notas_items_inventario i
            JOIN productos p ON i.producto_id = p.id
            WHERE i.nota_id = ?
        ");
        $stmtItems->execute([$notaId]);
        $details['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Adjuntos
        $stmtAdj = $this->pdo->prepare("SELECT * FROM notas_adjuntos WHERE nota_id = ? ORDER BY creado_at DESC");
        $stmtAdj->execute([$notaId]);
        $details['adjuntos'] = $stmtAdj->fetchAll(PDO::FETCH_ASSOC);

        return $details;
    }

    public function create(array $data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO notas_kanban (titulo, cliente_nombre, telefono, email, contenido, estado, prioridad, usuario_id, fecha_entrega, etiquetas, miembros)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['titulo'],
            $data['cliente_nombre'] ?? null,
            $data['telefono'] ?? null,
            $data['email'] ?? null,
            $data['contenido'] ?? '',
            $data['estado'] ?? 'pendiente',
            $data['prioridad'] ?? 'media',
            $data['usuario_id'],
            $data['fecha_entrega'] ?? null,
            $data['etiquetas'] ?? null,
            $data['miembros'] ?? null // JSON string
        ]);
        return $this->pdo->lastInsertId();
    }

    public function update($id, array $data) {
        $stmt = $this->pdo->prepare("
            UPDATE notas_kanban
            SET titulo = ?, cliente_nombre = ?, telefono = ?, email = ?, contenido = ?, estado = ?, prioridad = ?, fecha_entrega = ?, etiquetas = ?, miembros = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['titulo'],
            $data['cliente_nombre'],
            $data['telefono'],
            $data['email'],
            $data['contenido'],
            $data['estado'],
            $data['prioridad'],
            $data['fecha_entrega'],
            $data['etiquetas'],
            $data['miembros'],
            $id
        ]);
    }

    public function moveNote($id, $estado, $orden) {
        $stmt = $this->pdo->prepare("UPDATE notas_kanban SET estado = ?, orden = ? WHERE id = ?");
        return $stmt->execute([$estado, $orden, $id]);
    }

    public function delete($id) {
        // DB constraints usually handle cascading deletes, but enforcing here is safer if FKs not strict
        // Assuming MyISAM/InnoDB mixed or loose constraints, explicit delete logic is good.
        // Actually the original code just deleted the note. If FKs are set to CASCADE, children go too.
        // In notas_actions.php: straight DELETE. We will assume standard DELETE works.
        $stmt = $this->pdo->prepare("DELETE FROM notas_kanban WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- Sub-Entity Management ---

    public function clearRelations($notaId) {
        $this->pdo->prepare("DELETE FROM notas_items_inventario WHERE nota_id = ?")->execute([$notaId]);
        $this->pdo->prepare("DELETE FROM notas_checklists WHERE nota_id = ?")->execute([$notaId]);
        $this->pdo->prepare("DELETE FROM notas_adjuntos WHERE nota_id = ?")->execute([$notaId]);
    }

    public function addItems($notaId, array $items) {
        $stmt = $this->pdo->prepare("INSERT INTO notas_items_inventario (nota_id, producto_id, cantidad) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$notaId, $item['producto_id'], $item['cantidad'] ?? 1]);
        }
    }

    public function addChecklist($notaId, array $checklist) {
        $stmt = $this->pdo->prepare("INSERT INTO notas_checklists (nota_id, tarea, completada, orden) VALUES (?, ?, ?, ?)");
        foreach ($checklist as $idx => $check) {
            $stmt->execute([$notaId, $check['tarea'], $check['completada'] ?? 0, $idx]);
        }
    }

    public function addAttachments($notaId, array $adjuntos) {
         $stmt = $this->pdo->prepare("INSERT INTO notas_adjuntos (nota_id, nombre, url) VALUES (?, ?, ?)");
         foreach ($adjuntos as $adj) {
             $stmt->execute([$notaId, $adj['nombre'] ?? 'Sin nombre', $adj['url']]);
         }
    }
}
