// Import functionality
let selectedFile = null
let importHistory = []

document.addEventListener("DOMContentLoaded", () => {
  initImport()
  setupEventListeners()
  // Add event listener for form submission
  document.getElementById("importForm").addEventListener("submit", handleFormSubmit)
})

function initImport() {
  loadImportHistory()
  renderImportHistory()
}

function setupEventListeners() {
  const fileInput = document.getElementById("fileInput")
  const uploadArea = document.getElementById("uploadArea")

  // File input change
  fileInput.addEventListener("change", handleFileSelect)

  // Drag and drop
  uploadArea.addEventListener("dragover", handleDragOver)
  uploadArea.addEventListener(
    "drop",
    handleDrop
  )
  uploadArea.addEventListener("click", () => fileInput.click())
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

function handleDragOver(event) {
  event.preventDefault()
  event.currentTarget.classList.add("border-primary")
}

function handleDrop(event) {
  event.preventDefault()
  event.currentTarget.classList.remove("border-primary")

  const files = event.dataTransfer.files
  if (files.length > 0) {
    validateAndSetFile(files[0])
  }
}

function validateAndSetFile(file) {
  // Validate file type
  const allowedTypes = [".csv", ".xlsx", ".xls"]
  const fileExtension = "." + file.name.split(".").pop().toLowerCase()

  if (!allowedTypes.includes(fileExtension)) {
    showModal({ title: "Error de Archivo", message: "Tipo de archivo no soportado. Use CSV o Excel.", type: "error" })
    return
  }

  // Validate file size (max 10MB)
  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
        showModal({ title: "Error de Archivo", message: "El archivo es demasiado grande. Máximo 10MB.", type: "error" })
    return
  }

  selectedFile = file
  showFileInfo(file)
  document.getElementById("importBtn").disabled = false
}

function showFileInfo(file) {
  const fileInfo = document.getElementById("fileInfo")
  const fileName = document.getElementById("fileName")
  const fileSize = document.getElementById("fileSize")

  fileName.textContent = file.name
  fileSize.textContent = formatFileSize(file.size)
  fileInfo.style.display = "block"
}

function clearFile() {
  selectedFile = null
  document.getElementById("fileInfo").style.display = "none"
  document.getElementById("fileInput").value = ""
  document.getElementById("importBtn").disabled = true
}

function formatFileSize(bytes) {
  if (bytes === 0) return "0 Bytes"
  const k = 1024
  const sizes = ["Bytes", "KB", "MB", "GB"]
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Number.parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i]
}

async function handleFormSubmit(event) {
  event.preventDefault() // Prevent default form submission

  if (!selectedFile) {
    showModal({ title: "Error de Importación", message: "Por favor, selecciona un archivo primero.", type: "error" })
    return
  }

  const importForm = document.getElementById("importForm")
  const formData = new FormData(importForm)

  // Show progress section
  document.getElementById("importProgress").style.display = "block"
  document.getElementById("importBtn").disabled = true
  document.getElementById("currentStatus").textContent = "Enviando archivo..."

  try {
    const response = await fetch(importForm.action, {
      method: "POST",
      body: formData,
    })

    const result = await response.json()

    if (result.success) {
      showToast("¡Importación exitosa! " + result.message, "success")
      document.getElementById("currentStatus").textContent = "Archivo procesado en el servidor."
      // Here you would typically get actual import progress/results from the server
      // For now, we'll simulate a successful import for history
      addToImportHistory({
        date: new Date().toISOString(),
        type: formData.get("dataType"),
        filename: result.data.fileName,
        status: "exitoso", // Assuming success for now
        records: 0, // Placeholder
        errors: 0, // Placeholder
        user: "admin", // Placeholder
      })
    } else {
      document.getElementById("currentStatus").textContent = "Error en la importación."
      let errorMessage = result.message || "Error inesperado durante la importación."
      if (result.errors && Array.isArray(result.errors) && result.errors.length > 0) {
        errorMessage += "\n\nDetalles de los errores:\n"
        result.errors.forEach(error => {
          errorMessage += `- Fila ${error.row}: ${error.message}\n`
          if (error.data) {
            errorMessage += `  Datos: ${JSON.stringify(error.data)}\n`
          }
        })
      }
      showToast("Importación fallida: " + errorMessage, "error")
      addToImportHistory({
        date: new Date().toISOString(),
        type: formData.get("dataType"),
        filename: selectedFile.name,
        status: "error",
        records: (result.data && result.data.importedRecords) || 0,
        errors: (result.data && result.data.errorsEncountered) || 1,
        user: "admin",
      })
    }
  } catch (error) {
    console.error("Error durante la importación:", error)
    showToast("Error de conexión o del servidor: " + error.message, "error")
    document.getElementById("currentStatus").textContent = "Error de conexión."
    addToImportHistory({
      date: new Date().toISOString(),
      type: formData.get("dataType"),
      filename: selectedFile.name,
      status: "error",
      records: 0,
      errors: 1, // Indicate an error
      user: "admin",
    })
  } finally {
    document.getElementById("importBtn").disabled = false
    document.getElementById("importProgress").style.display = "none" // Hide progress section
    clearFile() // Clear selected file after attempt
  }
}

async function loadImportHistory() {
  console.log('Attempting to load import history...');
  try {
    const response = await fetch('php/get_import_history.php');
    const result = await response.json();
    console.log('Response from get_import_history.php:', result);

    if (result.success) {
      importHistory = result.history;
      console.log('Import history data:', importHistory);
      renderImportHistory();
    } else {
      showToast('Error al cargar el historial de importaciones: ' + result.message, 'error');
    }
  } catch (error) {
    console.error('Error fetching import history:', error);
    showToast('Error de conexión al cargar el historial de importaciones.', 'error');
  }
}

function renderImportHistory() {
  const tbody = document.getElementById("importHistoryTable")
  console.log('Rendering import history. Data length:', importHistory.length);

  tbody.innerHTML = importHistory
    .map(
      (item) => `
        <tr>
            <td class="font-mono">${formatDateTime(item.date)}</td>
            <td>
                <span class="badge badge-${getTypeColor(item.type)}">${getTypeLabel(item.type)}</span>
            </td>
            <td class="font-mono">${item.filename}</td>
            <td>${getStatusBadge(item.status)}</td>
            <td class="font-mono">
                <span class="text-success">${item.records}</span>
                ${item.errors > 0 ? `/ <span class="text-danger">${item.errors}</span>` : ""}
            </td>
            <td class="font-mono">${item.user}</td>
            <td>
                <button class="btn btn-outline-buzz btn-sm" onclick="viewImportDetails(${item.id})" title="Ver detalles">
                    <i class="fas fa-eye"></i>
                </button>
                ${
                  item.status === "error"
                    ? `
                    <button class="btn btn-outline-warning btn-sm ms-1" onclick="retryImport(${item.id})" title="Reintentar">
                        <i class="fas fa-redo"></i>
                    </button>
                `
                    : ""
                }
            </td>
        </tr>
    `
    )
    .join("")
    console.log('Table body innerHTML:', tbody.innerHTML);
}

function getTypeColor(type) {
  const colors = {
    products: "primary",
    customers: "success",
    orders: "warning",
  }
  return colors[type] || "secondary"
}

function getTypeLabel(type) {
  const labels = {
    products: "Productos",
    customers: "Clientes",
    orders: "Pedidos",
  }
  return labels[type] || type
}

function getStatusBadge(status) {
  const badges = {
    exitoso: '<span class="badge badge-success">Exitoso</span>',
    "completado-errores": '<span class="badge badge-warning">Con Errores</span>',
    error: '<span class="badge badge-danger">Error</span>',
    procesando: '<span class="badge badge-primary">Procesando</span>',
  }
  return badges[status] || '<span class="badge badge-secondary">Desconocido</span>'
}

function addToImportHistory(importData) {
  // After a new import, refresh the entire history from the database
  loadImportHistory();
}

function viewImportDetails(importId) {
  const importItem = importHistory.find((h) => h.id === importId)
  if (!importItem) return

  showModal({
    title: `Detalles de Importación #${importId}`,
    message: `
Fecha: ${formatDateTime(importItem.date)}
Tipo: ${getTypeLabel(importItem.type)}
Archivo: ${importItem.filename}
Estado: ${importItem.status}
Registros exitosos: ${importItem.records}
Errores: ${importItem.errors}
Usuario: ${importItem.user}`,
    type: "info"
  })
}

function retryImport(importId) {
  showModal({
    title: "Reintentar Importación",
    message: "¿Deseas reintentar esta importación?",
    type: "confirm",
    onConfirm: () => {
      showToast("Funcionalidad de reintento no implementada en esta demo.", "info")
    }
  })
}

