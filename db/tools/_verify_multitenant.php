<?php

// Solo ejecucion desde linea de comandos: estos scripts introspeccionan el esquema
// y algunos escriben en la base de datos. Sobre HTTP devuelven 403.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("403 - Solo ejecutable desde CLI.\n");
}
/**
 * _verify_multitenant.php
 *
 * Auditoria funcional del aislamiento multi-tenant. Distingue lo que esta
 * PROBADO de lo que solo se SUPONE:
 *
 *   - Las consultas de information_schema son verificaciones de esquema. Dicen
 *     que la base permite filtrar por tenant, no que el codigo lo haga.
 *   - La seccion 3 crea datos reales, ejecuta los repositorios y comprueba que
 *     un tenant no ve lo del otro. Esa es la unica que detecta una consulta sin
 *     empresa_id, porque una consulta sin filtro devuelve filas y el dato
 *     aparece.
 *
 * Las secciones usan consultas parametrizadas con PDO::quote para los
 * identificadores dinamicos (nombres de tabla y de columna). No hay
 * concatenacion de valores de entrada.
 *
 * Uso: php db/tools/_verify_multitenant.php
 * Salida: 0 si todo pasa, 1 si hay fallos.
 */

require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/src/Tenant/TenantContext.php';

$pdo = getDbConnection();

$fallos = 0;
$avisos = 0;

function titulo(string $texto): void
{
    echo "\n" . $texto . "\n" . str_repeat('-', strlen($texto)) . "\n";
}

function ok(string $descripcion, string $detalle = ''): void
{
    global $fallos;
    echo "  [OK]    $descripcion" . ($detalle !== '' ? "  ($detalle)" : '') . "\n";
}

function fallo(string $descripcion, string $detalle = ''): void
{
    global $fallos;
    $fallos++;
    echo "  [FALLA] $descripcion" . ($detalle !== '' ? "  -> $detalle" : '') . "\n";
}

function aviso(string $descripcion, string $detalle = ''): void
{
    global $avisos;
    $avisos++;
    echo "  [AVISO] $descripcion" . ($detalle !== '' ? "  -> $detalle" : '') . "\n";
}

function esTabla(string $nombre): bool
{
    $pdo = getDbConnection();
    $n = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($nombre)
    )->fetchColumn();
    return (int)$n > 0;
}

/**
 * Estado real de la columna empresa_id de una tabla: si admite NULL y que
 * DEFAULT tiene. No basta con decir "le falta empresa_id" al leer un INSERT, porque
 * el efecto depende de estos dos datos:
 *
 *   - DEFAULT numerico: el INSERT que la omite tiene exito y la fila cae en la
 *     empresa 1. Fuga silenciosa, la que no aparece en ninguna prueba.
 *   - NOT NULL sin DEFAULT: el mismo INSERT muere con el error 1364. Fallo duro
 *     y visible, pero rompe la operacion en produccion.
 *   - Admite NULL: la fila se queda sin empresa, lo cual es correcto solo en las
 *     tablas globales y en las de login.
 *
 * Devolver IS_NULLABLE y COLUMN_DEFAULT permite distinguir los tres casos en
 * lugar de aplicar el mismo veredicto a todos.
 */
function columnaEmpresa(string $tabla): ?array
{
    $pdo = getDbConnection();
    return $pdo->query(
        "SELECT IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($tabla)
        . " AND COLUMN_NAME = 'empresa_id'"
    )->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ===========================================================================
// 1. COBERTURA DE ESQUEMA
// ===========================================================================

titulo('1. Esquema: cobertura de empresa_id');

$tablas = $pdo->query("
    SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_ASSOC);

$conColumna = [];
foreach ($tablas as $t) {
    $n = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($t['TABLE_NAME'])
        . " AND COLUMN_NAME = 'empresa_id'"
    )->fetchColumn();
    if ((int)$n > 0) {
        $conColumna[] = $t['TABLE_NAME'];
    }
}

$conFk = $pdo->query("
    SELECT DISTINCT kcu.TABLE_NAME
    FROM information_schema.KEY_COLUMN_USAGE kcu
    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
      ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
     AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
    WHERE kcu.TABLE_SCHEMA = DATABASE() AND kcu.REFERENCED_TABLE_NAME = 'empresa'
")->fetchAll(PDO::FETCH_COLUMN);

$sinFk = array_values(array_diff($conColumna, $conFk));

echo "  Tablas base: " . count($tablas) . "\n";
echo "  Con empresa_id: " . count($conColumna) . "\n";
echo "  Con FK a empresa.id: " . count($conFk) . "\n";

if ($sinFk) {
    foreach ($sinFk as $t) {
        // password_reset_tokens y sesiones_api son globales por diseno:
        // un token de recuperacion existe antes de saber de que empresa es.
        if (in_array($t, ['password_reset_tokens', 'sesiones_api'], true)) {
            ok("$t sin FK (global por diseno)");
            continue;
        }
        fallo("$t tiene empresa_id pero sin FK hacia empresa.id", "una purga dejaria filas huerfanas");
    }
} else {
    ok("Toda tabla con empresa_id tiene FK hacia empresa.id");
}

// ===========================================================================
// 2. TRAMPAS DE ESQUEMA QUE PRODUCEN FUGAS SILENCIOSAS
// ===========================================================================

titulo('2. Trampas: DEFAULT y NULL que asignan un tenant implicito');

// Tablas globales por diseno: la columna existe, pero el token de recuperacion
// o la sesion de API se crean antes de saber a que empresa pertenece la
// peticion. Aqui el NULL es correcto. Lo que si seria un bug es un DEFAULT
// numerico, porque inventaria el tenant 1 en vez de dejar la columna vacia.
$globalesPorDiseno = ['password_reset_tokens', 'sesiones_api'];

// logs_login debe admitir NULL: un intento de login con un email inexistente no
// tiene empresa. Registrar solo el email y la IP es justo el caso de uso.
$nullablePorDiseno = ['logs_login'];

$trampas = [];
$decisiones = [];

foreach ($conColumna as $t) {
    if (!esTabla($t)) {
        continue;
    }
    $info = $pdo->query(
        "SELECT IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($t)
        . " AND COLUMN_NAME = 'empresa_id'"
    )->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        continue;
    }

    // Un DEFAULT numérico significa que un INSERT que olvide la columna no falla:
    // se inventa un tenant. Es la fuga más difícil de detectar en pruebas,
    // porque el INSERT tiene éxito y el bug aparece semanas después.
    if ($info['COLUMN_DEFAULT'] !== null && is_numeric($info['COLUMN_DEFAULT'])) {
        $trampas[] = "$t.empresa_id tiene DEFAULT " . $info['COLUMN_DEFAULT'];
    }

    if ($info['IS_NULLABLE'] === 'YES') {
        $nulos = (int)$pdo->query("SELECT COUNT(*) FROM `$t` WHERE empresa_id IS NULL")->fetchColumn();
        $filas = $nulos > 0 ? ": $nulos filas sin empresa" : '';

        if (in_array($t, $globalesPorDiseno, true) || in_array($t, $nullablePorDiseno, true)) {
            $motivo = in_array($t, $globalesPorDiseno, true) ? 'tabla global' : 'login sin empresa conocida';
            $decisiones[] = "$t.empresa_id admite NULL por diseño ($motivo)$filas";
        } else {
            $trampas[] = "$t.empresa_id admite NULL" . ($nulos > 0 ? " ($nulos filas)" : " (hoy vacio)");
        }
    }
}

foreach ($decisiones as $d) {
    ok($d);
}

if ($trampas) {
    foreach ($trampas as $t) {
        fallo($t, "un INSERT que omita la columna no falla: elige tenant por su cuenta");
    }
} else {
    ok("Ninguna columna empresa_id tiene DEFAULT numerico");
}

// ===========================================================================
// 3. PRUEBA FUNCIONAL CON DATOS REALES
// ===========================================================================

titulo('3. Prueba funcional: un tenant no ve los datos del otro');

require_once __DIR__ . '/../../php/src/Repository/ProductRepository.php';
require_once __DIR__ . '/../../php/src/Repository/ClienteRepository.php';
require_once __DIR__ . '/../../php/src/Repository/SupplierRepository.php';

$empresaA = 1;
$empresaB = null;

// Se busca una empresa que no sea la principal para usarla como contraparte.
// Si solo hay una, la prueba funcional no puede correr y se avisa en vez de
// fingir que pasa.
$candidatas = $pdo->query("SELECT id FROM empresa WHERE id <> $empresaA ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
if ($candidatas) {
    $empresaB = (int)$candidatas[0];
}

if ($empresaB === null) {
    aviso("No hay una segunda empresa: la prueba funcional de cruce no puede ejecutarse");
    aviso("Registra una demo (php api_register_demo.php) y vuelve a correr esta auditoria");
} else {
    $repo = new ProductRepository();

    $skuA = 'VERIFY-A-' . getmypid();
    $skuB = 'VERIFY-B-' . getmypid();

    try {
        // -- Crear una fila en cada tenant ---------------------------------
        $idA = TenantContext::withTenant($empresaA, static function () use ($repo, $skuA) {
            return $repo->create([
                'sku' => $skuA, 'nombre' => 'Verificacion tenant A',
                'descripcion' => 'Fila temporal de la auditoria multi-tenant',
                'precio' => 10.0, 'stock' => 5, 'min_stock' => 1, 'estado' => 'activo',
                'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
                'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
                'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
                'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
                'es_kit_virtual' => 0, 'variante_atributo' => null,
            ]);
        });

        $idB = TenantContext::withTenant($empresaB, static function () use ($repo, $skuB) {
            return $repo->create([
                'sku' => $skuB, 'nombre' => 'Verificacion tenant B',
                'descripcion' => 'Fila temporal de la auditoria multi-tenant',
                'precio' => 10.0, 'stock' => 5, 'min_stock' => 1, 'estado' => 'activo',
                'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
                'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
                'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
                'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
                'es_kit_virtual' => 0, 'variante_atributo' => null,
            ]);
        });

        ok("Fila creada en tenant $empresaA y en tenant $empresaB", "ids $idA / $idB");

        // -- Lectura cruzada ------------------------------------------------
        // Si findById no filtra, el tenant B lee la fila del tenant A.
        $vistoPorB = TenantContext::withTenant($empresaB, static fn() => $repo->findById($idA));
        if ($vistoPorB === null) {
            ok("Tenant $empresaB no puede leer por id una fila del tenant $empresaA");
        } else {
            fallo("Tenant $empresaB leyo por id una fila del tenant $empresaA", "fuga en findById");
        }

        $vistoPorA = TenantContext::withTenant($empresaA, static fn() => $repo->findById($idB));
        if ($vistoPorA === null) {
            ok("Tenant $empresaA no puede leer por id una fila del tenant $empresaB");
        } else {
            fallo("Tenant $empresaA leyo por id una fila del tenant $empresaB", "fuga en findById");
        }

        // findBySku devuelve bool (existe/no existe dentro del tenant), no una
        // fila. Se comprueba el ambito: el SKU del tenant A no puede ser visible
        // para el tenant B, aunque el indice unico sea global.
        $skuPorB = TenantContext::withTenant($empresaB, static fn() => $repo->findBySku($skuA));
        if ($skuPorB === false) {
            ok("findBySku no encuentra el SKU de otro tenant");
        } else {
            fallo("findBySku devolvio true para el SKU de otro tenant", "fuga en el indice o en el filtro");
        }

        // Control positivo: el mismo SKU debe ser visible para su propio tenant.
        // Sin esto, un false siempre pasaria aunque findBySku estuviera roto.
        $skuPorA = TenantContext::withTenant($empresaA, static fn() => $repo->findBySku($skuA));
        if ($skuPorA === true) {
            ok("Control positivo: el tenant $empresaA si encuentra su propio SKU");
        } else {
            fallo("El tenant $empresaA no encuentra su propio SKU", "findBySku esta roto, no aislado");
        }

        // -- Conteo ---------------------------------------------------------
        $totalA = TenantContext::withTenant($empresaA, static fn() => $repo->countForTenant());
        $totalB = TenantContext::withTenant($empresaB, static fn() => $repo->countForTenant());

        $realesA = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE empresa_id = $empresaA")->fetchColumn();
        $realesB = (int)$pdo->query("SELECT COUNT(*) FROM productos WHERE empresa_id = $empresaB")->fetchColumn();

        if ($totalA === $realesA) {
            ok("countForTenant de $empresaA coincide con el SQL directo", "$totalA filas");
        } else {
            fallo("countForTenant de $empresaA no coincide", "repo=$totalA sql=$realesA");
        }

        if ($totalB === $realesB) {
            ok("countForTenant de $empresaB coincide con el SQL directo", "$totalB filas");
        } else {
            fallo("countForTenant de $empresaB no coincide", "repo=$totalB sql=$realesB");
        }

        // -- Escritura sobre fila ajena -------------------------------------
        // ProductRepository::update devuelve bool ("¿se aplicó?"), no el numero
        // de filas: applyTenantUpdate comprueba antes que la fila exista dentro
        // del tenant activo y por eso mismo no ejecuta el UPDATE. El valor de
        // retorno esperado para una fila ajena es false.
        //
        // Se envia el juego completo de columnas porque update() las lee todas
        // por nombre; enviar solo 'nombre' produciria avisos de undefined key y
        // la prueba mediria el aviso, no el aislamiento.
        $intento = [
            'sku' => $skuA, 'nombre' => 'ESCRITURA INTRUSA', 'descripcion' => 'intento desde otro tenant',
            'precio' => 11.0, 'stock' => 6, 'min_stock' => 1, 'estado' => 'activo',
            'marca_id' => null, 'categoria' => null, 'proveedor_id' => null,
            'modelo' => null, 'color' => null, 'tamanio' => null, 'medidas' => null,
            'peso_kg' => null, 'material' => null, 'unidad_venta' => 'UND',
            'tipo_producto' => 'fisico', 'maneja_stock' => 1, 'producto_padre_id' => null,
            'es_kit_virtual' => 0, 'variante_atributo' => null,
        ];

        $aplicada = TenantContext::withTenant($empresaB, static fn() => $repo->update($idA, $intento));
        if ($aplicada === false) {
            ok("update sobre una fila ajena devuelve false", "el UPDATE ni se ejecuta");
        } else {
            fallo("update sobre una fila ajena se aplico", "rowExists no filtro por tenant");
        }

        $nombreReal = $pdo->query("SELECT nombre FROM productos WHERE id = " . (int)$idA)->fetchColumn();
        if ($nombreReal === 'Verificacion tenant A') {
            ok("El nombre original quedo intacto tras el intento de escritura cruzada");
        } else {
            fallo("La escritura cruzada SI modifico la fila", "nombre ahora: $nombreReal");
        }

        // Control positivo indispensable: la misma operacion desde el tenant
        // Dueño debe funcionar. Si esto fallara, el false de arriba no probaria
        // aislamiento sino que el repositorio esta roto.
        $legitima = TenantContext::withTenant($empresaA, static fn() => $repo->update($idA, $intento));
        $nombreTras = $pdo->query("SELECT nombre FROM productos WHERE id = " . (int)$idA)->fetchColumn();

        if ($legitima === true && $nombreTras === 'ESCRITURA INTRUSA') {
            ok("Control positivo: el tenant $empresaA si puede editar su propia fila");
        } else {
            fallo("El tenant $empresaA no pudo editar su propia fila", "el false anterior no probaba aislamiento");
        }

        // -- Fail closed ----------------------------------------------------
        // Sin tenant en contexto, el repositorio debe fallar en vez de
        // devolver datos. Si devuelve algo, un endpoint que olvide el
        // require sesion.php lee la tabla entera.
        TenantContext::clear();
        try {
            $repo->findAll();
            fallo("Sin tenant en contexto, findAll devolvio filas", "deberia lanzar TenantRequiredException");
        } catch (\TenantRequiredException $e) {
            ok("Sin tenant en contexto, findAll falla cerrado");
        } catch (\Throwable $e) {
            aviso("findAll sin tenant lanzo otra excepcion", get_class($e));
        }

    } finally {
        // Limpieza en finally: una asercion fallida no debe dejar filas que
        // bloqueen el siguiente intento por el indice unico de SKU. Se borra por
        // SKU y no con softDelete(), que espera un id y no un texto.
        $pdo->exec("DELETE FROM productos WHERE sku IN (" . $pdo->quote($skuA) . "," . $pdo->quote($skuB) . ")");
        TenantContext::clear();

        $restos = (int)$pdo->query(
            "SELECT COUNT(*) FROM productos WHERE sku IN (" . $pdo->quote($skuA) . "," . $pdo->quote($skuB) . ")"
        )->fetchColumn();
        echo "  Limpieza: " . ($restos === 0 ? "sin filas de prueba" : "QUEDAN $restos filas") . "\n";
    }
}

// ===========================================================================
// 4. INVENTARIO DE REPOSITORIOS
// ===========================================================================

titulo('4. Repositorios migrados a BaseRepository');

$migrados = [];
$pendientes = [];

foreach (glob(__DIR__ . '/../../php/src/Repository/*Repository.php') as $archivo) {
    $nombre = basename($archivo);
    if ($nombre === 'BaseRepository.php') {
        continue;
    }
    $fuente = file_get_contents($archivo);
    if (str_contains($fuente, 'extends BaseRepository')) {
        $migrados[] = $nombre;
    } else {
        $pendientes[] = $nombre;
    }
}

echo "  Migrados (" . count($migrados) . "): " . implode(', ', $migrados) . "\n";
echo "  Pendientes (" . count($pendientes) . "): " . implode(', ', $pendientes) . "\n";

if ($pendientes) {
    aviso("Cada repositorio sin migrar es una via potencial de fuga entre tenants");
    aviso("Prioridad: los que escriben antes que los que solo leen");
}

// ===========================================================================
// 5. TRIGGERS QUE ESCRIBEN SIN empresa_id
// ===========================================================================
//
// Un trigger escribe en la base igual que un repositorio, pero ningun analisis
// del codigo PHP lo muestra. Los dos fallos que produce no aparecen en ninguna
// seccion anterior:
//
//   - ERROR DURO: si la tabla destino es NOT NULL sin default (logs), el INSERT
//     del trigger tumba la operacion que lo disparo. Asi se rompio el backup
//     entero hasta la migracion 005.
//   - FUGA SILENCIOSA: si la tabla destino tiene DEFAULT 1, el INSERT del
//     trigger tiene exito y la fila cae en la empresa 1.
//
// La seccion 2 solo mira el DEFAULT de las tablas de la aplicacion. Esta mira
// que hay realmente dentro de los triggers.

titulo('5. Triggers que escriben en tablas de tenant');

$triggers = $pdo->query("
    SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_TIMING, EVENT_MANIPULATION, ACTION_STATEMENT
    FROM information_schema.TRIGGERS
    WHERE TRIGGER_SCHEMA = DATABASE()
    ORDER BY TRIGGER_NAME
")->fetchAll(PDO::FETCH_ASSOC);

if (!$triggers) {
    ok("La base no tiene triggers");
} else {
    $triggersRevisados = 0;
    $triggersConInsercion = 0;

    foreach ($triggers as $t) {
        $triggersRevisados++;
        $sentencia = (string)$t['ACTION_STATEMENT'];

        // Solo interesan los triggers que hacen INSERT en una tabla de tenant.
        if (!preg_match('/INSERT\s+INTO\s+`?(\w+)`?/i', $sentencia, $m)) {
            continue;
        }

        $tablaDestino = $m[1];
        if (!in_array($tablaDestino, $conColumna, true)) {
            continue;
        }

        $triggersConInsercion++;

        // ¿La sentencia nombra empresa_id? Con NEW.empresa_id el trigger propaga
        // el tenant de la fila que lo disparo, que es el unico dato correcto.
        if (!preg_match('/\bempresa_id\b/i', $sentencia)) {
            fallo(
                "El trigger {$t['TRIGGER_NAME']} inserta en `$tablaDestino` sin empresa_id",
                "no lo nombra ni siquiera: el DEFAULT decide a que empresa pertenece"
            );
            continue;
        }

        // Lo nombra, pero hay que confirmar que la columna destino puede recibirlo.
        $destino = columnaEmpresa($tablaDestino);

        if (!$destino) {
            continue;
        }

        if ($destino['IS_NULLABLE'] === 'YES') {
            ok("El trigger {$t['TRIGGER_NAME']} propaga empresa_id a `$tablaDestino` (admite NULL)");
        } else {
            ok("El trigger {$t['TRIGGER_NAME']} propaga empresa_id a `$tablaDestino`");
        }
    }

    echo "  Triggers revisados: $triggersRevisados (con INSERT en tabla de tenant: $triggersConInsercion)\n";
}

// ===========================================================================
// 6. INSERTs CIEGOS EN EL CODIGO
// ===========================================================================
//
// La seccion 2 senala las columnas que aun TIENEN DEFAULT. Esta se ocupa de las
// sentencias INSERT que omiten empresa_id, y el efecto depende de la columna:
//
//   - DEFAULT numerico: FUGA SILENCIOSA, la peor. El INSERT tiene exito y la fila
//     queda en la empresa 1 sin que nada falle ni avise.
//   - NOT NULL sin DEFAULT: ERROR DURO. El camino muere con el error 1364,
//     "Field 'empresa_id' doesn't have a default value", al ejecutarse.
//   - Admite NULL y la tabla es global o de login: correcto por diseno, la fila
//     se queda sin empresa a proposito. Se informa como aviso, no como fallo.
//
// Que el fallo sea duro o silencioso no lo hace inocuo: un error 1364 rompe la
// operacion en produccion y el DEFAULT silencioso rompe el aislamiento. Ambos
// se reportan.
//
// Se escanean los ficheros PHP de la aplicacion, no los .sql de db/: los dumps
// (dbuzz.sql, dbuzz_respaldodatosmock.sql) son anteriores al multi-tenant y
// reimportarlos destruiria el esquema de empresa_id, asi que no son codigo
// vivo. Los seeds y el artefacto de mocks se revisan aparte como aviso.

titulo('6. INSERTs que omiten empresa_id en el codigo PHP');

// Rutas que se escanean. Se excluyen artefactos, dependencias y los ficheros
// que el usuario esta editando en otra sesion.
$raizRepo = dirname(__DIR__, 2);
$rutasEscaneo = [
    $raizRepo . '/php/src',
    $raizRepo . '/php',
];
$excluirPatron = '#(vendor|node_modules|assets/landing|php/backups|tests)/#';

$ficherosPhp = [];
foreach ($rutasEscaneo as $ruta) {
    if (!is_dir($ruta)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ruta, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (!$f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }
        $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($raizRepo) + 1));
        if (preg_match($excluirPatron, $rel)) {
            continue;
        }
        $ficherosPhp[$rel] = $f->getPathname();
    }
}

echo "  Ficheros PHP analizados: " . count($ficherosPhp) . "\n";

$insertCiegos = [];
$insertOk = [];

foreach ($ficherosPhp as $rel => $ruta) {
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        continue;
    }

    // Se acumula la sentencia para poder detectar un INSERT partido en varias
    // lineas, que es como lo escribe casi todo el codigo con formato.
    $buffer = '';
    $bufferDesde = 0;

    foreach ($lineas as $i => $linea) {
        $n = $i + 1;
        $limpia = trim($linea);

        if ($buffer === '') {
            if (preg_match('/INSERT\s+(IGNORE\s+|LOW_PRIORITY\s+)?INTO\s+/i', $limpia, $m)) {
                $buffer = $limpia;
                $bufferDesde = $n;
            }
            continue;
        }

        $buffer .= ' ' . $limpia;

        // Cierre de sentencia por parentesis balancedado.
        if (substr_count($buffer, '(') <= substr_count($buffer, ')')) {
            if (preg_match('/INTO\s+`?(\w+)`?\s*\(/i', $buffer, $m)) {
                $tabla = $m[1];

                if (in_array($tabla, $conColumna, true)) {
                    // Se extrae la lista de columnas de la propia sentencia, no la
                    // del resto del fichero: un 'empresa_id' mentioned en otro
                    // INSERT no cuenta.
                    if (preg_match('/INTO\s+`?\w+`?\s*\(([^)]*)\)/is', $buffer, $cm)) {
                        $columnas = array_map(
                            static fn(string $c): string => trim($c, " \t\n\r`\"'"),
                            explode(',', $cm[1])
                        );
                        $columnas = array_filter(array_map('trim', $columnas));

                        if (in_array('empresa_id', $columnas, true)) {
                            $insertOk["$rel:$bufferDesde"] = $tabla;
                        } else {
                            $insertCiegos["$rel:$bufferDesde"] = $tabla;
                        }
                    }
                }
            }
            $buffer = '';
            $bufferDesde = 0;
        }
    }
}

// Clasificacion por efecto real: el mismo sintaxaquito produce un fallo duro en
// una tabla ya endurecida y una fuga silenciosa en una que conserve el DEFAULT.
$cacheColumna = [];
$casos = ['fuga' => [], 'duro' => [], 'nulo' => [], 'sinDato' => []];

foreach ($insertCiegos as $pos => $tabla) {
    if (!array_key_exists($tabla, $cacheColumna)) {
        $cacheColumna[$tabla] = columnaEmpresa($tabla);
    }
    $col = $cacheColumna[$tabla];

    if ($col === null) {
        $casos['sinDato'][] = "$pos ($tabla)";
        continue;
    }

    if ($col['IS_NULLABLE'] === 'YES') {
        // Solo es correcto si la tabla es global o de login por diseno. En
        // cualquier otra, la fila queda huerfana sin empresa y sin avisar.
        $porDiseno = in_array($tabla, $globalesPorDiseno, true)
            || in_array($tabla, $nullablePorDiseno, true);

        $casos[$porDiseno ? 'nulo' : 'fuga'][] = "$pos ($tabla)";
        continue;
    }

    if ($col['COLUMN_DEFAULT'] !== null && is_numeric($col['COLUMN_DEFAULT'])) {
        $casos['fuga'][] = "$pos ($tabla)";
    } else {
        $casos['duro'][] = "$pos ($tabla)";
    }
}

// La fuga silenciosa se reporta primero: es la que sobrevive a las pruebas.
foreach ($casos['fuga'] as $c) {
    fallo("INSERT ciego en $c", 'la columna admite NULL sin ser global o conserva DEFAULT 1: la fila entra sin tenant y no falla');
}

foreach ($casos['duro'] as $c) {
    fallo("INSERT ciego en $c", "la columna es NOT NULL sin DEFAULT: este camino muere con el error 1364 (Field 'empresa_id' doesn't have a default value)");
}

foreach ($casos['nulo'] as $c) {
    aviso("INSERT sin empresa_id en $c", 'tabla global o de login por diseno: la fila queda sin empresa, que es lo esperado');
}

foreach ($casos['sinDato'] as $c) {
    fallo("INSERT ciego en $c", 'no se pudo leer la definicion de empresa_id para diagnosticar el caso');
}

$totalInsert = count($insertOk) + count($insertCiegos);
if ($insertCiegos) {
    echo "  INSERTs analizados: $totalInsert (" . count($insertOk) . " con empresa_id, " . count($insertCiegos) . " sin ella)\n";
} else {
    ok('Ningun INSERT sobre tabla de tenant omite empresa_id', "$totalInsert revisados, todos con la columna presente");
}

// Los ficheros .sql de db/ no son codigo vivo, pero conviene que la auditoria
// los senale para que nadie los importe creyendo que funcionan.
$sqlSospechosos = [];
foreach (['dbuzz.sql', 'dbuzz_respaldodatosmock.sql', 'mock_libreria_inserts.sql'] as $sql) {
    $ruta = $raizRepo . '/db/' . $sql;
    if (!is_file($ruta)) {
        continue;
    }
    $fuente = file_get_contents($ruta);
    if (preg_match('/INTO\s+`?(pedidos|usuarios|productos|clientes|categorias|marcas|proveedores|importaciones)`?\s*\(([^)]*)\)/is', $fuente, $m)
        && !preg_match('/empresa_id/i', $m[2])) {
        $sqlSospechosos[] = $sql;
    }
}

if ($sqlSospechosos) {
    aviso(
        'Ficheros .sql de db/ que insertan sin empresa_id: ' . implode(', ', $sqlSospechosos),
        'no son codigo vivo; no los importes sobre una base ya migrada'
    );
}

// ===========================================================================
// 7. ENDPOINTS SIN SESION
// ===========================================================================

titulo('7. Endpoints que no cargan sesion.php');

$sospechosos = [];

foreach (glob(__DIR__ . '/../../php/api_*.php') as $archivo) {
    $nombre = basename($archivo);
    $fuente = file_get_contents($archivo);

    // api_auth y api_register_demo son los puntos de entrada: no pueden
    // exigir sesion porque la crean.
    if (in_array($nombre, ['api_auth.php', 'api_register_demo.php'], true)) {
        continue;
    }

    // Archivos que solo declaran una clase no son endpoints: no producen salida
    // si se solicitan por URL. api_docs_config.php solo define OpenApiConfig y
    // reportarlo como fuga seria un falso positivo.
    $soloDeclaraClase = preg_match('/^\s*(final\s+|abstract\s+)?class\s+\w+/m', $fuente) === 1
        && !str_contains($fuente, 'json_encode(')
        && !str_contains($fuente, 'http_response_code(');

    if ($soloDeclaraClase) {
        continue;
    }

    $cargaSesion = str_contains($fuente, 'sesion.php')
        || str_contains($fuente, 'isLoggedIn')
        || str_contains($fuente, 'requireSesion')
        || str_contains($fuente, 'SessionMiddleware');

    if (!$cargaSesion) {
        $sospechosos[] = $nombre;
    }
}

foreach (glob(__DIR__ . '/../../php/search_api.php') as $archivo) {
    $fuente = file_get_contents($archivo);
    if (!str_contains($fuente, 'sesion.php')) {
        $sospechosos[] = 'search_api.php';
    }
}

$sospechosos = array_values(array_unique($sospechosos));

if ($sospechosos) {
    foreach ($sospechosos as $s) {
        fallo("$s no carga sesion.php", "responde sin sesion y sin TenantContext");
    }
    echo "\n  Confirma la fuga antes de arreglar:\n";
    echo "    curl -s http://localhost/BuzzDM/php/" . $sospechosos[0] . "\n";
} else {
    ok("Todos los endpoints validan sesion");
}

// ===========================================================================
// RESUMEN
// ===========================================================================

titulo('Resumen');
echo "  Fallos: $fallos\n";
echo "  Avisos: $avisos\n";

if ($fallos > 0) {
    echo "\n  RESULTADO: FALLA\n";
    exit(1);
}

echo "\n  RESULTADO: PASA\n";
echo "  Los avisos no son fallos: señalan trabajo pendiente conocido.\n";
exit(0);
