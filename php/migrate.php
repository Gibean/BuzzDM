<?php
/**
 * Migrate - Runner de migraciones SQL para BuzzDM
 *
 * Uso:
 *   php php/migrate.php              -> aplica todas las migraciones pendientes
 *   php php/migrate.php --status     -> lista migraciones aplicadas y pendientes
 *   php php/migrate.php --only=NAME  -> aplica sólo los archivos que contengan NAME
 *
 * Cada migración se ejecuta dentro de su propia transacción y se registra en
 * la tabla `schema_migrations`. Las migraciones de este repositorio no son
 * idempotentes (usan ALTER TABLE ADD COLUMN), por lo que el registro es
 * obligatorio: reejecutarlas produciría errores de "duplicate column".
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("Este script solo se ejecuta por consola.\n");
}

require_once __DIR__ . '/db.php';

const MIGRATIONS_DIR = __DIR__ . '/../db/migrations';

/**
 * Divide un archivo SQL en sentencias individuales, respetando cadenas,
 * identificadores entre acentos graves y comentarios.
 */
function splitSqlStatements(string $sql): array
{
    $statements = [];

    // Fase 1: reparto por DELIMITER.
    //
    // El cuerpo de un trigger (BEGIN ... END) contiene ';' internos, así que
    // partir por ';' lo destroza en sentencias sueltas que MySQL rechaza. El
    // convenio de mysql-cli es declarar DELIMITER antes y después, y el runner
    // tenía que ignorarlo: por eso las dos migraciones con trigger que hay en
    // el repositorio se aplicaron a mano y el runner solo las auto-registró.
    // Sin esto no hay forma de corregir un trigger por migración.
    $delimiter = ';';
    $buffer = '';

    foreach (preg_split('/\R/', $sql) as $linea) {
        if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $linea, $m)) {
            // Lo que precede al DELIMITER ya es una región completa.
            $statements = array_merge($statements, splitByDelimiter($buffer, $delimiter));
            $buffer = '';
            $delimiter = $m[1];
            continue;
        }
        $buffer .= $linea . "\n";
    }

    $statements = array_merge($statements, splitByDelimiter($buffer, $delimiter));

    return array_values(array_filter(array_map('trim', $statements), static fn($s) => $s !== ''));
}

/**
 * Parte un fragmento por el delimitador indicado, respetando cadenas,
 * identificadores entre acentos graves y comentarios.
 */
function splitByDelimiter(string $sql, string $delimiter): array
{
    $statements = [];
    $current = '';
    $len = strlen($sql);
    $dl = strlen($delimiter);
    $inSingle = $inDouble = $inBacktick = $inLineComment = $inBlockComment = false;

    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';

        if ($inLineComment) {
            if ($ch === "\n") {
                $inLineComment = false;
                $current .= $ch;
            }
            continue;
        }

        if ($inBlockComment) {
            if ($ch === '*' && $next === '/') {
                $inBlockComment = false;
                $i++;
            }
            continue;
        }

        if (!$inSingle && !$inDouble && !$inBacktick) {
            if (($ch === '-' && $next === '-') || $ch === '#') {
                $inLineComment = true;
                continue;
            }
            if ($ch === '/' && $next === '*') {
                $inBlockComment = true;
                $i++;
                continue;
            }
        }

        if ($ch === "'" && !$inDouble && !$inBacktick) {
            // Comilla simple: se escapa duplicándola ('')
            if ($inSingle && $next === "'") {
                $current .= "''";
                $i++;
                continue;
            }
            $inSingle = !$inSingle;
        } elseif ($ch === '"' && !$inSingle && !$inBacktick) {
            $inDouble = !$inDouble;
        } elseif ($ch === '`' && !$inSingle && !$inDouble) {
            $inBacktick = !$inBacktick;
        } elseif ($ch === $delimiter[0] && substr($sql, $i, $dl) === $delimiter
                  && !$inSingle && !$inDouble && !$inBacktick) {
            $trimmed = trim($current);
            if ($trimmed !== '') {
                $statements[] = $trimmed;
            }
            $current = '';
            $i += $dl - 1;
            continue;
        }

        $current .= $ch;
    }

    $trimmed = trim($current);
    if ($trimmed !== '') {
        $statements[] = $trimmed;
    }

    return $statements;
}

/**
 * Migraciones anteriores a la existencia de este runner, que se aplicaron a
 * mano sobre el esquema. Se registran automáticamente la primera vez que se
 * crea la tabla schema_migrations, para que el runner no intente volver a
 * ejecutarlas (no son idempotentes: fallarían con "duplicate column").
 *
 * Se comprobó una por una contra el esquema real antes de incluirlas.
 */
const LEGACY_BASELINE = [
    'add_2fa_columns.sql',
    'almacen_codigo_auto.sql',
    'email_verification_setup.sql',
    'fix_cargos_existencias_columns.sql',
    'modulo_requisiciones.sql',
    'optimize_sesiones_api.sql',
    'permisos_transferencias.sql',
    'precios_proveedor.sql',
    'saas_multitenant_setup.sql',
    'transferencias_almacen.sql',
    // Superada por el backfill con empresa_id de saas_integrity_002.sql.
    'asignar_stock_almacen.sql',
];

function ensureMigrationsTable(PDO $pdo): bool
{
    $existed = (bool)$pdo->query("SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'schema_migrations'")->fetchColumn();

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `schema_migrations` (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `migration`  VARCHAR(190) NOT NULL,
            `checksum`   CHAR(64) NOT NULL,
            `ejecutada`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_schema_migration` (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    if ($existed) {
        return false;
    }

    $stmt = $pdo->prepare("INSERT IGNORE INTO schema_migrations (migration, checksum) VALUES (?, ?)");
    foreach (LEGACY_BASELINE as $file) {
        $path = MIGRATIONS_DIR . '/' . $file;
        $stmt->execute([$file, is_file($path) ? hash_file('sha256', $path) : str_repeat('0', 64)]);
    }

    return true;
}

function run(): int
{
    $options = getopt('', ['status', 'only::', 'force']);
    $showStatus = isset($options['status']);
    $only = $options['only'] ?? null;
    $force = isset($options['force']);

    try {
        $pdo = getDbConnection();
    } catch (Throwable $e) {
        fwrite(STDERR, "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n");
        return 1;
    }

    if (ensureMigrationsTable($pdo)) {
        echo "Baseline registrado: " . count(LEGACY_BASELINE) . " migracion(es) legacy ya aplicadas al esquema." . PHP_EOL . PHP_EOL;
    }

    $files = glob(MIGRATIONS_DIR . '/*.sql');
    sort($files, SORT_STRING);

    if (!$files) {
        echo "No se encontraron migraciones en " . realpath(MIGRATIONS_DIR) . "\n";
        return 0;
    }

    $applied = [];
    foreach ($pdo->query("SELECT migration, checksum FROM schema_migrations")->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $applied[$row['migration']] = $row['checksum'];
    }

    if ($showStatus) {
        echo str_pad("MIGRACION", 46) . "ESTADO" . PHP_EOL;
        echo str_repeat('-', 70) . PHP_EOL;
        foreach ($files as $file) {
            $name = basename($file);
            echo str_pad($name, 46) . (isset($applied[$name]) ? "[aplicada]" : "[PENDIENTE]") . PHP_EOL;
        }
        echo PHP_EOL . "Total: " . count($files) . " archivo(s), " . count($applied) . " aplicada(s)." . PHP_EOL;
        return 0;
    }

    $executed = 0;
    $skipped = 0;
    $failed = 0;

    foreach ($files as $file) {
        $name = basename($file);
        $checksum = hash_file('sha256', $file);

        if ($only !== null && stripos($name, $only) === false) {
            continue;
        }

        if (isset($applied[$name]) && !$force) {
            if ($applied[$name] !== $checksum) {
                echo "[AVISO] $name fue modificada despues de aplicarse (checksum distinto)." . PHP_EOL;
            }
            $skipped++;
            continue;
        }

        $sql = file_get_contents($file);
        if ($sql === false) {
            fwrite(STDERR, "[ERROR] No se pudo leer $name" . PHP_EOL);
            $failed++;
            continue;
        }

        $statements = splitSqlStatements($sql);
        echo "[APLICANDO] $name (" . count($statements) . " sentencias)" . PHP_EOL;

        // MySQL no soporta transacciones DDL: cada ALTER/CREATE/DROP provoca un
        // COMMIT implícito y PDO pierde la traza de la transacción. Envolver una
        // migración con DDL en begin/commit produce errores engañosos y da una
        // falsa sensación de atomicidad.
        //
        // Estrategia: si la migración contiene DDL se ejecuta sin transacción
        // (es lo único correcto); si es sólo DML, se ejecuta atómicamente.
        $hasDdl = false;
        foreach ($statements as $s) {
            if (preg_match('/^\s*(ALTER|CREATE|DROP|RENAME|TRUNCATE)\b/i', $s)) {
                $hasDdl = true;
                break;
            }
        }

        $useTransaction = !$hasDdl;
        if ($useTransaction) {
            $pdo->beginTransaction();
        }

        try {
            foreach ($statements as $stmt) {
                // PDO::exec() rechaza sentencias que devuelven juego de resultados.
                // Algunas migraciones legacy incluyen SELECT de verificación.
                if (preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i', $stmt)) {
                    $pdo->query($stmt);
                } else {
                    $pdo->exec($stmt);
                }
            }
            $pdo->prepare("INSERT INTO schema_migrations (migration, checksum) VALUES (?, ?)")
                ->execute([$name, $checksum]);
            if ($useTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }
            echo "[OK]        $name" . ($hasDdl ? " (sin transaccion: contiene DDL)" : " (transaccional)") . PHP_EOL;
            $executed++;
        } catch (Throwable $e) {
            if ($useTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            fwrite(STDERR, "[ERROR]    $name -> " . $e->getMessage() . PHP_EOL);
            if ($hasDdl) {
                fwrite(STDERR, "          Esta migracion contiene DDL: las sentencias ya aplicadas NO" . PHP_EOL);
                fwrite(STDERR, "          se revierten y no quedo registrada en schema_migrations." . PHP_EOL);
                fwrite(STDERR, "          Restaura el respaldo previo a reintentar. Deteniendo ejecucion." . PHP_EOL);
            } else {
                fwrite(STDERR, "          Transaccion revertida. Ningun cambio fue aplicado." . PHP_EOL);
            }
            $failed++;
            break;
        }
    }

    echo PHP_EOL . "Resumen: $executed aplicada(s), $skipped omitida(s), $failed error(es)." . PHP_EOL;
    return $failed > 0 ? 1 : 0;
}

exit(run());
