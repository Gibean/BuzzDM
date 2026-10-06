<?php
/**
 * test_company_types.php
 *
 * El sistema distingue dos tipos de empresa y las reglas son distintas:
 *
 *   - Demo (is_demo = 1): 15 días, huella digital para impedir abusar del
 *     trial, verificación de correo antes de activarse.
 *   - Empresa real (is_demo = 0): sin caducidad, sin huella digital, usable
 *     de inmediato.
 *
 * Estas pruebas existen porque esa distinción se puede perder sin que se note.
 * Hasta hace poco, createTrialEnvironment() fijaba is_demo = 1 en el INSERT, así
 * que no había ninguna forma de crear una empresa que no fuera demo.
 * verifyEmailCode(), además, marcaba cualquier sesión como demo sin mirar la
 * base, así que al verificar el correo una empresa real se convertía en demo y
 * le ponía una caducidad de 15 días que no le correspondía.
 *
 * Uso: php tests/test_company_types.php
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';
require_once __DIR__ . '/../php/src/Service/DeviceFingerprintService.php';
require_once __DIR__ . '/../php/src/Service/CompanyService.php';

$passed = 0;
$failed = 0;
$pdo = \Database::getConnection();

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

/**
 * Toda empresa que crea esta prueba se borra al terminar, incluso si el proceso
 * muere a mitad. Mismo motivo que en _tenant_fixture.php: un fallo real no debe
 * dejar empresas de prueba en la base.
 */
$empresasCreadas = [];
register_shutdown_function(static function () use ($pdo, &$empresasCreadas): void {
    foreach ($empresasCreadas as $id) {
        try {
            $pdo->prepare('DELETE FROM empresa WHERE id = ?')->execute([$id]);
        } catch (Throwable $e) {
            // No enmascarar el resultado del test con un fallo de limpieza.
        }
    }
});

$service = new CompanyService($pdo);
$fingerprintService = new DeviceFingerprintService($pdo);
$sufijo = getmypid() . substr((string)time(), -4);

echo "[1] Alta de empresa REAL\n";

$emailReal = 'real' . $sufijo . '@ejemplo.com';
$rifReal = 'J-' . str_pad((string)random_int(10000000, 99999999), 9, '0', STR_PAD_LEFT);

try {
    $real = $service->createCompany([
        'company_name' => 'Empresa Real de Prueba',
        'admin_email' => $emailReal,
        'admin_password' => 'ClaveSegura123',
        'admin_name' => 'Administrador',
        'rif' => $rifReal,
    ], true);

    $empresaRealId = (int)$real['company_id'];
    $empresasCreadas[] = $empresaRealId;

    check('createCompany() devuelve company_id', $empresaRealId > 0);
    check('no exige verificación de correo', $real['requires_verification'] === false);
    check('is_demo = false en la respuesta', $real['is_demo'] === false);
    check('expires_at = null (sin caducidad)', $real['expires_at'] === null, var_export($real['expires_at'], true));
    check('days_remaining = null', $real['days_remaining'] === null);

    $fila = $pdo->query("SELECT is_demo, trial_ends_at, device_fingerprint, status, rif FROM empresa WHERE id = $empresaRealId")->fetch(PDO::FETCH_ASSOC);

    check('BD: is_demo = 0', (int)$fila['is_demo'] === 0, json_encode($fila));
    check('BD: trial_ends_at IS NULL', $fila['trial_ends_at'] === null, json_encode($fila));
    check('BD: device_fingerprint IS NULL', $fila['device_fingerprint'] === null, json_encode($fila));
    check('BD: status = active, usable de inmediato', $fila['status'] === 'active', (string)$fila['status']);
    check('BD: conserva el RIF declarado, no uno inventado', $fila['rif'] === $rifReal, (string)$fila['rif']);

    $admin = $pdo->query("SELECT email, rol FROM usuarios WHERE empresa_id = $empresaRealId")->fetch(PDO::FETCH_ASSOC);
    check('crea su propio administrador', ($admin['rol'] ?? '') === 'admin', json_encode($admin));

    // Una empresa nueva sin catálogo base no puede emitir una factura: el nombre
    // de la tienda saldría en blanco y la moneda sería indeterminada.
    foreach (['categorias', 'almacenes', 'configuraciones', 'colores', 'materiales'] as $tabla) {
        $cuantas = (int)$pdo->query("SELECT COUNT(*) FROM `$tabla` WHERE empresa_id = $empresaRealId")->fetchColumn();
        check("catálogo base: $tabla tiene filas propias", $cuantas > 0, "n=$cuantas");
    }
} catch (Throwable $e) {
    check('createCompany() se ejecuta sin error', false, get_class($e) . ': ' . $e->getMessage());
}

echo "\n[2] La huella digital solo frena a las demos\n";

check(
    'una huella nueva no está bloqueada',
    $fingerprintService->isDeviceOrEmailBlocked('huella-inexistente-' . $sufijo, 'nuevo@ejemplo.com') === false
);
check(
    'una empresa real no bloquea su correo',
    $fingerprintService->isDeviceOrEmailBlocked('huella-inexistente-' . $sufijo, $emailReal) === false
);

echo "\n[3] El alta de DEMO mantiene sus reglas\n";

$emailDemo = 'demo' . $sufijo . '@ejemplo.com';

try {
    $demo = $service->createTrialEnvironment([
        'company_name' => 'Demo de Comprobación',
        'admin_email' => $emailDemo,
        'admin_password' => 'ClaveSegura123',
        'device_fingerprint' => 'huella-de-prueba-' . $sufijo,
    ]);

    $empresaDemoId = (int)$demo['company_id'];
    $empresasCreadas[] = $empresaDemoId;

    check('demo: exige verificación de correo', $demo['requires_verification'] === true);
    check('demo: days_remaining = 15', (int)$demo['days_remaining'] === 15);

    $filaDemo = $pdo->query("SELECT is_demo, trial_ends_at, status, device_fingerprint FROM empresa WHERE id = $empresaDemoId")->fetch(PDO::FETCH_ASSOC);

    check('demo: is_demo = 1', (int)$filaDemo['is_demo'] === 1, json_encode($filaDemo));
    check('demo: status = pending_verification', $filaDemo['status'] === 'pending_verification', (string)$filaDemo['status']);
    check('demo: trial_ends_at está en el futuro', strtotime((string)$filaDemo['trial_ends_at']) > time());

    // La huella no es el valor que envía el navegador: se combina con la IP y el
    // user agent, para que borrar localStorage no baste para saltarse el límite
    // de una demo por navegador.
    check(
        'demo: la huella se deriva de IP y user agent, no del cliente',
        $filaDemo['device_fingerprint'] !== null
            && $filaDemo['device_fingerprint'] !== 'huella-de-prueba-' . $sufijo,
        (string)$filaDemo['device_fingerprint']
    );

    check(
        'demo: su propia huella y correo quedan bloqueados para una segunda demo',
        $fingerprintService->isDeviceOrEmailBlocked((string)$filaDemo['device_fingerprint'], $emailDemo) === true
    );
} catch (Throwable $e) {
    check('createTrialEnvironment() se ejecuta sin error', false, get_class($e) . ': ' . $e->getMessage());
}

echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";
echo "Las empresas de prueba se eliminan al terminar.\n";

exit($failed > 0 ? 1 : 0);