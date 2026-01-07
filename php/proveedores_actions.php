<?php
/**
 * API REST para Proveedores
 * GET - Listar proveedores
 * POST - Crear proveedor
 * PUT - Actualizar proveedor
 * DELETE - Eliminar proveedor
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

try {
    switch ($method) {
        case 'GET':
            listarProveedores($pdo);
            break;
        case 'POST':
            crearProveedor($pdo);
            break;
        case 'PUT':
            actualizarProveedor($pdo);
            break;
        case 'DELETE':
            eliminarProveedor($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function listarProveedores($pdo) {
    $sql = "SELECT id, nombre, rif, telefono, email, direccion, notas, creado_at
            FROM proveedores
            WHERE deleted_at IS NULL
            ORDER BY nombre ASC";

    $stmt = $pdo->query($sql);
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'proveedores' => $proveedores
    ]);
}

function crearProveedor($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nombre'])) {
        throw new Exception('El nombre es obligatorio');
    }

    $sql = "INSERT INTO proveedores (nombre, rif, telefono, email, direccion, notas)
            VALUES (:nombre, :rif, :telefono, :email, :direccion, :notas)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => trim($data['nombre']),
        ':rif' => trim($data['rif'] ?? ''),
        ':telefono' => trim($data['telefono'] ?? ''),
        ':email' => trim($data['email'] ?? ''),
        ':direccion' => trim($data['direccion'] ?? ''),
        ':notas' => trim($data['notas'] ?? '')
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Proveedor creado correctamente',
        'id' => $pdo->lastInsertId()
    ]);
}

function actualizarProveedor($pdo) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de proveedor requerido');
    }

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nombre'])) {
        throw new Exception('El nombre es obligatorio');
    }

    $sql = "UPDATE proveedores SET
                nombre = :nombre,
                rif = :rif,
                telefono = :telefono,
                email = :email,
                direccion = :direccion,
                notas = :notas,
                actualizado_at = NOW()
            WHERE id = :id AND deleted_at IS NULL";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => trim($data['nombre']),
        ':rif' => trim($data['rif'] ?? ''),
        ':telefono' => trim($data['telefono'] ?? ''),
        ':email' => trim($data['email'] ?? ''),
        ':direccion' => trim($data['direccion'] ?? ''),
        ':notas' => trim($data['notas'] ?? ''),
        ':id' => $id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Proveedor actualizado correctamente'
    ]);
}

function eliminarProveedor($pdo) {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('ID de proveedor requerido');
    }

    // Soft delete
    $sql = "UPDATE proveedores SET deleted_at = NOW() WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Proveedor eliminado correctamente'
    ]);
}
?>
