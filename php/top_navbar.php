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
    <div class="d-flex align-items-center">
        <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="font-primary mb-0"><?php echo htmlspecialchars($nav_title); ?></h1>
        <?php if ($nav_subtitle !== ''): ?>
        <span class="text-muted ms-2 font-mono">/ <?php echo htmlspecialchars($nav_subtitle); ?></span>
        <?php endif; ?>
    </div>
    <?php if ($nav_actions !== ''): ?>
    <div class="d-flex align-items-center gap-2">
        <?php echo $nav_actions; ?>
    </div>
    <?php endif; ?>
</nav>
