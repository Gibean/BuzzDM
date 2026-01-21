-- ============================================================================
-- BuzzDM - Two-Factor Authentication Migration
-- ============================================================================
-- Run Date: 2026-01-20
-- Description: Adds 2FA columns to usuarios table
-- ============================================================================

-- Add 2FA columns to usuarios table
ALTER TABLE usuarios
ADD COLUMN two_factor_secret VARCHAR(64) NULL COMMENT 'Base32 encoded TOTP secret' AFTER password,
ADD COLUMN two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=disabled, 1=enabled' AFTER two_factor_secret,
ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL COMMENT 'When 2FA was activated' AFTER two_factor_enabled;

-- Add index for 2FA lookups
ALTER TABLE usuarios ADD INDEX idx_two_factor_enabled (two_factor_enabled);

-- Verification query
SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'usuarios'
AND COLUMN_NAME LIKE 'two_factor%';
