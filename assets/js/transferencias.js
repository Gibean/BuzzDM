/**
 * Transferencias - JavaScript
 * Gestión de transferencias entre almacenes
 */

let almacenes = [];
let paginaActual = 1;
const porPagina = 10;
let totalTransferencias = 0;
let transferenciaActualId = null;
let lineaIndex = 0;

// =====================================================
// INICIALIZACIÓN
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    inicializarFecha();
    cargarAlmacenes();
    cargarTransferencias();
    configurarEventos();
});

function inicializarFecha() {
    document.getElementById('transferenciaFecha').value = new Date().toISOString().split('T')[0];
}

function configurarEventos() {
    // Búsqueda con debounce
    const searchInput = document.getElementById('searchTransferencias');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => cargarTransferencias(), 300));
    }

    // Cerrar autocomplete al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-results') && !e.target.closest('.sku-input')) {
            cerrarTodosAutocomplete();
        }
    });
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function cerrarTodosAutocomplete() {
    document.querySelectorAll('.autocomplete-results').forEach(el => {
        el.classList.remove('show');
        el.innerHTML = '';
    });
}

// =====================================================
// NAVEGACIÓN
// =====================================================

function verHistorial() {
    document.getElementById('vistaHistorial').style.display = 'block';
    document.getElementById('vistaFormulario').style.display = 'none';
    transferenciaActualId = null;
    cargarTransferencias();
}
window.verHistorial = verHistorial;

function nuevaTransferencia() {
    transferenciaActualId = null;
    limpiarFormulario();
    document.getElementById('vistaHistorial').style.display = 'none';
    document.getElementById('vistaFormulario').style.display = 'block';
    document.getElementById('btnBorrador').style.display = 'inline-flex';
    document.getElementById('btnEnviar').style.display = 'inline-flex';
    agregarLineaProducto();
}
window.nuevaTransferencia = nuevaTransferencia;

function cancelarFormulario() {
    showConfirmation(
        'Cancelar',
        '¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.',
        verHistorial
    );
}
window.cancelarFormulario = cancelarFormulario;

function limpiarFormulario() {
    document.getElementById('formTransferencia').reset();
    document.getElementById('transferenciaId').value = '';
    document.getElementById('transferenciaCodigo').value = '';
    document.getElementById('lineasProductosContainer').innerHTML = '';
    document.getElementById('sinProductos').style.display = 'block';
    lineaIndex = 0;
    actualizarTotales();
    inicializarFecha();
}

// =====================================================
// CARGAR DATOS
// =====================================================

async function cargarAlmacenes() {
    try {
        const response = await fetch('php/api_warehouses.php');
        const data = await response.json();

        if (data.success) {
            // Support both 'warehouses' and 'almacenes' keys
            almacenes = data.warehouses || data.almacenes || [];
            const selectOrigen = document.getElementById('almacenOrigen');
            const selectDestino = document.getElementById('almacenDestino');

            const options = almacenes.map(a => `<option value="${a.id}">${escapeHtml(a.codigo)} - ${escapeHtml(a.nombre)}</option>`).join('');

            selectOrigen.innerHTML = '<option value="">Seleccionar origen...</option>' + options;
            selectDestino.innerHTML = '<option value="">Seleccionar destino...</option>' + options;
        } else {
            console.error('Error cargando almacenes:', data.message);
        }
    } catch (error) {
        console.error('Error cargando almacenes:', error);
    }
}

async function cargarTransferencias() {
    const params = new URLSearchParams({
        page: paginaActual,
        limit: porPagina,
        search: document.getElementById('searchTransferencias')?.value || '',
        estado: document.getElementById('filterEstado')?.value || ''
    });

    try {
        const response = await fetch(`php/api_transferencias.php?${params.toString()}`);
        const data = await response.json();

        if (data.success) {
            renderizarTabla(data.transferencias);
            totalTransferencias = data.total;
            actualizarPaginacion();
        } else {
            showToast('Error al cargar transferencias: ' + data.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'danger');
    }
}
window.cargarTransferencias = cargarTransferencias;

function renderizarTabla(transferencias) {
    const tbody = document.getElementById('tablaTransferenciasBody');

    if (!transferencias || transferencias.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-exchange-alt fa-2x mb-2"></i>
                    <p>No hay transferencias registradas</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = transferencias.map(t => `
        <tr class="row-${t.estado}">
            <td><span class="font-mono text-primary">${t.codigo}</span></td>
            <td>${formatearFecha(t.fecha_solicitud)}</td>
            <td>
                <span class="text-danger">${escapeHtml(t.almacen_origen_nombre)}</span>
                <i class="fas fa-arrow-right mx-2 text-muted"></i>
                <span class="text-success">${escapeHtml(t.almacen_destino_nombre)}</span>
            </td>
            <td class="text-center">${t.total_productos || 0}</td>
            <td class="text-center">${t.total_unidades || 0}</td>
            <td>
                <span class="badge badge-${t.estado}">${getEstadoTexto(t.estado)}</span>
            </td>
            <td>
                <div class="acciones-cargo">
                    <button class="btn-accion-cargo" onclick="verDetalle(${t.id})" title="Ver detalle">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${t.estado === 'borrador' ? `
                        <button class="btn-accion-cargo" onclick="editarTransferencia(${t.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-accion-cargo btn-confirmar" onclick="enviarDesdeHistorial(${t.id})" title="Enviar">
                            <i class="fas fa-truck"></i>
                        </button>
                    ` : ''}
                    ${t.estado === 'en_transito' ? `
                        <button class="btn-accion-cargo btn-success" onclick="recibirDesdeHistorial(${t.id})" title="Marcar recibida">
                            <i class="fas fa-check"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function getEstadoTexto(estado) {
    const estados = {
        'borrador': 'Borrador',
        'en_transito': 'En Tránsito',
        'recibido': 'Recibido',
        'cancelado': 'Cancelado'
    };
    return estados[estado] || estado;
}

function formatearFecha(fecha) {
    if (!fecha) return '-';
    const d = new Date(fecha + 'T00:00:00');
    return d.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function actualizarPaginacion() {
    const totalPaginas = Math.ceil(totalTransferencias / porPagina);
    const desde = totalTransferencias === 0 ? 0 : (paginaActual - 1) * porPagina + 1;
    const hasta = Math.min(paginaActual * porPagina, totalTransferencias);

    document.getElementById('showingFrom').textContent = desde;
    document.getElementById('showingTo').textContent = hasta;
    document.getElementById('totalTransferencias').textContent = totalTransferencias;

    const paginacion = document.getElementById('paginationTransferencias');
    if (totalPaginas <= 1) {
        paginacion.innerHTML = '';
        return;
    }

    let html = `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual - 1})">&laquo;</a>
    </li>`;

    for (let i = 1; i <= totalPaginas; i++) {
        if (i === 1 || i === totalPaginas || (i >= paginaActual - 2 && i <= paginaActual + 2)) {
            html += `<li class="page-item ${i === paginaActual ? 'active' : ''}">
                <a class="page-link" href="#" onclick="cambiarPagina(${i})">${i}</a>
            </li>`;
        } else if (i === paginaActual - 3 || i === paginaActual + 3) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" href="#" onclick="cambiarPagina(${paginaActual + 1})">&raquo;</a>
    </li>`;

    paginacion.innerHTML = html;
}

function cambiarPagina(pagina) {
    const totalPaginas = Math.ceil(totalTransferencias / porPagina);
    if (pagina < 1 || pagina > totalPaginas) return;
    paginaActual = pagina;
    cargarTransferencias();
}
window.cambiarPagina = cambiarPagina;

function limpiarFiltros() {
    document.getElementById('searchTransferencias').value = '';
    document.getElementById('filterEstado').value = '';
    paginaActual = 1;
    cargarTransferencias();
}
window.limpiarFiltros = limpiarFiltros;

// =====================================================
// LÍNEAS DE PRODUCTOS
// =====================================================

function agregarLineaProducto(producto = null) {
    const container = document.getElementById('lineasProductosContainer');
    const lineaId = `linea-${lineaIndex++}`;

    document.getElementById('sinProductos').style.display = 'none';

    const html = `
        <div class="cargo-linea-row" id="${lineaId}" data-producto-id="${producto?.id || ''}">
            <div class="linea-col col-sku">
                <div class="sku-container">
                    <input type="text"
                           class="form-control form-control-buzz sku-input"
                           placeholder="Buscar SKU..."
                           value="${escapeHtml(producto?.sku || '')}"
                           ${producto ? 'readonly' : ''}
                           autocomplete="off">
                    <div class="autocomplete-results"></div>
                </div>
            </div>
            <div class="linea-col col-nombre">
                <span class="nombre-display ${producto ? '' : 'placeholder'}">
                    ${producto?.nombre ? escapeHtml(producto.nombre) : 'Selecciona un producto...'}
                </span>
            </div>
            <div class="linea-col col-stock">
                <span class="stock-origen">${producto?.stock || '-'}</span>
            </div>
            <div class="linea-col col-cantidad">
                <input type="number"
                       class="form-control form-control-buzz cantidad-input"
                       min="1"
                       value="${producto?.cantidad || 1}">
            </div>
            <div class="linea-col col-acciones">
                <button type="button" class="btn-eliminar-linea" onclick="eliminarLinea('${lineaId}')" title="Eliminar">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);

    const linea = document.getElementById(lineaId);
    const skuInput = linea.querySelector('.sku-input');
    const resultsContainer = linea.querySelector('.autocomplete-results');
    const cantidadInput = linea.querySelector('.cantidad-input');

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

    cantidadInput.addEventListener('input', () => actualizarTotales());

    if (!producto) {
        skuInput.focus();
    }
}
window.agregarLineaProducto = agregarLineaProducto;

function eliminarLinea(lineaId) {
    const linea = document.getElementById(lineaId);
    if (linea) {
        linea.remove();
        actualizarTotales();

        const container = document.getElementById('lineasProductosContainer');
        if (container.children.length === 0) {
            document.getElementById('sinProductos').style.display = 'block';
        }
    }
}
window.eliminarLinea = eliminarLinea;

// =====================================================
// BÚSQUEDA DE PRODUCTOS
// =====================================================

async function buscarProductos(query, resultsContainer, lineaId) {
    if (query.length < 2) {
        resultsContainer.classList.remove('show');
        resultsContainer.innerHTML = '';
        return;
    }

    const almacenOrigenId = document.getElementById('almacenOrigen').value;

    // Require almacen origen selection first
    if (!almacenOrigenId) {
        resultsContainer.innerHTML = '<div class="autocomplete-empty">Primero selecciona el almacén origen</div>';
        resultsContainer.classList.add('show');
        return;
    }

    const productosEnTransferencia = obtenerProductosEnTransferencia(lineaId);

    try {
        // Search products with stock in the origin warehouse
        const response = await fetch(`php/api_transferencias.php?action=buscar_productos&search=${encodeURIComponent(query)}&almacen_id=${almacenOrigenId}&limit=10`);
        const data = await response.json();

        const productsList = data.productos || [];

        if (data.success && productsList.length > 0) {
            // Filter out products already in transfer and those with 0 stock
            const productosFiltrados = productsList.filter(p =>
                !productosEnTransferencia.includes(p.id.toString()) && p.stock_disponible > 0
            );

            if (productosFiltrados.length > 0) {
                resultsContainer.innerHTML = productosFiltrados.map(p => `
                    <div class="autocomplete-item"
                         data-id="${p.id}"
                         data-sku="${escapeHtml(p.sku)}"
                         data-nombre="${escapeHtml(p.nombre)}"
                         data-stock="${p.stock_disponible}"
                         onclick="seleccionarProducto('${lineaId}', this)">
                        <span class="ac-sku">${escapeHtml(p.sku)}</span>
                        <span class="ac-nombre">${escapeHtml(p.nombre)}</span>
                        <span class="ac-stock">Disp: ${p.stock_disponible}</span>
                    </div>
                `).join('');
            } else {
                resultsContainer.innerHTML = '<div class="autocomplete-empty">No hay productos disponibles con stock</div>';
            }
            resultsContainer.classList.add('show');
        } else {
            resultsContainer.innerHTML = '<div class="autocomplete-empty">No se encontraron productos con stock</div>';
            resultsContainer.classList.add('show');
        }
    } catch (error) {
        console.error('Error buscando productos:', error);
        resultsContainer.innerHTML = '<div class="autocomplete-empty">Error de conexión</div>';
        resultsContainer.classList.add('show');
    }
}

function obtenerProductosEnTransferencia(excludeLineaId = null) {
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
    const stock = elemento.dataset.stock;

    linea.dataset.productoId = id;
    linea.querySelector('.sku-input').value = sku;
    linea.querySelector('.sku-input').readOnly = true;
    linea.querySelector('.nombre-display').textContent = nombre;
    linea.querySelector('.nombre-display').classList.remove('placeholder');
    linea.querySelector('.stock-origen').textContent = stock;
    linea.querySelector('.autocomplete-results').classList.remove('show');

    actualizarTotales();
    linea.querySelector('.cantidad-input').focus();
    linea.querySelector('.cantidad-input').select();
}
window.seleccionarProducto = seleccionarProducto;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// =====================================================
// VALIDACIONES
// =====================================================

function validarAlmacenes() {
    const origen = document.getElementById('almacenOrigen').value;
    const destino = document.getElementById('almacenDestino').value;

    if (origen && destino && origen === destino) {
        showToast('El almacén origen y destino deben ser diferentes', 'warning');
        document.getElementById('almacenDestino').value = '';
    }
}
window.validarAlmacenes = validarAlmacenes;

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

function obtenerLineasValidas() {
    const lineas = [];
    document.querySelectorAll('.cargo-linea-row').forEach(linea => {
        const productoId = linea.dataset.productoId;
        const cantidad = parseInt(linea.querySelector('.cantidad-input').value) || 0;

        if (productoId && cantidad > 0) {
            lineas.push({
                producto_id: productoId,
                cantidad: cantidad
            });
        }
    });
    return lineas;
}

// =====================================================
// GUARDAR Y ENVIAR
// =====================================================

async function guardarBorrador() {
    await guardarTransferencia(false);
}
window.guardarBorrador = guardarBorrador;

async function enviarTransferencia() {
    const lineas = obtenerLineasValidas();
    if (lineas.length === 0) {
        showToast('Debes agregar al menos un producto', 'warning');
        return;
    }

    showConfirmation(
        'Enviar Transferencia',
        '¿Estás seguro de enviar esta transferencia? El stock se descontará del almacén origen.',
        async function() {
            await guardarTransferencia(true);
        }
    );
}
window.enviarTransferencia = enviarTransferencia;

async function guardarTransferencia(enviar) {
    const almacenOrigen = document.getElementById('almacenOrigen').value;
    const almacenDestino = document.getElementById('almacenDestino').value;
    const fecha = document.getElementById('transferenciaFecha').value;

    if (!almacenOrigen || !almacenDestino) {
        showToast('Debes seleccionar almacén origen y destino', 'warning');
        return;
    }

    if (almacenOrigen === almacenDestino) {
        showToast('El almacén origen y destino deben ser diferentes', 'warning');
        return;
    }

    if (!fecha) {
        showToast('La fecha es requerida', 'warning');
        return;
    }

    const lineas = obtenerLineasValidas();

    if (enviar && lineas.length === 0) {
        showToast('Debes agregar al menos un producto para enviar', 'warning');
        return;
    }

    const data = {
        id: document.getElementById('transferenciaId').value || null,
        almacen_origen_id: almacenOrigen,
        almacen_destino_id: almacenDestino,
        fecha_solicitud: fecha,
        motivo: document.getElementById('transferenciaMotivo').value,
        referencia_externa: document.getElementById('transferenciaReferencia').value,
        notas: document.getElementById('transferenciaNotas').value,
        lineas: lineas
    };

    try {
        // First save or update
        const url = data.id ? `php/api_transferencias.php?id=${data.id}` : 'php/api_transferencias.php';
        const method = data.id ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            const transferenciaId = result.id || data.id;

            // If enviar, call the action
            if (enviar && transferenciaId) {
                const enviarResponse = await fetch(`php/api_transferencias.php?action=enviar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: transferenciaId })
                });
                const enviarResult = await enviarResponse.json();

                if (enviarResult.success) {
                    showToast('Transferencia enviada correctamente', 'success');
                } else {
                    showToast('Error al enviar: ' + enviarResult.message, 'danger');
                    return;
                }
            } else {
                showToast('Transferencia guardada como borrador', 'success');
            }

            verHistorial();
        } else {
            showToast('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error al guardar la transferencia', 'danger');
    }
}

// =====================================================
// EDITAR
// =====================================================

async function editarTransferencia(id) {
    try {
        const response = await fetch(`php/api_transferencias.php?id=${id}`);
        const data = await response.json();

        if (data.success && data.transferencia) {
            cargarFormularioDesdeData(data.transferencia);
        } else {
            showToast('Error al cargar la transferencia', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'danger');
    }
}
window.editarTransferencia = editarTransferencia;

function cargarFormularioDesdeData(transferencia) {
    limpiarFormulario();

    transferenciaActualId = transferencia.id;
    document.getElementById('transferenciaId').value = transferencia.id;
    document.getElementById('transferenciaCodigo').value = transferencia.codigo;
    document.getElementById('transferenciaFecha').value = transferencia.fecha_solicitud;
    document.getElementById('almacenOrigen').value = transferencia.almacen_origen_id;
    document.getElementById('almacenDestino').value = transferencia.almacen_destino_id;
    document.getElementById('transferenciaMotivo').value = transferencia.motivo || '';
    document.getElementById('transferenciaReferencia').value = transferencia.referencia_externa || '';
    document.getElementById('transferenciaNotas').value = transferencia.notas || '';

    document.getElementById('sinProductos').style.display = 'none';
    if (transferencia.detalles && transferencia.detalles.length > 0) {
        transferencia.detalles.forEach(detalle => {
            agregarLineaProducto({
                id: detalle.producto_id,
                sku: detalle.producto_sku,
                nombre: detalle.producto_nombre,
                cantidad: detalle.cantidad_solicitada
            });
        });
    }

    document.getElementById('vistaHistorial').style.display = 'none';
    document.getElementById('vistaFormulario').style.display = 'block';
}

// =====================================================
// VER DETALLE
// =====================================================

async function verDetalle(id) {
    try {
        const response = await fetch(`php/api_transferencias.php?id=${id}`);
        const data = await response.json();

        if (data.success && data.transferencia) {
            mostrarModalDetalle(data.transferencia);
        } else {
            showToast('Error al cargar la transferencia', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
    }
}
window.verDetalle = verDetalle;

function mostrarModalDetalle(t) {
    const contenido = document.getElementById('modalDetalleContenido');

    let html = `
        <div class="detalle-cargo-info">
            <div class="info-item">
                <span class="info-label">Código</span>
                <span class="info-value font-mono text-primary">${t.codigo}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha Solicitud</span>
                <span class="info-value">${formatearFecha(t.fecha_solicitud)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado</span>
                <span class="badge badge-${t.estado}">${getEstadoTexto(t.estado)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Origen</span>
                <span class="info-value text-danger">${escapeHtml(t.almacen_origen_nombre)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Destino</span>
                <span class="info-value text-success">${escapeHtml(t.almacen_destino_nombre)}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Solicitado por</span>
                <span class="info-value">${escapeHtml(t.solicitado_por_nombre || '-')}</span>
            </div>
        </div>

        ${t.motivo ? `<div class="mb-3"><strong>Motivo:</strong> ${escapeHtml(t.motivo)}</div>` : ''}

        <div class="detalle-productos-tabla">
            <h6 class="text-muted mb-3">Productos</h6>
            <table class="table table-buzz">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Producto</th>
                        <th class="text-end">Solicitado</th>
                        <th class="text-end">Enviado</th>
                        <th class="text-end">Recibido</th>
                    </tr>
                </thead>
                <tbody>
                    ${t.detalles.map(d => `
                        <tr>
                            <td class="font-mono">${d.producto_sku}</td>
                            <td>${escapeHtml(d.producto_nombre)}</td>
                            <td class="text-end">${d.cantidad_solicitada}</td>
                            <td class="text-end">${d.cantidad_enviada || '-'}</td>
                            <td class="text-end">${d.cantidad_recibida || '-'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    `;

    contenido.innerHTML = html;

    // Show/hide action buttons based on state
    const btnCancelar = document.getElementById('btnCancelarTransferencia');
    const btnRecibir = document.getElementById('btnRecibirTransferencia');

    btnCancelar.classList.toggle('d-none', t.estado === 'recibido' || t.estado === 'cancelado');
    btnRecibir.classList.toggle('d-none', t.estado !== 'en_transito');

    btnCancelar.dataset.id = t.id;
    btnRecibir.dataset.id = t.id;

    document.getElementById('modalDetalle').style.display = 'block';
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').style.display = 'none';
}
window.cerrarModalDetalle = cerrarModalDetalle;

// =====================================================
// ACCIONES DESDE HISTORIAL
// =====================================================

function enviarDesdeHistorial(id) {
    showConfirmation(
        'Enviar Transferencia',
        '¿Estás seguro de enviar esta transferencia? El stock se descontará del almacén origen.',
        async function() {
            try {
                const response = await fetch(`php/api_transferencias.php?action=enviar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Transferencia enviada', 'success');
                    cargarTransferencias();
                } else {
                    showToast('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'danger');
            }
        }
    );
}
window.enviarDesdeHistorial = enviarDesdeHistorial;

function recibirDesdeHistorial(id) {
    showConfirmation(
        'Recibir Transferencia',
        '¿Confirmas que la mercancía fue recibida? El stock se agregará al almacén destino.',
        async function() {
            await ejecutarRecibir(id);
        }
    );
}
window.recibirDesdeHistorial = recibirDesdeHistorial;

function recibirTransferencia() {
    const id = document.getElementById('btnRecibirTransferencia').dataset.id;
    recibirDesdeHistorial(id);
}
window.recibirTransferencia = recibirTransferencia;

async function ejecutarRecibir(id) {
    try {
        const response = await fetch(`php/api_transferencias.php?action=recibir`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Transferencia recibida', 'success');
            cerrarModalDetalle();
            cargarTransferencias();
        } else {
            showToast('Error: ' + result.message, 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error de conexión', 'danger');
    }
}

function cancelarTransferencia() {
    const id = document.getElementById('btnCancelarTransferencia').dataset.id;

    showConfirmation(
        'Cancelar Transferencia',
        '¿Estás seguro de cancelar esta transferencia? Si ya fue enviada, el stock regresará al almacén origen.',
        async function() {
            try {
                const response = await fetch(`php/api_transferencias.php?action=cancelar`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: id })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Transferencia cancelada', 'success');
                    cerrarModalDetalle();
                    cargarTransferencias();
                } else {
                    showToast('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    );
}
window.cancelarTransferencia = cancelarTransferencia;
