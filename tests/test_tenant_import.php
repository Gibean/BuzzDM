<?php
/**
 * test_tenant_import.php
 *
 * Aislamiento del importador de Excel (php/api_import.php).
 *
 * El importador es la segunda vía de fuga del DEFAULT 1, y más grave que la de
 * pedidos porque no se limita a una tabla: el INSERT se montaba por concatenación
 * con las columnas del Excel y sin empresa_id, así que TODO lo que importaba el
 * tenant 14 (productos, clientes, proveedores, categorías, marcas y el movimiento
 * de stock inicial) acababa en la empresa 1.
 *
 * El UPDATE tenía el defecto espejo: el "SELECT id WHERE sku = ?" no llevaba
 * empresa_id, y como la clave única es (empresa_id, sku), el tenant 14 encontraba
 * el producto del tenant 1 y lo sobrescribía en vez de crear el suyo. Una
 * importación routine desde la demo modificaba el catálogo del cliente real.
 *
 * Esta prueba importa un Excel desde la empresa 14 contra un catálogo que ya
 * existe en la empresa 1 con los mismos nombres, y comprueba que conviven.
 *
 * Uso: php tests/test_tenant_import.php
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';
require_once __DIR__ . '/../php/src/Repository/ImportRepository.php';
require_once __DIR__ . '/../php/src/Service/ImportService.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Las dos empresas las crea la propia prueba y se borran al terminar.
// Antes eran las fijas 1 (Txiki) y 14 (la demo del navegador).
require_once __DIR__ . '/_tenant_fixture.php';

$passed = 0;
$failed = 0;

$pdo = \Database::getConnection();
$SUFIJO = 'IMP' . getmypid() . substr((string)time(), -4);

function check(string $descripcion, bool $condicion, string $detalle = ''): void
{
    global $passed, $failed;

    if ($condicion) {
        $passed++;
        echo "  [OK]   $descripcion\n";
    } else {
        $failed++;
        echo "  [FALLA] $descripcion" . ($detalle !== '' ? " -> $detalle" : '') . "\n";
    }
}

function empresaDeId(string $tabla, string $columna, $valor): ?int
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT empresa_id FROM `$tabla` WHERE `$columna` = ?");
    $stmt->execute([$valor]);
    $v = $stmt->fetchColumn();
    return $v === false ? null : (int)$v;
}

echo "=== AISLAMIENTO DEL IMPORTADOR (productos, clientes, catalogos) ===\n\n";

$catNombre = 'Categoria-' . $SUFIJO;
$skuBase = 'SKU-' . $SUFIJO;
$fichero = sys_get_temp_dir() . '/import_test_' . $SUFIJO . '.xlsx';

$catA = $prodA = 0;
$idsCatalogos = [];
$idsUsuarios = [];
$idsProductos = [];

try {
    // ---------------------------------------------------------------------------
    echo "[1] Catalonia previa en la empresa 1 con el mismo nombre\n";
    // El indice uk_proveedores_empresa_nombre y el patron de idx_clientes_tenant_nombre
    // confirman que el nombre es unico POR EMPRESA. Por eso el tenant 14 debe crear
    // su propia categoria, no apuntar a la de la empresa 1.
    // ---------------------------------------------------------------------------
    $pdo->prepare("INSERT INTO categorias (empresa_id, nombre) VALUES (?, ?)")->execute([TENANT_A, $catNombre]);
    $catA = (int)$pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO productos (empresa_id, sku, nombre, precio, stock, estado, tipo_producto, maneja_stock, unidad_venta)
         VALUES (?, ?, 'Producto empresa 1', 10, 5, 'activo', 'fisico', 1, 'UND')"
    )->execute([TENANT_A, $skuBase]);
    $prodA = (int)$pdo->lastInsertId();

    check('la categoria previa existe en la empresa ' . TENANT_A, $catA > 0);
    check('el producto previo existe en la empresa ' . TENANT_A, $prodA > 0);

    // ---------------------------------------------------------------------------
    echo "\n[2] Importacion desde la empresa " . TENANT_B . "\n";
    // ---------------------------------------------------------------------------
    $spreadsheet = new Spreadsheet();

    $s = $spreadsheet->createSheet();
    $s->setTitle('Productos');
    $s->fromArray([
        ['SKU', 'NombreProducto', 'PrecioVenta', 'StockInicial', 'Categoria', 'Marca', 'UnidadVenta'],
        [$skuBase, 'Producto de la importacion', 99.5, 12, $catNombre, 'Marca-' . $SUFIJO, 'UND'],
        [$skuBase . '-2', 'Segundo producto', 15, 0, $catNombre, 'Marca-' . $SUFIJO, 'UND'],
    ], null, 'A1');

    $s = $spreadsheet->createSheet();
    $s->setTitle('Clientes');
    $s->fromArray([
        ['DocumentoId', 'NombreRazon', 'Email', 'Telefono'],
        ['DOC-' . $SUFIJO, 'Cliente importado', 'cli-' . $SUFIJO . '@ejemplo.test', '04120000000'],
    ], null, 'A1');

    $s = $spreadsheet->createSheet();
    $s->setTitle('Marcas');
    $s->fromArray([['NombreMarca', 'SitioWeb'], ['Marca-' . $SUFIJO, 'https://ejemplo.test']], null, 'A1');

    $s = $spreadsheet->createSheet();
    $s->setTitle('Proveedores');
    $s->fromArray([['NombreEmpresa', 'RIF'], ['Proveedor-' . $SUFIJO, 'J-123']], null, 'A1');

    $spreadsheet->removeSheetByIndex(0);
    (new Xlsx($spreadsheet))->save($fichero);

    TenantContext::set(TENANT_B);

    // El usuario que importa debe pertenecer a la empresa que importa.
    // Antes se pasaba el id 1 (el administrador de Txiki), lo que ataba la
    // prueba a esa empresa. No era solo un dato incorrecto: la columna
    // importaciones.usuario_id tiene foreign key, así que un id de otra empresa
    // hace que el INSERT no cuele.
    $stmtUsuarioB = $pdo->prepare(
        "INSERT INTO usuarios (empresa_id, nombre, email, password, rol, estado, creado_at)
         VALUES (?, 'Usuario Importador', ?, 'x', 'admin', 'activo', NOW())"
    );
    $emailImportador = 'importador' . $SUFIJO . '@ejemplo.com';
    $stmtUsuarioB->execute([TENANT_B, $emailImportador]);
    $usuarioImportadorId = (int)$pdo->lastInsertId();
    $idsUsuarios[] = $usuarioImportadorId;

    $servicio = new ImportService();
    $res = $servicio->processFile($fichero, 'test_' . $SUFIJO . '.xlsx', $usuarioImportadorId);

    check('la importacion termina con success', ($res['success'] ?? false) === true, json_encode($res['results'] ?? $res));

    $prodB = (int)$pdo->query("SELECT id FROM productos WHERE sku = '$skuBase' AND empresa_id = " . TENANT_B)->fetchColumn();
    $idsProductos[] = $prodA;
    $idsProductos[] = $prodB;
    $idsProductos[] = (int)$pdo->query("SELECT id FROM productos WHERE sku = '{$skuBase}-2' AND empresa_id = " . TENANT_B)->fetchColumn();
    $idsCatalogos[] = $catA;
    $idsCatalogos[] = (int)$pdo->query("SELECT id FROM categorias WHERE nombre = '$catNombre' AND empresa_id = " . TENANT_B)->fetchColumn();

    // ---------------------------------------------------------------------------
    echo "\n[3] Aislamiento: el tenant " . TENANT_B . " no toco los datos del " . TENANT_A . "\n";
    // ---------------------------------------------------------------------------
    check('el SKU se creo para la empresa ' . TENANT_B, $prodB > 0, 'no existe con empresa_id=' . TENANT_B);
    check('el producto de la empresa ' . TENANT_A . ' sigue siendo suyo', empresaDeId('productos', 'id', $prodA) === TENANT_A);

    $nombreA = (string)$pdo->query("SELECT nombre FROM productos WHERE id = $prodA")->fetchColumn();
    check('el nombre del producto ajeno quedo intacto', $nombreA === 'Producto empresa 1', "nombre='$nombreA'");

    $duplicados = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE sku = '$skuBase'")->fetchColumn();
    check('hay una fila por empresa, no una compartida', $duplicados === 2, "filas=$duplicados");

    // El producto del tenant 14 debe apuntar a SU categoria, no a la de la empresa 1.
    $catB = (int)$pdo->query("SELECT categorias.id FROM productos p
                              JOIN categorias ON p.categoria = categorias.id
                              WHERE p.id = $prodB")->fetchColumn();
    check('el producto ajeno apunta a la categoria de su propia empresa', $catB === $idsCatalogos[1] ?? -1, "catRef=$catB");

    $catEmp = empresaDeId('categorias', 'id', $catB);
    check('la categoria referenciada pertenece al tenant ' . TENANT_B, $catEmp === TENANT_B, "empresa_id=$catEmp");

    // ---------------------------------------------------------------------------
    echo "\n[4] movimientos_inventario con empresa_id explicito\n";
    // El INSERT del movimiento de stock inicial es propio del servicio y tb omitia
    // la columna: el stock de un producto recien importado por el tenant 14 se
    // registraba como movimiento de la empresa 1.
    // ---------------------------------------------------------------------------
    $mov = $pdo->query("SELECT empresa_id, cantidad FROM movimientos_inventario WHERE producto_id = $prodB")->fetch(PDO::FETCH_ASSOC);
    check('el movimiento de stock inicial tiene empresa_id=' . TENANT_B, $mov !== false && (int)$mov['empresa_id'] === TENANT_B, json_encode($mov));

    // ---------------------------------------------------------------------------
    echo "\n[5] Historial de importaciones acotado al tenant\n";
    // ---------------------------------------------------------------------------
    $repo = new ImportRepository();
    $historial = TenantContext::withTenant(TENANT_B, static fn() => $repo->getHistory());
    check('getHistory() del tenant ' . TENANT_B . ' devuelve su importacion', count($historial) >= 1, 'entradas=' . count($historial));

    $ajenas = array_filter(
        $historial,
        static fn($h) => empresaDeId('importaciones', 'id', $h['id']) !== TENANT_B
    );
    check('getHistory() no expone importaciones de otras empresas', count($ajenas) === 0, 'ajenas=' . count($ajenas));

    $historialA = TenantContext::withTenant(TENANT_A, static fn() => $repo->getHistory());
    $ajenasParaA = array_filter(
        $historialA,
        static fn($h) => empresaDeId('importaciones', 'id', $h['id']) !== TENANT_A
    );
    check('getHistory() del tenant ' . TENANT_A . ' tampoco ve las del otro', count($ajenasParaA) === 0, 'ajenas=' . count($ajenasParaA));

    $cliEmp = empresaDeId('clientes', 'id', 'DOC-' . $SUFIJO);
    check('el cliente importado pertenece al tenant ' . TENANT_B, $cliEmp === TENANT_B, 'empresa_id=' . var_export($cliEmp, true));

    // ---------------------------------------------------------------------------
    echo "\n[6] Reimportar actualiza, no duplica\n";
    // Con el SELECT sin empresa_id, la segunda pasada habria encontrado el
    // producto de la empresa 1 y lo habria modificado otra vez.
    // ---------------------------------------------------------------------------
    $res2 = (new ImportService())->processFile($fichero, 'test2_' . $SUFIJO . '.xlsx', $usuarioImportadorId);
    $total = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE sku LIKE '{$skuBase}%'")->fetchColumn();
    check('no aparecen filas nuevas al reimportar', $total === 3, "filas=$total (1 de la empresa 1 + 2 de la " . TENANT_B . ')');
    check('el reporte cuenta las filas como actualizadas', ($res2['results']['Productos']['updated'] ?? 0) === 2);

    echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";

} catch (Throwable $e) {
    $failed++;
    echo "\n[ERROR NO CONTROLADO] " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "  en " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";
} finally {
    $pdoIds = implode(',', array_filter($idsProductos));
    if ($pdoIds !== '') {
        $pdo->exec("DELETE FROM movimientos_inventario WHERE producto_id IN ($pdoIds)");
        $pdo->exec("DELETE FROM detalle_pedido WHERE producto_id IN ($pdoIds)");
        $pdo->exec("DELETE FROM productos WHERE id IN ($pdoIds)");
    }

    // Segunda pasada por sufijo, para no dejar filas si una asercion fallo antes
    // de registrar su id.
    $pdo->exec("DELETE FROM clientes WHERE id LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM proveedores WHERE nombre LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM marcas WHERE nombre LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM categorias WHERE nombre LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM productos WHERE sku LIKE '%$SUFIJO%'");
    $pdo->exec("DELETE FROM importaciones WHERE archivo_nombre LIKE '%$SUFIJO%'");
    $pdo->exec("DELETE FROM usuarios WHERE email LIKE '%$SUFIJO%'");

    @unlink($fichero);
    TenantContext::clear();

    $restos = (int)$pdo->query(
        "SELECT (SELECT COUNT(*) FROM productos WHERE sku LIKE '%$SUFIJO%')
              + (SELECT COUNT(*) FROM categorias WHERE nombre LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM marcas WHERE nombre LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM proveedores WHERE nombre LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM clientes WHERE id LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM importaciones WHERE archivo_nombre LIKE '%$SUFIJO')"
    )->fetchColumn();

    echo "\nLimpieza: " . ($restos === 0
        ? "sin filas de prueba (sufijo $SUFIJO)"
        : "QUEDAN $restos fila(s) de prueba con sufijo $SUFIJO") . "\n";
}

exit($failed > 0 ? 1 : 0);
