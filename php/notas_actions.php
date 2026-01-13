<?php
/**
 * BuzzDM - Notas Kanban Actions API
 */

require_once 'sesion.php';
require_once 'db.php';
require_once 'notification_functions.php';

header('Content-Type: application/json');

function json_response($success, $data = [], $message = '') {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $notaId = (int)$_GET['id'];
                $stmt = $pdo->prepare("SELECT * FROM notas_kanban WHERE id = ?");
                $stmt->execute([$notaId]);
                $nota = $stmt->fetch();

                if ($nota) {
                    // Productos
                    $stmtItems = $pdo->prepare("
                        SELECT i.*, p.nombre as producto_nombre, p.sku, p.precio
                        FROM notas_items_inventario i
                        JOIN productos p ON i.producto_id = p.id
                        WHERE i.nota_id = ?
                    ");
                    $stmtItems->execute([$notaId]);
                    $nota['items'] = $stmtItems->fetchAll();

                    // Checklists
                    $stmtCheck = $pdo->prepare("SELECT * FROM notas_checklists WHERE nota_id = ? ORDER BY orden ASC");
                    $stmtCheck->execute([$notaId]);
                    $nota['checklist'] = $stmtCheck->fetchAll();

                    // Adjuntos
                    $stmtAdj = $pdo->prepare("SELECT * FROM notas_adjuntos WHERE nota_id = ? ORDER BY creado_at DESC");
                    $stmtAdj->execute([$notaId]);
                    $nota['adjuntos'] = $stmtAdj->fetchAll();

                    json_response(true, $nota);
                }
                json_response(false, null, "Nota no encontrada");
            }

            // 1. Obtener todas las notas agrupadas por estado (Kanban) con contadores de Trello
            $stmt = $pdo->prepare("
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
            $notas = $stmt->fetchAll();

            // 2. Fetch all details for these notes to provide data for previews and view mode
            if (!empty($notas)) {
                $noteIds = array_map(function($n) { return $n['id']; }, $notas);
                $placeholders = implode(',', array_fill(0, count($noteIds), '?'));

                // Fetch all checklist items
                $stmtCheck = $pdo->prepare("SELECT * FROM notas_checklists WHERE nota_id IN ($placeholders) ORDER BY nota_id, orden ASC");
                $stmtCheck->execute($noteIds);
                $allChecklists = $stmtCheck->fetchAll(PDO::FETCH_GROUP);

                // Fetch all linked products
                $stmtItems = $pdo->prepare("
                    SELECT i.*, p.nombre as producto_nombre, p.sku
                    FROM notas_items_inventario i
                    JOIN productos p ON i.producto_id = p.id
                    WHERE i.nota_id IN ($placeholders)
                ");
                $stmtItems->execute($noteIds);
                $allItems = $stmtItems->fetchAll(PDO::FETCH_GROUP);

                // Fetch all attachments
                $stmtAdj = $pdo->prepare("SELECT * FROM notas_adjuntos WHERE nota_id IN ($placeholders) ORDER BY nota_id, creado_at DESC");
                $stmtAdj->execute($noteIds);
                $allAdjuntos = $stmtAdj->fetchAll(PDO::FETCH_GROUP);

                // Map details back to notes
                foreach ($notas as &$nota) {
                    $id = $nota['id'];
                    $nota['checklist'] = $allChecklists[$id] ?? [];
                    $nota['items'] = $allItems[$id] ?? [];
                    $nota['adjuntos'] = $allAdjuntos[$id] ?? [];
                }
            }

            json_response(true, $notas);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['titulo'])) {
                json_response(false, null, "El título es obligatorio");
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO notas_kanban (titulo, cliente_nombre, telefono, email, contenido, estado, prioridad, usuario_id, fecha_entrega, etiquetas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $fecha_entrega = !empty($input['fecha_entrega']) ? $input['fecha_entrega'] : null;

            $stmt->execute([
                $input['titulo'],
                $input['cliente_nombre'] ?? null,
                $input['telefono'] ?? null,
                $input['contenido'] ?? '',
                $input['estado'] ?? 'pendiente',
                $input['prioridad'] ?? 'media',
                $_SESSION['usuario_id'],
                $fecha_entrega,
                $input['etiquetas'] ?? null,
                $miembrosJson
            ]);

            $notaId = $pdo->lastInsertId();

            // Insertar productos vinculados
            if (!empty($input['items']) && is_array($input['items'])) {
                $stmtItem = $pdo->prepare("INSERT INTO notas_items_inventario (nota_id, producto_id, cantidad) VALUES (?, ?, ?)");
                foreach ($input['items'] as $item) {
                    $stmtItem->execute([$notaId, $item['producto_id'], $item['cantidad'] ?? 1]);
                }
            }

            // Checklist
            if (!empty($input['checklist']) && is_array($input['checklist'])) {
                $stmtCheck = $pdo->prepare("INSERT INTO notas_checklists (nota_id, tarea, completada, orden) VALUES (?, ?, ?, ?)");
                foreach ($input['checklist'] as $idx => $check) {
                    $stmtCheck->execute([$notaId, $check['tarea'], $check['completada'] ?? 0, $idx]);
                }
            }

            // Adjuntos (Links)
            if (!empty($input['adjuntos']) && is_array($input['adjuntos'])) {
                $stmtAdj = $pdo->prepare("INSERT INTO notas_adjuntos (nota_id, nombre, url) VALUES (?, ?, ?)");
                foreach ($input['adjuntos'] as $adj) {
                    $stmtAdj->execute([$notaId, $adj['nombre'] ?? 'Sin nombre', $adj['url']]);
                }
            }

            // Notify Assigned Members
            if (!empty($input['miembros']) && is_array($input['miembros'])) {
                foreach ($input['miembros'] as $memberId) {
                    if ($memberId != $_SESSION['usuario_id']) {
                        createNotification($pdo, $memberId, 'task_assign', 'Nueva Tarea Asignada', 'Te han asignado la tarea: ' . $input['titulo'], 'todo.php?id=' . $notaId);
                    }
                }
            }

            $pdo->commit();
            json_response(true, ['id' => $notaId], "Nota creada correctamente");
            break;

        case 'PUT':
            $input = json_decode(file_get_contents('php://input'), true);
            $notaId = $input['id'] ?? null;

            if (!$notaId) json_response(false, null, "ID de nota no proporcionado");

            // Caso especial: Mover entre columnas (Drag & Drop)
            if (isset($input['action']) && $input['action'] === 'move') {
                $stmt = $pdo->prepare("UPDATE notas_kanban SET estado = ?, orden = ? WHERE id = ?");
                $stmt->execute([$input['estado'], $input['orden'] ?? 0, $notaId]);
                json_response(true, null, "Movido correctamente");
            }

            // Actualización completa
            $pdo->beginTransaction();

            // Handle Miembros
            $miembrosJson = null;
            if (isset($input['miembros']) && is_array($input['miembros'])) {
                 $miembrosJson = json_encode($input['miembros']);
            }

            $stmt = $pdo->prepare("
                UPDATE notas_kanban
                SET titulo = ?, cliente_nombre = ?, telefono = ?, email = ?, contenido = ?, estado = ?, prioridad = ?, fecha_entrega = ?, etiquetas = ?, miembros = ?
                WHERE id = ?
            ");
            $fecha_entrega = !empty($input['fecha_entrega']) ? $input['fecha_entrega'] : null;

            $stmt->execute([
                $input['titulo'],
                $input['cliente_nombre'] ?? null,
                $input['telefono'] ?? null,
                $input['email'] ?? null,
                $input['contenido'] ?? '',
                $input['estado'] ?? 'pendiente',
                $input['prioridad'] ?? 'media',
                $fecha_entrega,
                $input['etiquetas'] ?? null,
                $miembrosJson,
                $notaId
            ]);

            // Actualizar items de inventario
            $pdo->prepare("DELETE FROM notas_items_inventario WHERE nota_id = ?")->execute([$notaId]);
            if (!empty($input['items']) && is_array($input['items'])) {
                $stmtItem = $pdo->prepare("INSERT INTO notas_items_inventario (nota_id, producto_id, cantidad) VALUES (?, ?, ?)");
                foreach ($input['items'] as $item) {
                    $stmtItem->execute([$notaId, $item['producto_id'], $item['cantidad'] ?? 1]);
                }
            }

            // Actualizar Checklist
            $pdo->prepare("DELETE FROM notas_checklists WHERE nota_id = ?")->execute([$notaId]);
            if (!empty($input['checklist']) && is_array($input['checklist'])) {
                $stmtCheck = $pdo->prepare("INSERT INTO notas_checklists (nota_id, tarea, completada, orden) VALUES (?, ?, ?, ?)");
                foreach ($input['checklist'] as $idx => $check) {
                    $stmtCheck->execute([$notaId, $check['tarea'], $check['completada'] ?? 0, $idx]);
                }
            }

            // Actualizar Adjuntos
            $pdo->prepare("DELETE FROM notas_adjuntos WHERE nota_id = ?")->execute([$notaId]);
            if (!empty($input['adjuntos']) && is_array($input['adjuntos'])) {
                $stmtAdj = $pdo->prepare("INSERT INTO notas_adjuntos (nota_id, nombre, url) VALUES (?, ?, ?)");
                foreach ($input['adjuntos'] as $adj) {
                    $stmtAdj->execute([$notaId, $adj['nombre'] ?? 'Sin nombre', $adj['url']]);
                }
            }

            // Notify Assigned Members (Simple Approach: Notify all current members on update)
            // Ideally we should check diff, but notifying on update is also valid "Task Updated"
            if (!empty($input['miembros']) && is_array($input['miembros'])) {
                foreach ($input['miembros'] as $memberId) {
                    if ($memberId != $_SESSION['usuario_id']) {
                        createNotification($pdo, $memberId, 'task_assign', 'Tarea Actualizada', 'Se ha actualizado una tarea asignada: ' . $input['titulo'], 'todo.php?id=' . $notaId);
                    }
                }
            }

            $pdo->commit();
            json_response(true, null, "Nota actualizada");
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? null;
            if (!$id) json_response(false, null, "ID no proporcionado");

            $stmt = $pdo->prepare("DELETE FROM notas_kanban WHERE id = ?");
            $stmt->execute([$id]);
            json_response(true, null, "Nota eliminada");
            break;

        default:
            json_response(false, null, "Método no permitido");
            break;
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    json_response(false, null, "Error: " . $e->getMessage());
}
