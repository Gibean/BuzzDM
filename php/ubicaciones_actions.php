<?php
/**
 * API REST para Ubicaciones
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

    // GET - Listar ubicaciones de un almacén
    if ($method === 'GET') {
        $almacenId = $_GET['almacen_id'] ?? null;

        if (!$almacenId) {
            json_response(false, [], 'almacen_id es requerido');
        }

        $sql = "
            SELECT u.*,
                   COALESCE((SELECT COUNT(DISTINCT producto_id) FROM stock_ubicacion WHERE ubicacion_id = u.id), 0) as productos_count
            FROM ubicaciones u
            WHERE u.almacen_id = ?
            ORDER BY u.codigo ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$almacenId]);
        $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        json_response(true, ['ubicaciones' => $ubicaciones]);
    }

    // POST - Crear ubicación
    elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['almacen_id']) || empty($input['codigo'])) {
            json_response(false, [], 'almacen_id y código son obligatorios');
        }

        // Verificar código único dentro del almacén
        $stmt = $pdo->prepare("SELECT id FROM ubicaciones WHERE almacen_id = ? AND codigo = ?");
        $stmt->execute([$input['almacen_id'], trim($input['codigo'])]);
        if ($stmt->fetch()) {
            json_response(false, [], 'El código ya existe en este almacén');
        }

        $sql = "INSERT INTO ubicaciones (almacen_id, codigo, nombre, pasillo, estante, nivel, capacidad_maxima, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['almacen_id'],
            trim($input['codigo']),
            trim($input['nombre'] ?? ''),
            trim($input['pasillo'] ?? ''),
            trim($input['estante'] ?? ''),
            trim($input['nivel'] ?? ''),
            $input['capacidad_maxima'] ?: null,
            isset($input['activo']) ? ($input['activo'] ? 1 : 0) : 1
        ]);

        json_response(true, ['id' => $pdo->lastInsertId()], 'Ubicación creada exitosamente');
    }

    // PUT - Actualizar ubicación
    elseif ($method === 'PUT') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['codigo'])) {
            json_response(false, [], 'El código es obligatorio');
        }

        // Obtener almacen_id actual
        $stmt = $pdo->prepare("SELECT almacen_id FROM ubicaciones WHERE id = ?");
        $stmt->execute([$id]);
        $ubicacion = $stmt->fetch();

        if (!$ubicacion) {
            json_response(false, [], 'Ubicación no encontrada');
        }

        // Verificar código único (excluyendo este registro)
        $stmt = $pdo->prepare("SELECT id FROM ubicaciones WHERE almacen_id = ? AND codigo = ? AND id != ?");
        $stmt->execute([$ubicacion['almacen_id'], trim($input['codigo']), $id]);
        if ($stmt->fetch()) {
            json_response(false, [], 'El código ya existe en este almacén');
        }

        $sql = "UPDATE ubicaciones SET
                    codigo = ?, nombre = ?, pasillo = ?, estante = ?,
                    nivel = ?, capacidad_maxima = ?, activo = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            trim($input['codigo']),
            trim($input['nombre'] ?? ''),
            trim($input['pasillo'] ?? ''),
            trim($input['estante'] ?? ''),
            trim($input['nivel'] ?? ''),
            $input['capacidad_maxima'] ?: null,
            isset($input['activo']) ? ($input['activo'] ? 1 : 0) : 1,
            $id
        ]);

        json_response(true, [], 'Ubicación actualizada exitosamente');
    }

    // DELETE - Eliminar ubicación
    elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) json_response(false, [], 'ID no proporcionado');

        // Verificar si tiene stock asignado
        $stmt = $pdo->prepare("SELECT SUM(cantidad) as total FROM stock_ubicacion WHERE ubicacion_id = ?");
        $stmt->execute([$id]);
        $stock = $stmt->fetch();

        if ($stock && $stock['total'] > 0) {
            json_response(false, [], 'No se puede eliminar: tiene productos asignados');
        }

        // Eliminar ubicación
        $stmt = $pdo->prepare("DELETE FROM ubicaciones WHERE id = ?");
        $stmt->execute([$id]);

        json_response(true, [], 'Ubicación eliminada exitosamente');
    }

    else {
        json_response(false, [], 'Método no permitido');
    }

} catch (PDOException $e) {
    json_response(false, [], 'Error de base de datos: ' . $e->getMessage());
} catch (Exception $e) {
    json_response(false, [], 'Error del servidor: ' . $e->getMessage());
}
