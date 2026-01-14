/**
 * BuzzDM Notification Hub Logic
 * Handles polling, UI updates, and interactions for the notification system.
 */

// Determine base path for API calls (works from root or subdirectories)
const notifApiBase = (() => {
    const path = window.location.pathname;
    // If we're in /php/ directory, go up one level
    if (path.includes('/php/')) {
        return '../php/';
    }
    // Default: relative to root
    return 'php/';
})();

document.addEventListener('DOMContentLoaded', () => {
    const hub = document.getElementById('notificationHub');
    const closeBtn = document.getElementById('closeHub');
    const notifList = document.getElementById('notifList');
    const markAllReadBtn = document.getElementById('markAllRead');
    const clearHistoryBtn = document.getElementById('clearHistory');
    const overlay = document.getElementById('notificationOverlay');

    // Inject navbar action buttons (info + bell)
    injectNavbarButtons();

    // Event Listeners
    if (closeBtn) closeBtn.onclick = closeHub;
    if (markAllReadBtn) markAllReadBtn.onclick = () => performAction('mark_read', 'all');
    if (clearHistoryBtn) clearHistoryBtn.onclick = () => performAction('delete', 'all_read');

    // Overlay closes the hub
    if (overlay) overlay.onclick = closeHub;

    // Initial Fetch & Polling (every 60s)
    fetchNotifications();
    setInterval(fetchNotifications, 60000);
});

function injectNavbarButtons() {
    // Find the top navbar
    const topNavbar = document.querySelector('.top-navbar');
    if (!topNavbar) return;

    // Check if buttons already exist
    if (document.getElementById('notifBell')) return;

    // Strategy: Find or create a container for our utility buttons
    // Look for an existing actions container (second div in navbar that has buttons)
    let actionsContainer = null;

    // Get all direct children that could be containers
    const navChildren = topNavbar.querySelectorAll(':scope > div, :scope > nav');

    for (const child of navChildren) {
        // Skip the title container (has h1)
        if (child.querySelector('h1')) continue;

        // Found a potential actions container
        actionsContainer = child;
        break;
    }

    // If no actions container exists, create one
    if (!actionsContainer) {
        actionsContainer = document.createElement('div');
        actionsContainer.className = 'd-flex align-items-center navbar-utils';
        topNavbar.appendChild(actionsContainer);
    } else {
        // Ensure it has proper flexbox classes for consistent spacing
        if (!actionsContainer.classList.contains('d-flex')) {
            actionsContainer.classList.add('d-flex', 'align-items-center');
        }
    }

    // Create info/manual button
    const infoBtn = document.createElement('a');
    infoBtn.className = 'navbar-icon-btn navbar-info-btn';
    infoBtn.id = 'navInfoBtn';
    infoBtn.href = 'manual.php';
    infoBtn.title = 'Manual de Usuario';
    infoBtn.innerHTML = '<i class="fas fa-info-circle"></i>';

    // Create notification bell button
    const bellBtn = document.createElement('button');
    bellBtn.className = 'navbar-icon-btn notification-bell-btn';
    bellBtn.id = 'notifBell';
    bellBtn.type = 'button';
    bellBtn.title = 'Notificaciones';
    bellBtn.innerHTML = '<i class="fas fa-bell"></i><span class="notification-badge" style="display:none">0</span>';
    bellBtn.onclick = toggleHub;

    // Append buttons: Info first, then Bell
    actionsContainer.appendChild(infoBtn);
    actionsContainer.appendChild(bellBtn);
}

function openHub() {
    const hub = document.getElementById('notificationHub');
    const overlay = document.getElementById('notificationOverlay');

    if (hub) {
        hub.classList.add('open');
        document.body.classList.add('notif-hub-open');
    }
    if (overlay) {
        overlay.classList.add('active');
    }

    // Re-fetch when opening
    fetchNotifications();
}

function closeHub() {
    const hub = document.getElementById('notificationHub');
    const overlay = document.getElementById('notificationOverlay');

    if (hub) {
        hub.classList.remove('open');
        document.body.classList.remove('notif-hub-open');
    }
    if (overlay) {
        overlay.classList.remove('active');
    }
}

function toggleHub() {
    const hub = document.getElementById('notificationHub');
    if (hub && hub.classList.contains('open')) {
        closeHub();
    } else {
        openHub();
    }
}

async function fetchNotifications() {
    try {
        const response = await fetch(notifApiBase + 'api_notifications.php?action=fetch', {
            credentials: 'same-origin' // Ensure session cookies are sent
        });

        // Handle non-OK responses gracefully
        if (!response.ok) {
            console.warn('Notifications API returned:', response.status);
            return;
        }

        const data = await response.json();

        if (data.success) {
            updateBadge(data.unread_count);
            renderNotifications(data.notifications);
        }
    } catch (error) {
        console.error("Error fetching notifications:", error);
    }
}

function updateBadge(count) {
    const badge = document.querySelector('.notification-badge');
    const bell = document.querySelector('.notification-bell-btn');
    if (!badge || !bell) return;

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'block';
        bell.classList.add('has-unread');
    } else {
        badge.style.display = 'none';
        bell.classList.remove('has-unread');
    }
}

function renderNotifications(notifications) {
    const list = document.getElementById('notifList');
    if (!list) return;

    list.innerHTML = '';

    if (notifications.length === 0) {
        list.innerHTML = `
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No tienes notificaciones</p>
            </div>
        `;
        return;
    }

    notifications.forEach(notif => {
        const item = document.createElement('div');
        item.className = `notif-item type-${notif.tipo} ${notif.leido == 0 ? 'unread' : ''}`;

        // Icon logic
        let icon = 'fa-info-circle';
        if (notif.tipo === 'success') icon = 'fa-check-circle';
        if (notif.tipo === 'error') icon = 'fa-exclamation-triangle';
        if (notif.tipo === 'warning') icon = 'fa-exclamation-circle';
        if (notif.tipo === 'task_assign') icon = 'fa-tasks';
        if (notif.tipo === 'security') icon = 'fa-shield-alt';

        const timeAgo = timeSince(new Date(notif.creado_at));

        item.innerHTML = `
            <div style="display:flex; align-items:flex-start">
                 <div class="notif-icon"><i class="fas ${icon}"></i></div>
                 <div class="notif-content">
                     <div class="notif-title">${escapeHtml(notif.titulo)}</div>
                     <div class="notif-message">${escapeHtml(notif.mensaje)}</div>
                     <small class="notif-time">${timeAgo}</small>
                 </div>
                 ${notif.leido == 0 ? '<div style="width:8px; height:8px; background:var(--notif-blue, #00f2ff); border-radius:50%; margin-top:6px; flex-shrink:0;"></div>' : ''}
            </div>
        `;

        item.onclick = (e) => {
            // Navigate if there's a link
            if (notif.enlace) {
                window.location.href = notif.enlace;
            }
            // Mark as read
            if (notif.leido == 0) {
                markAsRead(notif.id);
            }
        };

        list.appendChild(item);
    });
}

async function markAsRead(id) {
    await performAction('mark_read', id);
    fetchNotifications(); // Refresh UI
}

async function performAction(action, id) {
    try {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('id', id);

        await fetch(notifApiBase + 'api_notifications.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Ensure session cookies are sent
        });

        fetchNotifications();
    } catch (e) {
        console.error(e);
    }
}

// Utility: Time Ago
function timeSince(date) {
  const seconds = Math.floor((new Date() - date) / 1000);
  let interval = seconds / 31536000;
  if (interval > 1) return Math.floor(interval) + " años";
  interval = seconds / 2592000;
  if (interval > 1) return Math.floor(interval) + " meses";
  interval = seconds / 86400;
  if (interval > 1) return Math.floor(interval) + " días";
  interval = seconds / 3600;
  if (interval > 1) return Math.floor(interval) + " horas";
  interval = seconds / 60;
  if (interval > 1) return Math.floor(interval) + " minutos";
  return Math.floor(seconds) + " segundos";
}

// Utility: Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
