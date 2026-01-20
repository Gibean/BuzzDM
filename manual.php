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
                    <li><a class="docs-step-item" data-section="empresa" onclick="showSection('empresa')">Datos de Empresa</a></li>
                    <li><a class="docs-step-item" data-section="usuarios" onclick="showSection('usuarios')">Usuarios y Roles</a></li>
                    <li><a class="docs-step-item" data-section="catalogos" onclick="showSection('catalogos')">Categorías y Marcas</a></li>
                    <li><a class="docs-step-item" data-section="clientes" onclick="showSection('clientes')">Registrar Clientes</a></li>
                    <li><a class="docs-step-item" data-section="proveedores" onclick="showSection('proveedores')">Agregar Proveedores</a></li>
                    <li><a class="docs-step-item" data-section="productos" onclick="showSection('productos')">Crear Productos</a></li>
                    <li><a class="docs-step-item" data-section="kits" onclick="showSection('kits')">Armar Kits</a></li>
                    <li><a class="docs-step-item" data-section="importar" onclick="showSection('importar')">Importar Excel</a></li>
                    <li><a class="docs-step-item" data-section="almacenes" onclick="showSection('almacenes')">Configurar Almacenes</a></li>
                    <li><a class="docs-step-item" data-section="cargos" onclick="showSection('cargos')">Registrar Cargos</a></li>
                    <li><a class="docs-step-item" data-section="pedidos" onclick="showSection('pedidos')">Crear Pedidos</a></li>
                    <li><a class="docs-step-item" data-section="pagos" onclick="showSection('pagos')">Estados de Pago</a></li>
                    <li><a class="docs-step-item" data-section="envios" onclick="showSection('envios')">Seguimiento Envíos</a></li>
                    <li><a class="docs-step-item" data-section="notas" onclick="showSection('notas')">Notas Kanban</a></li>
                    <li><a class="docs-step-item" data-section="divisas" onclick="showSection('divisas')">Monitor Divisas</a></li>
                    <li><a class="docs-step-item" data-section="backups" onclick="showSection('backups')">Respaldos</a></li>
                    <li><a class="docs-step-item" data-section="logs" onclick="showSection('logs')">Registro de Actividad</a></li>
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
                        <a class="docs-block-link" onclick="showSection('dashboard')">
                            <div class="docs-block-icon"><i class="fas fa-chart-line"></i></div>
                            <div class="docs-block-title">Panel de Control</div>
                            <div class="docs-block-desc">KPIs, gráficos y alertas del sistema</div>
                        </a>
                        <a class="docs-block-link" onclick="showSection('productos')">
                            <div class="docs-block-icon"><i class="fas fa-box"></i></div>
                            <div class="docs-block-title">Inventario</div>
                            <div class="docs-block-desc">Productos, kits e importación</div>
                        </a>
                        <a class="docs-block-link" onclick="showSection('pedidos')">
                            <div class="docs-block-icon"><i class="fas fa-shopping-cart"></i></div>
                            <div class="docs-block-title">Ventas</div>
                            <div class="docs-block-desc">Pedidos, pagos y envíos</div>
                        </a>
                        <a class="docs-block-link" onclick="showSection('clientes')">
                            <div class="docs-block-icon"><i class="fas fa-users"></i></div>
                            <div class="docs-block-title">Clientes</div>
                            <div class="docs-block-desc">Registro y gestión de clientes</div>
                        </a>
                        <a class="docs-block-link" onclick="showSection('almacenes')">
                            <div class="docs-block-icon"><i class="fas fa-warehouse"></i></div>
                            <div class="docs-block-title">Almacenes</div>
                            <div class="docs-block-desc">Ubicaciones y stock</div>
                        </a>
                        <a class="docs-block-link" onclick="showSection('empresa')">
                            <div class="docs-block-icon"><i class="fas fa-cog"></i></div>
                            <div class="docs-block-title">Configuración</div>
                            <div class="docs-block-desc">Empresa, usuarios y sistema</div>
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

        <!-- PRODUCTOS -->
        <div id="tpl-productos">
            <div class="docs-hero-minimal">
                <h1>Crear Productos</h1>
                <p class="lead">La base de tu inventario. Registra cada ítem con precisión para un control total.</p>
            </div>

            <h2><i class="fas fa-box me-2"></i>Cómo registrar un producto</h2>
            <p>Para mantener un inventario ordenado, sigue este flujo recomendado:</p>

            <ol class="step-list">
                <li><span class="step-title">Identificación (SKU)</span> El SKU es el código maestro. Puedes usar el de fábrica o generar uno propio. Debe ser único.</li>
                <li><span class="step-title">Clasificación</span> Asigna el producto a una Marca y Categoría. Esto es vital para los filtros y reportes de ventas.</li>
                <li><span class="step-title">Costos y Precios</span> Define tu precio de venta. Si incluyes el costo, el sistema podrá calcular tu margen de ganancia real.</li>
                <li><span class="step-title">Umbral de Alerta</span> Establece un "Mínimo". Cuando el stock caiga a este número, aparecerá en tu Dashboard.</li>
            </ol>

            <div class="docs-alert warning">
                Un producto con stock cero no aparecerá en el buscador de pedidos a menos que habilites "ventas sin stock".
            </div>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Registro
                    Registro --> Activo : Stock > 0
                    Activo --> Alerta : Stock <= Mínimo
                    Alerta --> Agotado : Stock = 0
                    Agotado --> Activo : Cargar Mercancía
                </div>
            </div>
        </div>

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
                <p class="lead">Desde la cotización inicial hasta la entrega final al cliente.</p>
            </div>

            <h2><i class="fas fa-shopping-bag me-2"></i>Ciclo de Vida de una Venta</h2>
            <ol class="step-list">
                <li><span class="step-title">Check-in Cliente</span> Busca un cliente existente. Si es nuevo, el sistema te permitirá crearlo sin salir de la pantalla de pedidos.</li>
                <li><span class="step-title">Selección de Ítems</span> Agrega productos. El sistema te avisará en tiempo real si el stock es insuficiente.</li>
                <li><span class="step-title">Confirmación</span> Al guardar, el stock queda "Reservado" (ya no está disponible para otros, pero aún no sale físicamente).</li>
                <li><span class="step-title">Cierre</span> Una vez pagado y entregado, el pedido pasa a histórico para tus reportes de ingresos.</li>
            </ol>

            <div class="mermaid-container">
                <div class="mermaid">
                    stateDiagram-v2
                    [*] --> Pendiente : Crear Pedido
                    Pendiente --> Pagado : Registrar Pago
                    Pendiente --> Enviado : Despachar
                    Pagado --> Entregado : Finalizar
                    Enviado --> Entregado : Finalizar
                    Entregado --> [*]
                </div>
            </div>
        </div>

        <!-- CLIENTES -->
        <div id="tpl-clientes">
            <div class="docs-hero-minimal">
                <h1>Base de Clientes</h1>
                <p class="lead">Conoce a tus compradores y agiliza tus cierres de venta.</p>
            </div>

            <h2><i class="fas fa-address-book me-2"></i>Gestión de Directorio</h2>
            <p>Un directorio de clientes bien alimentado te permite:</p>
            <ul>
                <li>Ver historial de compras por persona.</li>
                <li>Generar facturas y recibos automáticos.</li>
                <li>Hacer seguimiento de deudas pendientes.</li>
            </ul>

            <div class="docs-alert info">
                Usa el campo "Notas" para guardar detalles importantes, como direcciones de entrega preferidas o días de recepción.
            </div>

            <ol class="step-list">
                <li><span class="step-title">Registro Único</span> Evita duplicados verificando por Nro. de Cédula o RIF.</li>
                <li><span class="step-title">Clasificación</span> Identifica si es cliente Retail o Mayorista (si manejas precios diferenciados).</li>
            </ol>
        </div>

        <!-- CARGOS -->
        <div id="tpl-cargos">
            <div class="docs-hero-minimal">
                <h1>Entrada de Mercancía</h1>
                <p class="lead">Registra las compras a proveedores y abastece tus almacenes de forma controlada.</p>
            </div>

            <h2><i class="fas fa-truck-loading me-2"></i>El Proceso de "Cargo"</h2>
            <p>Un cargo es el documento legal que respalda la entrada física de productos a tu inventario.</p>

            <ol class="step-list">
                <li><span class="step-title">Origen</span> Selecciona el Proveedor y el Almacén de destino. Ten en cuenta que solo puedes elegir un almacén por cada cargo.</li>
                <li><span class="step-title">Conteo</span> Escanea o busca los productos recibidos. Ingresa la cantidad exacta que llegó físicamente.</li>
                <li><span class="step-title">Validación de Costos</span> Revisa que el costo unitario coincida con tu factura de compra. El sistema actualizará el "Costo Promedio" del producto.</li>
                <li><span class="step-title">Finalización</span> Al confirmar, el stock se suma inmediatamente y el costo se actualiza para los cálculos de ganancia.</li>
            </ol>

            <div class="docs-alert warning">
                Un cargo confirmado <strong>no se puede revertir</strong> directamente para mantener la integridad contable. Si hubo un error, deberás realizar un ajuste de inventario.
            </div>
        </div>

        <!-- ALMACENES -->
        <div id="tpl-almacenes">
            <div class="docs-hero-minimal">
                <h1>Gestión de Almacenes</h1>
                <p class="lead">Organiza tu inventario por ubicaciones físicas o virtuales.</p>
            </div>

            <h2><i class="fas fa-warehouse me-2"></i>Multi-Almacén</h2>
            <p>BuzzDM te permite separar el stock por sucursales, depósitos o incluso estantes virtuales.</p>

            <div class="docs-alert info">
                Puedes transferir mercancía entre almacenes sin afectar el costo total de tu inventario.
            </div>

            <ol class="step-list">
                <li><span class="step-title">Creación</span> Asigna un nombre claro (ej: "Depósito Norte" o "Tienda Principal").</li>
                <li><span class="step-title">Responsable</span> Puedes asignar un encargado para tener trazabilidad de quién administra cada ubicación.</li>
                <li><span class="step-title">Stock por Ubicación</span> Al consultar un producto, verás el desglose de cuántas unidades hay en cada almacén creado.</li>
            </ol>
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
                <h1>Colaboración Kanban</h1>
                <p class="lead">Administra tareas, recordatorios y pendientes de tu equipo de forma visual.</p>
            </div>

            <h2><i class="fas fa-columns me-2"></i>El Tablero de Trabajo</h2>
            <p>Usa las columnas para mover tarjetas según su progreso:</p>

            <ol class="step-list">
                <li><span class="step-title">Pendiente</span> Tareas nuevas o por iniciar.</li>
                <li><span class="step-title">En Proceso</span> Trabajo activo. Evita tener demasiadas notas aquí para no saturarte.</li>
                <li><span class="step-title">Terminado</span> Tareas concluidas. Límpialas periódicamente para mantener el orden.</li>
            </ol>

            <div class="docs-alert info">
                Puedes adjuntar fotos o documentos directamente en cada nota para tener toda la información en un solo lugar.
            </div>
        </div>

        <!-- BACKUPS -->
        <div id="tpl-backups">
            <div class="docs-hero-minimal">
                <h1>Respaldo y Seguridad</h1>
                <p class="lead">Protege la información de tu negocio ante cualquier eventualidad.</p>
            </div>

            <h2><i class="fas fa-shield-alt me-2"></i>Copias de Seguridad</h2>
            <p>El sistema permite generar archivos SQL que contienen toda tu base de datos.</p>

            <div class="docs-alert warning">
                Se recomienda generar un respaldo **al menos una vez por semana** y guardarlo fuera de este servidor (ej: Google Drive o un disco externo).
            </div>

            <ol class="step-list">
                <li><span class="step-title">Generación</span> Haz click en "Generar Nuevo Respaldo". El proceso puede tardar unos segundos.</li>
                <li><span class="step-title">Descarga</span> Una vez finalizado, verás un botón de descarga. Guárdalo con una fecha clara.</li>
                <li><span class="step-title">Restauración</span> En caso de emergencia, este archivo permite reconstruir toda tu base de datos desde cero.</li>
            </ol>
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
