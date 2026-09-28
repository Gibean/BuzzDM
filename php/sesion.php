<?php
// Inicia la sesión en cada página que lo requiera

require_once __DIR__ . '/../vendor/autoload.php';

use function PHPSTORM_META\map;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// Session Inactivity Timeout (24 hours)
// Edge Case: Prevents stale sessions from persisting indefinitely
// ========================================
define('SESSION_TIMEOUT_SECONDS', 24 * 60 * 60); // 24 hours

if (isset($_SESSION['last_activity'])) {
    $inactiveTime = time() - $_SESSION['last_activity'];

    if ($inactiveTime > SESSION_TIMEOUT_SECONDS) {
        // Session expired due to inactivity
        $expiredUserId = $_SESSION['usuario_id'] ?? null;

        // Destroy the session
        session_unset();
        session_destroy();
        session_start(); // Restart fresh session for redirects

        // Log the automatic logout (if we had a user)
        if ($expiredUserId) {
            // Note: Can't use LogController here as it may not be autoloaded yet
            // This will be logged via the frontend when redirected to login
            $_SESSION['session_expired'] = true;
            $_SESSION['expired_user_id'] = $expiredUserId;
        }

        // Will be handled below in the session check
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Get CSRF token for forms
 * @return string CSRF token
 */
function getCsrfToken(): string {
    return $_SESSION['csrf_token'] ?? '';
}

/**
 * Validate CSRF token
 * @param string $token Token from form/request
 * @return bool Whether token is valid
 */
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field HTML
 * @return string Hidden input HTML
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(getCsrfToken()) . '">';
}

// List of API scripts that should return JSON on auth/access failure
$api_scripts = [
    'api_sales_chart.php',
    'api_clientes.php', // Standardized API
    'api_products.php', // Standardized API
    'api_orders.php', // Standardized API
    'api_warehouses.php', // Standardized API
    'api_import.php', // Standardized API
    'api_cargos.php', // Standardized API
    'api_notes.php', // Standardized API
    'api_suppliers.php', // Standardized API
    'api_settings.php', // Standardized API
    'api_backups.php', // Standardized API
    'api_users.php', // Standardized API
    'api_catalogs.php', // Standardized API
    'api_logs.php', // Standardized API - Logs
    'api_auth.php', // Standardized API - Authentication
    'api_shipments.php', // Standardized API - Shipments
    'api_locations.php', // Standardized API - Locations
    'api_kits.php', // Standardized API - Kit Components
    // Notificaciones y estadísticas
    'api_notifications.php',
    'api_stats.php',
    'api_2fa.php', // Two-Factor Authentication
    'api_requisiciones.php' // Requisiciones de Compra
];

$current_page = basename($_SERVER['PHP_SELF']);
$is_api_call = in_array($current_page, $api_scripts);

function json_auth_error($message) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

/**
 * Check if request has valid dev token (only for development mode)
 * This allows Swagger UI and other API testing tools to bypass session auth
 * @return bool Whether dev token is valid
 */
function checkDevToken() {
    // Load .env if not already loaded
    if (!getenv('APP_ENV')) {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->safeLoad();
    }

    // Only allow dev token in development mode
    if (getenv('APP_ENV') !== 'development') {
        return false;
    }

    $devToken = getenv('DEV_API_TOKEN');
    if (empty($devToken)) {
        return false;
    }

    // Check X-Dev-Token header
    $headerToken = $_SERVER['HTTP_X_DEV_TOKEN'] ?? '';
    if (!empty($headerToken) && hash_equals($devToken, $headerToken)) {
        return true;
    }

    return false;
}

// Si no existe la variable de sesión del ID de usuario, verificar dev token como alternativa
if (!isset($_SESSION['usuario_id'])) {
    // Check if dev token is provided (for API testing)
    if ($is_api_call && checkDevToken()) {
        // Dev token valid - set a fake admin session for API testing
        $_SESSION['usuario_id'] = 1; // Assume admin user ID 1
        $_SESSION['usuario_nombre'] = 'API Dev User';
        $_SESSION['usuario_rol'] = 'admin';
        $_SESSION['is_dev_token_session'] = true;
    } else {
        if ($is_api_call) {
            json_auth_error('No autenticado. Por favor, inicia sesión.');
        } else {
            header('Location: login');
            exit();
        }
    }
}

// Conexión a la base de datos
require_once __DIR__ . '/db.php'; // This now provides the getDbConnection() function
$pdo = getDbConnection(); // Call the function to get the PDO object

// Verificar si el usuario está logeado y obtener su rol
if (isset($_SESSION['usuario_id'])) {
    $usuarioId = $_SESSION['usuario_id'];

    // Consultar el rol, nombre y empresa_id del usuario
    $query = "SELECT u.nombre, u.rol, COALESCE(u.empresa_id, 1) as empresa_id,
                     e.nombre as empresa_nombre, e.is_demo, e.status as empresa_status, e.trial_ends_at,
                     TIMESTAMPDIFF(DAY, NOW(), e.trial_ends_at) as demo_dias_restantes
              FROM usuarios u
              LEFT JOIN empresa e ON e.id = COALESCE(u.empresa_id, 1)
              WHERE u.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(1, $usuarioId, PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['empresa_id'] = (int)$usuario['empresa_id'];
        $_SESSION['empresa_nombre'] = $usuario['empresa_nombre'] ?? 'Mi Empresa';
        $_SESSION['is_demo'] = (bool)($usuario['is_demo'] ?? false);
        $_SESSION['demo_dias_restantes'] = max(0, (int)($usuario['demo_dias_restantes'] ?? 0));

        // --- Middleware de Control de Expiración SaaS Demo ---
        if ($_SESSION['is_demo'] && !empty($usuario['trial_ends_at'])) {
            $trialExpires = new DateTime($usuario['trial_ends_at']);
            $ahora = new DateTime();

            if ($ahora > $trialExpires || $usuario['empresa_status'] === 'expired') {
                $_SESSION['demo_expired'] = true;
                if ($is_api_call) {
                    header('Content-Type: application/json');
                    http_response_code(403);
                    echo json_encode([
                        'success' => false, 
                        'code' => 'TRIAL_EXPIRED',
                        'message' => 'El período de prueba de 15 días ha finalizado. Por favor exporta tus datos.'
                    ]);
                    exit;
                } elseif ($current_page !== 'demo_expired.php' && $current_page !== 'logout.php') {
                    header('Location: demo_expired.php');
                    exit;
                }
            }
        }
    } else {
        // Si el usuario no se encuentra en la DB, destruir sesión y redirigir
        session_unset();
        session_destroy();
        if ($is_api_call) {
            json_auth_error('Usuario no encontrado. Sesión inválida.');
        } else {
            header('Location: login');
            exit();
        }
    }


// --- Lógica de Control de Acceso Basada en Roles ---

/**
 * Get permissions from database for a specific role
 * Falls back to static array if database table is empty
 *
 * @param PDO $pdo Database connection
 * @param string $rol User role
 * @return array List of allowed page names
 */
function getPermisosFromDB($pdo, $rol) {
    try {
        $stmt = $pdo->prepare("SELECT pagina FROM permisos_rol WHERE rol = ?");
        $stmt->execute([$rol]);
        $permisos = array_column($stmt->fetchAll(), 'pagina');
        return $permisos;
    } catch (PDOException $e) {
        // Table might not exist yet, return empty to trigger fallback
        error_log('Error fetching permissions: ' . $e->getMessage());
        return [];
    }
}

// Obtener el rol del usuario de la sesión
$user_role = $_SESSION['usuario_rol'] ?? 'guest';

// Try to get permissions from database first
$db_permissions = getPermisosFromDB($pdo, $user_role);

// Fallback: Static array for when database is not yet migrated
$fallback_pages = [
    'admin' => [
        'dashboard.php', 'pedidos.php', 'envios.php', 'inventario.php', 'importacion.php',
        'admin.php', 'about.php', 'manual.php', 'login.php', 'logout.php',
        'api_sales_chart.php', 'create_admin.php', 'get_import_history.php',
        'users.php', 'sesion.php', 'db.php', 'sidebar.php', 'reports.php', 'api_orders.php',
        'search_api.php', 'clientes.php', 'api_clientes.php', 'api_products.php', 'api_sales_by_status_chart.php',
        'backup_status.php', 'api_logs.php',
        'cargos_existencias.php', 'almacenes.php', 'transferencias.php',
        'proveedores.php', 'todo.php', 'notas_actions.php', 'catalogos.php',
        'api_notifications.php', 'api_transferencias.php', 'api_stats.php', 'api_warehouses.php', 'api_import.php', 'api_notes.php', 'api_suppliers.php',
        'api_settings.php', 'api_backups.php', 'api_users.php', 'api_catalogs.php', 'api_shipments.php', 'api_locations.php', 'api_kits.php', 'api_cargos.php',
        'api_2fa.php',
        'requisiciones.php', 'api_requisiciones.php',
        'ajustes.php'
    ],
    'supervisor' => [
        'dashboard.php', 'pedidos.php', 'envios.php', 'inventario.php', 'importacion.php',
        'about.php', 'manual.php', 'login.php', 'logout.php', 'api_sales_chart.php',
        'api_products.php', 'sesion.php', 'db.php',
        'sidebar.php', 'reports.php', 'api_orders.php', 'search_api.php', 'clientes.php',
        'api_clientes.php', 'api_sales_by_status_chart.php', 'envios_actions.php', 'transferencias.php',
        'todo.php', 'notas_actions.php', 'cargos_existencias.php', 'almacenes.php', 'proveedores.php', 'catalogos.php',
        'api_notifications.php', 'api_stats.php', 'api_warehouses.php', 'api_import.php', 'api_notes.php', 'api_suppliers.php', 'api_catalogs.php', 'api_shipments.php', 'api_locations.php', 'api_kits.php', 'api_transferencias.php', 'api_cargos.php',
        'requisiciones.php', 'api_requisiciones.php',
        'ajustes.php'
    ],
    'operador' => [
        'dashboard.php', 'pedidos.php', 'envios.php', 'about.php', 'manual.php', 'login.php',
        'logout.php', 'api_sales_chart.php', 'sesion.php', 'db.php', 'sidebar.php',
        'api_orders.php', 'api_sales_by_status_chart.php',
        'envios_actions.php', 'todo.php', 'notas_actions.php',
        'api_notifications.php', 'api_stats.php', 'api_warehouses.php', 'api_notes.php', 'api_shipments.php',
        'requisiciones.php', 'api_requisiciones.php',
        'ajustes.php'
    ]
];

// Use database permissions if available, MERGED with fallback to prevent gaps
// (If DB table exists but doesn't have all pages, we don't want to block access)
if (!empty($db_permissions)) {
    // Start with fallback as base, then add any DB-specific permissions
    $allowed_pages = $fallback_pages;
    if (isset($allowed_pages[$user_role])) {
        $allowed_pages[$user_role] = array_unique(array_merge($allowed_pages[$user_role], $db_permissions));
    } else {
        $allowed_pages[$user_role] = $db_permissions;
    }
} else {
    $allowed_pages = $fallback_pages;
}

// HOTFIX: Ensure api_logs.php is allowed for admin even if not in DB yet (failed migration protection)
if ($user_role === 'admin' && isset($allowed_pages['admin']) && !in_array('api_logs.php', $allowed_pages['admin'])) {
    $allowed_pages['admin'][] = 'api_logs.php';
}

// HOTFIX: Ensure requisiciones is allowed for all roles until migration is run
$requisicion_pages = ['requisiciones.php', 'api_requisiciones.php'];
if (isset($allowed_pages[$user_role])) {
    foreach ($requisicion_pages as $page) {
        if (!in_array($page, $allowed_pages[$user_role])) {
            $allowed_pages[$user_role][] = $page;
        }
    }
}

// HOTFIX: Ensure reports.php is allowed for admin and supervisor even if not in DB yet
if (in_array($user_role, ['admin', 'supervisor']) && isset($allowed_pages[$user_role]) && !in_array('reports.php', $allowed_pages[$user_role])) {
    $allowed_pages[$user_role][] = 'reports.php';
}

// Always-allowed APIs for any authenticated user (core system functionality)
$always_allowed_apis = [
    'api_notifications.php',
    'api_stats.php',
    'search_api.php',
    'sidebar.php',
    'api_2fa.php', // 2FA is available for all authenticated users
    'api_tasa.php'
];

// Merge always-allowed APIs into current user's permissions
if (isset($allowed_pages[$user_role])) {
    $allowed_pages[$user_role] = array_merge($allowed_pages[$user_role], $always_allowed_apis);
} else {
    $allowed_pages[$user_role] = $always_allowed_apis;
}

// Verificar si la página actual está permitida para el rol del usuario
if (!isset($allowed_pages[$user_role]) || !in_array($current_page, $allowed_pages[$user_role])) {
    if ($is_api_call) {
        json_auth_error('Acceso denegado. No tienes permisos para esta acción.');
    } else if ($user_role === 'admin' || $user_role === 'supervisor' || $user_role === 'operador') {
        // Fix relative path redirect issues cleanly
        $redirectPath = ($current_page === 'manual.php' || !str_contains($_SERVER['PHP_SELF'], '/php/')) ? 'dashboard' : '../dashboard';
        header("Location: $redirectPath?error=access_denied&code=403");
    } else {
        $redirectPath = ($current_page === 'manual.php' || !str_contains($_SERVER['PHP_SELF'], '/php/')) ? 'login' : '../login';
        header("Location: $redirectPath?error=auth_required&code=401");
    }
    exit();
}

// Asegurarse de que los usuarios "supervisor" y "operador" no puedan acceder a admin.php directamente
if ($current_page === 'admin.php' && ($user_role === 'supervisor' || $user_role === 'operador')) {
    if ($is_api_call) {
        json_auth_error('Acceso denegado a la administración.');
    } else {
        header('Location: dashboard');
        exit();
    }
}

// Asegurarse de que los usuarios "operador" no puedan acceder a inventario.php o importacion.php
if (($current_page === 'inventario.php' || $current_page === 'importacion.php') && $user_role === 'operador') {
    if ($is_api_call) {
        json_auth_error('Acceso denegado al inventario/importación.');
    } else {
        header('Location: dashboard');
        exit();
    }
}

// --- Automatic Backup Check (Poor Man's Cron) ---
// Only run on page requests, not on API calls
if (!$is_api_call && file_exists(__DIR__ . '/backup_check.php')) {
    require_once __DIR__ . '/backup_check.php';
    checkAndRunAutoBackup();
    checkAndRunBcvSync();
}

?>
