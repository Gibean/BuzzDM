<?php
// Inicia la sesión en cada página que lo requiera

use function PHPSTORM_META\map;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
    // Legacy Actions

    'settings_actions.php',
    'logs.php',
    'users.php',
    'backups.php',
    'backup_status.php',
    'proveedores_search.php',
    // Phase 1.7 - Product types and kits
    'catalogos_actions.php',
    'kit_actions.php',
    // Phase 2 - Multi-warehouse
    'almacenes_actions.php',
    'ubicaciones_actions.php',
    // Proveedores
    'proveedores_actions.php',
    // Notificaciones y estadísticas
    'api_notifications.php',
    'api_stats.php'
];

$current_page = basename($_SERVER['PHP_SELF']);
$is_api_call = in_array($current_page, $api_scripts);

function json_auth_error($message) {
    header('Content-Type: application/json');
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Si no existe la variable de sesión del ID de usuario, significa que no ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    if ($is_api_call) {
        json_auth_error('No autenticado. Por favor, inicia sesión.');
    } else {
        header('Location: login.php');
        exit();
    }
}

// Conexión a la base de datos
require_once __DIR__ . '/db.php'; // This now provides the getDbConnection() function
$pdo = getDbConnection(); // Call the function to get the PDO object

// Verificar si el usuario está logeado y obtener su rol
if (isset($_SESSION['usuario_id'])) {
    $usuarioId = $_SESSION['usuario_id'];

    // Consultar el rol y nombre del usuario
    $query = "SELECT nombre, rol FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($query); // Use $pdo
    $stmt->bindParam(1, $usuarioId, PDO::PARAM_INT); // Use bindParam
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch directly

    if ($usuario) { // Check if user was found
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
    } else {
        // Si el usuario no se encuentra en la DB, destruir sesión y redirigir
        session_unset();
        session_destroy();
        if ($is_api_call) {
            json_auth_error('Usuario no encontrado. Sesión inválida.');
        } else {
            header('Location: login.php');
            exit();
        }
    }

    // No need for $stmt->close() with PDO
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
        'envios_actions.php', 'company_settings.php', 'company_settings_actions.php', 'backup.php',
        'backups.php', 'backup_status.php', 'logs.php', 'settings_actions.php',
        'cargos_existencias.php', 'almacenes.php',
        'catalogos_actions.php', 'kit_actions.php', 'almacenes_actions.php', 'ubicaciones_actions.php',
        'proveedores.php', 'proveedores_actions.php', 'proveedores_search.php', 'todo.php', 'notas_actions.php',
        'api_notifications.php', 'api_stats.php', 'api_warehouses.php', 'api_import.php', 'api_notes.php', 'api_suppliers.php',
        'api_settings.php', 'api_backups.php', 'api_users.php', 'api_catalogs.php'
    ],
    'supervisor' => [
        'dashboard.php', 'pedidos.php', 'envios.php', 'inventario.php', 'importacion.php',
        'about.php', 'manual.php', 'login.php', 'logout.php', 'api_sales_chart.php',
        'get_import_history.php', 'api_products.php', 'users.php', 'sesion.php', 'db.php',
        'sidebar.php', 'reports.php', 'api_orders.php', 'search_api.php', 'clientes.php',
        'api_clientes.php', 'api_sales_by_status_chart.php', 'envios_actions.php',
        'todo.php', 'notas_actions.php', 'api_notifications.php', 'api_stats.php', 'api_warehouses.php', 'api_import.php', 'api_notes.php', 'api_suppliers.php', 'api_catalogs.php'
    ],
    'operador' => [
        'dashboard.php', 'pedidos.php', 'envios.php', 'about.php', 'manual.php', 'login.php',
        'logout.php', 'api_sales_chart.php', 'sesion.php', 'db.php', 'sidebar.php',
        'api_orders.php', 'api_sales_by_status_chart.php',
        'envios_actions.php', 'todo.php', 'notas_actions.php',
        'api_notifications.php', 'api_stats.php', 'api_warehouses.php', 'api_notes.php'
    ]
];

// Use database permissions if available, otherwise use fallback
if (!empty($db_permissions)) {
    $allowed_pages = [$user_role => $db_permissions];
} else {
    $allowed_pages = $fallback_pages;
}

// Always-allowed APIs for any authenticated user (core system functionality)
$always_allowed_apis = [
    'api_notifications.php',
    'api_stats.php',
    'search_api.php',
    'sidebar.php'
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
        // Fix relative path redirect issues
        $redirectPath = (dirname($_SERVER['PHP_SELF']) == '/php') ? '../dashboard.php' : 'dashboard.php';
        header("Location: $redirectPath?error=access_denied&code=403");
    } else {
        $redirectPath = (dirname($_SERVER['PHP_SELF']) == '/php') ? '../login.php' : 'login.php';
        header("Location: $redirectPath?error=auth_required&code=401");
    }
    exit();
}

// Asegurarse de que los usuarios "supervisor" y "operador" no puedan acceder a admin.php directamente
if ($current_page === 'admin.php' && ($user_role === 'supervisor' || $user_role === 'operador')) {
    if ($is_api_call) {
        json_auth_error('Acceso denegado a la administración.');
    } else {
        header('Location: dashboard.php');
        exit();
    }
}

// Asegurarse de que los usuarios "operador" no puedan acceder a inventario.php o importacion.php
if (($current_page === 'inventario.php' || $current_page === 'importacion.php') && $user_role === 'operador') {
    if ($is_api_call) {
        json_auth_error('Acceso denegado al inventario/importación.');
    } else {
        header('Location: dashboard.php');
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
