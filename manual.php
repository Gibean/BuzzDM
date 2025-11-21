<?php
require_once 'php/sesion.php';
require_once 'php/sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Usuario - Buzz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    .accordion-button:not(.collapsed) {
        color: var(--bs-white);
        background-color: var(--bs-primary);
    }

    .accordion-button:focus {
        box-shadow: none;
    }

    .list-group-item {
        border: none;
    }
    </style>
</head>

<body>
    <main class="main-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title"><i class="bi bi-book-half me-2"></i>Manual de usuario</h1>
                </div>
                <div class="card-body">
                    <p class="lead">Bienvenido al manual de usuario de Buzz. Aquí encontrará una guía detallada sobre
                        cómo utilizar todas las características y funciones del sistema.</p>
                    <p>Haga clic en las diferentes secciones a continuación para expandir y ver la información de cada
                        módulo.</p>

                    <div class="accordion" id="manualAccordion">

                        <!-- Sección Dashboard -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                                data-bs-parent="#manualAccordion">
                                <div class="accordion-body">
                                    <p>El <strong>Dashboard</strong> es la pantalla principal al iniciar sesión. Ofrece
                                        una vista general del estado de su negocio a través de varias tarjetas y
                                        gráficos.</p>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i
                                                class="bi bi-graph-up-arrow me-2"></i><strong>Gráfico de
                                                Ventas:</strong> Muestra las tendencias de ventas a lo largo del tiempo.
                                        </li>
                                        <li class="list-group-item"><i
                                                class="bi bi-pie-chart-fill me-2"></i><strong>Ventas por
                                                Estado:</strong> Un gráfico circular que desglosa los pedidos según su
                                            estado actual (ej. Pendiente, Completado, Cancelado).</li>
                                        <li class="list-group-item"><i
                                                class="bi bi-bar-chart-line-fill me-2"></i><strong>Estadísticas
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
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>