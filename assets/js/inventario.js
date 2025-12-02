document.addEventListener('DOMContentLoaded', function() {
    const productModal = document.getElementById('productModal');
    const productForm = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    const productIdHidden = document.getElementById('productIdHidden');
    const productSku = document.getElementById('productSku');
    const productName = document.getElementById('productName');
    const productDescription = document.getElementById('productDescription');
    const productPrice = document.getElementById('productPrice');
    const productStock = document.getElementById('productStock');
    const productMinStock = document.getElementById('productMinStock');
    const productStatus = document.getElementById('productStatus');
    const productsTableBody = document.getElementById('productsTableBody');
    const paginationContainer = document.getElementById('pagination');
    const showingFromSpan = document.getElementById('showingFrom');
    const showingToSpan = document.getElementById('showingTo');
    const totalProductsSpan = document.getElementById('totalProducts');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortBySelect = document.getElementById('sortBy');

    let currentPage = 1;
    const productsPerPage = 10;
    let totalProducts = 0;
    const product_actions_url = 'php/product_actions.php';



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
              row.insertCell().textContent = parseFloat(product.price).toFixed(
                2
              )
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
              actionsCell.innerHTML = `
                            <button class="btn-action btn-edit me-1 edit-product-btn" data-id="${product.id}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-action btn-delete delete-product-btn" data-id="${product.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                        `
            })
          } else {
            productsTableBody.innerHTML =
              '<tr><td colspan="7" class="text-center">No hay productos registrados.</td></tr>'
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
      } else {
        modalTitle.textContent = "Nuevo Producto"
      }

      productModal.style.display = "block"
    }

    window.closeProductModal = function () {
      productModal.style.display = "none"
    }

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
        status: productStatus.value
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

    window.deleteProduct = async function(id) {
        showConfirmation(
            "Eliminar Producto",
            "¿Estás seguro de que quieres eliminar este producto?",
            async () => {
                try {
                    const response = await fetch(product_actions_url, {
                        method: 'DELETE',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        fetchProducts();
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Ocurrió un error al eliminar el producto.', 'danger');
                }
            }
        );
    };

    window.restoreProduct = async function(id) {
        showConfirmation(
            "Restaurar Producto",
            "¿Estás seguro de que quieres restaurar este producto?",
            async () => {
                try {
                    const response = await fetch(product_actions_url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'restore', id: id })
                    });
                    const data = await response.json();
                    if (data.success) {
                        showToast(data.message, 'success');
                        fetchProducts();
                    } else {
                        showToast('Error: ' + data.message, 'danger');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Ocurrió un error al restaurar el producto.', 'danger');
                }
            }
        );
    };

    searchInput.addEventListener('input', () => { currentPage = 1; fetchProducts(); });
    statusFilter.addEventListener('change', () => { currentPage = 1; fetchProducts(); });
    sortBySelect.addEventListener('change', () => { currentPage = 1; fetchProducts(); });

    // Add listener for show deleted filter
    const showDeletedFilter = document.getElementById('showDeletedFilter');
    if(showDeletedFilter) {
        showDeletedFilter.addEventListener('change', () => { currentPage = 1; fetchProducts(); });
    }

    // Update fetchProducts to include show_deleted
    const originalFetchProducts = fetchProducts;
    fetchProducts = async function() {
        const search = searchInput.value
        const status = statusFilter.value
        const sortBy = sortBySelect.value
        const showDeleted = document.getElementById('showDeletedFilter') ? document.getElementById('showDeletedFilter').checked : false;

        const params = new URLSearchParams({
            page: currentPage,
            limit: productsPerPage,
            search: search,
            status: status,
            sortBy: sortBy,
            sortOrder: "ASC"
        })

        if (showDeleted) {
            params.append('show_deleted', 'true');
        }

        try {
            const response = await fetch(`${product_actions_url}?${params.toString()}`)
            const data = await response.json()

            if (data.success) {
                productsTableBody.innerHTML = ""
                totalProducts = data.total

                if (data.products.length > 0) {
                    data.products.forEach((product) => {
                        const isDeleted = product.deleted_at !== null;
                        const row = productsTableBody.insertRow()
                        if (isDeleted) row.classList.add('table-danger');

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
                        } else if (product.min_stock && product.stock <= product.min_stock) {
                            displayStatus = "REABASTECER"
                            badgeClass = "badge-warning"
                        }

                        statusBadge.textContent = displayStatus
                        statusBadge.classList.add("badge", badgeClass)
                        statusCell.appendChild(statusBadge)

                        const actionsCell = row.insertCell()

                        if (isDeleted) {
                            actionsCell.innerHTML = `
                                <button class="btn btn-outline-success btn-sm me-1" onclick="restoreProduct(${product.id})" title="Restaurar"><i class="fas fa-trash-restore"></i></button>
                            `
                        } else {
                            actionsCell.innerHTML = `
                                <button class="btn btn-outline-buzz btn-sm me-1 edit-product-btn" data-id="${product.id}" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-danger btn-sm delete-product-btn" data-id="${product.id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                            `
                        }
                    })
                } else {
                    productsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No hay productos registrados.</td></tr>'
                }

                updatePaginationInfo()
                renderPagination()
            } else {
                showToast("Error al cargar productos: " + data.message, "danger")
            }
        } catch (error) {
            console.error("Error fetching products:", error)
            showToast("Error al conectar con el servidor para obtener productos.", "danger")
        }
    }

    productsTableBody.addEventListener('click', async (event) => {
        const editButton = event.target.closest('.edit-product-btn');
        const deleteButton = event.target.closest('.delete-product-btn');

        if (editButton) {
            const id = editButton.dataset.id;
            try {
                const response = await fetch(`${product_actions_url}?id=${id}`);
                const data = await response.json();
                if (data.success && data.products.length > 0) {
                    openProductModal(data.products[0]);
                } else {
                    showToast('Error al cargar datos del producto: ' + (data.message || 'No encontrado.'), 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al cargar los datos del producto.', 'danger');
            }
        } else if (deleteButton) {
            deleteProduct(deleteButton.dataset.id);
        }
    });

    // Clear filters function
    window.clearFilters = function() {
        searchInput.value = '';
        statusFilter.value = '';
        sortBySelect.value = 'name';
        const showDeletedFilter = document.getElementById('showDeletedFilter');
        if (showDeletedFilter) {
            showDeletedFilter.checked = false;
        }
        currentPage = 1;
        fetchProducts();
    };

    fetchProducts();
});
