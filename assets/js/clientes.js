// Customers management functionality
let currentCustomerPage = 1; // If pagination is needed later
const customersPerPage = 10; // If pagination is needed later

// Main function to fetch and render customers
async function fetchAndRenderCustomers() {
    const customersListContainer = document.getElementById("customersList");
    customersListContainer.innerHTML = `<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i><h5 class="text-muted mt-3">Cargando clientes...</h5></div>`;

    try {
        const searchInput = document.getElementById('customerSearchInput').value;
        const typeFilter = document.getElementById('customerTypeFilter').value;

        let url = `/buzz/php/clientes_actions.php?page=${currentCustomerPage}&limit=${customersPerPage}`;
        if (searchInput) {
            url += `&search=${searchInput}`;
        }
        if (typeFilter) {
            url += `&type=${typeFilter}`;
        }

        const data = await fetchJSON(url);
        
        renderCustomers(data.data.customers);
        // updateCustomerStats(data.data.totalCustomers); // If total customers is returned by API
        fetchAndRenderFrequentCustomers(); // Always update frequent customers
    } catch (error) {
        console.error('Error fetching customers:', error);
        customersListContainer.innerHTML = `<div class="text-center py-5 text-danger">${error.message}</div>`;
    }
}

function renderCustomers(customers) {
    const customersList = document.getElementById("customersList");
    customersList.innerHTML = ''; // Clear previous content

    if (!customers || customers.length === 0) {
        customersList.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No se encontraron clientes</h5>
                <p class="text-muted">Intenta ajustar los filtros o crear un nuevo cliente</p>
            </div>
        `;
        return;
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
                    ${customers.map(customer => `
                        <tr>
                            <td>${customer.id}</td>
                            <td>${customer.nombre}</td>
                            <td>${customer.email || 'N/A'}</td>
                            <td>${customer.telefono || 'N/A'}</td>
                            <td>${customer.vip == 1 ? '<i class="fas fa-star text-warning"></i> Sí' : 'No'}</td>
                            <td>
                                <button class="btn btn-outline-buzz btn-sm me-1" onclick="viewCustomerDetail('${customer.id}')" title="Ver detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-buzz btn-sm me-1" onclick="editCustomer('${customer.id}')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteCustomer('${customer.id}')" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;
    customersList.innerHTML = tableHtml;
    document.getElementById('totalCustomers').textContent = customers.length; // Update total customers stat
}

async function fetchAndRenderFrequentCustomers() {
    const frequentCustomersListContainer = document.getElementById('frequentCustomersList');
    frequentCustomersListContainer.innerHTML = `<p class="text-muted">Cargando clientes frecuentes...</p>`;
    try {
        const data = await fetchJSON(`/buzz/php/clientes_actions.php?action=frequent`);
        const frequentCustomers = data.data.frequentCustomers;

        if (frequentCustomers && frequentCustomers.length > 0) {
            frequentCustomersListContainer.innerHTML = `
                <ul class="list-group list-group-flush">
                    ${frequentCustomers.map(customer => `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            ${customer.nombre} <span class="badge bg-primary rounded-pill">${customer.total_orders} pedidos</span>
                        </li>
                    `).join('')}
                </ul>
            `;
        } else {
            frequentCustomersListContainer.innerHTML = `<p class="text-muted">No hay clientes frecuentes aún.</p>`;
        }
    } catch (error) {
        console.error('Error fetching frequent customers:', error);
        frequentCustomersListContainer.innerHTML = `<p class="text-danger">Error al cargar clientes frecuentes: ${error.message}</p>`;
    }
}

async function viewCustomerDetail(customerId) {
    try {
        const data = await fetchJSON(`/buzz/php/clientes_actions.php?id=${customerId}`);
        const customer = data.data.customer;

        if (customer) {
            document.getElementById('viewCustomerId').textContent = customer.id;
            document.getElementById('viewCustomerName').textContent = customer.nombre;
            document.getElementById('viewCustomerEmail').textContent = customer.email || 'N/A';
            document.getElementById('viewCustomerPhone').textContent = customer.telefono || 'N/A';
            document.getElementById('viewCustomerAddress').textContent = customer.direccion || 'N/A';
            document.getElementById('viewCustomerNotes').textContent = customer.notas || 'N/A';
            document.getElementById('viewCustomerVip').textContent = customer.vip == 1 ? 'Sí' : 'No';
            document.getElementById('viewCustomerCreatedAt').textContent = formatDateTime(customer.creado_at);

            const viewModal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
            viewModal.show();
        } else {
            showToast('Cliente no encontrado.', 'error');
        }
    } catch (error) {
        console.error('Error fetching customer details:', error);
        showToast(`Error al cargar detalles del cliente: ${error.message}`, 'error');
    }
}

async function editCustomer(customerId) {
    try {
        const data = await fetchJSON(`/buzz/php/clientes_actions.php?id=${customerId}`);
        const customer = data.data.customer;

        if (customer) {
            document.getElementById('editCustomerId').textContent = customer.id; // Display ID in modal title
            document.getElementById('editCustomerIdHidden').value = customer.id; // Hidden input for form submission
            document.getElementById('editCustomerName').value = customer.nombre;
            document.getElementById('editCustomerEmail').value = customer.email;
            document.getElementById('editCustomerPhone').value = customer.telefono;
            document.getElementById('editCustomerAddress').value = customer.direccion;
            document.getElementById('editCustomerNotes').value = customer.notas;
            document.getElementById('editCustomerVip').checked = customer.vip == 1;

            const editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
            editModal.show();

            document.getElementById('editCustomerForm').onsubmit = async (event) => {
                event.preventDefault();
                const updatedCustomer = {
                    id: document.getElementById('editCustomerIdHidden').value,
                    nombre: document.getElementById('editCustomerName').value,
                    email: document.getElementById('editCustomerEmail').value,
                    telefono: document.getElementById('editCustomerPhone').value,
                    direccion: document.getElementById('editCustomerAddress').value,
                    notas: document.getElementById('editCustomerNotes').value,
                    vip: document.getElementById('editCustomerVip').checked ? 1 : 0
                };

                try {
                    await fetchJSON(`/buzz/php/clientes_actions.php?id=${updatedCustomer.id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(updatedCustomer)
                    });
                    showToast('Cliente actualizado exitosamente.', 'success');
                    editModal.hide();
                    fetchAndRenderCustomers(); // Refresh list
                } catch (error) {
                    console.error('Error updating customer:', error);
                    showToast(`Error al actualizar cliente: ${error.message}`, 'error');
                }
            };
        } else {
            showToast('Cliente no encontrado para editar.', 'error');
        }
    } catch (error) {
        console.error('Error fetching customer for edit:', error);
        showToast(`Error al cargar cliente para editar: ${error.message}`, 'error');
    }
}


async function createCustomer() {
    // Placeholder for customer creation logic
    console.log("createCustomer function called. Implement actual customer creation logic here.");
    // Example: Reset form fields if needed
    // document.getElementById('createCustomerForm').reset();
}


async function deleteCustomer(customerId) {
    showModal({
        title: "Confirmar Eliminación",
        message: `¿Estás seguro de que deseas eliminar el cliente #${customerId}?`,
        type: "confirm",
        onConfirm: async () => {
            try {
                await fetchJSON(`/buzz/php/clientes_actions.php?id=${customerId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' }
                });
                showToast('Cliente eliminado exitosamente.', 'success');
                fetchAndRenderCustomers(); // Refresh list
            } catch (error) {
                console.error('Error deleting customer:', error);
                showToast(`Error al eliminar cliente: ${error.message}`, 'error');
            }
        }
    });
}

// Event listeners for filters
document.getElementById('applyCustomerFilters').addEventListener('click', fetchAndRenderCustomers);
document.getElementById('customerSearchInput').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        fetchAndRenderCustomers();
    }
});
document.getElementById('customerTypeFilter').addEventListener('change', fetchAndRenderCustomers);


// Initial load
document.addEventListener("DOMContentLoaded", () => {
    fetchAndRenderCustomers();

    const createCustomerModalElement = document.getElementById('createCustomerModal');
    if (createCustomerModalElement) {
        createCustomerModalElement.addEventListener('show.bs.modal', createCustomer);
    }
});