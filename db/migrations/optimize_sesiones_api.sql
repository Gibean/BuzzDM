-- Optimization: Add index on expiracion column for faster cleanup queries
-- Run this once to improve session cleanup performance

-- Add index to sesiones_api table for faster expired session cleanup
ALTER TABLE sesiones_api ADD INDEX idx_expiracion (expiracion);

-- One-time cleanup of old expired sessions
DELETE FROM sesiones_api WHERE expiracion < DATE_SUB(NOW(), INTERVAL 24 HOUR);
