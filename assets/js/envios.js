// Shipments management functionality
let shipments = []
let filteredShipments = []
let currentTasa = null;

document.addEventListener("DOMContentLoaded", () => {
  initShipments()
  setupEventListeners()
  setupAdvancedFilters()
})
// Advanced filters toggle logic
function setupAdvancedFilters() {
    const toggleBtn = document.getElementById("toggleAdvancedFilters");
    const advancedCollapse = document.getElementById("advancedFiltersCollapse");
    const activeBadge = document.getElementById("activeFiltersBadge");

    if (toggleBtn && advancedCollapse) {
        // Restore state from localStorage
        const isAdvVisible = localStorage.getItem("advFiltersVisible_envios") === "true";
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
                localStorage.setItem("advFiltersVisible_envios", "true");
            } else {
                advancedCollapse.classList.add("d-none");
                toggleBtn.classList.remove("active");
                localStorage.setItem("advFiltersVisible_envios", "false");
            }
        });
    }

    // Attach listener to advanced filters to update badge
    const sortBy = document.getElementById("sortBy");

    if(sortBy) sortBy.addEventListener('change', updateActiveFiltersCount);
}

function updateActiveFiltersCount() {
    const activeBadge = document.getElementById("activeFiltersBadge");
    if (!activeBadge) return;

    let activeFilters = 0;
    // Only count filters in the advanced section
    const sortBy = document.getElementById("sortBy");

    // Check if sortBy is not the default value (assuming 'date' is default)
    if (sortBy && sortBy.value !== "date") activeFilters++;

    if (activeFilters > 0) {
        activeBadge.textContent = activeFilters;
        activeBadge.classList.remove("d-none");
    } else {
        activeBadge.classList.add("d-none");
    }
}

async function fetchTasa() {
    try {
        const response = await fetchJSON('php/api_tasa.php')
        if (response.success && response.tasa && response.tasa.tasa_actual) {
            currentTasa = parseFloat(response.tasa.tasa_actual)
        }
    } catch (error) {
        console.error("Error fetching tasa:", error)
    }
}

async function initShipments() {
  await fetchTasa()
  await loadShipments()
  renderShipments()
  // updateStats() removed
  setupFilters()
}

function setupEventListeners() {
  document
    .getElementById("searchInput")
    .addEventListener("input", filterShipments)
  document
    .getElementById("statusFilter")
    .addEventListener("change", filterShipments)
  document.getElementById("sortBy").addEventListener("change", sortShipments)
}

async function loadShipments() {
  try {
    const searchInput = document.getElementById("searchInput").value
    const statusFilter = document.getElementById("statusFilter").value
    const sortBy = document.getElementById("sortBy").value

    let url = `php/api_shipments.php?`
    if (searchInput) {
      url += `&search=${encodeURIComponent(searchInput)}`
    }
    if (statusFilter) {
      url += `&statusFilter=${encodeURIComponent(statusFilter)}`
    }
    if (sortBy) {
      url += `&sortBy=${encodeURIComponent(sortBy)}`
    }

    const data = await fetchJSON(url)
    if (data.success) {
      shipments = data.data.shipments // Assuming 'data' contains 'shipments' array
      filteredShipments = [...shipments]
    } else {
      showNotification(`Error al cargar envíos: ${data.message}`, "error")
      shipments = []
      filteredShipments = []
    }
  } catch (error) {
    console.error("Error fetching shipments:", error)
    showNotification(`Error al cargar envíos: ${error.message}`, "error")
    shipments = []
    filteredShipments = []
  }
}

function renderShipments() {
  const container = document.getElementById("shipmentsContainer")

  if (filteredShipments.length === 0) {
    container.innerHTML = `
            <div class="buzz-card text-center py-5">
                <i class="fas fa-truck text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron envíos</h5>
                <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
            </div>
        `
    return
  }

  container.innerHTML = filteredShipments
    .map(
      (shipment) => `
        <div class="buzz-card mb-4 shipment-card-enhanced p-3 p-md-4">
            <!-- Header Row -->
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white text-uppercase fw-bold fs-6">ORDEN</span>
                <div>
                    ${getStatusBadge(shipment.status)}
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="fs-4 fw-bold text-white">#${shipment.id}</span>
                <span class="text-muted small"><i class="fas fa-calendar-alt opacity-50 me-1"></i> ${formatDate(shipment.date)}</span>
            </div>

            <hr class="border-secondary opacity-10 my-3">

            <!-- Details Row -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <span class="text-white x-small text-uppercase fw-bold d-block mb-2">CLIENTE</span>
                    <div class="fw-medium text-white text-truncate fs-6" title="${shipment.customer}">${shipment.customer}</div>
                </div>
                <div class="col-12 col-md-3">
                    <span class="text-white x-small text-uppercase fw-bold d-block mb-2">TOTAL</span>
                    <div class="fw-bold text-warning fs-6">${formatCurrency(shipment.amount)}</div>
                    ${currentTasa ? `<div class="text-muted font-monospace x-small mt-1" style="opacity:0.8;">Bs. ${(shipment.amount * currentTasa).toLocaleString("es-VE", {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>` : ''}
                </div>
                <div class="col-12 col-md-5">
                    <span class="text-white x-small text-uppercase fw-bold d-block mb-2">DESTINO</span>
                    <div class="text-white d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt text-muted mt-1"></i>
                        <span class="text-truncate fs-6" title="${shipment.address}">${shipment.address}</span>
                    </div>
                </div>
            </div>

            <!-- Action and Progress Section -->
            <div class="d-flex flex-column gap-3">
                <!-- Progress Block -->
                ${
                  shipment.status !== "recibido"
                    ? `
                    <div class="order-2 order-md-1">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2 gap-2">
                            <span class="text-muted small d-flex align-items-center gap-2">
                                <i class="fas fa-calendar-alt mt-1"></i>
                                <span>Entrega: <strong class="text-white">${
                                  shipment.estimatedDelivery
                                    ? formatDate(shipment.estimatedDelivery)
                                    : "N/A"
                                }</strong></span>
                                <!-- Mobile Status Text -->
                                <span class="d-md-none text-warning ms-1 fw-bold">- ${getProgressText(shipment.status)}</span>
                            </span>
                            <!-- Desktop Status Text -->
                            <span class="text-warning font-monospace small d-none d-md-block fw-bold">${getProgressText(shipment.status)}</span>
                        </div>
                        <div class="progress d-none d-md-flex mt-1 mb-2" style="height: 6px; background: rgba(255,255,255,0.05); border-radius: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: ${getProgressWidth(shipment.status)}%; border-radius: 6px;"></div>
                        </div>
                    </div>
                    `
                    : ""
                }

                <!-- Action Button Block -->
                <div class="order-1 order-md-2 mt-1 mt-md-0">
                    ${getStatusActions(shipment)}
                </div>
            </div>
        </div>
    `
    )
    .join("")
}

function getStatusBadge(status) {
  const badges = {
    pendiente: '<div class="status-badge status-pendiente"><i class="fas fa-clock"></i> Pendiente</div>',
    enviado: '<div class="status-badge status-enviado"><i class="fas fa-shipping-fast"></i> Enviado</div>',
    recibido: '<div class="status-badge status-entregado"><i class="fas fa-check-circle"></i> Entregado</div>'
  }
  return (
    badges[status] || '<div class="status-badge"><i class="fas fa-question"></i> Desconocido</div>'
  )
}

function getStatusActions(shipment) {
  switch (shipment.status) {
    case "pendiente":
      return `
                <button class="btn btn-warning w-100 fw-bold py-2" style="background-color: #ffb703; border-color: #ffb703; color: #000;" onclick="updateShipmentStatus(${shipment.id}, 'enviado')">
                    <i class="fas fa-truck me-2"></i> Marcar Enviado
                </button>
            `
    case "enviado":
      return `
                <button class="btn btn-success w-100 fw-bold py-2" onclick="updateShipmentStatus(${shipment.id}, 'recibido')">
                    <i class="fas fa-check-circle me-2"></i> Marcar Entregado
                </button>
            `
    case "recibido":
      return `
                <div class="text-success text-center fw-bold w-100 py-2 border border-success rounded bg-success bg-opacity-10">
                    <i class="fas fa-check-circle me-2"></i> Pedido Completado
                </div>
            `
    default:
      return ""
  }
}

function getProgressWidth(status) {
  const progress = {
    pendiente: 25,
    enviado: 75,
    recibido: 100
  }
  return progress[status] || 0
}

function getProgressText(status) {
  const texts = {
    pendiente: "Preparando envío...",
    enviado: "En tránsito...",
    recibido: "Entregado"
  }
  return texts[status] || ""
}

// updateStats function removed

async function filterShipments() {
  await loadShipments() // Load shipments with current filters
  renderShipments()
  // updateStats() removed
}

function setupFilters() {
  // Filters are applied in loadShipments now
  filterShipments()
}

function sortShipments() {
  const sortBy = document.getElementById("sortBy").value

  filteredShipments.sort((a, b) => {
    switch (sortBy) {
      case "date":
        return new Date(b.date) - new Date(a.date)
      case "status":
        return a.status.localeCompare(b.status)
      case "customer":
        return a.customer.localeCompare(b.customer)
      case "amount":
        return b.amount - a.amount
      default:
        return 0
    }
  })

  renderShipments()
}

async function updateShipmentStatus(shipmentId, newStatus) {
  const shipment = shipments.find((s) => s.id === shipmentId)
  if (!shipment) return

  const confirmMessage =
    newStatus === "enviado"
      ? "¿Confirmar que el pedido ha sido enviado?"
      : "¿Confirmar que el pedido ha sido entregado?"

  showModal({
    title: "Confirmar Actualización",
    message: confirmMessage,
    type: "confirm",
    onConfirm: async () => {
      let trackingNumber = shipment.trackingNumber
      if (newStatus === "enviado" && !trackingNumber) {
        trackingNumber = "TRK" + Date.now().toString().slice(-9)
      }

      try {
        const response = await fetchJSON(`php/api_shipments.php`, {
          method: "PUT",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            id: shipmentId,
            status: newStatus,
            trackingNumber: trackingNumber
          })
        })

        if (response.success) {
          showToast("Estado de envío actualizado exitosamente.", "success")
          // Update local data and re-render
          shipment.status = newStatus
          shipment.trackingNumber = trackingNumber
          filterShipments() // Re-apply filters and re-render to reflect changes
          // updateStats() removed
        } else {
          showToast(
            `Error al actualizar estado de envío: ${response.message}`,
            "error"
          )
        }
      } catch (error) {
        console.error("Error updating shipment status:", error)
        showToast(
          `Error al actualizar estado de envío: ${error.message}`,
          "error"
        )
      }
    }
  })
}

function formatDate(date) {
  const options = { year: "numeric", month: "2-digit", day: "2-digit" }
  return new Date(date).toLocaleDateString(undefined, options)
}

function formatCurrency(amount) {
  return parseFloat(amount).toLocaleString("en-US", { style: "currency", currency: "USD" })
}

// Removed - using showToast from common.js

// Clear filters function
window.clearFilters = function() {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('sortBy').value = 'date';
  filterShipments();
};
