// Orders management functionality
let orders = []
let filteredOrders = []
let editingOrderId = null
let orderProducts = []

// Sample products for order creation
const availableProducts = [
  { id: 1, sku: "LAP-001", name: "Laptop Gaming Pro", price: 1499.99, stock: 15 },
  { id: 2, sku: "MOU-005", name: "Mouse Inalámbrico", price: 59.99, stock: 7 },
  { id: 3, sku: "KEY-003", name: "Teclado Mecánico", price: 149.99, stock: 2 },
  { id: 4, sku: "AUR-004", name: "Auriculares Premium", price: 299.99, stock: 25 },
]

// Declare formatDate, formatCurrency, and showNotification functions
function formatDate(date) {
  return new Date(date).toLocaleDateString()
}

function formatCurrency(amount) {
  return amount.toLocaleString("es-ES", { style: "currency", currency: "EUR" })
}

function showNotification(message, type) {
  alert(`${type.toUpperCase()}: ${message}`)
}

document.addEventListener("DOMContentLoaded", () => {
  initOrders()
  setupEventListeners()
})

function initOrders() {
  loadOrders()
  renderOrders()
  updateStats()
  setupFilters()
}

function setupEventListeners() {
  // Search and filters
  document.getElementById("searchInput").addEventListener("input", filterOrders)
  document.getElementById("paymentFilter").addEventListener("change", filterOrders)
  document.getElementById("dateFrom").addEventListener("change", filterOrders)
  document.getElementById("dateTo").addEventListener("change", filterOrders)

  // Order form
  document.getElementById("orderForm").addEventListener("submit", handleOrderSubmit)
}

function loadOrders() {
  // Simulate loading orders from API
  orders = [
    {
      id: 1001,
      customer: "Juan Pérez",
      email: "juan@email.com",
      date: "2024-01-15",
      total: 1709.97,
      paymentStatus: "pagado",
      shippingAddress: "Calle Mayor 123, Madrid, 28001",
      shippingNotes: "Entregar en horario de mañana",
      products: [
        { sku: "LAP-001", name: "Laptop Gaming Pro", price: 1499.99, quantity: 1 },
        { sku: "MOU-005", name: "Mouse Inalámbrico", price: 59.99, quantity: 1 },
        { sku: "KEY-003", name: "Teclado Mecánico", price: 149.99, quantity: 1 },
      ],
    },
    {
      id: 1002,
      customer: "María García",
      email: "maria@email.com",
      date: "2024-01-14",
      total: 359.98,
      paymentStatus: "no-pagado",
      shippingAddress: "Avenida de la Paz 45, Barcelona, 08001",
      shippingNotes: "",
      products: [
        { sku: "AUR-004", name: "Auriculares Premium", price: 299.99, quantity: 1 },
        { sku: "MOU-005", name: "Mouse Inalámbrico", price: 59.99, quantity: 1 },
      ],
    },
    {
      id: 1003,
      customer: "Carlos López",
      email: "carlos@email.com",
      date: "2024-01-13",
      total: 149.99,
      paymentStatus: "pagado",
      shippingAddress: "Plaza España 12, Valencia, 46001",
      shippingNotes: "Llamar antes de entregar",
      products: [{ sku: "KEY-003", name: "Teclado Mecánico", price: 149.99, quantity: 1 }],
    },
  ]

  filteredOrders = [...orders]
}

function renderOrders() {
  const ordersList = document.getElementById("ordersList")

  if (filteredOrders.length === 0) {
    ordersList.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron pedidos</h5>
                <p class="text-muted">Intenta ajustar los filtros o crear un nuevo pedido</p>
            </div>
        `
    return
  }

  ordersList.innerHTML = filteredOrders
    .map(
      (order) => `
        <div class="border border-secondary rounded mb-3 p-3">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="font-mono text-primary">#${order.id}</div>
                    <small class="text-muted">${formatDate(order.date)}</small>
                </div>
                <div class="col-md-3">
                    <div class="font-mono">${order.customer}</div>
                    <small class="text-muted">${order.email}</small>
                </div>
                <div class="col-md-2">
                    <div class="font-mono">${formatCurrency(order.total)}</div>
                </div>
                <div class="col-md-2">
                    <span class="badge badge-${order.paymentStatus === "pagado" ? "success" : "warning"}">
                        ${order.paymentStatus === "pagado" ? "Pagado" : "No Pagado"}
                    </span>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-outline-buzz btn-sm me-1" onclick="viewOrderDetail(${order.id})" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-buzz btn-sm me-1" onclick="editOrder(${order.id})" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-buzz btn-sm" onclick="toggleOrderProducts(${order.id})" title="Ver productos">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            
            <!-- Collapsible products section -->
            <div id="orderProducts_${order.id}" class="mt-3" style="display: none;">
                <div class="border-top border-secondary pt-3">
                    <h6 class="text-primary font-mono mb-2">PRODUCTOS:</h6>
                    ${order.products
                      .map(
                        (product) => `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="font-mono">${product.sku}</span> - ${product.name}
                            </div>
                            <div class="text-end">
                                <span class="text-muted">Cant: ${product.quantity}</span>
                                <span class="font-mono ms-2">${formatCurrency(product.price * product.quantity)}</span>
                            </div>
                        </div>
                    `,
                      )
                      .join("")}
                    <div class="mt-2 pt-2 border-top border-secondary">
                        <div><strong>Dirección:</strong> ${order.shippingAddress}</div>
                        ${order.shippingNotes ? `<div><strong>Notas:</strong> ${order.shippingNotes}</div>` : ""}
                    </div>
                </div>
            </div>
        </div>
    `,
    )
    .join("")
}

function updateStats() {
  const totalOrders = orders.length
  const pendingOrders = orders.filter((o) => o.paymentStatus === "no-pagado").length
  const paidOrders = orders.filter((o) => o.paymentStatus === "pagado").length
  const totalRevenue = orders.reduce((sum, order) => sum + order.total, 0)

  document.getElementById("totalOrders").textContent = totalOrders
  document.getElementById("pendingOrders").textContent = pendingOrders
  document.getElementById("paidOrders").textContent = paidOrders
  document.getElementById("totalRevenue").textContent = formatCurrency(totalRevenue)
}

function setupFilters() {
  filterOrders()
}

function filterOrders() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase()
  const paymentFilter = document.getElementById("paymentFilter").value
  const dateFrom = document.getElementById("dateFrom").value
  const dateTo = document.getElementById("dateTo").value

  filteredOrders = orders.filter((order) => {
    const matchesSearch =
      !searchTerm ||
      order.id.toString().includes(searchTerm) ||
      order.customer.toLowerCase().includes(searchTerm) ||
      order.email.toLowerCase().includes(searchTerm)

    const matchesPayment = !paymentFilter || order.paymentStatus === paymentFilter

    const matchesDateFrom = !dateFrom || order.date >= dateFrom
    const matchesDateTo = !dateTo || order.date <= dateTo

    return matchesSearch && matchesPayment && matchesDateFrom && matchesDateTo
  })

  renderOrders()
}

function toggleOrderProducts(orderId) {
  const productsDiv = document.getElementById(`orderProducts_${orderId}`)
  const isVisible = productsDiv.style.display !== "none"
  productsDiv.style.display = isVisible ? "none" : "block"

  // Update chevron icon
  const button = event.target.closest("button")
  const icon = button.querySelector("i")
  icon.className = isVisible ? "fas fa-chevron-down" : "fas fa-chevron-up"
}

function openOrderModal(orderId = null) {
  editingOrderId = orderId
  const modal = document.getElementById("orderModal")
  const modalTitle = document.getElementById("orderModalTitle")

  if (orderId) {
    const order = orders.find((o) => o.id === orderId)
    modalTitle.textContent = "Editar Pedido"
    fillOrderForm(order)
  } else {
    modalTitle.textContent = "Nuevo Pedido"
    clearOrderForm()
  }

  modal.style.display = "block"
}

function closeOrderModal() {
  document.getElementById("orderModal").style.display = "none"
  editingOrderId = null
  orderProducts = []
}

function fillOrderForm(order) {
  document.getElementById("customerName").value = order.customer
  document.getElementById("customerEmail").value = order.email
  document.getElementById("shippingAddress").value = order.shippingAddress
  document.getElementById("shippingNotes").value = order.shippingNotes
  document.getElementById("paymentStatus").value = order.paymentStatus

  orderProducts = [...order.products]
  renderOrderProducts()
  calculateOrderTotal()
}

function clearOrderForm() {
  document.getElementById("orderForm").reset()
  orderProducts = []
  renderOrderProducts()
  calculateOrderTotal()
}

function addProductToOrder() {
  const productSelect = `
        <div class="border border-secondary rounded p-3 mb-2 order-product-item">
            <div class="row align-items-center">
                <div class="col-md-5">
                    <select class="form-control form-control-buzz product-select" onchange="updateProductInfo(this)">
                        <option value="">Seleccionar producto...</option>
                        ${availableProducts.map((p) => `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}">${p.name} (${p.sku})</option>`).join("")}
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control form-control-buzz quantity-input" placeholder="Cant." min="1" onchange="calculateOrderTotal()">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control form-control-buzz price-display" readonly placeholder="Precio">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeProductFromOrder(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `

  document.getElementById("orderProducts").insertAdjacentHTML("beforeend", productSelect)
}

function removeProductFromOrder(button) {
  button.closest(".order-product-item").remove()
  calculateOrderTotal()
}

function updateProductInfo(select) {
  const option = select.selectedOptions[0]
  const container = select.closest(".order-product-item")
  const priceDisplay = container.querySelector(".price-display")
  const quantityInput = container.querySelector(".quantity-input")

  if (option.value) {
    const price = Number.parseFloat(option.dataset.price)
    const stock = Number.parseInt(option.dataset.stock)

    priceDisplay.value = formatCurrency(price)
    quantityInput.max = stock
    quantityInput.value = 1
  } else {
    priceDisplay.value = ""
    quantityInput.value = ""
  }

  calculateOrderTotal()
}

function calculateOrderTotal() {
  let total = 0
  const productItems = document.querySelectorAll(".order-product-item")

  productItems.forEach((item) => {
    const select = item.querySelector(".product-select")
    const quantityInput = item.querySelector(".quantity-input")

    if (select.value && quantityInput.value) {
      const price = Number.parseFloat(select.selectedOptions[0].dataset.price)
      const quantity = Number.parseInt(quantityInput.value)
      total += price * quantity
    }
  })

  document.getElementById("orderTotal").value = formatCurrency(total)
}

function renderOrderProducts() {
  const container = document.getElementById("orderProducts")
  container.innerHTML = ""

  orderProducts.forEach((product) => {
    addProductToOrder()
    const lastItem = container.lastElementChild
    const select = lastItem.querySelector(".product-select")
    const quantityInput = lastItem.querySelector(".quantity-input")

    // Find and select the product
    const productOption = Array.from(select.options).find((option) => {
      const productData = availableProducts.find((p) => p.id == option.value)
      return productData && productData.sku === product.sku
    })

    if (productOption) {
      select.value = productOption.value
      updateProductInfo(select)
      quantityInput.value = product.quantity
    }
  })
}

function handleOrderSubmit(e) {
  e.preventDefault()

  // Collect product data
  const productItems = document.querySelectorAll(".order-product-item")
  const products = []

  productItems.forEach((item) => {
    const select = item.querySelector(".product-select")
    const quantityInput = item.querySelector(".quantity-input")

    if (select.value && quantityInput.value) {
      const selectedProduct = availableProducts.find((p) => p.id == select.value)
      products.push({
        sku: selectedProduct.sku,
        name: selectedProduct.name,
        price: selectedProduct.price,
        quantity: Number.parseInt(quantityInput.value),
      })
    }
  })

  if (products.length === 0) {
    showNotification("Debes agregar al menos un producto al pedido", "error")
    return
  }

  const total = products.reduce((sum, product) => sum + product.price * product.quantity, 0)

  const orderData = {
    customer: document.getElementById("customerName").value,
    email: document.getElementById("customerEmail").value,
    shippingAddress: document.getElementById("shippingAddress").value,
    shippingNotes: document.getElementById("shippingNotes").value,
    paymentStatus: document.getElementById("paymentStatus").value,
    products: products,
    total: total,
    date: new Date().toISOString().split("T")[0],
  }

  if (editingOrderId) {
    updateOrder(editingOrderId, orderData)
  } else {
    createOrder(orderData)
  }
}

function createOrder(orderData) {
  const newOrder = {
    id: Math.max(...orders.map((o) => o.id)) + 1,
    ...orderData,
  }

  orders.push(newOrder)
  filterOrders()
  updateStats()
  closeOrderModal()
  showNotification("Pedido creado exitosamente", "success")
}

function updateOrder(orderId, orderData) {
  const orderIndex = orders.findIndex((o) => o.id === orderId)
  if (orderIndex !== -1) {
    orders[orderIndex] = { ...orders[orderIndex], ...orderData }
    filterOrders()
    updateStats()
    closeOrderModal()
    showNotification("Pedido actualizado exitosamente", "success")
  }
}

function editOrder(orderId) {
  openOrderModal(orderId)
}

function viewOrderDetail(orderId) {
  const order = orders.find((o) => o.id === orderId)
  if (!order) return

  const modal = document.getElementById("orderDetailModal")
  const content = document.getElementById("orderDetailContent")

  content.innerHTML = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-primary font-mono">INFORMACIÓN DEL PEDIDO</h6>
                <p><strong>ID:</strong> #${order.id}</p>
                <p><strong>Fecha:</strong> ${formatDate(order.date)}</p>
                <p><strong>Cliente:</strong> ${order.customer}</p>
                <p><strong>Email:</strong> ${order.email}</p>
                <p><strong>Estado de pago:</strong> 
                    <span class="badge badge-${order.paymentStatus === "pagado" ? "success" : "warning"}">
                        ${order.paymentStatus === "pagado" ? "Pagado" : "No Pagado"}
                    </span>
                </p>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary font-mono">ENVÍO</h6>
                <p><strong>Dirección:</strong><br>${order.shippingAddress}</p>
                ${order.shippingNotes ? `<p><strong>Notas:</strong><br>${order.shippingNotes}</p>` : ""}
            </div>
        </div>
        
        <h6 class="text-primary font-mono mb-3">PRODUCTOS</h6>
        <div class="table-responsive">
            <table class="table table-buzz">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${order.products
                      .map(
                        (product) => `
                        <tr>
                            <td class="font-mono">${product.sku}</td>
                            <td>${product.name}</td>
                            <td class="font-mono">${formatCurrency(product.price)}</td>
                            <td class="font-mono">${product.quantity}</td>
                            <td class="font-mono">${formatCurrency(product.price * product.quantity)}</td>
                        </tr>
                    `,
                      )
                      .join("")}
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">TOTAL:</th>
                        <th class="font-mono text-primary">${formatCurrency(order.total)}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button class="btn btn-outline-buzz" onclick="closeOrderDetailModal()">Cerrar</button>
            <button class="btn btn-buzz" onclick="editOrder(${order.id}); closeOrderDetailModal();">
                <i class="fas fa-edit"></i> Editar Pedido
            </button>
        </div>
    `

  modal.style.display = "block"
}

function closeOrderDetailModal() {
  document.getElementById("orderDetailModal").style.display = "none"
}

// Close modals when clicking outside
document.getElementById("orderModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeOrderModal()
  }
})

document.getElementById("orderDetailModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeOrderDetailModal()
  }
})

// Initialize with one product row
document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    if (document.getElementById("orderProducts").children.length === 0) {
      addProductToOrder()
    }
  }, 100)
})
