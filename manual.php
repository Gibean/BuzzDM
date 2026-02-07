<?php
require_once 'php/sesion.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayuda - BUZZ Data Manager</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Driver.js CSS (Local) -->
    <link rel="stylesheet" href="assets/css/driver.css"/>
    <!-- Main Styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Documentation Styles -->
    <link rel="stylesheet" href="assets/css/manual.css">
</head>

<body>
    <main class="docs-layout">

        <!-- Mobile Sidebar Toggle -->
        <button class="docs-sidebar-toggle" id="docsSidebarToggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Mobile Overlay -->
        <div class="docs-overlay" id="docsOverlay"></div>

        <!-- HEADER - Fixed with Search -->
        <header class="docs-header">
            <a href="dashboard.php" class="docs-header-brand">
                <img src="assets/img/logov1_icon.png" alt="BuzzDM">
                <span>Centro de Ayuda</span>
            </a>

            <div class="docs-header-search">
                <input type="text" id="docsGlobalSearch" placeholder="Buscar en la documentación...">
            </div>

            <div class="docs-header-actions">
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-sun"></i>
                    <span>Claro</span>
                </button>
            </div>
        </header>

        <!-- Breadcrumb -->
        <nav class="docs-breadcrumb" id="docsBreadcrumb">
            <a href="#" onclick="showSection('home'); return false;">Centro de Ayuda</a>
        </nav>

        <!-- Main Area: Sidebar + Content -->
        <div class="docs-main-area">

            <!-- Left Sidebar: Module Navigation -->
            <aside class="docs-sidebar" id="docsSidebar">
                <div class="docs-sidebar-header">
                    <a href="dashboard.php" class="docs-back-btn-main">
                        <i class="fas fa-arrow-left"></i> Volver a BuzzDM
                    </a>
                </div>

            <!-- Navigation - Step by Step -->
            <nav class="docs-nav docs-nav-steps">
                <ol class="docs-steps-list">
                    <li><a class="docs-step-item active" data-section="home" onclick="showSection('home')">Vista General</a></li>
                    <li><a class="docs-step-item" data-section="dashboard" onclick="showSection('dashboard')">Panel de Control</a></li>

                    <!-- PRODUCTOS -->
                    <li><a class="docs-step-item" data-section="productos" onclick="showSection('productos')">Inventario</a></li>
                    <li><a class="docs-step-item" data-section="catalogos" onclick="showSection('catalogos')">Catálogos</a></li>
                    <li><a class="docs-step-item" data-section="almacenes" onclick="showSection('almacenes')">Almacenes</a></li>
                    <li><a class="docs-step-item" data-section="proveedores" onclick="showSection('proveedores')">Proveedores</a></li>

                    <!-- OPERACIONES -->
                    <li><a class="docs-step-item" data-section="clientes" onclick="showSection('clientes')">Clientes</a></li>
                    <li><a class="docs-step-item" data-section="pedidos" onclick="showSection('pedidos')">Pedidos</a></li>
                    <li><a class="docs-step-item" data-section="requisiciones" onclick="showSection('requisiciones')">Requisiciones</a></li>
                    <li><a class="docs-step-item" data-section="transferencias" onclick="showSection('transferencias')">Transferencias</a></li>
                    <li><a class="docs-step-item" data-section="envios" onclick="showSection('envios')">Envíos</a></li>
                    <li><a class="docs-step-item" data-section="cargos" onclick="showSection('cargos')">Cargar Existencias</a></li>

                    <!-- HERRAMIENTAS -->
                    <li><a class="docs-step-item" data-section="notas" onclick="showSection('notas')">Notas Kanban</a></li>
                    <li><a class="docs-step-item" data-section="importar" onclick="showSection('importar')">Importación</a></li>

                    <!-- SISTEMA -->
                    <li><a class="docs-step-item" data-section="empresa" onclick="showSection('empresa')">Configuración</a></li>
                    <li><a class="docs-step-item" data-section="usuarios" onclick="showSection('usuarios')">Usuarios</a></li>
                    <li><a class="docs-step-item" data-section="backups" onclick="showSection('backups')">Respaldos</a></li>
                </ol>
            </nav>
        </aside>

        <!-- Center: Content Area -->
        <section class="docs-content">
            <div class="docs-content-inner">

                <!-- MASTER VIEW -->
                <div id="master-view">
                    <!-- Minimal Hero -->
                    <div class="docs-hero-minimal" id="docs-hero">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1>Centro de Ayuda</h1>
                                <p class="lead">
                                    Hola, <strong><?php echo $_SESSION['usuario_nombre'] ?? 'Usuario'; ?></strong>. Bienvenid@ al soporte central de BuzzDM. Aquí resolvemos tus dudas para que impulses tu negocio sin complicaciones.
                                </p>
                                <button class="start-tour-btn" id="startTourBtn">
                                    <i class="fas fa-route me-2"></i>Iniciar Tour Guiado
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Module Grid -->
                    <div class="docs-module-grid" id="moduleGrid">
                        <!-- Group: Productos -->
                        <a class="docs-block-link" onclick="showSection('productos')">
                            <div class="docs-block-icon" style="color: var(--buzz-brand);"><i class="fas fa-boxes"></i></div>
                            <div class="docs-block-title">Productos</div>
                            <div class="docs-block-desc">Inventario, Catálogos, Almacenes</div>
                        </a>

                        <!-- Group: Operaciones -->
                        <a class="docs-block-link" onclick="showSection('pedidos')">
                            <div class="docs-block-icon" style="color: var(--buzz-success);"><i class="fas fa-shopping-cart"></i></div>
                            <div class="docs-block-title">Operaciones</div>
                            <div class="docs-block-desc">Pedidos, Requisiciones, Transferencias, Envíos, Cargas</div>
                        </a>

                        <!-- Group: Herramientas -->
                         <a class="docs-block-link" onclick="showSection('notas')">
                            <div class="docs-block-icon" style="color: var(--buzz-warning);"><i class="fas fa-sticky-note"></i></div>
                            <div class="docs-block-title">Herramientas</div>
                            <div class="docs-block-desc">Notas Kanban, Importación</div>
                        </a>

                        <!-- Group: Sistema -->
                        <a class="docs-block-link" onclick="showSection('empresa')">
                            <div class="docs-block-icon" style="color: var(--buzz-dark);"><i class="fas fa-cogs"></i></div>
                            <div class="docs-block-title">Sistema</div>
                            <div class="docs-block-desc">Configuración, Usuarios, Backups</div>
                        </a>
                    </div>

                    <!-- No Results -->
                    <div class="docs-no-results" id="noResults">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h4>Sin resultados</h4>
                        <p>No se encontraron resultados para tu búsqueda.</p>
                    </div>
                </div>

                <!-- DETAIL VIEW -->
                <div id="detail-view" style="display: none;">
                    <div class="docs-article-container" id="doc-content">
                        <!-- Content injected via JS -->
                    </div>
                </div>
            </div>
        </section>

        <!-- Right: Table of Contents (Inside main-area) -->
        <aside class="docs-toc" id="docsToc">
            <div class="docs-toc-header">En esta página</div>
            <ul id="docs-toc-list"></ul>

            <div class="docs-toc-footer mt-5 pt-4 border-top">
                <p class="small text-muted mb-2">¿Necesitas más ayuda?</p>
                <a href="#" class="d-block small mb-1"><i class="fas fa-book-open me-2"></i>Docs Avanzadas</a>
                <a href="#" class="d-block small"><i class="fas fa-life-ring me-2"></i>Soporte Técnico</a>
            </div>
        </aside>

        </div><!-- /docs-main-area -->

    </main>

    <!-- TEMPLATES -->
    <div id="content-templates" style="display: none;">

        <!-- DASHBOARD -->
        <div id="tpl-dashboard">
            <div class="docs-hero-minimal">
                <h1>Panel de Control</h1>
                <p class="lead">Tu centro de operaciones. Visualiza métricas, alertas y rendimiento en tiempo real.</p>
            </div>

            <h2><i class="fas fa-chart-pie me-2"></i>Visualización de Datos</h2>
            <p>El Dashboard está dividido en tres secciones estratégicas para facilitar la toma de decisiones:</p>

            <div class="docs-alert info">
                El Dashboard se actualiza automáticamente cada vez que accedes, dándote una foto exacta de tu negocio.
            </div>

            <ol class="step-list">
                <li><span class="step-title">Resumen de Ventas</span> Visualiza gráficos de barras con las ventas de los últimos 7 días y la distribución por estados de pago.</li>
                <li><span class="step-title">Alertas Críticas</span> Revisa los cuadros de stock bajo. Los productos resaltados en rojo requieren atención inmediata.</li>
                <li><span class="step-title">Accesos Rápidos</span> Usa los botones laterales para saltar directamente a las acciones más comunes como "Nuevo Pedido" o "Ver Inventario".</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    graph TD
                    A[Dashboard] --> B[Indicadores Rápidos]
                    A --> C[Gráficos de Tendencia]
                    A --> D[Alertas de Inventario]
                    B --> B1[Ventas Hoy]
                    B --> B2[Pedidos Pendientes]
                </div>
            </div>
        </div>

        <!-- PRODUCTOS / INVENTARIO -->
        <div id="tpl-productos">
            <div class="docs-hero-minimal">
                <h1>Gestión de Inventario</h1>
                <p class="lead">Control total de productos, stock, kits y variantes. El módulo más completo del sistema.</p>
            </div>

            <!-- SECCIÓN: Interfaz Principal -->
            <h2><i class="fas fa-desktop me-2"></i>Interfaz Principal</h2>
            <p>Al acceder al módulo de Inventario encontrarás:</p>

            <div class="docs-alert info">
                <strong>Barra Superior:</strong> Botón <code>+ Nuevo Producto</code> siempre visible. Haz clic para abrir el formulario de registro.
            </div>

            <h3>Barra de Filtros</h3>
            <p>Dos niveles de filtrado para encontrar productos rápidamente:</p>

            <table class="table table-bordered">
                <thead><tr><th>Filtro</th><th>Descripción</th><th>Ubicación</th></tr></thead>
                <tbody>
                    <tr><td><strong>Búsqueda</strong></td><td>Escribe SKU, nombre o descripción. Búsqueda instantánea.</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Estado</strong></td><td>Todos / Activo / Stock Bajo / Agotado</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Marca</strong></td><td>Filtra por fabricante registrado</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Categoría</strong></td><td>Filtra por clasificación de producto</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Modelo</strong></td><td>Filtra por texto en campo modelo</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>UND</strong></td><td>Unidad de venta: UND, KG, LT, MT, M2, PACK, CAJA, PAR</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Ordenar por</strong></td><td>Nombre, SKU, Stock o Precio</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Ver eliminados</strong></td><td>Muestra productos en papelera (soft delete)</td><td>Filtros Avanzados</td></tr>
                </tbody>
            </table>

            <div class="docs-alert warning">
                <strong>Tip:</strong> El botón "Filtros Avanzados" muestra un <span class="badge bg-primary">número</span> cuando hay filtros activos. El sistema recuerda si los dejaste abiertos.
            </div>

            <!-- SECCIÓN: Tabla de Productos -->
            <h3>Tabla de Productos</h3>
            <p>Columnas mostradas por cada producto:</p>
            <ul>
                <li><strong>SKU:</strong> Código único del producto</li>
                <li><strong>Nombre:</strong> Nombre comercial</li>
                <li><strong>Marca:</strong> Fabricante (o "-" si no tiene)</li>
                <li><strong>Categoría:</strong> Clasificación (o "-" si no tiene)</li>
                <li><strong>Modelo:</strong> Variante del producto</li>
                <li><strong>UND:</strong> Unidad de venta</li>
                <li><strong>Precio:</strong> Precio de venta en $</li>
                <li><strong>Stock:</strong> Cantidad disponible</li>
                <li><strong>Estado:</strong> <span class="badge badge-success">ABASTECIDO</span> <span class="badge badge-warning">REABASTECER</span> <span class="badge badge-secondary">AGOTADO</span></li>
                <li><strong>Acciones:</strong> <i class="fas fa-edit text-primary"></i> Editar | <i class="fas fa-trash text-danger"></i> Eliminar</li>
            </ul>

            <!-- SECCIÓN: Crear Producto -->
            <h2><i class="fas fa-plus-circle me-2"></i>Crear un Nuevo Producto</h2>
            <p>El formulario está dividido en 5 secciones. A continuación cada una en detalle:</p>

            <h3>1. Información Básica</h3>
            <ol class="step-list">
                <li><span class="step-title">SKU *</span> Código único. Puede ser el del fabricante o uno interno. <strong>No se puede cambiar después.</strong></li>
                <li><span class="step-title">Nombre *</span> Nombre comercial del producto para mostrar en pedidos.</li>
                <li><span class="step-title">Descripción</span> Detalles adicionales (opcional). Aparece en búsquedas.</li>
            </ol>

            <h3>2. Tipo de Producto</h3>
            <p>Elige según la naturaleza del ítem:</p>
            <table class="table table-bordered">
                <thead><tr><th>Tipo</th><th>Icono</th><th>Maneja Stock</th><th>Uso</th></tr></thead>
                <tbody>
                    <tr><td><strong>Producto Físico</strong></td><td><i class="fas fa-box"></i></td><td><i class="fas fa-check text-success"></i> Sí</td><td>Artículos tangibles con inventario</td></tr>
                    <tr><td><strong>Servicio</strong></td><td><i class="fas fa-tools"></i></td><td><i class="fas fa-times text-danger"></i> No</td><td>Instalación, mantenimiento, consultoría</td></tr>
                    <tr><td><strong>Kit / Combo</strong></td><td><i class="fas fa-boxes"></i></td><td>Depende</td><td>Conjunto de otros productos</td></tr>
                </tbody>
            </table>

            <div class="docs-alert info">
                <strong>Variantes:</strong> Si es producto físico, puedes marcar "Es variante de otro producto" para vincular tallas/colores a un producto padre.
            </div>

            <h3>3. Componentes del Kit (solo Kits)</h3>
            <p>Si seleccionas "Kit / Combo", aparecerá esta sección:</p>
            <ol class="step-list">
                <li><span class="step-title">Tipo de Kit</span>
                    <ul>
                        <li><strong>Virtual:</strong> El stock se calcula automáticamente según componentes. Al vender, descuenta de cada componente.</li>
                        <li><strong>Pre-armado:</strong> El kit tiene su propio stock físico independiente.</li>
                    </ul>
                </li>
                <li><span class="step-title">Añadir Componentes</span> Busca productos por SKU/nombre, define cantidad por kit.</li>
                <li><span class="step-title">Stock Calculado</span> Muestra cuántos kits puedes vender basado en el componente más escaso.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    graph LR
                    P1[Jabón x3] --> K(Kit Baño)
                    P2[Toalla x1] --> K
                    P3[Shampoo x2] --> K
                    K --> S[Stock Kit = mín de componentes/cantidad]
                </div>
            </div>

            <h3>4. Precio y Stock</h3>
            <ol class="step-list">
                <li><span class="step-title">Precio Unitario *</span> Precio de venta en dólares. Se usa para calcular total de pedidos.</li>
                <li><span class="step-title">Stock Actual *</span> Cantidad disponible. Puedes iniciar en 0 y cargar después con "Cargos".</li>
                <li><span class="step-title">Stock Mínimo</span> Umbral de alerta. Cuando el stock llegue a este número, el producto aparecerá en alertas del Dashboard.</li>
                <li><span class="step-title">Estado</span> Activo (visible) o Inactivo (oculto en búsquedas de pedidos).</li>
            </ol>

            <div class="docs-alert warning">
                El stock se actualiza automáticamente cuando: <br>
                • Se crea un Cargo de existencias (suma) <br>
                • Se confirma un Pedido (resta)
            </div>

            <h3>5. Categorización</h3>
            <ol class="step-list">
                <li><span class="step-title">Marca *</span> Fabricante del producto. Obligatorio.</li>
                <li><span class="step-title">Categoría</span> Clasificación para filtros y reportes.</li>
                <li><span class="step-title">Proveedor</span> De quién compras este producto (para trazabilidad).</li>
                <li><span class="step-title">Modelo</span> Variante específica (ej: "G2 Pro", "Clásico 2024").</li>
            </ol>

            <h3>6. Atributos Físicos (Opcional)</h3>
            <p>Campos adicionales para describir el producto:</p>
            <table class="table table-bordered">
                <tr><td><strong>Color</strong></td><td>Negro, Azul, Rojo...</td></tr>
                <tr><td><strong>Tamaño/Talla</strong></td><td>S, M, L, XL, Grande, Pequeño...</td></tr>
                <tr><td><strong>Medidas</strong></td><td>21x27 cm, 50x100 mm...</td></tr>
                <tr><td><strong>Peso (Kg)</strong></td><td>Peso del producto en kilogramos</td></tr>
                <tr><td><strong>Material</strong></td><td>Plástico, Madera, Metal...</td></tr>
                <tr><td><strong>Unidad de Venta</strong></td><td>UND, KG, LT, MT, M2, PACK, CAJA, PAR</td></tr>
            </table>

            <!-- SECCIÓN: Editar y Eliminar -->
            <h2><i class="fas fa-edit me-2"></i>Editar Producto</h2>
            <p>Haz clic en el botón <i class="fas fa-edit text-primary"></i> de cualquier fila para abrir el formulario con los datos cargados.</p>
            <div class="docs-alert info">
                <strong>Importante:</strong> El SKU no se puede modificar una vez creado. Si necesitas cambiarlo, elimina y crea uno nuevo.
            </div>

            <h2><i class="fas fa-trash me-2"></i>Eliminar y Restaurar</h2>
            <p>BuzzDM usa <strong>eliminación lógica (soft delete)</strong>:</p>
            <ol class="step-list">
                <li><span class="step-title">Eliminar</span> Haz clic en <i class="fas fa-trash text-danger"></i>. El producto se oculta pero NO se borra.</li>
                <li><span class="step-title">Ver Eliminados</span> Activa "Ver eliminados" en Filtros Avanzados. Las filas eliminadas aparecen en rojo.</li>
                <li><span class="step-title">Restaurar</span> Haz clic en <i class="fas fa-trash-restore text-success"></i> para recuperar el producto.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Activo : Crear
                    Activo --> Eliminado : Soft Delete
                    Eliminado --> Activo : Restaurar
                    Activo --> StockBajo : stock <= min
                    StockBajo --> Activo : stock > min
                    StockBajo --> Agotado : stock = 0
                    Agotado --> Activo : Cargar Mercancía
                </div>
            </div>

            <!-- SECCIÓN: Tips Avanzados -->
            <h2><i class="fas fa-lightbulb me-2"></i>Tips y Mejores Prácticas</h2>
            <ul>
                <li>✅ <strong>SKUs consistentes:</strong> Usa un patrón como <code>CAT-MARCA-MODELO</code> (ej: CEL-SAM-A54)</li>
                <li>✅ <strong>Stock mínimo realista:</strong> Calcula según tu rotación promedio semanal</li>
                <li>✅ <strong>Marcas y categorías primero:</strong> Crea los catálogos antes de registrar productos masivamente</li>
                <li>✅ <strong>Kits virtuales:</strong> Úsalos para combos promocionales sin duplicar inventario</li>
                <li>✅ <strong>No elimines, inactiva:</strong> Cambiar a "Inactivo" es mejor que eliminar (mantiene histórico)</li>
            </ul>

            <div class="docs-alert warning">
                <strong>Productos con stock 0:</strong> No aparecen en la búsqueda de pedidos a menos que manualmente se agreguen. Esto previene ventas sin existencia.
            </div>
        </div>"

        <!-- KITS -->
        <div id="tpl-kits">
            <div class="docs-hero-minimal">
                <h1>Armar Kits</h1>
                <p class="lead">Potencia tus ventas creando combos o paquetes de productos.</p>
            </div>

            <h2>¿Qué es un Kit en BuzzDM?</h2>
            <p>Es un producto "virtual" que representa un conjunto. Lo más importante: <strong>El stock del Kit depende de sus componentes.</strong></p>

            <div class="docs-alert info">
                Si un Kit lleva 2 pilas y solo quedan 1 en stock, el Kit se marcará automáticamente como "Agotado".
            </div>

            <ol class="step-list">
                <li><span class="step-title">Crear Producto Base</span> Crea un producto nuevo y marca el switch "Es un Kit".</li>
                <li><span class="step-title">Asignar Receta</span> En la pestaña de componentes, busca y agrega los productos base (ej: Caja + 3 Jabones).</li>
                <li><span class="step-title">Sincronización</span> Al guardar, el sistema calculará cuántos Kits puedes vender basándose en el componente más escaso.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    graph LR
                    P1[Producto A] -- x1 --> K(KIT)
                    P2[Producto B] -- x2 --> K
                    K -- Venta --> S[Descuento Automático]
                </div>
            </div>
        </div>

        <!-- IMPORTAR -->
        <div id="tpl-importar">
            <div class="docs-hero-minimal">
                <h1>Importación Masiva</h1>
                <p class="lead">Ahorra horas de trabajo cargando grandes volúmenes de datos desde Excel.</p>
            </div>

            <h2><i class="fas fa-file-excel me-2"></i>Reglas de Oro del Excel</h2>
            <div class="docs-alert warning">
                El archivo debe estar en formato <strong>.xlsx</strong> y no debe contener celdas combinadas ni fórmulas complejas.
            </div>

            <ol class="step-list">
                <li><span class="step-title">Plantilla Oficial</span> Descarga siempre la plantilla desde el botón "Importar". El sistema espera columnas específicas en un orden exacto.</li>
                <li><span class="step-title">Limpieza de Datos</span> Verifica que los SKUs no tengan espacios al final y que los precios no lleven símbolos de moneda ($).</li>
                <li><span class="step-title">Pre-validación</span> Al subir el archivo, el sistema te mostrará una vista previa. Revisa si hay filas marcadas en rojo antes de confirmar.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    graph LR
                    X[Excel] --> V[Validación Sistema]
                    V -->|Error| F[Corregir en Excel]
                    V -->|OK| S[Sincronizar DB]
                    F --> X
                </div>
            </div>
        </div>

        <!-- PEDIDOS -->
        <div id="tpl-pedidos">
            <div class="docs-hero-minimal">
                <h1>Gestión de Pedidos</h1>
                <p class="lead">El corazón operativo: desde la cotización hasta la entrega y cobranza.</p>
            </div>

            <!-- SECCIÓN: Interfaz Principal -->
            <h2><i class="fas fa-desktop me-2"></i>Interfaz Principal</h2>
            <p>La pantalla de pedidos muestra una lista de tarjetas expansibles con información resumida.</p>

            <div class="docs-alert info">
                <strong>Barra Superior:</strong> Botón <code>+ Nuevo Pedido</code> siempre visible. Abre el formulario de creación.
            </div>

            <h3>Sistema de Filtros</h3>
            <table class="table table-bordered">
                <thead><tr><th>Filtro</th><th>Descripción</th><th>Ubicación</th></tr></thead>
                <tbody>
                    <tr><td><strong>Búsqueda</strong></td><td>ID del pedido o nombre del cliente</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Estado de Pago</strong></td><td>Todos / Pagado / No Pagado</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Fecha Desde</strong></td><td>Filtrar desde una fecha específica</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Fecha Hasta</strong></td><td>Filtrar hasta una fecha específica</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Estado de Envío</strong></td><td>Pendiente / Enviado / Recibido</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Ver eliminados</strong></td><td>Muestra pedidos en papelera</td><td>Filtros Avanzados</td></tr>
                </tbody>
            </table>

            <!-- SECCIÓN: Crear Pedido -->
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Pedido</h2>
            <p>El modal de creación tiene los siguientes campos:</p>

            <h3>1. Selección de Cliente</h3>
            <ol class="step-list">
                <li><span class="step-title">Buscar Cliente *</span> Escribe nombre o cédula. El sistema muestra sugerencias en tiempo real.</li>
                <li><span class="step-title">Seleccionar</span> Haz clic en el cliente correcto. Su nombre aparece en verde confirmando la selección.</li>
                <li><span class="step-title">Fecha</span> Por defecto es la fecha actual. Puedes ajustarla si es un pedido retroactivo.</li>
            </ol>

            <div class="docs-alert warning">
                <strong>Cliente Nuevo:</strong> Si no existe, primero créalo en el módulo Clientes. El sistema no permite crear clientes desde el formulario de pedidos.
            </div>

            <h3>2. Dirección y Notas</h3>
            <ol class="step-list">
                <li><span class="step-title">Dirección de Envío *</span> Obligatoria. Se usa para generar la guía de despacho.</li>
                <li><span class="step-title">Notas de Envío</span> Instrucciones especiales: "Llamar antes", "Dejar con portero", etc.</li>
            </ol>

            <h3>3. Agregar Productos</h3>
            <ol class="step-list">
                <li><span class="step-title">+ Añadir Producto</span> Crea una fila nueva para buscar un producto.</li>
                <li><span class="step-title">Buscar</span> Escribe SKU o nombre. Se muestra precio y stock disponible.</li>
                <li><span class="step-title">Cantidad</span> Ingresa la cantidad deseada. El subtotal se calcula automáticamente.</li>
                <li><span class="step-title">Eliminar Fila</span> Haz clic en la <i class="fas fa-times text-danger"></i> para quitar un producto.</li>
            </ol>

            <div class="docs-alert info">
                <strong>Total Calculado:</strong> El total se actualiza en tiempo real mientras agregas productos. Se muestra en la esquina inferior derecha del modal.
            </div>

            <h3>4. Estados Iniciales</h3>
            <table class="table table-bordered">
                <thead><tr><th>Campo</th><th>Opciones</th><th>Por Defecto</th></tr></thead>
                <tbody>
                    <tr><td><strong>Estado de Pago</strong></td><td>No Pagado / Pagado</td><td>No Pagado</td></tr>
                    <tr><td><strong>Estado de Envío</strong></td><td>Pendiente / Enviado / Recibido</td><td>Pendiente</td></tr>
                </tbody>
            </table>

            <!-- SECCIÓN: Lista de Pedidos -->
            <h2><i class="fas fa-list me-2"></i>Lista de Pedidos</h2>
            <p>Cada pedido se muestra como una tarjeta con:</p>
            <ul>
                <li><strong>ID y Referencia:</strong> Identificación única</li>
                <li><strong>Cliente:</strong> Nombre del comprador</li>
                <li><strong>Total:</strong> Monto total del pedido</li>
                <li><strong>Estados:</strong> Badges de pago y envío con colores distintivos</li>
                <li><strong>Fecha:</strong> Cuándo se creó</li>
            </ul>

            <h3>Acciones por Pedido</h3>
            <table class="table table-bordered">
                <tr><td><i class="fas fa-chevron-down"></i></td><td><strong>Expandir</strong></td><td>Ver productos del pedido sin abrir modal</td></tr>
                <tr><td><i class="fas fa-eye text-info"></i></td><td><strong>Ver Detalle</strong></td><td>Modal con información completa</td></tr>
                <tr><td><i class="fas fa-edit text-primary"></i></td><td><strong>Editar</strong></td><td>Modificar productos, estados, dirección</td></tr>
                <tr><td><i class="fas fa-trash text-danger"></i></td><td><strong>Eliminar</strong></td><td>Soft delete (se puede restaurar)</td></tr>
            </table>

            <!-- SECCIÓN: Estados y Flujo -->
            <h2><i class="fas fa-exchange-alt me-2"></i>Flujo de Estados</h2>
            <p>Un pedido tiene dos estados independientes:</p>

            <h3>Estado de Pago</h3>
            <table class="table table-bordered">
                <tr><td class="text-danger"><i class="fas fa-times-circle"></i></td><td><strong>No Pagado</strong></td><td>Deuda pendiente del cliente</td></tr>
                <tr><td class="text-success"><i class="fas fa-check-circle"></i></td><td><strong>Pagado</strong></td><td>Conciliado y cerrado</td></tr>
            </table>

            <h3>Estado de Envío</h3>
            <table class="table table-bordered">
                <tr><td class="text-warning"><i class="fas fa-clock"></i></td><td><strong>Pendiente</strong></td><td>Esperando ser preparado</td></tr>
                <tr><td class="text-info"><i class="fas fa-truck"></i></td><td><strong>Enviado</strong></td><td>En tránsito hacia el cliente</td></tr>
                <tr><td class="text-success"><i class="fas fa-check"></i></td><td><strong>Recibido</strong></td><td>Entregado y confirmado</td></tr>
            </table>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    direction LR
                    [*] --> NoPagado_Pendiente : Crear
                    NoPagado_Pendiente --> NoPagado_Enviado : Despachar
                    NoPagado_Pendiente --> Pagado_Pendiente : Cobrar
                    NoPagado_Enviado --> Pagado_Enviado : Cobrar
                    Pagado_Pendiente --> Pagado_Enviado : Despachar
                    Pagado_Enviado --> Pagado_Recibido : Confirmar
                    NoPagado_Enviado --> NoPagado_Recibido : Confirmar
                    NoPagado_Recibido --> Pagado_Recibido : Cobrar
                    Pagado_Recibido --> [*]
                </div>
            </div>

            <!-- SECCIÓN: Tips -->
            <h2><i class="fas fa-lightbulb me-2"></i>Tips y Mejores Prácticas</h2>
            <ul>
                <li>✅ <strong>Pagos primero:</strong> Intenta cobrar antes de despachar para reducir riesgos</li>
                <li>✅ <strong>Notas detalladas:</strong> Incluye instrucciones claras de entrega</li>
                <li>✅ <strong>Filtros por fecha:</strong> Úsalos para reportes mensuales de ventas</li>
                <li>✅ <strong>Revisa stock:</strong> El sistema advierte si no hay suficiente, pero permite crear el pedido</li>
                <li>✅ <strong>Eliminar ≠ borrar:</strong> Los pedidos eliminados se pueden restaurar</li>
            </ul>

            <div class="docs-alert warning">
                <strong>Modificar pedidos:</strong> Al editar un pedido ya creado, los cambios en productos <strong>no afectan el stock automáticamente</strong>. Usa el módulo de Cargos para ajustes de inventario.
            </div>
        </div>"

        <!-- CLIENTES -->
        <div id="tpl-clientes">
            <div class="docs-hero-minimal">
                <h1>Gestión de Clientes</h1>
                <p class="lead">Tu directorio de compradores: desde el registro hasta el historial de compras.</p>
            </div>

            <!-- SECCIÓN: Interfaz Principal -->
            <h2><i class="fas fa-desktop me-2"></i>Interfaz Principal</h2>
            <p>La pantalla de clientes tiene dos áreas principales:</p>

            <h3>Clientes Frecuentes</h3>
            <div class="docs-alert info">
                <strong>Banner superior:</strong> Muestra una cuadrícula con tus clientes más activos (los que más pedidos tienen). Incluye iconos de <i class="fas fa-crown text-warning"></i> VIP y conteo total de clientes.
            </div>

            <h3>Sistema de Filtros</h3>
            <table class="table table-bordered">
                <thead><tr><th>Filtro</th><th>Descripción</th><th>Ubicación</th></tr></thead>
                <tbody>
                    <tr><td><strong>Búsqueda</strong></td><td>Nombre, email, teléfono o ID</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Tipo</strong></td><td>Todos / VIP / Regular</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Fecha Desde</strong></td><td>Registrado a partir de...</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Fecha Hasta</strong></td><td>Registrado hasta...</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Ver inactivos</strong></td><td>Clientes desactivados</td><td>Filtros Avanzados</td></tr>
                </tbody>
            </table>

            <!-- SECCIÓN: Crear Cliente -->
            <h2><i class="fas fa-plus-circle me-2"></i>Registrar Nuevo Cliente</h2>
            <p>El formulario tiene dos secciones:</p>

            <h3>1. Información Básica</h3>
            <ol class="step-list">
                <li><span class="step-title">ID Cliente *</span> Cédula o RIF. Formato: Tipo (V/E/J/G/P) + número. <strong>Único y no editable después.</strong></li>
                <li><span class="step-title">Nombre *</span> Nombre completo o razón social.</li>
                <li><span class="step-title">Email</span> Correo electrónico (opcional, para notificaciones).</li>
                <li><span class="step-title">Cliente VIP</span> Marca para identificar compradores especiales.</li>
            </ol>

            <div class="docs-alert info">
                <strong>Tipos de ID:</strong> V = Venezolano, E = Extranjero, J = Jurídico, G = Gobierno, P = Pasaporte
            </div>

            <h3>2. Contacto</h3>
            <ol class="step-list">
                <li><span class="step-title">País</span> Código telefónico (+58, +1, +57, +54, +34)</li>
                <li><span class="step-title">Teléfono *</span> Número de contacto principal.</li>
                <li><span class="step-title">Dirección</span> Dirección física (para envíos).</li>
                <li><span class="step-title">Notas</span> Información adicional: preferencias, horarios, etc.</li>
            </ol>

            <!-- SECCIÓN: Lista de Clientes -->
            <h2><i class="fas fa-list me-2"></i>Lista de Clientes</h2>
            <p>Cada cliente muestra:</p>
            <ul>
                <li><strong>ID:</strong> Cédula/RIF completo</li>
                <li><strong>Nombre:</strong> Con icono <i class="fas fa-star text-warning"></i> si es VIP</li>
                <li><strong>Email:</strong> Correo registrado</li>
                <li><strong>Teléfono:</strong> Con código de país</li>
                <li><strong>Pedidos:</strong> Número total de compras</li>
                <li><strong>Fecha:</strong> Cuándo se registró</li>
            </ul>

            <h3>Acciones por Cliente</h3>
            <table class="table table-bordered">
                <tr><td><i class="fas fa-eye text-info"></i></td><td><strong>Ver Detalle</strong></td><td>Modal con toda la información</td></tr>
                <tr><td><i class="fas fa-edit text-primary"></i></td><td><strong>Editar</strong></td><td>Modificar datos (excepto ID)</td></tr>
                <tr><td><i class="fas fa-whatsapp text-success"></i></td><td><strong>WhatsApp</strong></td><td>Abrir chat directo</td></tr>
                <tr><td><i class="fas fa-trash text-danger"></i></td><td><strong>Desactivar</strong></td><td>Oculta sin eliminar</td></tr>
            </table>

            <!-- SECCIÓN: VIP -->
            <h2><i class="fas fa-star me-2"></i>Sistema VIP</h2>
            <p>Los clientes VIP tienen beneficios especiales:</p>
            <ul>
                <li>✅ Aparecen con icono de <i class="fas fa-star text-warning"></i> en todas las listas</li>
                <li>✅ Se muestran primero en el banner de "Clientes Frecuentes"</li>
                <li>✅ Puedes filtrar solo VIPs para campañas especiales</li>
            </ul>

            <div class="docs-alert warning">
                <strong>Consejo:</strong> Marca como VIP a clientes con alto volumen de compras o relaciones comerciales importantes.
            </div>

            <!-- SECCIÓN: Tips -->
            <h2><i class="fas fa-lightbulb me-2"></i>Tips y Mejores Prácticas</h2>
            <ul>
                <li>✅ <strong>ID único:</strong> Siempre verifica que no exista antes de crear</li>
                <li>✅ <strong>Teléfono completo:</strong> Incluye código de país para WhatsApp</li>
                <li>✅ <strong>Notas útiles:</strong> Guarda horarios de recepción o preferencias de pago</li>
                <li>✅ <strong>Dirección precisa:</strong> Para agilizar envíos y evitar errores</li>
            </ul>
        </div>

        <!-- CARGOS -->
        <div id="tpl-cargos">
            <div class="docs-hero-minimal">
                <h1>Cargar Existencias (Cargos)</h1>
                <p class="lead">Entrada formal de mercancía: compras, devoluciones, ajustes y más.</p>
            </div>

            <!-- SECCIÓN: Interfaz Principal -->
            <h2><i class="fas fa-desktop me-2"></i>Interfaz Principal</h2>
            <p>Este módulo tiene dos vistas que puedes alternar:</p>
            <ul>
                <li><strong>Historial:</strong> Lista de todos los cargos registrados (vista por defecto)</li>
                <li><strong>Formulario:</strong> Crear o editar un cargo</li>
            </ul>

            <div class="docs-alert info">
                <strong>Barra Superior:</strong> Dos botones: <code>Historial</code> (volver a la lista) y <code>+ Nuevo Cargo</code> (crear).
            </div>

            <h3>Filtros del Historial</h3>
            <table class="table table-bordered">
                <thead><tr><th>Filtro</th><th>Descripción</th><th>Ubicación</th></tr></thead>
                <tbody>
                    <tr><td><strong>Búsqueda</strong></td><td>Código, proveedor o referencia</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Fecha Desde</strong></td><td>Fecha mínima</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Fecha Hasta</strong></td><td>Fecha máxima</td><td>Siempre visible</td></tr>
                    <tr><td><strong>Estado</strong></td><td>Borrador / Confirmado / Anulado</td><td>Filtros Avanzados</td></tr>
                    <tr><td><strong>Concepto</strong></td><td>Compra, Donación, Ajuste, etc.</td><td>Filtros Avanzados</td></tr>
                </tbody>
            </table>

            <!-- SECCIÓN: Crear Cargo -->
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Nuevo Cargo</h2>
            <p>El formulario se divide en estas secciones:</p>

            <h3>1. Identificación</h3>
            <ol class="step-list">
                <li><span class="step-title">Código</span> Auto-generado por el sistema (formato: CRG-XXXXXX)</li>
                <li><span class="step-title">Fecha *</span> Fecha del movimiento. Por defecto hoy.</li>
            </ol>

            <h3>2. Concepto y Destino</h3>
            <table class="table table-bordered">
                <thead><tr><th>Concepto</th><th>Icono</th><th>Cuándo usar</th></tr></thead>
                <tbody>
                    <tr><td><strong>Compra a Proveedor</strong></td><td><i class="fas fa-box"></i></td><td>Mercancía adquirida</td></tr>
                    <tr><td><strong>Transferencia</strong></td><td><i class="fas fa-exchange-alt"></i></td><td>Mover entre almacenes</td></tr>
                    <tr><td><strong>Ajuste Positivo</strong></td><td><i class="fas fa-plus"></i></td><td>Corrección de conteo</td></tr>
                    <tr><td><strong>Inventario Inicial</strong></td><td><i class="fas fa-clipboard-list"></i></td><td>Carga inicial del sistema</td></tr>
                    <tr><td><strong>Devolución Cliente</strong></td><td><i class="fas fa-undo"></i></td><td>Producto regresado</td></tr>
                    <tr><td><strong>Donación</strong></td><td><i class="fas fa-gift"></i></td><td>Mercancía recibida sin costo</td></tr>
                    <tr><td><strong>Otro</strong></td><td><i class="fas fa-edit"></i></td><td>Casos especiales</td></tr>
                </tbody>
            </table>

            <div class="docs-alert info">
                <strong>Almacén de Destino *:</strong> Obligatorio. Define a qué ubicación se sumará el stock.
            </div>

            <h3>3. Proveedor y Referencia</h3>
            <ol class="step-list">
                <li><span class="step-title">Proveedor</span> Opcional. Busca por nombre. Vincula compras a proveedores específicos para histórico de precios.</li>
                <li><span class="step-title">Referencia / Factura</span> Número de documento externo para trazabilidad.</li>
                <li><span class="step-title">Control de Costos (Compras)</span> Si el concepto es **Compra**, el sistema habilita el campo "Precio Compra". Esto permite calcular el valor total del ingreso y actualizar costos promedio.</li>
                <li><span class="step-title">Notas</span> Observaciones internas sobre el movimiento.</li>
            </ol>

            <h3>4. Productos del Cargo</h3>
            <ol class="step-list">
                <li><span class="step-title">+ Añadir Producto</span> Crea una fila nueva</li>
                <li><span class="step-title">Buscar SKU</span> Escribe para buscar productos existentes</li>
                <li><span class="step-title">Cantidad</span> Unidades que ingresan al inventario</li>
                <li><span class="step-title">Eliminar</span> Quita la línea con <i class="fas fa-times text-danger"></i></li>
            </ol>

            <div class="docs-alert warning">
                <strong>Resumen en tiempo real:</strong> Las tarjetas inferiores muestran Total Productos y Total Unidades mientras agregas items.
            </div>

            <!-- SECCIÓN: Estados -->
            <h2><i class="fas fa-tasks me-2"></i>Estados del Cargo</h2>
            <table class="table table-bordered">
                <tr><td class="text-warning"><i class="fas fa-edit"></i></td><td><strong>Borrador</strong></td><td>En edición. No afecta stock. Se puede modificar o eliminar.</td></tr>
                <tr><td class="text-success"><i class="fas fa-check-circle"></i></td><td><strong>Confirmado</strong></td><td>Stock sumado. Ya no editable. Solo anulable.</td></tr>
                <tr><td class="text-danger"><i class="fas fa-ban"></i></td><td><strong>Anulado</strong></td><td>Stock revertido. Movimiento inválido.</td></tr>
            </table>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Borrador : Nuevo Cargo
                    Borrador --> Borrador : Editar/Productos
                    Borrador --> Confirmado : Confirmar
                    Confirmado --> Anulado : Anular (con motivo)
                    Borrador --> [*] : Eliminar
                </div>
            </div>

            <!-- SECCIÓN: Acciones -->
            <h2><i class="fas fa-cogs me-2"></i>Acciones Disponibles</h2>
            <table class="table table-bordered">
                <tr><td><i class="fas fa-eye text-info"></i></td><td><strong>Ver Detalle</strong></td><td>Modal con información completa</td></tr>
                <tr><td><i class="fas fa-edit text-primary"></i></td><td><strong>Editar</strong></td><td>Solo borradores</td></tr>
                <tr><td><i class="fas fa-check text-success"></i></td><td><strong>Confirmar</strong></td><td>Suma stock inmediatamente</td></tr>
                <tr><td><i class="fas fa-ban text-warning"></i></td><td><strong>Anular</strong></td><td>Requiere motivo. Revierte stock.</td></tr>
            </table>

            <!-- SECCIÓN: Tips -->
            <h2><i class="fas fa-lightbulb me-2"></i>Tips y Mejores Prácticas</h2>
            <ul>
                <li>✅ <strong>Usa borradores:</strong> Guarda primero y confirma después de verificar conteo físico</li>
                <li>✅ <strong>Referencia de factura:</strong> Siempre incluye el número para auditorías</li>
                <li>✅ <strong>Proveedor registrado:</strong> Vincula para tener historial de compras</li>
                <li>✅ <strong>Anular ≠ Eliminar:</strong> Solo puedes anular cargos confirmados; los borradores se eliminan</li>
            </ul>

            <div class="docs-alert warning">
                <strong>Importante:</strong> Al anular un cargo confirmado, el stock se resta automáticamente. Esta acción genera un registro de auditoría.
            </div>
        </div>

        <!-- ALMACENES -->
        <div id="tpl-almacenes">
            <div class="docs-hero-minimal">
                <h1>Gestión de Almacenes</h1>
                <p class="lead">Organiza tu inventario por ubicaciones físicas: depósitos, tiendas, estantes.</p>
            </div>

            <!-- SECCIÓN: Multi-Almacén -->
            <h2><i class="fas fa-warehouse me-2"></i>Sistema Multi-Almacén</h2>
            <p>Separa el stock por ubicación para:</p>
            <ul>
                <li>Saber exactamente qué hay en cada punto de venta</li>
                <li>Transferir mercancía entre ubicaciones</li>
                <li>Asignar responsables por almacén</li>
            </ul>

            <!-- SECCIÓN: Crear Almacén -->
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Almacén</h2>
            <ol class="step-list">
                <li><span class="step-title">Nombre *</span> Identificador claro: "Depósito Central", "Tienda Norte", etc.</li>
                <li><span class="step-title">Dirección</span> Ubicación física para referencias de envío</li>
                <li><span class="step-title">Responsable</span> Usuario encargado de la gestión</li>
                <li><span class="step-title">Estado</span> Activo / Inactivo (sin eliminar datos)</li>
            </ol>

            <div class="docs-alert info">
                <strong>Stock por Almacén:</strong> Al consultar un producto, verás el desglose de unidades en cada ubicación.
            </div>

            <!-- SECCIÓN: Transferencias -->
            <h2><i class="fas fa-exchange-alt me-2"></i>Transferencias</h2>
            <p>Para mover mercancía entre almacenes:</p>
            <ol class="step-list">
                <li><span class="step-title">Cargo de Salida</span> Crea un cargo con concepto "Transferencia" en el almacén origen</li>
                <li><span class="step-title">Cargo de Entrada</span> Crea un cargo correspondiente en el almacén destino</li>
            </ol>

            <div class="docs-alert warning">
                <strong>Sin efecto en costo:</strong> Las transferencias no afectan el costo promedio de los productos.
            </div>
        </div>

        <!-- PROVEEDORES -->
        <div id="tpl-proveedores">
            <div class="docs-hero-minimal">
                <h1>Gestión de Proveedores</h1>
                <p class="lead">Mantén un directorio de tus aliados comerciales y fuentes de suministro.</p>
            </div>

            <h2><i class="fas fa-handshake me-2"></i>Relación Comercial</h2>
            <p>Registrar proveedores es fundamental para:");</p>
            <ul>
                <li>Tener trazabilidad de quién te suministra cada producto.</li>
                <li>Comparar costos de compra entre diferentes fuentes.</li>
                <li>Mantener contacto rápido (WhatsApp/Correo) desde el sistema.</li>
            </ul>

            <ol class="step-list">
                <li><span class="step-title">Datos Fiscales</span> Registra el RIF o NIT para tus registros contables legales.</li>
                <li><span class="step-title">Contactos</span> Agrega el nombre del vendedor y su teléfono para pedidos rápidos.</li>
            </ol>
        </div>

        <!-- CATALOGOS -->
        <div id="tpl-catalogos">
            <div class="docs-hero-minimal">
                <h1>Clasificación y Atributos</h1>
                <p class="lead">Estructura tu inventario para que encontrar un producto sea cuestión de segundos.</p>
            </div>

            <h2><i class="fas fa-tags me-2"></i>Categorías, Marcas y Unidades</h2>
            <p>Los catálogos son los cimientos de tu tienda virtual y reportes.</p>

            <div class="docs-alert info">
                Una buena categorización permite a tus clientes filtrar productos por "Celulares", "Accesorios", etc., mejorando su experiencia de compra.
            </div>

            <ol class="step-list">
                <li><span class="step-title">Categorías</span> Agrupa productos por tipo. Ej: "Electrónica", "Hogar".</li>
                <li><span class="step-title">Marcas</span> Identifica el fabricante. Vital para reportes de garantía y fidelidad.</li>
                <li><span class="step-title">Unidades de Medida</span> Define cómo vendes: Por unidad (Pza), por peso (Kg), o por paquete (Pte).</li>
            </ol>
        </div>

        <!-- NOTAS -->
        <div id="tpl-notas">
            <div class="docs-hero-minimal">
                <h1>Notas Kanban</h1>
                <p class="lead">Tablero visual para gestión de tareas, proyectos y colaboración en equipo.</p>
            </div>

            <!-- SECCIÓN: Tablero -->
            <h2><i class="fas fa-columns me-2"></i>El Tablero Kanban</h2>
            <p>Visualiza el flujo de trabajo en 3 columnas:</p>

            <table class="table table-bordered">
                <tr><td class="text-warning"><i class="fas fa-clock"></i></td><td><strong>PENDIENTE</strong></td><td>Tareas nuevas, esperando inicio</td></tr>
                <tr><td class="text-info"><i class="fas fa-spinner"></i></td><td><strong>EN PROCESO</strong></td><td>Trabajo activo, en desarrollo</td></tr>
                <tr><td class="text-success"><i class="fas fa-check"></i></td><td><strong>TERMINADO</strong></td><td>Completadas, listas para archivo</td></tr>
            </table>

            <div class="docs-alert info">
                <strong>Drag & Drop:</strong> Arrastra las tarjetas entre columnas para cambiar su estado. El cambio se guarda automáticamente.
            </div>

            <!-- SECCIÓN: Crear Nota -->
            <h2><i class="fas fa-plus-circle me-2"></i>Crear Nueva Nota</h2>
            <ol class="step-list">
                <li><span class="step-title">Título *</span> Nombre corto y descriptivo de la tarea</li>
                <li><span class="step-title">Descripción</span> Detalle del trabajo a realizar</li>
                <li><span class="step-title">Cliente</span> Vincula la nota a un cliente específico</li>
                <li><span class="step-title">Prioridad</span> Baja / Media / Alta - Afecta el orden visual</li>
                <li><span class="step-title">Color</span> Código de color para categorización visual</li>
                <li><span class="step-title">Miembros</span> Asigna usuarios responsables de la tarea</li>
            </ol>

            <!-- SECCIÓN: Contenido de Tarjeta -->
            <h2><i class="fas fa-tasks me-2"></i>Contenido de la Tarjeta</h2>
            <p>Cada nota puede incluir:</p>
            <ul>
                <li><strong>Checklist:</strong> Lista de pasos internos (marcar como completados)</li>
                <li><strong>Productos vinculados:</strong> SKUs relacionados con la tarea</li>
                <li><strong>Adjuntos:</strong> Fotos, documentos, PDFs</li>
                <li><strong>Comentarios:</strong> Historial de actualizaciones</li>
            </ul>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Pendiente : Crear
                    Pendiente --> EnProceso : Arrastrar
                    EnProceso --> Pendiente : Pausar
                    EnProceso --> Terminado : Completar
                    Terminado --> [*] : Archivar
                </div>
            </div>

            <!-- SECCIÓN: Tips -->
            <h2><i class="fas fa-lightbulb me-2"></i>Tips y Mejores Prácticas</h2>
            <ul>
                <li>✅ <strong>WIP Limit:</strong> No tengas más de 3-5 tareas "En Proceso" por persona</li>
                <li>✅ <strong>Colores consistentes:</strong> Usa rojo para urgentes, azul para rutina</li>
                <li>✅ <strong>Limpieza semanal:</strong> Archiva las tareas terminadas regularmente</li>
                <li>✅ <strong>Checklists:</strong> Divide tareas grandes en pasos pequeños</li>
            </ul>
        </div>

        <!-- BACKUPS -->
        <div id="tpl-backups">
            <div class="docs-hero-minimal">
                <h1>Respaldos y Seguridad</h1>
                <p class="lead">Protege la información de tu negocio con copias de seguridad automáticas y manuales.</p>
            </div>

            <!-- SECCIÓN: Backup Automático -->
            <h2><i class="fas fa-robot me-2"></i>Respaldo Automático</h2>
            <p>BuzzDM genera respaldos automáticamente cada 24 horas:</p>
            <ul>
                <li>Verificación silenciosa cada vez que accedes al sistema</li>
                <li>Backup nocturno si han pasado +24 horas sin respaldo</li>
                <li>Historial de los últimos respaldos disponibles</li>
            </ul>

            <div class="docs-alert info">
                <strong>Poor Man's Cron:</strong> El sistema no requiere configuración de tareas programadas del servidor.
            </div>

            <!-- SECCIÓN: Backup Manual -->
            <h2><i class="fas fa-download me-2"></i>Respaldo Manual</h2>
            <ol class="step-list">
                <li><span class="step-title">+ Generar Respaldo</span> Crea un nuevo archivo .sql con toda la base de datos</li>
                <li><span class="step-title">Esperar</span> El proceso tarda 5-30 segundos según el tamaño</li>
                <li><span class="step-title">Descargar</span> Haz clic en el ícono de descarga junto al backup</li>
            </ol>

            <!-- SECCIÓN: Lista de Backups -->
            <h2><i class="fas fa-list me-2"></i>Historial de Respaldos</h2>
            <p>Cada backup muestra:</p>
            <ul>
                <li><strong>Fecha/Hora:</strong> Cuándo se generó</li>
                <li><strong>Tamaño:</strong> Peso del archivo SQL</li>
                <li><strong>Tipo:</strong> Automático <i class="fas fa-robot"></i> o Manual <i class="fas fa-user"></i></li>
                <li><strong>Acciones:</strong> <i class="fas fa-download text-primary"></i> Descargar | <i class="fas fa-trash text-danger"></i> Eliminar</li>
            </ul>

            <div class="docs-alert warning">
                <strong>Regla de Oro:</strong> Descarga y guarda en la nube (Google Drive, Dropbox) al menos 1 backup por semana.
            </div>

            <!-- SECCIÓN: Restauración -->
            <h2><i class="fas fa-undo me-2"></i>Restauración</h2>
            <p>Para restaurar desde un backup:</p>
            <ol class="step-list">
                <li><span class="step-title">Acceso phpMyAdmin</span> Ingresa al panel de base de datos</li>
                <li><span class="step-title">Importar</span> Selecciona el archivo .sql descargado</li>
                <li><span class="step-title">Ejecutar</span> El sistema reconstruirá todas las tablas y datos</li>
            </ol>

            <div class="docs-alert warning">
                <strong>⚠️ Precaución:</strong> La restauración sobrescribe TODOS los datos actuales. Solo usar en emergencias.
            </div>
        </div>

        <!-- EMPRESA -->
        <div id="tpl-empresa">
            <div class="docs-hero-minimal">
                <h1>Identidad de Empresa</h1>
                <p class="lead">Personaliza BuzzDM con los datos legales y visuales de tu negocio.</p>
            </div>

            <h2><i class="fas fa-building me-2"></i>Configuración Legal</h2>
            <p>La información que registres aquí aparecerá automáticamente en tus reportes y recibos.</p>

            <ol class="step-list">
                <li><span class="step-title">Razón Social y RIF</span> Asegúrate de que coincidan exactamente con tu registro fiscal.</li>
                <li><span class="step-title">Dirección y Contacto</span> Estos datos son vitales para que tus proveedores y clientes te ubiquen correctamente.</li>
                <li><span class="step-title">Logo Corporativo</span> Sube una imagen clara. El sistema la usará para personalizar la interfaz y los documentos impresos.</li>
            </ol>

            <div class="docs-alert info">
                Puedes actualizar estos datos en cualquier momento si tu empresa cambia de sede o redes sociales.
            </div>
        </div>

        <!-- USUARIOS -->
        <div id="tpl-usuarios">
            <div class="docs-hero-minimal">
                <h1>Gestión de Accesos</h1>
                <p class="lead">Controla quién puede entrar al sistema y qué acciones puede realizar.</p>
            </div>

            <h2><i class="fas fa-user-shield me-2"></i>Roles y Permisos</h2>
            <p>No todos los usuarios necesitan ver todo. Asigna roles según la responsabilidad:</p>

            <ul>
                <li><strong>Administrador</strong>: Acceso total a configuraciones, backups y logs.</li>
                <li><strong>Vendedor</strong>: Enfocado en pedidos, clientes y consulta de stock.</li>
                <li><strong>Almacenista</strong>: Especializado en cargos, almacenes e inventario.</li>
            </ul>

            <ol class="step-list">
                <li><span class="step-title">Crear Cuenta</span> Asigna un correo real y una contraseña segura.</li>
                <li><span class="step-title">Asignar Rol</span> Elige el perfil que mejor se adapte a sus tareas diarias.</li>
                <li><span class="step-title">Seguridad</span> Puedes desactivar usuarios en cualquier momento sin borrar sus registros históricos.</li>
            </ol>
        </div>

        <!-- PAGOS -->
        <div id="tpl-pagos">
            <div class="docs-hero-minimal">
                <h1>Control de Pagos</h1>
                <p class="lead">Asegura tu flujo de caja monitoreando cada transacción.</p>
            </div>

            <h2><i class="fas fa-money-check-alt me-2"></i>El Semáforo de Cartera</h2>
            <p>Cada pedido tiene un estado de pago que debes actualizar manualmente:</p>

            <ol class="step-list">
                <li><span class="step-title">No Pagado (Rojo)</span> Deuda pendiente del cliente. Aparece en las alertas de cobranza.</li>
                <li><span class="step-title">Pagado (Verde)</span> Conciliado con tu banco o caja física. Cierra el ciclo financiero del pedido.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Pendiente
                    Pendiente --> Pagado : Registro de Pago
                    Pagado --> Conciliado : Auditoría Admin
                    Conciliado --> [*]
                </div>
            </div>
        </div>

        <!-- ENVIOS -->
        <div id="tpl-envios">
            <div class="docs-hero-minimal">
                <h1>Rastreo Operativo</h1>
                <p class="lead">Garantiza que cada pedido llegue a su destino final.</p>
            </div>

            <h2><i class="fas fa-shipping-fast me-2"></i>Estados de Envío</h2>
            <p>Mantén a tu equipo informado sobre la ubicación física de la mercancía:</p>

            <ol class="step-list">
                <li><span class="step-title">Pendiente</span> Pedido nuevo, aún no preparado.</li>
                <li><span class="step-title">En Proceso</span> El equipo de almacén está armando el paquete.</li>
                <li><span class="step-title">Enviado</span> La mercancía salió del almacén por transporte propio o externo.</li>
                <li><span class="step-title">Entregado</span> El cliente recibió conforme. El stock se deduce definitivamente.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    flowchart LR
                    A[Pendiente] --> B[Pick & Pack]
                    B --> C[Tránsito]
                    C --> D[Entregado]
                </div>
            </div>
        </div>

        <!-- LOGS -->
        <div id="tpl-logs">
            <div class="docs-hero-minimal">
                <h1>Auditoría y Trazabilidad</h1>
                <p class="lead">Mantén un registro histórico de cada cambio realizado para garantizar la transparencia.</p>
            </div>

            <h2><i class="fas fa-history me-2"></i>La Caja Negra del Sistema</h2>
            <p>El registro de actividad guarda detalles cruciales de la operación diaria:</p>

            <ol class="step-list">
                <li><span class="step-title">Quién</span> El nombre del usuario que realizó la acción.</li>
                <li><span class="step-title">Qué</span> El tipo de acción (Creación, Edición, Eliminación, Login).</li>
                <li><span class="step-title">Cuándo</span> Fecha y hora exacta (segundos incluidos).</li>
                <li><span class="step-title">Referencia</span> El ID del registro afectado para facilitar búsquedas.</li>
            </ol>

            <div class="docs-alert info">
                Estos registros son permanentes y no pueden ser alterados por ningún usuario, garantizando una auditoría confiable.
            </div>
        </div>

        <!-- DIVISAS -->
        <div id="tpl-divisas">
            <div class="docs-hero-minimal">
                <h1>Manejo Multi-moneda</h1>
                <p class="lead">Gestiona tus precios en moneda local o extranjera con tasas actualizadas.</p>
            </div>

            <h2><i class="fas fa-exchange-alt me-2"></i>Monitor del Dólar</h2>
            <p>BuzzDM usa una tasa de referencia centralizada para todos los cálculos de conversión.</p>

            <ol class="step-list">
                <li><span class="step-title">Consulta</span> El monitor muestra el valor actual del dólar según las fuentes configuradas.</li>
                <li><span class="step-title">Actualización</span> Puedes actualizar la tasa manualmente si el mercado varía bruscamente.</li>
                <li><span class="step-title">Impacto</span> Al cambiar la tasa, todos los precios marcados "en $ pero pagaderos en BS" se recalculan al instante en la pantalla de pedidos.</li>
            </ol>

            <div class="docs-alert info">
                Esta herramienta es fundamental para proteger tus márgenes de ganancia ante la inflación o volatilidad cambiaria.
            </div>
        </div>

        <!-- REQUISICIONES -->
        <div id="tpl-requisiciones">
            <div class="docs-hero-minimal">
                <h1>Requisiciones de Compra</h1>
                <p class="lead">Planifica el abastecimiento de tu inventario de forma inteligente y centralizada.</p>
            </div>

            <h2><i class="fas fa-magic me-2"></i>Auto-Stock Inteligente</h2>
            <p>BuzzDM analiza tu inventario en tiempo real para sugerirte qué comprar:</p>
            <ul>
                <li><strong>Detección de Stock Bajo:</strong> Identifica productos por debajo de su stock mínimo configurado.</li>
                <li><strong>Propuesta de Venta:</strong> El botón <span class="badge bg-warning text-dark"><i class="fas fa-magic"></i> Auto-Stock</span> genera automáticamente una lista con las cantidades necesarias.</li>
                <li><strong>Sugerencia de Proveedor:</strong> Para cada producto, el sistema propone el último proveedor al que se le compró.</li>
            </ul>

            <h2><i class="fas fa-tasks me-2"></i>Ciclo de Vida</h2>
            <ol class="step-list">
                <li><span class="step-title">Generación</span> Registra los ítems necesarios, asigna prioridad (Baja a Urgente) y notas.</li>
                <li><span class="step-title">Aprobación</span> Un usuario con rango Administrativo debe revisar y "Aprobar" la solicitud.</li>
                <li><span class="step-title">Procesamiento</span> Una vez aprobada, puedes hacer clic en "Generar Cargos". Esto creará automáticamente entradas de inventario (Borradores) para cada proveedor involucrado.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Pendiente : Crear Requisición
                    Pendiente --> Aprobada : Revisión Admin
                    Pendiente --> Rechazada : Falta Presupuesto/Error
                    Aprobada --> Procesada : Generar Cargos (Entradas)
                    Procesada --> [*]
                </div>
            </div>

            <div class="docs-alert info">
                Este flujo garantiza que las compras sigan un proceso de control previo antes de afectar el inventario físico.
            </div>
        </div>

        <!-- TRANSFERENCIAS -->
        <div id="tpl-transferencias">
            <div class="docs-hero-minimal">
                <h1>Transferencias entre Almacenes</h1>
                <p class="lead">Mueve stock de forma segura entre tus distintas ubicaciones físicas.</p>
            </div>

            <h2><i class="fas fa-exchange-alt me-2"></i>Movimiento de Stock</h2>
            <p>Este módulo gestiona el traslado interno de mercancía sin afectar las ventas o compras externas.</p>

            <div class="docs-alert warning">
                <strong>Responsabilidad:</strong> Una transferencia resta stock del origen al "Enviar" y suma al destino solo cuando se marca como "Recibida".
            </div>

            <ol class="step-list">
                <li><span class="step-title">Nueva Transferencia</span> Elige el almacén de origen y el de destino. El sistema no permite que sean el mismo.</li>
                <li><span class="step-title">Selección de Artículos</span> Agrega los productos. El sistema te muestra el stock disponible en el almacén de origen para evitar errores.</li>
                <li><span class="step-title">Envío</span> Al hacer clic en "Enviar", la transferencia pasa a estado "En Tránsito". El stock queda bloqueado (restado del origen).</li>
                <li><span class="step-title">Recepción</span> El responsable en el almacén de destino debe buscar la transferencia y marcarla como "Recibida". En ese momento, el stock se suma al inventario del destino.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Borrador : Crear
                    Borrador --> EnTránsito : Enviar (Resta Origen)
                    EnTránsito --> Recibido : Recibir (Suma Destino)
                    EnTránsito --> Cancelado : Cancelar (Reclama Origen)
                </div>
            </div>

            <h2><i class="fas fa-truck-loading me-2"></i>Seguimiento</h2>
            <p>Usa la "Referencia Externa" para colocar números de guía de transporte o nombres de choferes para un mejor control logístico.</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>
    <!-- Driver.js (Local) -->
    <script src="assets/js/driver.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10.6.1/dist/mermaid.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/manual.js"></script>
</body>
</html>
