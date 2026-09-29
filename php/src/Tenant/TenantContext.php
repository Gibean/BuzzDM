<?php
/**
 * TenantContext
 *
 * Fuente única de verdad sobre "qué empresa está operando" en la petición actual.
 *
 * Por qué existe: antes de esta clase cada repositorio leía
 * $_SESSION['empresa_id'] por su cuenta y, en la práctica, ninguno lo usaba.
 * Todas las consultas corrían sin filtro de empresa_id y todas las columnas
 * tenían DEFAULT 1, así que los datos de cada empresa demo acababan en el
 * tenant de la empresa principal.
 *
 * Reglas de uso:
 *   - En peticiones web, php/sesion.php lo inicializa desde la sesión del
 *     usuario antes de ejecutar cualquier repositorio.
 *   - En procesos CLI (cronjob de purga, exportador, tests) se establece
 *     explícitamente con TenantContext::set() para cada empresa procesada.
 *   - Ningún repositorio puede ejecutar una consulta sin tenant: BaseRepository
 *     lanza TenantRequiredException si el contexto está vacío. Es preferible
 *     fallar envisible en desarrollo que devolver datos de otra empresa.
 */

class TenantRequiredException extends RuntimeException
{
    public function __construct(string $detalle = '')
    {
        $mensaje = 'No hay tenant activo en el contexto. No se puede ejecutar la consulta sin empresa_id.'
            . ($detalle !== '' ? " Detalle: $detalle" : '');
        parent::__construct($mensaje);
    }
}

class TenantContext
{
    private static ?int $tenantId = null;

    /**
     * Si la empresa activa es una demo. Vive en el contexto y no sólo en la
     * sesión porque los límites operativos de la demo (máximo de productos,
     * purga, exportación) también se aplican desde procesos CLI; si se leyera
     * únicamente de $_SESSION, un importador o un cron correría sin límites.
     * null = desconocido, se resuelve contra la sesión.
     */
    private static ?bool $isDemo = null;

    /** Tenant usado por los procesos CLI cuando explícitamente se permite el global. */
    public const GLOBAL_SCOPE = 0;

    /**
     * Fija el tenant activo. Sólo debe llamarse en el borde del proceso
     * (sesion.php, cronjob, test), nunca desde un servicio de negocio.
     */
    public static function set(?int $tenantId, ?bool $isDemo = null): void
    {
        self::$tenantId = $tenantId;
        self::$isDemo = $isDemo;
    }

    /**
     * ¿La empresa activa es una demo?
     *
     * Si el contexto no lo sabe, cae a la sesión para no romper el arranque
     * previo a la instalación completa del contexto.
     */
    public static function isDemo(): bool
    {
        if (self::$isDemo !== null) {
            return self::$isDemo;
        }

        return (bool)($_SESSION['is_demo'] ?? false);
    }

    /**
     * @return int|null ID de la empresa activa, o null si no hay ninguna
     */
    public static function id(): ?int
    {
        return self::$tenantId;
    }

    public static function isSet(): bool
    {
        return self::$tenantId !== null;
    }

    /**
     * Devuelve el tenant activo o lanza excepción.
     *
     * GLOBAL_SCOPE (0) es válido y significa "sin filtro de tenant". Se reserva
     * para el cronjob de purga y para tareas administrativas explícitas; nunca
     * debe alcanzarlo una petición web autenticada.
     */
    public static function require(string $detalle = ''): int
    {
        if (self::$tenantId === null) {
            throw new TenantRequiredException($detalle);
        }
        return self::$tenantId;
    }

    /**
     * Ejecuta un callback con un tenant concreto y restaura el anterior.
     * Usado por el cronjob para procesar cada empresa sin arrastrar estado.
     */
    public static function withTenant(?int $tenantId, callable $callback, ?bool $isDemo = null)
    {
        $previous = self::$tenantId;
        $previousIsDemo = self::$isDemo;
        self::$tenantId = $tenantId;
        self::$isDemo = $isDemo;
        try {
            return $callback();
        } finally {
            self::$tenantId = $previous;
            self::$isDemo = $previousIsDemo;
        }
    }

    /**
     * Vacía el contexto. Sólo para tests y para el cierre de peticiones largas.
     */
    public static function clear(): void
    {
        self::$tenantId = null;
        self::$isDemo = null;
    }
}
