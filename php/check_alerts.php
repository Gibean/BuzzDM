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
            $userId = $_SESSION['usuario_id'];

            // 1. LOW STOCK CHECK
            // Check if there is an existing UNREAD low stock notification
            $stmtCheck = $pdo->prepare("SELECT id, mensaje FROM notificaciones WHERE usuario_id = ? AND tipo = 'low_stock' AND leido = 0 ORDER BY creado_at DESC LIMIT 1");
            $stmtCheck->execute([$userId]);
            $existingNotif = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            // Check actual stock
            // Ensure v_low_stock view exists, otherwise query table directly
            $stmtStock = $pdo->query("SELECT COUNT(*) FROM v_low_stock");
            $lowCount = $stmtStock->fetchColumn();

            if ($lowCount > 0) {
                $msg = "Hay $lowCount productos con existencias críticas. Se recomienda reabastecer.";

                if ($existingNotif) {
                    // Update existing notification timestamp and message (if changed) to bump it up
                    // This prevents accumulation of duplicate alerts
                    $updateStmt = $pdo->prepare("UPDATE notificaciones SET creado_at = NOW(), mensaje = ? WHERE id = ?");
                    $updateStmt->execute([$msg, $existingNotif['id']]);
                } else {
                    // Create new notification if no unread one exists
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
