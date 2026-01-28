// Shipments management functionality
let shipments = []
let filteredShipments = []

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

async function initShipments() {
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
        <div class="buzz-card mb-3 shipment-card-enhanced p-3">
            <div class="row g-2 align-items-center">
                <!-- Row 1 Mobile: Order ID & Status -->
                <div class="col-6 col-md-2 order-1">
                    <div class="d-flex flex-column">
                        <span class="text-muted x-small text-uppercase fw-bold">Orden</span>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="shipment-id fs-5">#${shipment.id}</span>
                            <span class="shipment-date text-muted x-small d-none d-md-inline">${formatDate(shipment.date)}</span>
                        </div>
                        <span class="shipment-date text-muted x-small d-md-none">${formatDate(shipment.date)}</span>
                    </div>
                </div>

                <!-- Row 1 Mobile (Right): Status -->
                <div class="col-6 col-md-2 order-2 text-end text-md-start">
                    <span class="text-muted x-small text-uppercase fw-bold d-none d-md-block mb-1">Estado</span>
                    ${getStatusBadge(shipment.status)}
                </div>

                <!-- Row 2 Mobile: Client & Amount -->
                <div class="col-7 col-md-3 order-3 order-md-2">
                    <div class="d-flex flex-column">
                        <span class="text-muted x-small text-uppercase fw-bold">Cliente</span>
                        <div class="shipment-customer-name text-truncate fw-medium" title="${shipment.customer}">${shipment.customer}</div>
                    </div>
                </div>

                <div class="col-5 col-md-1 order-3 order-md-2 text-end text-md-start">
                     <div class="d-flex flex-column">
                        <span class="text-muted x-small text-uppercase fw-bold d-md-none">Total</span>
                        <span class="shipment-amount fw-bold text-primary">${formatCurrency(shipment.amount)}</span>
                    </div>
                </div>

                <!-- Row 3 Mobile: Destination -->
                <div class="col-12 col-md-2 order-4 order-md-3">
                     <div class="d-flex flex-column">
                        <span class="text-muted x-small text-uppercase fw-bold mb-1">Destino</span>
                        <div class="d-flex align-items-center gap-2">
                             <div class="shipment-address-text small text-truncate flex-grow-1" title="${shipment.address}">
                                ${shipment.address}
                            </div>
                        </div>
                        ${
                          shipment.trackingNumber
                            ? `<div class="tracking-badge mt-1 d-inline-flex border rounded px-2 py-0 border-opacity-25 border-warning bg-warning bg-opacity-10 text-warning x-small">
                                <i class="fas fa-barcode me-1"></i> ${shipment.trackingNumber}
                               </div>`
                            : ""
                        }
                    </div>
                </div>

                <!-- Row 4 Mobile: Actions -->
                <div class="col-12 col-md-2 order-5 text-end order-md-last">
                     <div class="d-grid d-md-block">
                        ${getStatusActions(shipment)}
                    </div>
                </div>
            </div>

            ${
              shipment.status !== "recibido"
                ? `
                <div class="row mt-2 pt-2 border-top border-secondary border-opacity-10 align-items-center g-2">
                    <div class="col-auto">
                        <small class="text-muted x-small"><i class="fas fa-calendar-alt me-1"></i> Entrega: <strong>${
                          shipment.estimatedDelivery
                            ? formatDate(shipment.estimatedDelivery)
                            : "N/A"
                        }</strong></small>
                    </div>
                    <div class="col">
                         <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: ${getProgressWidth(shipment.status)}%"></div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <small class="text-warning font-mono x-small">${getProgressText(shipment.status)}</small>
                    </div>
                </div>
            `
                : ""
            }
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
                <button class="btn btn-buzz btn-sm" onclick="updateShipmentStatus(${shipment.id}, 'enviado')">
                    <i class="fas fa-shipping-fast"></i> Marcar Enviado
                </button>
            `
    case "enviado":
      return `
                <button class="btn btn-success btn-sm" onclick="updateShipmentStatus(${shipment.id}, 'recibido')">
                    <i class="fas fa-check"></i> Marcar Entregado
                </button>
            `
    case "recibido":
      return `
                <span class="text-success font-mono">
                    <i class="fas fa-check-circle"></i> Completado
                </span>
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
  return amount.toLocaleString("en-US", { style: "currency", currency: "USD" })
}

// Removed - using showToast from common.js

// Clear filters function
window.clearFilters = function() {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('sortBy').value = 'date';
  filterShipments();
};
