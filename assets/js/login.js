// Login functionality
document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm")
  const loginAlert = document.getElementById("loginAlert")
  const loginAlertText = document.getElementById("loginAlertText")

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault()

    const email = document.getElementById("email").value
    const password = document.getElementById("password").value

    // Validación básica
    if (!email || !password) {
      showAlert("Por favor, completa todos los campos")
      return
    }

    if (!isValidEmail(email)) {
      showAlert("Por favor, ingresa un email válido")
      return
    }

    // Simular autenticación (aquí iría la llamada al backend)
    authenticateUser(email, password)
  })

  function authenticateUser(email, password) {
    // Mostrar loading
    const submitBtn = loginForm.querySelector('button[type="submit"]')
    const originalText = submitBtn.innerHTML
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...'
    submitBtn.disabled = true

    // Simular delay de red
    setTimeout(() => {
      // Credenciales de prueba
      if (email === "admin@buzz.com" && password === "admin123") {
        // Login exitoso
        localStorage.setItem(
          "buzzUser",
          JSON.stringify({
            email: email,
            name: "Administrador",
            role: "admin",
            loginTime: new Date().toISOString(),
          }),
        )

        // Redireccionar al dashboard
        window.location.href = "dashboard.html"
      } else {
        // Login fallido
        showAlert("Credenciales incorrectas. Usa admin@buzz.com / admin123")
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    }, 1500)
  }

  function showAlert(message) {
    loginAlertText.textContent = message
    loginAlert.style.display = "block"

    // Auto-hide después de 5 segundos
    setTimeout(() => {
      loginAlert.style.display = "none"
    }, 5000)
  }

  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(email)
  }
})
