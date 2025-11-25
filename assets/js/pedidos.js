// Orders management functionality
let currentPage = 1;
const ordersPerPage = 10;

// Main function to fetch and render orders
async function fetchAndRenderOrders() {
  const ordersListContainer = document.getElementById("ordersList")
  ordersListContainer.innerHTML = `<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i><h5 class="text-muted mt-3">Cargando pedidos...</h5></div>`

  try {
    const searchInput = document.getElementById("searchInput").value
    const paymentFilter = document.getElementById("paymentFilter").value
    const dateFrom = document.getElementById("dateFrom").value
    const dateTo = document.getElementById("dateTo").value

    let url = `php/pedidos_actions.php?page=${currentPage}&limit=${ordersPerPage}`
    if (searchInput) {
      url += `&search=${encodeURIComponent(searchInput)}`
    }
    if (paymentFilter) {
      url += `&paymentStatus=${encodeURIComponent(paymentFilter)}`
    }
    if (dateFrom) {
      url += `&dateFrom=${encodeURIComponent(dateFrom)}`
    }
    if (dateTo) {
      url += `&dateTo=${encodeURIComponent(dateTo)}`
    }

    const data = await fetchJSON(url)

    renderOrders(data.orders)
    updateStats(data.orders)
    // renderPagination(data.total); // Pagination not implemented yet
  } catch (error) {
    console.error("Error fetching orders:", error)
    showToast(`Error al cargar pedidos: ${error.message}`, "error")
    ordersListContainer.innerHTML = `<div class="text-center py-5 text-danger">Error al cargar pedidos.</div>` // Keep a visual indicator
  }
}

function renderOrders(orders) {
  const ordersList = document.getElementById("ordersList")
  ordersList.innerHTML = "" // Clear previous content

  if (!orders || orders.length === 0) {
    ordersList.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron pedidos</h5>
                <p class="text-muted">Intenta ajustar los filtros o crear un nuevo pedido</p>
            </div>
        `
    return
  }

  ordersList.innerHTML = orders
    .map(
      (order) => `
        <div class="border border-secondary rounded mb-3 p-3 order-card">
            <div class="row align-items-center">
                <div class="col-md-2">
                    <div class="font-mono text-primary">#${order.id}</div>
                    <small class="text-muted">${formatDate(order.date)}</small>
                </div>
                <div class="col-md-3">
                    <div class="font-mono">${
                      order.customer || "Cliente no especificado"
                    }</div>
                    <small class="text-muted">${order.email || "N/A"}</small>
                </div>
                <div class="col-md-2">
                    <div class="font-mono">${formatCurrency(order.total)}</div>
                </div>
                <div class="col-md-2">
                    <span class="badge badge-${
                      order.paymentStatus === "pagado" ? "success" : "warning"
                    }">
                        ${
                          order.paymentStatus === "pagado"
                            ? "Pagado"
                            : "No Pagado"
                        }
                    </span>
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-outline-buzz btn-sm me-1" onclick="viewOrderDetail(${
                      order.id
                    })" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-buzz btn-sm me-1" onclick="editOrder(${
                      order.id
                    })" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-buzz btn-sm me-1" onclick="toggleOrderProducts(this, ${
                      order.id
                    })" title="Ver productos">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteOrder(${
                      order.id
                    })" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Collapsible products section will be loaded on demand -->
            <div id="orderProducts_${
              order.id
            }" class="mt-3" style="display: none;">
                <div class="text-center p-3"><i class="fas fa-spinner fa-spin text-muted"></i> Cargando productos...</div>
            </div>
        </div>
    `
    )
    .join("")
}

function updateStats(orders) {
  const totalOrders = orders.length
  const pendingOrders = orders.filter(
    (o) => o.paymentStatus === "no-pagado"
  ).length
  const paidOrders = orders.filter((o) => o.paymentStatus === "pagado").length
  const totalRevenue = orders.reduce(
    (sum, order) => sum + parseFloat(order.total),
    0
  )

  document.getElementById("totalOrders").textContent = totalOrders
  document.getElementById("pendingOrders").textContent = pendingOrders
  document.getElementById("paidOrders").textContent = paidOrders
  document.getElementById("totalRevenue").textContent =
    formatCurrency(totalRevenue)
}

async function toggleOrderProducts(button, orderId) {
  const productsDiv = document.getElementById(`orderProducts_${orderId}`)
  const icon = button.querySelector("i")
  const isVisible = productsDiv.style.display !== "none"

  if (isVisible) {
    productsDiv.style.display = "none"
    icon.className = "fas fa-chevron-down"
  } else {
    productsDiv.style.display = "block"
    icon.className = "fas fa-chevron-up"
    if (!productsDiv.dataset.loaded) {
      productsDiv.innerHTML = `<div class="text-center p-3"><i class="fas fa-spinner fa-spin text-muted"></i> Cargando productos...</div>`
      try {
        const data = await fetchJSON(
          `php/pedidos_actions.php?action=get_products&order_id=${orderId}`
        )
        if (data.products && data.products.length > 0) {
          productsDiv.innerHTML = `
                        <table class="table table-sm table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.products
                                  .map(
                                    (p) => `
                                    <tr>
                                        <td>${p.product_name}</td>
                                        <td>${p.cantidad}</td>
                                        <td>${formatCurrency(
                                          p.precio_unitario
                                        )}</td>
                                        <td>${formatCurrency(
                                          p.cantidad * p.precio_unitario
                                        )}</td>
                                    </tr>
                                `
                                  )
                                  .join("")}
                            </tbody>
                        </table>
                    `
        } else {
          productsDiv.innerHTML = `<div class="p-3 text-muted">No hay productos para este pedido.</div>`
        }
        productsDiv.dataset.loaded = true
      } catch (error) {
        console.error("Error fetching order products:", error)
        productsDiv.innerHTML = `<div class="p-3 text-danger">Error al cargar productos: ${error.message}</div>`
      }
    }
  }
}

async function viewOrderDetail(orderId) {
  try {
    const data = await fetchJSON(`php/pedidos_actions.php?id=${orderId}`)
    const order = data.order

    if (order) {
      // Assuming a modal with ID 'orderDetailModal' and elements to display info
      document.getElementById("detailOrderId").textContent = order.id
      document.getElementById("detailOrderReference").textContent =
        order.referencia
      document.getElementById("detailOrderCustomer").textContent =
        order.customer_name || "N/A"
      document.getElementById("detailOrderEmail").textContent =
        order.customer_email || "N/A"
      document.getElementById("detailOrderDate").textContent = formatDateTime(
        order.fecha_creacion
      )
      document.getElementById("detailOrderTotal").textContent = formatCurrency(
        order.total
      )
      document.getElementById("detailOrderPaymentStatus").textContent =
        order.estado_pago
      document.getElementById("detailOrderShippingStatus").textContent =
        order.estado_envio
      document.getElementById("detailOrderShippingAddress").textContent =
        order.direccion_envio || "N/A"
      document.getElementById("detailOrderShippingNotes").textContent =
        order.notas_envio || "N/A"

      const productsHtml =
        order.products && order.products.length > 0
          ? `
                <h6>Productos:</h6>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.products
                          .map(
                            (p) => `
                            <tr>
                                <td>${p.product_name}</td>
                                <td>${p.cantidad}</td>
                                <td>${formatCurrency(p.precio_unitario)}</td>
                                <td>${formatCurrency(
                                  p.cantidad * p.precio_unitario
                                )}</td>
                            </tr>
                        `
                          )
                          .join("")}
                    </tbody>
                </table>
            `
          : "<p>No hay productos asociados a este pedido.</p>"
      document.getElementById("detailOrderProducts").innerHTML = productsHtml

      // Show the modal (assuming Bootstrap 5 modal)
      const detailModal = new bootstrap.Modal(
        document.getElementById("orderDetailModal")
      )
      detailModal.show()
    } else {
      showToast("Pedido no encontrado.", "error")
    }
  } catch (error) {
    console.error("Error fetching order details:", error)
    showToast(`Error al cargar detalles del pedido: ${error.message}`, "error")
  }
}

async function editOrder(orderId) {
  try {
    const data = await fetchJSON(`php/pedidos_actions.php?id=${orderId}`)
    const order = data.order

    if (order) {
      const editModalElement = document.getElementById("editOrderModal")
      const editModal = new bootstrap.Modal(editModalElement)

      // Populate the edit modal form
      document.getElementById("editOrderId").textContent = order.id
      document.getElementById("editOrderIdHidden").value = order.id
      document.getElementById("editOrderReference").value = order.referencia
      document.getElementById("editOrderCustomerId").value = order.cliente_id
      document.getElementById("editOrderPaymentStatus").value =
        order.estado_pago
      document.getElementById("editOrderShippingStatus").value =
        order.estado_envio
      document.getElementById("editOrderShippingAddress").value =
        order.direccion_envio
      document.getElementById("editOrderShippingNotes").value =
        order.notas_envio

      const editProductsContainer = document.getElementById(
        "editOrderProductsContainer"
      )
      const addEditOrderProductRowButton = document.getElementById(
        "addEditOrderProductRow"
      )
      const editOrderCalculatedTotalSpan = document.getElementById(
        "editOrderCalculatedTotal"
      )

      // Clear previous products and reset counter
      editProductsContainer.innerHTML = ""
      let editProductRowCounter = 0

      const updateEditCalculatedTotal = () => {
        let total = 0
        editProductsContainer
          .querySelectorAll(".product-row")
          .forEach((row) => {
            const quantity =
              parseFloat(row.querySelector(".product-quantity-input").value) ||
              0
            const price =
              parseFloat(row.querySelector(".product-price-input").value) || 0
            total += quantity * price
          })
        editOrderCalculatedTotalSpan.textContent = formatCurrency(total)
      }

      const addEditProductRow = (product = null) => {
        editProductRowCounter++
        const newRow = document.createElement("div")
        newRow.classList.add("row", "mb-2", "product-row")
        newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-buzz product-search-input" placeholder="Nombre o SKU del producto" value="${
                          product ? product.product_name : ""
                        }">
                        <input type="hidden" class="product-id-input" value="${
                          product ? product.producto_id : ""
                        }">
                        <input type="hidden" class="product-price-input" value="${
                          product ? product.precio_unitario : ""
                        }">
                        <div class="product-search-results list-group position-absolute w-100" style="z-index: 999;"></div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control form-control-buzz product-quantity-input" value="${
                          product ? product.cantidad : 1
                        }" min="1">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control form-control-buzz product-subtotal-display" value="${formatCurrency(
                          product
                            ? product.cantidad * product.precio_unitario
                            : 0
                        )}" readonly>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-product-row"><i class="fas fa-trash"></i></button>
                    </div>
                `
        editProductsContainer.appendChild(newRow)

        const productSearchInput = newRow.querySelector(".product-search-input")
        const productIdInput = newRow.querySelector(".product-id-input")
        const productPriceInput = newRow.querySelector(".product-price-input")
        const productQuantityInput = newRow.querySelector(
          ".product-quantity-input"
        )
        const productSearchResultsDiv = newRow.querySelector(
          ".product-search-results"
        )
        const productSubtotalDisplay = newRow.querySelector(
          ".product-subtotal-display"
        )
        const removeRowButton = newRow.querySelector(".remove-product-row")

        const debounce = (func, delay) => {
          let timeout
          return function (...args) {
            const context = this
            clearTimeout(timeout)
            timeout = setTimeout(() => func.apply(context, args), delay)
          }
        }

        const searchProducts = async () => {
          const query = productSearchInput.value.trim()
          if (query.length < 2) {
            productSearchResultsDiv.innerHTML = ""
            return
          }

          try {
            const data = await fetchJSON(
              `php/search_api.php?action=search_products&query=${encodeURIComponent(
                query
              )}`
            )
            if (data.success && data.products.length > 0) {
              productSearchResultsDiv.innerHTML = data.products
                .map((p) => {
                  const isOutOfStock = p.stock === 0
                  const stockInfo = isOutOfStock
                    ? '<span class="badge bg-danger ms-2">Sin stock</span>'
                    : `<span class="badge bg-success ms-2">Stock: ${p.stock}</span>`

                  return `
                                    <a href="#" class="list-group-item list-group-item-action ${
                                      isOutOfStock
                                        ? "list-group-item-danger disabled"
                                        : ""
                                    }"
                                       data-product-id="${p.id}"
                                       data-product-name="${p.nombre}"
                                       data-product-price="${p.precio}"
                                       data-product-stock="${p.stock}"
                                       ${
                                         isOutOfStock
                                           ? 'style="pointer-events: none; opacity: 0.6;"'
                                           : ""
                                       }>
                                        ${p.nombre} (SKU: ${
                    p.sku
                  }) - ${formatCurrency(p.precio)} ${stockInfo}
                                    </a>
                                `
                })
                .join("")
            } else {
              productSearchResultsDiv.innerHTML =
                '<div class="list-group-item">No se encontraron productos.</div>'
            }
          } catch (error) {
            console.error("Error searching products:", error)
            productSearchResultsDiv.innerHTML =
              '<div class="list-group-item text-danger">Error al buscar productos.</div>'
          }
        }

        productSearchInput.addEventListener(
          "input",
          debounce(searchProducts, 300)
        )

        productSearchResultsDiv.addEventListener("click", (event) => {
          const target = event.target.closest("a.list-group-item")
          if (target) {
            const productStock = parseInt(target.dataset.productStock)
            const productName = target.dataset.productName

            // Check if product is out of stock
            if (productStock === 0) {
              showToast(
                `${productName} no está disponible (sin stock)`,
                "error",
                3000
              )
              return
            }

            const productId = target.dataset.productId
            const productPrice = parseFloat(target.dataset.productPrice)

            productIdInput.value = productId
            productPriceInput.value = productPrice
            productSearchInput.value = productName
            productSearchResultsDiv.innerHTML = ""
            productQuantityInput.value = 1
            productQuantityInput.dispatchEvent(new Event("input"))
          }
        })

        const updateProductSubtotal = () => {
          const quantity = parseFloat(productQuantityInput.value) || 0
          const price = parseFloat(productPriceInput.value) || 0
          productSubtotalDisplay.value = formatCurrency(quantity * price)
          updateEditCalculatedTotal()
        }

        productQuantityInput.addEventListener("input", updateProductSubtotal)
        productPriceInput.addEventListener("input", updateProductSubtotal)

        removeRowButton.addEventListener("click", () => {
          newRow.remove()
          updateEditCalculatedTotal()
        })

        updateProductSubtotal()
      }

      // Add existing products
      if (order.products && order.products.length > 0) {
        order.products.forEach((p) => addEditProductRow(p))
      } else {
        addEditProductRow() // Add an empty row if no products
      }

      addEditOrderProductRowButton.addEventListener("click", () =>
        addEditProductRow()
      )

      // Clear products and total when modal is hidden
      editModalElement.addEventListener("hidden.bs.modal", () => {
        editProductsContainer.innerHTML = ""
        editOrderCalculatedTotalSpan.textContent = formatCurrency(0)
      })

      // Show the modal
      editModal.show()

      // Handle form submission for update
      document.getElementById("editOrderForm").onsubmit = async (event) => {
        event.preventDefault()
        const updatedOrder = {
          id: orderId,
          cliente_id: document.getElementById("editOrderCustomerId").value,
          referencia: document.getElementById("editOrderReference").value,
          estado_pago: document.getElementById("editOrderPaymentStatus").value,
          estado_envio: document.getElementById("editOrderShippingStatus")
            .value,
          direccion_envio: document.getElementById("editOrderShippingAddress")
            .value,
          notas_envio: document.getElementById("editOrderShippingNotes").value,
          products: []
        }

        // Collect products from dynamic fields
        const productRows =
          editProductsContainer.querySelectorAll(".product-row")
        productRows.forEach((row) => {
          const productId = row.querySelector(".product-id-input").value
          const quantity = parseFloat(
            row.querySelector(".product-quantity-input").value
          )
          const price = parseFloat(
            row.querySelector(".product-price-input").value
          )

          if (productId && quantity > 0 && price >= 0) {
            updatedOrder.products.push({
              producto_id: productId,
              cantidad: quantity,
              precio_unitario: price
            })
          }
        })

        // Calculate total based on collected products
        updatedOrder.total = updatedOrder.products.reduce(
          (sum, p) => sum + p.cantidad * p.precio_unitario,
          0
        )

        if (!updatedOrder.cliente_id) {
          showToast("Por favor, selecciona un cliente.", "error")
          return
        }
        if (updatedOrder.products.length === 0) {
          showToast("Por favor, añade al menos un producto.", "error")
          return
        }

        try {
          await fetchJSON(`php/pedidos_actions.php?id=${orderId}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(updatedOrder)
          })
          showToast("Pedido actualizado exitosamente.", "success")
          editModal.hide()
          fetchAndRenderOrders()
        } catch (error) {
          console.error("Error updating order:", error)
          showToast(`Error al actualizar pedido: ${error.message}`, "error")
        }
      }
    } else {
      showToast("Pedido no encontrado para editar.", "error")
    }
  } catch (error) {
    console.error("Error fetching order for edit:", error)
    showToast(`Error al cargar pedido para editar: ${error.message}`, "error")
  }
}

async function openOrderModal() {
  const createModalElement = document.getElementById("createOrderModal")
  const createModal = new bootstrap.Modal(createModalElement)
  createModal.show()

  const clientSearchInput = document.getElementById("createOrderClientSearch")
  const clientSearchResultsDiv = document.getElementById(
    "createOrderClientSearchResults"
  )
  const selectedClientIdInput = document.getElementById(
    "createOrderSelectedClientId"
  )
  const selectedClientDisplay = document.getElementById(
    "createOrderSelectedClientDisplay"
  )

  // Clear previous selections and search results when modal opens
  clientSearchInput.value = ""
  clientSearchResultsDiv.innerHTML = ""
  selectedClientIdInput.value = ""
  selectedClientDisplay.textContent = ""
  document.getElementById("createOrderShippingAddress").value = "" // Clear textarea

  // Debounce function to limit API calls
  const debounce = (func, delay) => {
    let timeout
    return function (...args) {
      const context = this
      clearTimeout(timeout)
      timeout = setTimeout(() => func.apply(context, args), delay)
    }
  }

  const searchClients = async () => {
    const query = clientSearchInput.value.trim()
    if (query.length < 2) {
      clientSearchResultsDiv.innerHTML = ""
      return
    }

    try {
      const data = await fetchJSON(
        `php/search_api.php?action=search_clients&query=${encodeURIComponent(
          query
        )}`
      )
      if (data.success && data.clients.length > 0) {
        clientSearchResultsDiv.innerHTML = data.clients
          .map(
            (client) => `
                    <a href="#" class="list-group-item list-group-item-action"
                       data-client-id="${client.id}"
                       data-client-name="${client.nombre}">
                        ${client.nombre} (${client.id})
                    </a>
                `
          )
          .join("")
      } else {
        clientSearchResultsDiv.innerHTML =
          '<div class="list-group-item">No se encontraron clientes.</div>'
      }
    } catch (error) {
      console.error("Error searching clients:", error)
      clientSearchResultsDiv.innerHTML =
        '<div class="list-group-item text-danger">Error al buscar clientes.</div>'
    }
  }

  clientSearchInput.addEventListener("input", debounce(searchClients, 300))

  clientSearchResultsDiv.addEventListener("click", (event) => {
    const target = event.target.closest("a.list-group-item")
    if (target) {
      const clientId = target.dataset.clientId
      const clientName = target.dataset.clientName
      selectedClientIdInput.value = clientId
      selectedClientDisplay.textContent = `Cliente seleccionado: ${clientName} (${clientId})`
      clientSearchInput.value = clientName // Set input value to selected client name
      clientSearchResultsDiv.innerHTML = "" // Clear search results
    }
  })

  // Product search and dynamic rows
  const productsContainer = document.getElementById(
    "createOrderProductsContainer"
  )
  const addProductRowButton = document.getElementById(
    "addCreateOrderProductRow"
  )
  const calculatedTotalSpan = document.getElementById(
    "createOrderCalculatedTotal"
  )

  let productRowCounter = 0

  const updateCalculatedTotal = () => {
    let total = 0
    productsContainer.querySelectorAll(".product-row").forEach((row) => {
      const quantity =
        parseFloat(row.querySelector(".product-quantity-input").value) || 0
      const price =
        parseFloat(row.querySelector(".product-price-input").value) || 0
      total += quantity * price
    })
    calculatedTotalSpan.textContent = formatCurrency(total)
  }

  const addProductRow = () => {
    productRowCounter++
    const newRow = document.createElement("div")
    newRow.classList.add("row", "mb-2", "product-row")
    newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control form-control-buzz product-search-input" placeholder="Nombre o SKU del producto">
                <input type="hidden" class="product-id-input">
                <input type="hidden" class="product-price-input">
                <div class="product-search-results list-group position-absolute w-100" style="z-index: 999;"></div>
            </div>
            <div class="col-md-2">
                <input type="number" class="form-control form-control-buzz product-quantity-input" value="1" min="1">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-buzz product-subtotal-display" value="${formatCurrency(
                  0
                )}" readonly>
            </div>
            <div class="col-md-2 d-flex align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm remove-product-row"><i class="fas fa-trash"></i></button>
            </div>
        `
    productsContainer.appendChild(newRow)

    const productSearchInput = newRow.querySelector(".product-search-input")
    const productIdInput = newRow.querySelector(".product-id-input")
    const productPriceInput = newRow.querySelector(".product-price-input")
    const productQuantityInput = newRow.querySelector(".product-quantity-input")
    const productSearchResultsDiv = newRow.querySelector(
      ".product-search-results"
    )
    const productSubtotalDisplay = newRow.querySelector(
      ".product-subtotal-display"
    )
    const removeRowButton = newRow.querySelector(".remove-product-row")

    const searchProducts = async () => {
      const query = productSearchInput.value.trim()
      if (query.length < 2) {
        productSearchResultsDiv.innerHTML = ""
        return
      }

      try {
        const data = await fetchJSON(
          `php/search_api.php?action=search_products&query=${encodeURIComponent(
            query
          )}`
        )
        if (data.success && data.products.length > 0) {
          productSearchResultsDiv.innerHTML = data.products
            .map((product) => {
              const isOutOfStock = product.stock === 0
              const stockInfo = isOutOfStock
                ? '<span class="badge bg-danger ms-2">Sin stock</span>'
                : `<span class="badge bg-success ms-2">Stock: ${product.stock}</span>`

              return `
                            <a href="#" class="list-group-item list-group-item-action ${
                              isOutOfStock
                                ? "list-group-item-danger disabled"
                                : ""
                            }"
                               data-product-id="${product.id}"
                               data-product-name="${product.nombre}"
                               data-product-price="${product.precio}"
                               data-product-stock="${product.stock}"
                               ${
                                 isOutOfStock
                                   ? 'style="pointer-events: none; opacity: 0.6;"'
                                   : ""
                               }>
                                ${product.nombre} (SKU: ${
                product.sku
              }) - ${formatCurrency(product.precio)} ${stockInfo}
                            </a>
                        `
            })
            .join("")
        } else {
          productSearchResultsDiv.innerHTML =
            '<div class="list-group-item">No se encontraron productos.</div>'
        }
      } catch (error) {
        console.error("Error searching products:", error)
        productSearchResultsDiv.innerHTML =
          '<div class="list-group-item text-danger">Error al buscar productos.</div>'
      }
    }

    productSearchInput.addEventListener("input", debounce(searchProducts, 300))

    productSearchResultsDiv.addEventListener("click", (event) => {
      const target = event.target.closest("a.list-group-item")
      if (target) {
        const productStock = parseInt(target.dataset.productStock)
        const productName = target.dataset.productName

        // Check if product is out of stock
        if (productStock === 0) {
          showToast(
            `${productName} no está disponible (sin stock)`,
            "error",
            3000
          )
          return
        }

        const productId = target.dataset.productId
        const productPrice = parseFloat(target.dataset.productPrice)

        productIdInput.value = productId
        productPriceInput.value = productPrice
        productSearchInput.value = productName // Set input value to selected product name
        productSearchResultsDiv.innerHTML = "" // Clear search results
        productQuantityInput.value = 1 // Default quantity to 1
        productQuantityInput.dispatchEvent(new Event("input")) // Trigger input event to update subtotal and total
      }
    })

    const updateProductSubtotal = () => {
      const quantity = parseFloat(productQuantityInput.value) || 0
      const price = parseFloat(productPriceInput.value) || 0
      productSubtotalDisplay.value = formatCurrency(quantity * price)
      updateCalculatedTotal()
    }

    productQuantityInput.addEventListener("input", updateProductSubtotal)
    productPriceInput.addEventListener("input", updateProductSubtotal) // In case price is manually changed

    removeRowButton.addEventListener("click", () => {
      newRow.remove()
      updateCalculatedTotal()
    })

    updateProductSubtotal() // Initial subtotal calculation for the new row
  }

  addProductRowButton.addEventListener("click", addProductRow)

  // Add an initial product row when the modal opens
  addProductRow()

  // Clear products and total when modal is hidden
  createModalElement.addEventListener("hidden.bs.modal", () => {
    productsContainer.innerHTML = ""
    calculatedTotalSpan.textContent = formatCurrency(0)
    productRowCounter = 0 // Reset counter
  })

  document.getElementById("createOrderForm").onsubmit = async (event) => {
    event.preventDefault()
    const newOrder = {
      cliente_id: selectedClientIdInput.value, // Use the hidden input for client ID
      estado_pago: document.getElementById("createOrderPaymentStatus").value,
      estado_envio: document.getElementById("createOrderShippingStatus").value,
      direccion_envio: document.getElementById("createOrderShippingAddress")
        .value,
      notas_envio: document.getElementById("createOrderShippingNotes").value,
      products: [] // Collect products from dynamic fields
    }

    // Collect products from dynamic fields
    const productRows = productsContainer.querySelectorAll(".product-row")
    productRows.forEach((row) => {
      const productId = row.querySelector(".product-id-input").value
      const quantity = parseFloat(
        row.querySelector(".product-quantity-input").value
      )
      const price = parseFloat(row.querySelector(".product-price-input").value)

      if (productId && quantity > 0 && price >= 0) {
        newOrder.products.push({
          producto_id: productId,
          cantidad: quantity,
          precio_unitario: price
        })
      }
    })

    // Calculate total based on collected products
    newOrder.total = newOrder.products.reduce(
      (sum, p) => sum + p.cantidad * p.precio_unitario,
      0
    )

    if (!newOrder.cliente_id) {
      showToast("Por favor, selecciona un cliente.", "error")
      return
    }
    if (newOrder.products.length === 0) {
      showToast("Por favor, añade al menos un producto.", "error")
      return
    }

    try {
      await fetchJSON(`php/pedidos_actions.php`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(newOrder)
      })
      showToast("Pedido creado exitosamente.", "success")
      createModal.hide()
      fetchAndRenderOrders() // Refresh list
      document.getElementById("createOrderForm").reset() // Clear form
      // Clear dynamic product rows and reset total
      productsContainer.innerHTML = ""
      calculatedTotalSpan.textContent = formatCurrency(0)
      productRowCounter = 0 // Reset counter
    } catch (error) {
      console.error("Error creating order:", error)
      showToast(`Error al crear pedido: ${error.message}`, "error")
    }
  }
}

async function deleteOrder(orderId) {
    showModal({
        title: "Confirmar Eliminación",
        message: `¿Estás seguro de que deseas eliminar el pedido #${orderId}?`,
        type: "confirm",
        onConfirm: async () => {
            try {
                await fetchJSON(`php/pedidos_actions.php`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: orderId })
                });
                showToast('Pedido eliminado exitosamente.', 'success');
                fetchAndRenderOrders(); // Refresh list
            } catch (error) {
                console.error('Error deleting order:', error);
                showToast(`Error al eliminar pedido: ${error.message}`, 'error');
            }
        }
    });
}

// Initial load
document.addEventListener("DOMContentLoaded", fetchAndRenderOrders);

// Event listeners for filters
document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    fetchAndRenderOrders();
});

document.getElementById('paymentFilter').addEventListener('change', () => {
    currentPage = 1;
    fetchAndRenderOrders();
});

document.getElementById('dateFrom').addEventListener('change', () => {
    currentPage = 1;
    fetchAndRenderOrders();
});

document.getElementById('dateTo').addEventListener('change', () => {
    currentPage = 1;
    fetchAndRenderOrders();
});

