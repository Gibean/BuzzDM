<?php
/**
 * API REST para Almacenes
 * Sistema Multi-Almacén BuzzDM
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

    // GET - Listar almacenes o obtener uno específico
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Obtener almacén específico con sus ubicaciones
            $stmt = $pdo->prepare("
                SELECT a.*,
                       (SELECT COUNT(*) FROM ubicaciones WHERE almacen_id = a.id) as total_ubicaciones,
                       (SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE almacen_id = a.id) as total_productos
                FROM almacenes a
                WHERE a.id = ?
            ");
            $stmt->execute([$_GET['id']]);
            $almacen = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($almacen) {
                // Obtener ubicaciones
                $stmtUbi = $pdo->prepare("SELECT * FROM ubicaciones WHERE almacen_id = ? ORDER BY codigo");
                $stmtUbi->execute([$_GET['id']]);
                $almacen['ubicaciones'] = $stmtUbi->fetchAll(PDO::FETCH_ASSOC);

                json_response(true, ['almacen' => $almacen]);
            } else {
                json_response(false, [], 'Almacén no encontrado');
            }
        } else {
            // Listar todos los almacenes
            $showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] === 'true';
            $whereClause = $showInactive ? '1=1' : 'a.activo = 1';

            $sql = "
                SELECT a.*,
                       (SELECT COUNT(*) FROM ubicaciones WHERE almacen_id = a.id AND activo = 1) as total_ubicaciones,
                       (SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE almacen_id = a.id AND cantidad > 0) as total_productos
                FROM almacenes a
                WHERE $whereClause
                ORDER BY a.es_principal DESC, a.nombre ASC
            ";
            $almacenes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            // Obtener estadísticas
            $stats = [
                'total_almacenes' => $pdo->query("SELECT COUNT(*) FROM almacenes WHERE activo = 1")->fetchColumn(),
                'total_ubicaciones' => $pdo->query("SELECT COUNT(*) FROM ubicaciones WHERE activo = 1")->fetchColumn(),
                'total_productos' => $pdo->query("SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE cantidad > 0")->fetchColumn(),
                'almacen_principal' => $pdo->query("SELECT nombre FROM almacenes WHERE es_principal = 1 LIMIT 1")->fetchColumn() ?: 'N/A'
            ];

            json_response(true, ['almacenes' => $almacenes, 'stats' => $stats]);
        }
    }

    // POST - Crear almacén
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['codigo']) || empty($input['nombre'])) {
            json_response(false, [], 'Código y nombre son obligatorios');
        }

        // Verificar código único
        $stmt = $pdo->prepare("SELECT id FROM almacenes WHERE codigo = ?");
        $stmt->execute([trim($input['codigo'])]);
        if ($stmt->fetch()) {
            json_response(false, [], 'El código ya existe');
        }

        // Si es principal, quitar el flag de los demás
        if (!empty($input['es_principal'])) {
            $pdo->exec("UPDATE almacenes SET es_principal = 0");
        }

        $sql = "INSERT INTO almacenes (codigo, nombre, direccion, telefono, tipo, es_principal, activo, notas)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($input['codigo']),
            trim($input['nombre']),
            trim($input['direccion'] ?? ''),
            trim($input['telefono'] ?? ''),
            $input['tipo'] ?? 'almacen',
            $input['es_principal'] ? 1 : 0,
            isset($input['activo']) ? ($input['activo'] ? 1 : 0) : 1,
            trim($input['notas'] ?? '')
        ]);

        json_response(true, ['id' => $pdo->lastInsertId()], 'Almacén creado exitosamente');
    }

    // PUT - Actualizar almacén
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['codigo']) || empty($input['nombre'])) {
            json_response(false, [], 'Código y nombre son obligatorios');
        }

        // Verificar código único (excluyendo este registro)
        $stmt = $pdo->prepare("SELECT id FROM almacenes WHERE codigo = ? AND id != ?");
        $stmt->execute([trim($input['codigo']), $id]);
        if ($stmt->fetch()) {
            json_response(false, [], 'El código ya existe');
        }

        // Si es principal, quitar el flag de los demás
        if (!empty($input['es_principal'])) {
            $pdo->prepare("UPDATE almacenes SET es_principal = 0 WHERE id != ?")->execute([$id]);
        }

        $sql = "UPDATE almacenes SET
                    codigo = ?, nombre = ?, direccion = ?, telefono = ?,
                    tipo = ?, es_principal = ?, activo = ?, notas = ?,
                    actualizado_at = NOW()
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($input['codigo']),
            trim($input['nombre']),
            trim($input['direccion'] ?? ''),
            trim($input['telefono'] ?? ''),
            $input['tipo'] ?? 'almacen',
            $input['es_principal'] ? 1 : 0,
            isset($input['activo']) ? ($input['activo'] ? 1 : 0) : 1,
            trim($input['notas'] ?? ''),
            $id
        ]);

        json_response(true, [], 'Almacén actualizado exitosamente');
    }

    // DELETE - Desactivar almacén
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        // Verificar si es el almacén principal
        $stmt = $pdo->prepare("SELECT es_principal FROM almacenes WHERE id = ?");
        $stmt->execute([$id]);
        $almacen = $stmt->fetch();

        if ($almacen && $almacen['es_principal']) {
            json_response(false, [], 'No se puede desactivar el almacén principal');
        }

        // Soft delete - solo desactivar
        $stmt = $pdo->prepare("UPDATE almacenes SET activo = 0, actualizado_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);

        json_response(true, [], 'Almacén desactivado exitosamente');
    }

    else {
        json_response(false, [], 'Método no permitido');
    }

} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
