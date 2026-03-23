/**
 * ajustes.js - Security Settings & System Info
 * Extracted from admin.js to support the standalone Ajustes page
 */

;(function () {
  'use strict'

  document.addEventListener('DOMContentLoaded', () => {
    initSecuritySettings()
  })

  // =========================================
  // Two-Factor Authentication (2FA)
  // =========================================

  async function load2FAStatus() {
    try {
      const response = await fetch('php/api_2fa.php')
      const data = await response.json()

      const statusBadge = document.getElementById('twoFactorStatus')
      const disabledArea = document.getElementById('twoFactorDisabledArea')
      const setupArea = document.getElementById('twoFactorSetupArea')
      const enabledArea = document.getElementById('twoFactorEnabledArea')

      if (!statusBadge) return

      // Hide all areas first
      if (disabledArea) disabledArea.style.display = 'none'
      if (setupArea) setupArea.style.display = 'none'
      if (enabledArea) enabledArea.style.display = 'none'

      if (data.data && data.data.is_enabled) {
        statusBadge.className = 'badge bg-success'
        statusBadge.textContent = 'Activado'
        if (enabledArea) enabledArea.style.display = 'block'
      } else {
        statusBadge.className = 'badge bg-warning text-dark'
        statusBadge.textContent = 'Desactivado'
        if (disabledArea) disabledArea.style.display = 'block'

        // Cache the 2FA setup data for when user clicks to configure
        if (data.data && data.data.qr_svg) {
          window._2faSetupData = {
            qr_svg: data.data.qr_svg,
            secret: data.data.secret
          }
        }
      }
    } catch (error) {
      console.error('Error loading 2FA status:', error)
      const statusBadge = document.getElementById('twoFactorStatus')
      if (statusBadge) {
        statusBadge.className = 'badge bg-danger'
        statusBadge.textContent = 'Error'
      }
    }
  }

  function start2FASetup() {
    const disabledArea = document.getElementById('twoFactorDisabledArea')
    const setupArea = document.getElementById('twoFactorSetupArea')
    const qrContainer = document.getElementById('twoFactorQR')
    const secretCode = document.getElementById('twoFactorSecretCode')
    const manualContainer = document.getElementById('manualCodeContainer')

    if (disabledArea) disabledArea.style.display = 'none'
    if (setupArea) setupArea.style.display = 'block'
    if (manualContainer) manualContainer.classList.add('d-none')

    // Display the cached QR code
    if (window._2faSetupData) {
      if (qrContainer) {
        qrContainer.innerHTML = atob(window._2faSetupData.qr_svg)
      }
      if (secretCode) {
        secretCode.textContent = window._2faSetupData.secret
      }
    }

    // Clear the verification input
    const verifyInput = document.getElementById('twoFactorVerifyCode')
    if (verifyInput) verifyInput.value = ''
  }

  function cancel2FASetup() {
    const disabledArea = document.getElementById('twoFactorDisabledArea')
    const setupArea = document.getElementById('twoFactorSetupArea')

    if (setupArea) setupArea.style.display = 'none'
    if (disabledArea) disabledArea.style.display = 'block'

    // Clear the verification input
    const verifyInput = document.getElementById('twoFactorVerifyCode')
    if (verifyInput) verifyInput.value = ''
  }

  function showManualCode(e) {
    e.preventDefault()
    const container = document.getElementById('manualCodeContainer')
    if (container) {
      container.classList.toggle('d-none')
    }
  }

  async function enable2FA() {
    const code = document.getElementById('twoFactorVerifyCode')?.value || ''

    if (!/^\d{6}$/.test(code)) {
      showToast('Ingresa un código de 6 dígitos', 'warning')
      return
    }

    try {
      const response = await fetch('php/api_2fa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'enable', code })
      })
      const data = await response.json()

      if (data.success) {
        showToast(data.message || '2FA activado exitosamente', 'success')
        load2FAStatus()
      } else {
        showToast(data.message || 'Error al activar 2FA', 'error')
      }
    } catch (error) {
      console.error('Error enabling 2FA:', error)
      showToast('Error de conexión', 'error')
    }
  }

  async function disable2FA() {
    const password = prompt('Ingresa tu contraseña para desactivar 2FA:')

    if (!password) return

    try {
      const response = await fetch('php/api_2fa.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'disable', password })
      })
      const data = await response.json()

      if (data.success) {
        showToast(data.message || '2FA desactivado', 'success')
        load2FAStatus()
      } else {
        showToast(data.message || 'Error al desactivar 2FA', 'error')
      }
    } catch (error) {
      console.error('Error disabling 2FA:', error)
      showToast('Error de conexión', 'error')
    }
  }

  // =========================================
  // Session Timeout - Uses global SessionTimeout API
  // =========================================

  function initSessionTimeout() {
    const toggle = document.getElementById('sessionTimeout')
    const minutesSelect = document.getElementById('sessionTimeoutMinutes')

    // Load saved settings from global API or localStorage
    const settings = window.SessionTimeout ? window.SessionTimeout.getSettings() : {
      enabled: localStorage.getItem('sessionTimeoutEnabled') !== 'false',
      minutes: parseInt(localStorage.getItem('sessionTimeoutMinutes') || '30', 10)
    }

    if (toggle) toggle.checked = settings.enabled
    if (minutesSelect) minutesSelect.value = settings.minutes

    // Event listeners - use global API if available
    if (toggle) {
      toggle.addEventListener('change', function() {
        if (window.SessionTimeout) {
          window.SessionTimeout.setEnabled(this.checked)
        } else {
          localStorage.setItem('sessionTimeoutEnabled', this.checked)
        }
        showToast(this.checked ? 'Timeout de sesión activado' : 'Timeout de sesión desactivado', this.checked ? 'success' : 'info')
      })
    }

    if (minutesSelect) {
      minutesSelect.addEventListener('change', function() {
        if (window.SessionTimeout) {
          window.SessionTimeout.setMinutes(parseInt(this.value, 10))
        } else {
          localStorage.setItem('sessionTimeoutMinutes', this.value)
        }
        showToast(`Timeout configurado a ${this.value} minutos`, 'info')
      })
    }
  }

  // =========================================
  // Init - called on page load
  // =========================================

  function initSecuritySettings() {
    load2FAStatus()
    initSessionTimeout()

    // 2FA buttons
    const start2FASetupBtn = document.getElementById('start2FASetupBtn')
    const cancel2FASetupBtn = document.getElementById('cancel2FASetupBtn')
    const showManualCodeLink = document.getElementById('showManualCode')
    const enable2FABtn = document.getElementById('enable2FABtn')
    const disable2FABtn = document.getElementById('disable2FABtn')

    if (start2FASetupBtn) {
      start2FASetupBtn.addEventListener('click', start2FASetup)
    }
    if (cancel2FASetupBtn) {
      cancel2FASetupBtn.addEventListener('click', cancel2FASetup)
    }
    if (showManualCodeLink) {
      showManualCodeLink.addEventListener('click', showManualCode)
    }
    if (enable2FABtn) {
      enable2FABtn.addEventListener('click', enable2FA)
    }
    if (disable2FABtn) {
      disable2FABtn.addEventListener('click', disable2FA)
    }

    const verifyCodeInput = document.getElementById('twoFactorVerifyCode')
    if (verifyCodeInput) {
      verifyCodeInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6)
      })
    }
  }
})()
