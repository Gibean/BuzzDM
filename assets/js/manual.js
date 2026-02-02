/**
 * manual.js - Enterprise Documentation Logic
 * SPA Navigation, Global Search, Driver.js Tour, Mermaid Rendering
 * BuzzDM User Manual
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all components
    initThemeToggle(); // Theme first to avoid flash
    initMermaid();
    initSPANavigation();
    initGlobalSearch();
    initTour();
    initMobileSidebar();
    initGlossaryTooltips();
});

// ==========================================
// THEME TOGGLE (Light/Dark Mode)
// ==========================================
let currentTheme = 'dark';

function initThemeToggle() {
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;

    // Load saved theme or default to dark
    const savedTheme = localStorage.getItem('docs-theme') || 'dark';
    setTheme(savedTheme);

    if (!toggle) return;

    toggle.addEventListener('click', () => {
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
        localStorage.setItem('docs-theme', newTheme);
    });
}

function setTheme(theme) {
    const html = document.documentElement;
    const toggle = document.getElementById('themeToggle');

    html.setAttribute('data-theme', theme);
    currentTheme = theme;

    // Update toggle button text/icon
    if (toggle) {
        const icon = toggle.querySelector('i');
        const text = toggle.querySelector('span');

        if (theme === 'dark') {
            icon.className = 'fas fa-moon';
            text.textContent = 'Modo Claro';
        } else {
            icon.className = 'fas fa-sun';
            text.textContent = 'Modo Oscuro';
        }
    }

    // Reinitialize Mermaid with correct theme
    updateMermaidTheme(theme);
}

function updateMermaidTheme(theme) {
    const isDark = theme === 'dark';

    mermaid.initialize({
        startOnLoad: false,
        theme: isDark ? 'dark' : 'default',
        themeVariables: isDark ? {
            primaryColor: '#6ea8fe',
            primaryTextColor: '#dfe5eb',
            primaryBorderColor: '#6ea8fe',
            lineColor: '#8b939e',
            secondaryColor: '#1a1f28',
            tertiaryColor: '#10141a',
            background: '#1a1f28',
            mainBkg: '#1a1f28',
            nodeBorder: '#6ea8fe',
            clusterBkg: '#10141a',
            clusterBorder: '#343a40',
            titleColor: '#dfe5eb',
            edgeLabelBackground: '#1a1f28'
        } : {
            primaryColor: '#0d6efd',
            primaryTextColor: '#1a1a1a',
            primaryBorderColor: '#0d6efd',
            lineColor: '#6c757d',
            secondaryColor: '#f8f9fa',
            tertiaryColor: '#ffffff',
            background: '#ffffff',
            mainBkg: '#f8f9fa',
            nodeBorder: '#0d6efd',
            clusterBkg: '#f8f9fa',
            clusterBorder: '#dee2e6',
            titleColor: '#1a1a1a',
            edgeLabelBackground: '#ffffff'
        },
        securityLevel: 'loose',
        flowchart: {
            useMaxWidth: true,
            htmlLabels: true,
            curve: 'basis'
        }
    });
}

// ==========================================
// MERMAID INITIALIZATION
// ==========================================
function initMermaid() {
    // Initial setup handled by updateMermaidTheme via initThemeToggle
    updateMermaidTheme(currentTheme);
}

// ==========================================
// SPA NAVIGATION (Master/Detail)
// ==========================================
let currentSection = 'home';

function initSPANavigation() {
    // Card click handlers (Updated for new Block Link style)
    document.querySelectorAll('.docs-block-link').forEach(card => {
        card.addEventListener('click', () => {
            const section = card.dataset.section;
            if (section) showSection(section);
        });
    });

    // Nav item click handlers
    document.querySelectorAll('.docs-nav-item').forEach(item => {
        item.addEventListener('click', () => {
            const section = item.dataset.section;
            if (section) showSection(section);
        });
    });
}

function showSection(sectionId) {
    // Special case: home shows master view
    if (sectionId === 'home') {
        showMaster();
        return;
    }

    const template = document.getElementById(`tpl-${sectionId}`);
    const docContent = document.getElementById('doc-content');
    const masterView = document.getElementById('master-view');
    const detailView = document.getElementById('detail-view');
    const toc = document.getElementById('docsToc');

    if (!template || !docContent) {
        console.warn(`Template not found for section: ${sectionId}`);
        return;
    }

    // Update content
    docContent.innerHTML = template.innerHTML;

    // Switch views
    masterView.style.display = 'none';
    detailView.style.display = 'block';

    // Show TOC
    if (toc) {
        toc.classList.remove('hidden');
    }

    // Update navigation active state
    updateNavActiveState(sectionId);

    // Update breadcrumb
    updateBreadcrumb(sectionId);

    // Render Mermaid diagrams in new content
    renderMermaidDiagrams();

    // Generate Table of Contents
    generateTableOfContents();

    // Initialize tooltips for new content
    refreshGlossaryTooltips();

    // Scroll to top
    document.querySelector('.docs-content').scrollTop = 0;
    window.scrollTo(0, 0);

    // Store current section
    currentSection = sectionId;

    // Close mobile sidebar if open
    closeMobileSidebar();
}

function showMaster() {
    const masterView = document.getElementById('master-view');
    const detailView = document.getElementById('detail-view');
    const toc = document.getElementById('docsToc');

    masterView.style.display = 'block';
    detailView.style.display = 'none';

    if (toc) {
        toc.classList.add('hidden');
    }

    updateNavActiveState('home');
    currentSection = 'home';

    // Scroll to top
    window.scrollTo(0, 0);
}

function updateNavActiveState(sectionId) {
    // Update step items (new sidebar style)
    document.querySelectorAll('.docs-step-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === sectionId) {
            item.classList.add('active');
        }
    });
    // Legacy support for old nav items
    document.querySelectorAll('.docs-nav-item').forEach(item => {
        item.classList.remove('active');
        if (item.dataset.section === sectionId) {
            item.classList.add('active');
        }
    });
}

// Section names mapping
const sectionNames = {
    'home': 'Centro de Ayuda',
    'dashboard': 'Panel de Control',
    'empresa': 'Datos de Empresa',
    'usuarios': 'Usuarios y Roles',
    'catalogos': 'Categorías y Marcas',
    'clientes': 'Registrar Clientes',
    'proveedores': 'Agregar Proveedores',
    'productos': 'Crear Productos',
    'kits': 'Armar Kits',
    'importar': 'Importar Excel',
    'almacenes': 'Configurar Almacenes',
    'cargos': 'Registrar Cargos',
    'pedidos': 'Crear Pedidos',
    'pagos': 'Estados de Pago',
    'envios': 'Seguimiento Envíos',
    'notas': 'Notas Kanban',
    'divisas': 'Monitor Divisas',
    'backups': 'Respaldos',
    'logs': 'Registro de Actividad'
};

function updateBreadcrumb(sectionId) {
    const breadcrumb = document.getElementById('docsBreadcrumb');
    if (!breadcrumb) return;

    const sectionName = sectionNames[sectionId] || sectionId;

    if (sectionId === 'home') {
        breadcrumb.innerHTML = `<a href="#" onclick="showSection('home'); return false;">Centro de Ayuda</a>`;
    } else {
        breadcrumb.innerHTML = `
            <a href="#" onclick="showSection('home'); return false;">Centro de Ayuda</a>
            <span>›</span>
            <span>${sectionName}</span>
        `;
    }
}

// ==========================================
// MERMAID DIAGRAM RENDERING
// ==========================================
async function renderMermaidDiagrams() {
    const mermaidElements = document.querySelectorAll('#doc-content .mermaid');

    if (mermaidElements.length === 0) return;

    for (let i = 0; i < mermaidElements.length; i++) {
        const element = mermaidElements[i];
        const graphDefinition = element.textContent.trim();

        if (!graphDefinition) continue;

        try {
            const id = `mermaid-graph-${Date.now()}-${i}`;
            const { svg } = await mermaid.render(id, graphDefinition);
            element.innerHTML = svg;
        } catch (error) {
            console.error('Mermaid rendering error:', error);
            element.innerHTML = `<p style="color: #dc3545;">Error rendering diagram</p>`;
        }
    }
}

// ==========================================
// TABLE OF CONTENTS GENERATION
// ==========================================
function generateTableOfContents() {
    const tocList = document.getElementById('docs-toc-list');
    const content = document.getElementById('doc-content');

    if (!tocList || !content) return;

    // Clear existing TOC
    tocList.innerHTML = '';

    // Find all headings
    const headings = content.querySelectorAll('h2, h3');

    if (headings.length === 0) {
        tocList.innerHTML = '<li class="docs-toc-item"><span style="color: var(--buzz-text-muted); font-size: 0.8rem;">Sin secciones</span></li>';
        return;
    }

    headings.forEach((heading, index) => {
        // Add ID to heading for anchor links
        const headingId = `section-${index}`;
        heading.id = headingId;

        // Create TOC item
        const li = document.createElement('li');
        li.className = 'docs-toc-item';

        const a = document.createElement('a');
        a.className = 'docs-toc-link';
        a.href = `#${headingId}`;
        a.textContent = heading.textContent.replace(/^[📊🎯📈⚠️📦➕✏️🗑️🔍📋🛒💰🚚👥⭐📥📝🏭📍🔄🔒📤💱⚙️🏢]/g, '').trim();

        // Add level class for indentation
        if (heading.tagName === 'H3') {
            a.classList.add('level-3');
        }

        // Smooth scroll on click
        a.addEventListener('click', (e) => {
            e.preventDefault();
            heading.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Update active state
            document.querySelectorAll('.docs-toc-link').forEach(link => link.classList.remove('active'));
            a.classList.add('active');
        });

        li.appendChild(a);
        tocList.appendChild(li);
    });
}

// ==========================================
// GLOBAL SEARCH
// ==========================================
function initGlobalSearch() {
    const searchInput = document.getElementById('docsGlobalSearch');
    const moduleGrid = document.getElementById('module-grid');
    const noResults = document.getElementById('noResults');

    if (!searchInput) return;

    let debounceTimer;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(e.target.value.toLowerCase().trim());
        }, 300);
    });

    // Clear search on Escape
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            clearSearch();
        }
    });
}

function performSearch(query) {
    const cards = document.querySelectorAll('.docs-block-link');
    const noResults = document.getElementById('noResults');
    let hasResults = false;

    if (!query) {
        // Show all cards
        cards.forEach(card => {
            card.style.display = '';
        });
        if (noResults) noResults.classList.remove('visible');
        return;
    }

    cards.forEach(card => {
        const title = card.querySelector('.docs-block-title')?.textContent.toLowerCase() || '';
        const desc = card.querySelector('.docs-block-desc')?.textContent.toLowerCase() || '';
        const keywords = card.dataset.keywords?.toLowerCase() || '';

        const matches = title.includes(query) || desc.includes(query) || keywords.includes(query);

        if (matches) {
            card.style.display = '';
            hasResults = true;
        } else {
            card.style.display = 'none';
        }
    });

    if (noResults) {
        noResults.classList.toggle('visible', !hasResults);
    }
}

function clearSearch() {
    const searchInput = document.getElementById('docsGlobalSearch');
    if (searchInput) {
        searchInput.value = '';
        performSearch('');
    }
}

// ==========================================
// MOBILE SIDEBAR TOGGLE
// ==========================================
function initMobileSidebar() {
    const toggle = document.getElementById('docsSidebarToggle');
    const sidebar = document.getElementById('docsSidebar');
    const overlay = document.getElementById('docsOverlay');

    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        if (overlay) overlay.classList.toggle('visible');
    });

    if (overlay) {
        overlay.addEventListener('click', closeMobileSidebar);
    }
}

function closeMobileSidebar() {
    const sidebar = document.getElementById('docsSidebar');
    const overlay = document.getElementById('docsOverlay');

    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('visible');
}

// ==========================================
// DRIVER.JS TOUR (Simplified 4-Step)
// ==========================================
function initTour() {
    const startBtn = document.getElementById('startTourBtn');

    if (!startBtn) return;

    startBtn.addEventListener('click', startTour);
}

function startTour() {
    // Ensure we're on the home view before starting tour
    if (currentSection !== 'home') {
        showMaster();
    }

    // Small delay to ensure DOM is ready
    setTimeout(() => {
        const driver = window.driver.js.driver;

        const driverObj = driver({
            popoverClass: 'docs-tour-theme',
            showProgress: true,
            progressText: '{{current}} de {{total}}',
            nextBtnText: 'Siguiente',
            prevBtnText: 'Anterior',
            doneBtnText: '¡Listo!',
            animate: true,
            allowClose: true,
            overlayOpacity: 0.75,
            stagePadding: 10,
            stageRadius: 8,
            steps: [
                {
                    element: '#docs-hero',
                    popover: {
                        title: '👋 ¡Bienvenido al Centro de Ayuda!',
                        description: 'Aquí encontrarás guías completas para cada módulo de BUZZ Data Manager. El sistema está diseñado para ser intuitivo y flexible.',
                        side: 'bottom',
                        align: 'center'
                    }
                },
                {
                    element: '#docsSidebar',
                    popover: {
                        title: '🔢 Flujo de Trabajo',
                        description: 'Hemos organizado la navegación siguiendo el proceso lógico de uso del sistema, desde la configuración inicial hasta la administración avanzada.',
                        side: 'right',
                        align: 'start'
                    }
                },
                {
                    element: '#docsGlobalSearch',
                    popover: {
                        title: '🔍 Búsqueda Inteligente',
                        description: 'Busca cualquier funcionalidad o término. El sistema filtrará instantáneamente las guías relevantes para ti.',
                        side: 'bottom',
                        align: 'start'
                    }
                },
                {
                    element: '#themeToggle',
                    popover: {
                        title: '🌗 Modo Oscuro/Claro',
                        description: 'Personaliza tu experiencia visual. Cambia el tema según tu preferencia o las condiciones de iluminación.',
                        side: 'bottom',
                        align: 'end'
                    }
                },
                {
                    element: '#moduleGrid',
                    popover: {
                        title: '🧱 Módulos Principales',
                        description: 'Haz clic en cualquier tarjeta para abrir la guía detallada. Dentro encontrarás diagramas interactivos y pasos detallados.',
                        side: 'top',
                        align: 'center'
                    }
                },
                {
                    element: '#docsBreadcrumb',
                    popover: {
                        title: '📍 ¿Dónde estás?',
                        description: 'Usa el rastro de migas de pan para saber exactamente en qué sección te encuentras y volver rápidamente al inicio.',
                        side: 'bottom',
                        align: 'start'
                    }
                },
                {
                    element: '#docsToc',
                    popover: {
                        title: '📑 Tabla de Contenidos',
                        description: 'Al leer un artículo, usa este panel para saltar rápidamente entre subsecciones y encontrar el dato preciso.',
                        side: 'left',
                        align: 'start'
                    }
                }
            ],
            onDestroyStarted: () => {
                driverObj.destroy();
            }
        });

        driverObj.drive();
    }, 100);
}

// ==========================================
// GLOSSARY TOOLTIPS (Tippy.js)
// ==========================================
function initGlossaryTooltips() {
    // Initialize tooltips for glossary terms in the currently visible content
    const terms = document.querySelectorAll('.glossary-term');

    if (terms.length === 0) return;

    terms.forEach(term => {
        if (term._tippy) return; // Already initialized

        tippy(term, {
            theme: 'buzz',
            placement: 'top',
            arrow: true,
            animation: 'fade',
            delay: [200, 0],
            maxWidth: 280
        });
    });
}

// Re-initialize tooltips when content changes (SPA navigation)
function refreshGlossaryTooltips() {
    setTimeout(() => {
        const terms = document.querySelectorAll('#doc-content .glossary-term');
        terms.forEach(term => {
            if (term._tippy) return;

            tippy(term, {
                theme: 'buzz',
                placement: 'top',
                arrow: true,
                animation: 'fade',
                delay: [200, 0],
                maxWidth: 280
            });
        });
    }, 100);
}

// ==========================================
// EXPOSE FUNCTIONS TO GLOBAL SCOPE
// ==========================================
window.showSection = showSection;
window.showMaster = showMaster;
window.clearSearch = clearSearch;
window.refreshGlossaryTooltips = refreshGlossaryTooltips;
