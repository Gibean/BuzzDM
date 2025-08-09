// Admin functionality
let users = []
let logs = []
let backups = []
let currentTab = "users"

function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleString()
}

function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.classList.add("notification", `notification-${type}`)
  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    document.body.removeChild(notification)
  }, 3000)
}

document.addEventListener("DOMContentLoaded", () => {
  initAdmin()
  setupEventListeners()
})

function initAdmin() {
  loadUsers()
  loadLogs()
  loadBackups()
  renderCurrentTab()
}

function setupEventListeners() {
  // Tab navigation
  document.querySelectorAll("[data-tab]").forEach((tab) => {
    tab.addEventListener("click", function () {
      switchTab(this.dataset.tab)
    })
  })

  // User form
  document.getElementById("userForm").addEventListener("submit", handleUserSubmit)
}

function switchTab(tabName) {
  // Update active tab
  document.querySelectorAll("[data-tab]").forEach((tab) => {
    tab.classList.remove("active", "text-primary")
    tab.classList.add("text-muted")
  })

  document.querySelector(`[data-tab="${tabName}"]`).classList.add("active", "text-primary")
  document.querySelector(`[data-tab="${tabName}"]`).classList.remove("text-muted")

  // Hide all tab contents
  document.querySelectorAll(".tab-content").forEach((content) => {
    content.style.display = "none"
  })

  // Show selected tab
  document.getElementById(`${tabName}Tab`).style.display = "block"
  currentTab = tabName

  renderCurrentTab()
}

function renderCurrentTab() {
  switch (currentTab) {
    case "users":
      renderUsers()
      updateUserStats()
      break
    case "logs":
      renderLogs()
      break
    case "backups":
      renderBackups()
      break
    case "settings":
      // Settings are static HTML
      break
  }
}

function loadUsers() {
  users = [
    {
      id: 1,
      name: "Administrador",
      email: "admin@buzz.com",
      role: "admin",
      status: "active",
      lastLogin: "2024-01-15T10:30:00Z",
    },
    {
      id: 2,
      name: "Juan Vendedor",
      email: "juan@buzz.com",
      role: "user",
      status: "active",
      lastLogin: "2024-01-14T15:45:00Z",
    },
    {
      id: 3,
      name: "María López",
      email: "maria@buzz.com",
      role: "user",
      status: "inactive",
      lastLogin: "2024-01-10T09:15:00Z",
    },
  ]
}

function renderUsers() {
  const tbody = document.getElementById("usersTable")

  tbody.innerHTML = users
    .map(
      (user) => `
        <tr>
            <td class="font-mono">${user.name}</td>
            <td class="font-mono">${user.email}</td>
            <td>
                <span class="badge badge-${user.role === "admin" ? "warning" : "primary"}">
                    ${user.role === "admin" ? "Administrador" : "Usuario"}
                </span>
            </td>
            <td>
                <span class="badge badge-${user.status === "active" ? "success" : "secondary"}">
                    ${user.status === "active" ? "Activo" : "Inactivo"}
                </span>
            </td>
            <td class="font-mono">${formatDateTime(user.lastLogin)}</td>
            <td>
                <button class="btn btn-outline-buzz btn-sm me-1" onclick="editUser(${user.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-warning btn-sm me-1" onclick="resetPassword(${user.id})" title="Reset Password">
                    <i class="fas fa-key"></i>
                </button>
                ${
                  user.id !== 1
                    ? `
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteUser(${user.id})" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                `
                    : ""
                }
            </td>
        </tr>
    `,
    )
    .join("")
}

function updateUserStats() {
  const total = users.length
  const active = users.filter((u) => u.status === "active").length
  const admins = users.filter((u) => u.role === "admin").length
  const todaySessions = Math.floor(Math.random() * 20) + 5 // Simulated

  document.getElementById("totalUsers").textContent = total
  document.getElementById("activeUsers").textContent = active
  document.getElementById("adminUsers").textContent = admins
  document.getElementById("todaySessions").textContent = todaySessions
}

function loadLogs() {
  logs = [
    {
      id: 1,
      datetime: "2024-01-15T10:30:00Z",
      user: "admin@buzz.com",
      action: "Login exitoso",
      ip: "192.168.1.100",
      browser: "Chrome 120.0",
    },
    {
      id: 2,
      datetime: "2024-01-15T09:45:00Z",
      user: "juan@buzz.com",
      action: "Login exitoso",
      ip: "192.168.1.101",
      browser: "Firefox 121.0",
    },
    {
      id: 3,
      datetime: "2024-01-15T08:30:00Z",
      user: "admin@buzz.com",
      action: "Producto creado",
      ip: "192.168.1.100",
      browser: "Chrome 120.0",
    },
    {
      id: 4,
      datetime: "2024-01-14T16:20:00Z",
      user: "maria@buzz.com",
      action: "Login fallido",
      ip: "192.168.1.102",
      browser: "Safari 17.0",
    },
  ]
}

function renderLogs() {
  const tbody = document.getElementById("logsTable")

  tbody.innerHTML = logs
    .map(
      (log) => `
        <tr>
            <td class="font-mono">${formatDateTime(log.datetime)}</td>
            <td class="font-mono">${log.user}</td>
            <td>
                <span class="badge badge-${getActionColor(log.action)}">
                    ${log.action}
                </span>
            </td>
            <td class="font-mono">${log.ip}</td>
            <td class="text-muted">${log.browser}</td>
        </tr>
    `,
    )
    .join("")
}

function getActionColor(action) {
  if (action.includes("exitoso")) return "success"
  if (action.includes("fallido")) return "danger"
  if (action.includes("creado") || action.includes("actualizado")) return "primary"
  return "secondary"
}

function loadBackups() {
  backups = [
    {
      id: 1,
      date: "2024-01-15T02:00:00Z",
      type: "Automático",
      size: "45.2 MB",
      status: "completado",
    },
    {
      id: 2,
      date: "2024-01-14T02:00:00Z",
      type: "Automático",
      size: "44.8 MB",
      status: "completado",
    },
    {
      id: 3,
      date: "2024-01-13T14:30:00Z",
      type: "Manual",
      size: "44.5 MB",
      status: "completado",
    },
  ]
}

function renderBackups() {
  const tbody = document.getElementById("backupsTable")

  tbody.innerHTML = backups
    .map(
      (backup) => `
        <tr>
            <td class="font-mono">${formatDateTime(backup.date)}</td>
            <td>
                <span class="badge badge-${backup.type === "Automático" ? "primary" : "warning"}">
                    ${backup.type}
                </span>
            </td>
            <td class="font-mono">${backup.size}</td>
            <td>
                <span class="badge badge-${backup.status === "completado" ? "success" : "danger"}">
                    ${backup.status === "completado" ? "Completado" : "Error"}
                </span>
            </td>
            <td>
                <button class="btn btn-outline-buzz btn-sm me-1" onclick="downloadBackup(${backup.id})" title="Descargar">
                    <i class="fas fa-download"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="deleteBackup(${backup.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `,
    )
    .join("")
}

function openUserModal(userId = null) {
  const modal = document.getElementById("userModal")
  const modalTitle = document.getElementById("userModalTitle")

  if (userId) {
    const user = users.find((u) => u.id === userId)
    modalTitle.textContent = "Editar Usuario"
    fillUserForm(user)
  } else {
    modalTitle.textContent = "Nuevo Usuario"
    clearUserForm()
  }

  modal.style.display = "block"
}

function closeUserModal() {
  document.getElementById("userModal").style.display = "none"
}

function fillUserForm(user) {
  document.getElementById("userName").value = user.name
  document.getElementById("userEmail").value = user.email
  document.getElementById("userRole").value = user.role
  document.getElementById("userStatus").value = user.status

  // Hide password fields for editing
  document.getElementById("userPassword").required = false
  document.getElementById("userPasswordConfirm").required = false
}

function clearUserForm() {
  document.getElementById("userForm").reset()
  document.getElementById("userPassword").required = true
  document.getElementById("userPasswordConfirm").required = true
}

function handleUserSubmit(e) {
  e.preventDefault()

  const password = document.getElementById("userPassword").value
  const passwordConfirm = document.getElementById("userPasswordConfirm").value

  if (password && password !== passwordConfirm) {
    showNotification("Las contraseñas no coinciden", "error")
    return
  }

  const userData = {
    name: document.getElementById("userName").value,
    email: document.getElementById("userEmail").value,
    role: document.getElementById("userRole").value,
    status: document.getElementById("userStatus").value,
  }

  // Simulate user creation/update
  const newUser = {
    id: Math.max(...users.map((u) => u.id)) + 1,
    ...userData,
    lastLogin: new Date().toISOString(),
  }

  users.push(newUser)
  renderUsers()
  updateUserStats()
  closeUserModal()
  showNotification("Usuario guardado exitosamente", "success")
}

function editUser(userId) {
  openUserModal(userId)
}

function resetPassword(userId) {
  const user = users.find((u) => u.id === userId)
  if (confirm(`¿Resetear la contraseña de ${user.name}?`)) {
    showNotification("Email de reset enviado al usuario", "success")
  }
}

function deleteUser(userId) {
  const user = users.find((u) => u.id === userId)
  if (confirm(`¿Eliminar el usuario ${user.name}? Esta acción no se puede deshacer.`)) {
    users = users.filter((u) => u.id !== userId)
    renderUsers()
    updateUserStats()
    showNotification("Usuario eliminado exitosamente", "success")
  }
}

function createBackup() {
  if (confirm("¿Crear un nuevo backup de la base de datos?")) {
    const newBackup = {
      id: Math.max(...backups.map((b) => b.id)) + 1,
      date: new Date().toISOString(),
      type: "Manual",
      size: (Math.random() * 10 + 40).toFixed(1) + " MB",
      status: "completado",
    }

    backups.unshift(newBackup)
    renderBackups()
    showNotification("Backup creado exitosamente", "success")
  }
}

function downloadBackup(backupId) {
  showNotification("Descarga de backup iniciada", "info")
}

function deleteBackup(backupId) {
  if (confirm("¿Eliminar este backup?")) {
    backups = backups.filter((b) => b.id !== backupId)
    renderBackups()
    showNotification("Backup eliminado", "success")
  }
}

function filterLogs() {
  showNotification("Filtro de logs aplicado", "info")
}

// Close modal when clicking outside
document.getElementById("userModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeUserModal()
  }
})
