/**
 * BUZZ Data Manager - Global Tutorial
 * Handles linear tutorial flow across multiple pages using Driver.js
 */

class TutorialManager {
    constructor() {
        this.driver = window.driver.js.driver({
            animate: true,
            opacity: 0.75,
            padding: 5,
            allowClose: true, // Restore 'X' button and ESC/Overlay functionality
            overlayColor: '#000', // Standard black shadow
            doneBtnText: 'Siguiente Módulo',
            closeBtnText: 'Salir',
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            popoverClass: 'driver-theme-buzz', // Custom Dark Theme
            onCloseClick: () => {
                this.endTutorial();
                this.driver.destroy();
            },
            onDestroyStarted: () => {
                 this.handleDestroy();
            }
        });

        // State keys
        this.STORAGE_KEY_ACTIVE = 'buzz_tutorial_active';
        this.STORAGE_KEY_STEP = 'buzz_tutorial_step'; // Index within current module
        this.STORAGE_KEY_MODULE = 'buzz_tutorial_module'; // Module name

        // Module Sequence
        this.modules = [
            'dashboard',
            'inventario',
            'catalogos',
            'almacenes',
            'proveedores',
            'clientes',
            'pedidos',
            'envios',
            'cargos',
            'notas',
            'importacion',
            'admin',
            'about'
        ];

        this.init();
    }

    init() {
        const isActive = localStorage.getItem(this.STORAGE_KEY_ACTIVE) === 'true';
        if (isActive) {
            // Slight delay to ensure DOM is ready
            setTimeout(() => this.checkAndRun(), 800);
        }

        // Listen for specific tutorial events
        window.addEventListener('buzz-tutorial-next-module', () => this.nextModule());
    }

    start() {
        localStorage.setItem(this.STORAGE_KEY_ACTIVE, 'true');
        localStorage.setItem(this.STORAGE_KEY_MODULE, 'dashboard');
        localStorage.setItem(this.STORAGE_KEY_STEP, '0');
        window.location.href = 'dashboard';
    }

    endTutorial() {
        localStorage.removeItem(this.STORAGE_KEY_ACTIVE);
        localStorage.removeItem(this.STORAGE_KEY_MODULE);
        localStorage.removeItem(this.STORAGE_KEY_STEP);
        // Optional: Notify user
        // alert('Tutorial finalizado.');
    }

    handleDestroy() {
        if(!this.driver.hasNextStep()) {
             // If we are at the end, next module logic handles it.
             // If user manually closed, endTutorial is called via onCloseClick
        }
    }

    checkAndRun() {
        const currentModule = localStorage.getItem(this.STORAGE_KEY_MODULE);
        // Robust URL parsing: Get filename without query params
        const currentPage = window.location.pathname.split('/').pop().split('?')[0];

        console.log(`Tutorial Check: Module ${currentModule} on Page ${currentPage}`);

        if (this.isCorrectPage(currentModule, currentPage)) {
            this.runModuleSteps(currentModule);
        } else {
            // Redirect if on wrong page
            const targetPage = this.getPageForModule(currentModule);
            if (targetPage && targetPage !== currentPage) {
                window.location.href = targetPage;
            }
        }
    }

    isCorrectPage(module, page) {
        // Support both .php and clean URLs during transition
        const pageClean = page.replace('.php', '');
        const map = {
            'dashboard': 'dashboard',
            'inventario': 'inventario',
            'catalogos': 'catalogos',
            'almacenes': 'almacenes',
            'proveedores': 'proveedores',
            'clientes': 'clientes',
            'pedidos': 'pedidos',
            'envios': 'envios',
            'cargos': 'cargos_existencias',
            'notas': 'todo',
            'importacion': 'importacion',
            'admin': 'admin',
            'config': 'company_settings',
            'about': 'about'
        };
        return map[module] === pageClean;
    }

    getPageForModule(module) {
        const map = {
            'dashboard': 'dashboard',
            'inventario': 'inventario',
            'catalogos': 'catalogos',
            'almacenes': 'almacenes',
            'proveedores': 'proveedores',
            'clientes': 'clientes',
            'pedidos': 'pedidos',
            'envios': 'envios',
            'cargos': 'cargos_existencias',
            'notas': 'todo',
            'importacion': 'importacion',
            'admin': 'admin',
            'config': 'company_settings',
            'about': 'about'
        };
        return map[module];
    }

    nextModule() {
        const currentModule = localStorage.getItem(this.STORAGE_KEY_MODULE);
        const currentIndex = this.modules.indexOf(currentModule);

        if (currentIndex < this.modules.length - 1) {
            const nextMod = this.modules[currentIndex + 1];
            localStorage.setItem(this.STORAGE_KEY_MODULE, nextMod);
            localStorage.setItem(this.STORAGE_KEY_STEP, '0');

            const nextUrl = this.getPageForModule(nextMod);
            window.location.href = nextUrl;
        } else {
            // End of tutorial sequence
            this.endTutorial();
            this.driver.destroy();
            // Final completion logic if needed
        }
    }

    runModuleSteps(module) {
        // Pre-load confetti if about module
        if (module === 'about') {
            this.loadConfetti();
        }

        const steps = this.getSteps(module);
        if (!steps || steps.length === 0) return;

        this.driver.setSteps(steps);
        this.driver.drive(0);
    }

    loadConfetti() {
        if (document.getElementById('confetti-script')) return;
        const script = document.createElement('script');
        script.id = 'confetti-script';
        script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js';
        document.head.appendChild(script);
    }

    triggerCelebration() {
        if (window.confetti) {
            var duration = 3 * 1000;
            var animationEnd = Date.now() + duration;
            var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 99999 }; // Higher z-index for driver

            var interval = setInterval(function() {
                var timeLeft = animationEnd - Date.now();

                if (timeLeft <= 0) {
                    return clearInterval(interval);
                }

                var particleCount = 50 * (timeLeft / duration);
                // since particles fall down, start a bit higher than random
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
            }, 250);

            function randomInRange(min, max) {
                return Math.random() * (max - min) + min;
            }
        }
    }

    getSteps(module) {
        switch (module) {
             case 'dashboard':
                return [
                    {
                        element: '.top-navbar',
                        popover: {
                            title: 'Bienvenido al Centro de Comando',
                            description: 'Aquí comienza tu aprendizaje de BuzzDM. Esto es el Dashboard, tu vista general.',
                            side: 'bottom', align: 'start'
                        }
                    },
                    {
                        element: '#salesChart',
                        popover: { title: 'Métricas Clave', description: 'Visualiza rápidamente el rendimiento con indicadores en tiempo real.', side: 'bottom' }
                    },
                    {
                        element: '.chart-period-tabs',
                        popover: { title: 'Periodos de Tiempo', description: 'Filtra las estadísticas por día, semana, mes o año.', side: 'left' }
                    },
                    {
                        element: '#paymentStatusChart',
                        popover: { title: 'Estado de Pagos', description: 'Gráfico detallado de tus ventas pagadas vs. pendientes.', side: 'top' }
                    },
                     {
                        element: '#topProducts', // Target the inner list for precision
                        popover: { title: 'Top 5 Productos', description: 'Tus artículos más vendidos este mes.', side: 'left' }
                    },
                     {
                        element: '#lowStockProducts', // Target the Low Stock List container
                        popover: { title: 'Alertas de Stock', description: 'Productos críticos que requieren reabastecimiento inmediato.', side: 'left' }
                    },
                    {
                        element: '.currency-widget', // Target currency widget
                        popover: { title: 'Monitor de Divisas', description: 'Tasas de cambio actualizadas en tiempo real (BCV, Paralelo).', side: 'left', onNextClick: () => this.nextModule() }
                    }
                ];

            // Placeholder for other modules - will fill as we verify IDs
             case 'inventario':
                return [
                    {
                        element: '.filter-wrapper',
                        popover: {
                            title: 'Filtros y Búsqueda',
                            description: 'Encuentra productos por SKU, nombre, categoría o estado. Usa los filtros avanzados.',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#showDeletedFilter',
                        popover: { title: 'Ver Eliminados', description: 'Permite visualizar productos que han sido eliminados del sistema (Papelera de reciclaje).', side: 'top' }
                    },
                    {
                        element: '.table-buzz',
                        popover: {
                            title: 'Gestión de Productos',
                            description: 'Lista de inventario. Aquí puedes ver el stock, precios y estado de cada ítem.',
                            side: 'top',
                            align: 'start'
                        }
                    },
                     {
                        element: 'table thead tr th:last-child', // Corrected selector for Action Column
                        popover: {
                             title: 'Acciones Rápidas',
                             description: 'Usa los botones de esta columna para Editar (Lápiz) o Eliminar (Basura) un producto.',
                             side: 'left'
                        }
                    },
                    {
                        element: '#btnNewProduct',
                        popover: {
                            title: 'Nuevo Producto',
                            description: 'Haz click aquí para registrar un nuevo ítem en tu inventario.',
                            side: 'bottom',
                            onNextClick: () => {
                                if(window.openProductModal) {
                                    window.openProductModal();
                                    setTimeout(() => this.driver.moveNext(), 400);
                                } else {
                                    this.driver.moveNext();
                                }
                            }
                        }
                    },
                    {
                        element: '#productSku',
                        popover: { title: 'SKU y Nombre', description: 'El código único y nombre son obligatorios. El sistema valida duplicados.', side: 'right' }
                    },
                    {
                        element: '#productTipoProducto',
                        popover: {
                            title: 'Tipo de Producto',
                            description: '<ul><li><b>Físico:</b> Controla stock (zapatos, ropa).</li><li><b>Servicio:</b> Intangible, sin stock (envío, garantía).</li><li><b>Kit:</b> Conjunto de otros productos.</li></ul>',
                            side: 'right'
                        }
                    },
                    {
                        element: '#productStock',
                        popover: { title: 'Control de Stock', description: 'Define el stock actual y el mínimo para alertas.', side: 'top' }
                    },
                    {
                        element: '#productsPagination',
                        popover: { title: 'Navegación', description: 'Usa la paginación para moverte entre listas largas de productos.', side: 'top', onNextClick: () => this.nextModule() }
                    }
                ];

            case 'catalogos':
                return [
                    {
                        element: '.catalogs-grid',
                        popover: { title: 'Catálogos Maestros', description: 'Aquí defines las propiedades base de tus productos: Categorías, Marcas, Colores y Materiales.', side: 'top' }
                    },
                    {
                        element: '[data-catalog="categorias"]',
                        popover: { title: 'Gestión por Tarjetas', description: 'Cada sección funciona independientemente. Puedes expandir/colapsar y ver el conteo de items.', side: 'right' }
                    },
                    {
                        element: '[data-catalog="categorias"] .btn-add-sm',
                        popover: {
                            title: 'Agregar Item',
                            description: 'Usa este botón pequeño en cada tarjeta para crear nuevos elementos rápidamente.',
                            side: 'bottom',
                            onNextClick: () => this.nextModule()
                        }
                    }
                ];
             case 'almacenes':
                return [
                   {
                        element: '.row.mb-4', // Target the entire row of stats
                        popover: { title: 'Resumen de Almacenes', description: 'Vista global de almacenes activos, ubicaciones totales y ocupación.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewAlmacen',
                        popover: { title: 'Nuevo Almacén', description: 'Registra depósitos físicos o virtuales para organizar tu stock.', side: 'bottom' }
                    },
                    {
                        element: '#almacenesGrid',
                        popover: { title: 'Gestión de Ubicaciones', description: 'Haz click en cada tarjeta para ver el inventario detallado por ubicación.', side: 'top', onNextClick: () => this.nextModule() }
                    }
                ];
             case 'proveedores':
                return [
                    {
                        element: '#btnNewProveedor',
                        popover: { title: 'Gestión de Proveedores', description: 'Centraliza la información de tus aliados comerciales para agilizar las órdenes de compra.', side: 'bottom' }
                    },
                    {
                        element: '#proveedoresContainer',
                        popover: { title: 'Directorio', description: 'Aquí verás la lista completa de tus proveedores.', side: 'top' }
                    },
                     {
                         element: '#proveedoresContainer', // Fallback to container to avoid errors if no rows
                         popover: {
                            title: 'Acciones Rápidas',
                            description: 'En cada fila encontrarás botones para **Editar** información o **Eliminar** el proveedor.',
                            side: 'top',
                            onNextClick: () => this.nextModule()
                        }
                    }
                ];
             case 'clientes':
                return [
                    {
                        element: '.frequent-customers-banner',
                        popover: { title: 'Clientes VIP', description: 'Acceso rápido a tus clientes más frecuentes.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewCliente',
                        popover: {
                            title: 'Nuevo Cliente',
                            description: 'Registra los datos básicos. <b>Importante:</b> Asegúrate de incluir la dirección exacta para agilizar los envíos.',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '.filter-wrapper', // Generalizing filters
                        popover: {
                            title: 'Filtros y Auditoría',
                            description: 'Encuentra clientes por tipo, fecha o estado. Activa "Ver inactivos" para consultar registros eliminados.',
                            side: 'bottom'
                        }
                    },
                    {
                        element: '#customersList', // Targeting list container
                        popover: {
                             title: 'Gestión de Clientes',
                             description: 'Visualiza tu cartera de clientes. Usa los botones de acción en cada fila (Ver, Editar, Eliminar) para gestionarlos.',
                             side: 'top',
                             onNextClick: () => this.nextModule()
                        }
                    }
                ];
            case 'pedidos':
                return [
                    {
                        element: '#newOrderBtn', // Ensure ID is correct in pedidos.php
                        popover: {
                            title: 'Crear Pedido',
                            description: 'Inicia una nueva venta. Haz click para ver el formulario.',
                            side: 'bottom',
                            onNextClick: () => {
                                if(window.openCreateOrderModal) {
                                    window.openCreateOrderModal();
                                    setTimeout(() => this.driver.moveNext(), 500);
                                } else {
                                     // Fallback if global function not found, try bootstrap way or just move next
                                     const btn = document.getElementById('newOrderBtn');
                                     if(btn) btn.click();
                                     setTimeout(() => this.driver.moveNext(), 500);
                                }
                            }
                        }
                    },
                    {
                        element: '#createOrderClientSearch',
                        popover: { title: 'Asignar Cliente', description: 'Busca y selecciona un cliente existente o crea uno rápido.', side: 'right' }
                    },
                    {
                         element: '#createOrderProductsContainer',
                         popover: { title: 'Agregar Productos', description: 'Aquí aparecerán los productos. Usa el botón "Añadir Producto" para buscar y cargar items al pedido.', side: 'top' }
                    },
                    {
                        element: '#createOrderShippingAddress',
                         popover: {
                             title: 'Detalles de Envío',
                             description: 'Confirma la dirección y notas de entrega. (Cerraremos el modal ahora).',
                             side: 'left',
                             onNextClick: () => {
                                 if(window.closeModal) window.closeModal('createOrderModal');
                                 setTimeout(() => this.driver.moveNext(), 400);
                             }
                        }
                    },
                    {
                        element: '.filter-wrapper',
                        popover: { title: 'Filtros y Estados', description: 'Filtra pedidos por fecha, estado de pago o estado de envío.', side: 'bottom' }
                    },
                    {
                         element: '#ordersList .dropdown-toggle', // Target dropdown in list
                         popover: { title: 'Acciones de Pedido', description: 'Usa el menú desplegable en cada fila para: Ver Detalle, Editar, Abonar deuda o Anular.', side: 'left' }
                    },

                    {
                        // Added step for element details
                        element: '#ordersList',
                        popover: {
                            title: 'Contenido del Pedido',
                            description: 'Despliega los detalles o usa la vista completa para revisar los items (productos, cantidad, precio).',
                            side: 'top'
                        }
                    },
                    {
                        element: '#ordersList',
                        popover: {
                            title: 'Flujo de Trabajo',
                            description: '<ul><li><b>Ver Pedido:</b> Haz click en el icono de ojo.</li><li><b>Pago -> Envío:</b> Cuando marcas un pedido como "Pagado", pasa automáticamente a la lista de "Envíos Pendientes".</li></ul>',
                            side: 'top',
                            onNextClick: () => this.nextModule()
                        }
                    }
                ];
             case 'envios':
                return [
                    {
                        element: '.stats-card',
                        popover: { title: 'Panel de Envíos', description: 'Monitoreo de KPI logísticos. Los envíos se generan automáticamente desde los Pedidos.', side: 'bottom' }
                    },
                    {
                        element: '#statusFilter',
                        popover: { title: 'Tracking', description: 'Filtra por estado para actualizar el seguimiento de paquetes.', side: 'bottom', onNextClick: () => this.nextModule() }
                    }
                ];
             case 'cargos': // Match key in isCorrectPage map
                return [
                    {
                        element: '.top-navbar',
                        popover: { title: 'Entrada de Inventario', description: 'Aquí registras cualquier ingreso de mercancía: Compras, Ajustes o Devoluciones.', side: 'bottom' }
                    },
                     {
                        element: '#btnVerHistorial',
                        popover: { title: 'Historial', description: 'Consulta movimientos pasados y anulaciones aquí.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewCargo',
                        popover: {
                            title: 'Crear Movimiento',
                            description: 'Haz click aquí para registrar una nueva entrada de stock.',
                            side: 'bottom',
                            onNextClick: () => {
                                // Simulate click to open form
                                const btn = document.getElementById('btnNewCargo');
                                if(btn) btn.click();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    {
                        element: '#cargoConcepto',
                        popover: { title: 'Concepto', description: 'Define la razón del ingreso: "Compra" (a proveedor), "Ajuste Positivo" (corrección), etc.', side: 'right' }
                    },
                    {
                        element: '#cargoReferencia',
                        popover: { title: 'Referencia / Factura', description: 'Ingresa el número de factura del proveedor. Este campo es puramente informativo para tu seguimiento.', side: 'left' }
                    },
                    {
                         element: '#btnAddProductLine', // Point to the button that adds products
                         popover: { title: 'Buscador de Productos', description: 'Usa este botón para buscar y añadir los ítems que están ingresando.', side: 'top' }
                    },
                   {
                        element: '#btnConfirmar',
                        popover: { title: 'Finalizar', description: 'Guarda y confirma el movimiento para actualizar el stock inmediatamente.', side: 'top', onNextClick: () => this.nextModule() }
                    }
                ];
             case 'notas':
                return [
                    {
                        element: '#kanbanBoard',
                        popover: { title: 'Tablero Kanban', description: 'Gestiona tareas visualmente. Arrastra y suelta tarjetas entre columnas (Pendiente -> Seguimiento -> Resuelto) para actualizar su estado.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewNote',
                        popover: {
                            title: 'Nueva Tarea',
                            description: 'Crea recordatorios o seguimientos. Puedes vincularlos a clientes específicos.',
                            side: 'bottom',
                            onNextClick: () => {
                                // Simulate open modal
                                if(window.openNewNoteModal) {
                                    window.openNewNoteModal();
                                    setTimeout(() => this.driver.moveNext(), 500);
                                }
                            }
                        }
                    },
                    {
                        element: '#noteForm', // Focus on form
                        popover: { title: 'Detalles de la Nota', description: 'Aquí defines el contenido. Notas: Los productos y archivos se agregan mediante enlaces en la descripción.', side: 'right' }
                    },
                    {
                        element: '.modal-tools-top',
                        popover: { title: 'Herramientas', description: '<ul><li><b>Etiquetas:</b> Clasifica la nota (Urgente, Venta, Soporte).</li><li><b>Miembros:</b> Asigna usuarios (ej: Almacenero) para que reciban la tarea.</li></ul>', side: 'bottom' }
                    },
                     {
                        element: '#notePriority',
                        popover: {
                            title: 'Prioridad',
                            description: 'Define la importancia. Las notas de prioridad Alta se destacan visualmente.',
                            side: 'left',
                            onNextClick: () => {
                                if(window.closeNoteModal) window.closeNoteModal();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    {
                        element: '.kanban-card:first-child', // Target first card if exists, or board
                        popover: { title: 'Edición', description: 'Haz click en cualquier tarjeta existente para ver sus detalles, editarla o añadir comentarios.', side: 'top', onNextClick: () => this.nextModule() }
                    }
                ];
             case 'importacion':
                return [
                    {
                        element: '.top-navbar',
                        popover: { title: 'Importación Masiva', description: 'Herramienta para cargar inventario inicial o actualizaciones grandes desde Excel.', side: 'bottom' }
                    },
                    {
                        element: '.upload-area',
                        popover: { title: 'Zona de Carga', description: 'Arrastra tu archivo Excel (.xlsx) aquí. Asegúrate de usar la plantilla oficial.', side: 'bottom' }
                    },
                    {
                        element: '.btn-group', // Target the button group for templates
                        popover: { title: 'Plantillas', description: 'Descarga el formato Excel requerido aquí (Productos, Clientes, etc). No modifiques los encabezados.', side: 'left' }
                    },
                     {
                        element: '#importHistoryTable',
                        popover: { title: 'Historial', description: 'Verifica el estado de tus importaciones anteriores (Exitoso/Errores).', side: 'top', onNextClick: () => this.nextModule() }
                    }
                ];
             case 'admin':
                return [
                    // --- Users Tab ---
                    {
                        element: '[data-tab="users"]',
                        popover: {
                            title: 'Gestión de Usuarios',
                            description: 'Controla quién accede al sistema.',
                            side: 'bottom',
                            onNextClick: () => {
                                document.querySelector('[data-tab="users"]').click();
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#newUserBtn',
                        popover: { title: 'Crear Usuario', description: 'Registra nuevos operadores o administradores.', side: 'bottom' }
                    },
                    {
                        element: '#usersTable',
                        popover: { title: 'Estado y Bloqueo', description: 'Verás si un usuario está Activo o Inactivo. Puedes bloquear el acceso inmediatamente desde las acciones.', side: 'top' }
                    },

                    // --- Logs Tab ---
                     {
                        element: '[data-tab="logs"]',
                        popover: {
                            title: 'Auditoría (Logs)',
                            description: 'Registro de seguridad de todas las acciones importantes.',
                            side: 'bottom',
                             onNextClick: () => {
                                document.querySelector('[data-tab="logs"]').click();
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#logsTable',
                        popover: { title: 'Registro de Actividad', description: 'Monitorea quién hizo qué y cuándo. Útil para seguridad.', side: 'top' }
                    },

                    // --- Config Tab ---
                    {
                        element: '[data-tab="settings"]',
                        popover: {
                            title: 'Configuración',
                            description: 'Personaliza tu empresa, logo y seguridad (2FA).',
                            side: 'bottom',
                            onNextClick: () => {
                                // Switch to settings tab
                                const tab = document.querySelector('[data-tab="settings"]');
                                if(tab) tab.click();
                                setTimeout(() => this.driver.moveNext(), 300);
                            }
                        }
                    },
                     {
                          element: '#companyInfoForm',
                          popover: { title: 'Datos de Empresa', description: 'Define datos fiscales y de contacto. Esta información aparecerá automáticamente en el <b>encabezado de los reportes</b> y documentos.', side: 'top' }
                     },
                     {
                          element: '#logoUploadArea',
                          popover: { title: 'Logo', description: 'Sube tu logo en **SVG**. Esto garantiza la mejor calidad en todas las pantallas.', side: 'left' }
                     },
                     {
                         element: '#twoFactorDisabledArea', // Target the specific area or button
                         popover: { title: 'Seguridad 2FA', description: 'Protege tu cuenta activando la verificación en dos pasos. Escanea el QR con Google Authenticator.', side: 'top' }
                     },
                     {
                         element: 'select[name="session_timeout"]',
                         popover: { title: 'Timeout', description: 'Define el tiempo de inactividad antes de cerrar la sesión automáticamente.', side: 'top' }
                     },

                      // --- Reports Tab ---
                     {
                         element: '[data-tab="reports"]',
                         popover: {
                             title: 'Reportes',
                             description: 'Genera informes de sistema en PDF y Excel.',
                             side: 'bottom',
                             onNextClick: () => {
                                 const tab = document.querySelector('[data-tab="reports"]');
                                 if(tab) tab.click();
                                 setTimeout(() => this.driver.moveNext(), 300);
                             }
                         }
                     },
                     {
                         element: '#adminPasswordReport',
                         popover: { title: 'Seguridad', description: '⚠️ Requerido: Ingrese su contraseña de administrador antes de solicitar cualquier reporte.', side: 'left' }
                     },
                      {
                         element: '#reportOptionsContainer',
                         popover: { title: 'Tipos de Reporte', description: 'Seleccione entre Ventas, Inventario, Clientes y más.', side: 'top' }
                     },

                     // --- Backups Tab ---
                     {
                         element: '[data-tab="backups"]',
                         popover: {
                             title: 'Respaldos (Backups)',
                             description: 'Gestión crítica de la base de datos.',
                             side: 'bottom',
                             onNextClick: () => {
                                 const tab = document.querySelector('[data-tab="backups"]');
                                 if(tab) tab.click();
                                 setTimeout(() => this.driver.moveNext(), 300);
                             }
                         }
                     },
                     {
                         element: 'button[onclick="createBackup()"]',
                         popover: { title: 'Backup Manual', description: 'Cree una copia manual instantánea aquí.', side: 'bottom' }
                     },
                     {
                          element: '#autoBackupContainer', // Fallback to container if switch logic is complex
                          popover: { title: 'Backups Automáticos', description: 'Active esta opción para copias diarias. Las copias se guardan en el servidor.', side: 'right' }
                     },
                     {
                         element: '#backupsTable', // Target table for restore example
                         popover: { title: 'Restauración', description: '⚠️ ADVERTENCIA: En la lista de backups, usa el botón "Restaurar" para volver a un punto anterior. Esto **sobrescribe** toda la información.', side: 'top', onNextClick: () => this.nextModule() }
                     }
                 ];

             case 'about':
                return [
                    {
                        element: '.about-header',
                        popover: {
                            title: '¡Felicidades! 🎉',
                            description: 'Has completado el recorrido base de BuzzDM. Ahora estás listo para operar el sistema.',
                            side: 'bottom',
                            onPopoverRender: () => {
                                // Trigger confetti when this step is shown
                                setTimeout(() => this.triggerCelebration(), 500);
                            }
                        }
                    },
                    {
                        element: '.manual-btn',
                        popover: {
                            title: 'Manual de Usuario',
                            description: 'Si tienes dudas específicas sobre algún módulo, haz click aquí para consultar la documentación detallada o busca el botón de Ayuda en el sistema.',
                            side: 'top',
                            onNextClick: () => this.nextModule()
                        }
                    }
                ];

            default:
                return [];
        }
    }
}

// Global Instance
window.tutorialManager = new TutorialManager();
