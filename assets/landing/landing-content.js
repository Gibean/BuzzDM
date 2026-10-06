/**
 * Buzz Data Manager - Landing Page Content & Config (v3.6.1)
 * Proyecto Académico de Demostración Técnica e Investigación Operativa para PYMEs.
 *
 * AVISO: este fichero es contenido, NO comportamiento. La paleta Ctrl+K no se
 * implementa aquí ni en index.html: vive en el ERP (assets/js/common.js,
 * initBuzzCommandPalette). Lo que aparece más abajo solo la describe, así que
 * debe seguir describiendo lo que el ERP hace de verdad: filtrar y abrir los
 * módulos que el sidebar ya habilitó para el rol del usuario. No ejecutar
 * acciones de negocio (requisiciones, ajustes de stock, exportaciones).
 *
 * Ojo al editar: landing.js solo consume ecosystem.modules. El resto de las
 * secciones son la fuente de contenido en español; si se cambia aquí, hay que
 * reflejar el cambio en el HTML estático de index.html, que es lo que se pinta.
 */

window.BUZZ_LANDING_DATA = {
  brand: {
    name: "Buzz Data Manager",
    shortName: "BuzzDM",
    tagline: "Proyecto Académico · ERP Operativo",
    badgeHero: "PROYECTO ACADÉMICO · DEMO EN LÍNEA DE 14 DÍAS",
    version: "v2.5 Open-Core",
    logoPath: "assets/img/logov1_long.png",
    iconPath: "assets/img/logov1_icon.png"
  },
  
  navigation: {
    megaMenu: [
      {
        id: "producto",
        label: "Módulos",
        columns: [
          {
            title: "Módulos Core",
            items: [
              { title: "Control de Inventario", desc: "Multialmacén, existencias en tiempo real y kardex", icon: "fa-boxes-stacked", link: "#modulos" },
              { title: "Gestión de Pedidos", desc: "Flujos de despacho y control de estados (sin facturación)", icon: "fa-cart-shopping", link: "#modulos" },
              { title: "Requisiciones Inteligentes", desc: "Reabastecimiento predictivo basado en consumo", icon: "fa-brain", link: "#modulos" },
              { title: "Tableros Kanban", desc: "Visualización de flujo operativo en almacén", icon: "fa-table-columns", link: "#modulos" }
            ]
          },
          {
            title: "Capacidades Clave",
            items: [
              { title: "Importación Masiva", desc: "Carga Excel y CSV sin pérdida de datos", icon: "fa-file-import", link: "#modulos" },
              { title: "Estadísticas y BI", desc: "Métricas operativas y rotación de stock", icon: "fa-chart-line", link: "#modulos" },
              { title: "Paleta de Módulos", desc: "Navegación por teclado entre los módulos del sistema", icon: "fa-keyboard", link: "#productividad" }
            ]
          }
        ]
      },
      {
        id: "soluciones",
        label: "Sectores",
        columns: [
          {
            title: "Casos de Aplicación",
            items: [
              { title: "Comercio & Retail", desc: "Control de stock en tienda física y almacén", icon: "fa-store", link: "#diferenciadores" },
              { title: "Distribución y Mayoristas", desc: "Gestión ágil de bultos, envíos y reposición", icon: "fa-truck-fast", link: "#diferenciadores" },
              { title: "Librerías y Talleres", desc: "Insumos, artículos y tracking de órdenes", icon: "fa-gears", link: "#diferenciadores" }
            ]
          }
        ]
      },
      {
        id: "recursos",
        label: "Recursos",
        columns: [
          {
            title: "Código y Documentación",
            items: [
              { title: "Guía de Contribución Git", desc: "Flujo paso a paso para colaborar en GitHub", icon: "fa-code-branch", link: "guia_contribucion.html" },
              { title: "Manual de Usuario", desc: "Guías de instalación y uso del sistema", icon: "fa-book-open", link: "manual.php" },
              { title: "Paleta de módulos", desc: "Atajo Ctrl+K dentro del ERP para abrir cualquier sección", icon: "fa-keyboard", link: "#productividad" }
            ]
          }
        ]
      }
    ],
    links: [],
    ctaLogin: { label: "Iniciar sesión", href: "login" },
    ctaPrimary: { label: "Acceder a la demo", href: "registro" }
  },

  hero: {
    announcementBadge: "PROYECTO ACADÉMICO · DEMO EN LÍNEA DE 14 DÍAS",
    h1: "BuzzDM. Tu operación, bajo control.",
    h2Price: "Software ERP open source para PYMEs desarrollado como proyecto académico de grado. Demostración funcional completa de 14 días sin fines comerciales.",
    ctaPrimary: {
      label: "Explorar demo de 14 días",
      href: "registro",
      subtext: "Acceso inmediato · Sin tarjeta de crédito"
    },
    ctaSecondary: {
      label: "Consultas académicas",
      sizes: [
        { label: "Consulta sobre arquitectura técnica", action: "mailto:buzzdatamanager@gmail.com?subject=Consulta%20Tecnica%20BuzzDM" },
        { label: "Colaboración o feedback de investigación", action: "mailto:buzzdatamanager@gmail.com?subject=Feedback%20Academico%20BuzzDM" },
        { label: "Prueba guiada en entorno real", action: "mailto:buzzdatamanager@gmail.com?subject=Prueba%20Guiada%20BuzzDM" }
      ]
    },
    mockup: {
      alt: "Panel de control del ERP Buzz Data Manager con analíticas y stock en tiempo real",
      kpis: [
        { label: "SKUs activos", value: "1,480" },
        { label: "Entrega a tiempo", value: "99.4%" },
        { label: "Tiempo de entrada", value: "-68%" }
      ]
    }
  },

  ecosystem: {
    tag: "MODULARIDAD PURA",
    title: "Un ecosistema que crece con tu empresa",
    subtitle: "Software enfocado 100% en la operativa de almacén, compras y despachos diarios. BuzzDM no incluye facturación fiscal ni enredos tributarios: tus datos se exportan limpios para tu software contable habitual.",
    switchTextOff: "Sin BuzzDM",
    switchSubOff: "Caos manual y disperso",
    switchTextOn: "Con BuzzDM",
    switchSubOn: "ERP operativo unificado",
    modules: [
      {
        id: "inventario",
        title: "Inventario Multialmacén",
        desc: "Kardex en vivo, rotación de existencias, cálculo de punto de reorden y control multisede centralizado en tiempo real.",
        badge: "Control Total",
        icon: "fa-boxes-stacked",
        altTitle: "Tablas de Excel Lentas",
        altDesc: "Tablas de Excel lentas llenas de información sobre productos, desactualizadas, duplicadas y propensas a errores de fórmula.",
        altBadge: "Hojas de Cálculo",
        altIcon: "fa-file-excel",
        chaosStyle: { rotate: "-1.8deg", x: "-8px", y: "8px" }
      },
      {
        id: "importacion",
        title: "Importación Masiva",
        desc: "Carga miles de filas desde Excel o CSV en segundos sin perder categorías ni duplicar registros de productos.",
        badge: "Cero Fricción",
        icon: "fa-file-import",
        altTitle: "Carga Manual Uno a Uno",
        altDesc: "Guardar producto por producto cada vez que intentas un sistema nuevo o necesitas actualizar listas de precios de catálogo.",
        altBadge: "Pérdida de Tiempo",
        altIcon: "fa-keyboard",
        chaosStyle: { rotate: "1.6deg", x: "10px", y: "-8px" }
      },
      {
        id: "pedidos",
        title: "Ventas y Pedidos",
        desc: "Flujo operativo directo desde la orden hasta el despacho en almacén con validación de stock real. (Sin facturación fiscal).",
        badge: "Despacho Ágil",
        icon: "fa-cart-shopping",
        altTitle: "Pedidos Sin Control",
        altDesc: "No hay control sobre los mismos: información dispersa por diferentes apps de notas o papeles no adaptados a las necesidades del cliente.",
        altBadge: "Información Dispersa",
        altIcon: "fa-note-sticky",
        chaosStyle: { rotate: "-2deg", x: "-6px", y: "12px" }
      },
      {
        id: "requisiciones",
        title: "Requisiciones Inteligentes",
        desc: "Genera sugerencias automáticas de compra según histórico de consumo, rotación y stock mínimo para evitar roturas.",
        badge: "Compras Claras",
        icon: "fa-brain",
        altTitle: "Compras a Ciegas",
        altDesc: "Las compras no se hacen fundamentadas sino a lo que 'mejor venga' o lo que ya no hay, permitiendo que se compren cosas no productivas o tener problemas con el mismo proveedor recurrentemente.",
        altBadge: "Gasto Ineficiente",
        altIcon: "fa-triangle-exclamation",
        chaosStyle: { rotate: "2.1deg", x: "12px", y: "-5px" }
      },
      {
        id: "kanban",
        title: "Tableros Kanban Operativos",
        desc: "Visualiza el estado de pedidos, tareas de almacén y despachos en paneles arrastrables sin fricción ni llamadas.",
        badge: "Flujo Visual",
        icon: "fa-table-columns",
        altTitle: "Organización Ausente",
        altDesc: "Similar a ventas y pedidos: organización ausente o regada por diferentes apps no intuitivas, notas adhesivas y cuellos de botella invisibles.",
        altBadge: "Desconexión",
        altIcon: "fa-diagram-project",
        chaosStyle: { rotate: "-1.5deg", x: "-10px", y: "8px" }
      },
      {
        id: "estadísticas",
        title: "Estadísticas y BI Operativo",
        desc: "Métricas visuales directas de rotación, productos con mayor demanda y balances para resolver problemas al instante.",
        badge: "Decisiones con Datos",
        icon: "fa-chart-pie",
        altTitle: "Cero Visibilidad Visual",
        altDesc: "No hay manera de ver todo en estadísticas visuales para identificar y atacar problemas en el punto exacto (target issues at point).",
        altBadge: "A Ciegas",
        altIcon: "fa-eye-slash",
        chaosStyle: { rotate: "1.8deg", x: "8px", y: "-6px" }
      }
    ],
    footerLinkText: "Ver todas las funciones del sistema &rarr;"
  },

  quote: {
    text: "“Tener el inventario de libros y papelería al día era un caos constante entre el almacén y el mostrador. Con BuzzDM pudimos organizar las existencias, planificar las compras antes del inicio de temporada y saber qué reponer sin perder horas en hojas de cálculo.”",
    author: "María Castro",
    role: "Gerente General · Librería Proyecto académico demostrativo",
    avatarAlt: "María Castro, Gerente General de Librería Proyecto académico demostrativo",
    metricHighlight: "Caso real de validación operativa en PYME"
  },

  videoSection: {
    tag: "DEMOSTRACIÓN TÉCNICA",
    h2: "Presentación académica del proyecto",
    lead: "Recorrido explicativo sobre la arquitectura, el diseño de la base de datos y la operación en tiempo real de los módulos de inventario, pedidos y compras del ERP.",
    posterPlaceholder: "Video de Defensa y Demostración Práctica del Software",
    recommendedResolution: "1920x1080 (16:9)",
    featuresBullet: [
      "Demostración de flujos de almacén y reposición",
      "Explicación de la arquitectura multi-tenant y base de datos",
      "Validación práctica con usuarios y tiempos de respuesta"
    ]
  },

  productivity: {
    tag: "RENDIMIENTO EXTREMO",
    h2: "Operaciones a la velocidad del trabajo real",
    performanceClaim: "Hasta un 72% menos de tiempo en entrada de datos y consultas indexadas con respuesta inferior a 45ms.",
    commandPalette: {
      shortcut: "Ctrl + K",
      label: "Paleta de módulos del ERP",
      desc: "Función del sistema, no de esta web. Dentro del ERP, Ctrl+K filtra y abre los módulos que tienes disponibles: la lista se arma leyendo tu menú lateral, así que nunca ofrece secciones que tu rol no puede usar.",
      demoResults: [
        { text: "Pedidos", badge: "Operaciones" },
        { text: "Requisiciones", badge: "Operaciones" },
        { text: "Importación", badge: "Herramientas" }
      ]
    },
    bentoCards: [
      {
        title: "Búsqueda universal indexada",
        desc: "Endpoint php/search_api.php optimizado para filtrar productos, clientes y usuarios en memoria en menos de 50 ms.",
        icon: "fa-bolt-lightning",
        tag: "45ms latencia"
      },
      {
        title: "Entrada numérica fluida",
        desc: "Diseñado para lectores de código de barras y terminales de mostrador con avance automático con tecla Tab.",
        icon: "fa-barcode",
        tag: "Ergonomía"
      },
      {
        title: "Sincronización multiusuario",
        desc: "Sin bloqueos de archivo: múltiples operarios gestionando inventario a la vez con coherencia inmediata.",
        icon: "fa-users-gear",
        tag: "Tiempo Real"
      },
      {
        title: "Exportaciones en un clic",
        desc: "Tus datos en CSV, Excel o SQL cuando los necesites, listos para tu contador o sistema tributario habitual.",
        icon: "fa-file-excel",
        tag: "Libertad total"
      }
    ]
  },

  differentiators: {
    tag: "POR QUÉ BUZZ DATA MANAGER",
    sectionTitle: "Seis razones sólidas para elegir una solución honesta",
    cards: [
      {
        h3: "1. Código abierto en GitHub",
        desc: "Todo el código fuente es público y transparente. Puedes auditar cómo se procesan los datos, clonar el repositorio o colaborar activamente.",
        linkText: "Guía de contribución con Git",
        linkHref: "guia_contribucion.html",
        icon: "fa-code"
      },
      {
        h3: "2. Ecosistema e integraciones",
        desc: "API REST documentada y arquitectura lista para conectar balanzas, lectores de código de barras y tiendas online.",
        linkText: "Documentación de API",
        linkHref: "api_docs/",
        icon: "fa-network-wired"
      },
      {
        h3: "3. Proyecto académico independiente",
        desc: "Desarrollado como investigación y solución de software de grado, libre de intereses corporativos o dependencias de consultoría externa.",
        linkText: "Filosofía del proyecto",
        linkHref: "about.php",
        icon: "fa-graduation-cap"
      },
      {
        h3: "4. Tus datos son tuyos",
        desc: "Exporta tu base completa cuando quieras en formatos estándar (SQL, CSV). Sin bloqueos de proveedor ni costes de salida.",
        linkText: "Política de datos libres",
        linkHref: "importacion.php",
        icon: "fa-database"
      },
      {
        h3: "5. Sin cargos ocultos",
        desc: "Demo completa de 14 días sin tarjeta. Sin costes por usuario adicional, comisiones por transacción ni permanencia forzada.",
        linkText: "Condiciones de la demo",
        linkHref: "#acceso-demo",
        icon: "fa-tag"
      },
      {
        h3: "6. Operativa pura (sin burocracia fiscal)",
        desc: "BuzzDM se concentra al 100% en almacén, compras y despachos sin inflar el sistema con facturación electrónica que cambia de normativas cada año. Exporta tus datos para tu gestoría o software contable.",
        linkText: "Ver comparativa",
        linkHref: "#comparativa-detalle",
        icon: "fa-scale-balanced"
      }
    ]
  },

  socialProof: {
    heading: "Validación del sistema en entorno real",
    stats: [
      { number: "1", label: "Caso piloto real", sub: "Librería Proyecto académico demostrativo (almacén y tienda)" },
      { number: "6", label: "Módulos operativos", sub: "Inventario, pedidos, compras, kanban, importación y BI" },
      { number: "< 50ms", label: "Tiempo de respuesta", sub: "Consultas indexadas en entorno local (PHP/MySQL)" }
    ],
    featuredTestimonial: {
      quote: "Pasamos de perder pedidos por no saber si había existencias en el depósito a despachar ágilmente múltiples pedidos en el mismo día sin complicaciones. Las requisiciones inteligentes nos salvaron de quedarnos sin productos en temporada alta.",
      author: "María Castro",
      role: "Gerente General · Librería Proyecto académico demostrativo",
      resultMetric: "+38% en velocidad de preparación y despacho",
      avatarInitials: "MC"
    }
  },

  finalCta: {
    h2: "Comprueba el funcionamiento del ERP.",
    lead: "14 días de acceso completo a la demo académica. Evalúa BuzzDM con datos de prueba o importa tu catálogo.",
    buttonText: "Acceder a la demo",
    buttonHref: "registro",
    microcopy: "Acceso instantáneo"
  }
};
