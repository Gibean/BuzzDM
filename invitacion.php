<?php
/**
 * BUZZ Data Manager - Invitación Interactiva
 * Sobre negro animado que revela invitación personalizada
 *
 * Uso: invitacion?para=zulismar | invitacion?para=eduardo | etc.
 */

require_once __DIR__ . '/php/security.php';

// Debe emitirse antes de cualquier salida HTML.
sendSecurityHeaders();

// Mapping de invitados
// Nombres de ejemplo: en una publicacion publica no se listan personas reales
// (jurados, tutores, representantes) sin su consentimiento. Sustituyelos por los
// tuyos si sirves esta pagina en un evento real; el archivo original con los
// nombres de la defensa se guarda solo en tu maquina, fuera del repositorio.
$invitados = [
    '01' => [
        'nombre'  => 'MSc. Ana Rodríguez',
        'rol'     => 'Jurado Evaluador',
        'prefijo' => 'Estimada',
        'titulo'  => 'Profesora',
    ],
    '02' => [
        'nombre'  => 'Ing. Carlos Méndez',
        'rol'     => 'Jurado Evaluador',
        'prefijo' => 'Estimado',
        'titulo'  => 'Profesor',
    ],
    '03' => [
        'nombre'  => 'Lic. Patricia Salom',
        'rol'     => 'Tutora Académica',
        'prefijo' => 'Estimada',
        'titulo'  => 'Profesora',
    ],
    '04' => [
        'nombre'  => 'María González',
        'rol'     => 'Representante de la Comunidad',
        'prefijo' => 'Estimada',
        'titulo'  => 'Sra.',
    ],
    '05' => [
        'nombre'  => 'Lic. Jorge Olivares',
        'rol'     => 'Representante de la Comunidad',
        'prefijo' => 'Estimado',
        'titulo'  => 'Licenciado',
    ],
    'invitado' => [
        'nombre'  => 'Invitado Especial',
        'rol'     => 'Invitado de Honor',
        'prefijo' => 'Estimado/a',
        'titulo'  => '',
    ],
];

// Determinar invitado actual
// Solo se aceptan claves alfanumericas que existan en la lista de invitados; cualquier
// otro valor cae en 'invitado'. Este parametro es publico (la pagina no requiere sesion),
// por lo que no puede construir claves ni inyectar markup.
$paraKey = isset($_GET['para']) && is_string($_GET['para']) ? trim($_GET['para']) : '';
$paraKey = (preg_match('/^[a-zA-Z0-9_-]{1,32}$/', $paraKey) && isset($invitados[$paraKey]))
    ? $paraKey
    : 'invitado';
$invitado = $invitados[$paraKey];

$nombre  = htmlspecialchars($invitado['nombre']);
$rol     = htmlspecialchars($invitado['rol']);
$prefijo = htmlspecialchars($invitado['prefijo']);
$titulo  = htmlspecialchars($invitado['titulo']);

// Generar mensaje personalizado
$esJurado = in_array($paraKey, ['01', '02']);
$esTutor  = $paraKey === '03';
$esComunidad = in_array($paraKey, ['04', '05']);

if ($esJurado) {
    $mensaje = "$prefijo $titulo, nos es grato extenderle una cordial invitación para que nos honre con su presencia como <span class=\"inv-highlight\">Jurado Evaluador</span> en la defensa de nuestro Proyecto Socio Tecnológico.";
} elseif ($esTutor) {
    $mensaje = "$prefijo $titulo, con profundo agradecimiento por su guía y compromiso a lo largo de este camino, le extendemos una cordial invitación a la <span class=\"inv-highlight\">Presentación y Defensa</span> de nuestro Proyecto Socio Tecnológico.";
} elseif ($esComunidad) {
    $mensaje = "$prefijo $titulo, como parte fundamental de este proyecto, le extendemos una cordial invitación a la <span class=\"inv-highlight\">Presentación y Defensa</span> de nuestro Proyecto Socio Tecnológico, desarrollado en beneficio de su organización.";
} else {
    $mensaje = "$prefijo, le extendemos una cordial invitación para acompañarnos en la <span class=\"inv-highlight\">Presentación y Defensa</span> de nuestro Proyecto Socio Tecnológico.";
}

$proyectoNombre = "Implementación de un Sistema de Gestión de Pedidos para una Librería, El Tigre, Estado Anzoátegui";

// Metadata personalizada
$ogTitle = ($paraKey === 'invitado')
    ? "Estás Invitado — BUZZ Data Manager"
    : "$nombre — Invitación BUZZ Data Manager";

$ogDescription = ($paraKey === 'invitado')
    ? "Le invitamos cordialmente a la Presentación y Defensa del Proyecto Socio Tecnológico BUZZ Data Manager. Martes 14 de Abril, 4:00 PM."
    : "$rol — Le invitamos cordialmente a la Presentación y Defensa del Proyecto Socio Tecnológico BUZZ Data Manager. Martes 14 de Abril, 4:00 PM.";

$pageTitle = ($paraKey === 'invitado')
    ? "Invitación — BUZZ Data Manager"
    : "Invitación para $nombre — BDM";

// Base URL (utiliza configuración central para localhost/producción)
require_once __DIR__ . '/php/config.php';
$baseUrl = base_url();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $ogDescription; ?>">

    <!-- Open Graph (WhatsApp, Facebook, etc.) -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $ogTitle; ?>">
    <meta property="og:description" content="<?php echo $ogDescription; ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/assets/img/recursosinv/inv-banner.webp">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8'); ?>/invitacion?para=<?php echo htmlspecialchars($paraKey, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo $ogTitle; ?>">
    <meta name="twitter:description" content="<?php echo $ogDescription; ?>">
    <meta name="twitter:image" content="<?php echo $baseUrl; ?>/assets/img/recursosinv/inv-banner.webp">

    <link rel="shortcut icon" href="assets/img/recursosinv/inv-logo1.png" type="image/png">
    <link rel="stylesheet" href="assets/css/invitacion.css">
</head>

<body>

    <!-- Scanline overlay -->
    <div class="inv-scanline"></div>

    <!-- Main container (mobile proportions) -->
    <div class="inv-container">

        <!-- Background -->
        <div class="inv-bg-layer">
            <div class="bg-image"></div>
            <div class="bg-deco"></div>
            <div class="bg-gradient"></div>
        </div>

        <!-- Ambient particles -->
        <div class="inv-particles">
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
            <div class="inv-particle"></div>
        </div>

        <!-- ========== ENVELOPE SCENE ========== -->
        <div class="inv-envelope-scene" id="envelopeScene">

            <div class="inv-envelope" id="envelope"
                style="opacity: 0; transform: translateY(20px); transition: all 0.8s ease;">

                <!-- Envelope back (full rectangle behind everything) -->
                <div class="inv-envelope-back"></div>

                <!-- Letter peeking behind body -->
                <div class="inv-letter-peek"></div>

                <!-- Envelope body (front panel) -->
                <div class="inv-envelope-body"></div>

                <!-- Flap (triangular top) -->
                <div class="inv-envelope-flap">
                    <div class="inv-envelope-flap-inner"></div>
                </div>

                <!-- Wax seal at junction -->
                <div class="inv-wax-seal">
                    <img src="assets/img/recursosinv/inv-logo2.png" alt="Sello">
                </div>
            </div>

            <div class="inv-envelope-cta">
                <p>Toca para abrir</p>
                <span class="cta-icon">▼</span>
            </div>
        </div>

        <!-- ========== INVITATION CARD SCENE ========== -->
        <div class="inv-card-scene" id="cardScene">
            <div class="inv-card-wrapper">
                <!-- Card back (visible during flip) -->
                <div class="inv-card-back"></div>
                <div class="inv-card">
                    <!-- Corner ornaments -->
                    <div class="inv-card-corner-br"></div>
                    <div class="inv-card-corner-tr"></div>
                    <div class="inv-card-corner-bl"></div>

                    <!-- Background decoration -->
                    <div class="card-bg-deco"></div>

                    <!-- Shimmer line -->
                    <div class="inv-shimmer-line"></div>

                    <div class="inv-card-content">

                        <!-- University -->
                        <div class="inv-university">
                            <img src="assets/img/recursosinv/inv_logouptjaa.png" alt="UPTJAA">
                            <div class="inv-university-name">
                                Universidad Politécnica
                                Territorial<br>
                                José Antonio Anzoátegui
                            </div>
                        </div>

                        <div class="inv-divider"></div>

                        <!-- Project Logo -->
                        <div class="inv-logo-section">
                            <img src="assets/img/recursosinv/inv-logo1.png" alt="BUZZ Data Manager">
                            <div class="inv-project-title">Buzz Data Manager</div>
                            <div class="inv-project-subtitle">Proyecto Socio Tecnológico</div>
                        </div>

                        <div class="inv-divider wide"></div>

                        <!-- Personalized greeting -->
                        <div class="inv-greeting">
                            <div class="inv-greeting-role"><?php echo $rol; ?></div>
                            <div class="inv-greeting-name"><?php echo $nombre; ?></div>
                        </div>

                        <!-- Message -->
                        <div class="inv-message">
                            <p><?php echo $mensaje; ?></p>
                        </div>

                        <div class="inv-divider"></div>

                        <!-- Event details -->
                        <div class="inv-details">
                            <!-- Project -->
                            <div class="inv-detail-row">
                                <div class="inv-detail-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                    </svg>
                                </div>
                                <div class="inv-detail-text">
                                    <div class="inv-detail-label">Proyecto</div>
                                    <div class="inv-detail-value" style="font-size: 0.72rem; line-height: 1.4;">
                                        <?php echo htmlspecialchars($proyectoNombre); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="inv-detail-row">
                                <div class="inv-detail-icon">
                                    <svg viewBox="0 0 24 24">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                </div>
                                <div class="inv-detail-text">
                                    <div class="inv-detail-label">Fecha</div>
                                    <div class="inv-detail-value">Martes, 14 de Abril de 2026</div>
                                </div>
                            </div>

                            <!-- Time -->
                            <div class="inv-detail-row">
                                <div class="inv-detail-icon">
                                    <svg viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                </div>
                                <div class="inv-detail-text">
                                    <div class="inv-detail-label">Hora</div>
                                    <div class="inv-detail-value">4:00 PM</div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="inv-detail-row">
                                <div class="inv-detail-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                </div>
                                <div class="inv-detail-text">
                                    <div class="inv-detail-label">Lugar</div>
                                    <div class="inv-detail-value">Salón Nexus 4.0<br><span
                                            style="font-size: 0.7rem; opacity: 0.7;">Sede Noel Rodríguez — UPTJAA</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="inv-divider"></div>

                        <!-- Authors -->
                        <div class="inv-authors">
                            <div class="inv-authors-title">Presentado por</div>
                            <div class="inv-author-name">T.S.U. Gisvel Aray y Yeicson Acosta</div>
                            <div class="inv-author-name">Gisvel Aray y Yeicson Acosta</div>
                        </div>

                        <div class="inv-divider wide"></div>

                        <!-- Footer logo -->
                        <div class="inv-footer">
                            <div class="inv-footer-logo">
                                <img src="assets/img/recursosinv/inv-logo1.png" alt="Buzz Data Manager">
                                <span>Proyecto socio tecnológico</span>
                            </div>
                        </div>

                        <div class="inv-footer-label">El Tigre — Estado Anzoátegui</div>
                    </div>
                </div>
            </div><!-- /inv-card-wrapper -->
        </div>

    </div>

    <script src="assets/js/invitacion.js"></script>
</body>

</html>
