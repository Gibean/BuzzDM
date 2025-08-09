// Shipments management functionality
let shipments = []
let filteredShipments = []

document.addEventListener("DOMContentLoaded", () => {
  initShipments()
  setupEventListeners()
})

function initShipments() {
  loadShipments()
  renderShipments()
  updateStats()
  setupFilters()
}

function setupEventListeners() {
  document.getElementById("searchInput").addEventListener("input", filterShipments)
  document.getElementById("statusFilter").addEventListener("change", filterShipments)
  document.getElementById("sortBy").addEventListener("change", sortShipments)
}

function loadShipments() {
  // Simulate loading shipments from API
  shipments = [
    {
      id: 1001,
      customer: "Juan Pérez",
      date: "2024-01-15",
      amount: 1709.97,
      status: "entregado",
      address: "Calle Mayor 123, Madrid, 28001",
      trackingNumber: "TRK001234567",
      estimatedDelivery: "2024-01-17",
    },
    {
      id: 1002,
      customer: "María García",
      date: "2024-01-14",
      amount: 359.98,
      status: "enviado",
      address: "Avenida de la Paz 45, Barcelona, 08001",
      trackingNumber: "TRK001234568",
      estimatedDelivery: "2024-01-18",
    },
    {
      id: 1003,
      customer: "Carlos López",
      date: "2024-01-13",
      amount: 149.99,
      status: "pendiente",
      address: "Plaza España 12, Valencia, 46001",
      trackingNumber: "",
      estimatedDelivery: "2024-01-19",
    },
    {
      id: 1004,
      customer: "Ana Martín",
      date: "2024-01-12",
      amount: 899.99,
      status: "enviado",
      address: "Gran Vía 88, Sevilla, 41001",
      trackingNumber: "TRK001234569",
      estimatedDelivery: "2024-01-16",
    },
    {
      id: 1005,
      customer: "Luis Rodríguez",
      date: "2024-01-11",
      amount: 299.99,
      status: "entregado",
      address: "Paseo de Gracia 100, Barcelona, 08008",
      trackingNumber: "TRK001234570",
      estimatedDelivery: "2024-01-15",
    },
  ]

  filteredShipments = [...shipments]
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
                        <small class="text-muted">Entrega estimada: ${formatDate(shipment.estimatedDelivery)}</small>
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

function updateStats() {
  const pending = shipments.filter((s) => s.status === "pendiente").length
  const shipped = shipments.filter((s) => s.status === "enviado").length
  const delivered = shipments.filter((s) => s.status === "entregado").length
  const total = shipments.length

  document.getElementById("pendingShipments").textContent = pending
  document.getElementById("shippedOrders").textContent = shipped
  document.getElementById("deliveredOrders").textContent = delivered
  document.getElementById("totalShipments").textContent = total
}

function setupFilters() {
  filterShipments()
}

function filterShipments() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase()
  const statusFilter = document.getElementById("statusFilter").value

  filteredShipments = shipments.filter((shipment) => {
    const matchesSearch =
      !searchTerm ||
      shipment.id.toString().includes(searchTerm) ||
      shipment.customer.toLowerCase().includes(searchTerm) ||
      shipment.address.toLowerCase().includes(searchTerm) ||
      (shipment.trackingNumber && shipment.trackingNumber.toLowerCase().includes(searchTerm))

    const matchesStatus = !statusFilter || shipment.status === statusFilter

    return matchesSearch && matchesStatus
  })

  sortShipments()
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

function updateShipmentStatus(shipmentId, newStatus) {
  const shipment = shipments.find((s) => s.id === shipmentId)
  if (!shipment) return

  const confirmMessage =
    newStatus === "enviado"
      ? "¿Confirmar que el pedido ha sido enviado?"
      : "¿Confirmar que el pedido ha sido entregado?"

  if (confirm(confirmMessage)) {
    shipment.status = newStatus

    // Generate tracking number if marking as shipped
    if (newStatus === "enviado" && !shipment.trackingNumber) {
      shipment.trackingNumber = "TRK" + Date.now().toString().slice(-9)
    }

    filterShipments()
    updateStats()

    const statusText = newStatus === "enviado" ? "enviado" : "entregado"
    showNotification(`Pedido #${shipmentId} marcado como ${statusText}`, "success")
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
