-- ============================================================================
-- MIGRACIÓN: HASH DE LOS TOKENS DE RECUPERACIÓN DE CONTRASEÑA
-- ============================================================================
-- El token de recuperación se genera con bin2hex(random_bytes(32)) y se envía
-- por email. Hasta ahora se guardaba tal cual en password_reset_tokens.token,
-- de modo que cualquiera con lectura de la base de datos -- una copia de
-- seguridad, un phpMyAdmin compartido, una inyección SQL -- obtenía los
-- enlaces de recuperación todavía vigentes y podía cambiar la contraseña de la
-- cuenta. Ese token es, en la práctica, una contraseña temporal de un solo uso.
--
-- El token NO necesita ser reversible: se genera al vuelo, se envía al usuario
-- y lo único que hace falta es localizar la fila que le corresponde. Por eso
-- basta con almacenar SHA-256 y buscar por el hash.
--
-- SHA-256 en hexadecimal ocupa 64 caracteres, exactamente los mismos que
--(token actual, así que la columna nueva no ensancha la tabla.
--
-- ORDEN DE LAS SENTENCIAS (importante)
-- 1. Se añade token_hash y se rellena con SHA2(token, 256) ANTES de tocar
--    nada más. Ese backfill es lo que mantiene vivos los enlaces ya enviados:
--    al validarlos se hashea el token que llega por URL y coincide con
--    token_hash. Por eso el orden no se puede alterar.
-- 2. Después se añade el índice único, que es lo que hace la consulta por
--    hash tan rápida como la que había por token en claro.
-- 3. Y al final token pasa a admitir NULL, porque a partir de aquí el código
--    escribe solo el hash y deja la columna en claro vacía.
--
-- QUÉ PASA CON LOS TOKENS QUE YA EXISTEN
-- Sus filas conservan el texto plano hasta que se consumen o expiran, momento
-- en que php/cleanup_tokens.php las borra. Los tokens de recuperación duran una
-- hora, así que la exposición residual de este cambio se agota sola en menos de
-- una hora. No se vacía la columna a propósito: hacerlo de golpe rompe el
-- rollback a la versión anterior del código, que todavía buscaría por
-- password_reset_tokens.token.
--
-- La columna token se conserva. Puede eliminarse más adelante, una vez
-- confirmado que no queda ninguna fila en uso:
--   ALTER TABLE `password_reset_tokens` DROP COLUMN `token`;
-- ============================================================================

ALTER TABLE `password_reset_tokens`
    ADD COLUMN `token_hash` CHAR(64) NULL DEFAULT NULL AFTER `token`;

UPDATE `password_reset_tokens`
    SET `token_hash` = SHA2(`token`, 256)
    WHERE `token_hash` IS NULL;

ALTER TABLE `password_reset_tokens`
    ADD UNIQUE KEY `uniq_token_hash` (`token_hash`);

ALTER TABLE `password_reset_tokens`
    MODIFY COLUMN `token` CHAR(64) NULL DEFAULT NULL;