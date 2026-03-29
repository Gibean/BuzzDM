// Customers management functionality
let currentCustomerPage = 1 // If pagination is needed later
const customersPerPage = 10 // If pagination is needed later

// Main function to fetch and render customers
async function fetchAndRenderCustomers() {
  const customersListContainer = document.getElementById("customersList")
  customersListContainer.innerHTML = `<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i><h5 class="text-muted mt-3">Cargando clientes...</h5></div>`

  try {
    const searchInput = document.getElementById("customerSearchInput").value
    const typeFilter = document.getElementById("customerTypeFilter").value
    const dateFrom = document.getElementById("customerDateFrom")
      ? document.getElementById("customerDateFrom").value
      : ""
    const dateTo = document.getElementById("customerDateTo")
      ? document.getElementById("customerDateTo").value
      : ""
    const showDeleted = document.getElementById("showDeletedFilter")
      ? document.getElementById("showDeletedFilter").checked
      : false

    let url = `php/api_clientes.php?page=${currentCustomerPage}&limit=${customersPerPage}`
    if (searchInput) {
      url += `&search=${encodeURIComponent(searchInput)}`
    }
    if (typeFilter) {
      url += `&type=${encodeURIComponent(typeFilter)}`
    }
    if (dateFrom) {
      url += `&dateFrom=${encodeURIComponent(dateFrom)}`
    }
    if (dateTo) {
      url += `&dateTo=${encodeURIComponent(dateTo)}`
    }
    if (showDeleted) {
      url += `&show_deleted=true`
    }

    const data = await fetchJSON(url)

    renderCustomers(data.data.customers)
    // updateCustomerStats(data.data.totalCustomers); // If total customers is returned by API
    fetchAndRenderFrequentCustomers() // Always update frequent customers
  } catch (error) {
    console.error("Error fetching customers:", error)
    customersListContainer.innerHTML = `<div class="text-center py-5 text-danger">${error.message}</div>`
  }
}

function renderCustomers(customers) {
  const customersList = document.getElementById("customersList")
  customersList.innerHTML = "" // Clear previous content

  if (!customers || customers.length === 0) {
    customersList.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron clientes</h5>
                <p class="text-muted">Intenta ajustar los filtros o crear un nuevo cliente</p>
            </div>
        `
    return
  }

  const tableHtml = `
                <div class="table-responsive">
            <table class="table table-buzz">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>VIP</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    ${customers
                      .map((customer) => {
                        const isDeleted = customer.deleted_at !== null
                        const deletedBadge = isDeleted
                          ? '<span class="badge bg-danger ms-2">ELIMINADO</span>'
                          : ""

                        let actionButtons = ""
                        if (isDeleted) {
                          actionButtons = `
                                  <button class="btn btn-outline-success btn-sm" onclick="restoreCustomer('${customer.id}')" title="Restaurar">
                                      <i class="fas fa-trash-restore"></i>
                                  </button>
                              `
                        } else {
                          actionButtons = `
                                  <button class="btn-action btn-view me-1" onclick="viewCustomerDetail('${customer.id}')" title="Ver detalle">
                                      <i class="fas fa-eye"></i>
                                  </button>
                                  <button class="btn-action btn-edit me-1" onclick="editCustomer('${customer.id}')" title="Editar">
                                      <i class="fas fa-edit"></i>
                                  </button>
                                  <button class="btn-action btn-delete" onclick="deleteCustomer('${customer.id}')" title="Eliminar">
                                      <i class="fas fa-trash"></i>
                                  </button>
                              `
                        }

                        return `
                          <tr ${isDeleted ? 'class="table-danger"' : ""}>
                              <td>${customer.id}${deletedBadge}</td>
                              <td>${customer.nombre}</td>
                              <td>${customer.email || "N/A"}</td>
                              <td>${customer.telefono || "N/A"}</td>
                              <td>${
                                customer.vip == 1
                                  ? '<i class="fas fa-star text-warning"></i> Sí'
                                  : "No"
                              }</td>
                              <td>
                                  ${actionButtons}
                              </td>
                          </tr>
                      `
                      })
                      .join("")}
                </tbody>
            </table>
        </div>
    `
  customersList.innerHTML = tableHtml
  document.getElementById("totalCustomersNum").textContent = customers.length // Update total customers stat
}

async function fetchAndRenderFrequentCustomers() {
  const frequentCustomersListContainer = document.getElementById(
    "frequentCustomersList"
  )
  frequentCustomersListContainer.innerHTML = `<p class="text-muted">Cargando clientes frecuentes...</p>`
  try {
    const data = await fetchJSON(`php/api_clientes.php?action=frequent`)
    const frequentCustomers = data.data.frequentCustomers

    if (frequentCustomers && frequentCustomers.length > 0) {
      frequentCustomersListContainer.innerHTML = frequentCustomers
        .map(
          (customer) => `
            <div class="frequent-customer-item">
                <span class="frequent-customer-name">${customer.nombre}</span>
                <span class="frequent-customer-orders">${customer.total_orders} pedidos</span>
            </div>
        `
        )
        .join("")
    } else {
      frequentCustomersListContainer.innerHTML = `<p class="text-muted">No hay clientes frecuentes aún.</p>`
    }
  } catch (error) {
    console.error("Error fetching frequent customers:", error)
    frequentCustomersListContainer.innerHTML = `<p class="text-danger">Error al cargar clientes frecuentes: ${error.message}</p>`
  }
}

async function viewCustomerDetail(customerId) {
  try {
    const data = await fetchJSON(`php/api_clientes.php?id=${customerId}`)
    const customer = data.data.customer

    if (customer) {
      document.getElementById("viewCustomerId").textContent = customer.id
      document.getElementById("viewCustomerName").textContent = customer.nombre
      document.getElementById("viewCustomerEmail").textContent =
        customer.email || "No especificado"
      document.getElementById("viewCustomerPhone").textContent =
        customer.telefono || "No especificado"
      document.getElementById("viewCustomerAddress").textContent =
        customer.direccion || "No especificada"
      document.getElementById("viewCustomerNotes").textContent =
        customer.notas || "Sin notas"

      // Handle VIP badge visibility
      const vipBadge = document.getElementById("viewCustomerVip")
      if (customer.vip == 1) {
        vipBadge.style.display = "inline-block"
      } else {
        vipBadge.style.display = "none"
      }

      document.getElementById("viewCustomerCreatedAt").textContent =
        formatDateTime(customer.creado_at)
      document.getElementById("viewCustomerTotalOrders").textContent =
        customer.total_orders || "0"

      openModal('viewCustomerModal')
    } else {
      showToast("Cliente no encontrado.", "error")
    }
  } catch (error) {
    console.error("Error fetching customer details:", error)
    showToast(`Error al cargar detalles del cliente: ${error.message}`, "error")
  }
}

async function editCustomer(customerId) {
  try {
    const data = await fetchJSON(`php/api_clientes.php?id=${customerId}`)
    const customer = data.data.customer

    if (customer) {
      document.getElementById("editCustomerId").textContent = customer.id
      document.getElementById("editCustomerIdHidden").value = customer.id
      document.getElementById("editCustomerName").value = customer.nombre
      document.getElementById("editCustomerEmail").value = customer.email
      document.getElementById("editCustomerPhone").value = customer.telefono
      // Parse phone to set country code selector
      parseAndSetPhoneFields(customer.telefono, "editCustomerPhoneCountryCode", "editCustomerPhone");
      document.getElementById("editCustomerAddress").value = customer.direccion
      document.getElementById("editCustomerNotes").value = customer.notas
      document.getElementById("editCustomerVip").checked = customer.vip == 1

      openModal('editCustomerModal')

      document.getElementById("editCustomerForm").onsubmit = async (event) => {
        event.preventDefault()
        const editForm = document.getElementById("editCustomerForm")
        if (typeof validateFormWithToast === "function" && !validateFormWithToast(editForm)) return

        const email = document.getElementById("editCustomerEmail").value.trim()
        if (email && typeof isValidEmail === "function") {
          const emailCheck = isValidEmail(email)
          if (!emailCheck.valid) {
            showToast(emailCheck.message, "warning")
            document.getElementById("editCustomerEmail").classList.add("is-invalid")
            document.getElementById("editCustomerEmail").focus()
            return
          }
        }

        const fullPhone = combinePhoneNumber("editCustomerPhoneCountryCode", "editCustomerPhone")
        const countryCode = document.getElementById("editCustomerPhoneCountryCode").value
        const phoneInput = document.getElementById("editCustomerPhone")
        const phoneCheck = validatePhoneByCountry(fullPhone, countryCode)
        if (!phoneCheck.valid) {
          showToast(phoneCheck.message, "warning")
          phoneInput.classList.add("is-invalid")
          phoneInput.focus()
          return
        }

        const updatedCustomer = {
          id: document.getElementById("editCustomerIdHidden").value,
          nombre: document.getElementById("editCustomerName").value,
          email: document.getElementById("editCustomerEmail").value,
          telefono: combinePhoneNumber("editCustomerPhoneCountryCode", "editCustomerPhone"),
          direccion: document.getElementById("editCustomerAddress").value,
          notas: document.getElementById("editCustomerNotes").value,
          vip: document.getElementById("editCustomerVip").checked ? 1 : 0
        }

        try {
          await fetchJSON(`php/api_clientes.php?id=${updatedCustomer.id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(updatedCustomer)
          })
          showToast("Cliente actualizado exitosamente.", "success")
          closeModal('editCustomerModal', true)
          fetchAndRenderCustomers()
        } catch (error) {
          console.error("Error updating customer:", error)
          showToast(`Error al actualizar cliente: ${error.message}`, "error")
        }
      }
    } else {
      showToast("Cliente no encontrado para editar.", "error")
    }
  } catch (error) {
    console.error("Error fetching customer for edit:", error)
    showToast(`Error al cargar cliente para editar: ${error.message}`, "error")
  }
}

async function deleteCustomer(customerId) {
  showModal({
    title: "Confirmar Eliminación",
    message: `¿Estás seguro de que deseas eliminar el cliente ${customerId}?`,
    type: "confirm",
    onConfirm: async () => {
      try {
        await fetchJSON(`php/api_clientes.php?id=${customerId}`, {
          method: "DELETE"
        })
        showToast("Cliente eliminado exitosamente.", "success")
        fetchAndRenderCustomers() // Refresh list
      } catch (error) {
        console.error("Error deleting customer:", error)
        showToast(`Error al eliminar cliente: ${error.message}`, "error")
      }
    }
  })
}

async function restoreCustomer(customerId) {
  try {
    const response = await fetchJSON("php/api_clientes.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "restore", id: customerId })
    })

    if (response.success) {
      showToast("Cliente restaurado exitosamente.", "success")
      fetchAndRenderCustomers()
    } else {
      showToast(`Error: ${response.message}`, "error")
    }
  } catch (error) {
    console.error("Error restoring customer:", error)
    showToast(`Error al restaurar cliente: ${error.message}`, "error")
  }
}

async function setupCreateCustomerForm() {
  const form = document.getElementById("createCustomerForm")
  if (typeof initEmailValidation === "function") {
    initEmailValidation([
      document.getElementById("createCustomerEmail"),
      document.getElementById("editCustomerEmail")
    ].filter(Boolean))
  }

  form.onsubmit = async (event) => {
    event.preventDefault()
    if (typeof validateFormWithToast === "function" && !validateFormWithToast(form)) return

    const email = document.getElementById("createCustomerEmail").value.trim()
    if (email && typeof isValidEmail === "function") {
      const emailCheck = isValidEmail(email)
      if (!emailCheck.valid) {
        showToast(emailCheck.message, "warning")
        document.getElementById("createCustomerEmail").classList.add("is-invalid")
        document.getElementById("createCustomerEmail").focus()
        return
      }
    }

    const fullPhone = combinePhoneNumber("createCustomerPhoneCountryCode", "createCustomerPhone")
    const countryCode = document.getElementById("createCustomerPhoneCountryCode").value
    const phoneInput = document.getElementById("createCustomerPhone")
    const phoneCheck = validatePhoneByCountry(fullPhone, countryCode)
    if (!phoneCheck.valid) {
      showToast(phoneCheck.message, "warning")
      phoneInput.classList.add("is-invalid")
      phoneInput.focus()
      return
    }

    // Combine ID type and number
    const idType = document.getElementById("createCustomerIdType").value
    const idNumber = document.getElementById("createCustomerId").value
    const fullId = `${idType}-${idNumber}`

    const newCustomer = {
      id: fullId,
      nombre: document.getElementById("createCustomerName").value,
      email: document.getElementById("createCustomerEmail").value,
      telefono: fullPhone,
      direccion: document.getElementById("createCustomerAddress").value,
      notas: document.getElementById("createCustomerNotes").value,
      vip: document.getElementById("createCustomerVip").checked ? 1 : 0
    }

    try {
      const response = await fetchJSON("php/api_clientes.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(newCustomer)
      })

      if (response.success) {
        showToast("Cliente creado exitosamente.", "success")
        closeModal('createCustomerModal', true)
        form.reset()
        fetchAndRenderCustomers()
      } else {
        showToast(`Error: ${response.message}`, "error")
      }
    } catch (error) {
      console.error("Error creating customer:", error)
      showToast(`Error al crear cliente: ${error.message}`, "error")
    }
  }
}

// Clear filters function
window.clearFilters = function () {
  document.getElementById("customerSearchInput").value = ""
  document.getElementById("customerTypeFilter").value = ""
  const showDeletedFilter = document.getElementById("showDeletedFilter")
  if (showDeletedFilter) {
    showDeletedFilter.checked = false
  }
  currentCustomerPage = 1
  fetchAndRenderCustomers()
}

// Event listeners for filters
document
  .getElementById("applyCustomerFilters")
  .addEventListener("click", fetchAndRenderCustomers)
document
  .getElementById("customerSearchInput")
  .addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      fetchAndRenderCustomers()
    }
  })
document
  .getElementById("customerTypeFilter")
  .addEventListener("change", () => { fetchAndRenderCustomers(); updateActiveFiltersCount(); });
document
  .getElementById("showDeletedFilter")
  .addEventListener("change", () => { fetchAndRenderCustomers(); updateActiveFiltersCount(); });

// Date filter event listeners
if (document.getElementById("customerDateFrom")) {
  document.getElementById("customerDateFrom").addEventListener("change", () => { fetchAndRenderCustomers(); updateActiveFiltersCount(); });
}
if (document.getElementById("customerDateTo")) {
  document.getElementById("customerDateTo").addEventListener("change", () => { fetchAndRenderCustomers(); updateActiveFiltersCount(); });
}

// Clear filters function
window.clearFilters = function() {
  document.getElementById('customerSearchInput').value = '';
  document.getElementById('customerTypeFilter').value = '';
  if (document.getElementById('customerDateFrom')) {
    document.getElementById('customerDateFrom').value = '';
  }
  if (document.getElementById('customerDateTo')) {
    document.getElementById('customerDateTo').value = '';
  }
  const showDeletedFilter = document.getElementById('showDeletedFilter');
  if (showDeletedFilter) {
    showDeletedFilter.checked = false;
  }
  currentCustomerPage = 1;
  fetchAndRenderCustomers();
  updateActiveFiltersCount();
};

// Advanced filters toggle logic
function setupAdvancedFilters() {
    const toggleBtn = document.getElementById("toggleAdvancedFilters");
    const advancedCollapse = document.getElementById("advancedFiltersCollapse");
    const activeBadge = document.getElementById("activeFiltersBadge");

    if (toggleBtn && advancedCollapse) {
        // Restore state from localStorage
        const isAdvVisible = localStorage.getItem("advFiltersVisible_clientes") === "true";
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
                localStorage.setItem("advFiltersVisible_clientes", "true");
            } else {
                advancedCollapse.classList.add("d-none");
                toggleBtn.classList.remove("active");
                localStorage.setItem("advFiltersVisible_clientes", "false");
            }
        });
    }
}

function updateActiveFiltersCount() {
    const activeBadge = document.getElementById("activeFiltersBadge");
    if (!activeBadge) return;

    let activeFilters = 0;
    // Only count filters in the advanced section
    const customerTypeFilter = document.getElementById("customerTypeFilter");
    const customerDateFrom = document.getElementById("customerDateFrom");
    const customerDateTo = document.getElementById("customerDateTo");
    const showDeletedFilter = document.getElementById("showDeletedFilter");

    if (customerTypeFilter && customerTypeFilter.value !== "") activeFilters++;
    if (customerDateFrom && customerDateFrom.value !== "") activeFilters++;
    if (customerDateTo && customerDateTo.value !== "") activeFilters++;
    if (showDeletedFilter && showDeletedFilter.checked) activeFilters++;

    if (activeFilters > 0) {
        activeBadge.textContent = activeFilters;
        activeBadge.classList.remove("d-none");
    } else {
        activeBadge.classList.add("d-none");
    }
}

// Initial load
document.addEventListener("DOMContentLoaded", () => {
  fetchAndRenderCustomers();
  setupAdvancedFilters();
  setupCreateCustomerForm();
});

// Valida teléfono de forma general para evitar bloqueos estrictos por país
function validatePhoneByCountry(fullPhone, countryCode) {
  const digits = (fullPhone || "").replace(/\D/g, "")
  if (digits.length === 0) return { valid: true }

  // Mínimo 8 dígitos (ej. código de país + número corto)
  if (digits.length < 8) {
    return { valid: false, message: "El teléfono debe tener al menos 8 dígitos." }
  }
  return { valid: true }
}

// Helper function to combine country code and phone number
function combinePhoneNumber(countryCodeId, phoneId) {
  const countryCode = document.getElementById(countryCodeId).value;
  let phoneNumber = document.getElementById(phoneId).value.trim();
  // Remove leading zero if present
  if (phoneNumber.startsWith("0")) {
    phoneNumber = phoneNumber.substring(1);
  }
  return countryCode + phoneNumber;
}

// Helper function to parse stored phone and set fields
function parseAndSetPhoneFields(fullPhone, countryCodeId, phoneId) {
  if (!fullPhone) return;

  const countryCodes = ["+58", "+1", "+57", "+54", "+34", "+52", "+55"];
  let matchedCode = "+58"; // Default
  let localNumber = fullPhone;

  for (const code of countryCodes) {
    if (fullPhone.startsWith(code)) {
      matchedCode = code;
      localNumber = fullPhone.substring(code.length);
      break;
    }
  }

  document.getElementById(countryCodeId).value = matchedCode;
  document.getElementById(phoneId).value = localNumber;
}
