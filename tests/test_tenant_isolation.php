<?php
/**
 * test_tenant_isolation.php
 *
 * Pruebas de regresión del aislamiento multi-tenant.
 *
 * Estas pruebas cubren los invariantes que, si no se verifican, se rompen en
 * silencio: una consulta sin empresa_id no devuelve datos, pero sí devuelve
 * datos, y nadie se entera hasta que un cliente ve el catálogo de otro.
 *
 * El cuerpo del test va dentro de try/finally porque una aserción fallida no
 * debe dejar filas de prueba en la base de datos. Ya ha pasado: un test
 * anterior murió en una línea intermedia y dejó un producto huérfano con
 * SKU 'TEST-AISL-1' que bloqueó el siguiente intento de ejecución.
 *
 * Uso: php tests/test_tenant_isolation.php
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';
require_once __DIR__ . '/../php/src/Repository/ProductRepository.php';

// Las dos empresas las crea la propia prueba y se borran al terminar.
//
// Antes eran ids fijos que existian en la base de desarrollo (la empresa 1 y la
// demo que el visitor acababa de registrar). Eso ataba las pruebas a un catalogo
// externo y a una demovariable: en una base recien limpiada fallaban con un error
// de foreign key antes de comprobar nada. Ojo al'ordre de estas dos lineas: el
// fixture usa define(), y un define() NO pisa a un const ya declarado en el
// mismo archivo. Con const TENANT_A = 1 delante, el fixture se creaba pero
// las pruebas seguian operando sobre la empresa 1.
require_once __DIR__ . '/_tenant_fixture.php';

const SKU_A = 'ISO-TEST-A';
const SKU_B = 'ISO-TEST-B';
const SKU_COLISION = 'ISO-TEST-COMPARTIDO';

$passed = 0;
$failed = 0;
$repo = new ProductRepository();

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

function crearProducto(int $empresaId, string $sku, float $precio = 10.0): int
{
    global $repo;

    $id = TenantContext::withTenant($empresaId, static function () use ($repo, $sku, $precio) {
        return $repo->create([
            'sku' => $sku, 'nombre' => 'Producto aislamiento ' . $sku,
            'descripcion' => 'Fila temporal de prueba de aislamiento',
            'precio' => $precio, 'stock' => 5, 'min_stock' => 1, 'estado' => 'activo',
            'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
            'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
            'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
            'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
            'es_kit_virtual' => 0, 'variante_atributo' => null,
        ]);
    });

    return (int)$id;
}

echo "=== AISLAMIENTO MULTI-TENANT ===\n\n";

$idsACrear = [];

try {

// ---------------------------------------------------------------------------
echo "[1] El repositorio se niega a operar sin tenant\n";
// Sin esto, un endpoint sin sesión devolvería el catálogo completo.
// ---------------------------------------------------------------------------
TenantContext::clear();
unset($_SESSION['empresa_id']);

$lanzo = false;
try {
    $repo->findById(1);
} catch (TenantRequiredException $e) {
    $lanzo = true;
}
check('findById() lanza TenantRequiredException sin contexto', $lanzo);

$lanzo = false;
try {
    $repo->findAll();
} catch (TenantRequiredException $e) {
    $lanzo = true;
}
check('findAll() lanza TenantRequiredException sin contexto', $lanzo);

// El DEFAULT 1 de la columna es la trampa original: sin tenant explícito,
// un INSERT se iba a la empresa principal. Ahora debe rechazarse.
$lanzo = false;
try {
    $repo->create([
        'sku' => 'ISO-TEST-SIN-TENANT', 'nombre' => 'No debe existir',
        'descripcion' => '', 'precio' => 1, 'stock' => 0, 'min_stock' => 0,
        'estado' => 'activo', 'marca_id' => null, 'categoria' => null,
        'proveedor_id' => null, 'modelo' => null, 'color' => null, 'tamanio' => null,
        'medidas' => null, 'peso_kg' => null, 'material' => null,
        'unidad_venta' => 'UND', 'tipo_producto' => 'fisico', 'maneja_stock' => 1,
        'producto_padre_id' => null, 'es_kit_virtual' => 0, 'variante_atributo' => null,
    ]);
} catch (TenantRequiredException $e) {
    $lanzo = true;
}
check('create() lanza TenantRequiredException sin contexto', $lanzo);

// ---------------------------------------------------------------------------
echo "\n[2] Listado: cada empresa ve únicamente su catálogo\n";
// ---------------------------------------------------------------------------
$listadoA = TenantContext::withTenant(TENANT_A, static fn() => $repo->findAll(['limit' => 1000]));
$listadoB = TenantContext::withTenant(TENANT_B, static fn() => $repo->findAll(['limit' => 1000]));

$idsA = array_column($listadoA['products'], 'id');
$idsB = array_column($listadoB['products'], 'id');

check('Empresa ' . TENANT_A . ' tiene productos', count($idsA) > 0, 'total=' . $listadoA['total']);
check('Empresa ' . TENANT_B . ' no tiene productos', count($idsB) === 0, 'total=' . $listadoB['total']);
check('Los listados no comparten ninguna fila', count(array_intersect($idsA, $idsB)) === 0);

$todosDelTenant = TenantContext::withTenant(
    TENANT_A,
    static fn() => $repo->findAll(['limit' => 1000])['products']
);
$filtrados = array_filter($todosDelTenant, static fn($p) => (int)$p['empresa_id'] !== TENANT_A);
check('Ninguna fila del listado pertenece a otra empresa', count($filtrados) === 0);

// ---------------------------------------------------------------------------
echo "\n[3] Acceso directo por id entre empresas\n";
// ---------------------------------------------------------------------------
$idA = crearProducto(TENANT_A, SKU_A);
$idB = crearProducto(TENANT_B, SKU_B);
$idsACrear = [$idA, $idB];
check('El producto de la empresa ' . TENANT_A . ' se crea', $idA > 0);
check('El producto de la empresa ' . TENANT_B . ' se crea', $idB > 0);

$desdeB = TenantContext::withTenant(TENANT_B, static fn() => $repo->findById($idA));
check('La empresa ' . TENANT_B . ' no puede leer el producto ' . $idA . ' de la empresa ' . TENANT_A, $desdeB === null);

$desdeA = TenantContext::withTenant(TENANT_A, static fn() => $repo->findById($idB));
check('La empresa ' . TENANT_A . ' no puede leer el producto ' . $idB . ' de la empresa ' . TENANT_B, $desdeA === null);

$propioB = TenantContext::withTenant(TENANT_B, static fn() => $repo->findById($idB));
check('La empresa ' . TENANT_B . ' sí lee su propio producto ' . $idB, $propioB !== null);

// ---------------------------------------------------------------------------
echo "\n[4] Escrituras: un id ajeno se comporta como inexistente\n";
// ---------------------------------------------------------------------------
$actualizado = TenantContext::withTenant(TENANT_B, static fn() => $repo->update($idA, [
    'sku' => SKU_A, 'nombre' => 'ESCRITO POR LA EMPRESA EQUIVOCADA',
    'descripcion' => 'intento de escritura cruzada', 'precio' => 999, 'stock' => 1,
    'min_stock' => 1, 'estado' => 'activo', 'marca_id' => null, 'categoria' => null,
    'proveedor_id' => null, 'modelo' => null, 'color' => null, 'tamanio' => null,
    'medidas' => null, 'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
    'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
    'es_kit_virtual' => 0, 'variante_atributo' => null,
]));
check('update() sobre un id ajeno devuelve false', $actualizado === false);

$intacto = TenantContext::withTenant(TENANT_A, static fn() => $repo->findById($idA));
check('El producto ajeno conserva su nombre original', ($intacto['nombre'] ?? '') !== 'ESCRITO POR LA EMPRESA EQUIVOCADA');

$borrado = TenantContext::withTenant(TENANT_B, static fn() => $repo->softDelete($idA));
check('softDelete() sobre un id ajeno devuelve false', $borrado === false);

$sigueVivo = TenantContext::withTenant(TENANT_A, static fn() => $repo->findById($idA));
check('El producto ajeno sigue sin estar borrado', $sigueVivo !== null && $sigueVivo['deleted_at'] === null);

// ---------------------------------------------------------------------------
echo "\n[5] Unicidad por tenant (migración saas_tenant_uniqueness_003)\n";
// Regresión del fallo que impedía registrar un segundo usuario demo:
// CompanyService crea el almacén con el código literal 'ALM-001'.
// ---------------------------------------------------------------------------
$colisionA = crearProducto(TENANT_A, SKU_COLISION);
$idsACrear[] = $colisionA;
$colisionB = 0;
$errorDuplicado = '';
try {
    $colisionB = crearProducto(TENANT_B, SKU_COLISION);
    $idsACrear[] = $colisionB;
} catch (PDOException $e) {
    $errorDuplicado = $e->getMessage();
}
check(
    'El mismo SKU puede existir en dos empresas distintas',
    $colisionA > 0 && $colisionB > 0,
    $errorDuplicado
);

$duplicadoEnMismoTenant = false;
try {
    crearProducto(TENANT_A, SKU_COLISION);
} catch (PDOException $e) {
    $duplicadoEnMismoTenant = str_contains($e->getMessage(), 'uk_productos_empresa_sku');
}
check('El SKU sigue siendo único dentro de la misma empresa', $duplicadoEnMismoTenant);

$skuVisible = TenantContext::withTenant(TENANT_B, static fn() => $repo->findBySku(SKU_COLISION));
check('findBySku() sólo encuentra el SKU dentro del tenant activo', $skuVisible === true);

$skuAjeno = TenantContext::withTenant(TENANT_B, static fn() => $repo->findBySku(SKU_A));
check('findBySku() no encuentra el SKU de otra empresa', $skuAjeno === false);

// ---------------------------------------------------------------------------
echo "\n[6] Contexto restaurado tras withTenant()\n";
// ---------------------------------------------------------------------------
TenantContext::clear();
TenantContext::withTenant(TENANT_A, static function () {
    TenantContext::withTenant(TENANT_B, static function () {
        // El anidamiento debe funcionar: la operación interna gana.
    });
});
check('withTenant() restaura el tenant previo', TenantContext::id() === null);

TenantContext::set(TENANT_A, true);
TenantContext::withTenant(TENANT_B, static function () {
}, false);
check('withTenant() restaura también la bandera is_demo', TenantContext::isDemo() === true);
check('isDemo() refleja el contexto', TenantContext::isDemo() === true);
TenantContext::clear();

TenantContext::set(TENANT_A, false);
check('isDemo() devuelve false para la empresa principal', TenantContext::isDemo() === false);
TenantContext::clear();

// ---------------------------------------------------------------------------
echo "\n[7] Guardar sin cambios no se interpreta como error\n";
// MySQL devuelve rowCount()=0 cuando el UPDATE reescribe los mismos valores.
// Si update() devolviera eso, guardar el formulario sin tocar nada mostraría
// un "no se pudo guardar" falso. Y el inverso: updateTenant() sigue devolviendo
// el conteo real para quien lo necesite.
// ---------------------------------------------------------------------------
$sinCambios = TenantContext::withTenant(TENANT_A, static function () use ($repo, $idA) {
    $actual = $repo->findById($idA);
    return $repo->update($idA, [
        'sku' => $actual['sku'], 'nombre' => $actual['name'], 'descripcion' => $actual['description'],
        'precio' => $actual['price'], 'stock' => $actual['stock'], 'min_stock' => $actual['min_stock'],
        'estado' => $actual['status'], 'marca_id' => $actual['marca_id'], 'categoria' => $actual['categoria'],
        'proveedor_id' => $actual['proveedor_id'], 'modelo' => $actual['modelo'], 'color' => $actual['color'],
        'tamanio' => $actual['tamanio'], 'medidas' => $actual['medidas'], 'peso_kg' => $actual['peso_kg'],
        'material' => $actual['material'], 'unidad_venta' => $actual['unidad_venta'],
        'tipo_producto' => $actual['tipo_producto'], 'maneja_stock' => $actual['maneja_stock'],
        'producto_padre_id' => $actual['producto_padre_id'], 'es_kit_virtual' => $actual['es_kit_virtual'],
        'variante_atributo' => $actual['variante_atributo'],
    ]);
});
check('update() con valores idénticos devuelve true', $sinCambios === true);

$conCambios = TenantContext::withTenant(TENANT_A, static fn() => $repo->update($idA, [
    'sku' => SKU_A, 'nombre' => 'Nombre corregido',
    'descripcion' => 'desc', 'precio' => 42.0, 'stock' => 7, 'min_stock' => 1,
    'estado' => 'activo', 'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
    'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null, 'peso_kg' => null,
    'material' => null, 'unidad_venta' => 'UND', 'tipo_producto' => 'fisico', 'maneja_stock' => 1,
    'producto_padre_id' => null, 'es_kit_virtual' => 0, 'variante_atributo' => null,
]));
check('update() con cambios reales devuelve true', $conCambios === true);

$verificado = TenantContext::withTenant(TENANT_A, static fn() => $repo->findById($idA));
// findById() expone la columna como 'name' (alias), no 'nombre'.
check('El cambio se persistió', ($verificado['name'] ?? '') === 'Nombre corregido');

$softDeletePropio = TenantContext::withTenant(TENANT_A, static fn() => $repo->softDelete($idA));
check('softDelete() sobre fila propia devuelve true', $softDeletePropio === true);
$restaurado = TenantContext::withTenant(TENANT_A, static fn() => $repo->restore($idA));
check('restore() sobre fila propia devuelve true', $restaurado === true);

echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";

} catch (Throwable $e) {
    $failed++;
    echo "\n[ERROR NO CONTROLADO] " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "  en " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";
} finally {
    // -------------------------------------------------------------------------
    // Se ejecuta siempre, incluso si una aserción lanza una excepción, y no
    // depende del contexto de tenant ni del código bajo prueba: borra por id
    // con SQL directo. Un test que deja basura en la BD es peor que un test que
    // no se ejecuta, porque envenena la siguiente ejecución.
    // -------------------------------------------------------------------------
    $pdo = \Database::getConnection();
    $ids = array_values(array_filter($idsACrear));
    if ($ids !== []) {
        $marcadores = implode(',', array_map('intval', $ids));
        $pdo->exec("DELETE FROM productos WHERE id IN ($marcadores)");
        echo "\nLimpieza: eliminados " . count($ids) . " producto(s) de prueba.\n";
    }

    $restos = $pdo->query("SELECT COUNT(*) FROM productos WHERE sku LIKE 'ISO-TEST-%'")->fetchColumn();
    echo $restos == 0
        ? "Limpieza: no quedan filas ISO-TEST-%.\n"
        : "[AVISO] Limpieza: quedan $restos fila(s) ISO-TEST-%.\n";
}

exit($failed > 0 ? 1 : 0);
