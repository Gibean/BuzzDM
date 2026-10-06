<?php
/**
 * Centralized System Action Logger
 *
 * Use this helper to log all critical system actions (CRUD operations)
 * to the logs table for audit trail and reporting.
 */

require_once __DIR__ . '/../Tenant/TenantContext.php';

/**
 * Resuelve la empresa a la que pertenece una entrada de bitácora.
 *
 * `logs.empresa_id` es NOT NULL y sin DEFAULT. El INSERT de este helper omitía la
 * columna, así que las 30 llamadas de producción fallaban con error 1364
 * ("Field 'empresa_id' doesn't have a default value") y el catch de abajo se
 * tragaba la excepción: no se guardaba ninguna acción de sistema de ninguna
 * empresa. La bitácora era decorativa.
 *
 * Se resuelve con el mismo criterio que BaseRepository: primero el contexto
 * explícito y, si no hay, la sesión. Todos los llamadores pasan por
 * sesion.php, que aborta la petición si la empresa viene vacía, así que en la
 * práctica siempre hay tenant. Si aun así no lo hay, se devuelve null y el
 * llamador NO escribe: es preferible perder una línea de bitácora a atribuir la
 * acción a una empresa arbitraria.
 *
 * @return int|null
 */
function resolveLogEmpresaId(): ?int
{
    if (TenantContext::isSet()) {
        $id = TenantContext::id();
        return ($id !== null && $id > 0) ? (int)$id : null;
    }

    if (!empty($_SESSION['empresa_id'])) {
        return (int)$_SESSION['empresa_id'];
    }

    return null;
}

/**
 * Log a system action to the database
 *
 * @param PDO $pdo Database connection
 * @param int|null $userId User performing the action (null for system actions)
 * @param string $accion Action description (e.g., "Producto creado", "Cliente eliminado")
 * @param array|string|null $detalles Additional details (will be JSON-encoded if array)
 * @return bool Success status
 */
function logSystemAction($pdo, $userId, $accion, $detalles = null) {
    try {
        $empresaId = resolveLogEmpresaId();

        if ($empresaId === null) {
            error_log("Logger Error: no hay empresa activa, no se registra la acción '$accion'");
            return false;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO logs (empresa_id, usuario_id, accion, detalles, fecha)
             VALUES (?, ?, ?, ?, NOW())"
        );

        // Convert array details to JSON
        if (is_array($detalles)) {
            $detalles = json_encode($detalles, JSON_UNESCAPED_UNICODE);
        }

        return $stmt->execute([$empresaId, $userId, $accion, $detalles]);
    } catch (Exception $e) {
        error_log("Logger Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current user ID from session for logging
 *
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['usuario_id'] ?? null;
}

/**
 * Get current username from session for log details
 *
 * @return string Username or 'Sistema'
 */
function getCurrentUserName() {
    return $_SESSION['usuario_nombre'] ?? 'Sistema';
}
