/**
 * manual.js
 * Interactive User Manual logic using Driver.js, Tippy.js, and Lottie
 * + SPA View Logic (Master/Detail)
 */

document.addEventListener('DOMContentLoaded', () => {
    initTooltips();
    initTour();
    initSearch();
    // Initialize Mermaid (but it won't render hidden elements correctly until shown)
    mermaid.initialize({
        startOnLoad: false,
        theme: 'dark',
        securityLevel: 'loose'
    });
});

// ==========================================
// SPA NAVIGATION (Master/Detail)
// ==========================================

function showSection(sectionId) {
    const masterView = document.getElementById('master-view');
    const detailView = document.getElementById('detail-view');
    const docContent = document.getElementById('doc-content');
    const template = document.getElementById(`tpl-${sectionId}`);

    if (template) {
        masterView.style.display = 'none';
        detailView.style.display = 'block';

        // Inject content
        docContent.innerHTML = template.innerHTML;

        // Render Diagrams
        setTimeout(() => {
            mermaid.init(undefined, document.querySelectorAll('.mermaid'));
        }, 100);

        // Update TOC (Simple H3 grabber)
        updateTableOfContents();

        // Scroll to top
        window.scrollTo(0,0);
    } else {
        // Fallback for sections not yet migrated
        const moduleMap = {
            'envios': 'envios.php',
            'importacion': 'importacion.php',
            'admin': 'admin.php',
            'cargos': 'cargos_existencias.php'
        };
        if(moduleMap[sectionId]) {
            window.location.href = moduleMap[sectionId];
        } else {
            alert('Guía en construcción');
        }
    }
}

function showMaster() {
    document.getElementById('master-view').style.display = 'block';
    document.getElementById('detail-view').style.display = 'none';
    window.scrollTo(0,0);
}

function updateTableOfContents() {
    const toc = document.getElementById('doc-toc');
    const headers = document.getElementById('doc-content').querySelectorAll('h3');
    toc.innerHTML = '';

    headers.forEach((header, index) => {
        const id = `section-${index}`;
        header.id = id;
        const li = document.createElement('li');
        li.innerHTML = `<a href="#${id}" class="text-decoration-none text-muted hover-primary"><i class="fas fa-angle-right me-2"></i>${header.textContent}</a>`;
        toc.appendChild(li);

        // Smooth scroll
        li.querySelector('a').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById(id).scrollIntoView({behavior: 'smooth'});
        });
    });
}

function clearSearch() {
    const input = document.getElementById('manualSearch');
    if (input) {
        input.value = '';
        input.dispatchEvent(new Event('input'));
    }
}


// ==========================================
// TIPPY.JS - Tooltips
// ==========================================
function initTooltips() {
    tippy('[data-tippy-content]', {
        theme: 'buzz',
        placement: 'top',
        animation: 'scale',
        duration: 200
    });
}

// ==========================================
// DRIVER.JS - Guided Tour
// ==========================================
function initTour() {
    const driver = window.driver.js.driver;

    const driverObj = driver({
        showProgress: true,
        animate: true,
        allowClose: true,
        stagePadding: 10,
        overlayOpacity: 0.5, // Reduced opacity
        popoverClass: 'driverjs-theme',
        steps: [
            {
                element: '#hero-section',
                popover: {
                    title: '¡Bienvenido al Centro de Ayuda!',
                    description: 'Esta sección está diseñada para ayudarte. Aquí encontrarás guías interactivas y documentación de todo el sistema.',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#module-grid',
                popover: {
                    title: 'Guías por Módulo',
                    description: 'Haz click en cualquier tarjeta para ver el MANUAL COMPLETO sin salir de esta página.',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#search-container',
                popover: {
                    title: 'Buscador Inteligente',
                    description: 'Escribe palabras clave como "factura", "stock" o "usuario" para filtrar las guías.',
                    side: "bottom",
                    align: 'center'
                }
            }
        ],
        nextBtnText: 'Siguiente <i class="fas fa-arrow-right ms-1"></i>',
        prevBtnText: 'Anterior',
        doneBtnText: '¡Entendido! <i class="fas fa-check ms-1"></i>',

        onHighlightStarted: (element) => {
            if (element) {
                element.style.zIndex = '100005';
                element.style.position = 'relative';
            }
        },
        onDeselected: (element) => {
             if (element) {
                element.style.zIndex = '';
                element.style.position = '';
            }
        }
    });

    const startBtn = document.getElementById('start-tour-btn');
    if (startBtn) {
        startBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            driverObj.drive();
        });
    }
}

// ==========================================
// MODULE CARDS - Search Logic
// ==========================================
function initSearch() {
    const searchInput = document.getElementById('manualSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.manual-card');
            let hasResults = false;

            cards.forEach(card => {
                // Traverse up to col element to hide/show
                const col = card.parentElement;
                const title = card.querySelector('h3').textContent.toLowerCase();
                const desc = card.querySelector('p').textContent.toLowerCase();
                const keywords = card.getAttribute('data-keywords') || '';

                if (title.includes(term) || desc.includes(term) || keywords.includes(term)) {
                    col.style.display = 'block';
                    hasResults = true;
                } else {
                    col.style.display = 'none';
                }
            });

            const noResults = document.getElementById('noResults');
            if (noResults) {
                noResults.style.display = hasResults ? 'none' : 'block';
            }
        });
    }
}

// Call initSearch inside DOMContentLoaded

// ==========================================
// TIPPY.JS - Tooltips
// ==========================================
function initTooltips() {
    // Initialize tooltips for elements with data-tippy-content
    tippy('[data-tippy-content]', {
        theme: 'buzz',
        placement: 'top',
        animation: 'scale',
        duration: 200
    });
}

// ==========================================
// DRIVER.JS - Guided Tour
// ==========================================
function initTour() {
    const driver = window.driver.js.driver;

    const driverObj = driver({
        showProgress: true,
        animate: true,
        allowClose: true,
        stagePadding: 10, // Breathing room around highlighted element
        overlayOpacity: 0.6, // Lighter overlay to avoid "black screen" feeling
        popoverClass: 'driverjs-theme', // Use our custom CSS
        steps: [
            {
                element: '#hero-section',
                popover: {
                    title: '¡Bienvenido al Centro de Ayuda!',
                    description: 'Esta sección está diseñada para ayudarte. Aquí encontrarás guías interactivas y documentación de todo el sistema.',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#module-grid',
                popover: {
                    title: 'Guías por Módulo',
                    description: '¿Tienes dudas sobre Inventario o Pedidos? Haz click en estas tarjetas para ver el manual específico de cada área.',
                    side: "top",
                    align: 'center'
                }
            },
            {
                element: '#search-container',
                popover: {
                    title: 'Buscador Inteligente',
                    description: 'Escribe palabras clave como "factura", "stock" o "usuario" para filtrar rápidamente las guías que necesitas.',
                    side: "bottom",
                    align: 'center'
                }
            },
            {
                element: '#start-tour-btn',
                popover: {
                    title: 'Estamos para ayudarte',
                    description: 'Si alguna vez te sientes perdido, puedes volver a iniciar este tour cuando quieras.',
                    side: "left",
                    align: 'center'
                }
            }
        ],
        nextBtnText: 'Siguiente <i class="fas fa-arrow-right ms-1"></i>',
        prevBtnText: 'Anterior',
        doneBtnText: '¡Entendido! <i class="fas fa-check ms-1"></i>',

        // Fix z-index issues proactively
        onHighlightStarted: (element) => {
            if (element) {
                element.style.zIndex = '100005';
                element.style.position = 'relative';
            }
        },
        onDeselected: (element) => {
             if (element) {
                element.style.zIndex = '';
                element.style.position = '';
            }
        }
    });

    // Attach tour start to button
    const startBtn = document.getElementById('start-tour-btn');
    if (startBtn) {
        startBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            driverObj.drive();
        });
    }

    // Auto-start tour if specific param is present? Maybe not for now to avoid annoying users.
}

// ==========================================
// MODULE CARDS - Search Logic
// ==========================================
const searchInput = document.getElementById('manualSearch');
if (searchInput) {
    searchInput.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.manual-card');
        let hasResults = false;

        cards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const desc = card.querySelector('p').textContent.toLowerCase();
            const keywords = card.getAttribute('data-keywords') || '';

            if (title.includes(term) || desc.includes(term) || keywords.includes(term)) {
                card.parentElement.style.display = 'block';
                hasResults = true;
            } else {
                card.parentElement.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noResults');
        if (noResults) {
            noResults.style.display = hasResults ? 'none' : 'block';
        }
    });
}
