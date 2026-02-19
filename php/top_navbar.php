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
    <?php if ($nav_actions !== ''): ?>
    <div class="top-navbar__actions">
        <?php echo $nav_actions; ?>
    </div>
    <?php endif; ?>
</nav>
