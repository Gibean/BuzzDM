<?php
/**
 * API REST para Componentes de Kits
 * Sistema de Kits/Combos BuzzDM
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

function json_response($success, $data = [], $message = '') {
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if (!empty($data)) $response = array_merge($response, $data);
    echo json_encode($response);
    exit;
}

try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET - Obtener componentes de un kit
    if ($method === 'GET') {
        $kitId = $_GET['kit_id'] ?? null;

        if (!$kitId) {
            json_response(false, [], 'kit_id es requerido');
        }

        // Obtener componentes con detalles
        $sql = "SELECT
                    pc.id,
                    pc.kit_id,
                    pc.componente_id,
                    p.sku AS componente_sku,
                    p.nombre AS componente_nombre,
                    p.tipo_producto AS componente_tipo,
                    p.precio AS componente_precio,
                    p.stock AS componente_stock,
                    pc.cantidad,
                    (pc.cantidad * p.precio) AS subtotal,
                    pc.orden,
                    CASE
                        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual = 1
                        THEN (SELECT fn_stock_kit_virtual(p.id))
                        WHEN p.tipo_producto = 'servicio' THEN 999999
                        ELSE p.stock
                    END AS stock_disponible
                FROM producto_componentes pc
                INNER JOIN productos p ON p.id = pc.componente_id
                WHERE pc.kit_id = ?
                ORDER BY pc.orden, pc.id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kitId]);
        $componentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcular stock disponible del kit (si es virtual)
        $stmtKit = $pdo->prepare("SELECT es_kit_virtual FROM productos WHERE id = ?");
        $stmtKit->execute([$kitId]);
        $kit = $stmtKit->fetch();

        $stockKit = null;
        if ($kit && $kit['es_kit_virtual']) {
            $stmtStock = $pdo->prepare("SELECT fn_stock_kit_virtual(?)");
            $stmtStock->execute([$kitId]);
            $stockKit = $stmtStock->fetchColumn();
        }

        // Calcular totales
        $totalComponentes = count($componentes);
        $costoTotal = array_reduce($componentes, function($sum, $c) {
            return $sum + ($c['subtotal'] ?? 0);
        }, 0);

        json_response(true, [
            'componentes' => $componentes,
            'stock_kit' => $stockKit,
            'total_componentes' => $totalComponentes,
            'costo_componentes' => $costoTotal
        ]);
    }

    // POST - Agregar componente a kit
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $kitId = $input['kit_id'] ?? null;
        $componenteId = $input['componente_id'] ?? null;
        $cantidad = floatval($input['cantidad'] ?? 1);

        if (!$kitId || !$componenteId) {
            json_response(false, [], 'kit_id y componente_id son requeridos');
        }

        if ($cantidad <= 0) {
            json_response(false, [], 'La cantidad debe ser mayor a 0');
        }

        // Verificar que el kit existe y es de tipo kit
        $stmt = $pdo->prepare("SELECT tipo_producto FROM productos WHERE id = ?");
        $stmt->execute([$kitId]);
        $kit = $stmt->fetch();

        if (!$kit || $kit['tipo_producto'] !== 'kit') {
            json_response(false, [], 'El producto no es un kit');
        }

        // Verificar que el componente existe
        $stmt = $pdo->prepare("SELECT id FROM productos WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$componenteId]);
        if (!$stmt->fetch()) {
            json_response(false, [], 'El componente no existe');
        }

        // Evitar que un kit sea componente de sí mismo
        if ($kitId == $componenteId) {
            json_response(false, [], 'Un kit no puede ser componente de sí mismo');
        }

        // Obtener orden máximo
        $stmt = $pdo->prepare("SELECT MAX(orden) FROM producto_componentes WHERE kit_id = ?");
        $stmt->execute([$kitId]);
        $maxOrden = $stmt->fetchColumn() ?? 0;

        // Insertar o actualizar
        $sql = "INSERT INTO producto_componentes (kit_id, componente_id, cantidad, orden)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$kitId, $componenteId, $cantidad, $maxOrden + 1]);

        json_response(true, ['id' => $pdo->lastInsertId()], 'Componente agregado');
    }

    // PUT - Actualizar cantidad de componente
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        $input = json_decode(file_get_contents('php://input'), true);
        $cantidad = floatval($input['cantidad'] ?? 0);
        $orden = isset($input['orden']) ? intval($input['orden']) : null;

        if ($cantidad <= 0) {
            json_response(false, [], 'La cantidad debe ser mayor a 0');
        }

        $sql = "UPDATE producto_componentes SET cantidad = ?";
        $params = [$cantidad];

        if ($orden !== null) {
            $sql .= ", orden = ?";
            $params[] = $orden;
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        json_response(true, [], 'Componente actualizado');
    }

    // DELETE - Eliminar componente de kit
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        $stmt = $pdo->prepare("DELETE FROM producto_componentes WHERE id = ?");
        $stmt->execute([$id]);

        json_response(true, [], 'Componente eliminado');
    }

    else {
        json_response(false, [], 'Método no permitido');
    }

} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
