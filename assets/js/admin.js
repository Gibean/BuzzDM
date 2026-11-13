;(function () {
  // Admin functionality
  let users = []
  let logs = []
  let backups = []
  let currentTab = "users"
  let currentUserId = null

  function escapeHtml(text) {
    if (text == null) return ''
    const div = document.createElement("div")
    div.textContent = String(text)
    return div.innerHTML
  }

  document.addEventListener("DOMContentLoaded", () => {
    initAdmin()
    setupEventListeners()
  })

  function initAdmin() {
    switchTab("users")
    setupAdvancedFilters()
  }

  function setupEventListeners() {
    // Tab navigation
    document.querySelectorAll("[data-tab]").forEach((tab) => {
      tab.addEventListener("click", function () {
        switchTab(this.dataset.tab)
      })
    })

    const newUserBtn = document.getElementById("newUserBtn")
    if (newUserBtn) {
      newUserBtn.addEventListener("click", () => {
        openUserModal()
      })
    }

    // User form
    const userForm = document.getElementById("userForm")
    if (userForm) {
      userForm.addEventListener("submit", handleUserSubmit)
    }

    // Company Info Form
    const companyInfoForm = document.getElementById("companyInfoForm")
    if (companyInfoForm) {
      companyInfoForm.addEventListener("submit", saveCompanyInfo)
    }

    // Logo Upload
    const logoFileInput = document.getElementById("logoFileInput")
    const logoUploadArea = document.getElementById("logoUploadArea")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")
    const clearLogoFileBtn = document.getElementById("clearLogoFileBtn")

    if (logoFileInput && logoUploadArea && uploadLogoBtn && clearLogoFileBtn) {
      logoFileInput.addEventListener("change", handleLogoFileInputChange)
      uploadLogoBtn.addEventListener("click", uploadLogo)
      clearLogoFileBtn.addEventListener("click", clearLogoFile)

      // Drag and Drop for Logo Upload
      ;["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        logoUploadArea.addEventListener(eventName, preventDefaults, false)
      })
      ;["dragenter", "dragover"].forEach((eventName) => {
        logoUploadArea.addEventListener(eventName, highlight, false)
      })
      ;["dragleave", "drop"].forEach((eventName) => {
        logoUploadArea.addEventListener(eventName, unhighlight, false)
      })

      logoUploadArea.addEventListener("drop", handleDrop, false)
    }

    // User modal close event
    const userModal = document.getElementById("userModal")
    if (userModal) {
      userModal.addEventListener("click", function (e) {
        if (e.target === this) {
          closeUserModal()
        }
      })
    }

    // Reports Tab Logic - Multiple Selection Support
    const reportOptions = document.querySelectorAll(".report-option")
    const generateReportBtn = document.getElementById("generateReportBtn")
    const adminPasswordInput = document.getElementById("adminPasswordReport")
    const reportError = document.getElementById("reportError")
    let selectedReportTypes = [] // Changed to array for multiple selections

    if (
      reportOptions.length > 0 &&
      generateReportBtn &&
      adminPasswordInput &&
      reportError
    ) {
      console.log("Report button logic initialized:", {
        reportOptionsCount: reportOptions.length,
        hasButton: !!generateReportBtn,
        hasPasswordInput: !!adminPasswordInput
      });

      // Function to check if button should be enabled
      function checkReportButtonState() {
        const shouldEnable = selectedReportTypes.length > 0 && adminPasswordInput.value.trim() !== "";
        console.log("Checking button state:", {
          selectedReportTypes,
          passwordLength: adminPasswordInput.value.length,
          shouldEnable
        });

        if (shouldEnable) {
          generateReportBtn.disabled = false;
          // Update button text to show count
          const count = selectedReportTypes.length;
          generateReportBtn.innerHTML = `<i class="fas fa-download"></i> Generar ${count} Reporte${count > 1 ? 's' : ''}`;
        } else {
          generateReportBtn.disabled = true;
          generateReportBtn.innerHTML = '<i class="fas fa-download"></i> Generar Reporte';
        }
      }

      // Toggle selection on click
      reportOptions.forEach((option) => {
        option.addEventListener("click", () => {
          const reportType = option.dataset.reportType

          // Toggle selection
          if (option.classList.contains("selected-buzz")) {
            // Deselect
            option.classList.remove("selected-buzz")
            selectedReportTypes = selectedReportTypes.filter(type => type !== reportType)
          } else {
            // Select
            option.classList.add("selected-buzz")
            selectedReportTypes.push(reportType)
          }

          console.log("Selected report types:", selectedReportTypes);
          checkReportButtonState()
          reportError.textContent = ""
        })
      })

      // Enable button when password is typed (multiple events for reliability)
      adminPasswordInput.addEventListener("input", () => {
        console.log("Password input changed");
        checkReportButtonState()
      })

      adminPasswordInput.addEventListener("keyup", () => {
        console.log("Password keyup event");
        checkReportButtonState()
      })

      adminPasswordInput.addEventListener("change", () => {
        console.log("Password change event");
        checkReportButtonState()
      })

      generateReportBtn.addEventListener("click", () => {
        const password = adminPasswordInput.value

        if (selectedReportTypes.length === 0) {
          reportError.textContent = "Por favor, seleccione al menos un tipo de reporte."
          return
        }

        if (password.trim() === "") {
          reportError.textContent = "Por favor, ingrese la contraseña de administrador."
          return
        }

        const count = selectedReportTypes.length;
        generateReportBtn.innerHTML =
          `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando ${count} reporte${count > 1 ? 's' : ''}...`
        generateReportBtn.disabled = true

        // Use POST form submission to avoid password in URL (security)
        const typesParam = selectedReportTypes.join(',')

        // Create hidden form for POST submission
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'php/reports.php';
        form.target = '_blank';

        const tipoInput = document.createElement('input');
        tipoInput.type = 'hidden';
        tipoInput.name = 'tipo';
        tipoInput.value = typesParam;
        form.appendChild(tipoInput);

        const passwordInput = document.createElement('input');
        passwordInput.type = 'hidden';
        passwordInput.name = 'password';
        passwordInput.value = password;
        form.appendChild(passwordInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);

        // Reset after a short delay
        setTimeout(() => {
          resetReportButton()
        }, 2000)
      })

      function resetReportButton() {
        generateReportBtn.innerHTML =
          '<i class="fas fa-download"></i> Generar Reporte'
        generateReportBtn.disabled = true
        adminPasswordInput.value = ""
        reportOptions.forEach((opt) => opt.classList.remove("selected-buzz"))
        selectedReportTypes = []
        reportError.textContent = ""
      }

      // Password visibility toggle for admin report password
      const toggleAdminPasswordReport = document.getElementById("toggleAdminPasswordReport")
      const toggleAdminPasswordReportIcon = document.getElementById("toggleAdminPasswordReportIcon")

      if (toggleAdminPasswordReport && toggleAdminPasswordReportIcon) {
        toggleAdminPasswordReport.addEventListener("click", function () {
          const type = adminPasswordInput.getAttribute("type") === "password" ? "text" : "password"
          adminPasswordInput.setAttribute("type", type)

          // Toggle icon
          if (type === "text") {
            toggleAdminPasswordReportIcon.classList.remove("fa-eye")
            toggleAdminPasswordReportIcon.classList.add("fa-eye-slash")
          } else {
            toggleAdminPasswordReportIcon.classList.remove("fa-eye-slash")
            toggleAdminPasswordReportIcon.classList.add("fa-eye")
          }
        })

        // Change icon color on hover
        toggleAdminPasswordReport.addEventListener("mouseenter", function () {
          this.style.color = "var(--buzz-primary-color)"
        })
        toggleAdminPasswordReport.addEventListener("mouseleave", function () {
          this.style.color = "var(--buzz-text-muted)"
        })
      }
    }

    // Generic password visibility toggle for .btn-toggle-password buttons (user modal)
    document.querySelectorAll('.btn-toggle-password').forEach(btn => {
      btn.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target')
        const input = document.getElementById(targetId)
        if (!input) return

        const icon = this.querySelector('i')
        if (input.type === 'password') {
          input.type = 'text'
          icon.classList.remove('fa-eye')
          icon.classList.add('fa-eye-slash')
        } else {
          input.type = 'password'
          icon.classList.remove('fa-eye-slash')
          icon.classList.add('fa-eye')
        }
      })
    })

    // Expose functions to window for HTML onclick attributes
    window.filterLogs = () => loadLogs(1);
    window.clearLogsFilters = () => {
        document.getElementById("logSearch").value = "";
        document.getElementById("logDateFrom").value = "";
        document.getElementById("logDateTo").value = "";
        const logActionFilter = document.getElementById("logActionFilter");
        if (logActionFilter) logActionFilter.value = "";

        // Reset badge
        const activeBadge = document.getElementById("activeFiltersBadge");
        if(activeBadge) activeBadge.classList.add("d-none");

        loadLogs(1);
    };

    const prevLogBtn = document.getElementById('logsPrevPage')
    const nextLogBtn = document.getElementById('logsNextPage')

    if (prevLogBtn) {
        prevLogBtn.addEventListener('click', () => {
            if (currentLogPage > 1) loadLogs(currentLogPage - 1)
        })
    }

    if (nextLogBtn) {
        nextLogBtn.addEventListener('click', () => {
            loadLogs(currentLogPage + 1)
        })
    }

    const logFilterButton = document.querySelector("#logsTab button[onclick='filterLogs()']")
    if (logFilterButton) {
      // Remove onclick attribute to avoid double firing if we attach listener
      // logFilterButton.removeAttribute("onclick");
      // Actually, since we exposed window.filterLogs, the onclick works.
      // We don't need this listener if onclick is present.
      // But let's keep it safe.
    }
  }

// Advanced filters toggle logic for Logs
function setupAdvancedFilters() {
    const toggleBtn = document.getElementById("toggleAdvancedFilters");
    const advancedCollapse = document.getElementById("advancedFiltersCollapse");
    const activeBadge = document.getElementById("activeFiltersBadge");

    if (toggleBtn && advancedCollapse) {
        // Restore state from localStorage
        const isAdvVisible = localStorage.getItem("advFiltersVisible_admin_logs") === "true";
        if (isAdvVisible) {
            advancedCollapse.classList.remove("d-none");
            toggleBtn.classList.add("active");
        }

         // Initial count update
        updateActiveFiltersCount();

        toggleBtn.addEventListener("click", () => {
            const isHidden = advancedCollapse.classList.contains("d-none");
            if (isHidden) {
                advancedCollapse.classList.remove("d-none");
                toggleBtn.classList.add("active");
                localStorage.setItem("advFiltersVisible_admin_logs", "true");
            } else {
                advancedCollapse.classList.add("d-none");
                toggleBtn.classList.remove("active");
                localStorage.setItem("advFiltersVisible_admin_logs", "false");
            }
        });
    }

    // Attach listener to advanced filters to update badge
    const logDateFrom = document.getElementById("logDateFrom");
    const logDateTo = document.getElementById("logDateTo");
    const logActionFilter = document.getElementById("logActionFilter");

    if(logDateFrom) logDateFrom.addEventListener('change', updateActiveFiltersCount);
    if(logDateTo) logDateTo.addEventListener('change', updateActiveFiltersCount);
    if(logActionFilter) logActionFilter.addEventListener('change', updateActiveFiltersCount);
}

function updateActiveFiltersCount() {
    const activeBadge = document.getElementById("activeFiltersBadge");
    if (!activeBadge) return;

    let activeFilters = 0;
    // Only count filters in the advanced section
    const logDateFrom = document.getElementById("logDateFrom");
    const logDateTo = document.getElementById("logDateTo");
    const logActionFilter = document.getElementById("logActionFilter");

    if (logDateFrom && logDateFrom.value !== "") activeFilters++;
    if (logDateTo && logDateTo.value !== "") activeFilters++;
    if (logActionFilter && logActionFilter.value !== "") activeFilters++;

    if (activeFilters > 0) {
        activeBadge.textContent = activeFilters;
        activeBadge.classList.remove("d-none");
    } else {
        activeBadge.classList.add("d-none");
    }
}

  function preventDefaults(e) {
    e.preventDefault()
    e.stopPropagation()
  }

  function highlight() {
    document.getElementById("logoUploadArea").classList.add("highlight")
  }

  function unhighlight() {
    document.getElementById("logoUploadArea").classList.remove("highlight")
  }

  function handleDrop(e) {
    const dt = e.dataTransfer
    const files = dt.files
    document.getElementById("logoFileInput").files = files
    handleLogoFileInputChange()
  }

  function switchTab(tabName) {
  // Update active tab - only toggle 'active' class (CSS handles styling)
  document.querySelectorAll("[data-tab]").forEach((tab) => {
    tab.classList.remove("active")
  })

  const activeTab = document.querySelector(`[data-tab="${tabName}"]`)
  if (activeTab) {
    activeTab.classList.add("active")
  }
    // Hide all tab contents
    document.querySelectorAll(".tab-content").forEach((content) => {
      content.style.display = "none"
    })

    // Show selected tab
    const selectedTab = document.getElementById(`${tabName}Tab`)
    if (selectedTab) {
      selectedTab.style.display = "block"
    }

    currentTab = tabName

    renderCurrentTab()
  }

  function renderCurrentTab() {
    switch (currentTab) {
      case "users":
        loadUsers()
        break
      case "logs":
        loadLogs()
        break
      case "backups":
        loadBackups()
        break
      case "reports":
        // Reports are static HTML, no specific render action needed
        break
      case "settings":
        loadCompanyInfo()
        break
    }
  }

  async function loadUsers() {
    try {
      const response = await fetchJSON("php/api_users.php")
      users = (response.users || []).filter(u => u.estado === 'activo')
      renderUsers()
      updateUserStats()
    } catch (error) {
      console.error(error)
      showModal({
        title: "Error",
        message: "Error al cargar usuarios",
        type: "error"
      })
    }
  }

  function renderUsers() {
    const tbody = document.getElementById("usersTable")
    if (!tbody) return

    tbody.innerHTML = users
      .map(
        (user) => `
        <tr>
            <td class="font-mono">${escapeHtml(user.nombre)}</td>
            <td class="font-mono">${escapeHtml(user.email)}</td>
            <td>
                <span class="badge badge-${
                  user.rol === "admin" ? "warning" : "primary"
                }">
                    ${user.rol}
                </span>
            </td>
            <td>
                <span class="badge badge-${
                  user.estado === "activo" ? "success" : "secondary"
                }">
                    ${user.estado}
                </span>
            </td>
            <td class="font-mono">${
              user.ultimo_acceso ? formatDateTime(user.ultimo_acceso) : "N/A"
            }</td>
            <td>
                <button class="btn-action btn-edit me-1" onclick="editUser(${
                  user.id
                })" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>

                ${
                  user.id !== 1
                    ? `
                    <button class="btn-action btn-delete" onclick="deleteUser(${user.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                `
                    : ""
                }
            </td>
        </tr>
    `
      )
      .join("")
  }

  async function updateUserStats() {
    const totalUsers = document.getElementById("totalUsers")
    if (!totalUsers) return

    const total = users.length
    const active = users.filter((u) => u.estado === "activo").length
    const admins = users.filter((u) => u.rol === "admin").length

    try {
      const sessionData = await fetchJSON("php/api_users.php?action=get_sessions")
      const todaySessions = sessionData?.session_count ?? 0
      document.getElementById("todaySessions").textContent = todaySessions
    } catch (error) {
      console.error("Error fetching sessions:", error)
      document.getElementById("todaySessions").textContent = "N/A"
    }

    totalUsers.textContent = total
    document.getElementById("activeUsers").textContent = active
    document.getElementById("adminUsers").textContent = admins
  }

  // Pagination State
  let currentLogPage = 1
  const LOGS_PER_PAGE = 10

  async function loadLogs(page = 1) {
    const logSearchEl = document.getElementById("logSearch")
    const logDateFromEl = document.getElementById("logDateFrom")
    const logDateToEl = document.getElementById("logDateTo")
    const logActionFilterEl = document.getElementById("logActionFilter")

    const logSearch = logSearchEl ? logSearchEl.value : ''
    const logDateFrom = logDateFromEl ? logDateFromEl.value : ''
    const logDateTo = logDateToEl ? logDateToEl.value : ''
    const logActionFilter = logActionFilterEl ? logActionFilterEl.value : ''

    let queryParams = `page=${page}&limit=${LOGS_PER_PAGE}`
    if (logSearch) queryParams += `&search=${logSearch}`
    if (logDateFrom) queryParams += `&dateFrom=${logDateFrom}`
    if (logDateTo) queryParams += `&dateTo=${logDateTo}`
    if (logActionFilter) queryParams += `&action=${logActionFilter}`

    try {
      const response = await fetchJSON(`php/api_logs.php?${queryParams}`)

      // Handle both old format (array) and new format (object with data & pagination)
      if (Array.isArray(response)) {
          logs = response
          renderLogs()
      } else if (response.success && response.data) { // New API structure
          logs = response.data
          // Fixed ReferenceError: use updatePaginationControls instead of renderPagination (or alias it)
          if (response.pagination) updatePaginationControls(response.pagination)
          renderLogs()
      } else { // Standard direct structure if any
          logs = response.data || []
          if(response.pagination) updatePaginationControls(response.pagination)
          renderLogs()
      }
    } catch (error) {
      console.error("Error al cargar logs:", error)
      showToast("Error al cargar logs.", "error")
    }
  }

  function updatePaginationControls(pagination) {
      if (!pagination || pagination.total_records <= 0) {
          const container = document.getElementById('logsPagination')
          if (container) container.style.display = 'none'
          return
      }

      currentLogPage = pagination.current_page

      // Use the new unified pagination helper
      renderPaginationControls({
          containerId: 'logsPagination',
          currentPage: pagination.current_page,
          totalPages: pagination.total_pages,
          totalItems: pagination.total_records,
          itemsPerPage: pagination.per_page,
          onPageChange: (page) => loadLogs(page)
      })
  }

  // Alias for backward compatibility or if I missed any renderPagination calls
  function renderPagination(pagination) {
      updatePaginationControls(pagination);
  }

  function formatLogDetails(detalles) {
      if (!detalles) return "N/A"
      if (typeof detalles !== 'string' || !detalles.trim().startsWith('{')) return detalles
      try {
          const d = JSON.parse(detalles)
          const parts = []
          for (const key in d) {
              const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' ')
              let val = d[key]
              if (key === 'tamanio_mb') val += ' MB'
              if (key === 'duracion_ms') val += ' ms'
              parts.push(`<strong>${label}:</strong> ${val}`)
          }
          return parts.join(' | ')
      } catch (e) {
          return detalles
      }
  }

  function getActionColor(action) {
    if (!action) return "secondary"
    const actionLower = action.toLowerCase()
    if (actionLower.includes("exitoso") || actionLower.includes("success") || actionLower.includes("completado")) return "success"
    if (actionLower.includes("fallido") || actionLower.includes("error") || actionLower.includes("denegado"))
      return "danger"
    if (actionLower.includes("creado") || actionLower.includes("actualizado") || actionLower.includes("nuevo"))
      return "primary"
    return "secondary"
  }

  function renderLogs() {
    const tbody = document.getElementById("logsTable")
    if (!tbody) return

    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No se encontraron registros de actividad.</td></tr>'
        return
    }

    tbody.innerHTML = logs
      .map(
        (log) => `
        <tr>
            <td class="font-mono" style="font-size: 0.85em;">${formatDateTime(log.fecha)}</td>
            <td class="font-mono">${log.nombre_usuario ? escapeHtml(log.nombre_usuario) : "Sistema"}</td>
            <td>
                <span class="badge badge-${getActionColor(log.accion)}">
                    ${log.accion}
                </span>
            </td>
            <td class="text-muted" style="font-size: 0.9em;">
                ${formatLogDetails(log.detalles)}
            </td>
        </tr>
    `
      )
      .join("")
  }


  async function loadBackups() {
    const tableBody = document.getElementById("backupsTable")
    if (!tableBody) return

    tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Cargando backups...</td></tr>'

    try {
      const data = await fetchJSON("php/api_backups.php")

      if (!data.success) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar backups</td></tr>'
        showToast("Error al cargar backups", "error")
        return
      }

      backups = data.backups

      if (backups.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay backups creados</td></tr>'
      } else {
        // Render backups table
        tableBody.innerHTML = backups.map(backup => {
          const fecha = new Date(backup.fecha).toLocaleString('es-ES')
          const tamanioMB = (backup.tamaño_bytes / 1048576).toFixed(2)

        // Determine tipo badge color
        const tipoBadgeClass = backup.tipo === 'manual' ? 'badge-primary' : 'badge-info'

        // Determine estado badge color
        let estadoBadgeClass = 'badge-secondary'
        if (backup.estado === 'completado') estadoBadgeClass = 'badge-success'
        else if (backup.estado === 'error') estadoBadgeClass = 'badge-danger'
        else if (backup.estado === 'en_proceso') estadoBadgeClass = 'badge-warning'

        return `
          <tr>
            <td class="font-mono">${fecha}</td>
            <td>
              <span class="badge ${tipoBadgeClass}">
                ${backup.tipo}
              </span>
            </td>
            <td class="font-mono">${tamanioMB} MB</td>
            <td>
              <span class="badge ${estadoBadgeClass}">
                ${backup.estado}
              </span>
            </td>
            <td>
              ${backup.estado === 'completado' ? `
                <button class="btn-action btn-restore me-1" onclick="restoreBackup(${backup.id}, '${backup.archivo}')" title="Restaurar Base de Datos" style="color: var(--buzz-warning);">
                  <i class="fas fa-undo"></i>
                </button>
                <button class="btn-action btn-download me-1" onclick="downloadBackup(${backup.id})" title="Descargar">
                  <i class="fas fa-download"></i>
                </button>
                <button class="btn-action btn-delete" onclick="deleteBackup(${backup.id}, '${backup.archivo}')" title="Eliminar">
                  <i class="fas fa-trash"></i>
                </button>
              ` : '-'}
            </td>
          </tr>
        `
      }).join('');
      }

    } catch (error) {
      console.error("Error loading backups:", error)
      tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error al cargar backups</td></tr>'
      showToast("Error al cargar backups", "error")
    }

    // Also load auto-backup status
    loadAutoBackupStatus()
  }

  async function loadAutoBackupStatus() {
    console.log('loadAutoBackupStatus: Starting...')

    const statusDot = document.getElementById('statusDot')
    const statusLabel = document.getElementById('statusLabel')
    const statusMessage = document.getElementById('statusMessage')
    const lastAutoBackup = document.getElementById('lastAutoBackup')
    const nextAutoBackup = document.getElementById('nextAutoBackup')
    const totalAutoBackups = document.getElementById('totalAutoBackups')

    console.log('loadAutoBackupStatus: Elements found:', {
      statusDot: !!statusDot,
      statusLabel: !!statusLabel,
      statusMessage: !!statusMessage
    })

    if (!statusDot || !statusLabel) {
      console.error('loadAutoBackupStatus: Required elements not found!')
      return
    }

    try {
      console.log('loadAutoBackupStatus: Fetching data...')
      const data = await fetchJSON('php/api_backups.php?action=status')
      console.log('loadAutoBackupStatus: Data received:', data)

      if (!data.success) {
        console.error('loadAutoBackupStatus: Server returned error:', data.error)
        statusLabel.textContent = 'ERROR'
        statusLabel.className = 'font-primary status-label-inactive'
        statusDot.className = 'status-dot-large inactive'
        if (statusMessage) statusMessage.textContent = data.error || 'Error al verificar'
        return
      }

      const autoBackup = data.autoBackup
      console.log('loadAutoBackupStatus: Updating UI with:', autoBackup)

      // Update status dot and label
      statusDot.className = `status-dot-large ${autoBackup.status}`
      statusLabel.textContent = autoBackup.statusLabel
      statusLabel.className = `font-primary status-label-${autoBackup.status}`
      if (statusMessage) statusMessage.textContent = autoBackup.message

      // Update details
      if (lastAutoBackup) {
        lastAutoBackup.textContent = autoBackup.lastRunAgo || 'Nunca'
      }
      if (nextAutoBackup) {
        nextAutoBackup.textContent = autoBackup.nextExpectedFormatted || '--'
      }
      if (totalAutoBackups) {
        totalAutoBackups.textContent = autoBackup.totalAutoBackups
      }

      // Sync toggle state with server config
      const autoBackupToggle = document.getElementById('autoBackupToggle')
      if (autoBackupToggle && data.fallbackEnabled !== undefined) {
        autoBackupToggle.checked = data.fallbackEnabled
      }

      console.log('loadAutoBackupStatus: UI updated successfully')

    } catch (error) {
      console.error('loadAutoBackupStatus: Exception caught:', error)
      if (statusDot) statusDot.className = 'status-dot-large inactive'
      if (statusLabel) {
        statusLabel.textContent = 'ERROR'
        statusLabel.className = 'font-primary status-label-inactive'
      }
      if (statusMessage) statusMessage.textContent = 'Error al verificar estado'
    }
  }

  // Handle auto-backup toggle
  function setupAutoBackupToggle() {
    const toggle = document.getElementById('autoBackupToggle')
    if (!toggle) return

    toggle.addEventListener('change', async function() {
      const enabled = this.checked

      try {
        const data = await fetchJSON('php/api_backups.php?action=toggle', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            enabled: enabled,
            csrf_token: document.querySelector('[name="csrf_token"]')?.value
          })
        })

        if (data.success) {
          showToast(
            enabled ? 'Backup automático activado' : 'Backup automático desactivado',
            'success'
          )
        } else {
          // Revert toggle
          this.checked = !enabled
          showToast(data.error || 'Error al cambiar configuración', 'error')
        }

      } catch (error) {
        console.error('Error toggling auto-backup:', error)
        // Revert toggle
        this.checked = !enabled
        showToast('Error al cambiar configuración', 'error')
      }
    })
  }

  // Initialize toggle when backups tab loads
  document.addEventListener('DOMContentLoaded', () => {
    setupAutoBackupToggle()
  })

  async function createBackup() {
    const createBtn = document.querySelector('button[onclick="createBackup()"]')
    if (createBtn) {
      createBtn.disabled = true
      createBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Creando...'
    }

    try {
      const data = await fetchJSON("php/api_backups.php?action=create", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
            csrf_token: document.querySelector('[name="csrf_token"]')?.value
        })
      })

      if (data.success) {
        showToast(`Backup creado: ${data.backup.tamaño_mb} MB (${data.backup.duracion_ms}ms)`, "success")
        loadBackups() // Reload backups list
      } else {
        showToast(data.error || "Error al crear backup", "error")
        console.error("Backup error:", data)
      }

    } catch (error) {
      console.error("Error creating backup:", error)
      showToast("Error al crear backup", "error")
    } finally {
      if (createBtn) {
        createBtn.disabled = false
        createBtn.innerHTML = '<i class="fas fa-download"></i> Crear Backup'
      }
    }
  }

  async function downloadBackup(backupId) {
    try {
      // Use direct link approach to preserve server filename
      window.location.href = `php/api_backups.php?action=download&id=${backupId}`;
      showToast("Descargando backup...", "success");
    } catch (error) {
      console.error("Error downloading backup:", error);
      showToast("Error al descargar backup", "error");
    }
  }

  async function deleteBackup(backupId, filename) {
    showModal({
      title: "Confirmar Eliminación",
      message: `¿Está seguro de que desea eliminar el backup "${filename}"?\n\nEsta acción no se puede deshacer.`,
      type: "confirm",
      onConfirm: async () => {
        try {
          const csrf = document.querySelector('[name="csrf_token"]')?.value || "";
          const data = await fetchJSON(`php/api_backups.php?id=${backupId}&csrf_token=${encodeURIComponent(csrf)}`, {
            method: "DELETE"
          })

          if (data.success) {
            showToast("Backup eliminado exitosamente", "success")
            loadBackups() // Reload backups list
          } else {
            showToast(data.error || "Error al eliminar backup", "error")
          }

        } catch (error) {
          console.error("Error deleting backup:", error)
          showToast("Error al eliminar backup", "error")
        }
      }
    })
  }

  async function restoreBackup(backupId, filename) {
    showModal({
      title: "⚠️ ADVERTENCIA DE RESTAURACIÓN",
      message: `¿Está seguro de que desea restaurar la base de datos desde "${filename}"?\n\nESTO SOBREESCRIBIRÁ SU BASE DE DATOS ACTUAL.\n\nTodos los datos actuales se perderán y serán reemplazados por los de este backup.`,
      type: "confirm",
      timer: 5,
      onConfirm: async () => {
        // Show loading state
        showToast("Iniciando restauración... Por favor espere.", "info", 5000)

        try {
          const data = await fetchJSON("php/api_backups.php?action=restore", {
            method: "POST",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({
                id: backupId,
                csrf_token: document.querySelector('[name="csrf_token"]')?.value
            })
          })

          if (data.success) {
            showModal({
                title: "Restauración Exitosa",
                message: `La base de datos ha sido restaurada correctamente.\nDuración: ${data.duracion_ms}ms`,
                type: "success"
            })
            loadBackups() // Reload list to show logs if any
          } else {
            showModal({
                title: "Error de Restauración",
                message: data.error || "Error al restaurar la base de datos",
                type: "error"
            })
          }

        } catch (error) {
          console.error("Error restoring backup:", error)
          showModal({
              title: "Error Crítico",
              message: "Error de red o servidor al intentar restaurar.",
              type: "error"
          })
        }
      }
    })
  }

  function filterLogs() {
    loadLogs()
  }

  function clearLogsFilters() {
    const logSearchEl = document.getElementById("logSearch");
    const logDateFromEl = document.getElementById("logDateFrom");
    const logDateToEl = document.getElementById("logDateTo");

    if (logSearchEl) logSearchEl.value = '';
    if (logDateFromEl) logDateFromEl.value = '';
    if (logDateToEl) logDateToEl.value = '';
    loadLogs(1);
  }

  function openUserModal(user = null) {
    const userModal = document.getElementById("userModal")
    const userModalTitle = document.getElementById("userModalTitle")
    const userForm = document.getElementById("userForm")
    const userPassword = document.getElementById("userPassword")
    const userPasswordConfirm = document.getElementById("userPasswordConfirm")

    if (
      !userModal ||
      !userModalTitle ||
      !userForm ||
      !userPassword ||
      !userPasswordConfirm
    )
      return

    userForm.reset()
    userPassword.required = true
    userPasswordConfirm.required = true

    if (user) {
      currentUserId = user.id
      userModalTitle.innerHTML = '<i class="fas fa-edit"></i> Editar Usuario'
      document.getElementById("inputUserName").value = user.nombre
      document.getElementById("userEmail").value = user.email
      document.getElementById("selectUserRole").value = user.rol
      // Map DB state (active/inactive) to Dropdown values (activo/inactivo)
      let displayStatus = user.estado;
      if (displayStatus === 'active') displayStatus = 'activo';
      if (displayStatus === 'inactive') displayStatus = 'inactivo';
      document.getElementById("userStatus").value = displayStatus

      userPassword.required = false
      userPasswordConfirm.required = false
    } else {
      currentUserId = null
      userModalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario'
    }
    window.openModal('userModal')
  }

  function closeUserModal(force) {
    window.closeModal('userModal', !!force)
    currentUserId = null
  }

  async function handleUserSubmit(e) {
    e.preventDefault()

    const userNameInput = document.getElementById("inputUserName");
    const userRoleInput = document.getElementById("selectUserRole");

    const user = {
      nombre: userNameInput ? userNameInput.value : '',
      email: document.getElementById("userEmail").value,
      rol: userRoleInput ? userRoleInput.value : 'operador',
      estado: document.getElementById("userStatus").value,
      csrf_token: document.querySelector('#userForm [name="csrf_token"]')?.value
    }

    const password = document.getElementById("userPassword").value
    const passwordConfirm = document.getElementById("userPasswordConfirm").value

    // Email validation
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/
    if (!emailRegex.test(user.email)) {
      showToast("Por favor, ingrese un correo electrónico válido.", "error")
      return
    }

    // Password validation for new users (Strict)
    if (!currentUserId && !password) {
      showToast("La contraseña es obligatoria para nuevos usuarios.", "error")
      return
    }

    // Password complexity validation
    if (password) {
      if (password.length < 8) {
        showToast("La contraseña debe tener al menos 8 caracteres.", "error")
        return
      }
      if (!/[a-z]/.test(password)) {
        showToast(
          "La contraseña debe contener al menos una letra minúscula.",
          "error"
        )
        return
      }
      if (!/[A-Z]/.test(password)) {
        showToast(
          "La contraseña debe contener al menos una letra mayúscula.",
          "error"
        )
        return
      }
      if (!/[0-9]/.test(password)) {
        showToast("La contraseña debe contener al menos un número.", "error")
        return
      }
      if (!/[^a-zA-Z0-9]/.test(password)) {
        showToast(
          "La contraseña debe contener al menos un carácter especial.",
          "error"
        )
        return
      }
    }

    if (password !== passwordConfirm) {
      showToast("Las contraseñas no coinciden.", "error")
      return
    }

    if (password) {
      user.password = password
    }

    let method = "POST"
    let url = "php/api_users.php"

    if (currentUserId) {
      user.id = currentUserId
      method = "PUT"
    }

    try {
      const data = await fetchJSON(url, {
        method: method,
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(user)
      })

      // Fix: Check data.success explicitly instead of data.error
      if (!data.success) {
        showToast(data.message || data.error || "Error al guardar usuario", "error")
      } else {
        showToast(data.message || "Usuario guardado exitosamente", "success")
        loadUsers()
        closeUserModal(true)
      }
    } catch (error) {
      console.error("Error:", error)
      showToast("Error al guardar usuario", "error")
    }
  }

  async function editUser(id) {
    try {
      const response = await fetchJSON(`php/api_users.php?id=${id}`)
      // Support wrapped format (response.data or response.user) and unwrapped
      const user = response.data || response.user || response

      if (user && user.id) {
        openUserModal(user)
      } else {
        showToast("Error: No se pudieron cargar los datos del usuario", "error")
        console.error("Invalid user data received:", response)
      }
    } catch (error) {
      console.error("Error al obtener usuario para edición:", error)
      showToast("Error al obtener usuario.", "error")
    }
  }

  async function deleteUser(id) {
    showModal({
      title: "Confirmar Eliminación",
      message: `¿Está seguro de que desea eliminar este usuario?`,
      type: "confirm",
      onConfirm: async () => {
        try {
          const csrf = document.querySelector('[name="csrf_token"]')?.value || "";
          const data = await fetchJSON(`php/api_users.php?id=${id}&csrf_token=${encodeURIComponent(csrf)}`, {
            method: "DELETE"
          })

          if (!data.success) {
            showModal({ title: "Error", message: data.message || "Error al eliminar usuario", type: "error" })
          } else {
            showToast("Usuario eliminado exitosamente", "success")
            loadUsers()
          }
        } catch (error) {
          console.error("Error al eliminar usuario:", error)
          showModal({
            title: "Error",
            message: "Error al eliminar usuario",
            type: "error"
          })
        }
      }
    })
  }

  function resetPassword(userId) {
    const user = users.find((u) => u.id === userId)
    if (confirm(`¿Resetear la contraseña de ${user.name}?`)) {
      showToast("Email de reset enviado al usuario (simulado)", "success")
    }
  }

  // --- Settings Tab Logic ---
  const MAX_LOGO_SIZE_MB = 6

  async function loadCompanyInfo() {
    try {
      const data = await fetchJSON(
        "php/api_settings.php?action=get_company_info"
      )

      // Ensure we have the target elements before trying to set values
      const rifTypeEl = document.getElementById("rifType");
      const rifNumberEl = document.getElementById("rifNumber");
      const companyNameEl = document.getElementById("companyName");
      const companyAddressEl = document.getElementById("companyAddress");
      const phoneCodeEl = document.getElementById("phoneCode");
      const phoneNumberEl = document.getElementById("phoneNumber");
      const companyEmailEl = document.getElementById("companyEmail");
      const logoPreviewEl = document.getElementById("currentLogoPreview");

      if (data.success && data.data) {
        const info = data.data
        const rifType = info.rif ? info.rif.substring(0, 1) : "J"
        const rifNumber = info.rif ? info.rif.substring(1) : ""

        let phoneCode = ""
        let phoneNumber = ""
        if (info.telefono) {
            // List of known country codes to detect
            const countryCodes = ["+58", "+1", "+57", "+54", "+34", "+52", "+55"]
            let found = false
            for (const code of countryCodes) {
                if (info.telefono.startsWith(code)) {
                    phoneCode = code
                    phoneNumber = info.telefono.substring(code.length).trim()
                    found = true
                    break
                }
            }
            if (!found) {
                // If no known code, put everything in phoneNumber
                phoneNumber = info.telefono
            }
        }

        if (rifTypeEl) rifTypeEl.value = rifType
        if (rifNumberEl) rifNumberEl.value = rifNumber
        if (companyNameEl) companyNameEl.value = info.nombre || ""
        if (companyAddressEl) companyAddressEl.value = info.direccion || ""
        if (phoneCodeEl) phoneCodeEl.value = phoneCode
        if (phoneNumberEl) phoneNumberEl.value = phoneNumber
        if (companyEmailEl) companyEmailEl.value = info.email || ""

        if (logoPreviewEl) {
          if (info.logo_svg) {
            logoPreviewEl.innerHTML = `<img src="${info.logo_svg}" alt="Current Logo" style="max-width: 100%; max-height: 120px; border: 1px solid rgba(255,160,0,0.1); padding: 5px;">`
          } else {
            logoPreviewEl.innerHTML = '<i class="fas fa-image fa-3x text-muted opacity-25"></i>'
          }
        }
      } else if (!data.success) {
         // Silently handle if no company info is yet in DB
         console.warn("No company info found in database yet.");
      }
    } catch (error) {
      console.error("Error fetching company info:", error)
    }
  }

  async function saveCompanyInfo(e) {
    e.preventDefault()

    // Get field values for validation
    const rifNumberEl = document.getElementById("rifNumber")
    const phoneCodeEl = document.getElementById("phoneCode")
    const phoneNumberEl = document.getElementById("phoneNumber")
    const companyEmailEl = document.getElementById("companyEmail")

    // 1. RIF Validation - only numbers and optional hyphen at end
    const rifNumber = rifNumberEl ? rifNumberEl.value.trim() : ""
    if (rifNumber && !/^[0-9]+(-[0-9])?$/.test(rifNumber)) {
      showToast("El RIF/Cédula debe contener solo números (formato: 123456789 o 123456789-0)", "warning")
      rifNumberEl.focus()
      return
    }

    // 2. Phone Validation - clean the number
    let phoneNumber = phoneNumberEl ? phoneNumberEl.value.trim() : ""
    // Remove any + prefix if user accidentally typed it
    phoneNumber = phoneNumber.replace(/^\+\d+\s*/, "")
    // Remove leading zero
    if (phoneNumber.startsWith("0")) {
      phoneNumber = phoneNumber.substring(1)
    }
    // Only allow numbers and spaces
    phoneNumber = phoneNumber.replace(/[^0-9\s]/g, "")
    if (phoneNumberEl) phoneNumberEl.value = phoneNumber

    // 3. Email Validation - valid format, no accents
    const email = companyEmailEl ? companyEmailEl.value.trim() : ""
    if (email) {
      // Check for accents/special chars
      const accentRegex = /[áéíóúñÁÉÍÓÚÑàèìòùÀÈÌÒÙ]/
      if (accentRegex.test(email)) {
        showToast("El email no puede contener acentos ni caracteres especiales", "warning")
        companyEmailEl.focus()
        return
      }
      // Basic email format validation
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(email)) {
        showToast("Por favor, ingrese un email válido (ej: admin@empresa.com)", "warning")
        companyEmailEl.focus()
        return
      }
    }

    const form = e.target
    const formData = new FormData(form)
    formData.append("action", "update_company_info")

    try {
      const data = await fetchJSON("php/api_settings.php", {
        method: "POST",
        body: formData
      })

      if (data.success) {
        showToast(data.message, "success")
        loadCompanyInfo() // Reload to show updated info
      } else {
        showToast(
          data.message || "Error al guardar información de la empresa.",
          "error"
        )
      }
    } catch (error) {
      console.error("Error saving company info:", error)
      showToast("Error de red al guardar información de la empresa.", "error")
    }
  }

  function handleLogoFileInputChange() {
    const logoFileInput = document.getElementById("logoFileInput")
    const logoFileInfo = document.getElementById("logoFileInfo")
    const logoFileName = document.getElementById("logoFileName")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")

    if (logoFileInput.files.length === 0) {
      logoFileInfo.style.display = "none"
      return
    }

    const file = logoFileInput.files[0]
    if (logoFileName) logoFileName.textContent = file.name
    if (logoFileInfo) logoFileInfo.style.display = "block"
    if (uploadLogoBtn) uploadLogoBtn.disabled = false
  }

  async function uploadLogo() {
    const logoFileInput = document.getElementById("logoFileInput")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")

    if (!logoFileInput.files || logoFileInput.files.length === 0) {
      showToast("Por favor, seleccione un archivo de logo.", "error")
      return
    }

    const file = logoFileInput.files[0]
    const formData = new FormData()
    formData.append("action", "upload_logo")
    formData.append("logoFile", file)
    formData.append("csrf_token", document.querySelector('[name="csrf_token"]')?.value || '')

    if (uploadLogoBtn) {
        uploadLogoBtn.disabled = true
        uploadLogoBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Subiendo...'
    }

    try {
      const data = await fetchJSON("php/api_settings.php", {
        method: "POST",
        body: formData
      })

      if (data.success) {
        showToast(data.message, "success")
        loadCompanyInfo() // Reload to show new logo
        clearLogoFile() // Clear file input after successful upload
      } else {
        showToast(data.message || "Error al subir el logo.", "error")
      }
    } catch (error) {
      console.error("Error uploading logo:", error)
      showToast("Error de red al subir el logo.", "error")
    } finally {
        if (uploadLogoBtn) {
            uploadLogoBtn.disabled = false
            uploadLogoBtn.innerHTML = '<i class="fas fa-upload me-1"></i>SUBIR LOGO'
        }
    }
  }

  function clearLogoFile() {
    const logoFileInput = document.getElementById("logoFileInput")
    const logoFileInfo = document.getElementById("logoFileInfo")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")

    if (logoFileInput) logoFileInput.value = ""
    if (logoFileInfo) logoFileInfo.style.display = "none"
    if (uploadLogoBtn) uploadLogoBtn.disabled = true
  }

  // Make functions globally accessible for inline event handlers
  window.editUser = editUser
  window.deleteUser = deleteUser
  window.resetPassword = resetPassword
  window.downloadBackup = downloadBackup
  window.deleteBackup = deleteBackup
  window.createBackup = createBackup
  window.restoreBackup = restoreBackup
  window.filterLogs = filterLogs
  window.clearLogsFilters = clearLogsFilters
  window.openUserModal = openUserModal
  window.closeUserModal = closeUserModal
})()

