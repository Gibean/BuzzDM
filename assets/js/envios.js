// Shipments management functionality
let shipments = []
let filteredShipments = []

document.addEventListener("DOMContentLoaded", () => {
  initShipments()
  setupEventListeners()
})

async function initShipments() {
  await loadShipments()
  renderShipments()
  updateStats()
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

    let url = `php/envios_actions.php?`
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
        <div class="buzz-card mb-3 shipment-card-enhanced">
            <div class="shipment-grid">
                <!-- Column 1: Order Recap -->
                <div class="shipment-col shipment-col-id">
                    <span class="text-muted small">ORDEN</span>
                    <div class="shipment-id">#${shipment.id}</div>
                    <div class="shipment-date">${formatDate(shipment.date)}</div>
                </div>

                <!-- Column 2: Customer Recap -->
                <div class="shipment-col shipment-col-customer">
                    <span class="text-muted small">CLIENTE</span>
                    <div class="shipment-customer-name">${shipment.customer}</div>
                    <div class="shipment-amount">${formatCurrency(shipment.amount)}</div>
                </div>

                <!-- Column 3: Status -->
                <div class="shipment-col shipment-col-status">
                    <span class="text-muted small mb-1">ESTADO</span>
                    ${getStatusBadge(shipment.status)}
                </div>

                <!-- Column 4: Delivery Details -->
                <div class="shipment-col shipment-col-address">
                    <span class="text-muted small">DESTINO</span>
                    <div class="shipment-address-text" title="${shipment.address}">
                        ${shipment.address}
                    </div>
                    ${
                      shipment.trackingNumber
                        ? `<div class="tracking-badge mt-2">
                            <i class="fas fa-barcode"></i>
                            <span>${shipment.trackingNumber}</span>
                           </div>`
                        : ""
                    }
                </div>

                <!-- Column 5: Actions -->
                <div class="shipment-col shipment-col-actions text-end">
                    <span class="text-muted small mb-2 d-none d-xl-block">ACCIONES</span>
                    ${getStatusActions(shipment)}
                </div>
            </div>

            ${
              shipment.status !== "recibido"
                ? `
                <div class="shipment-progress-row">
                    <div class="shipment-meta-info">
                        <i class="fas fa-calendar-alt text-muted"></i>
                        <span>Entrega estimada: <strong>${
                          shipment.estimatedDelivery
                            ? formatDate(shipment.estimatedDelivery)
                            : "N/A"
                        }</strong></span>
                    </div>
                    <div class="shipment-progress-container">
                        <div class="progress-buzz">
                            <div class="progress-buzz-bar" style="width: ${getProgressWidth(
                              shipment.status
                            )}%"></div>
                        </div>
                        <div class="progress-text-enhanced">
                            <i class="fas fa-truck-loading"></i>
                            <span>${getProgressText(shipment.status)}</span>
                        </div>
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

async function updateStats() {
  // Re-fetch data to get accurate stats from the backend
  await loadShipments()
  const pending = shipments.filter((s) => s.status === "pendiente").length
  const shipped = shipments.filter((s) => s.status === "enviado").length
  const delivered = shipments.filter((s) => s.status === "entregado").length
  const total = shipments.length

  document.getElementById("pendingShipments").textContent = pending
  document.getElementById("shippedOrders").textContent = shipped
  document.getElementById("deliveredOrders").textContent = delivered
  document.getElementById("totalShipments").textContent = total
}

async function filterShipments() {
  await loadShipments() // Load shipments with current filters
  renderShipments()
  updateStats()
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
        const response = await fetchJSON(`php/envios_actions.php`, {
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
          updateStats()
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
  return 'Pref. ' + amount.toLocaleString("en-US", { style: "currency", currency: "USD" })
}

// Removed - using showToast from common.js

// Clear filters function
window.clearFilters = function() {
  document.getElementById('searchInput').value = '';
  document.getElementById('statusFilter').value = '';
  document.getElementById('sortBy').value = 'date';
  filterShipments();
};
