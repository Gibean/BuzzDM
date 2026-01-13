<?php
/**
 * Check for system alerts (Low Stock, etc.) and generate notifications.
 * Should be included in dashboard.php or main layout.
 * Assumes $pdo and session are available.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'notification_functions.php';

// Only for Admins and Supervisors
if (isset($_SESSION['usuario_rol']) && in_array($_SESSION['usuario_rol'], ['admin', 'supervisor'])) {

    // Alert Frequency Control: Check once per session/day to avoid spam
    $today = date('Y-m-d');

    if (!isset($_SESSION['last_alert_check']) || $_SESSION['last_alert_check'] !== $today) {

        // Ensure we have a separate connection if $pdo is not set (failsafe)
        if (!isset($pdo)) {
            require_once 'db.php';
            $pdo = getDbConnection();
        }

        try {
            $userId = $_SESSION['user_id'];

            // 1. LOW STOCK CHECK
            // Check if we already sent a notification today
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND tipo = 'low_stock' AND DATE(creado_at) = ?");
            $stmtCheck->execute([$userId, $today]);

            if ($stmtCheck->fetchColumn() == 0) {
                // Check actual stock
                // Ensure v_low_stock view exists, otherwise query table directly
                $stmtStock = $pdo->query("SELECT COUNT(*) FROM v_low_stock"); // v_low_stock should be defined in DB
                $lowCount = $stmtStock->fetchColumn();
                // If view fails or logic differs, fallback:
                // SELECT COUNT(*) FROM productos WHERE stock <= min_stock

                if ($lowCount > 0) {
                    $msg = "Hay $lowCount productos con existencias críticas. Se recomienda reabastecer.";
                    createNotification($pdo, $userId, 'low_stock', 'Stock Crítico Detectado', $msg, 'inventario.php?filter=low_stock');
                }
            }

            // 2. SECURITY ALERT (Example: Failed logins - logic usually in login process, but we can check logs here)
            // ...

            // Mark checked
            $_SESSION['last_alert_check'] = $today;

        } catch (Exception $e) {
            error_log("Alert Check Error: " . $e->getMessage());
        }
    }
}
