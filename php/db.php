<?php
date_default_timezone_set('America/Caracas');
/**
 * BuzzDM - Database Connection Manager
 *
 * Implements singleton pattern for efficient database connection handling.
 * Supports .env configuration file for credentials.
 */

// Load Composer autoloader (required for phpdotenv)
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

// Load .env configuration if available
// Strategy: load .env first (production defaults), then .env.local overrides (local dev)
$dotenv_path = __DIR__ . '/../';
if (class_exists('Dotenv\\Dotenv')) {
    if (file_exists($dotenv_path . '.env')) {
        $dotenv = Dotenv\Dotenv::createMutable($dotenv_path, '.env');
        $dotenv->safeLoad();
    }
    if (file_exists($dotenv_path . '.env.local')) {
        $dotenvLocal = Dotenv\Dotenv::createMutable($dotenv_path, '.env.local');
        $dotenvLocal->safeLoad();
    }
}


/**
 * Database singleton class
 * Ensures only one database connection is created per request
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Get the database connection instance
     * Creates connection on first call, returns existing on subsequent calls
     *
     * @return PDO The database connection
     * @throws PDOException On connection failure
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            // Read from environment variables with fallbacks
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'dbuzz';
            $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
            $password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

            try {
                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
                self::$instance = new PDO($dsn, $user, $password);

                // Set PDO attributes for optimal behavior
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw new PDOException(
                    'Error de conexión a la base de datos. Por favor, inténtelo de nuevo más tarde.',
                    (int)$e->getCode()
                );
            }
        }
        return self::$instance;
    }

    /**
     * Reset the connection (useful for testing or reconnection)
     */
    public static function resetConnection(): void {
        self::$instance = null;
    }

    /**
     * Check if we're in debug mode
     */
    public static function isDebugMode(): bool {
        $debug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false';
        return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
    }
}

/**
 * Legacy function for backward compatibility
 * Existing code uses getDbConnection() - this wraps the singleton
 *
 * @return PDO The database connection
 */
function getDbConnection(): PDO {
    return Database::getConnection();
}

/**
 * Fetches company data from the 'empresa' table.
 * Assumes there's only one company record (id=1).
 * @return array|null Company data or null if not found.
 */
function getCompanyData() {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT rif, nombre, direccion, telefono, email, logo_svg FROM empresa WHERE id = 1 LIMIT 1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Updates company data in the 'empresa' table.
 * Inserts if no record exists (id=1), otherwise updates.
 * @param string $rif
 * @param string $nombre
 * @param string $direccion
 * @param string $telefono
 * @param string $email
 * @param string|null $logo_filename
 * @return bool True on success, false on failure.
 */
function updateCompanyData($rif, $nombre, $direccion, $telefono, $email, $logo_filename = null) {
    $pdo = Database::getConnection();

    $sql = "INSERT INTO empresa (id, rif, nombre, direccion, telefono, email, logo_svg, actualizado_at)
            VALUES (1, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                rif = VALUES(rif),
                nombre = VALUES(nombre),
                direccion = VALUES(direccion),
                telefono = VALUES(telefono),
                email = VALUES(email),
                actualizado_at = NOW()";

    $params = [$rif, $nombre, $direccion, $telefono, $email, $logo_filename];

    if ($logo_filename !== null) {
        $sql .= ", logo_svg = VALUES(logo_svg)";
    }

    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

/**
 * Get today's exchange rate from the database
 * Falls back to the most recent rate if today's isn't set
 *
 * @return array|null Rate data with 'tasa', 'fecha', 'fuente' or null
 */
function getTasaCambioHoy() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT tasa_actual, fecha_tasa, fuente FROM v_tasa_hoy");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return null if no rate is configured
    if ($result && $result['fuente'] === 'no_configurada') {
        return null;
    }
    return $result;
}

/**
 * Get all available exchange rates for today
 *
 * @return array Array of rates indexed by source
 */
function getAllTasasHoy() {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SELECT fuente, tasa, fecha, creado_at FROM tasas_cambio WHERE fecha = CURDATE()");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $indexed = [];
    foreach ($results as $row) {
        $indexed[$row['fuente']] = $row;
    }

    // If empty for today, fallback to most recent date but keep all sources from that date
    if (empty($indexed)) {
        $stmt = $pdo->query("SELECT MAX(fecha) FROM tasas_cambio");
        $maxFecha = $stmt->fetchColumn();
        if ($maxFecha) {
            $stmt = $pdo->prepare("SELECT fuente, tasa, fecha, creado_at FROM tasas_cambio WHERE fecha = ?");
            $stmt->execute([$maxFecha]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $indexed[$row['fuente']] = $row;
            }
        }
    }

    return $indexed;
}

/**
 * Set today's exchange rate
 *
 * @param float $tasa The USD rate (how many VES per 1 USD)
 * @param string $fuente Source of the rate (manual, bcv, etc.)
 * @param int|null $usuario_id User who set the rate
 * @return bool Success
 */
function setTasaCambioHoy($tasa, $fuente = 'manual', $usuario_id = null) {
    $pdo = Database::getConnection();

    $sql = "INSERT INTO tasas_cambio (tasa, fuente, fecha, usuario_id)
            VALUES (?, ?, CURDATE(), ?)
            ON DUPLICATE KEY UPDATE
                tasa = VALUES(tasa),
                fuente = VALUES(fuente),
                usuario_id = VALUES(usuario_id)";

    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$tasa, $fuente, $usuario_id]);
}

?>
