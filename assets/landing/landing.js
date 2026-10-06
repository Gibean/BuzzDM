/**
 * Buzz Data Manager - Landing Interactions (v3.6.1)
 * Proyecto Académico de Demostración Técnica
 * - Sin/Con BuzzDM con sombras suaves y textos alternativos
 * - La paleta Ctrl+K NO se implementa aquí: vive en el ERP, en
 *   assets/js/common.js (initBuzzCommandPalette). Esta landing solo la
 *   describe con texto y un mockup estático. No se registra ningún atajo
 *   global ni ningún preventDefault sobre Ctrl+K, para no competir con el
 *   atajo del sistema.
 * - Modales legales y banner de cookies
 */

document.addEventListener('DOMContentLoaded', () => {
  const DATA = window.BUZZ_LANDING_DATA;

  // 1. Sticky navbar
  const header = document.querySelector('.site-header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.classList.toggle('scrolled', window.scrollY > 24);
    }, { passive: true });
  }

  // 2. Mobile drawer
  const mobileToggle = document.getElementById('mobileNavToggle');
  const mobileDrawer = document.getElementById('mobileDrawer');
  if (mobileToggle && mobileDrawer) {
    mobileToggle.addEventListener('click', () => {
      const open = mobileDrawer.classList.toggle('active');
      mobileToggle.setAttribute('aria-expanded', String(open));
      mobileToggle.setAttribute('aria-label', open ? 'Cerrar menu de navegacion' : 'Abrir menu de navegacion');
      mobileDrawer.setAttribute('aria-hidden', String(!open));
    });
    mobileDrawer.querySelectorAll('a, button').forEach(el => {
      el.addEventListener('click', () => {
        mobileDrawer.classList.remove('active');
        mobileToggle.setAttribute('aria-expanded', 'false');
        mobileDrawer.setAttribute('aria-hidden', 'true');
      });
    });
  }

  // 3. Expert dropdown (consultas académicas)
  const expertWrap = document.getElementById('expertWrap');
  const expertTrigger = document.getElementById('expertTrigger');
  if (expertWrap && expertTrigger) {
    expertTrigger.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = expertWrap.classList.toggle('open');
      expertTrigger.setAttribute('aria-expanded', String(open));
    });
    document.addEventListener('click', (e) => {
      if (!expertWrap.contains(e.target)) {
        expertWrap.classList.remove('open');
        expertTrigger.setAttribute('aria-expanded', 'false');
      }
    });
    expertWrap.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        expertWrap.classList.remove('open');
        expertTrigger.setAttribute('aria-expanded', 'false');
        expertTrigger.focus();
      }
    });
  }

  // 4. Módulos y Switch Con / Sin BuzzDM
  const modulesContainer = document.getElementById('modulesContainer');
  const toggleBtn = document.getElementById('moduleSwitchBtn');
  const labelOff = document.getElementById('labelOff');
  const labelOn = document.getElementById('labelOn');
  let isOrdered = true; // Por defecto: Con BuzzDM (Ordenado)

  function renderModules() {
    if (!modulesContainer) return;
    const modules = DATA?.ecosystem?.modules || [];
    modulesContainer.innerHTML = '';

    const grid = document.createElement('div');
    grid.className = 'modules-grid';

    modules.forEach((mod) => {
      const item = document.createElement('div');
      item.className = 'module-item' + (!isOrdered ? ' is-chaos-card' : '');
      item.setAttribute('data-id', mod.id);

      if (!isOrdered) {
        // En modo "Sin BuzzDM", mostramos las opciones alternativas dolorosas
        item.innerHTML = `
          <div class="module-item-top">
            <div class="module-icon chaos-icon" aria-hidden="true">
              <i class="fa-solid ${mod.altIcon || 'fa-triangle-exclamation'}"></i>
            </div>
            <span class="module-badge chaos-badge">${mod.altBadge || 'Problema'}</span>
          </div>
          <div class="module-text">
            <h3 class="module-name chaos-title">${mod.altTitle}</h3>
            <p class="module-desc chaos-desc">${mod.altDesc}</p>
          </div>
        `;
      } else {
        // En modo "Con BuzzDM", mostramos el módulo ERP ordenado
        item.innerHTML = `
          <div class="module-item-top">
            <div class="module-icon" aria-hidden="true">
              <i class="fa-solid ${mod.icon}"></i>
            </div>
            <span class="module-badge">${mod.badge}</span>
          </div>
          <div class="module-text">
            <h3 class="module-name">${mod.title}</h3>
            <p class="module-desc">${mod.desc}</p>
          </div>
        `;
      }

      grid.appendChild(item);
    });

    modulesContainer.appendChild(grid);

    // Estados visuales del toggle destacado
    modulesContainer.classList.toggle('mode-chaos', !isOrdered);
    if (toggleBtn) {
      toggleBtn.classList.toggle('active', isOrdered);
      toggleBtn.setAttribute('aria-checked', String(isOrdered));
    }

    if (labelOn) {
      labelOn.classList.toggle('is-active', isOrdered);
    }
    if (labelOff) {
      labelOff.classList.toggle('is-active', !isOrdered);
      labelOff.classList.toggle('is-chaos-active', !isOrdered);
    }
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      isOrdered = !isOrdered;
      renderModules();
    });
  }

  // Permitir clic directo en los labels para alternar
  if (labelOff) {
    labelOff.addEventListener('click', () => {
      if (isOrdered) {
        isOrdered = false;
        renderModules();
      }
    });
  }
  if (labelOn) {
    labelOn.addEventListener('click', () => {
      if (!isOrdered) {
        isOrdered = true;
        renderModules();
      }
    });
  }

  if (DATA) renderModules();

  // 5. Scroll-reveal para diferenciadores
  const revealEls = document.querySelectorAll('.diff-item');
  if ('IntersectionObserver' in window && revealEls.length) {
    const revealObs = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          revealObs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    revealEls.forEach((el) => revealObs.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('revealed'));
  }

  // 6. Modales Legales
  //
  // focoPrevio guarda quién tenía el foco al abrir. Sin restaurarlo, el
  // teclado se quedaba apuntando al interior de un overlay ya oculto: al
  // cerrar con Escape el foco caía en <body> y el usuario tenía que tabular
  // desde el principio para volver al pie de página.
  let focoPrevio = null;

  function openModal(modal) {
    if (!modal) return;
    focoPrevio = document.activeElement;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    const focusable = modal.querySelector('button, [href], input, select, textarea');
    if (focusable) focusable.focus();
  }
  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('active');
    document.body.style.overflow = '';
    if (focoPrevio && document.contains(focoPrevio) && typeof focoPrevio.focus === 'function') {
      focoPrevio.focus();
    }
    focoPrevio = null;
  }

  const legalContent = {
    privacy: `
      <h4 style="margin-bottom:0.75rem;">1. Responsable del tratamiento</h4>
      <p>Buzz Data Manager (Proyecto Académico), buzzdatamanager@gmail.com</p>
      <h4 style="margin-top:1.5rem;margin-bottom:0.75rem;">2. Finalidad</h4>
      <p>Los datos registrados se utilizan exclusivamente para habilitar el acceso a la demostración técnica del software. No se comercializan ni se ceden a terceros.</p>
      <h4 style="margin-top:1.5rem;margin-bottom:0.75rem;">3. Derechos</h4>
      <p>Puedes solicitar la eliminación inmediata de tu acceso o datos escribiendo a buzzdatamanager@gmail.com.</p>
    `,
    terms: `
      <h4 style="margin-bottom:0.75rem;">1. Aceptación</h4>
      <p>El uso de la demo de Buzz Data Manager implica la aceptación de estos términos de evaluación técnica y académica.</p>
      <h4 style="margin-top:1.5rem;margin-bottom:0.75rem;">2. Uso permitido</h4>
      <p>La demo es para pruebas operativas de demostración de software. Queda prohibido el uso con fines ilícitos.</p>
    `,
    cookies: `
      <h4 style="margin-bottom:0.75rem;">1. Cookies técnicas</h4>
      <p>Utilizamos únicamente almacenamiento local y cookies técnicas necesarias para la sesión de demostración. Sin rastreo publicitario de terceros.</p>
    `
  };

  function setupLegalModal(btnId, modalId, contentId, key) {
    const btn = document.getElementById(btnId);
    const modal = document.getElementById(modalId);
    const content = document.getElementById(contentId);
    if (!btn || !modal) return;
    btn.addEventListener('click', () => {
      if (content) content.innerHTML = legalContent[key] || '';
      openModal(modal);
    });
    modal.querySelectorAll('.modal-close').forEach((cb) => {
      cb.addEventListener('click', () => closeModal(modal));
    });
    modal.addEventListener('click', (e) => {
      if (e.target === modal) closeModal(modal);
    });
  }

  setupLegalModal('openPrivacyModal', 'privacyModal', 'privacyContent', 'privacy');
  setupLegalModal('openTermsModal', 'termsModal', 'termsContent', 'terms');
  setupLegalModal('openCookiesModal', 'cookiesModal', 'cookiesContent', 'cookies');

  const openCookiePolicyBtn = document.getElementById('openCookiePolicyBtn');
  if (openCookiePolicyBtn) {
    openCookiePolicyBtn.addEventListener('click', () => {
      const modal = document.getElementById('cookiesModal');
      const content = document.getElementById('cookiesContent');
      if (content) content.innerHTML = legalContent.cookies;
      openModal(modal);
    });
  }

  // 7. Banner de cookies
  const cookieBanner = document.getElementById('cookieBanner');
  const cookieAccept = document.getElementById('cookieAccept');
  const cookieDecline = document.getElementById('cookieDecline');
  const COOKIE_KEY = 'buzzdm_cookie_consent';

  if (cookieBanner && !localStorage.getItem(COOKIE_KEY)) {
    cookieBanner.classList.add('active');
  }

  function dismissBanner(choice) {
    localStorage.setItem(COOKIE_KEY, choice);
    if (cookieBanner) cookieBanner.classList.remove('active');
  }

  if (cookieAccept) cookieAccept.addEventListener('click', () => dismissBanner('accepted'));
  if (cookieDecline) cookieDecline.addEventListener('click', () => dismissBanner('essential'));

  // Escape cierra los modales legales abiertos.
  //
  // Antes esta misma línea llamaba a closeCmdPalette(), que quedó sin definir
  // cuando la paleta se movió al ERP. No era un aviso: era un ReferenceError
  // en cada pulsación de Escape, y al ser la primera sentencia del bloque la
  // línea siguiente no llegaba a ejecutarse, así que ningún modal se cerraba
  // con el teclado.
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.active').forEach((m) => closeModal(m));
    }
  });
});
