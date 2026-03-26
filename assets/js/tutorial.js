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
            allowClose: true,
            overlayColor: '#000',
            stagePadding: 4,
            overlayClickable: true,
            showProgress: true,
            keyboardControl: true, // Enables Left/Right arrow navigation
            doneBtnText: 'Siguiente Módulo',
            closeBtnText: 'Salir',
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            popoverClass: 'driver-theme-buzz',
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
            'cargos',
            'pedidos',
            'requisiciones',
            'transferencias',
            'envios',
            'notas',
            'importacion',
            'ajustes',
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
            'cargos': 'cargos_existencias',
            'pedidos': 'pedidos',
            'requisiciones': 'requisiciones',
            'transferencias': 'transferencias',
            'envios': 'envios',
            'notas': 'todo',
            'importacion': 'importacion',
            'ajustes': 'ajustes',
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
            'cargos': 'cargos_existencias',
            'pedidos': 'pedidos',
            'requisiciones': 'requisiciones',
            'transferencias': 'transferencias',
            'envios': 'envios',
            'notas': 'todo',
            'importacion': 'importacion',
            'ajustes': 'ajustes',
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
                        element: '.col-lg-8 .buzz-card.chart-container',
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
                        element: '#topProductsCard', // Target the card for precision
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
                        popover: { title: 'Ver Eliminados', description: 'Permite visualizar productos que han sido eliminados del sistema (Papelera de reciclaje).', side: 'top' },
                        onHighlightStarted: () => {
                            const btn = document.getElementById('toggleAdvancedFilters');
                            if (btn && !btn.classList.contains('active')) btn.click();
                        }
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
                            description: 'Haz click para crear una nueva categoría.',
                            side: 'bottom',
                            onNextClick: () => {
                                document.querySelector('[data-catalog="categorias"] .btn-add-sm').click();
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#itemName',
                        popover: { title: 'Nombre', description: 'Nombre claro y descriptivo del elemento. Ej: "Electrónica".', side: 'right' }
                    },
                    {
                        element: '.field-desc',
                        popover: { title: 'Descripción', description: 'Detalle adicional sobre esta categoría o marca.', side: 'right' }
                    },
                    {
                        element: '[data-catalog="categorias"] .catalog-card-body',
                        popover: { title: 'Elemento Guardado', description: 'Una vez guardado, aparecerá aquí como un chip o tarjeta para uso rápido.', side: 'top', onNextClick: () => {
                            if (typeof closeItemModal === 'function') closeItemModal();
                            this.nextModule();
                        }}
                    }
                ];
             case 'almacenes':
                return [
                   {
                        element: '.row.mb-4',
                        popover: { title: 'Resumen de Almacenes', description: 'Vista global de almacenes activos, ubicaciones totales y ocupación.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewAlmacen',
                        popover: { 
                            title: 'Nuevo Almacén', 
                            description: 'Haz click aquí para registrar depósitos físicos o virtuales.', 
                            side: 'bottom',
                            onNextClick: () => {
                                if(typeof openAlmacenModal === 'function') openAlmacenModal();
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#almacenNombre',
                        popover: { title: 'Nombre del Almacén', description: 'Asigna un nombre reconocible (Ej: "Principal", "Depósito Sur").', side: 'right' }
                    },
                    {
                        element: '#almacenTipo',
                        popover: { title: 'Tipo de Espacio', description: 'Clasifica si es una tienda, bodega o depósito temporal.', side: 'right' }
                    },
                    {
                        element: '#almacenPrincipalCheck',
                        popover: { title: 'Almacén Principal', description: 'Márcalo para convertirlo en el destino por defecto de las compras nuevas.', side: 'right' }
                    },
                    {
                        element: '#almacenesGrid',
                        popover: { 
                            title: 'Gestión de Ubicaciones', 
                            description: 'Al guardar, se mostrará aquí. Puedes entrar a cada almacén para configurar pasillos y estantes (ubicaciones).', 
                            side: 'top',
                            onNextClick: () => {
                                if(typeof closeAlmacenModal === 'function') closeAlmacenModal();
                                this.nextModule();
                            }
                        }
                    }
                ];
             case 'proveedores':
                return [
                    {
                        element: '#btnNewProveedor',
                        popover: { 
                            title: 'Gestión de Proveedores', 
                            description: 'Haz click para registrar un aliado comercial y agilizar las futuras compras.', 
                            side: 'bottom',
                            onNextClick: () => {
                                if(typeof abrirModalProveedor === 'function') abrirModalProveedor();
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#proveedorNombre',
                        popover: { title: 'Nombre', description: 'Razón social o nombre comercial del proveedor.', side: 'right' }
                    },
                    {
                        element: '#proveedorRif',
                        popover: { title: 'Identificación', description: 'Ingresa el RIF completo (Ej: J-12345678-9).', side: 'right' }
                    },
                    {
                        element: '#proveedorTelefono',
                        popover: { title: 'Contacto Principal', description: 'Número telefónico, no olvides verificar el código de país.', side: 'right' }
                    },
                    {
                        element: '#proveedorEmail',
                        popover: { title: 'Correo Electrónico', description: 'Se enviarán las órdenes de compra a esta dirección.', side: 'right' }
                    },
                    {
                         element: '#proveedoresContainer',
                         popover: {
                            title: 'Directorio',
                            description: 'Aquí verás las tarjetas de tus proveedores. Podrás consultar el histórico de precios (botón "Productos") que te han ofrecido.',
                            side: 'top',
                            onNextClick: () => {
                                if(typeof cerrarModalProveedor === 'function') cerrarModalProveedor();
                                this.nextModule();
                            }
                        }
                    }
                ];
             case 'clientes':
                return [
                    {
                        element: '.frequent-customers-banner',
                        popover: { title: 'Clientes VIP', description: 'Acceso rápido e inteligente a los clientes que más te compran.', side: 'bottom' }
                    },
                    {
                        element: '#btnNewCliente',
                        popover: {
                            title: 'Nuevo Cliente',
                            description: 'Haz click para registrar un cliente en la base de datos central.',
                            side: 'bottom',
                            onNextClick: () => {
                                if(typeof openModal === 'function') openModal('createCustomerModal');
                                setTimeout(() => this.driver.moveNext(), 400);
                            }
                        }
                    },
                    {
                        element: '#createCustomerId',
                        popover: { title: 'ID Cliente', description: 'Cédula o RIF del cliente. Esencial para la facturación.', side: 'right' }
                    },
                    {
                        element: '#createCustomerName',
                        popover: { title: 'Nombre', description: 'Nombre representativo para las guías de envío.', side: 'right' }
                    },
                    {
                        element: '#createCustomerPhone',
                        popover: { title: 'WhatsApp o Teléfono', description: 'Fundamental para coordinar entregas.', side: 'right' }
                    },
                    {
                        element: '#createCustomerEmail',
                        popover: { title: 'Email', description: 'Destino automático de facturas y notificaciones.', side: 'right' }
                    },
                    {
                        element: '#customersList',
                        popover: {
                             title: 'Gestión Completa',
                             description: 'En el listado podrás editar, eliminar o ver el historial completo de pedidos de cada cliente.',
                             side: 'top',
                             onNextClick: () => {
                                if(typeof closeModal === 'function') closeModal('createCustomerModal');
                                this.nextModule();
                             }
                        }
                    }
                ];
            case 'pedidos':
                return [
                    {
                        element: '#newOrderBtn',
                        popover: {
                            title: 'Crear Pedido',
                            description: 'Inicia una nueva venta. Haz click para abrir el formulario.',
                            side: 'bottom',
                            onNextClick: () => {
                                const btn = document.getElementById('newOrderBtn');
                                if(btn) btn.click();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    { element: '#createOrderClientSearch', popover: { title: '1. Cliente', description: 'Escribe para buscar por nombre o cédula. Si no existe, puedes crearlo.', side: 'right' } },
                    { element: '#createOrderDate', popover: { title: '2. Fecha', description: 'Fecha de emisión del pedido. Por defecto es hoy.', side: 'left' } },
                    { element: '#createOrderShippingAddress', popover: { title: '3. Dirección', description: 'Fundamental para la logística de envíos. Si elegiste cliente frecuente, se auto-completa.', side: 'right' } },
                    { element: '#createOrderShippingNotes', popover: { title: '4. Notas de Envío', description: 'Instrucciones especiales para el repartidor (Ej: Dejar en portería).', side: 'right' } },
                    { element: '#createOrderProductsContainer', popover: { title: '5. Productos', description: 'Lista de items a comprar.', side: 'top' } },
                    { element: '#addCreateOrderProductRow', popover: { title: 'Añadir Producto', description: 'Agrega un nuevo renglón para buscar otro producto.', side: 'top' } },
                    { element: '#createOrderPaymentStatus', popover: { title: 'Estado de Pago', description: 'Registra si el anticipo/total ya fue recibido.', side: 'top' } },
                    { element: '#createOrderShippingStatus', popover: { title: 'Estado de Envío', description: 'Elige Pendiente, Enviado o Recibido. Al guardar, pasará al panel de Envíos.', side: 'top', onNextClick: () => {
                        const modalEl = document.getElementById('createOrderModal');
                        if (modalEl) {
                           const modalInstance = bootstrap.Modal.getInstance(modalEl);
                           if (modalInstance) modalInstance.hide();
                        }
                        setTimeout(() => this.driver.moveNext(), 500);
                    }} },
                    { element: '#ordersList', popover: { title: 'Flujo de Trabajo', description: 'Aquí verás todo. Usa el menú de la fila para abonar deuda, enviar o facturar.', side: 'top', align: 'center', onNextClick: () => this.nextModule() } }
                ];
             case 'requisiciones':
                return [
                    {
                        element: '.top-navbar',
                        popover: { title: 'Requisiciones', description: 'Gestión de solicitudes de abastecimiento internas hacia el área de compras.', side: 'bottom' }
                    },
                    {
                        element: '#btnFromLowStock',
                        popover: { title: 'Auto-Stock Inteligente', description: 'El sistema analiza el stock crítico y te propone qué comprar automáticamente.', side: 'bottom' }
                    },
                    {
                        element: '#btnNuevaRequisicion',
                        popover: {
                            title: 'Nueva Requisición',
                            description: 'Crea una solicitud manualmente.',
                            side: 'bottom',
                            onNextClick: () => {
                                const btn = document.getElementById('btnNuevaRequisicion');
                                if(btn) btn.click();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    { element: '#prioridadRequisicion', popover: { title: '1. Prioridad', description: 'Urgente, Normal o Baja.', side: 'right' } },
                    { element: '#fechaRequisicion', popover: { title: '2. Fecha', description: 'Fecha de solicitud de la requisición.', side: 'right' } },
                    { element: '#btnAddLinea', popover: { title: '3. Añadir Ítems', description: 'Agrega las referencias que faltan. El sistema vinculará cada ítem con su proveedor habitual.', side: 'top' } },
                    { element: '#resumenProveedores', popover: { title: 'Consolidado', description: 'Cálculo de inversión por proveedor.', side: 'left' } },
                    { element: '#btnCancelar', popover: { title: 'Guardar y Acciones', description: 'Usa **Guardar** para confirmar la requisición, o **Volver** para cancelar. Si eres supervisor, podrás **Aprobar** directamente.', side: 'top', onNextClick: () => {
                         // Return to history view before moving to next module
                         const btnCancelar = document.getElementById('btnCancelar');
                         if (btnCancelar) btnCancelar.click();
                         setTimeout(() => this.nextModule(), 300);
                    } } }
                ];
             case 'transferencias':
                return [
                    {
                        element: '.top-navbar',
                        popover: { title: 'Transferencias', description: 'Movimiento interno de productos entre unidades de negocio.', side: 'bottom' }
                    },
                    {
                        element: '#btnNueva',
                        popover: {
                            title: 'Iniciar Traslado',
                            description: 'Haz click para agendar un movimiento físico de stock.',
                            side: 'bottom',
                            onNextClick: () => {
                                const btn = document.getElementById('btnNueva');
                                if(btn) btn.click();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    { element: '#almacenOrigen', popover: { title: '1. Origen', description: 'Almacén de donde saldrá la mercancía (se descuenta el stock).', side: 'right' } },
                    { element: '#almacenDestino', popover: { title: '2. Destino', description: 'Almacén que recibirá. El stock no ingresa hasta que se confirme la recepción.', side: 'right' } },
                    { element: '#lineasProductosContainer', popover: { title: '3. Ítems', description: 'Agrega los productos y las cantidades exactas a despachar usando el botón "Añadir Producto".', side: 'top' } },
                    { element: '#btnEnviar', popover: { title: 'Procesar', description: 'Pondrá la mercancía "En Tránsito" y emitirá una guía.', side: 'top', onNextClick: () => {
                        // Return to history view
                        if (typeof cancelarFormulario === 'function') cancelarFormulario();
                        setTimeout(() => this.nextModule(), 300);
                    } } }
                ];

             case 'envios':
                return [
                    {
                        element: '.filter-wrapper',
                        popover: { title: 'Panel de Envíos', description: 'Filtra y busca los envíos generados automáticamente desde los Pedidos.', side: 'bottom' }
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
                                const btn = document.getElementById('btnNewCargo');
                                if(btn) btn.click();
                                setTimeout(() => this.driver.moveNext(), 500);
                            }
                        }
                    },
                    {
                        element: '#cargoConcepto',
                        popover: { 
                            title: '1. Concepto', 
                            description: '<ul><li><b>Compra a Proveedor:</b> Suma stock y registra costo.</li><li><b>Ajuste Positivo:</b> Por sobrante de inventario.</li><li><b>Devolución:</b> Cliente retorna producto.</li></ul>', 
                            side: 'right',
                            onHighlightStarted: () => {
                                const sel = document.getElementById('cargoConcepto');
                                if (sel) {
                                    sel.focus();
                                    // Visual cue
                                    sel.style.boxShadow = "0 0 10px rgba(255,160,0,0.5)";
                                    setTimeout(() => sel.style.boxShadow = "", 2000);
                                }
                            }
                        }
                    },
                    {
                        element: '#proveedorBusqueda',
                        popover: { title: '2. Proveedor', description: '(Opcional/Requerido para compras). Vincula a quién le compraste para el historial.', side: 'left' }
                    },
                    {
                         element: '#btnAddProductLine',
                         popover: { title: '3. Ítems', description: 'Busca los productos. Recuerda ingresar el "Precio de Compra" si seleccionaste el concepto de Compra a Proveedor.', side: 'top' }
                    },
                    {
                        element: '#btnConfirmar',
                        popover: { title: 'Diferencia de Botones', description: '<ul><li><b>Guardar Borrador:</b> Guarda pero NO suma stock aún.</li><li><b>Confirmar:</b> Finaliza y suma el stock inmediatamente.</li></ul>', side: 'top', onNextClick: () => {
                            const modal = document.getElementById('modalCargo');
                            if (modal) modal.style.display = 'none';
                            this.nextModule();
                        } }
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
             case 'ajustes':
                return [
                    {
                        element: '.top-navbar',
                        popover: { title: 'Ajustes de Cuenta', description: 'Opciones de seguridad personal y preferencias de sesión del usuario.', side: 'bottom' }
                    },
                    {
                        element: '.col-lg-7 .buzz-card',
                        popover: { title: 'Seguridad 2FA y Timeout', description: 'Protege tu cuenta activando la verificación en dos pasos (Google Authenticator) y define el tiempo máximo de inactividad antes de cerrar tu sesión.', side: 'top' }
                    },
                     {
                        element: '.col-lg-5 .buzz-card',
                        popover: { title: 'Estado del Servidor', description: 'Revisa de un vistazo cómo está respondiendo tu hosting (versión PHP, disco, memoria).', side: 'left', onNextClick: () => this.nextModule() }
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

                     // --- Config Tab (Info Empresa) ---
                    {
                        element: '[data-tab="settings"]',
                        popover: {
                            title: 'Información de Empresa',
                            description: 'Personaliza los datos públicos de tu negocio.',
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
                          popover: { title: 'Datos de Empresa', description: 'Define tu RIF, nombre y dirección física. Esta información se inyectará automáticamente en el **encabezado de todos los reportes** de PDF.', side: 'top' }
                     },
                     {
                          element: '#logoUploadArea',
                          popover: { title: 'Logo Oficial', description: 'Sube tu logo en **PNG o JPG**. El sistema lo convertirá y optimizará automáticamente a WebP para que todo cargue súper rápido.', side: 'left' }
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
                         popover: { title: 'Seguridad', description: 'Requerido: Ingrese su contraseña de administrador antes de solicitar cualquier reporte.', side: 'left' }
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
                          element: '#autoBackupToggle',
                          popover: { title: 'Backups Automáticos', description: 'Active esta opción para copias diarias. Las copias se guardan en el servidor.', side: 'right' }
                     },
                     {
                         element: '#backupsTable', // Target table for restore example
                         popover: { title: 'Restauración', description: 'ADVERTENCIA: En la lista de backups, usa el botón "Restaurar" para volver a un punto anterior. Esto sobrescribe toda la información.', side: 'top', onNextClick: () => this.nextModule() }
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
