// Import functionality
let selectedFile = null
let importHistory = []

document.addEventListener("DOMContentLoaded", () => {
  initImport()
  setupEventListeners()
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
  uploadArea.addEventListener("drop", handleDrop)
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
    showNotification("Tipo de archivo no soportado. Use CSV o Excel.", "error")
    return
  }

  // Validate file size (max 10MB)
  const maxSize = 10 * 1024 * 1024
  if (file.size > maxSize) {
    showNotification("El archivo es demasiado grande. Máximo 10MB.", "error")
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

function startImport() {
  if (!selectedFile) {
    showNotification("Por favor, selecciona un archivo primero.", "error")
    return
  }

  const dataType = document.getElementById("dataType").value
  const updateExisting = document.getElementById("updateExisting").checked
  const validateData = document.getElementById("validateData").checked

  // Show progress section
  document.getElementById("importProgress").style.display = "block"
  document.getElementById("importBtn").disabled = true

  // Simulate import process
  simulateImport(dataType, updateExisting, validateData)
}

function simulateImport(dataType, updateExisting, validateData) {
  const totalRecords = Math.floor(Math.random() * 1000) + 100
  let processed = 0
  let successful = 0
  let errors = 0

  document.getElementById("totalCount").textContent = totalRecords
  document.getElementById("currentStatus").textContent = "Iniciando importación..."

  const interval = setInterval(() => {
    // Simulate processing
    const batchSize = Math.floor(Math.random() * 20) + 5
    processed = Math.min(processed + batchSize, totalRecords)

    // Simulate some errors
    const batchErrors = Math.floor(Math.random() * 3)
    errors += batchErrors
    successful = processed - errors

    // Update progress
    const progress = (processed / totalRecords) * 100
    document.getElementById("progressBar").style.width = progress + "%"
    document.getElementById("processedCount").textContent = processed
    document.getElementById("successCount").textContent = successful
    document.getElementById("errorCount").textContent = errors

    // Update status
    if (processed < totalRecords) {
      document.getElementById("currentStatus").textContent = `Procesando registros... ${processed}/${totalRecords}`
    } else {
      document.getElementById("currentStatus").textContent = "Importación completada"
      clearInterval(interval)

      // Add to history
      addToImportHistory({
        date: new Date().toISOString(),
        type: dataType,
        filename: selectedFile.name,
        status: errors > 0 ? "completado-errores" : "exitoso",
        records: successful,
        errors: errors,
        user: "admin",
      })

      // Re-enable import button
      setTimeout(() => {
        document.getElementById("importBtn").disabled = false
        showNotification(
          `Importación completada. ${successful} registros procesados, ${errors} errores.`,
          errors > 0 ? "warning" : "success",
        )
      }, 1000)
    }
  }, 100)
}

function loadImportHistory() {
  // Simulate loading import history
  importHistory = [
    {
      id: 1,
      date: "2024-01-15T10:30:00Z",
      type: "products",
      filename: "productos_enero.csv",
      status: "exitoso",
      records: 245,
      errors: 0,
      user: "admin",
    },
    {
      id: 2,
      date: "2024-01-14T15:45:00Z",
      type: "customers",
      filename: "clientes_nuevos.xlsx",
      status: "completado-errores",
      records: 189,
      errors: 3,
      user: "admin",
    },
    {
      id: 3,
      date: "2024-01-13T09:15:00Z",
      type: "orders",
      filename: "pedidos_diciembre.csv",
      status: "error",
      records: 0,
      errors: 156,
      user: "vendedor1",
    },
  ]
}

function renderImportHistory() {
  const tbody = document.getElementById("importHistoryTable")

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
    `,
    )
    .join("")
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
  const newImport = {
    id: Math.max(...importHistory.map((h) => h.id)) + 1,
    ...importData,
  }

  importHistory.unshift(newImport)
  renderImportHistory()
}

function viewImportDetails(importId) {
  const importItem = importHistory.find((h) => h.id === importId)
  if (!importItem) return

  alert(`Detalles de Importación #${importId}:
    
Fecha: ${formatDateTime(importItem.date)}
Tipo: ${getTypeLabel(importItem.type)}
Archivo: ${importItem.filename}
Estado: ${importItem.status}
Registros exitosos: ${importItem.records}
Errores: ${importItem.errors}
Usuario: ${importItem.user}`)
}

function retryImport(importId) {
  if (confirm("¿Deseas reintentar esta importación?")) {
    showNotification("Funcionalidad de reintento no implementada en esta demo.", "info")
  }
}

function showNotification(message, type) {
  console.log(`Notification (${type}): ${message}`)
}

function formatDateTime(dateString) {
  const date = new Date(dateString)
  return date.toLocaleString()
}
