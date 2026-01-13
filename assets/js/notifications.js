/**
 * BuzzDM Notification Hub Logic
 * Handles polling, UI updates, and interactions for the notification system.
 */

document.addEventListener('DOMContentLoaded', () => {
    const hub = document.getElementById('notificationHub');
    const closeBtn = document.getElementById('closeHub');
    const notifList = document.getElementById('notifList');
    const markAllReadBtn = document.getElementById('markAllRead');
    const clearHistoryBtn = document.getElementById('clearHistory');

    // Inject Bell Icon
    injectBellIcon();

    // Event Listeners
    if (closeBtn) closeBtn.onclick = toggleHub;
    if (markAllReadBtn) markAllReadBtn.onclick = () => performAction('mark_read', 'all');
    if (clearHistoryBtn) clearHistoryBtn.onclick = () => performAction('delete', 'all_read');

    // Initial Fetch & Polling (every 60s)
    fetchNotifications();
    setInterval(fetchNotifications, 60000);
});

function injectBellIcon() {
    // Try to find the top navbar actions area
    const topNavbar = document.querySelector('.top-navbar > div'); // Adjust selector based on layout
    // Or maybe a specific container. If not found, create a floating one.

    let container = topNavbar;
    let isFloating = false;

    if (!container) {
        // Fallback: Fixed position
        /*
        container = document.createElement('div');
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px'; // Conflict with toasts? Move left or adjust.
        container.style.zIndex = '9990';
        document.body.appendChild(container);
        isFloating = true;
        */

       // Ideally, sidebar.php should have provided a hook, but let's try to append to the .top-navbar if it exists outside the div
       const nav = document.querySelector('.top-navbar');
       if (nav) {
           container = nav.querySelector('div') || nav;
       } else {
           // Last resort: Fixed floating button bottom-left or simply don't show (but we must)
           return; // User has to ensure top-navbar exists.
       }
    }

    const btn = document.createElement('button');
    btn.className = 'notification-bell-btn';
    btn.id = 'notifBell';
    btn.innerHTML = '<i class="fas fa-bell"></i><span class="notification-badge" style="display:none">0</span>';
    btn.onclick = toggleHub;

    // Prepend to be the first item or append?
    // In top-navbar usually: [Toggle Sidebar] [Title] ..... [Actions]
    // If container is flex, appending puts it at the end.
    container.appendChild(btn);
}

function toggleHub() {
    const hub = document.getElementById('notificationHub');
    const isActive = hub.classList.contains('open');
    if (isActive) {
        hub.classList.remove('open');
    } else {
        hub.classList.add('open');
        // Re-fetch when opening
        fetchNotifications();
    }
}

async function fetchNotifications() {
    try {
        const response = await fetch('php/api_notifications.php?action=fetch');
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
                     <div class="notif-title">${notif.titulo}</div>
                     <div class="notif-message">${notif.mensaje}</div>
                     <small class="notif-time">${timeAgo}</small>
                 </div>
                 ${notif.leido == 0 ? '<div style="width:8px; height:8px; background:var(--tactical-blue); border-radius:50%; margin-top:6px;"></div>' : ''}
            </div>
        `;

        item.onclick = (e) => {
            // If click on checking/action buttons logic (if any)
            if (notif.enlace) {
                window.location.href = notif.enlace;
            }
            if (notif.leido == 0) {
                markAsRead(notif.id);
            }
        };

        // Add long press or separate delete button logic?
        // Simplicity: Right click to delete? Or hover actions?
        // For now, simple click reads it.

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

        await fetch('php/api_notifications.php', {
            method: 'POST',
            body: formData
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
