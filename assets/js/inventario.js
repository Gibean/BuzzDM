// Inventory management functionality
let products = []
let filteredProducts = []
let currentPage = 1
const itemsPerPage = 10
let editingProductId = null

function formatCurrency(amount) {
  return amount.toLocaleString("es-ES", { style: "currency", currency: "EUR" })
}

function showNotification(message, type) {
  const notification = document.createElement("div")
  notification.className = `notification ${type}`
  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    document.body.removeChild(notification)
  }, 3000)
}

document.addEventListener("DOMContentLoaded", () => {
  initInventory()
  setupEventListeners()
})

function initInventory() {
  loadProducts()
  renderProducts()
  setupFilters()
}

function setupEventListeners() {
  // Search input
  document.getElementById("searchInput").addEventListener("input", filterProducts)

  // Status filter
  document.getElementById("statusFilter").addEventListener("change", filterProducts)

  // Sort by
  document.getElementById("sortBy").addEventListener("change", sortProducts)

  // Product form
  document.getElementById("productForm").addEventListener("submit", handleProductSubmit)
}

function loadProducts() {
  // Simulate loading products from API
  products = [
    {
      id: 1,
      sku: "LAP-001",
      name: "Laptop Gaming Pro",
      description: "Laptop para gaming de alta gama con RTX 4070",
      price: 1499.99,
      stock: 15,
      minStock: 10,
      status: "activo",
    },
    {
      id: 2,
      sku: "MOU-005",
      name: "Mouse Inalámbrico",
      description: "Mouse inalámbrico ergonómico con sensor óptico",
      price: 59.99,
      stock: 7,
      minStock: 20,
      status: "activo",
    },
    {
      id: 3,
      sku: "KEY-003",
      name: "Teclado Mecánico",
      description: "Teclado mecánico RGB con switches Cherry MX",
      price: 149.99,
      stock: 2,
      minStock: 15,
      status: "activo",
    },
    {
      id: 4,
      sku: "MON-002",
      name: "Monitor 4K",
      description: "Monitor 4K de 27 pulgadas con HDR",
      price: 599.99,
      stock: 0,
      minStock: 5,
      status: "activo",
    },
    {
      id: 5,
      sku: "AUR-004",
      name: "Auriculares Premium",
      description: "Auriculares con cancelación de ruido activa",
      price: 299.99,
      stock: 25,
      minStock: 10,
      status: "activo",
    },
  ]

  filteredProducts = [...products]
}

function renderProducts() {
  const tbody = document.getElementById("productsTableBody")
  const startIndex = (currentPage - 1) * itemsPerPage
  const endIndex = startIndex + itemsPerPage
  const pageProducts = filteredProducts.slice(startIndex, endIndex)

  tbody.innerHTML = pageProducts
    .map(
      (product) => `
        <tr>
            <td class="font-mono">${product.sku}</td>
            <td>${product.name}</td>
            <td class="text-muted" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${product.description}</td>
            <td class="font-mono">${formatCurrency(product.price)}</td>
            <td class="font-mono">${product.stock}</td>
            <td>${getStatusBadge(product)}</td>
            <td>
                <button class="btn btn-outline-buzz btn-sm me-1" onclick="editProduct(${product.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-outline-danger btn-sm" onclick="deleteProduct(${product.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `,
    )
    .join("")

  updatePagination()
  updateProductCount()
}

function getStatusBadge(product) {
  if (product.stock === 0) {
    return '<span class="badge badge-danger">Agotado</span>'
  } else if (product.stock <= product.minStock) {
    return '<span class="badge badge-warning">Stock Bajo</span>'
  } else {
    return '<span class="badge badge-success">Activo</span>'
  }
}

function updatePagination() {
  const totalPages = Math.ceil(filteredProducts.length / itemsPerPage)
  const pagination = document.getElementById("pagination")

  let paginationHTML = ""

  // Previous button
  if (currentPage > 1) {
    paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Anterior</a></li>`
  }

  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    if (i === currentPage) {
      paginationHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`
    } else {
      paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${i})">${i}</a></li>`
    }
  }

  // Next button
  if (currentPage < totalPages) {
    paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Siguiente</a></li>`
  }

  pagination.innerHTML = paginationHTML
}

function updateProductCount() {
  const startIndex = (currentPage - 1) * itemsPerPage
  const endIndex = Math.min(startIndex + itemsPerPage, filteredProducts.length)

  document.getElementById("showingFrom").textContent = filteredProducts.length > 0 ? startIndex + 1 : 0
  document.getElementById("showingTo").textContent = endIndex
  document.getElementById("totalProducts").textContent = filteredProducts.length
}

function changePage(page) {
  currentPage = page
  renderProducts()
}

function setupFilters() {
  filterProducts()
}

function filterProducts() {
  const searchTerm = document.getElementById("searchInput").value.toLowerCase()
  const statusFilter = document.getElementById("statusFilter").value

  filteredProducts = products.filter((product) => {
    const matchesSearch =
      !searchTerm ||
      product.sku.toLowerCase().includes(searchTerm) ||
      product.name.toLowerCase().includes(searchTerm) ||
      product.description.toLowerCase().includes(searchTerm)

    const matchesStatus = !statusFilter || getProductStatus(product) === statusFilter

    return matchesSearch && matchesStatus
  })

  currentPage = 1
  sortProducts()
}

function getProductStatus(product) {
  if (product.stock === 0) {
    return "agotado"
  } else if (product.stock <= product.minStock) {
    return "stock-bajo"
  } else {
    return "activo"
  }
}

function sortProducts() {
  const sortBy = document.getElementById("sortBy").value

  filteredProducts.sort((a, b) => {
    switch (sortBy) {
      case "name":
        return a.name.localeCompare(b.name)
      case "sku":
        return a.sku.localeCompare(b.sku)
      case "stock":
        return b.stock - a.stock
      case "price":
        return b.price - a.price
      default:
        return 0
    }
  })

  renderProducts()
}

function clearFilters() {
  document.getElementById("searchInput").value = ""
  document.getElementById("statusFilter").value = ""
  document.getElementById("sortBy").value = "name"
  filterProducts()
}

function openProductModal(productId = null) {
  editingProductId = productId
  const modal = document.getElementById("productModal")
  const modalTitle = document.getElementById("modalTitle")

  if (productId) {
    const product = products.find((p) => p.id === productId)
    modalTitle.textContent = "Editar Producto"
    fillProductForm(product)
  } else {
    modalTitle.textContent = "Nuevo Producto"
    clearProductForm()
  }

  modal.style.display = "block"
}

function closeProductModal() {
  document.getElementById("productModal").style.display = "none"
  editingProductId = null
}

function fillProductForm(product) {
  document.getElementById("productSku").value = product.sku
  document.getElementById("productName").value = product.name
  document.getElementById("productDescription").value = product.description
  document.getElementById("productPrice").value = product.price
  document.getElementById("productStock").value = product.stock
  document.getElementById("productMinStock").value = product.minStock
  document.getElementById("productStatus").value = product.status
}

function clearProductForm() {
  document.getElementById("productForm").reset()
  document.getElementById("productMinStock").value = "10"
  document.getElementById("productStatus").value = "activo"
}

function handleProductSubmit(e) {
  e.preventDefault()

  const formData = {
    sku: document.getElementById("productSku").value,
    name: document.getElementById("productName").value,
    description: document.getElementById("productDescription").value,
    price: Number.parseFloat(document.getElementById("productPrice").value),
    stock: Number.parseInt(document.getElementById("productStock").value),
    minStock: Number.parseInt(document.getElementById("productMinStock").value),
    status: document.getElementById("productStatus").value,
  }

  // Validate SKU uniqueness
  const existingProduct = products.find((p) => p.sku === formData.sku && p.id !== editingProductId)
  if (existingProduct) {
    showNotification("El SKU ya existe. Por favor, usa un SKU único.", "error")
    return
  }

  if (editingProductId) {
    updateProduct(editingProductId, formData)
  } else {
    createProduct(formData)
  }
}

function createProduct(productData) {
  const newProduct = {
    id: Math.max(...products.map((p) => p.id)) + 1,
    ...productData,
  }

  products.push(newProduct)
  filterProducts()
  closeProductModal()
  showNotification("Producto creado exitosamente", "success")
}

function updateProduct(productId, productData) {
  const productIndex = products.findIndex((p) => p.id === productId)
  if (productIndex !== -1) {
    products[productIndex] = { ...products[productIndex], ...productData }
    filterProducts()
    closeProductModal()
    showNotification("Producto actualizado exitosamente", "success")
  }
}

function editProduct(productId) {
  openProductModal(productId)
}

function deleteProduct(productId) {
  const product = products.find((p) => p.id === productId)
  if (confirm(`¿Estás seguro de que deseas eliminar el producto "${product.name}"?`)) {
    products = products.filter((p) => p.id !== productId)
    filterProducts()
    showNotification("Producto eliminado exitosamente", "success")
  }
}

// Close modal when clicking outside
document.getElementById("productModal").addEventListener("click", function (e) {
  if (e.target === this) {
    closeProductModal()
  }
})
