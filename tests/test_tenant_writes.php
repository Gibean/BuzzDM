<?php
/**
 * test_tenant_writes.php
 *
 * Aislamiento de las vías de ESCRITURA de pedidos, envíos y usuarios.
 *
 * Complementa a test_tenant_isolation.php, que cubre el catálogo. Aquí importa
 * otra cosa: que una escritura desde la empresa 14 no termine en la empresa 1.
 *
 * El fallo que se verifica es el del DEFAULT 1. Estas tablas lo traían y sus
 * INSERT lo omitían, de modo que el guardado era correcto para la empresa 1 y
 * silenciosamente erróneo para cualquier otra. Una prueba que solo comprobara
 * "el guardado devuelve true" habría pasado en ambos casos.
 *
 * Todas las pruebas usan filas creadas aquí, no las 600 de empresa 1: si una
 * aserción fallara por un bug real, el daño se limita a datos de prueba.
 *
 * Uso: php tests/test_tenant_writes.php
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';
require_once __DIR__ . '/../php/src/Repository/ProductRepository.php';
require_once __DIR__ . '/../php/src/Repository/OrderRepository.php';
require_once __DIR__ . '/../php/src/Repository/ShipmentRepository.php';
require_once __DIR__ . '/../php/src/Repository/UserRepository.php';

// Las dos empresas las crea la propia prueba y se borran al terminar.
// Antes eran las fijas 1 (Librería Txiki) y 14 (la demo del navegador).
//
// El fixture usa define(), y un define() no pisa a un const declarado antes en
// el mismo archivo: con const TENANT_A = 1 delante, el fixture creaba sus
// empresas pero estas pruebas seguían operando sobre la empresa 1.
require_once __DIR__ . '/_tenant_fixture.php';

$passed = 0;
$failed = 0;

$pdo = \Database::getConnection();
$productos = new ProductRepository();
$pedidos = new OrderRepository();
$envios = new ShipmentRepository();
$usuarios = new UserRepository();

$SUFIJO = (string)getmypid() . substr((string)time(), -4);

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

function crearProducto(int $empresaId, string $sku): int
{
    global $productos;

    return (int)TenantContext::withTenant($empresaId, static function () use ($productos, $sku) {
        return $productos->create([
            'sku' => $sku, 'nombre' => 'Producto escritura ' . $sku,
            'descripcion' => 'Fixture de la prueba de escritura',
            'precio' => 25.0, 'stock' => 10, 'min_stock' => 1, 'estado' => 'activo',
            'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
            'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
            'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
            'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
            'es_kit_virtual' => 0, 'variante_atributo' => null,
        ]);
    });
}

function crearPedido(int $empresaId, string $referencia): int
{
    global $pedidos;

    return (int)TenantContext::withTenant($empresaId, static function () use ($pedidos, $referencia) {
        return $pedidos->createHeader([
            'cliente_id' => null,
            'referencia' => $referencia,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'total' => 50.0,
            'estado_pago' => 'no_pagado',
            'estado_envio' => 'pendiente',
            'direccion_envio' => 'Direccion de prueba',
            'notas_envio' => 'Nota de prueba',
        ]);
    });
}

function crearUsuario(int $empresaId, string $email): int
{
    global $usuarios;

    return (int)TenantContext::withTenant($empresaId, static function () use ($usuarios, $email) {
        return $usuarios->create([
            'nombre' => 'Usuario de prueba',
            'email' => $email,
            'password' => password_hash('no-es-la-real', PASSWORD_DEFAULT),
            'rol' => 'operador',
            'estado' => 'activo',
        ]);
    });
}

function empresaDe(int $filaId, string $tabla): ?int
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT empresa_id FROM `$tabla` WHERE id = ?");
    $stmt->execute([$filaId]);
    $v = $stmt->fetchColumn();
    return $v === false ? null : (int)$v;
}

echo "=== AISLAMIENTO DE ESCRITURAS (pedidos, envios, usuarios) ===\n\n";

$pedidoA = $pedidoB = 0;
$productoA = $productoB = 0;
$usuarioA = $usuarioB = 0;
$emailA = $emailB = '';

try {

// ---------------------------------------------------------------------------
echo "[1] El INSERT se atribuye al tenant activo, no al DEFAULT de la columna\n";
// Este es el bug de fondo: la columna tiene DEFAULT 1, asi que un INSERT que la
// omita se guarda en la empresa 1 sin dar error.
// ---------------------------------------------------------------------------
$referenciaA = 'ESCR-A-' . $SUFIJO;
$referenciaB = 'ESCR-B-' . $SUFIJO;
$pedidoA = crearPedido(TENANT_A, $referenciaA);
$pedidoB = crearPedido(TENANT_B, $referenciaB);

check('El pedido de la empresa ' . TENANT_A . ' se crea', $pedidoA > 0);
check('El pedido de la empresa ' . TENANT_B . ' se crea', $pedidoB > 0);

check(
    'El pedido creado desde la empresa ' . TENANT_B . ' queda en la ' . TENANT_B,
    empresaDe($pedidoB, 'pedidos') === TENANT_B,
    'empresa_id=' . var_export(empresaDe($pedidoB, 'pedidos'), true)
);
check(
    'El pedido creado desde la empresa ' . TENANT_A . ' queda en la ' . TENANT_A,
    empresaDe($pedidoA, 'pedidos') === TENANT_A,
    'empresa_id=' . var_export(empresaDe($pedidoA, 'pedidos'), true)
);

$emailA = "escritura-a-$SUFIJO@ejemplo.test";
$emailB = "escritura-b-$SUFIJO@ejemplo.test";
$usuarioA = crearUsuario(TENANT_A, $emailA);
$usuarioB = crearUsuario(TENANT_B, $emailB);

check('El usuario de la empresa ' . TENANT_B . ' se crea', $usuarioB > 0);
check(
    'El usuario creado desde la empresa ' . TENANT_B . ' queda en la ' . TENANT_B,
    empresaDe($usuarioB, 'usuarios') === TENANT_B,
    'empresa_id=' . var_export(empresaDe($usuarioB, 'usuarios'), true)
);

// Sin tenant no se escribe: debe fallar, no caer en la empresa 1.
// Se llama al repositorio directamente, no a crearPedido(): ese helper envuelve
// la llamada en withTenant() y por tanto nunca probaria la ausencia de tenant.
TenantContext::clear();
unset($_SESSION['empresa_id']);
$lanzo = false;
try {
    $pedidos->createHeader([
        'cliente_id' => null,
        'referencia' => 'ESCR-SIN-TENANT-' . $SUFIJO,
        'fecha_creacion' => date('Y-m-d H:i:s'),
        'total' => 50.0,
        'estado_pago' => 'no_pagado',
        'estado_envio' => 'pendiente',
        'direccion_envio' => 'Direccion de prueba',
        'notas_envio' => 'Nota de prueba',
    ]);
} catch (TenantRequiredException $e) {
    $lanzo = true;
}
check('createHeader() sin tenant lanza TenantRequiredException', $lanzo);

$huerfano = (int)$pdo->query(
    "SELECT COUNT(*) FROM pedidos WHERE referencia = 'ESCR-SIN-TENANT-$SUFIJO'"
)->fetchColumn();
check('No quedó ningún pedido sin empresa por el camino', $huerfano === 0, "encontrados=$huerfano");

// ---------------------------------------------------------------------------
echo "\n[2] Lectura: cada empresa solo ve sus pedidos\n";
// ---------------------------------------------------------------------------
$vistoPorB = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->findById($pedidoA));
check('La empresa ' . TENANT_B . ' no puede leer el pedido ' . $pedidoA . ' ajeno', $vistoPorB === null);

$vistoPorA = TenantContext::withTenant(TENANT_A, static fn() => $pedidos->findById($pedidoB));
check('La empresa ' . TENANT_A . ' no puede leer el pedido ' . $pedidoB . ' ajeno', $vistoPorA === null);

$propioB = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->findById($pedidoB));
check('La empresa ' . TENANT_B . ' sí lee su propio pedido', $propioB !== null);

// Control positivo del listado: el tenant debe ver su pedido y no el ajeno.
$listaB = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->findAll(['limit' => 1000]));
$idsListaB = array_column($listaB['orders'], 'id');
check('findAll() del tenant ' . TENANT_B . ' incluye su pedido', in_array($pedidoB, $idsListaB, true));
check('findAll() del tenant ' . TENANT_B . ' excluye el pedido ajeno', !in_array($pedidoA, $idsListaB, true));

// El listado no debe filtrar nada por id, solo por empresa.
$fueraDeRango = array_filter(
    $listaB['orders'],
    static fn($o) => (int)$o['empresa_id'] !== TENANT_B
);
check('Ninguna fila del listado pertenece a otra empresa', count($fueraDeRango) === 0);

// Y el tenant principal debe ver los suyos.
$listaA = TenantContext::withTenant(TENANT_A, static fn() => $pedidos->findAll(['limit' => 1000]));
$idsListaA = array_column($listaA['orders'], 'id');
check('findAll() del tenant ' . TENANT_A . ' incluye su pedido', in_array($pedidoA, $idsListaA, true));
check('findAll() del tenant ' . TENANT_A . ' excluye el pedido ajeno', !in_array($pedidoB, $idsListaA, true));

// ---------------------------------------------------------------------------
echo "\n[3] Escritura sobre un pedido ajeno\n";
// updateHeader() devolvia true con cualquier id. Con un id del tenant 1, el
// tenant 14 confirmaba un guardado que habia modificado datos de otro.
// ---------------------------------------------------------------------------
$cabeceraAjena = [
    'cliente_id' => null,
    'referencia' => 'REESCRITO-POR-EL-TENANT-EQUIVOCADO',
    'fecha_creacion' => date('Y-m-d H:i:s'),
    'total' => 999.0,
    'estado_pago' => 'pagado',
    'estado_envio' => 'enviado',
    'direccion_envio' => 'Direccion reescrita',
    'notas_envio' => 'Nota reescrita',
];

$actualizado = TenantContext::withTenant(
    TENANT_B,
    static fn() => $pedidos->updateHeader($pedidoA, $cabeceraAjena)
);
check('updateHeader() sobre un pedido ajeno devuelve false', $actualizado === false, "devolvio=" . var_export($actualizado, true));

$referenciaReal = (string)$pdo->query("SELECT referencia FROM pedidos WHERE id = $pedidoA")->fetchColumn();
check('La referencia del pedido ajeno quedo intacta', $referenciaReal === $referenciaA, "ahora='$referenciaReal'");

$borrado = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->softDelete($pedidoA));
check('softDelete() sobre un pedido ajeno devuelve false', $borrado === false);

$deletedAt = $pdo->query("SELECT deleted_at FROM pedidos WHERE id = $pedidoA")->fetchColumn();
check('El pedido ajeno no quedo marcado como borrado', $deletedAt === null);

$restaurado = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->restore($pedidoA));
check('restore() sobre un pedido ajeno devuelve false', $restaurado === false);

// Control positivo: el mismo update sobre el pedido propio sí funciona.
$cabeceraPropia = $cabeceraAjena;
$cabeceraPropia['referencia'] = 'ESCRITO-EN-SU-PROPIO-TENANT';
$okPropio = TenantContext::withTenant(
    TENANT_A,
    static fn() => $pedidos->updateHeader($pedidoA, $cabeceraPropia)
);
check('updateHeader() sobre el pedido propio devuelve true', $okPropio === true);

$refTrasUpdate = (string)$pdo->query("SELECT referencia FROM pedidos WHERE id = $pedidoA")->fetchColumn();
check('El cambio sobre el pedido propio si se persistio', $refTrasUpdate === 'ESCRITO-EN-SU-PROPIO-TENANT');

// ---------------------------------------------------------------------------
echo "\n[4] detalle_pedido sin empresa_id: el alcance viene del pedido padre\n";
// detalle_pedido no tiene columna empresa_id, asi que no se puede filtrar por
// ella. deleteDetails($id) sin comprobar el padre borraba las lineas del pedido
// que leouniéramos: era la forma mas directa de destruir datos de otro tenant.
// ---------------------------------------------------------------------------
$productoA = crearProducto(TENANT_A, 'ESCR-P-A-' . $SUFIJO);
$productoB = crearProducto(TENANT_B, 'ESCR-P-B-' . $SUFIJO);

$detalleOk = TenantContext::withTenant(TENANT_A, static fn() => $pedidos->createDetail($pedidoA, [
    'producto_id' => $productoA, 'cantidad' => 2, 'precio_unitario' => 25.0,
]));
check('createDetail() sobre el pedido propio inserta', $detalleOk === true);

$lineasAntes = (int)$pdo->query("SELECT COUNT(*) FROM detalle_pedido WHERE pedido_id = $pedidoA")->fetchColumn();
check('El pedido propio tiene 1 linea', $lineasAntes === 1, "lineas=$lineasAntes");

// Desde el tenant equivocado no se puede ni añadir ni borrar.
$detalleAjeno = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->createDetail($pedidoA, [
    'producto_id' => $productoB, 'cantidad' => 99, 'precio_unitario' => 1.0,
]));
check('createDetail() sobre un pedido ajeno devuelve false', $detalleAjeno === false);

$lineasTras = (int)$pdo->query("SELECT COUNT(*) FROM detalle_pedido WHERE pedido_id = $pedidoA")->fetchColumn();
check('No se inserto ninguna linea en el pedido ajeno', $lineasTras === 1, "lineas=$lineasTras");

$borrarAjeno = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->deleteDetails($pedidoA));
check('deleteDetails() sobre un pedido ajeno devuelve false', $borrarAjeno === false);

$lineasFinal = (int)$pdo->query("SELECT COUNT(*) FROM detalle_pedido WHERE pedido_id = $pedidoA")->fetchColumn();
check('Las lineas del pedido ajeno siguen intactas', $lineasFinal === 1, "lineas=$lineasFinal");

$detallesAjenos = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->getDetails($pedidoA));
check('getDetails() de un pedido ajeno devuelve vacio', $detallesAjenos === []);

// Control positivo sobre el pedido propio.
$detallesPropios = TenantContext::withTenant(TENANT_A, static fn() => $pedidos->getDetails($pedidoA));
check('getDetails() del pedido propio devuelve su linea', count($detallesPropios) === 1);

$borrarPropio = TenantContext::withTenant(TENANT_A, static fn() => $pedidos->deleteDetails($pedidoA));
check('deleteDetails() sobre el pedido propio funciona', $borrarPropio === true);

// ---------------------------------------------------------------------------
echo "\n[5] Stock: adjustStock() no toca productos de otra empresa\n";
// ---------------------------------------------------------------------------
$stockAntes = (int)$pdo->query("SELECT stock FROM productos WHERE id = $productoA")->fetchColumn();

TenantContext::withTenant(TENANT_B, static function () use ($pedidos, $productoA) {
    $pedidos->adjustStock($productoA, -5);
});

$stockDespues = (int)$pdo->query("SELECT stock FROM productos WHERE id = $productoA")->fetchColumn();
check('El stock del producto ajeno no cambio', $stockDespues === $stockAntes, "antes=$stockAntes ahora=$stockDespues");

$stockAjenoLectura = TenantContext::withTenant(TENANT_B, static fn() => $pedidos->getProductStock($productoA));
check('getProductStock() de un producto ajeno devuelve null', $stockAjenoLectura === null);

TenantContext::withTenant(TENANT_A, static function () use ($pedidos, $productoA) {
    $pedidos->adjustStock($productoA, -3);
});
$stockFinal = (int)$pdo->query("SELECT stock FROM productos WHERE id = $productoA")->fetchColumn();
check('El stock del producto propio si se descuenta', $stockFinal === $stockAntes - 3, "antes=$stockAntes ahora=$stockFinal");

// ---------------------------------------------------------------------------
echo "\n[6] ShipmentRepository: updateStatus() sobre un pedido ajeno\n";
// El fallo mas silencioso de este lote: el UPDATE no filtraba por empresa y
// devolvia true porque afectaba de verdad a una fila ajena.
// ---------------------------------------------------------------------------
$envioAjeno = TenantContext::withTenant(
    TENANT_B,
    static fn() => $envios->updateStatus($pedidoA, 'recibido', 'RASTREO-INTRUSO')
);
check('updateStatus() sobre un pedido ajeno devuelve false', $envioAjeno === false, "devolvio=" . var_export($envioAjeno, true));

$estadoReal = (string)$pdo->query("SELECT estado_envio FROM pedidos WHERE id = $pedidoA")->fetchColumn();
$trackingReal = $pdo->query("SELECT tracking_number FROM pedidos WHERE id = $pedidoA")->fetchColumn();
check('El estado de envio del pedido ajeno quedo intacto', $estadoReal === 'enviado', "estado='$estadoReal'");
check('No se escribio un numero de rastreo ajeno', $trackingReal === null, "tracking=" . var_export($trackingReal, true));

$envioPropio = TenantContext::withTenant(
    TENANT_A,
    static fn() => $envios->updateStatus($pedidoA, 'recibido', 'RASTREO-PROPIO')
);
check('updateStatus() sobre el pedido propio devuelve true', $envioPropio === true);

$estadoTras = (string)$pdo->query("SELECT estado_envio FROM pedidos WHERE id = $pedidoA")->fetchColumn();
check('El estado del pedido propio si se actualizo', $estadoTras === 'recibido', "estado='$estadoTras'");

$enviosB = TenantContext::withTenant(TENANT_B, static fn() => $envios->findAll(['limit' => 1000]));
$idsEnviosB = array_column($enviosB['shipments'], 'id');
check('El listado de envios del tenant ' . TENANT_B . ' excluye pedidos ajenos', !in_array($pedidoA, $idsEnviosB, true));

// ---------------------------------------------------------------------------
echo "\n[7] Usuarios: gestion por tenant, unicidad de email global\n";
// ---------------------------------------------------------------------------
$usuariosB = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->findAll());
$idsUsuariosB = array_column($usuariosB, 'id');
check('findAll() del tenant ' . TENANT_B . ' excluye usuarios ajenos', !in_array($usuarioA, $idsUsuariosB, true));
check('findAll() del tenant ' . TENANT_B . ' incluye su propio usuario', in_array($usuarioB, $idsUsuariosB, true));

$usuarioAjeno = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->findById($usuarioA));
check('findById() de un usuario ajeno devuelve null', $usuarioAjeno === null);

$propio = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->findById($usuarioB));
check('findById() del usuario propio lo devuelve', $propio !== null);

// findByEmail() debe seguir siendo global: el indice UNIQUE de email lo es, y
// UserService lo usa para avisar de duplicados. Si se filtrara por tenant, un
// tenant podria reutilizar el correo de otro y el INSERT reventaria con
// duplicate key en vez de dar un error claro.
$emailAjeno = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->findByEmail($emailA));
check('findByEmail() sigue encontrando usuarios de otras empresas', $emailAjeno !== null && (int)$emailAjeno['id'] === $usuarioA);

$actualizadoUsuario = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->update($usuarioA, [
    'nombre' => 'REESCRITO', 'email' => $emailA, 'rol' => 'admin',
]));
check('update() sobre un usuario ajeno devuelve false', $actualizadoUsuario === false);

$nombreReal = (string)$pdo->query("SELECT nombre FROM usuarios WHERE id = $usuarioA")->fetchColumn();
check('El nombre del usuario ajeno quedo intacto', $nombreReal !== 'REESCRITO', "nombre='$nombreReal'");

$desactivado = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->updateStatus($usuarioA, 'inactivo'));
check('updateStatus() sobre un usuario ajeno devuelve false', $desactivado === false);

$borradoUsuario = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->delete($usuarioA));
check('delete() sobre un usuario ajeno devuelve false', $borradoUsuario === false);

$estadoRealUsuario = (string)$pdo->query("SELECT estado FROM usuarios WHERE id = $usuarioA")->fetchColumn();
check('El usuario ajeno sigue activo', $estadoRealUsuario === 'activo', "estado='$estadoRealUsuario'");

// El secreto de 2FA de otro tenant no debe ser legible.
TenantContext::withTenant(TENANT_A, static fn() => $usuarios->enable2FA($usuarioA, 'SECRETODETENANT-A'));
$secretoAjeno = TenantContext::withTenant(TENANT_B, static fn() => $usuarios->get2FASecret($usuarioA));
check('get2FASecret() de un usuario ajeno devuelve null', $secretoAjeno === null, "devolvio=" . var_export($secretoAjeno, true));

$secretoPropio = TenantContext::withTenant(TENANT_A, static fn() => $usuarios->get2FASecret($usuarioA));
check('get2FASecret() del usuario propio si devuelve el secreto', $secretoPropio === 'SECRETODETENANT-A');

// countAdmins() debe contar solo los de este tenant: UserService lo usa para no
// permitir borrar al ultimo administrador, y con el conteo global un tenant sin
// admins quedaba bloqueado por el admin de otra empresa.
$adminsA = (int)TenantContext::withTenant(TENANT_A, static fn() => $usuarios->countAdmins());
$adminsB = (int)TenantContext::withTenant(TENANT_B, static fn() => $usuarios->countAdmins());
$adminsRealesA = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE empresa_id = " . TENANT_A . " AND rol = 'admin'")->fetchColumn();
$adminsRealesB = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE empresa_id = " . TENANT_B . " AND rol = 'admin'")->fetchColumn();
check('countAdmins() del tenant ' . TENANT_A . ' coincide con el SQL directo', $adminsA === $adminsRealesA, "repo=$adminsA sql=$adminsRealesA");
check('countAdmins() del tenant ' . TENANT_B . ' coincide con el SQL directo', $adminsB === $adminsRealesB, "repo=$adminsB sql=$adminsRealesB");

echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";

} catch (Throwable $e) {
    $failed++;
    echo "\n[ERROR NO CONTROLADO] " . get_class($e) . ': ' . $e->getMessage() . "\n";
    echo "  en " . $e->getFile() . ':' . $e->getLine() . "\n";
    echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";
} finally {
    // Borrado por id con SQL directo, sin depender del contexto de tenant ni
    // del codigo bajo prueba. El orden respeta las FKs: detalle antes que
    // pedido, pedido antes que producto.
    $pedidosIds = array_values(array_filter([$pedidoA, $pedidoB]));
    $productosIds = array_values(array_filter([$productoA, $productoB]));
    $usuariosIds = array_values(array_filter([$usuarioA, $usuarioB]));

    if ($pedidosIds !== []) {
        $m = implode(',', array_map('intval', $pedidosIds));
        $pdo->exec("DELETE FROM detalle_pedido WHERE pedido_id IN ($m)");
        $pdo->exec("DELETE FROM pedidos WHERE id IN ($m)");
    }
    if ($productosIds !== []) {
        $m = implode(',', array_map('intval', $productosIds));
        $pdo->exec("DELETE FROM productos WHERE id IN ($m)");
    }
    if ($usuariosIds !== []) {
        $m = implode(',', array_map('intval', $usuariosIds));
        $pdo->exec("DELETE FROM usuarios WHERE id IN ($m)");
    }

    // Segunda pasada por sufijo: si una asercion fallo antes de registrar su id
    // en la variable de arriba, el fixture seguia en la base sin ser borrado.
    $pdo->exec(
        "DELETE d FROM detalle_pedido d
         JOIN pedidos p ON p.id = d.pedido_id
         WHERE p.referencia LIKE '%$SUFIJO'"
    );
    $pdo->exec("DELETE FROM pedidos WHERE referencia LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM productos WHERE sku LIKE '%$SUFIJO'");
    $pdo->exec("DELETE FROM usuarios WHERE email LIKE '%$SUFIJO'");

    $restos = (int)$pdo->query(
        "SELECT (SELECT COUNT(*) FROM pedidos WHERE referencia LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM productos WHERE sku LIKE '%$SUFIJO')
              + (SELECT COUNT(*) FROM usuarios WHERE email LIKE '%$SUFIJO')"
    )->fetchColumn();

    echo "\nLimpieza: " . ($restos === 0
        ? "sin filas de prueba (sufijo $SUFIJO)"
        : "QUEDAN $restos fila(s) de prueba con sufijo $SUFIJO") . "\n";
}

exit($failed > 0 ? 1 : 0);
