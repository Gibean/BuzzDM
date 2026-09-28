<?php
/**
 * Top Navbar Component
 * Reusable page header with sidebar toggle, title, subtitle, and optional action buttons.
 *
 * Usage (before including this file, set these variables):
 *   $nav_title   = 'PAGE TITLE';           // Required
 *   $nav_subtitle = 'breadcrumb text';    // Optional, "/ " is added automatically
 *   $nav_actions  = '<button>...</button>'; // Optional, HTML for right-side buttons
 *
 * Compatible with notifications.js (injects notification bell) and tutorial.js (references .top-navbar).
 */

$nav_title   = $nav_title   ?? 'BUZZ Data Manager';
$nav_subtitle = $nav_subtitle ?? '';
$nav_actions  = $nav_actions  ?? '';
?>
<nav class="top-navbar">
    <div class="top-navbar__title-block">
        <button class="top-navbar__toggle" id="sidebarToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="top-navbar__heading"><?php echo htmlspecialchars($nav_title); ?></h1>
        <?php if ($nav_subtitle !== ''): ?>
        <span class="top-navbar__subtitle">/ <?php echo htmlspecialchars($nav_subtitle); ?></span>
        <?php endif; ?>
    </div>
    <div class="top-navbar__actions" style="display: flex; align-items: center; gap: 12px;">
        <?php if (!empty($_SESSION['is_demo'])): ?>
        <div class="demo-trial-badge d-none d-sm-inline-flex align-items-center gap-2 px-2 py-1 rounded" style="background: rgba(255, 160, 0, 0.12); border: 1px solid rgba(255, 160, 0, 0.3); font-size: 0.72rem; color: var(--buzz-primary-color, #ffa000);">
            <i class="fas fa-hourglass-half"></i>
            <span>Prueba: <strong><?php echo (int)($_SESSION['demo_dias_restantes'] ?? 0); ?> días restantes</strong></span>
        </div>
        <?php endif; ?>
        <?php echo $nav_actions; ?>
    </div>
</nav>
