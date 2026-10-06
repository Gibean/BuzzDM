<?php
/**
 * Helper functions for internal system notifications.
 * Requires db.php to be included/available where this is used, or pass $pdo explicitly.
 */
require_once __DIR__ . '/src/Tenant/TenantContext.php';

function createNotification($conn, $userId, $type, $title, $message, $link = null) {
    if (!$conn) return false;

    try {
        if ($conn instanceof PDO) {
            $tenantId = TenantContext::id();
            if ($tenantId === null) {
                error_log('createNotification: No hay tenant activo en contexto');
                return false;
            }
            $stmt = $conn->prepare("INSERT INTO notificaciones (empresa_id, usuario_id, tipo, titulo, mensaje, enlace, creado_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            return $stmt->execute([$tenantId, $userId, $type, $title, $message, $link]);
        }
        elseif ($conn instanceof mysqli) {
            $tenantId = TenantContext::id();
            if ($tenantId === null) {
                error_log('createNotification: No hay tenant activo en contexto');
                return false;
            }
            $stmt = $conn->prepare("INSERT INTO notificaciones (empresa_id, usuario_id, tipo, titulo, mensaje, enlace, creado_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("iissss", $tenantId, $userId, $type, $title, $message, $link);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
    return false;
}
