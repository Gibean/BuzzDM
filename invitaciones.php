<?php
/**
 * BUZZ Data Manager - Panel de Invitaciones
 * Mini menú para acceder y copiar los links de cada invitación
 */

require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/security.php';

// Las cabeceras deben emitirse antes de cualquier salida HTML.
sendSecurityHeaders();

$baseUrl = base_url();

$invitaciones = [
    [
        'key'    => '01',
        'nombre' => 'MSc. Zulismar Gallardo',
        'rol'    => 'Jurado Evaluador',
        'icon'   => '⚖️',
    ],
    [
        'key'    => '02',
        'nombre' => 'Ing. Eduardo Yaguare',
        'rol'    => 'Jurado Evaluador',
        'icon'   => '⚖️',
    ],
    [
        'key'    => '03',
        'nombre' => 'Lic. Coral Jeidub',
        'rol'    => 'Tutora Académica',
        'icon'   => '📚',
    ],
    [
        'key'    => '04',
        'nombre' => 'Sheira Jaramillo',
        'rol'    => 'Gerente General — Proyecto académico demostrativo',
        'icon'   => '🏢',
    ],
    [
        'key'    => '05',
        'nombre' => 'Lic. Yosmar Pérez',
        'rol'    => 'Representante de la Comunidad',
        'icon'   => '🤝',
    ],
    [
        'key'    => 'invitado',
        'nombre' => 'Invitado General',
        'rol'    => 'Invitado de Honor',
        'icon'   => '✉️',
    ],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Invitaciones — BDM</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="assets/img/recursosinv/inv-logo1.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto+Mono:wght@300;400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c10;
            --card: #111419;
            --gold: #c9a84c;
            --gold-light: #dfc06a;
            --text: #e8e4dc;
            --muted: #a09888;
            --border: rgba(201, 168, 76, 0.2);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .panel {
            width: 100%;
            max-width: 480px;
        }

        .panel-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .panel-header img {
            width: 48px;
            margin-bottom: 10px;
            filter: drop-shadow(0 0 10px rgba(201,168,76,0.3));
        }

        .panel-header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.85rem;
            color: var(--gold);
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .panel-header p {
            font-family: 'Roboto Mono', monospace;
            font-size: 0.65rem;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .inv-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .inv-item {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .inv-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--gold);
            opacity: 0.4;
            transition: opacity 0.2s;
        }

        .inv-item:hover {
            border-color: rgba(201, 168, 76, 0.4);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .inv-item:hover::before {
            opacity: 1;
        }

        .inv-icon {
            font-size: 1.4rem;
            flex-shrink: 0;
            width: 36px;
            text-align: center;
        }

        .inv-info {
            flex: 1;
            min-width: 0;
        }

        .inv-info .name {
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text);
        }

        .inv-info .role {
            font-family: 'Roboto Mono', monospace;
            font-size: 0.6rem;
            color: var(--muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .inv-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 4px;
            background: transparent;
            color: var(--gold);
            font-family: 'Roboto Mono', monospace;
            font-size: 0.6rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn:hover {
            background: rgba(201, 168, 76, 0.1);
            border-color: var(--gold);
        }

        .btn.copied {
            background: rgba(201, 168, 76, 0.2);
            border-color: var(--gold);
            color: var(--gold-light);
        }

        .btn svg {
            width: 13px;
            height: 13px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(20px);
            background: var(--card);
            border: 1px solid var(--gold);
            color: var(--gold);
            font-family: 'Roboto Mono', monospace;
            font-size: 0.7rem;
            letter-spacing: 1px;
            padding: 10px 20px;
            border-radius: 6px;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 100;
        }

        .toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .divider {
            width: 60px;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 16px auto;
            opacity: 0.3;
        }

        .panel-footer {
            text-align: center;
            margin-top: 20px;
        }

        .panel-footer p {
            font-family: 'Roboto Mono', monospace;
            font-size: 0.55rem;
            color: var(--muted);
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="panel">
    <div class="panel-header">
        <img src="assets/img/recursosinv/inv-logo1.png" alt="BDM">
        <h1>Invitaciones</h1>
        <p>Links de presentación</p>
    </div>

    <div class="inv-list">
        <?php foreach ($invitaciones as $inv): 
            $link = app_url('invitacion?para=' . $inv['key']);
        ?>
        <div class="inv-item">
            <div class="inv-icon"><?php echo $inv['icon']; ?></div>
            <div class="inv-info">
                <div class="name"><?php echo htmlspecialchars($inv['nombre']); ?></div>
                <div class="role"><?php echo htmlspecialchars($inv['rol']); ?></div>
            </div>
            <div class="inv-actions">
                <button class="btn" onclick="copyLink('<?php echo $link; ?>', this)" title="Copiar link">
                    <svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Copiar
                </button>
                <a class="btn" href="<?php echo $link; ?>" target="_blank" title="Abrir invitación">
                    <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Abrir
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div class="panel-footer">
        <p>14 de Abril, 2026 — 4:00 PM</p>
    </div>
</div>

<div class="toast" id="toast">✓ Link copiado al portapapeles</div>

<script nonce="<?= cspNonce() ?>">
function copyLink(link, btn) {
    navigator.clipboard.writeText(link).then(() => {
        btn.classList.add('copied');
        btn.innerHTML = '<svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Copiado';
        
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
            btn.classList.remove('copied');
            btn.innerHTML = '<svg viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copiar';
        }, 2000);
    });
}
</script>

</body>
</html>
