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

    authenticateUser(email, password)
  })

  function authenticateUser(email, password) {
    // Mostrar loading
    const submitBtn = loginForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    submitBtn.disabled = true;

    fetch("/buzz/php/login_guard.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({ email, password }),
    })
    .then(response => {
        if (!response.ok) {
            // If server response is not ok (e.g., 500, 404),
            // try to parse the error message from the body,
            // otherwise throw a generic error.
            return response.json().then(errorData => {
                throw new Error(errorData.message || `Error del servidor: ${response.status}`);
            }).catch(() => {
                // This catch is for cases where response.json() fails (e.g., empty response)
                throw new Error(`Error del servidor: ${response.status}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '/buzz/dashboard.php';
        } else {
            // This handles cases where response.ok is true, but the application
            // logic returns a failure (e.g., wrong password).
            showAlert(data.message || "Error inesperado. Por favor, intente nuevamente.");
        }
    })
    .catch((error) => {
        console.error("Error de conexión o del servidor:", error);
        // Display the specific error message thrown from the .then() block
        showAlert(error.message || "No se pudo conectar con el servidor. Verifique su conexión.");
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
  }

  function showAlert(message) {
    if (!loginAlertText || !loginAlert) {
      console.error("Elemento loginAlertText o loginAlert no encontrado en el DOM.")
      return
    }
    loginAlertText.textContent = message
    loginAlert.style.display = "block"

    // Auto-hide después de 5 segundos
    setTimeout(() => {
      loginAlert.style.display = "none"
    }, 5000)
  }

  function isValidEmail(email) {
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    return emailRegex.test(email)
  }
})