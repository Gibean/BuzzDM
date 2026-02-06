<?php
/**
 * Centralized System Action Logger
 *
 * Use this helper to log all critical system actions (CRUD operations)
 * to the logs table for audit trail and reporting.
 */

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
        $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())");

        // Convert array details to JSON
        if (is_array($detalles)) {
            $detalles = json_encode($detalles, JSON_UNESCAPED_UNICODE);
        }

        return $stmt->execute([$userId, $accion, $detalles]);
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
