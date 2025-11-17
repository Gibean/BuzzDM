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
  document.getElementById("searchInput").addEventListener("input", filterShipments)
  document.getElementById("statusFilter").addEventListener("change", filterShipments)
  document.getElementById("sortBy").addEventListener("change", sortShipments)
}

async function loadShipments() {
  try {
    const searchInput = document.getElementById('searchInput').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const sortBy = document.getElementById('sortBy').value;

    let url = `/buzz/php/envios_actions.php?`;
    if (searchInput) {
        url += `&search=${encodeURIComponent(searchInput)}`;
    }
    if (statusFilter) {
        url += `&statusFilter=${encodeURIComponent(statusFilter)}`;
    }
    if (sortBy) {
        url += `&sortBy=${encodeURIComponent(sortBy)}`;
    }

    const data = await fetchJSON(url);
    if (data.success) {
      shipments = data.data.shipments; // Assuming 'data' contains 'shipments' array
      filteredShipments = [...shipments];
    } else {
      showNotification(`Error al cargar envíos: ${data.message}`, "error");
      shipments = [];
      filteredShipments = [];
    }
  } catch (error) {
    console.error('Error fetching shipments:', error);
    showNotification(`Error al cargar envíos: ${error.message}`, "error");
    shipments = [];
    filteredShipments = [];
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
        <div class="buzz-card mb-3">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="font-mono text-primary">#${shipment.id}</div>
                    <small class="text-muted">${formatDate(shipment.date)}</small>
                </div>
                <div class="col-md-3">
                    <div class="font-mono">${shipment.customer}</div>
                    <small class="text-muted">${formatCurrency(shipment.amount)}</small>
                </div>
                <div class="col-md-2">
                    ${getStatusBadge(shipment.status)}
                </div>
                <div class="col-md-3">
                    <div class="text-muted" style="font-size: 0.9rem;">
                        ${shipment.address.length > 40 ? shipment.address.substring(0, 40) + "..." : shipment.address}
                    </div>
                    ${shipment.trackingNumber ? `<small class="font-mono text-primary">Tracking: ${shipment.trackingNumber}</small>` : ""}
                </div>
                <div class="col-md-2 text-end">
                    ${getStatusActions(shipment)}
                </div>
            </div>
            
            ${
              shipment.status !== "entregado"
                ? `
                <div class="row mt-3 pt-3 border-top border-secondary">
                    <div class="col-md-6">
                        <small class="text-muted">Entrega estimada: ${shipment.estimatedDelivery ? formatDate(shipment.estimatedDelivery) : 'N/A'}</small>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: ${getProgressWidth(shipment.status)}%"></div>
                        </div>
                        <small class="text-muted">${getProgressText(shipment.status)}</small>
                    </div>
                </div>
            `
                : ""
            }
        </div>
    `,
    )
    .join("")
}

function getStatusBadge(status) {
  const badges = {
    pendiente: '<span class="badge badge-warning">Pendiente</span>',
    enviado: '<span class="badge badge-primary">Enviado</span>',
    entregado: '<span class="badge badge-success">Entregado</span>',
  }
  return badges[status] || '<span class="badge badge-secondary">Desconocido</span>'
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
                <button class="btn btn-success btn-sm" onclick="updateShipmentStatus(${shipment.id}, 'entregado')">
                    <i class="fas fa-check"></i> Marcar Entregado
                </button>
            `
    case "entregado":
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
    entregado: 100,
  }
  return progress[status] || 0
}

function getProgressText(status) {
  const texts = {
    pendiente: "Preparando envío...",
    enviado: "En tránsito...",
    entregado: "Entregado",
  }
  return texts[status] || ""
}

async function updateStats() {
  // Re-fetch data to get accurate stats from the backend
  await loadShipments(); 
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
  await loadShipments(); // Load shipments with current filters
  renderShipments();
  updateStats();
}

function setupFilters() {
  // Filters are applied in loadShipments now
  filterShipments();
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

  if (confirm(confirmMessage)) {
    let trackingNumber = shipment.trackingNumber;
    if (newStatus === "enviado" && !trackingNumber) {
      trackingNumber = "TRK" + Date.now().toString().slice(-9);
    }

    try {
      const response = await fetchJSON(`/buzz/php/envios_actions.php`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: shipmentId, status: newStatus, trackingNumber: trackingNumber })
      });

      if (response.success) {
        showNotification('Estado de envío actualizado exitosamente.', 'success');
        // Update local data and re-render
        shipment.status = newStatus;
        shipment.trackingNumber = trackingNumber;
        filterShipments(); // Re-apply filters and re-render to reflect changes
        updateStats();
      } else {
        showNotification(`Error al actualizar estado de envío: ${response.message}`, "error");
      }
    } catch (error) {
      console.error('Error updating shipment status:', error);
      showNotification(`Error al actualizar estado de envío: ${error.message}`, "error");
    }
  }
}

function formatDate(date) {
  const options = { year: "numeric", month: "2-digit", day: "2-digit" }
  return new Date(date).toLocaleDateString(undefined, options)
}

function formatCurrency(amount) {
  return amount.toLocaleString("es-ES", { style: "currency", currency: "EUR" })
}

function showNotification(message, type) {
  alert(`${type.toUpperCase()}: ${message}`)
}