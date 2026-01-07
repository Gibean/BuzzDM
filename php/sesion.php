<?php
// Inicia la sesión en cada página que lo requiera

use function PHPSTORM_META\map;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// List of API scripts that should return JSON on auth/access failure
$api_scripts = [
    'api_sales_chart.php',
    'clientes_actions.php',
    'get_import_history.php',
    'pedidos_actions.php',
    'product_actions.php',
    'search_api.php',
    'process_import.php',
    'settings_actions.php',
    'logs.php',
    'users.php',
    'backups.php',
    'backup_status.php',
    // Phase 1 - Cargos de existencias
    'cargos_actions.php',
    'productos_search.php',
    'proveedores_search.php',
    // Phase 1.7 - Product types and kits
    'catalogos_actions.php',
    'kit_actions.php',
    // Phase 2 - Multi-warehouse
    'almacenes_actions.php',
    'ubicaciones_actions.php',
    // Proveedores
    'proveedores_actions.php'
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
require_once 'db.php'; // This now provides the getDbConnection() function
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

// Definir las páginas permitidas para cada rol
$allowed_pages = [
    'admin' => [
        'dashboard.php',
        'pedidos.php',
        'envios.php',
        'inventario.php',
        'importacion.php',
        'admin.php',
        'about.php',
        'manual.php',
        'login.php',
        'logout.php',
        'api_sales_chart.php',
        'create_admin.php',
        'get_import_history.php',
        'product_actions.php',
        'users.php',
        'sesion.php',
        'db.php',
        'sidebar.php',
        'reports.php',
        'pedidos_actions.php',
        'search_api.php',
        'clientes.php',
        'clientes_actions.php',
        'api_sales_by_status_chart.php',
        'envios_actions.php',
        'company_settings.php',
        'company_settings_actions.php',
        'backup.php',
        'backups.php',
        'backup_status.php',
        'logs.php',
        'api_sales_chart.php',
        'clientes_actions.php',
        'get_import_history.php',
        'pedidos_actions.php',
        'product_actions.php',
        'search_api.php',
        'process_import.php',
        'manual.php',
        'settings_actions.php',
        'cargos_existencias.php',
        'almacenes.php',
        // API scripts for inventory phases
        'cargos_actions.php',
        'productos_search.php',
        'catalogos_actions.php',
        'kit_actions.php',
        'almacenes_actions.php',
        'ubicaciones_actions.php',
        // Proveedores
        'proveedores.php',
        'proveedores_actions.php'
    ],
    'supervisor' => [
        'dashboard.php',
        'pedidos.php',
        'envios.php',
        'inventario.php',
        'importacion.php',
        'about.php',
        'manual.php',
        'login.php',
        'logout.php',
        'api_sales_chart.php',
        'get_import_history.php',
        'product_actions.php',
        'users.php',
        'sesion.php',
        'db.php',
        'sidebar.php',
        'reports.php',
        'pedidos_actions.php',
        'search_api.php',
        'clientes.php',
        'clientes_actions.php',
        'api_sales_by_status_chart.php',
        'envios_actions.php',
        'api_sales_chart.php',
        'clientes_actions.php',
        'get_import_history.php',
        'pedidos_actions.php',
        'product_actions.php',
        'search_api.php',
        'process_import.php',
        'manual.php',
        // Phase 1 and 2 pages for supervisor
        'cargos_existencias.php',
        'almacenes.php',
        'cargos_actions.php',
        'productos_search.php',
        'catalogos_actions.php',
        'kit_actions.php',
        'almacenes_actions.php',
        'ubicaciones_actions.php',
        'proveedores.php',
        'proveedores_actions.php'
    ],
    'operador' => [
        'dashboard.php',
        'pedidos.php',
        'envios.php',
        'about.php',
        'manual.php',
        'login.php',
        'logout.php',
        'api_sales_chart.php',
        'sesion.php',
        'db.php',
        'sidebar.php',
        'pedidos_actions.php',
        'search_api.php',
        'api_sales_by_status_chart.php',
        'envios_actions.php',
        'pedidos_actions.php',
        'product_actions.php',
        'search_api.php',
        'manual.php'

    ]
];

// Obtener el rol del usuario de la sesión
$user_role = $_SESSION['usuario_rol'] ?? 'guest';

// Verificar si la página actual está permitida para el rol del usuario
if (!isset($allowed_pages[$user_role]) || !in_array($current_page, $allowed_pages[$user_role])) {
    if ($is_api_call) {
        json_auth_error('Acceso denegado. No tienes permisos para esta acción.');
    } else if ($user_role === 'admin' || $user_role === 'supervisor' || $user_role === 'operador') {
        header('Location: dashboard.php');
    } else {
        header('Location: login.php');
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
}

?>
