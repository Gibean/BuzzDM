// Common functionality for all pages
document.addEventListener("DOMContentLoaded", () => {
  // Check authentication
  checkAuth()

  // Initialize user info
  initUserInfo()

  // Initialize sidebar toggle
  initSidebarToggle()

  // Update session time
  updateSessionTime()
  setInterval(updateSessionTime, 1000)

  // Update last update time
  updateLastUpdateTime()
  setInterval(updateLastUpdateTime, 30000)
})

function checkAuth() {
  const user = localStorage.getItem("buzzUser")
  if (!user && !window.location.pathname.includes("login.html")) {
    window.location.href = "login.html"
    return
  }

  if (user && window.location.pathname.includes("login.html")) {
    window.location.href = "dashboard.html"
    return
  }
}

function initUserInfo() {
  const user = JSON.parse(localStorage.getItem("buzzUser") || "{}")
  const userNameEl = document.getElementById("userName")
  const userRoleEl = document.getElementById("userRole")

  if (userNameEl && user.name) {
    userNameEl.textContent = user.name
  }

  if (userRoleEl && user.role) {
    userRoleEl.textContent = user.role === "admin" ? "Administrador" : "Usuario"
  }
}

function initSidebarToggle() {
  const sidebarToggle = document.getElementById("sidebarToggle")
  const sidebar = document.getElementById("sidebar")

  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", () => {
      sidebar.classList.toggle("show")
    })

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", (e) => {
      if (window.innerWidth <= 768) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
          sidebar.classList.remove("show")
        }
      }
    })
  }
}

function updateSessionTime() {
  const user = JSON.parse(localStorage.getItem("buzzUser") || "{}")
  const sessionTimeEl = document.getElementById("sessionTime")

  if (sessionTimeEl && user.loginTime) {
    const loginTime = new Date(user.loginTime)
    const now = new Date()
    const diff = now - loginTime

    const hours = Math.floor(diff / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((diff % (1000 * 60)) / 1000)

    sessionTimeEl.textContent = `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`
  }
}

function updateLastUpdateTime() {
  const lastUpdateEl = document.getElementById("lastUpdate")
  if (lastUpdateEl) {
    const now = new Date()
    lastUpdateEl.textContent = now.toLocaleTimeString("es-ES")
  }
}

function logout() {
  if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
    localStorage.removeItem("buzzUser")
    window.location.href = "login.html"
  }
}

// Utility functions
function formatCurrency(amount) {
  return new Intl.NumberFormat("es-ES", {
    style: "currency",
    currency: "EUR",
  }).format(amount)
}

function formatDate(date) {
  return new Date(date).toLocaleDateString("es-ES")
}

function formatDateTime(date) {
  return new Date(date).toLocaleString("es-ES")
}

function showNotification(message, type = "info") {
  // Create notification element
  const notification = document.createElement("div")
  notification.className = `alert alert-${type} position-fixed`
  notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
    `
  notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `

  document.body.appendChild(notification)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (notification.parentElement) {
      notification.remove()
    }
  }, 5000)
}

// Add slide in animation
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`
document.head.appendChild(style)
