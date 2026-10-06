<?php
/**
 * RateLimiter - Limitacion de intentos por ventana temporal.
 *
 * Sustituye al bloqueo permanente por usuarios.intentos_fallidos. Cada combinacion
 * de accion e identificador (email o IP) se contabiliza en una ventana deslizante;
 * al superar el maximo se aplica un bloqueo temporal en lugar de inutilizar la
 * cuenta de forma definitiva.
 *
 * Decisiones de diseno:
 *
 * - La clave se guarda como SHA-256 de accion+identificador. Esta tabla no
 *   guarda emails ni IPs en claro, de modo que no se convierte en un segundo
 *   almacen de datos personales.
 * - Si la tabla no existe todavia (migracion no aplicada), el limitador falla
 *   ABIERTO y registra una aviso. Cerrar el acceso a la aplicacion por no
 *   tener aplicada una migracion seria peor que la ausencia temporal del limite.
 * - Los limites por IP son mas altos que los por email porque las oficinas,
 *  Networkseva y los operadores moviles comparten una misma IP publica; un
 *   limite bajo por IP bloquearia a usuarios legitimos.
 */
class RateLimiter {

    /** Acciones usadas en la aplicacion. */
    const LOGIN_POR_EMAIL = 'login_email';
    const LOGIN_POR_IP    = 'login_ip';
    const DOS_FACTOR      = 'two_factor';
    const RECUPERACION    = 'recuperacion';
    const RESET_PASSWORD  = 'reset_password';

    /** @var array<string,PDO> Cache de conexiones por DSN, por si falta la global. */
    private static $conexiones = [];

    /**
     * Comprueba si una accion esta bloqueada, sin contar el intento.
     *
     * @param string $accion
     * @param string $identificador
     * @return array{bloqueado:bool, restantes:int, segundos:int}
     */
    public static function estado($accion, $identificador) {
        $pdo = self::pdo();
        if ($pdo === null) {
            return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT intentos, bloqueado_hasta FROM rate_limits WHERE clave = ? LIMIT 1'
            );
            $stmt->execute([self::clave($accion, $identificador)]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila) {
                return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
            }

            if (!empty($fila['bloqueado_hasta'])) {
                $restante = strtotime($fila['bloqueado_hasta']) - time();
                if ($restante > 0) {
                    return ['bloqueado' => true, 'restantes' => 0, 'segundos' => $restante];
                }
            }
        } catch (PDOException $e) {
            self::avisoSinMigracion($e);
            return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
        }

        return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
    }

    /**
     * Registra un intento y devuelve si queda bloqueada la accion.
     *
     * @param string $accion
     * @param string $identificador
     * @param int    $maxIntentos      Intentos permitidos dentro de la ventana.
     * @param int    $ventanaSegundos  Longitud de la ventana deslizante.
     * @param int    $cooldownSegundos Duracion del bloqueo al superarla.
     * @return array{bloqueado:bool, restantes:int, segundos:int}
     */
    public static function registrar($accion, $identificador, $maxIntentos, $ventanaSegundos, $cooldownSegundos) {
        $pdo = self::pdo();
        if ($pdo === null) {
            return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
        }

        $clave = self::clave($accion, $identificador);

        try {
            $pdo->beginTransaction();

            // INSERT ... ON DUPLICATE KEY permite que el contador se incremente
            // de forma atomica sin una ventana de carrera entre peticiones
            // concurrentes del mismo identificador.
            $stmt = $pdo->prepare(
                'INSERT INTO rate_limits (clave, accion, intentos, ventana_inicio, bloqueado_hasta)
                 VALUES (?, ?, 1, NOW(), NULL)
                 ON DUPLICATE KEY UPDATE
                     intentos = IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta <= NOW(),
                                   1,
                                   intentos + 1),
                     ventana_inicio = IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta <= NOW(),
                                         NOW(),
                                         ventana_inicio),
                     bloqueado_hasta = IF(bloqueado_hasta IS NOT NULL AND bloqueado_hasta <= NOW(),
                                          NULL,
                                          bloqueado_hasta)'
            );
            $stmt->execute([$clave, $accion]);

            // Si la ventana deslizante ya expiro, el contador vuelve a empezar.
            $stmt = $pdo->prepare(
                'UPDATE rate_limits
                    SET intentos = 1, ventana_inicio = NOW(), bloqueado_hasta = NULL
                  WHERE clave = ? AND ventana_inicio < (NOW() - INTERVAL ? SECOND)'
            );
            $stmt->execute([$clave, $ventanaSegundos]);

            $stmt = $pdo->prepare('SELECT intentos FROM rate_limits WHERE clave = ? LIMIT 1');
            $stmt->execute([$clave]);
            $intentos = (int) $stmt->fetchColumn();

            if ($intentos > $maxIntentos) {
                $stmt = $pdo->prepare(
                    'UPDATE rate_limits SET bloqueado_hasta = (NOW() + INTERVAL ? SECOND) WHERE clave = ?'
                );
                $stmt->execute([$cooldownSegundos, $clave]);
                $pdo->commit();
                return ['bloqueado' => true, 'restantes' => 0, 'segundos' => $cooldownSegundos];
            }

            $pdo->commit();
            return [
                'bloqueado'  => false,
                'restantes' => max(0, $maxIntentos - $intentos),
                'segundos'   => 0,
            ];

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            self::avisoSinMigracion($e);
            return ['bloqueado' => false, 'restantes' => PHP_INT_MAX, 'segundos' => 0];
        }
    }

    /**
     * Consume un intento y responde si la accion queda bloqueada.
     *
     * Atajo de registrar() con los limites por defecto de la accion.
     *
     * @param string $accion
     * @param string $identificador
     * @return array{bloqueado:bool, restantes:int, segundos:int}
     */
    public static function consumir($accion, $identificador) {
        $limites = self::limites($accion);

        // Si la accion ya esta bloqueada no se incrementa nada: de lo contrario
        // el contador seguiria creciendo durante el cooldown.
        $estado = self::estado($accion, $identificador);
        if ($estado['bloqueado']) {
            return $estado;
        }

        return self::registrar(
            $accion,
            $identificador,
            $limites['max'],
            $limites['ventana'],
            $limites['cooldown']
        );
    }

    /**
     * Comprueba el bloqueo sin consumir intento (usado antes de validar).
     *
     * @param string $accion
     * @param string $identificador
     * @return array{bloqueado:bool, restantes:int, segundos:int}
     */
    public static function comprobar($accion, $identificador) {
        return self::estado($accion, $identificador);
    }

    /**
     * Limpia el contador tras un exito.
     *
     * @param string $accion
     * @param string $identificador
     */
    public static function limpiar($accion, $identificador) {
        $pdo = self::pdo();
        if ($pdo === null) {
            return;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM rate_limits WHERE clave = ?');
            $stmt->execute([self::clave($accion, $identificador)]);
        } catch (PDOException $e) {
            self::avisoSinMigracion($e);
        }
    }

    /**
     * Limites por accion.
     *
     * Los valores por IP son deliberadamente mas altos: una oficina o una red
     * movil comparte IP entre varios usuarios legitimos, y un limite bajo
     * bloquearia al primero que se equivoque unas veces.
     *
     * @param string $accion
     * @return array{max:int, ventana:int, cooldown:int}
     */
    public static function limites($accion) {
        switch ($accion) {
            case self::LOGIN_POR_EMAIL:
                return ['max' => 10, 'ventana' => 900, 'cooldown' => 900];

            case self::LOGIN_POR_IP:
                return ['max' => 40, 'ventana' => 900, 'cooldown' => 600];

            case self::DOS_FACTOR:
                // 6 digitos = 1.000.000 de combinaciones; 5 intentos por ciclo
                // de 5 minutos deja margen de sobra a un error de dedo.
                return ['max' => 5, 'ventana' => 300, 'cooldown' => 300];

            case self::RECUPERACION:
                // Evita usar el formulario para bombardear el correo de terceros.
                return ['max' => 3, 'ventana' => 3600, 'cooldown' => 3600];

            case self::RESET_PASSWORD:
                return ['max' => 10, 'ventana' => 3600, 'cooldown' => 900];

            default:
                return ['max' => 10, 'ventana' => 900, 'cooldown' => 900];
        }
    }

    /**
     * Proxies de confianza, tomados de RATE_LIMIT_PROXIES.
     *
     * Lista vacia por defecto: sin ella el limitador se apoya solo en
     * REMOTE_ADDR, que es el unico valor que no puedefalsear el cliente.
     *
     * @return string[] IPs exactas o notacion CIDR.
     */
    private static function proxiesDeConfianza(): array {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $crudo = (string)(getenv('RATE_LIMIT_PROXIES') ?: ($_ENV['RATE_LIMIT_PROXIES'] ?? ''));

        $cache = [];
        foreach (explode(',', $crudo) as $entrada) {
            $entrada = trim($entrada);
            if ($entrada !== '') {
                $cache[] = $entrada;
            }
        }

        return $cache;
    }

    /**
     * Indica si una IP pertenece a la lista de proxies de confianza.
     *
     * @param string $ip
     * @return bool
     */
    private static function esProxyDeConfianza(string $ip): bool {
        foreach (self::proxiesDeConfianza() as $proxy) {
            if (strpos($proxy, '/') !== false) {
                if (self::ipEnRango($ip, $proxy)) {
                    return true;
                }
            } elseif ($proxy === $ip) {
                return true;
            }
        }

        return false;
    }

    /**
     * Comprueba si una IP cae dentro de un rango CIDR, en IPv4 o IPv6.
     *
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private static function ipEnRango(string $ip, string $cidr): bool {
        [$red, $prefijo] = array_pad(explode('/', $cidr, 2), 2, '');

        $ipBin = @inet_pton($ip);
        $redBin = @inet_pton(trim($red));
        if ($ipBin === false || $redBin === false || strlen($ipBin) !== strlen($redBin)) {
            return false;
        }

        $bits = (int) $prefijo;
        $maximo = strlen($ipBin) * 8;
        if ($bits < 0 || $bits > $maximo) {
            return false;
        }

        $bytes = intdiv($bits, 8);
        if ($bytes > 0 && strncmp($ipBin, $redBin, $bytes) !== 0) {
            return false;
        }

        $resto = $bits % 8;
        if ($resto === 0) {
            return true;
        }

        $mascara = ~((1 << (8 - $resto)) - 1) & 0xFF;

        return (ord($ipBin[$bytes]) & $mascara) === (ord($redBin[$bytes]) & $mascara);
    }

    /**
     * IP del cliente.
     *
     * Detras de un proxy de confianza la IP real se reconstruye desde
     * X-Forwarded-For recorriéndolo de derecha a izquierda y saltando los proxies
     * conocidos: el primer salto que no es de confianza es el cliente.
     *
     * X-Forwarded-For NO se honra si REMOTE_ADDR no figura en RATE_LIMIT_PROXIES.
     * Confiar en esa cabecera sin comprobar quien la envia permitiria a un
     * atacante rotar IPs falsificandola y evadir el limite por IP.
     *
     * @return string
     */
    public static function ipCliente() {
        $remoto = (string)($_SERVER['REMOTE_ADDR'] ?? '');
        if (filter_var($remoto, FILTER_VALIDATE_IP) === false) {
            $remoto = '';
        }

        if ($remoto !== '' && self::esProxyDeConfianza($remoto)) {
            $xff = (string)($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
            if ($xff !== '') {
                $saltos = array_reverse(array_map('trim', explode(',', $xff)));
                foreach ($saltos as $salto) {
                    if (filter_var($salto, FILTER_VALIDATE_IP) === false) {
                        continue;
                    }
                    if (self::esProxyDeConfianza($salto)) {
                        continue;
                    }

                    return $salto;
                }
            }
        }

        return $remoto !== '' ? $remoto : 'desconocida';
    }

    /**
     * Mensaje en espanol con los minutos restantes, para el 429.
     *
     * @param int $segundos
     * @return string
     */
    public static function mensajeEspera($segundos) {
        if ($segundos < 60) {
            return "Demasiados intentos. Vuelve a probar en $segundos segundos.";
        }

        $minutos = (int) ceil($segundos / 60);
        return "Demasiados intentos. Vuelve a probar en $minutos minuto"
            . ($minutos === 1 ? '' : 's') . '.';
    }

    /**
     * Clave opaca por accion e identificador.
     *
     * @param string $accion
     * @param string $identificador
     * @return string
     */
    private static function clave($accion, $identificador) {
        return hash('sha256', $accion . '|' . strtolower(trim($identificador)));
    }

    /**
     * Obtiene el PDO, reutilizando el global cuando esta disponible.
     *
     * @return PDO|null
     */
    private static function pdo() {
        if (function_exists('getDbConnection')) {
            try {
                return getDbConnection();
            } catch (Throwable $e) {
                // Se intenta el respaldo de abajo.
            }
        }

        return self::conexionPropia();
    }

    /**
     * Conexion de respaldo si getDbConnection() no existe o falla.
     *
     * @return PDO|null
     */
    private static function conexionPropia() {
        try {
            require_once __DIR__ . '/../db.php';
            if (function_exists('getDbConnection')) {
                return getDbConnection();
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Distingue "migracion no aplicada" de un fallo real de base de datos.
     *
     * @param PDOException $e
     */
    private static function avisoSinMigracion(PDOException $e) {
        $codigo = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;

        // 1146 = tabla no existe, 42S02. Es el caso esperado antes de aplicar la
        // migracion y solo merece un aviso por peticion.
        if ($codigo === 1146) {
            static $avisado = false;
            if (!$avisado) {
                $avisado = true;
                error_log(
                    'BUZZ RateLimiter: la tabla rate_limits no existe. '
                    . 'Ejecuta php migrate.php para aplicar db/migrations/rate_limits_setup.sql. '
                    . 'El limitador esta en modo permisivo hasta entonces.'
                );
            }
            return;
        }

        error_log('BUZZ RateLimiter error: ' . $e->getMessage());
    }
}