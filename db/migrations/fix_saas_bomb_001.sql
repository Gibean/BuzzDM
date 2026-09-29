-- ============================================================================
-- MIGRACIÓN CRÍTICA 001: Neutralización de la bomba de purga de producción
-- BuzzDM - Corrige los daños introducidos por saas_multitenant_setup.sql
--
-- CONTEXTO:
-- saas_multitenant_setup.sql marcó la empresa principal del sistema
-- (empresa.id = 1, Librería Txiki) con is_demo = 1 y un trial_ends_at
-- de 15 días. El cronjob php/cron/demo_cleanup.php selecciona
--   WHERE is_demo = 1 AND status = 'expired'
-- y ejecuta un DELETE FROM empresa con ON DELETE CASCADE, lo que habría
-- destruido la base de datos de producción al vencer el plazo.
--
-- Esta migración deja la empresa principal fuera del alcance de la purga.
-- ============================================================================

-- 1. La empresa principal deja de ser demo y nunca tendrá trial
UPDATE `empresa`
SET `is_demo`        = 0,
    `status`         = 'active',
    `trial_ends_at`  = NULL,
    `admin_email`    = NULL
WHERE `id` = 1;

-- 2. is_demo debe valer 0 por defecto.
--    Con DEFAULT 1, cualquier INSERT INTO empresa futuro nacía marcado como
--    demo y era candidato automático a ser purgado por el cronjob.
ALTER TABLE `empresa`
    MODIFY COLUMN `is_demo` TINYINT(1) NOT NULL DEFAULT 0;

-- 3. Una sola cuenta demo por correo de administrador.
--    Sin este índice, dos empresas demo distintas podían compartir admin_email
--    y el bloqueo de isDeviceOrEmailBlocked quedaba como única barrera.
--    Se ignoran los NULL porque MySQL admite múltiples NULL en índices únicos.
DELETE FROM `empresa` WHERE `device_fingerprint` = '';
ALTER TABLE `empresa`
    ADD UNIQUE KEY `uk_empresa_admin_email` (`admin_email`);

-- 4. Índice compuesto que sirve exactamente al SELECT del cronjob de purga,
--    de modo que la consulta no requiera un full scan sobre la tabla empresa.
ALTER TABLE `empresa`
    DROP INDEX `idx_empresa_status_trial`,
    ADD KEY `idx_empresa_purga` (`is_demo`, `status`, `trial_ends_at`);
