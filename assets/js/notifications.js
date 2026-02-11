/**
 * BuzzDM Notification Hub Logic
 * Handles polling, UI updates, and interactions for the notification system.
 */

// Determine base path for API calls (works from root or subdirectories)
const notifApiBase = (() => {
    const path = window.location.pathname;
    // Extract base path up to and including BuzzDM (or similar app folder)
    const match = path.match(/^(\/[^\/]+\/)/);
    if (match) {
        return match[1] + 'php/';
    }
    // Fallback for direct access
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

    // Check if already injected
    if (document.getElementById('notifBell') || document.getElementById('mobileBottomNav')) return;

    const isMobile = window.matchMedia('(max-width: 768px)').matches;

    if (isMobile) {
        injectMobileLayout(topNavbar);
    } else {
        injectDesktopButtons(topNavbar);
    }
}

/**
 * Mobile layout: logo in header + bottom nav bar
 */
function injectMobileLayout(topNavbar) {
    // 1. Inject logo into header (top-right)
    const logo = document.createElement('img');
    logo.src = 'assets/img/logov1_icon.png';
    logo.alt = 'BuzzDM';
    logo.className = 'navbar-mobile-logo';
    topNavbar.appendChild(logo);

    // 2. Find page-specific primary action button (e.g. "+ Nuevo Pedido")
    const headerPrimaryBtn = topNavbar.querySelector('.btn-buzz');
    let primaryAction = null;
    if (headerPrimaryBtn) {
        primaryAction = {
            icon: 'fa-plus',
            onclick: headerPrimaryBtn.getAttribute('onclick') || null,
            id: headerPrimaryBtn.id || null
        };
    }

    // 3. Detect current page for "active" state
    const currentPath = window.location.pathname.split('/').pop().replace('.php', '');
    const isDashboard = currentPath === 'dashboard' || currentPath === '' || currentPath === 'index';

    // 4. Build bottom nav bar
    const nav = document.createElement('nav');
    nav.className = 'mobile-bottom-nav';
    nav.id = 'mobileBottomNav';

    // --- Item 1: Manual ---
    const manualItem = document.createElement('a');
    manualItem.href = 'manual';
    manualItem.className = 'bottom-nav-item' + (currentPath === 'manual' ? ' active' : '');
    manualItem.innerHTML = '<i class="fas fa-book"></i><span>Manual</span>';

    // --- Item 2: Notifications ---
    const notifItem = document.createElement('button');
    notifItem.className = 'bottom-nav-item';
    notifItem.id = 'notifBell';
    notifItem.type = 'button';
    notifItem.onclick = toggleHub;
    notifItem.innerHTML = '<i class="fas fa-bell"></i><span>Alertas</span>';

    // --- Item 3: Primary action (if exists) ---
    let primaryItem = null;
    if (primaryAction) {
        primaryItem = document.createElement('button');
        primaryItem.className = 'bottom-nav-item nav-primary';
        primaryItem.type = 'button';
        primaryItem.innerHTML = '<i class="fas ' + primaryAction.icon + '"></i><span>Nuevo</span>';
        if (primaryAction.onclick) {
            primaryItem.setAttribute('onclick', primaryAction.onclick);
        } else if (primaryAction.id) {
            // Trigger click on the original hidden button
            primaryItem.addEventListener('click', function() {
                const origBtn = document.getElementById(primaryAction.id);
                if (origBtn) origBtn.click();
            });
        }
    }

    // --- Item 4: Home ---
    const homeItem = document.createElement('a');
    homeItem.href = 'dashboard.php';
    homeItem.className = 'bottom-nav-item' + (isDashboard ? ' active' : '');
    homeItem.innerHTML = '<i class="fas fa-home"></i><span>Inicio</span>';

    // Assemble: Manual, Notif, [Primary], Home
    nav.appendChild(manualItem);
    nav.appendChild(notifItem);
    if (primaryItem) nav.appendChild(primaryItem);
    nav.appendChild(homeItem);

    document.body.appendChild(nav);
}

/**
 * Desktop layout: buttons in header (original behavior)
 */
function injectDesktopButtons(topNavbar) {
    // Strategy: Find or create a container for utility buttons
    let actionsContainer = null;

    const navChildren = topNavbar.querySelectorAll(':scope > div, :scope > nav');

    for (const child of navChildren) {
        if (child.querySelector('h1')) continue;
        actionsContainer = child;
        break;
    }

    if (!actionsContainer) {
        actionsContainer = document.createElement('div');
        actionsContainer.className = 'd-flex align-items-center navbar-utils';
        topNavbar.appendChild(actionsContainer);
    } else {
        if (!actionsContainer.classList.contains('d-flex')) {
            actionsContainer.classList.add('d-flex', 'align-items-center');
        }
    }

    // Create info/manual button
    const infoBtn = document.createElement('a');
    infoBtn.className = 'navbar-icon-btn navbar-info-btn';
    infoBtn.id = 'navInfoBtn';
    infoBtn.href = 'manual';
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
    // Desktop badge (span inside bell button)
    const badge = document.querySelector('.notification-badge');
    const bell = document.querySelector('.notification-bell-btn');
    if (badge && bell) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
            bell.classList.add('has-unread');
        } else {
            badge.style.display = 'none';
            bell.classList.remove('has-unread');
        }
    }

    // Mobile bottom nav badge
    const mobileNotifBtn = document.querySelector('#mobileBottomNav #notifBell');
    if (mobileNotifBtn) {
        // Remove existing badge
        const existing = mobileNotifBtn.querySelector('.bottom-nav-badge');
        if (existing) existing.remove();

        if (count > 0) {
            const mobileBadge = document.createElement('span');
            mobileBadge.className = 'bottom-nav-badge';
            mobileBadge.textContent = count > 99 ? '99+' : count;
            mobileNotifBtn.appendChild(mobileBadge);
        }
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
