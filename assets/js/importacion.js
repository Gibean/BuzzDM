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

  // Prompt for password using a dynamically created Bootstrap modal
  const password = await new Promise((resolve) => {
      const modalHtml = `
      <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content buzz-card bg-card border-secondary">
            <div class="modal-header border-bottom border-secondary">
              <h5 class="modal-title font-primary text-primary"><i class="fas fa-lock"></i> Autenticación Requerida</h5>
              <button type="button" class="btn-close btn-close-white" aria-label="Close" onclick="document.getElementById('cancelPasswordBtn').click()"></button>
            </div>
            <div class="modal-body text-start">
              <p class="text-light">Ingresa tu contraseña para autorizar la importación masiva:</p>
              <input type="password" id="importPasswordInput" class="form-control bg-dark text-white border-secondary" placeholder="Tu contraseña">
            </div>
            <div class="modal-footer border-top border-secondary">
              <button type="button" class="btn btn-outline-secondary" id="cancelPasswordBtn">Cancelar</button>
              <button type="button" class="btn btn-buzz" id="confirmPasswordBtn">Autorizar</button>
            </div>
          </div>
        </div>
      </div>`;
      
      document.body.insertAdjacentHTML('beforeend', modalHtml);
      const modalEl = document.getElementById('passwordModal');
      const bsModal = new bootstrap.Modal(modalEl);
      
      const confirmBtn = document.getElementById('confirmPasswordBtn');
      const cancelBtn = document.getElementById('cancelPasswordBtn');
      const passInput = document.getElementById('importPasswordInput');
      
      let resolved = false;
      const cleanup = (val) => {
          if (resolved) return;
          resolved = true;
          bsModal.hide();
          setTimeout(() => { modalEl.remove(); resolve(val); }, 300);
      };
      
      confirmBtn.onclick = () => {
          const val = passInput.value.trim();
          if (val) cleanup(val);
          else passInput.classList.add('is-invalid');
      };

      passInput.addEventListener('keypress', (e) => {
          if(e.key === 'Enter') confirmBtn.click();
      });
      
      cancelBtn.onclick = () => cleanup(null);
      
      bsModal.show();
      setTimeout(() => passInput.focus(), 500);
  });

  if (!password) {
      return; // Canceled by user
  }

  const importForm = document.getElementById("importForm")
  const formData = new FormData(importForm)
  formData.append('password', password)

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
      document.getElementById("currentStatus").textContent = "Procesamiento finalizado."
      let totalMsg = ""
      let hasErrors = false

      // Process per-entity results
      for (const [entity, stats] of Object.entries(result.results)) {
          let entityMsg = `<b>${entity}:</b> ${stats.processed} procesados`
          if (stats.failed > 0) {
              entityMsg += `, <span class="text-danger">${stats.failed} fallos</span>`
              hasErrors = true
          } else {
              entityMsg += `, <span class="text-success">Exitoso</span>`
          }
          totalMsg += `<div>${entityMsg}</div>`
      }

      showToast("Importación completada", hasErrors ? "warning" : "success")

      // Update status area with detailed breakdown
      const statusDiv = document.getElementById("currentStatus")
      statusDiv.innerHTML = totalMsg

      addToImportHistory({
        date: new Date().toISOString(),
        type: "general",
        filename: result.data ? result.data.fileName : selectedFile.name,
        status: hasErrors ? "completado-errores" : "exitoso",
        records: Object.values(result.results).reduce((acc, curr) => acc + curr.processed, 0),
        errors: Object.values(result.results).reduce((acc, curr) => acc + curr.failed, 0),
        user: "admin",
      })

    } else {
      document.getElementById("currentStatus").textContent = "Error crítico en la importación."
      showToast("Error: " + result.message, "error")
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
    const response = await fetch('php/api_import.php');
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


// Expose function for onclick events
window.downloadTemplate = async (type) => {
    // Capture Toast ID
    let toastId = showToast("Descargando plantilla...", "info")

    try {
        // Keeping download_template.php as per original file
        const response = await fetch(`php/download_template.php?type=${type}`)
        if (!response.ok) throw new Error("Error en el servidor al descargar")

        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)

        // Extract filename from header or assume default
        let filename = `Plantilla_${type.charAt(0).toUpperCase() + type.slice(1)}.xlsx`
        const disposition = response.headers.get('Content-Disposition')
        if (disposition && disposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
                filename = matches[1].replace(/['"]/g, '');
            }
        }

        const a = document.createElement('a')
        a.style.display = 'none'
        a.href = url
        a.download = filename
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        document.body.removeChild(a)

        // Update the existing toast instead of showing a new one
        updateToast(toastId, `Plantilla descargada: ${filename}`, "success")

    } catch (error) {
        console.error("Download error:", error)
        // Update toast with error
        updateToast(toastId, "Error al descargar la plantilla.", "error")
    }
}
