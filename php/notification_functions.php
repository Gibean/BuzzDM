<?php
/**
 * Helper functions for internal system notifications.
 * Requires db.php to be included/available where this is used, or pass $pdo explicitly.
 */

function createNotification($conn, $userId, $type, $title, $message, $link = null) {
    if (!$conn) return false;

    // Use prepared statements based on connection type (mysqli or PDO)
    // Detailed check to support both db.php styles (mysqli vs PDO)
    // Based on previous files, db.php provides getDbConnection() returning PDO, but some files might use $conn mysqli.
    // Let's assume PDO since notas_actions.php uses getDbConnection().

    try {
        if ($conn instanceof PDO) {
            $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace, creado_at) VALUES (?, ?, ?, ?, ?, NOW())");
            return $stmt->execute([$userId, $type, $title, $message, $link]);
        }
        elseif ($conn instanceof mysqli) {
            $stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace, creado_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issss", $userId, $type, $title, $message, $link);
            return $stmt->execute();
        }
    } catch (Exception $e) {
        // Silently fail or log error to avoid breaking the main flow
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
    return false;
}
