<?php
/**
 * tests/_tenant_fixture.php
 *
 * Provee las dos empresas (TENANT_A y TENANT_B) que usan las pruebas de
 * aislamiento, creandolas en tiempo de ejecucion con define().
 *
 * Por que existe:
 * Estas pruebas necesitan dos empresas para comprobar el cruce. Antes apuntaban
 * a la empresa 1, que era el catalogo de Libreria Txiki, y a la empresa 14, que
 * era la demo que se acababa de registrar en el navegador. Ese acoplamiento
 * fragil ya ha roto varias veces:
 *
 *   - Al vaciar la base, las pruebas lanzaban "Integrity constraint violation:
 *     fk_productos_empresa" porque la empresa 1 ya no existia. La prueba de
 *     aislamiento no se puede ejecutar en una base recien limpiada, que es
 *     justo cuando mas hace falta.
 *   - En una instalacion nueva, sin Txiki, no se ejecutaban en absoluto.
 *   - Con "const TENANT_A = 1" delante, define() no pisa al const y las
 *     pruebas seguian operando sobre Txiki: pasaban por el motivo equivocado y
 *     solo se rompio al eliminar Txiki.
 *
 * Que los dos tenants los cree la propia prueba quita esa dependencia: los
 * tests pasan a ser autocontenidos y sobreviven a que se elimine Txiki.
 *
 * Por que define() y no una variable:
 * TENANT_A y TENANT_B se usan dentro de closures y funciones anidadas
 * (static fn() => ... !== TENANT_B). Una variable necesitaria un use(...) en
 * cada closure. Una constante de runtime se lee igual desde cualquier ambito,
 * asi que la sustitucion es de una linea por archivo y los usos quedan intactos.
 *
 * Por que A se siembra con productos:
 * Una asercion comprueba que el listado de A devuelve filas y el de B no. Si A
 * naciera vacia, esa comprobacion dependeria de que la base ya tuviera un
 * catalogo cargado, es decir, volveria a atar el test a datos externos. Se
 * siembran dos productos propios para que la asercion sea sobre el
 * comportamiento del repositorio y no sobre el estado previo de la base.
 *
 * Por que se limpia con register_shutdown_function:
 * Un fallo real a mitad de una prueba puede morir antes del finally del test y
 * dejar filas huerfanas que bloquean la ejecucion siguiente; ya habia pasado.
 * El shutdown corre tambien ante error fatal, y borrar la empresa arrastra por
 * ON DELETE CASCADE todos los datos de prueba de las tablas hijas.
 *
 * Solo borra las empresas que este archivo ha creado.
 *
 * Uso: se incluye desde los tests; no se ejecuta directamente.
 */

require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';
require_once __DIR__ . '/../php/src/Repository/ProductRepository.php';

if (defined('TENANT_A') || defined('TENANT_B')) {
    return;
}

$fixturePdo = \Database::getConnection();
$fixtureRepo = new ProductRepository();

// El sufijo evita chocar con una ejecucion anterior si un proceso muere sin
// llegar al shutdown (por ejemplo, un kill -9 o un fallo del motor).
$fixtureSufijo = 'T' . str_pad((string)getmypid(), 6, '0', STR_PAD_LEFT) . substr((string)time(), -4);

$crearEmpresaDePruebas = static function (string $nombre) use ($fixturePdo, $fixtureSufijo): int {
    $stmt = $fixturePdo->prepare(
        "INSERT INTO empresa (tipo_rif, rif, nombre, is_demo, status, trial_ends_at, creado_at)
         VALUES ('J', :rif, :nombre, 1, 'active', DATE_ADD(NOW(), INTERVAL 15 DAY), NOW())"
    );
    $stmt->execute([
        ':rif'    => 'J-' . $fixtureSufijo . substr(sha1($nombre), 0, 4),
        ':nombre' => $nombre,
    ]);

    return (int)$fixturePdo->lastInsertId();
};

$tenantAId = $crearEmpresaDePruebas('Empresa A de pruebas ' . $fixtureSufijo);
$tenantBId = $crearEmpresaDePruebas('Empresa B de pruebas ' . $fixtureSufijo);

// Catalogo minimo propio de A. Se crea por el repositorio, no con SQL a pelo,
// para que las filas sean validas segun las mismas reglas que usa la aplicacion.
TenantContext::withTenant($tenantAId, static function () use ($fixtureRepo, $fixtureSufijo): void {
    foreach (['SKU-A-1', 'SKU-A-2'] as $sku) {
        $fixtureRepo->create([
            'sku' => $sku . '-' . $fixtureSufijo, 'nombre' => 'Producto de catalogo ' . $sku,
            'descripcion' => 'Fila base creada por el fixture de pruebas de tenancy',
            'precio' => 10.0, 'stock' => 5, 'min_stock' => 1, 'estado' => 'activo',
            'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
            'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
            'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
            'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
        ]);
    }
});

define('TENANT_A', $tenantAId);
define('TENANT_B', $tenantBId);

register_shutdown_function(static function () use ($fixturePdo, $tenantAId, $tenantBId): void {
    foreach ([$tenantAId, $tenantBId] as $id) {
        try {
            $fixturePdo->prepare('DELETE FROM empresa WHERE id = ?')->execute([$id]);
        } catch (Throwable $e) {
            // Nunca enmascarar el resultado del test con un fallo de limpieza. Si
            // esta fila sobrevive, la siguiente ejecucion crea otras y el
            // proceso sigue siendo correcto.
        }
    }
});

echo "  [fixture] TENANT_A = empresa {$tenantAId} con catalogo propio | TENANT_B = empresa {$tenantBId} (se eliminan al terminar)\n";