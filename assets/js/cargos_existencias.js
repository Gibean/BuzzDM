/**
 * Cargos de Existencias - JavaScript
 * Sistema de carga masiva de inventario
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar
    inicializarFecha();
    cargarAlmacenes();
    cargarCargos();
    configurarEventos();
});

// =====================================================
// VARIABLES GLOBALES
// =====================================================

let lineaIndex = 0;
let paginaActual = 1;
const porPagina = 10;
let totalCargos = 0;
let cargoActualId = null;
let productosBuscados = {};

// =====================================================
// INICIALIZACIÓN
// =====================================================

function inicializarFecha() {
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('cargoFecha').value = hoy;
}

function configurarEventos() {
    // Filtros
    document.getElementById('searchCargos').addEventListener('input', debounce(cargarCargos, 300));
    document.getElementById('filterEstado').addEventListener('change', cargarCargos);
    document.getElementById('filterConcepto').addEventListener('change', cargarCargos);
    document.getElementById('filterFechaDesde').addEventListener('change', cargarCargos);
    document.getElementById('filterFechaHasta').addEventListener('change', cargarCargos);

    // Concepto change handler (también llamado desde onchange en HTML)
    document.getElementById('cargoConcepto').addEventListener('change', function() {
        onConceptoChange();
    });

    // Búsqueda de proveedor
    const proveedorInput = document.getElementById('proveedorBusqueda');
    proveedorInput.addEventListener('input', debounce(function() {
        buscarProveedor(this.value);
    }, 300));

    // Cerrar autocomplete al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-results') && !e.target.closest('.sku-input') && !e.target.closest('#proveedorBusqueda')) {
            cerrarTodosAutocomplete();
        }
    });
}

// =====================================================
// UTILIDADES
// =====================================================

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function formatearMoneda(valor) {
    return '$' + parseFloat(valor || 0).toFixed(2);
}

function cerrarTodosAutocomplete() {
    document.querySelectorAll('.autocomplete-results').forEach(el => {
        el.classList.remove('show');
        el.innerHTML = '';
    });
}

// =====================================================
// NAVEGACIÓN ENTRE VISTAS
// =====================================================

function verHistorial() {
    document.getElementById('vistaHistorial').style.display = 'block';
    document.getElementById('vistaFormulario').style.display = 'none';
    cargoActualId = null;
    cargarCargos();
}

function nuevoCargo() {
    cargoActualId = null;
    limpiarFormulario();
    document.getElementById('vistaHistorial').style.display = 'none';
    document.getElementById('vistaFormulario').style.display = 'block';
    document.getElementById('btnBorrador').style.display = 'inline-flex';
    document.getElementById('btnConfirmar').style.display = 'inline-flex';

    // Agregar primera línea vacía
    agregarLineaProducto();
}

function cancelarCargo() {
    showConfirmation(
        'Cancelar Cargo',
        '¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.',
        function() {
            verHistorial();
        }
    );
}

function limpiarFormulario() {
    document.getElementById('formCargo').reset();
    document.getElementById('cargoId').value = '';
    document.getElementById('cargoCodigo').value = '';
    document.getElementById('cargoProveedorId').value = '';
    document.getElementById('proveedorSeleccionado').style.display = 'none';
    document.getElementById('lineasProductosContainer').innerHTML = '';
    document.getElementById('sinProductos').style.display = 'block';
    document.getElementById('conceptoOtroContainer').style.display = 'none';
    lineaIndex = 0;
    actualizarTotales();
    inicializarFecha();
    // Reset almacen selector
    const almacenSelect = document.getElementById('cargoAlmacenId');
    if (almacenSelect) almacenSelect.selectedIndex = 0;
}

// =====================================================
// CARGAR Y MOSTRAR CARGOS (HISTORIAL)
// =====================================================

async function cargarCargos() {
    const params = new URLSearchParams({
        page: paginaActual,
        limit: porPagina,
        search: document.getElementById('searchCargos').value,
        estado: document.getElementById('filterEstado').value,
        concepto: document.getElementById('filterConcepto').value,
        fecha_desde: document.getElementById('filterFechaDesde').value,
        fecha_hasta: document.getElementById('filterFechaHasta').value
    });

    try {
        const response = await fetch(`php/cargos_actions.php?${params.toString()}`);
        const data = await response.json();

        if (data.success) {
            renderizarTablaCargos(data.cargos);
            totalCargos = data.total;
            actualizarPaginacion();
        } else {
            showToast('Error al cargar cargos: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al conectar con el servidor', 'danger');
    }
}

function renderizarTablaCargos(cargos) {
    const tbody = document.getElementById('tablaCargosBody');

    if (!cargos || cargos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p>No hay cargos registrados</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = cargos.map(cargo => `
        <tr class="row-${cargo.estado}">
            <td>
                <span class="font-mono text-primary">${cargo.codigo}</span>
            </td>
            <td>${formatearFecha(cargo.fecha)}</td>
            <td>${cargo.concepto_texto}</td>
            <td>${cargo.proveedor_nombre || '<span class="text-muted">-</span>'}</td>
            <td class="text-center">${cargo.total_productos}</td>
            <td class="text-center">${cargo.total_unidades}</td>
            <td class="font-mono">${formatearMoneda(cargo.valor_total)}</td>
            <td>
                <span class="badge badge-${cargo.estado}">${cargo.estado_texto}</span>
            </td>
            <td>
                <div class="acciones-cargo">
                    <button class="btn-accion-cargo" onclick="verDetalleCargo(${cargo.id})" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${cargo.estado === 'borrador' ? `
                        <button class="btn-accion-cargo" onclick="editarCargo(${cargo.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-accion-cargo btn-confirmar" onclick="confirmarCargoDesdeHistorial(${cargo.id})" title="Confirmar">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                    ${cargo.estado === 'confirmado' ? `
                        <button class="btn-accion-cargo btn-anular" onclick="mostrarModalAnular(${cargo.id})" title="Anular">
                            <i class="fas fa-ban"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha + 'T00:00:00');
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function actualizarPaginacion() {
    const totalPaginas = Math.ceil(totalCargos / porPagina);
    const desde = totalCargos === 0 ? 0 : (paginaActual - 1) * porPagina + 1;
    const hasta = Math.min(paginaActual * porPagina, totalCargos);

    document.getElementById('showingFrom').textContent = desde;
    document.getElementById('showingTo').textContent = hasta;
    document.getElementById('totalCargos').textContent = totalCargos;

    const paginacion = document.getElementById('paginationCargos');
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }

    let html = '';

    // Anterior
    html += `
        <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">&laquo;</a>
        </li>
    `;

    // Páginas
    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `
                <li class="page-item ${i === paginaActual ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
                </li>
            `;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    // Siguiente
    html += `
        <li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">&raquo;</a>
        </li>
    `;

    paginacion.innerHTML = html;
}

function cambiarPagina(pagina) {
    const totalPaginas = Math.ceil(totalCargos / porPagina);
    if (pagina < 1 || pagina > totalPaginas) return;
    paginaActual = pagina;
    cargarCargos();
}

function limpiarFiltros() {
    document.getElementById('searchCargos').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterConcepto').value = '';
    document.getElementById('filterFechaDesde').value = '';
    document.getElementById('filterFechaHasta').value = '';
    paginaActual = 1;
    cargarCargos();
}

// =====================================================
// LÍNEAS DE PRODUCTOS
// =====================================================

function agregarLineaProducto(producto = null) {
    const container = document.getElementById('lineasProductosContainer');
    const lineaId = `linea-${lineaIndex++}`;

    document.getElementById('sinProductos').style.display = 'none';

    const html = `
        <div class="cargo-linea-row" id="${lineaId}" data-producto-id="${producto?.id || ''}">
            <div class="sku-container">
                <input type="text"
                       class="form-control form-control-buzz sku-input"
                       placeholder="BUSCAR SKU..."
                       value="${producto?.sku || ''}"
                       ${producto ? 'readonly' : ''}
                       autocomplete="off">
                <div class="autocomplete-results"></div>
            </div>
            <div class="linea-col col-nombre">
                <span class="nombre-display ${producto ? '' : 'placeholder'}">
                    ${producto?.nombre || 'Selecciona un producto...'}
                </span>
            </div>
            <div class="linea-col col-cantidad">
                <div class="input-group input-group-sm">
                    <input type="number"
                           class="form-control form-control-buzz cantidad-input"
                           min="1"
                           value="${producto?.cantidad || 1}">
                </div>
            </div>
            <div class="linea-col col-acciones">
                <button type="button" class="btn-eliminar-linea" onclick="eliminarLinea('${lineaId}')" title="Eliminar línea">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <!-- Campo oculto para mantener compatibilidad con el backend si es necesario -->
            <input type="hidden" class="costo-input" value="${producto?.costo || 0}">
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    const linea = document.getElementById(lineaId);
    const skuInput = linea.querySelector('.sku-input');
    const resultsContainer = linea.querySelector('.autocomplete-results');
    const cantidadInput = linea.querySelector('.cantidad-input');
    const costoInput = linea.querySelector('.costo-input');

    // Configurar autocomplete
    if (!producto) {
        skuInput.addEventListener('input', debounce(function() {
            buscarProductos(this.value, resultsContainer, lineaId);
        }, 300));

        skuInput.addEventListener('focus', function() {
            if (this.value.length >= 2) {
                buscarProductos(this.value, resultsContainer, lineaId);
            }
        });
    }

    // Calcular totales en cambios
    cantidadInput.addEventListener('input', () => actualizarTotales());

    // Focus en el input de SKU si es nueva línea
    if (!producto) {
        skuInput.focus();
    }
}

function eliminarLinea(lineaId) {
    const linea = document.getElementById(lineaId);
    if (linea) {
        linea.remove();
        actualizarTotales();

        // Mostrar mensaje si no quedan líneas
        const container = document.getElementById('lineasProductosContainer');
        if (container.children.length === 0) {
            document.getElementById('sinProductos').style.display = 'block';
        }
    }
}

// =====================================================
// BÚSQUEDA DE PRODUCTOS
// =====================================================

async function buscarProductos(query, resultsContainer, lineaId) {
    if (query.length < 2) {
        resultsContainer.classList.remove('show');
        resultsContainer.innerHTML = '';
        return;
    }

    // Verificar productos ya agregados
    const productosEnCargo = obtenerProductosEnCargo(lineaId);

    try {
        const response = await fetch(`php/productos_search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.productos && data.productos.length > 0) {
            // Filtrar productos ya agregados
            const productosFiltrados = data.productos.filter(p => !productosEnCargo.includes(p.id.toString()));

            if (productosFiltrados.length > 0) {
                resultsContainer.innerHTML = productosFiltrados.map(p => `
                    <div class="autocomplete-item"
                         data-id="${p.id}"
                         data-sku="${p.sku}"
                         data-nombre="${escapeHtml(p.nombre)}"
                         data-precio="${p.precio}"
                         onclick="seleccionarProducto('${lineaId}', this)">
                        <span class="ac-sku">${p.sku}</span>
                        <span class="ac-nombre">${escapeHtml(p.nombre)}</span>
                        <span class="ac-stock">Stock: ${p.stock}</span>
                    </div>
                `).join('');
            } else {
                resultsContainer.innerHTML = '<div class="autocomplete-empty">Todos los productos coincidentes ya están agregados</div>';
            }
            resultsContainer.classList.add('show');
        } else {
            resultsContainer.innerHTML = '<div class="autocomplete-empty">No se encontraron productos</div>';
            resultsContainer.classList.add('show');
        }
    } catch (error) {
        console.error('Error buscando productos:', error);
    }
}

function obtenerProductosEnCargo(excludeLineaId = null) {
    const productos = [];
    document.querySelectorAll('.cargo-linea-row').forEach(linea => {
        if (linea.id !== excludeLineaId && linea.dataset.productoId) {
            productos.push(linea.dataset.productoId);
        }
    });
    return productos;
}

function seleccionarProducto(lineaId, elemento) {
    const linea = document.getElementById(lineaId);
    const id = elemento.dataset.id;
    const sku = elemento.dataset.sku;
    const nombre = elemento.dataset.nombre;
    const precio = parseFloat(elemento.dataset.precio) || 0;

    linea.dataset.productoId = id;
    linea.querySelector('.sku-input').value = sku;
    linea.querySelector('.sku-input').readOnly = true;
    linea.querySelector('.nombre-display').textContent = nombre;
    linea.querySelector('.nombre-display').classList.remove('placeholder');
    linea.querySelector('.costo-input').value = precio.toFixed(2);

    // Cerrar autocomplete
    linea.querySelector('.autocomplete-results').classList.remove('show');

    actualizarTotales();

    // Focus en cantidad
    linea.querySelector('.cantidad-input').focus();
    linea.querySelector('.cantidad-input').select();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// =====================================================
// CÁLCULOS
// =====================================================

function actualizarLineaTotales(lineaId) {
    // Solo actualiza los totales generales cuando cambia cantidad
    actualizarTotales();
}

function actualizarTotales() {
    let totalProductos = 0;
    let totalUnidades = 0;

    document.querySelectorAll('.cargo-linea-row').forEach(linea => {
        if (linea.dataset.productoId) {
            totalProductos++;
            const cantidad = parseFloat(linea.querySelector('.cantidad-input').value) || 0;
            totalUnidades += cantidad;
        }
    });

    document.getElementById('totalProductos').textContent = totalProductos;
    document.getElementById('totalUnidades').textContent = totalUnidades;
}

// =====================================================
// PROVEEDORES
// =====================================================

async function buscarProveedor(query) {
    const resultsContainer = document.getElementById('proveedorResultados');

    if (query.length < 2) {
        resultsContainer.classList.remove('show');
        resultsContainer.innerHTML = '';
        return;
    }

    try {
        const response = await fetch(`php/proveedores_search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success && data.proveedores && data.proveedores.length > 0) {
            resultsContainer.innerHTML = data.proveedores.map(p => `
                <div class="autocomplete-item" onclick="seleccionarProveedor(${p.id}, '${escapeHtml(p.nombre)}')">
                    <span class="ac-nombre">${escapeHtml(p.nombre)}</span>
                </div>
            `).join('');
            resultsContainer.classList.add('show');
        } else {
            resultsContainer.innerHTML = '<div class="autocomplete-empty">No se encontraron proveedores</div>';
            resultsContainer.classList.add('show');
        }
    } catch (error) {
        console.error('Error buscando proveedores:', error);
    }
}

function seleccionarProveedor(id, nombre) {
    document.getElementById('cargoProveedorId').value = id;
    document.getElementById('proveedorBusqueda').value = '';
    document.getElementById('proveedorResultados').classList.remove('show');

    const selectedDiv = document.getElementById('proveedorSeleccionado');
    selectedDiv.querySelector('.selected-name').textContent = nombre;
    selectedDiv.style.display = 'inline-flex';
}

function limpiarProveedor() {
    document.getElementById('cargoProveedorId').value = '';
    document.getElementById('proveedorSeleccionado').style.display = 'none';
}

// =====================================================
// GUARDAR Y CONFIRMAR
// =====================================================

async function guardarBorrador() {
    await guardarCargo(false);
}

async function confirmarCargo() {
    // Validar que hay productos
    const lineas = obtenerLineasValidas();
    if (lineas.length === 0) {
        showToast('Debes agregar al menos un producto', 'warning');
        return;
    }

    showConfirmation(
        'Confirmar Cargo',
        '¿Estás seguro de confirmar este cargo? El stock de los productos se actualizará inmediatamente.',
        async function() {
            await guardarCargo(true);
        }
    );
}

async function guardarCargo(confirmar) {
    // Validaciones
    const fecha = document.getElementById('cargoFecha').value;
    const concepto = document.getElementById('cargoConcepto').value;
    const conceptoOtro = document.getElementById('cargoConceptoOtro').value;

    if (!fecha) {
        showToast('La fecha es requerida', 'warning');
        return;
    }

    if (concepto === 'otro' && !conceptoOtro.trim()) {
        showToast('Debes especificar el concepto', 'warning');
        return;
    }

    const lineas = obtenerLineasValidas();

    if (confirmar && lineas.length === 0) {
        showToast('Debes agregar al menos un producto para confirmar', 'warning');
        return;
    }

    const data = {
        id: document.getElementById('cargoId').value || null,
        fecha: fecha,
        concepto: concepto,
        concepto_otro: conceptoOtro,
        proveedor_id: document.getElementById('cargoProveedorId').value || null,
        almacen_id: document.getElementById('cargoAlmacenId').value || null,
        referencia_externa: document.getElementById('cargoReferencia').value,
        notas: document.getElementById('cargoNotas').value,
        confirmar: confirmar,
        lineas: lineas
    };

    // Validate almacen is selected
    if (!data.almacen_id) {
        showToast('Debes seleccionar un almacén destino', 'warning');
        return;
    }

    try {
        const url = data.id
            ? `php/cargos_actions.php?id=${data.id}`
            : 'php/cargos_actions.php';
        const method = data.id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            verHistorial();
        } else {
            showToast('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar el cargo', 'danger');
    }
}

function obtenerLineasValidas() {
    const lineas = [];
    document.querySelectorAll('.cargo-linea-row').forEach(linea => {
        const productoId = linea.dataset.productoId;
        const cantidad = parseInt(linea.querySelector('.cantidad-input').value) || 0;
        const costo = parseFloat(linea.querySelector('.costo-input').value) || 0;

        if (productoId && cantidad > 0) {
            lineas.push({
                producto_id: productoId,
                cantidad: cantidad,
                costo_unitario: costo
            });
        }
    });
    return lineas;
}

// =====================================================
// EDITAR CARGO
// =====================================================

async function editarCargo(id) {
    try {
        const response = await fetch(`php/cargos_actions.php?id=${id}`);
        const data = await response.json();

        if (data.success && data.cargo) {
            cargarFormularioDesdeData(data.cargo);
        } else {
            showToast('Error al cargar el cargo', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al conectar con el servidor', 'danger');
    }
}

function cargarFormularioDesdeData(cargo) {
    limpiarFormulario();

    cargoActualId = cargo.id;
    document.getElementById('cargoId').value = cargo.id;
    document.getElementById('cargoCodigo').value = cargo.codigo;
    document.getElementById('cargoFecha').value = cargo.fecha;
    document.getElementById('cargoConcepto').value = cargo.concepto;

    if (cargo.concepto === 'otro') {
        document.getElementById('conceptoOtroContainer').style.display = 'block';
        document.getElementById('cargoConceptoOtro').value = cargo.concepto_otro || '';
    }

    if (cargo.proveedor_id) {
        document.getElementById('cargoProveedorId').value = cargo.proveedor_id;
        const selectedDiv = document.getElementById('proveedorSeleccionado');
        selectedDiv.querySelector('.selected-name').textContent = cargo.proveedor_nombre;
        selectedDiv.style.display = 'inline-flex';
    }

    document.getElementById('cargoReferencia').value = cargo.referencia_externa || '';
    document.getElementById('cargoNotas').value = cargo.notas || '';

    // Set almacen if exists
    if (cargo.almacen_id) {
        document.getElementById('cargoAlmacenId').value = cargo.almacen_id;
    }

    // Cargar líneas
    document.getElementById('sinProductos').style.display = 'none';
    if (cargo.detalles && cargo.detalles.length > 0) {
        cargo.detalles.forEach(detalle => {
            agregarLineaProducto({
                id: detalle.producto_id,
                sku: detalle.producto_sku,
                nombre: detalle.producto_nombre,
                cantidad: detalle.cantidad,
                costo: detalle.costo_unitario
            });
        });
    }

    document.getElementById('vistaHistorial').style.display = 'none';
    document.getElementById('vistaFormulario').style.display = 'block';
}

// =====================================================
// VER DETALLE
// =====================================================

async function verDetalleCargo(id) {
    try {
        const response = await fetch(`php/cargos_actions.php?id=${id}`);
        const data = await response.json();

        if (data.success && data.cargo) {
            mostrarModalDetalle(data.cargo);
        } else {
            showToast('Error al cargar el cargo', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al conectar con el servidor', 'danger');
    }
}

function mostrarModalDetalle(cargo) {
    const contenido = document.getElementById('modalDetalleContenido');

    let html = `
        <div class="detalle-cargo-info">
            <div class="info-item">
                <span class="info-label">Código</span>
                <span class="info-value font-mono text-primary">${cargo.codigo}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha</span>
                <span class="info-value">${formatearFecha(cargo.fecha)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Concepto</span>
                <span class="info-value">${cargo.concepto_texto}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado</span>
                <span class="badge badge-${cargo.estado}">${cargo.estado_texto}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Proveedor</span>
                <span class="info-value">${cargo.proveedor_nombre || '-'}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Referencia</span>
                <span class="info-value">${cargo.referencia_externa || '-'}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Registrado por</span>
                <span class="info-value">${cargo.registrado_por_nombre}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha registro</span>
                <span class="info-value">${formatearFechaHora(cargo.creado_at)}</span>
            </div>
        </div>

        ${cargo.notas ? `<div class="mb-3"><strong>Notas:</strong> ${escapeHtml(cargo.notas)}</div>` : ''}

        <div class="detalle-productos-tabla">
            <h6 class="text-muted mb-3">Productos del Cargo</h6>
            <table class="table table-buzz">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Producto</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Costo Unit.</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${cargo.detalles.map(d => `
                        <tr>
                            <td class="font-mono">${d.producto_sku}</td>
                            <td>${escapeHtml(d.producto_nombre)}</td>
                            <td class="text-end">${d.cantidad}</td>
                            <td class="text-end font-mono">${formatearMoneda(d.costo_unitario)}</td>
                            <td class="text-end font-mono">${formatearMoneda(d.subtotal)}</td>
                        </tr>
                    `).join('')}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"><strong>Totales</strong></td>
                        <td class="text-end"><strong>${cargo.total_unidades}</strong></td>
                        <td></td>
                        <td class="text-end font-mono text-primary"><strong>${formatearMoneda(cargo.valor_total)}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;

    contenido.innerHTML = html;

    // Mostrar/ocultar botones según estado
    const btnEditar = document.getElementById('btnEditarCargo');
    const btnConfirmar = document.getElementById('btnConfirmarDesdeModal');

    if (cargo.estado === 'borrador') {
        btnEditar.style.display = 'inline-flex';
        btnConfirmar.style.display = 'inline-flex';
        btnEditar.onclick = () => { cerrarModalDetalle(); editarCargo(cargo.id); };
        btnConfirmar.onclick = () => { cerrarModalDetalle(); confirmarCargoDesdeHistorial(cargo.id); };
    } else {
        btnEditar.style.display = 'none';
        btnConfirmar.style.display = 'none';
    }

    document.getElementById('modalDetalleCargo').style.display = 'block';
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalleCargo').style.display = 'none';
}

function formatearFechaHora(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha);
    return d.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// =====================================================
// CONFIRMAR DESDE HISTORIAL
// =====================================================

function confirmarCargoDesdeHistorial(id) {
    showConfirmation(
        'Confirmar Cargo',
        '¿Estás seguro de confirmar este cargo? El stock de los productos se actualizará inmediatamente.',
        async function() {
            try {
                const response = await fetch(`php/cargos_actions.php?action=confirm&id=${id}`, {
                    method: 'POST'
                });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    cargarCargos();
                } else {
                    showToast('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al confirmar el cargo', 'danger');
            }
        }
    );
}

// =====================================================
// ANULAR CARGO
// =====================================================

function mostrarModalAnular(id) {
    document.getElementById('anularCargoId').value = id;
    document.getElementById('motivoAnulacion').value = '';
    document.getElementById('modalAnular').style.display = 'block';
}

function cerrarModalAnular() {
    document.getElementById('modalAnular').style.display = 'none';
}

async function ejecutarAnulacion() {
    const id = document.getElementById('anularCargoId').value;
    const motivo = document.getElementById('motivoAnulacion').value.trim();

    if (!motivo) {
        showToast('Debes ingresar un motivo de anulación', 'warning');
        return;
    }

    try {
        const response = await fetch(`php/cargos_actions.php?action=cancel&id=${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ motivo: motivo })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cerrarModalAnular();
            cargarCargos();
        } else {
            showToast('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al anular el cargo', 'danger');
    }
}

// =====================================================
// CARGAR ALMACENES
// =====================================================

async function cargarAlmacenes() {
    const select = document.getElementById('cargoAlmacenId');
    if (!select) return;

    try {
        const response = await fetch('php/almacenes_actions.php');
        const data = await response.json();

        if (data.success && data.almacenes) {
            select.innerHTML = '<option value="">Seleccionar almacén...</option>';
            data.almacenes.forEach(almacen => {
                select.innerHTML += `<option value="${almacen.id}">${almacen.nombre}${almacen.es_principal ? ' (Principal)' : ''}</option>`;
            });

            // Auto-select principal warehouse if there's only one or if there's a principal
            const principal = data.almacenes.find(a => a.es_principal);
            if (principal) {
                select.value = principal.id;
            }
        }
    } catch (error) {
        console.error('Error cargando almacenes:', error);
    }
}

// =====================================================
// CONCEPTO CHANGE HANDLER
// =====================================================

function onConceptoChange() {
    const concepto = document.getElementById('cargoConcepto').value;
    const otroContainer = document.getElementById('conceptoOtroContainer');
    const proveedorContainer = document.getElementById('proveedorBusqueda')?.closest('.col-md-6');

    // Show/hide "otro" field
    if (otroContainer) {
        otroContainer.style.display = concepto === 'otro' ? 'block' : 'none';
        if (concepto !== 'otro') {
            document.getElementById('cargoConceptoOtro').value = '';
        }
    }

    // Show/hide proveedor field based on concept
    // Only show for "compra" (purchases)
    if (proveedorContainer) {
        if (concepto === 'compra') {
            proveedorContainer.style.display = 'block';
        } else {
            proveedorContainer.style.display = 'none';
            limpiarProveedor();
        }
    }
}

// =====================================================
// MODAL NUEVO PROVEEDOR (Quick Add)
// =====================================================

function abrirModalNuevoProveedor() {
    document.getElementById('nuevoProveedorNombre').value = '';
    document.getElementById('nuevoProveedorRif').value = '';
    document.getElementById('nuevoProveedorTelefono').value = '';
    document.getElementById('modalNuevoProveedor').style.display = 'block';
    document.getElementById('nuevoProveedorNombre').focus();
}

function cerrarModalNuevoProveedor() {
    document.getElementById('modalNuevoProveedor').style.display = 'none';
}

async function guardarNuevoProveedor() {
    const nombre = document.getElementById('nuevoProveedorNombre').value.trim();

    if (!nombre) {
        showToast('El nombre del proveedor es obligatorio', 'warning');
        return;
    }

    const data = {
        nombre: nombre,
        rif: document.getElementById('nuevoProveedorRif').value.trim(),
        telefono: document.getElementById('nuevoProveedorTelefono').value.trim()
    };

    try {
        const response = await fetch('php/proveedores_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast('Proveedor creado correctamente', 'success');
            cerrarModalNuevoProveedor();

            // Auto-select the new proveedor
            seleccionarProveedor(result.id, nombre);
        } else {
            showToast('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al crear proveedor', 'danger');
    }
}
