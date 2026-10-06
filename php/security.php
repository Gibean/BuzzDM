<?php
/**
 * Helpers de seguridad transversal: escape de HTML y nonce para CSP.
 *
 * Este archivo no requiere sesion ni base de datos, para poder incluirlo
 * desde cualquier punto (incluidos endpoints publicos) sin arrastrar
 * dependencias ni ejecutar la logica de autorizacion de sesion.php.
 */

if (!function_exists('h')) {
    /**
     * Escapa texto para insertarlo dentro de HTML o de un atributo.
     *
     * Uso: <?= h($valor) ?>
     *
     * @param mixed $value
     * @return string
     */
    function h($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('hAttr')) {
    /**
     * Alias explicito para uso en atributos HTML.
     *
     * @param mixed $value
     * @return string
     */
    function hAttr($value): string
    {
        return h($value);
    }
}

if (!function_exists('cspNonce')) {
    /**
     * Devuelve (creando si hace falta) el nonce de la Content-Security-Policy
     * de la peticion actual.
     *
     * Debe inyectarse como <script nonce="<?= cspNonce() ?>"> y la cabecera CSP
     * debe declarar script-src 'nonce-<valor>'. El valor se genera una sola vez
     * por peticion y se comparte entre todas las Inclusiones.
     */
    function cspNonce(): string
    {
        static $nonce = null;

        if ($nonce === null) {
            $nonce = base64_encode(random_bytes(16));
        }

        return $nonce;
    }
}

if (!function_exists('isHttpsRequest')) {
    /**
     * Detecta si la peticion actual llego por HTTPS, incluyendo el caso de un
     * proxy inverso que reenvia el protocolo original en X-Forwarded-Proto.
     */
    function isHttpsRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') {
            return true;
        }

        return (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}

if (!function_exists('isCspStrictlyEnforceable')) {
    /**
     * Indica si la pagina actual puede recibir la CSP en modo enforce.
     *
     * Solo las paginas sin atributos on*= inline. El resto depende de codigo en
     * linea que aun no se ha migrado a addEventListener, y aplicarles una CSP
     * estricta dejaria sus botones, filtros y drag & drop sin funcionar.
     */
    function isCspStrictlyEnforceable(): bool
    {
        static $enforceable = null;

        if ($enforceable !== null) {
            return $enforceable;
        }

        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $page = basename($script, '.php');

        $pages = [
            'login',
            'registro',
            'forgot_password',
            'reset_password',
            'invitacion',
            'invitaciones',
            'ajustes',
            'reports',
            'demo_expired',
        ];

        $enforceable = in_array($page, $pages, true)
            || $page === 'system_footer'
            || $page === 'top_navbar';

        return $enforceable;
    }
}

if (!function_exists('sendSecurityHeaders')) {
    /**
     * Envia las cabeceras de endurecimiento que no dependen de mod_headers.
     *
     * Se invoca una sola vez por peticion. Definir BUZZDM_DISABLE_CSP=1 impide
     * enviar la CSP (kill switch para desplegar sin exponer la aplicacion a un
     * error de cabeceras que la deje sin estilos).
     */
    function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $disabled = getenv('BUZZDM_DISABLE_CSP') === '1'
            || (($_ENV['BUZZDM_DISABLE_CSP'] ?? '') === '1');

        if (!$disabled) {
            $nonce = cspNonce();

            // Estilos: Bootstrap, Font Awesome y Google Fonts llegan desde CDN.
            // Omitir estos origenes deja la pagina sin tipografia ni grid
            // responsive, por eso se permiten de forma explicita.
            $styleHosts = [
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://fonts.googleapis.com',
            ];

// Scripts: Bootstrap bundle, Chart.js, Mermaid, Tippy y
            // FingerprintJS (registro.php).
            $scriptHosts = [
                'https://cdn.jsdelivr.net',
                'https://cdnjs.cloudflare.com',
                'https://code.jquery.com',
                'https://unpkg.com',
                'https://openfpcdn.io',
            ];

            $frameAncestors = 'https://localhost https://127.0.0.1';

            $policy = "default-src 'self'; "
                . "base-uri 'self'; "
                . "object-src 'none'; "
                . "form-action 'self'; "
                . "frame-ancestors $frameAncestors; "
                . "img-src 'self' data: blob: https:; "
                // 'unsafe-inline' es obligatorio en style-src: hay 176 atributos
                // style="" y 13 bloques <style> en 28 archivos. El CSS no
                // ejecuta codigo, a diferencia de script-src, que si lo hace.
                . "font-src 'self' data: https:; "
                . "media-src 'self' blob:; "
                . "connect-src 'self'; "
                . "style-src 'self' 'unsafe-inline' " . implode(' ', $styleHosts) . "; "
                . "script-src 'self' 'nonce-$nonce' " . implode(' ', $scriptHosts) . "; "
                . "worker-src 'self' blob:; "
                . "child-src 'self' blob:;";

            if (isCspStrictlyEnforceable()) {
                header("Content-Security-Policy: $policy", true);
            } else {
                header("Content-Security-Policy-Report-Only: $policy", true);
            }
        }

        // HSTS solo sobre HTTPS: en HTTP plano se ignoraria, y ademas forzar
        // HTTPS mediante HSTS romperia los entornos locales que siguen en http.
        if (isHttpsRequest()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains', true);
        }
    }
}

