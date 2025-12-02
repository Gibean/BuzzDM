;(function () {
  // Admin functionality
  let users = []
  let logs = []
  let backups = []
  let currentTab = "users"
  let currentUserId = null

  function formatDateTime(dateString) {
    const date = new Date(dateString)
    return date.toLocaleString()
  }

  document.addEventListener("DOMContentLoaded", () => {
    initAdmin()
    setupEventListeners()
  })

  function initAdmin() {
    switchTab("users")
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

    // Reports Tab Logic
    const reportOptions = document.querySelectorAll(".report-option")
    const generateReportBtn = document.getElementById("generateReportBtn")
    const adminPasswordInput = document.getElementById("adminPasswordReport")
    const reportError = document.getElementById("reportError")
    let selectedReportType = null

    if (
      reportOptions.length > 0 &&
      generateReportBtn &&
      adminPasswordInput &&
      reportError
    ) {
      reportOptions.forEach((option) => {
        option.addEventListener("click", () => {
          reportOptions.forEach((opt) => opt.classList.remove("selected-buzz"))
          option.classList.add("selected-buzz")
          selectedReportType = option.dataset.reportType
          generateReportBtn.disabled = false
          reportError.textContent = ""
        })
      })

      generateReportBtn.addEventListener("click", () => {
        const password = adminPasswordInput.value

        if (!selectedReportType) {
          reportError.textContent = "Por favor, seleccione un tipo de reporte."
          return
        }

        if (password.trim() === "") {
          reportError.textContent =
            "Por favor, ingrese la contraseña de administrador."
          return
        }

        generateReportBtn.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando...'
        generateReportBtn.disabled = true

        const reportUrl = `php/reports.php?tipo=${selectedReportType}&password=${encodeURIComponent(password)}`
        const newWindow = window.open(reportUrl, "_blank")

        if (newWindow) {
          const checkWindowClosed = setInterval(() => {
            if (newWindow.closed) {
              clearInterval(checkWindowClosed)
              resetReportButton()
            }
          }, 1000)
        } else {
          resetReportButton()
        }
      })

      function resetReportButton() {
        generateReportBtn.innerHTML =
          '<i class="fas fa-download"></i> Generar Reporte'
        generateReportBtn.disabled = false
        adminPasswordInput.value = ""
        reportOptions.forEach((opt) => opt.classList.remove("selected-buzz"))
        selectedReportType = null
        reportError.textContent = ""
      }
    }

    const logFilterButton = document.querySelector("#logsTab button")
    if (logFilterButton) {
      logFilterButton.addEventListener("click", () => loadLogs(1))
    }

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
    // Update active tab
    document.querySelectorAll("[data-tab]").forEach((tab) => {
      tab.classList.remove("active", "text-primary")
      tab.classList.add("text-muted")
    })

    const activeTab = document.querySelector(`[data-tab="${tabName}"]`)
    if (activeTab) {
      activeTab.classList.add("active", "text-primary")
      activeTab.classList.remove("text-muted")
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
      const data = await fetchJSON("php/users.php")
      users = data
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
            <td class="font-mono">${user.nombre}</td>
            <td class="font-mono">${user.email}</td>
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
      const sessionData = await fetchJSON("php/users.php?action=get_sessions")
      const todaySessions = sessionData.session_count
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

    const logSearch = logSearchEl ? logSearchEl.value : ''
    const logDateFrom = logDateFromEl ? logDateFromEl.value : ''
    const logDateTo = logDateToEl ? logDateToEl.value : ''

    let queryParams = `page=${page}&limit=${LOGS_PER_PAGE}`
    if (logSearch) queryParams += `&search=${logSearch}`
    if (logDateFrom) queryParams += `&dateFrom=${logDateFrom}`
    if (logDateTo) queryParams += `&dateTo=${logDateTo}`

    try {
      const response = await fetchJSON(`php/logs.php?${queryParams}`)

      // Handle both old format (array) and new format (object with data & pagination)
      if (Array.isArray(response)) {
          logs = response
          renderLogs()
      } else {
          logs = response.data
          renderLogs()
          updatePaginationControls(response.pagination)
      }
    } catch (error) {
      console.error("Error al cargar logs:", error)
      showToast("Error al cargar logs.", "error")
    }
  }

  function updatePaginationControls(pagination) {
      const paginationContainer = document.getElementById('logsPagination')
      const infoText = document.getElementById('logsInfoText')
      const prevBtn = document.getElementById('logsPrevPage')
      const nextBtn = document.getElementById('logsNextPage')
      const currentPageSpan = document.getElementById('logsCurrentPage')

      if (!paginationContainer || !pagination) return

      // Check if all required elements exist
      if (!infoText || !prevBtn || !nextBtn || !currentPageSpan) {
          console.warn('Pagination elements not found')
          return
      }

      if (pagination.total_records > 0) {
          paginationContainer.style.display = 'flex'
          paginationContainer.style.setProperty('display', 'flex', 'important')

          const start = (pagination.current_page - 1) * pagination.per_page + 1
          const end = Math.min(pagination.current_page * pagination.per_page, pagination.total_records)

          infoText.textContent = `Mostrando ${start}-${end} de ${pagination.total_records} registros`
          currentPageSpan.textContent = `Página ${pagination.current_page}`

          prevBtn.disabled = !pagination.has_prev
          nextBtn.disabled = !pagination.has_next

          currentLogPage = pagination.current_page
      } else {
          paginationContainer.style.display = 'none'
      }
  }

  function renderLogs() {
    const tbody = document.getElementById("logsTable")
    if (!tbody) return

    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No se encontraron registros</td></tr>'
        return
    }

    tbody.innerHTML = logs
      .map(
        (log) => `
        <tr>
            <td class="font-mono">${formatDateTime(log.fecha)}</td>
            <td class="font-mono">${log.nombre_usuario || "N/A"}</td>
            <td>
                <span class="badge badge-${getActionColor(log.accion)}">
                    ${log.accion}
                </span>
            </td>
            <td class="text-muted">${log.detalles || "N/A"}</td>
        </tr>
    `
      )
      .join("")
  }

  function getActionColor(action) {
    const actionLower = action.toLowerCase()
    if (actionLower.includes("exitoso")) return "success"
    if (actionLower.includes("fallido") || actionLower.includes("denegado"))
      return "danger"
    if (actionLower.includes("creado") || actionLower.includes("actualizado"))
      return "primary"
    return "secondary"
  }

  function loadBackups() {
    console.log("Loading backups...")
  }

  function createBackup() {
    console.log("Creating backup...")
  }

  function downloadBackup(backupId) {
    showToast("Descarga de backup iniciada", "info")
  }

  function deleteBackup(backupId) {
    if (confirm("¿Eliminar este backup?")) {
      showToast("Backup eliminado (simulado)", "success")
    }
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
      userModalTitle.textContent = "Editar Usuario"
      document.getElementById("userName").value = user.nombre
      document.getElementById("userEmail").value = user.email
      document.getElementById("userRole").value = user.rol
      document.getElementById("userStatus").value = user.estado
      userPassword.required = false
      userPasswordConfirm.required = false
    } else {
      currentUserId = null
      userModalTitle.textContent = "Nuevo Usuario"
    }
    userModal.style.display = "block"
  }

  function closeUserModal() {
    const modal = document.getElementById("userModal")
    if (modal) {
      modal.style.display = "none"
    }
    currentUserId = null
  }

  async function handleUserSubmit(e) {
    e.preventDefault()

    const user = {
      nombre: document.getElementById("userName").value,
      email: document.getElementById("userEmail").value,
      rol: document.getElementById("userRole").value,
      estado: document.getElementById("userStatus").value
    }

    const password = document.getElementById("userPassword").value
    const passwordConfirm = document.getElementById("userPasswordConfirm").value

    // Email validation
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/
    if (!emailRegex.test(user.email)) {
      showToast("Por favor, ingrese un correo electrónico válido.", "error")
      return
    }

    // Password validation
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
    let url = "php/users.php"

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

      if (data.error) {
        showToast(data.error, "error")
      } else {
        showToast(data.message, "success")
        loadUsers()
        closeUserModal()
      }
    } catch (error) {
      console.error("Error:", error)
      showToast("Error al guardar usuario", "error")
    }
  }

  async function editUser(id) {
    try {
      const user = await fetchJSON(`php/users.php?id=${id}`)
      if (user) {
        openUserModal(user)
      } else {
        showToast("Usuario no encontrado.", "error")
      }
    } catch (error) {
      console.error("Error al obtener usuario para edición:", error)
    }
  }

  async function deleteUser(id) {
    showModal({
      title: "Confirmar Eliminación",
      message: `¿Está seguro de que desea eliminar este usuario?`,
      type: "confirm",
      onConfirm: async () => {
        try {
          const data = await fetchJSON("php/users.php", {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id })
          })

          if (data.error) {
            showModal({ title: "Error", message: data.error, type: "error" })
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
        "php/settings_actions.php?action=get_company_info"
      )
      if (data.success && data.data) {
        const info = data.data
        const rifType = info.rif ? info.rif.substring(0, 1) : ""
        const rifNumber = info.rif ? info.rif.substring(1) : ""
        const phoneCode = info.telefono
          ? info.telefono.substring(0, info.telefono.indexOf(" "))
          : ""
        const phoneNumber = info.telefono
          ? info.telefono.substring(info.telefono.indexOf(" ") + 1)
          : ""

        document.getElementById("rifType").value = rifType
        document.getElementById("rifNumber").value = rifNumber
        document.getElementById("companyName").value = info.nombre
        document.getElementById("companyAddress").value = info.direccion
        document.getElementById("phoneCode").value = phoneCode
        document.getElementById("phoneNumber").value = phoneNumber
        document.getElementById("companyEmail").value = info.email

        if (info.logo_svg) {
          document.getElementById(
            "currentLogoPreview"
          ).innerHTML = `<img src="${info.logo_svg}" alt="Current Logo" style="max-width: 150px; max-height: 150px; border: 1px solid #ddd; padding: 5px;">`
        } else {
          document.getElementById("currentLogoPreview").innerHTML =
            '<p class="text-muted">No hay logo cargado.</p>'
        }
      } else {
        showToast(
          data.message || "Error al cargar información de la empresa.",
          "error"
        )
      }
    } catch (error) {
      console.error("Error fetching company info:", error)
      showToast("Error de red al cargar información de la empresa.", "error")
    }
  }

  async function saveCompanyInfo(e) {
    e.preventDefault()
    const form = e.target
    const formData = new FormData(form)
    formData.append("action", "update_company_info")

    try {
      const data = await fetchJSON("php/settings_actions.php", {
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

  async function handleLogoFileInputChange() {
    const logoFileInput = document.getElementById("logoFileInput")
    const logoFileInfo = document.getElementById("logoFileInfo")
    const logoFileName = document.getElementById("logoFileName")
    const logoFileSize = document.getElementById("logoFileSize")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")

    if (logoFileInput.files.length === 0) {
      showToast("Por favor, seleccione un archivo de logo.", "error")
      return
    }

    const file = logoFileInput.files[0]
    const formData = new FormData()
    formData.append("action", "upload_logo")
    formData.append("logoFile", file)

    uploadLogoBtn.disabled = true

    try {
      const data = await fetchJSON("php/settings_actions.php", {
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
      uploadLogoBtn.disabled = false
    }
  }

  function clearLogoFile() {
    const logoFileInput = document.getElementById("logoFileInput")
    const logoFileInfo = document.getElementById("logoFileInfo")
    const uploadLogoBtn = document.getElementById("uploadLogoBtn")

    logoFileInput.value = ""
    logoFileInfo.style.display = "none"
    uploadLogoBtn.disabled = true
  }

  // Make functions globally accessible for inline event handlers
  window.editUser = editUser
  window.deleteUser = deleteUser
  window.resetPassword = resetPassword
  window.downloadBackup = downloadBackup
  window.deleteBackup = deleteBackup
  window.createBackup = createBackup
  window.filterLogs = filterLogs
  window.clearLogsFilters = clearLogsFilters
  window.openUserModal = openUserModal
  window.closeUserModal = closeUserModal
})()
