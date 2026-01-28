/**
 * Global Session Timeout Manager
 * Monitors user activity and auto-logs out after inactivity period
 * Settings are stored in localStorage and configurable from Admin > Config
 */

(function() {
  'use strict';

  // Configuration
  const STORAGE_KEY_ENABLED = 'sessionTimeoutEnabled';
  const STORAGE_KEY_MINUTES = 'sessionTimeoutMinutes';
  const WARNING_BEFORE_MS = 60000; // 1 minute warning

  // State
  let timeoutId = null;
  let warningId = null;
  let countdownInterval = null;

  /**
   * Get settings from localStorage
   */
  function getSettings() {
    return {
      enabled: localStorage.getItem(STORAGE_KEY_ENABLED) !== 'false', // Default: enabled
      minutes: parseInt(localStorage.getItem(STORAGE_KEY_MINUTES) || '30', 10)
    };
  }

  /**
   * Start the session timeout timer
   */
  function startTimeout() {
    clearTimeout();

    const settings = getSettings();
    if (!settings.enabled) return;

    const timeoutMs = settings.minutes * 60 * 1000;

    // Set warning timer (1 minute before logout)
    warningId = window.setTimeout(() => {
      showWarningModal();
    }, timeoutMs - WARNING_BEFORE_MS);

    // Set logout timer
    timeoutId = window.setTimeout(() => {
      performLogout();
    }, timeoutMs);
  }

  /**
   * Clear all timers
   */
  function clearTimeout() {
    if (timeoutId) {
      window.clearTimeout(timeoutId);
      timeoutId = null;
    }
    if (warningId) {
      window.clearTimeout(warningId);
      warningId = null;
    }
    if (countdownInterval) {
      window.clearInterval(countdownInterval);
      countdownInterval = null;
    }
  }

  /**
   * Reset timeout on user activity
   */
  function resetTimeout() {
    const settings = getSettings();
    if (settings.enabled) {
      startTimeout();
    }
  }

  /**
   * Show warning modal before logout
   */
  function showWarningModal() {
    // Remove existing modal if present
    const existing = document.getElementById('sessionTimeoutWarningModal');
    if (existing) existing.remove();

    // Create modal
    const modal = document.createElement('div');
    modal.id = 'sessionTimeoutWarningModal';
    modal.innerHTML = `
      <div style="
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.85);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.3s ease;
      ">
        <div style="
          background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
          border: 1px solid rgba(255,160,0,0.3);
          border-radius: 16px;
          padding: 40px;
          max-width: 400px;
          text-align: center;
          box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        ">
          <i class="fas fa-clock" style="font-size: 3rem; color: #ffc107; margin-bottom: 20px;"></i>
          <h4 style="color: #fff; margin-bottom: 15px;">Sesión por expirar</h4>
          <p style="color: #adb5bd; margin-bottom: 25px;">
            Tu sesión se cerrará en <strong id="sessionCountdownValue" style="color: #ffc107;">60</strong> segundos por inactividad.
          </p>
          <button id="sessionContinueBtn" style="
            background: linear-gradient(135deg, #ffa000 0%, #ff6f00 100%);
            color: #1a1a2e;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
          ">
            <i class="fas fa-check"></i> Continuar trabajando
          </button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    // Add styles for animation
    if (!document.getElementById('sessionTimeoutStyles')) {
      const style = document.createElement('style');
      style.id = 'sessionTimeoutStyles';
      style.textContent = `
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        #sessionContinueBtn:hover {
          transform: scale(1.05);
          box-shadow: 0 5px 20px rgba(255,160,0,0.4);
        }
      `;
      document.head.appendChild(style);
    }

    // Countdown
    let seconds = 60;
    const countdownEl = document.getElementById('sessionCountdownValue');
    countdownInterval = window.setInterval(() => {
      seconds--;
      if (countdownEl) countdownEl.textContent = seconds;
      if (seconds <= 0) {
        window.clearInterval(countdownInterval);
      }
    }, 1000);

    // Continue button
    document.getElementById('sessionContinueBtn').addEventListener('click', () => {
      closeWarningModal();
      resetTimeout();
      if (typeof showToast === 'function') {
        showToast('Sesión extendida', 'success');
      }
    });
  }

  /**
   * Close warning modal
   */
  function closeWarningModal() {
    const modal = document.getElementById('sessionTimeoutWarningModal');
    if (modal) modal.remove();
    if (countdownInterval) {
      window.clearInterval(countdownInterval);
      countdownInterval = null;
    }
  }

  /**
   * Calculate the base path for API calls
   */
  function getApiBasePath() {
    const path = window.location.pathname;
    // Extract base path (e.g. /BuzzDM/) from current URL
    const match = path.match(/^(\/[^\/]+\/)/);
    if (match) {
      return match[1] + 'php/';
    }
    return 'php/';
  }

  /**
   * Log the inactivity logout to the server
   */
  async function logInactivityLogout() {
    try {
      const settings = getSettings();
      const apiPath = getApiBasePath() + 'api_logs.php';

      await fetch(apiPath, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'log_inactivity_logout',
          minutes: settings.minutes
        }),
        credentials: 'same-origin'
      });
    } catch (error) {
      console.error('[SessionTimeout] Error logging inactivity:', error);
    }
  }

  /**
   * Perform logout due to inactivity
   */
  async function performLogout() {
    closeWarningModal();
    clearTimeout();

    // Log the inactivity logout
    await logInactivityLogout();

    // Show toast if available
    if (typeof showToast === 'function') {
      showToast('Sesión cerrada por inactividad', 'warning');
    }

    // Redirect to login after brief delay
    setTimeout(() => {
      window.location.href = getLoginUrl();
    }, 1500);
  }

  /**
   * Get correct login URL based on current path
   */
  function getLoginUrl() {
    const path = window.location.pathname;
    // Check if we're in a subdirectory (like /php/)
    if (path.includes('/php/')) {
      return '../login';
    }
    return 'login';
  }

  /**
   * Initialize event listeners for activity detection
   */
  function initActivityListeners() {
    // Throttle activity events to prevent excessive resets
    let lastActivity = Date.now();
    const throttleMs = 5000; // Only reset every 5 seconds max

    const handleActivity = () => {
      const now = Date.now();
      if (now - lastActivity > throttleMs) {
        lastActivity = now;
        resetTimeout();
      }
    };

    document.addEventListener('click', handleActivity);
    document.addEventListener('keypress', handleActivity);
    document.addEventListener('scroll', handleActivity);
    document.addEventListener('mousemove', handleActivity);
  }

  /**
   * Listen for settings changes from Admin panel
   */
  function listenForSettingsChanges() {
    // Watch for localStorage changes (from other tabs or Admin panel)
    window.addEventListener('storage', (event) => {
      if (event.key === STORAGE_KEY_ENABLED || event.key === STORAGE_KEY_MINUTES) {
        const settings = getSettings();
        if (settings.enabled) {
          startTimeout();
        } else {
          clearTimeout();
        }
      }
    });
  }

  /**
   * Expose API for Admin panel to control
   */
  window.SessionTimeout = {
    start: startTimeout,
    stop: clearTimeout,
    reset: resetTimeout,
    getSettings: getSettings,
    setEnabled: (enabled) => {
      localStorage.setItem(STORAGE_KEY_ENABLED, enabled);
      if (enabled) {
        startTimeout();
      } else {
        clearTimeout();
      }
    },
    setMinutes: (minutes) => {
      localStorage.setItem(STORAGE_KEY_MINUTES, minutes);
      const settings = getSettings();
      if (settings.enabled) {
        startTimeout();
      }
    }
  };

  /**
   * Initialize on DOM ready
   */
  function init() {
    const settings = getSettings();

    // Only initialize if enabled
    if (settings.enabled) {
      startTimeout();
    }

    initActivityListeners();
    listenForSettingsChanges();

    console.log('[SessionTimeout] Initialized - Enabled:', settings.enabled, '- Minutes:', settings.minutes);
  }

  // Start when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
