<?php
/**
 * BuzzDM - Centralized Configuration
 *
 * Automatically detects the environment (localhost vs production)
 * and configures URLs and paths accordingly.
 */

// Prevent multiple inclusions
if (defined('BUZZDM_CONFIG_LOADED')) {
    return;
}
define('BUZZDM_CONFIG_LOADED', true);

// Load Composer autoloader (only if not already loaded)
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload_path) && !class_exists('Dotenv\Dotenv', false)) {
    require_once $autoload_path;
}

// Load .env configuration if available
$dotenv_path = __DIR__ . '/../';
if (class_exists('Dotenv\Dotenv') && file_exists($dotenv_path . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenv_path);
    $dotenv->safeLoad();
}

/**
 * Environment detection class
 */
class AppConfig {
    private static ?self $instance = null;

    private string $environment;
    private string $baseUrl;
    private string $basePath;

    private function __construct() {
        $this->detectEnvironment();
        $this->configureUrls();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Detect if we're running in localhost or production
     */
    private function detectEnvironment(): void {
        // Check explicit environment variable first
        $envSetting = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: '';

        if (!empty($envSetting)) {
            $this->environment = strtolower($envSetting);
            return;
        }

        // Auto-detect based on host
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Common localhost patterns
        $localhostPatterns = [
            'localhost',
            '127.0.0.1',
            '::1',
            '.local',
            '.test',
            '.dev'
        ];

        $isLocalhost = false;
        foreach ($localhostPatterns as $pattern) {
            if (stripos($host, $pattern) !== false) {
                $isLocalhost = true;
                break;
            }
        }

        // Also check for common XAMPP/WAMP/MAMP port patterns
        $port = $_SERVER['SERVER_PORT'] ?? '80';
        if (in_array($port, ['8080', '8888', '3000', '5000'])) {
            $isLocalhost = true;
        }

        $this->environment = $isLocalhost ? 'local' : 'production';
    }

    /**
     * Configure URLs based on detected environment
     */
    private function configureUrls(): void {
        // Base URL for links, emails, redirects (e.g. http://localhost/buzzdm or https://example.com)
        $envBaseUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';

        if (!empty($envBaseUrl)) {
            $this->baseUrl = rtrim($envBaseUrl, '/');
            $this->basePath = parse_url($this->baseUrl, PHP_URL_PATH) ?: '';
            return;
        }

        // Auto-detect protocol
        $protocol = 'http';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            $protocol = 'https';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $protocol = 'https';
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Determine base path
        if ($this->environment === 'local' || $this->environment === 'development') {
            // Calculate project path relative to Document Root dynamically
            $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)));

            if ($docRoot && strpos($projectRoot, $docRoot) === 0) {
                $this->basePath = rtrim(substr($projectRoot, strlen($docRoot)), '/');
            } else {
                $this->basePath = '/BuzzDM'; // Default fallback for local
            }
        } else {
            // Production: assume project is at root
            $this->basePath = '';
        }

        $this->baseUrl = $protocol . '://' . $host . $this->basePath;
    }

    /**
     * Check if running in local/development environment
     */
    public function isLocal(): bool {
        return $this->environment === 'local' || $this->environment === 'development';
    }

    /**
     * Check if running in production environment
     */
    public function isProduction(): bool {
        return $this->environment === 'production';
    }

    /**
     * Get the current environment name
     */
    public function getEnvironment(): string {
        return $this->environment;
    }

    /**
     * Get the full base URL (e.g., http://localhost/BuzzDM or https://example.com)
     */
    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    /**
     * Get the base path only (e.g., /BuzzDM or empty string)
     */
    public function getBasePath(): string {
        return $this->basePath;
    }

    /**
     * Generate a full URL for a given path
     * @param string $path Relative path (e.g., 'dashboard', 'php/api_auth.php')
     */
    public function url(string $path = ''): string {
        $path = ltrim($path, '/');
        return $this->baseUrl . '/' . $path;
    }

    /**
     * Generate a relative URL (useful for redirects)
     * @param string $path Relative path
     */
    public function relativePath(string $path = ''): string {
        $path = ltrim($path, '/');
        return $this->basePath . '/' . $path;
    }

    /**
     * Get RewriteBase for .htaccess (with trailing slash)
     */
    public function getRewriteBase(): string {
        return empty($this->basePath) ? '/' : $this->basePath . '/';
    }

    /**
     * Check if debug mode is enabled
     */
    public function isDebugMode(): bool {
        $debug = $_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'false';
        return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
    }
}

// Convenience functions for quick access

/**
 * Get AppConfig instance
 */
function config(): AppConfig {
    return AppConfig::getInstance();
}

/**
 * Generate full URL for a path
 */
function app_url(string $path = ''): string {
    return AppConfig::getInstance()->url($path);
}

/**
 * Get base URL
 */
function base_url(): string {
    return AppConfig::getInstance()->getBaseUrl();
}

/**
 * Get base path
 */
function base_path(): string {
    return AppConfig::getInstance()->getBasePath();
}

/**
 * Check if local environment
 */
function is_local(): bool {
    return AppConfig::getInstance()->isLocal();
}

/**
 * Check if production environment
 */
function is_production(): bool {
    return AppConfig::getInstance()->isProduction();
}
