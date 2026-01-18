document.addEventListener("DOMContentLoaded", function () {
  const productModal = document.getElementById("productModal")
  const productForm = document.getElementById("productForm")
  const modalTitle = document.getElementById("modalTitle")
  const productIdHidden = document.getElementById("productIdHidden")
  const productSku = document.getElementById("productSku")
  const productName = document.getElementById("productName")
  const productDescription = document.getElementById("productDescription")
  const productPrice = document.getElementById("productPrice")
  const productStock = document.getElementById("productStock")
  const productMinStock = document.getElementById("productMinStock")
  const productStatus = document.getElementById("productStatus")
  // New attribute fields
  const productMarca = document.getElementById("productMarca")
  const productCategoria = document.getElementById("productCategoria")
  const productProveedor = document.getElementById("productProveedor")
  const productModelo = document.getElementById("productModelo")
  const productColor = document.getElementById("productColor")
  const productTamanio = document.getElementById("productTamanio")
  const productMedidas = document.getElementById("productMedidas")
  const productPeso = document.getElementById("productPeso")
  const productMaterial = document.getElementById("productMaterial")
  const productUnidadVenta = document.getElementById("productUnidadVenta")

  // Product type fields
  const productTipoProducto = document.getElementById("productTipoProducto")
  const productEsKitVirtual = document.getElementById("productEsKitVirtual")
  const productVarianteAtributo = document.getElementById("productVarianteAtributo")
  const productoPadreId = document.getElementById("productoPadreId")
  const kitComponentesSection = document.getElementById("kitComponentesSection")
  const kitComponentesContainer = document.getElementById("kitComponentesContainer")

  // Kit components state
  let kitComponentes = []
  let componenteIdCounter = 0

  const productsTableBody = document.getElementById("productsTableBody")
  const paginationContainer = document.getElementById("pagination")
  const showingFromSpan = document.getElementById("showingFrom")
  const showingToSpan = document.getElementById("showingTo")
  const totalProductsSpan = document.getElementById("totalProducts")
  const searchInput = document.getElementById("searchInput")
  const statusFilter = document.getElementById("statusFilter")
  const marcaFilter = document.getElementById("marcaFilter")
  const categoriaFilter = document.getElementById("categoriaFilter")
  const modeloFilter = document.getElementById("modeloFilter")
  const undFilter = document.getElementById("undFilter")
  const sortBySelect = document.getElementById("sortBy")

  let currentPage = 1
  const productsPerPage = 10
  let totalProducts = 0
  const product_actions_url = "php/api_products.php"

  // Catalogs cache
  let catalogos = { marcas: [], categorias: [], proveedores: [] }

  // Load catalogs on page load
  cargarCatalogos()

  // Manejar filtros desde URL (ej: ?status=stock-bajo)
  const urlParams = new URLSearchParams(window.location.search);
  const statusParam = urlParams.get('status');
  if (statusParam && statusFilter) {
    statusFilter.value = statusParam;
  }

  async function fetchProducts() {
    const search = searchInput.value
    const status = statusFilter.value
    const sortBy = sortBySelect.value
    const params = new URLSearchParams({
      page: currentPage,
      limit: productsPerPage,
      search: search,
      status: status,
      sortBy: sortBy,
      sortOrder: "ASC"
    })

    try {
      const response = await fetch(
        `${product_actions_url}?${params.toString()}`
      )
      const data = await response.json()

      if (data.success) {
        productsTableBody.innerHTML = ""
        totalProducts = data.total

        if (data.products.length > 0) {
          data.products.forEach((product) => {
            const row = productsTableBody.insertRow()
            row.insertCell().textContent = product.sku
            row.insertCell().textContent = product.name
            row.insertCell().textContent = product.description
            row.insertCell().textContent = parseFloat(product.price).toFixed(2)
            row.insertCell().textContent = product.stock

            const statusCell = row.insertCell()
            const statusBadge = document.createElement("span")
            let displayStatus = "ABASTECIDO"
            let badgeClass = "badge-success"

            if (product.stock <= 0) {
              displayStatus = "AGOTADO"
              badgeClass = "badge-secondary"
            } else if (
              product.min_stock &&
              product.stock <= product.min_stock
            ) {
              displayStatus = "REABASTECER"
              badgeClass = "badge-warning"
            }

            statusBadge.textContent = displayStatus
            statusBadge.classList.add("badge", badgeClass)
            statusCell.appendChild(statusBadge)

            if (product.stock <= 0) {
              displayStatus = "AGOTADO"
              badgeClass = "badge-secondary"
            } else if (
              product.min_stock &&
              product.stock <= product.min_stock
            ) {
              displayStatus = "REABASTECER"
              badgeClass = "badge-warning"
            }

            statusBadge.textContent = displayStatus
            statusBadge.classList.add("badge", badgeClass)
            statusCell.appendChild(statusBadge)
            const actionsCell = row.insertCell()
            actionsCell.innerHTML = `
                            <button class="btn btn-outline-buzz btn-sm me-1 edit-product-btn" data-id="${product.id}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-outline-danger btn-sm delete-product-btn" data-id="${product.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                        `
          })
        } else {
          productsTableBody.innerHTML =
            '<tr><td colspan="10" class="text-center">No hay productos registrados.</td></tr>'
        }

        updatePaginationInfo()
        renderPagination()
      } else {
        showToast("Error al cargar productos: " + data.message, "danger")
      }
    } catch (error) {
      console.error("Error fetching products:", error)
      showToast(
        "Error al conectar con el servidor para obtener productos.",
        "danger"
      )
    }
  }

  function updatePaginationInfo() {
    const start = (currentPage - 1) * productsPerPage + 1
    const end = Math.min(currentPage * productsPerPage, totalProducts)
    showingFromSpan.textContent = totalProducts === 0 ? 0 : start
    showingToSpan.textContent = end
    totalProductsSpan.textContent = totalProducts
  }

  function renderPagination() {
    paginationContainer.innerHTML = ""
    const totalPages = Math.ceil(totalProducts / productsPerPage)

    if (totalPages <= 1) return

    const createPageItem = (
      content,
      page,
      isDisabled = false,
      isActive = false
    ) => {
      const li = document.createElement("li")
      li.className = `page-item ${isDisabled ? "disabled" : ""} ${
        isActive ? "active" : ""
      }`
      const a = document.createElement("a")
      a.className = "page-link"
      a.href = "#"
      a.innerHTML = content
      a.addEventListener("click", (e) => {
        e.preventDefault()
        if (!isDisabled) {
          currentPage = page
          fetchProducts()
        }
      })
      li.appendChild(a)
      return li
    }

    paginationContainer.appendChild(
      createPageItem("&laquo;", currentPage - 1, currentPage === 1)
    )
    for (let i = 1; i <= totalPages; i++) {
      paginationContainer.appendChild(
        createPageItem(i, i, false, i === currentPage)
      )
    }
    paginationContainer.appendChild(
      createPageItem("&raquo;", currentPage + 1, currentPage === totalPages)
    )
  }

  window.openProductModal = function (product = null) {
    productForm.reset()
    productIdHidden.value = ""
    productSku.readOnly = !!product

    // Reset new fields
    if (productMarca) productMarca.value = ""
    if (productCategoria) productCategoria.value = ""
    if (productProveedor) productProveedor.value = ""
    if (productModelo) productModelo.value = ""
    if (productColor) productColor.value = ""
    if (productTamanio) productTamanio.value = ""
    if (productMedidas) productMedidas.value = ""
    if (productPeso) productPeso.value = ""
    if (productMaterial) productMaterial.value = ""
    if (productUnidadVenta) productUnidadVenta.value = "UND"
    // Reset product type fields
    if (productTipoProducto) productTipoProducto.value = "fisico"
    if (productEsKitVirtual) productEsKitVirtual.value = "1"
    if (productVarianteAtributo) productVarianteAtributo.value = ""
    if (productoPadreId) productoPadreId.value = ""
    document.getElementById("esVariante")?.checked && (document.getElementById("esVariante").checked = false)
    kitComponentes = []
    renderKitComponentes()
    onTipoProductoChange()

    if (product) {
      modalTitle.textContent = "Editar Producto"
      productIdHidden.value = product.id
      productSku.value = product.sku
      productName.value = product.name
      productDescription.value = product.description
      productPrice.value = product.price
      productStock.value = product.stock
      productMinStock.value = product.min_stock || 10
      productStatus.value = product.status
      // New fields
      if (productMarca) productMarca.value = product.marca_id || ""
      if (productCategoria) productCategoria.value = product.categoria || ""
      if (productProveedor) productProveedor.value = product.proveedor_id || ""
      if (productModelo) productModelo.value = product.modelo || ""
      if (productColor) productColor.value = product.color || ""
      if (productTamanio) productTamanio.value = product.tamanio || ""
      if (productMedidas) productMedidas.value = product.medidas || ""
      if (productPeso) productPeso.value = product.peso_kg || ""
      if (productMaterial) productMaterial.value = product.material || ""
      if (productUnidadVenta) productUnidadVenta.value = product.unidad_venta || "UND"
      // Product type fields
      if (productTipoProducto) productTipoProducto.value = product.tipo_producto || "fisico"
      if (productEsKitVirtual) productEsKitVirtual.value = product.es_kit_virtual ? "1" : "0"
      if (productVarianteAtributo) productVarianteAtributo.value = product.variante_atributo || ""
      if (productoPadreId) productoPadreId.value = product.producto_padre_id || ""
      if (product.producto_padre_id) {
        document.getElementById("esVariante").checked = true
        onEsVarianteChange()
      }
      onTipoProductoChange()
      // Load kit components if it's a kit
      if (product.tipo_producto === "kit" && product.id) {
        cargarComponentesKit(product.id)
      }
    } else {
      modalTitle.textContent = "Nuevo Producto"
    }

    productModal.style.display = "block"
  }

  window.closeProductModal = function () {
    productModal.style.display = "none"
  }

  // Cerrar modal al hacer click fuera de él
  productModal.addEventListener("click", function (event) {
    if (event.target === productModal) {
      closeProductModal()
    }
  })

  productForm.addEventListener("submit", async (event) => {
    event.preventDefault()
    await saveProduct()
  })

  async function saveProduct() {
    const id = productIdHidden.value
    const method = id ? "PUT" : "POST"

    const productData = {
      id: id,
      sku: productSku.value.trim(),
      name: productName.value.trim(),
      description: productDescription.value.trim(),
      price: parseFloat(productPrice.value),
      stock: parseInt(productStock.value),
      min_stock: parseInt(productMinStock.value),
      status: productStatus.value,
      // New fields
      marca_id: productMarca ? productMarca.value || null : null,
      categoria: productCategoria ? productCategoria.value || null : null,
      proveedor_id: productProveedor ? productProveedor.value || null : null,
      modelo: productModelo ? productModelo.value.trim() || null : null,
      color: productColor ? productColor.value.trim() || null : null,
      tamanio: productTamanio ? productTamanio.value.trim() || null : null,
      medidas: productMedidas ? productMedidas.value.trim() || null : null,
      peso_kg: productPeso ? (parseFloat(productPeso.value) || null) : null,
      material: productMaterial ? productMaterial.value.trim() || null : null,
      unidad_venta: productUnidadVenta ? productUnidadVenta.value || "UND" : "UND",
      // Product type fields
      tipo_producto: productTipoProducto ? productTipoProducto.value : "fisico",
      es_kit_virtual: productEsKitVirtual ? productEsKitVirtual.value === "1" : false,
      producto_padre_id: productoPadreId?.value || null,
      variante_atributo: productVarianteAtributo ? productVarianteAtributo.value.trim() || null : null,
      maneja_stock: productTipoProducto?.value !== "servicio"
    }

    // Validation for required fields
    if (!productData.sku) {
      showToast("El campo SKU es obligatorio.", "warning")
      return
    }
    if (!productData.name) {
      showToast("El campo Nombre es obligatorio.", "warning")
      return
    }
    if (isNaN(productData.price)) {
      showToast(
        "El campo Precio Unitario es obligatorio y debe ser un número.",
        "warning"
      )
      return
    }
    if (isNaN(productData.stock)) {
      showToast(
        "El campo Stock Actual es obligatorio y debe ser un número entero.",
        "warning"
      )
      return
    }

    // Validation for negative values
    if (productData.price < 0) {
      showToast("El Precio Unitario no puede ser negativo.", "warning")
      return
    }
    if (productData.stock < 0) {
      showToast("El Stock Actual no puede ser negativo.", "warning")
      return
    }
    if (productData.min_stock < 0) {
      showToast("El Stock Mínimo no puede ser negativo.", "warning")
      return
    }

    try {
      const response = await fetch(
        product_actions_url + (method === "PUT" ? `?id=${id}` : ""),
        {
          method: method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(productData)
        }
      )
      const data = await response.json()
      if (data.success) {
        // If it's a kit, save components
        const productId = id || data.id
        if (productData.tipo_producto === 'kit' && productId && kitComponentes.length > 0) {
          await guardarComponentesKit(productId)
        }
        showToast(data.message, "success")
        closeProductModal()
        fetchProducts()
      } else {
        showToast("Error: " + data.message, "danger")
      }
    } catch (error) {
      console.error("Error:", error)
      showToast("Ocurrió un error al guardar el producto.", "danger")
    }
  }

  async function guardarComponentesKit(kitId) {
    // First, we need to clear existing components and add new ones
    // For simplicity, we'll add each component
    for (const comp of kitComponentes) {
      try {
        if (comp.id) {
          // Update existing
          await fetch(`php/kit_actions.php?id=${comp.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cantidad: comp.cantidad })
          })
        } else {
          // Add new
          await fetch('php/kit_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              kit_id: kitId,
              componente_id: comp.componenteId,
              cantidad: comp.cantidad
            })
          })
        }
      } catch (error) {
        console.error('Error guardando componente:', error)
      }
    }
  }

  window.deleteProduct = async function (id) {
    showConfirmation(
      "Eliminar Producto",
      "¿Estás seguro de que quieres eliminar este producto?",
      async () => {
        try {
          const response = await fetch(product_actions_url, {
            method: "DELETE",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id })
          })
          const data = await response.json()
          if (data.success) {
            showToast(data.message, "success")
            fetchProducts()
          } else {
            showToast("Error: " + data.message, "danger")
          }
        } catch (error) {
          console.error("Error:", error)
          showToast("Ocurrió un error al eliminar el producto.", "danger")
        }
      }
    )
  }

  window.restoreProduct = async function (id) {
    showConfirmation(
      "Restaurar Producto",
      "¿Estás seguro de que quieres restaurar este producto?",
      async () => {
        try {
          const response = await fetch(product_actions_url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "restore", id: id })
          })
          const data = await response.json()
          if (data.success) {
            showToast(data.message, "success")
            fetchProducts()
          } else {
            showToast("Error: " + data.message, "danger")
          }
        } catch (error) {
          console.error("Error:", error)
          showToast("Ocurrió un error al restaurar el producto.", "danger")
        }
      }
    )
  }

  searchInput.addEventListener("input", () => {
    currentPage = 1
    fetchProducts()
  })
  statusFilter.addEventListener("change", () => {
    currentPage = 1
    fetchProducts()
  })
  sortBySelect.addEventListener("change", () => {
    currentPage = 1
    fetchProducts()
  })

  // Add listener for show deleted filter
  const showDeletedFilter = document.getElementById("showDeletedFilter")
  if (showDeletedFilter) {
    showDeletedFilter.addEventListener("change", () => {
      currentPage = 1
      updateActiveFiltersCount()
      fetchProducts()
    })
  }

  // New filter listeners
  if (marcaFilter) marcaFilter.addEventListener("change", () => { currentPage = 1; updateActiveFiltersCount(); fetchProducts(); });
  if (categoriaFilter) categoriaFilter.addEventListener("change", () => { currentPage = 1; updateActiveFiltersCount(); fetchProducts(); });
  if (modeloFilter) modeloFilter.addEventListener("input", () => { currentPage = 1; updateActiveFiltersCount(); fetchProducts(); });
  if (undFilter) undFilter.addEventListener("change", () => { currentPage = 1; updateActiveFiltersCount(); fetchProducts(); });

  // Advanced filters toggle logic
  const toggleBtn = document.getElementById("toggleAdvancedFilters")
  const advancedCollapse = document.getElementById("advancedFiltersCollapse")
  const activeBadge = document.getElementById("activeFiltersBadge")

  if (toggleBtn && advancedCollapse) {
    // Restore state from localStorage
    const isAdvVisible = localStorage.getItem("advFiltersVisible") === "true"
    if (isAdvVisible) {
      advancedCollapse.classList.remove("d-none")
      toggleBtn.classList.add("active")
    }

    toggleBtn.addEventListener("click", () => {
      const isHidden = advancedCollapse.classList.contains("d-none")
      if (isHidden) {
        advancedCollapse.classList.remove("d-none")
        toggleBtn.classList.add("active")
        localStorage.setItem("advFiltersVisible", "true")
      } else {
        advancedCollapse.classList.add("d-none")
        toggleBtn.classList.remove("active")
        localStorage.setItem("advFiltersVisible", "false")
      }
    })
  }

  // Function to update the active filters count (only for advanced filters)
  function updateActiveFiltersCount() {
    if (!activeBadge) return

    let activeFilters = 0;
    // Only count filters in the advanced section
    if (marcaFilter && marcaFilter.value !== "") activeFilters++;
    if (categoriaFilter && categoriaFilter.value !== "") activeFilters++;
    if (modeloFilter && modeloFilter.value.trim() !== "") activeFilters++;
    if (undFilter && undFilter.value !== "") activeFilters++;
    if (sortBySelect && sortBySelect.value !== "name") activeFilters++;
    const showDeletedFilter = document.getElementById("showDeletedFilter");
    if (showDeletedFilter && showDeletedFilter.checked) activeFilters++;

    activeBadge.textContent = activeFilters;
    activeBadge.classList.toggle("d-none", activeFilters === 0);
  }

  // Update fetchProducts to include show_deleted
  const originalFetchProducts = fetchProducts
  fetchProducts = async function () {
    const search = searchInput.value
    const status = statusFilter.value
    const marcaId = marcaFilter ? marcaFilter.value : ""
    const categoriaId = categoriaFilter ? categoriaFilter.value : ""
    const modelo = modeloFilter ? modeloFilter.value : ""
    const unidadVenta = undFilter ? undFilter.value : ""
    const sortBy = sortBySelect.value
    const showDeleted = document.getElementById("showDeletedFilter")
      ? document.getElementById("showDeletedFilter").checked
      : false

    const params = new URLSearchParams({
      page: currentPage,
      limit: productsPerPage,
      search: search,
      status: status,
      marca_id: marcaId,
      categoria_id: categoriaId,
      modelo: modelo,
      unidad_venta: unidadVenta,
      sortBy: sortBy,
      sortOrder: "ASC"
    })

    if (showDeleted) {
      params.append("show_deleted", "true")
    }

    try {
      const response = await fetch(
        `${product_actions_url}?${params.toString()}`
      )
      const data = await response.json()

      if (data.success) {
        productsTableBody.innerHTML = ""
        totalProducts = data.total

        if (data.products.length > 0) {
          data.products.forEach((product) => {
            const isDeleted = product.deleted_at !== null
            const row = productsTableBody.insertRow()
            if (isDeleted) row.classList.add("table-danger")

            row.insertCell().textContent = product.sku
            row.insertCell().textContent = product.name
            row.insertCell().textContent = product.marca_nombre || '-'
            row.insertCell().textContent = product.categoria_nombre || '-'
            row.insertCell().textContent = product.modelo || '-'
            row.insertCell().textContent = product.unidad_venta || 'UND'
            row.insertCell().textContent = parseFloat(product.price).toFixed(2)
            row.insertCell().textContent = product.stock

            const statusCell = row.insertCell()
            const statusBadge = document.createElement("span")
            let displayStatus = "ABASTECIDO"
            let badgeClass = "badge-success"

            if (product.stock <= 0) {
              displayStatus = "AGOTADO"
              badgeClass = "badge-secondary"
            } else if (
              product.min_stock &&
              product.stock <= product.min_stock
            ) {
              displayStatus = "REABASTECER"
              badgeClass = "badge-warning"
            }

            statusBadge.textContent = displayStatus
            statusBadge.classList.add("badge", badgeClass)
            statusCell.appendChild(statusBadge)

            const actionsCell = row.insertCell()

            if (isDeleted) {
              actionsCell.innerHTML = `
                                <button class="btn-action btn-restore me-1" onclick="restoreProduct(${product.id})" title="Restaurar" style="color: var(--buzz-success);"><i class="fas fa-trash-restore"></i></button>
                            `
            } else {
              actionsCell.innerHTML = `
                                <button class="btn-action btn-edit me-1 edit-product-btn" data-id="${product.id}" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-delete delete-product-btn" data-id="${product.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                            `
            }
          })
        } else {
          productsTableBody.innerHTML =
            '<tr><td colspan="10" class="text-center">No hay productos registrados.</td></tr>'
        }

        updatePaginationInfo()
        renderPagination()
      } else {
        showToast("Error al cargar productos: " + data.message, "danger")
      }
    } catch (error) {
      console.error("Error fetching products:", error)
      showToast(
        "Error al conectar con el servidor para obtener productos.",
        "danger"
      )
    }
  }

  productsTableBody.addEventListener("click", async (event) => {
    const editButton = event.target.closest(".edit-product-btn")
    const deleteButton = event.target.closest(".delete-product-btn")

    if (editButton) {
      const id = editButton.dataset.id
      try {
        const response = await fetch(`${product_actions_url}?id=${id}`)
        const data = await response.json()
        if (data.success && data.products.length > 0) {
          openProductModal(data.products[0])
        } else {
          showToast(
            "Error al cargar datos del producto: " +
              (data.message || "No encontrado."),
            "danger"
          )
        }
      } catch (error) {
        console.error("Error:", error)
        showToast("Error al cargar los datos del producto.", "danger")
      }
    } else if (deleteButton) {
      deleteProduct(deleteButton.dataset.id)
    }
  })

  // Función para limpiar filtros de productos
  window.clearProductFilters = function () {
    searchInput.value = ""
    statusFilter.value = ""
    if (marcaFilter) marcaFilter.value = ""
    if (categoriaFilter) categoriaFilter.value = ""
    if (modeloFilter) modeloFilter.value = ""
    if (undFilter) undFilter.value = ""
    sortBySelect.value = "name"
    const showDeletedFilter = document.getElementById("showDeletedFilter")
    if (showDeletedFilter) {
      showDeletedFilter.checked = false
    }
    currentPage = 1
    updateActiveFiltersCount()
    fetchProducts()
  }

  // =====================================================
  // CARGAR CATÁLOGOS (Marcas, Categorías, Proveedores)
  // =====================================================

  async function cargarCatalogos() {
    try {
      const response = await fetch('php/api_catalogs.php?type=resumen')
      const data = await response.json()

      if (data.success) {
        catalogos = data.catalogos

        // Llenar select de marcas
        if (productMarca) {
          productMarca.innerHTML = '<option value="">Seleccionar marca...</option>'
          catalogos.marcas.forEach(m => {
            productMarca.innerHTML += `<option value="${m.id}">${m.nombre}</option>`
          })
          // También llenar el filtro si existe
          if (marcaFilter) {
            marcaFilter.innerHTML = '<option value="">Todas las marcas</option>'
            catalogos.marcas.forEach(m => {
              marcaFilter.innerHTML += `<option value="${m.id}">${m.nombre}</option>`
            })
          }
        }

        // Llenar select de categorías
        if (productCategoria) {
          productCategoria.innerHTML = '<option value="">Sin categoría</option>'
          catalogos.categorias.forEach(c => {
            productCategoria.innerHTML += `<option value="${c.id}">${c.nombre}</option>`
          })
          // También llenar el filtro si existe
          if (categoriaFilter) {
            categoriaFilter.innerHTML = '<option value="">Todas las categorías</option>'
            catalogos.categorias.forEach(c => {
              categoriaFilter.innerHTML += `<option value="${c.id}">${c.nombre}</option>`
            })
          }
        }

        // Llenar select de proveedores
        if (productProveedor) {
          productProveedor.innerHTML = '<option value="">Sin proveedor</option>'
          catalogos.proveedores.forEach(p => {
            productProveedor.innerHTML += `<option value="${p.id}">${p.nombre}</option>`
          })
        }
      }
    } catch (error) {
      console.error('Error cargando catálogos:', error)
    }
  }

  // =====================================================
  // PRODUCT TYPE HANDLERS
  // =====================================================

  window.onTipoProductoChange = function() {
    const tipo = productTipoProducto?.value || 'fisico'
    const hint = document.getElementById('tipoProductoHint')
    const kitTypeContainer = document.getElementById('kitTypeContainer')
    const varianteSection = document.getElementById('varianteSection')
    const stockSection = document.querySelector('#productStock')?.closest('.col-md-6')

    // Update hint text
    const hints = {
      'fisico': 'Producto con inventario físico',
      'servicio': 'Servicio sin manejo de stock',
      'kit': 'Conjunto de otros productos'
    }
    if (hint) hint.textContent = hints[tipo] || ''

    // Show/hide kit type selector
    if (kitTypeContainer) {
      kitTypeContainer.style.display = tipo === 'kit' ? 'block' : 'none'
    }

    // Show/hide kit components section
    if (kitComponentesSection) {
      kitComponentesSection.style.display = tipo === 'kit' ? 'block' : 'none'
    }

    // Show/hide variant section (only for physical products)
    if (varianteSection) {
      varianteSection.style.display = tipo === 'fisico' ? 'flex' : 'none'
    }

    // Adjust stock field behavior
    if (stockSection) {
      if (tipo === 'servicio') {
        stockSection.style.opacity = '0.5'
        if (productStock) productStock.value = '0'
        if (productStock) productStock.disabled = true
      } else if (tipo === 'kit' && productEsKitVirtual?.value === '1') {
        stockSection.style.opacity = '0.5'
        if (productStock) productStock.disabled = true
      } else {
        stockSection.style.opacity = '1'
        if (productStock) productStock.disabled = false
      }
    }
  }

  window.onKitTypeChange = function() {
    const esVirtual = productEsKitVirtual?.value === '1'
    const stockSection = document.querySelector('#productStock')?.closest('.col-md-6')

    if (stockSection) {
      if (esVirtual) {
        stockSection.style.opacity = '0.5'
        if (productStock) productStock.disabled = true
      } else {
        stockSection.style.opacity = '1'
        if (productStock) productStock.disabled = false
      }
    }
  }

  window.onEsVarianteChange = function() {
    const esVariante = document.getElementById('esVariante')?.checked
    const padreContainer = document.getElementById('productoPadreContainer')
    const atributoRow = document.getElementById('varianteAtributoRow')

    if (padreContainer) padreContainer.style.display = esVariante ? 'block' : 'none'
    if (atributoRow) atributoRow.style.display = esVariante ? 'flex' : 'none'
  }

  window.limpiarProductoPadre = function() {
    if (productoPadreId) productoPadreId.value = ''
    const selected = document.getElementById('productoPadreSeleccionado')
    const search = document.getElementById('productoPadreBusqueda')
    if (selected) selected.style.display = 'none'
    if (search) search.value = ''
  }

  // =====================================================
  // KIT COMPONENTS MANAGEMENT
  // =====================================================

  async function cargarComponentesKit(kitId) {
    try {
      const response = await fetch(`php/kit_actions.php?kit_id=${kitId}`)
      const data = await response.json()

      if (data.success) {
        kitComponentes = data.componentes.map(c => ({
          id: c.id,
          componenteId: c.componente_id,
          sku: c.componente_sku,
          nombre: c.componente_nombre,
          cantidad: c.cantidad,
          precio: c.componente_precio,
          stock: c.stock_disponible
        }))
        renderKitComponentes()

        if (data.stock_kit !== null) {
          document.getElementById('stockKitCalculado').textContent = data.stock_kit
        }
        document.getElementById('costoComponentes').textContent =
          '$' + (data.costo_componentes || 0).toFixed(2)
      }
    } catch (error) {
      console.error('Error cargando componentes:', error)
    }
  }

  function renderKitComponentes() {
    const container = kitComponentesContainer
    const sinComponentes = document.getElementById('sinComponentes')

    if (!container) return

    if (kitComponentes.length === 0) {
      container.innerHTML = ''
      if (sinComponentes) sinComponentes.style.display = 'block'
      return
    }

    if (sinComponentes) sinComponentes.style.display = 'none'

    container.innerHTML = kitComponentes.map((c, idx) => `
      <div class="kit-componente-row" data-idx="${idx}">
        <div class="row align-items-center py-2 border-bottom border-secondary">
          <div class="col-md-5">
            <span class="font-mono text-accent">${c.sku || ''}</span>
            <span class="ms-2">${c.nombre || ''}</span>
          </div>
          <div class="col-md-2">
            <input type="number" class="form-control form-control-buzz form-control-sm"
                   value="${c.cantidad}" min="0.001" step="0.001"
                   onchange="actualizarCantidadComponente(${idx}, this.value)">
          </div>
          <div class="col-md-2 text-muted">
            Stock: ${c.stock || 0}
          </div>
          <div class="col-md-2 text-end">
            $${((c.cantidad || 0) * (c.precio || 0)).toFixed(2)}
          </div>
          <div class="col-md-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarComponenteKit(${idx})">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>
    `).join('')

    // Update totals
    actualizarTotalesKit()
  }

  function actualizarTotalesKit() {
    let costoTotal = 0
    let minStock = Infinity

    kitComponentes.forEach(c => {
      costoTotal += (c.cantidad || 0) * (c.precio || 0)
      if (c.cantidad > 0) {
        const kitsDisponibles = Math.floor((c.stock || 0) / c.cantidad)
        if (kitsDisponibles < minStock) minStock = kitsDisponibles
      }
    })

    if (minStock === Infinity) minStock = 0

    document.getElementById('stockKitCalculado').textContent = minStock
    document.getElementById('costoComponentes').textContent = '$' + costoTotal.toFixed(2)
  }

  window.agregarComponenteKit = function() {
    // Show a simple prompt to search for product
    const sku = prompt('Ingresa el SKU o nombre del producto a agregar:')
    if (!sku) return

    // Search for product
    buscarProductoComponente(sku)
  }

  async function buscarProductoComponente(search) {
    try {
      const response = await fetch(`php/productos_search.php?q=${encodeURIComponent(search)}&limit=5`)
      const data = await response.json()

      if (data.success && data.productos.length > 0) {
        const producto = data.productos[0]

        // Check if already in list
        if (kitComponentes.some(c => c.componenteId == producto.id)) {
          showToast('Este producto ya está en el kit', 'warning')
          return
        }

        kitComponentes.push({
          id: null,
          componenteId: producto.id,
          sku: producto.sku,
          nombre: producto.nombre,
          cantidad: 1,
          precio: parseFloat(producto.precio || 0),
          stock: parseInt(producto.stock || 0)
        })

        renderKitComponentes()
        showToast('Componente agregado', 'success')
      } else {
        showToast('Producto no encontrado', 'warning')
      }
    } catch (error) {
      console.error('Error buscando producto:', error)
      showToast('Error al buscar producto', 'danger')
    }
  }

  window.actualizarCantidadComponente = function(idx, cantidad) {
    if (kitComponentes[idx]) {
      kitComponentes[idx].cantidad = parseFloat(cantidad) || 0
      actualizarTotalesKit()
    }
  }

  window.eliminarComponenteKit = function(idx) {
    kitComponentes.splice(idx, 1)
    renderKitComponentes()
  }

  fetchProducts()
})

