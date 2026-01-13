<?php
/**
 * API para gestión de catálogos maestros
 * Soporta CRUD para Categorías, Marcas, Colores y Materiales
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

try {
    // Si es GET, puede ser para selects (formato resumido) o para la gestión (formato completo)
    if ($method === 'GET') {
        $type = $_GET['type'] ?? 'all'; // 'resumen' para selects, 'all' para gestión completa

        if ($type === 'resumen') {
            // Formato original usado por pedidos/inventario
            $marcas = $pdo->query("SELECT id, nombre FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $proveedores = $pdo->query("SELECT id, nombre FROM proveedores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $colores = $pdo->query("SELECT id, nombre FROM colores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $materiales = $pdo->query("SELECT id, nombre FROM materiales ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'catalogos' => [
                    'marcas' => $marcas,
                    'categorias' => $categorias,
                    'proveedores' => $proveedores,
                    'colores' => $colores,
                    'materiales' => $materiales
                ]
            ]);
            exit;
        }

        // Gestión completa según tabla
        $catalog = $_GET['catalog'] ?? null;
        if (!$catalog) {
            // Cargar todo para la página inicial de catálogos
            $marcas = $pdo->query("SELECT * FROM marcas ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $colores = $pdo->query("SELECT * FROM colores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
            $materiales = $pdo->query("SELECT * FROM materiales ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'marcas' => $marcas,
                'categorias' => $categorias,
                'colores' => $colores,
                'materiales' => $materiales
            ]);
            exit;
        }

        // Caso específico de una tabla
        $allowed = ['categorias', 'marcas', 'colores', 'materiales'];
        if (!in_array($catalog, $allowed)) throw new Exception("Catálogo no válido");

        $stmt = $pdo->query("SELECT * FROM $catalog ORDER BY nombre");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    }

    // POST: Crear nuevo registro
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $catalog = $data['catalog'] ?? null;

        if ($catalog === 'categorias') {
            $stmt = $pdo->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (?, ?)");
            $stmt->execute([$data['nombre'], $data['descripcion'] ?? '']);
        } elseif ($catalog === 'marcas') {
            $stmt = $pdo->prepare("INSERT INTO marcas (nombre, descripcion, sitio_web) VALUES (?, ?, ?)");
            $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $data['sitio_web'] ?? '']);
        } elseif ($catalog === 'colores') {
            $stmt = $pdo->prepare("INSERT INTO colores (nombre, codigo_hex) VALUES (?, ?)");
            $stmt->execute([$data['nombre'], $data['codigo_hex'] ?? '#000000']);
        } elseif ($catalog === 'materiales') {
            $stmt = $pdo->prepare("INSERT INTO materiales (nombre) VALUES (?)");
            $stmt->execute([$data['nombre']]);
        } else {
            throw new Exception("Operación no permitida");
        }

        echo json_encode(['success' => true, 'message' => 'Registro creado exitosamente']);
    }

    // PUT: Actualizar registro
    elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $catalog = $data['catalog'] ?? null;
        $id = $data['id'] ?? null;

        if (!$id) throw new Exception("ID Requerido");

        if ($catalog === 'categorias') {
            $stmt = $pdo->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $id]);
        } elseif ($catalog === 'marcas') {
            $stmt = $pdo->prepare("UPDATE marcas SET nombre = ?, descripcion = ?, sitio_web = ? WHERE id = ?");
            $stmt->execute([$data['nombre'], $data['descripcion'] ?? '', $data['sitio_web'] ?? '', $id]);
        } elseif ($catalog === 'colores') {
            $stmt = $pdo->prepare("UPDATE colores SET nombre = ?, codigo_hex = ? WHERE id = ?");
            $stmt->execute([$data['nombre'], $data['codigo_hex'] ?? '#000000', $id]);
        } elseif ($catalog === 'materiales') {
            $stmt = $pdo->prepare("UPDATE materiales SET nombre = ? WHERE id = ?");
            $stmt->execute([$data['nombre'], $id]);
        } else {
            throw new Exception("Operación no permitida");
        }

        echo json_encode(['success' => true, 'message' => 'Registro actualizado exitosamente']);
    }

    // DELETE: Eliminar registro
    elseif ($method === 'DELETE') {
        $catalog = $_GET['catalog'] ?? null;
        $id = $_GET['id'] ?? null;

        if (!$catalog || !$id) throw new Exception("Catálogo e ID requeridos");

        $stmt = $pdo->prepare("DELETE FROM $catalog WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Registro eliminado exitosamente']);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
