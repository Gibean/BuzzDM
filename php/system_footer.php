<?php
/**
 * System Footer Component
 * Displays system information: date, time, last update, and version
 */

// Get current time for display
$currentTime = date('H:i');
$currentDate = date('d/m/Y');
$lastUpdate = date('H:i');
$systemVersion = 'v1.3.2.0';
?>
<footer class="system-footer">
    <div class="system-footer-left">
        <div class="system-footer-item">
            <i class="fas fa-calendar-alt"></i>
            <span class="footer-value" id="footerDate"><?php echo $currentDate; ?></span>
        </div>
        <div class="system-footer-item">
            <i class="fas fa-clock"></i>
            <span class="footer-value" id="footerTime"><?php echo $currentTime; ?></span>
        </div>
        <div class="system-footer-item hide-mobile">
            <i class="fas fa-sync-alt"></i>
            <span class="footer-label">Últ. Act:</span>
            <span class="footer-value" id="footerLastUpdate"><?php echo $lastUpdate; ?></span>
        </div>
    </div>
    <div class="system-footer-right">
        <div class="system-footer-item">
            <i class="fas fa-code-branch"></i>
            <span class="footer-value"><?php echo $systemVersion; ?></span>
        </div>
        <div class="status-indicator">
            <div class="status-dot"></div>
            <span class="status-text">Online</span>
        </div>
    </div>
</footer>

<script>
// Update time every second
function updateFooterTime() {
    const now = new Date();
    const timeElement = document.getElementById('footerTime');
    const dateElement = document.getElementById('footerDate');

    if (timeElement) {
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        timeElement.textContent = `${hours}:${minutes}`;
    }

    if (dateElement) {
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const year = now.getFullYear();
        dateElement.textContent = `${day}/${month}/${year}`;
    }
}

// Update last update time
function updateLastUpdateTime() {
    const lastUpdateElement = document.getElementById('footerLastUpdate');
    if (lastUpdateElement) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        lastUpdateElement.textContent = `${hours}:${minutes}`;
    }
}

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        updateFooterTime();
        setInterval(updateFooterTime, 1000);
    });
} else {
    updateFooterTime();
    setInterval(updateFooterTime, 1000);
}
</script>

<!-- Global Tutorial Mode Dependencies -->
<link rel="stylesheet" href="assets/css/driver.css">
<link rel="stylesheet" href="assets/css/driver-buzz.css">
<script src="assets/js/driver.js"></script>
<script src="assets/js/tutorial.js"></script>
