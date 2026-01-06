<?php
/**
 * API REST para Cargos de Existencias
 *
 * Endpoints:
 * GET    /cargos_actions.php              - Listar cargos con filtros
 * GET    /cargos_actions.php?id=X         - Obtener cargo específico
 * POST   /cargos_actions.php              - Crear nuevo cargo
 * PUT    /cargos_actions.php?id=X         - Actualizar cargo (solo borrador)
 * DELETE /cargos_actions.php?id=X         - Eliminar cargo (solo borrador)
 * POST   /cargos_actions.php?action=confirm&id=X - Confirmar cargo
 * POST   /cargos_actions.php?action=cancel&id=X  - Anular cargo
 */

require_once 'sesion.php';
require_once 'db.php';

header('Content-Type: application/json');

// Verificar rol admin/supervisor
if (!in_array($_SESSION['usuario_rol'], ['admin', 'supervisor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$pdo = getDbConnection();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                obtenerCargo($pdo, intval($_GET['id']));
            } else {
                listarCargos($pdo);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $_GET['action'] ?? 'create';

            if ($action === 'confirm' && isset($_GET['id'])) {
                confirmarCargo($pdo, intval($_GET['id']));
            } elseif ($action === 'cancel' && isset($_GET['id'])) {
                anularCargo($pdo, intval($_GET['id']), $input['motivo'] ?? '');
            } else {
                crearCargo($pdo, $input);
            }
            break;

        case 'PUT':
            if (!isset($_GET['id'])) {
                throw new Exception('ID de cargo requerido');
            }
            $input = json_decode(file_get_contents('php://input'), true);
            actualizarCargo($pdo, intval($_GET['id']), $input);
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('ID de cargo requerido');
            }
            eliminarCargo($pdo, intval($_GET['id']));
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// =====================================================
// LISTAR CARGOS
// =====================================================

function listarCargos($pdo) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = max(1, min(100, intval($_GET['limit'] ?? 10)));
    $offset = ($page - 1) * $limit;

    $where = [];
    $params = [];

    // Filtro de búsqueda
    if (!empty($_GET['search'])) {
        $search = '%' . $_GET['search'] . '%';
        $where[] = "(c.codigo LIKE :search OR p.nombre LIKE :search OR c.referencia_externa LIKE :search)";
        $params[':search'] = $search;
    }

    // Filtro de estado
    if (!empty($_GET['estado'])) {
        $where[] = "c.estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }

    // Filtro de concepto
    if (!empty($_GET['concepto'])) {
        $where[] = "c.concepto = :concepto";
        $params[':concepto'] = $_GET['concepto'];
    }

    // Filtro de fechas
    if (!empty($_GET['fecha_desde'])) {
        $where[] = "c.fecha >= :fecha_desde";
        $params[':fecha_desde'] = $_GET['fecha_desde'];
    }

    if (!empty($_GET['fecha_hasta'])) {
        $where[] = "c.fecha <= :fecha_hasta";
        $params[':fecha_hasta'] = $_GET['fecha_hasta'];
    }

    $whereClause = count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '';

    // Contar total
    $sqlCount = "SELECT COUNT(DISTINCT c.id) as total
                 FROM cargos_existencias c
                 LEFT JOIN proveedores p ON c.proveedor_id = p.id
                 $whereClause";
    $stmtCount = $pdo->prepare($sqlCount);
    foreach ($params as $key => $value) {
        $stmtCount->bindValue($key, $value);
    }
    $stmtCount->execute();
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    // Obtener cargos
    $sql = "SELECT
                c.id,
                c.codigo,
                c.fecha,
                c.concepto,
                CASE c.concepto
                    WHEN 'compra' THEN 'Compra'
                    WHEN 'donacion' THEN 'Donación'
                    WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                    WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                    WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                    WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
                END AS concepto_texto,
                p.nombre AS proveedor_nombre,
                c.referencia_externa,
                c.total_productos,
                c.total_unidades,
                c.valor_total,
                c.estado,
                CASE c.estado
                    WHEN 'borrador' THEN 'Borrador'
                    WHEN 'confirmado' THEN 'Confirmado'
                    WHEN 'anulado' THEN 'Anulado'
                END AS estado_texto,
                u.nombre AS registrado_por_nombre,
                c.creado_at
            FROM cargos_existencias c
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            LEFT JOIN usuarios u ON c.registrado_por = u.id
            $whereClause
            ORDER BY c.creado_at DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cargos' => $cargos,
        'total' => intval($total),
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

// =====================================================
// OBTENER CARGO ESPECÍFICO
// =====================================================

function obtenerCargo($pdo, $id) {
    // Obtener encabezado
    $sql = "SELECT
                c.*,
                CASE c.concepto
                    WHEN 'compra' THEN 'Compra'
                    WHEN 'donacion' THEN 'Donación'
                    WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                    WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                    WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                    WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
                END AS concepto_texto,
                CASE c.estado
                    WHEN 'borrador' THEN 'Borrador'
                    WHEN 'confirmado' THEN 'Confirmado'
                    WHEN 'anulado' THEN 'Anulado'
                END AS estado_texto,
                c.almacen_id,
                a.nombre AS almacen_nombre,
                p.nombre AS proveedor_nombre,
                u.nombre AS registrado_por_nombre,
                uc.nombre AS confirmado_por_nombre,
                ua.nombre AS anulado_por_nombre
            FROM cargos_existencias c
            LEFT JOIN proveedores p ON c.proveedor_id = p.id
            LEFT JOIN almacenes a ON c.almacen_id = a.id
            LEFT JOIN usuarios u ON c.registrado_por = u.id
            LEFT JOIN usuarios uc ON c.confirmado_por = uc.id
            LEFT JOIN usuarios ua ON c.anulado_por = ua.id
            WHERE c.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cargo) {
        throw new Exception('Cargo no encontrado');
    }

    // Obtener detalles
    $sqlDetalles = "SELECT
                        d.id,
                        d.producto_id,
                        pr.sku AS producto_sku,
                        pr.nombre AS producto_nombre,
                        d.cantidad,
                        d.costo_unitario,
                        d.subtotal,
                        d.notas
                    FROM detalle_cargo_existencias d
                    INNER JOIN productos pr ON d.producto_id = pr.id
                    WHERE d.cargo_id = :cargo_id
                    ORDER BY d.id";

    $stmtDetalles = $pdo->prepare($sqlDetalles);
    $stmtDetalles->execute([':cargo_id' => $id]);
    $cargo['detalles'] = $stmtDetalles->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'cargo' => $cargo
    ]);
}

// =====================================================
// CREAR CARGO
// =====================================================

function crearCargo($pdo, $data) {
    validarDatosCargo($data);

    $pdo->beginTransaction();

    try {
        // Generar código automático (sin depender de función SQL)
        $year = date('Y');
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 10) AS UNSIGNED)), 0) + 1 as next_num
                               FROM cargos_existencias
                               WHERE codigo LIKE ?");
        $stmt->execute(["CGO-$year-%"]);
        $nextNum = $stmt->fetchColumn();
        $codigo = sprintf("CGO-%s-%04d", $year, $nextNum);

        // Determinar estado inicial
        $estado = ($data['confirmar'] ?? false) ? 'confirmado' : 'borrador';

        // Insertar encabezado
        $sql = "INSERT INTO cargos_existencias
                (codigo, fecha, concepto, concepto_otro, proveedor_id, almacen_id,
                 referencia_externa, notas, registrado_por, estado)
                VALUES
                (:codigo, :fecha, :concepto, :concepto_otro, :proveedor_id, :almacen_id,
                 :referencia, :notas, :usuario_id, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':fecha' => $data['fecha'],
            ':concepto' => $data['concepto'],
            ':concepto_otro' => $data['concepto_otro'] ?? null,
            ':proveedor_id' => !empty($data['proveedor_id']) ? $data['proveedor_id'] : null,
            ':almacen_id' => !empty($data['almacen_id']) ? $data['almacen_id'] : null,
            ':referencia' => $data['referencia_externa'] ?? null,
            ':notas' => $data['notas'] ?? null,
            ':usuario_id' => $_SESSION['usuario_id'],
            ':estado' => $estado
        ]);

        $cargoId = $pdo->lastInsertId();

        // Insertar líneas de detalle
        $totales = insertarDetalles($pdo, $cargoId, $data['lineas'] ?? []);

        // Actualizar totales en el encabezado
        $pdo->prepare("UPDATE cargos_existencias
                       SET total_productos = ?, total_unidades = ?, valor_total = ?
                       WHERE id = ?")
            ->execute([$totales['productos'], $totales['unidades'], $totales['valor'], $cargoId]);

        // Si se confirma directamente, actualizar confirmado_por
        if ($estado === 'confirmado') {
            $pdo->prepare("UPDATE cargos_existencias
                           SET confirmado_por = ?, fecha_confirmacion = NOW()
                           WHERE id = ?")
                ->execute([$_SESSION['usuario_id'], $cargoId]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => $estado === 'confirmado'
                ? 'Cargo confirmado y stock actualizado'
                : 'Cargo guardado como borrador',
            'cargo_id' => $cargoId,
            'codigo' => $codigo
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// =====================================================
// ACTUALIZAR CARGO
// =====================================================

function actualizarCargo($pdo, $id, $data) {
    // Verificar que el cargo existe y está en borrador
    $cargo = obtenerCargoParaEdicion($pdo, $id);

    validarDatosCargo($data);

    $pdo->beginTransaction();

    try {
        // Determinar estado
        $estado = ($data['confirmar'] ?? false) ? 'confirmado' : 'borrador';

        // Actualizar encabezado
        $sql = "UPDATE cargos_existencias SET
                    fecha = :fecha,
                    concepto = :concepto,
                    concepto_otro = :concepto_otro,
                    proveedor_id = :proveedor_id,
                    almacen_id = :almacen_id,
                    referencia_externa = :referencia,
                    notas = :notas,
                    estado = :estado
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $data['fecha'],
            ':concepto' => $data['concepto'],
            ':concepto_otro' => $data['concepto_otro'] ?? null,
            ':proveedor_id' => !empty($data['proveedor_id']) ? $data['proveedor_id'] : null,
            ':almacen_id' => !empty($data['almacen_id']) ? $data['almacen_id'] : null,
            ':referencia' => $data['referencia_externa'] ?? null,
            ':notas' => $data['notas'] ?? null,
            ':estado' => $estado,
            ':id' => $id
        ]);

        // Eliminar detalles anteriores
        $pdo->prepare("DELETE FROM detalle_cargo_existencias WHERE cargo_id = ?")
            ->execute([$id]);

        // Insertar nuevos detalles
        $totales = insertarDetalles($pdo, $id, $data['lineas'] ?? []);

        // Actualizar totales
        $pdo->prepare("UPDATE cargos_existencias
                       SET total_productos = ?, total_unidades = ?, valor_total = ?
                       WHERE id = ?")
            ->execute([$totales['productos'], $totales['unidades'], $totales['valor'], $id]);

        // Si se confirma, actualizar confirmado_por
        if ($estado === 'confirmado') {
            $pdo->prepare("UPDATE cargos_existencias
                           SET confirmado_por = ?, fecha_confirmacion = NOW()
                           WHERE id = ?")
                ->execute([$_SESSION['usuario_id'], $id]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => $estado === 'confirmado'
                ? 'Cargo confirmado y stock actualizado'
                : 'Cargo actualizado correctamente'
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// =====================================================
// CONFIRMAR CARGO
// =====================================================

function confirmarCargo($pdo, $id) {
    $cargo = obtenerCargoParaEdicion($pdo, $id);

    // Verificar que tiene productos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM detalle_cargo_existencias WHERE cargo_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() == 0) {
        throw new Exception('El cargo no tiene productos');
    }

    // Actualizar estado (el trigger actualizará el stock)
    $sql = "UPDATE cargos_existencias
            SET estado = 'confirmado',
                confirmado_por = ?,
                fecha_confirmacion = NOW()
            WHERE id = ?";

    $pdo->prepare($sql)->execute([$_SESSION['usuario_id'], $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Cargo confirmado. Stock actualizado correctamente.'
    ]);
}

// =====================================================
// ANULAR CARGO
// =====================================================

function anularCargo($pdo, $id, $motivo) {
    if (empty($motivo)) {
        throw new Exception('El motivo de anulación es requerido');
    }

    // Verificar que el cargo existe y está confirmado
    $stmt = $pdo->prepare("SELECT estado FROM cargos_existencias WHERE id = ?");
    $stmt->execute([$id]);
    $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cargo) {
        throw new Exception('Cargo no encontrado');
    }

    if ($cargo['estado'] !== 'confirmado') {
        throw new Exception('Solo se pueden anular cargos confirmados');
    }

    // Anular (el trigger revertirá el stock)
    $sql = "UPDATE cargos_existencias
            SET estado = 'anulado',
                anulado_por = ?,
                fecha_anulacion = NOW(),
                motivo_anulacion = ?
            WHERE id = ?";

    $pdo->prepare($sql)->execute([$_SESSION['usuario_id'], $motivo, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Cargo anulado. Stock revertido correctamente.'
    ]);
}

// =====================================================
// ELIMINAR CARGO
// =====================================================

function eliminarCargo($pdo, $id) {
    $cargo = obtenerCargoParaEdicion($pdo, $id);

    $pdo->prepare("DELETE FROM cargos_existencias WHERE id = ?")->execute([$id]);

    echo json_encode([
        'success' => true,
        'message' => 'Cargo eliminado correctamente'
    ]);
}

// =====================================================
// FUNCIONES AUXILIARES
// =====================================================

function validarDatosCargo($data) {
    if (empty($data['fecha'])) {
        throw new Exception('La fecha es requerida');
    }

    if (empty($data['concepto'])) {
        throw new Exception('El concepto es requerido');
    }

    if ($data['concepto'] === 'otro' && empty($data['concepto_otro'])) {
        throw new Exception('Debe especificar el concepto');
    }
}

function obtenerCargoParaEdicion($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id, estado FROM cargos_existencias WHERE id = ?");
    $stmt->execute([$id]);
    $cargo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cargo) {
        throw new Exception('Cargo no encontrado');
    }

    if ($cargo['estado'] !== 'borrador') {
        throw new Exception('Solo se pueden editar cargos en estado borrador');
    }

    return $cargo;
}

function insertarDetalles($pdo, $cargoId, $lineas) {
    $sqlDetalle = "INSERT INTO detalle_cargo_existencias
                   (cargo_id, producto_id, cantidad, costo_unitario, notas)
                   VALUES (:cargo_id, :producto_id, :cantidad, :costo, :notas)";
    $stmtDetalle = $pdo->prepare($sqlDetalle);

    $totalProductos = 0;
    $totalUnidades = 0;
    $valorTotal = 0;

    foreach ($lineas as $linea) {
        if (empty($linea['producto_id']) || empty($linea['cantidad'])) continue;

        $cantidad = intval($linea['cantidad']);
        $costo = floatval($linea['costo_unitario'] ?? 0);

        if ($cantidad <= 0) continue;

        $stmtDetalle->execute([
            ':cargo_id' => $cargoId,
            ':producto_id' => $linea['producto_id'],
            ':cantidad' => $cantidad,
            ':costo' => $costo,
            ':notas' => $linea['notas'] ?? null
        ]);

        $totalProductos++;
        $totalUnidades += $cantidad;
        $valorTotal += $cantidad * $costo;
    }

    return [
        'productos' => $totalProductos,
        'unidades' => $totalUnidades,
        'valor' => $valorTotal
    ];
}
