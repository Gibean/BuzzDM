<?php
require_once 'php/sesion.php';
require_once 'php/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - BUZZ Data Manager</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    .manual-header {
        background: linear-gradient(135deg, var(--buzz-dark-bg) 0%, var(--buzz-card-bg) 100%);
        border-bottom: 2px solid var(--buzz-primary-color);
        padding: 2rem;
        border-radius: 8px 8px 0 0;
    }

    .search-box {
        position: relative;
        margin-bottom: 2rem;
    }

    .search-box input {
        padding-left: 3rem;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--buzz-text-muted);
        pointer-events: none;
    }

    .accordion-item {
        background-color: var(--buzz-card-bg);
        border: 1px solid var(--buzz-border-color);
        margin-bottom: 0.5rem;
        border-radius: 4px !important;
        overflow: hidden;
    }

    .accordion-button {
        background-color: var(--buzz-card-bg);
        color: var(--buzz-text-color);
        font-family: var(--font-primary);
        font-weight: 600;
        border: none;
        padding: 1.25rem 1.5rem;
        transition: all 0.3s ease;
    }

    .accordion-button:not(.collapsed) {
        background-color: var(--buzz-primary-color);
        color: var(--buzz-dark-bg);
        box-shadow: none;
    }

    .accordion-button:hover {
        background-color: var(--buzz-primary-color);
        color: var(--buzz-dark-bg);
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 2px rgba(255, 160, 0, 0.2);
        border-color: var(--buzz-primary-color);
    }

    .accordion-button::after {
        filter: brightness(0) invert(1);
    }

    .accordion-button:not(.collapsed)::after {
        filter: brightness(0);
    }

    .accordion-body {
        background-color: var(--buzz-card-bg);
        color: var(--buzz-text-color);
        padding: 1.5rem;
        border-top: 1px solid var(--buzz-border-color);
    }

    .manual-list-item {
        background-color: transparent;
        border: none;
        border-left: 3px solid var(--buzz-primary-color);
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        color: var(--buzz-text-color);
        font-family: var(--font-monospace);
        transition: all 0.3s ease;
    }

    .manual-list-item:hover {
        background-color: rgba(255, 160, 0, 0.1);
        border-left-color: var(--buzz-primary-color);
        transform: translateX(5px);
    }

    .manual-list-item i {
        color: var(--buzz-primary-color);
        margin-right: 0.75rem;
        width: 20px;
    }

    .no-results {
        text-align: center;
        padding: 3rem;
        color: var(--buzz-text-muted);
    }

    .no-results i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    </style>
</head>

<body>
    <main class="main-content">
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">MANUAL DE USUARIO</h1>
                <span class="text-muted ms-2 font-mono">/ GUÍA INTERACTIVA</span>
            </div>
        </nav>

        <div class="buzz-card">
            <div class="manual-header">
                <h2 class="font-primary text-primary mb-3">
                    <i class="fas fa-book-open me-2"></i>Bienvenido al Manual de BUZZ Data Manager
                </h2>
                <p class="font-mono text-muted mb-0">
                    Guía completa para utilizar todas las funcionalidades del sistema de gestión
                </p>
            </div>

            <div class="p-4">
                <!-- Search Box -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" class="form-control form-control-buzz" id="manualSearch"
                           placeholder="Buscar en el manual... (ej: productos, clientes, importar)">
                </div>

                <!-- Accordion Container -->
                <div class="accordion" id="manualAccordion">

                        <!-- Sección Dashboard -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>El <strong>Dashboard</strong> es la pantalla principal al iniciar sesión. Ofrece
                                        una vista general del estado de su negocio a través de varias tarjetas y
                                        gráficos.</p>
                                    <ul class="list-unstyled">
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-line me-2"></i><strong>Gráfico de
                                                Ventas:</strong> Muestra las tendencias de ventas a lo largo del tiempo.
                                        </li>
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-pie me-2"></i><strong>Ventas por
                                                Estado:</strong> Un gráfico circular que desglosa los pedidos según su
                                            estado actual (ej. Pendiente, Completado, Cancelado).</li>
                                        <li class="manual-list-item"><i
                                                class="fas fa-chart-bar me-2"></i><strong>Estadísticas
                                                Clave:</strong> Tarjetas con métricas importantes como total de ventas,
                                            número de clientes y cantidad de pedidos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Pedidos -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-box-seam me-2"></i>Gestión de Pedidos
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>La sección de <strong>Pedidos</strong> le permite administrar todas las órdenes
                                        de sus clientes.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-plus-circle-fill me-2"></i><strong>Crear Pedido:</strong>
                                            Puede registrar nuevos pedidos asociándolos a un cliente y agregando
                                            productos del inventario.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-pencil-square me-2"></i><strong>Editar Pedido:</strong>
                                            Modificar la información de un pedido existente, como su estado (pendiente,
                                            en proceso, enviado, etc.).</li>
                                        <li class="list-group-item"><i class="bi bi-search me-2"></i><strong>Buscar y
                                                Filtrar:</strong> Localice pedidos rápidamente usando la barra de
                                            búsqueda o los filtros disponibles.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-file-earmark-pdf-fill me-2"></i><strong>Generar
                                                Reportes:</strong> Es posible que desde aquí o la sección de reportes
                                            pueda generar un PDF con los detalles del pedido.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Clientes -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="bi bi-people-fill me-2"></i>Gestión de Clientes
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Administre la información de todos sus clientes de forma centralizada.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-person-plus-fill me-2"></i><strong>Añadir Cliente:</strong>
                                            Registre nuevos clientes con su información de contacto y fiscal.</li>
                                        <li class="list-group-item"><i class="bi bi-pencil-fill me-2"></i><strong>Editar
                                                Cliente:</strong> Actualice los datos de cualquier cliente registrado.
                                        </li>
                                        <li class="list-group-item"><i
                                                class="bi bi-trash-fill me-2"></i><strong>Eliminar Cliente:</strong>
                                            Borre clientes que ya no estén activos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Inventario -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="bi bi-bookshelf me-2"></i>Inventario
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Controle todos los productos de su negocio.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-plus-square-fill me-2"></i><strong>Añadir
                                                Producto:</strong> Ingrese nuevos productos al sistema con detalles como
                                            SKU, nombre, descripción y stock inicial.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-pencil-square me-2"></i><strong>Editar Producto:</strong>
                                            Modifique la información y el stock de los productos existentes.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Importación -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Importación de Datos
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Esta potente herramienta le permite cargar datos de forma masiva utilizando
                                        archivos de Excel.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i class="bi bi-download me-2"></i><strong>Descargar
                                                Plantilla:</strong> Obtenga el archivo <code>template_buzz.xlsx</code>
                                            que sirve como guía para la importación.</li>
                                        <li class="list-group-item"><i class="bi bi-upload me-2"></i><strong>Procesar
                                                Importación:</strong> Suba el archivo Excel con sus datos para cargarlos
                                            automáticamente en el sistema (ej. nuevos productos, clientes o pedidos).
                                        </li>
                                        <li class="list-group-item"><i
                                                class="bi bi-clock-history me-2"></i><strong>Historial de
                                                Importaciones:</strong> Revise las importaciones que se han realizado
                                            anteriormente.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Sección Administración -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    <i class="bi bi-person-badge-fill me-2"></i>Administración
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>Sección dedicada a la configuración y gestión de alto nivel del sistema.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-person-fill-gear me-2"></i><strong>Gestión de
                                                Usuarios:</strong> Permite crear, editar y eliminar los usuarios que
                                            tienen acceso al sistema.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-building-fill-gear me-2"></i><strong>Configuración de la
                                                Empresa:</strong> Actualice los datos de su empresa, como el nombre, RIF
                                            y el logo que aparece en los reportes.</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-shield-lock-fill me-2"></i><strong>Backups:</strong> Genere
                                            y descargue copias de seguridad de la base de datos.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- No Results Message -->
                <div id="noResults" class="no-results" style="display: none;">
                    <i class="fas fa-search"></i>
                    <p class="font-mono">No se encontraron resultados para su búsqueda</p>
                    <small class="text-muted">Intente con otros términos de búsqueda</small>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script>
        // Search functionality for manual
        const searchInput = document.getElementById('manualSearch');
        const accordionItems = document.querySelectorAll('.accordion-item');
        const noResults = document.getElementById('noResults');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;

            accordionItems.forEach(item => {
                const button = item.querySelector('.accordion-button');
                const body = item.querySelector('.accordion-body');
                const buttonText = button.textContent.toLowerCase();
                const bodyText = body.textContent.toLowerCase();

                // Check if search term is in title or content
                if (searchTerm === '' || buttonText.includes(searchTerm) || bodyText.includes(searchTerm)) {
                    item.style.display = '';
                    visibleCount++;

                    // If searching and found, expand the section
                    if (searchTerm !== '' && (buttonText.includes(searchTerm) || bodyText.includes(searchTerm))) {
                        const collapse = item.querySelector('.accordion-collapse');
                        if (!collapse.classList.contains('show')) {
                            button.click();
                        }
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (visibleCount === 0 && searchTerm !== '') {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });

        // Smooth scroll for better UX
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 350);
            });
        });
    </script>
</body>

</html>
