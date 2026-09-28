// ============================================
// LANDING PAGE - PROFESSIONAL INTERACTIONS
// Subtle hexagon animation, scroll effects
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // HEXAGON CANVAS ANIMATION (Subtle)
    // ============================================

    const canvas = document.getElementById('hexagonCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let hexagons = [];

        // Set canvas size
        function resizeCanvas() {
            const parent = canvas.parentElement;
            canvas.width = parent ? parent.offsetWidth : window.innerWidth;
            canvas.height = parent ? parent.offsetHeight : window.innerHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        // Hexagon class
        class Hexagon {
            constructor(x, y, size) {
                this.x = x;
                this.y = y;
                this.size = size;
                this.opacity = Math.random() * 0.45; // Más notable
                this.fadeDirection = Math.random() > 0.5 ? 1 : -1;
                this.fadeSpeed = 0.003 + Math.random() * 0.006; // Desvanecimiento más rápido
            }

            draw() {
                ctx.beginPath();
                for (let i = 0; i < 6; i++) {
                    const angle = (Math.PI / 3) * i;
                    const x = this.x + this.size * Math.cos(angle);
                    const y = this.y + this.size * Math.sin(angle);
                    if (i === 0) {
                        ctx.moveTo(x, y);
                    } else {
                        ctx.lineTo(x, y);
                    }
                }
                ctx.closePath();
                ctx.strokeStyle = `rgba(255, 160, 0, ${this.opacity})`;
                ctx.lineWidth = 1.5;
                ctx.stroke();
            }

            update() {
                // Fade in/out
                this.opacity += this.fadeSpeed * this.fadeDirection;
                if (this.opacity > 0.45 || this.opacity < 0.02) {
                    this.fadeDirection *= -1;
                }
                this.draw();
            }
        }

        // Create hexagon grid
        function createHexagons() {
            hexagons = [];
            const size = 50; // Hexágonos más grandes
            const spacing = size * 1.8;

            for (let y = -size; y < canvas.height + size; y += spacing * 1.5) {
                for (let x = -size; x < canvas.width + size; x += spacing * Math.sqrt(3)) {
                    const offsetX = (y / (spacing * 1.5)) % 2 === 0 ? 0 : spacing * Math.sqrt(3) / 2;
                    hexagons.push(new Hexagon(x + offsetX, y, size));
                }
            }
        }

        createHexagons();
        window.addEventListener('resize', createHexagons);

        // Animation loop
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hexagons.forEach(hex => hex.update());
            requestAnimationFrame(animate);
        }
        animate();
    }

    // ============================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ============================================

    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        observer.observe(card);
    });

    // Observe stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        observer.observe(card);
    });

    // ============================================
    // COUNTER ANIMATION (Stats)
    // ============================================

    function animateCounter(element, target, duration = 2000) {
        const start = 0;
        const increment = target / (duration / 16); // 60fps
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target + (element.dataset.suffix || '');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current) + (element.dataset.suffix || '');
            }
        }, 16);
    }

    // Trigger counter animation when visible
    const statsObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                entry.target.classList.add('counted');
                const number = entry.target.querySelector('.stat-number');
                if (number) {
                    const target = parseInt(number.dataset.target);
                    animateCounter(number, target);
                }
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stat-card').forEach(card => {
        statsObserver.observe(card);
    });

    // ============================================
    // SMOOTH SCROLLING
    // ============================================

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ============================================
    // SCROLL INDICATOR HIDE ON SCROLL
    // ============================================

    const scrollIndicator = document.querySelector('.scroll-indicator');
    if (scrollIndicator) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                scrollIndicator.style.opacity = '0';
            } else {
                scrollIndicator.style.opacity = '1';
            }
        });
    }

    // ============================================
    // REGISTRO DEMO SAAS - MULTI-TENANT & FINGERPRINT
    // ============================================

    const modalOverlay = document.getElementById('demoModalOverlay');
    const btnOpenHero = document.getElementById('btnOpenDemoModal');
    const btnOpenCta = document.getElementById('btnOpenDemoModalCta');
    const btnCloseModal = document.getElementById('btnCloseDemoModal');
    const formRegisterDemo = document.getElementById('formRegisterDemo');
    const alertBox = document.getElementById('demoAlertBox');
    const btnSubmit = document.getElementById('btnSubmitDemo');
    const inputFingerprint = document.getElementById('device_fingerprint');

    // Inicializar FingerprintJS del cliente
    let clientVisitorId = null;
    if (window.FingerprintJS) {
        FingerprintJS.load()
            .then(fp => fp.get())
            .then(result => {
                clientVisitorId = result.visitorId;
                if (inputFingerprint) {
                    inputFingerprint.value = clientVisitorId;
                }
            })
            .catch(err => {
                console.warn('FingerprintJS fallback to backend generation:', err);
            });
    }

    function openModal() {
        if (!modalOverlay) return;
        modalOverlay.style.display = 'flex';
        // Forzar reflow para animación CSS
        setTimeout(() => {
            modalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            const firstInput = document.getElementById('company_name');
            if (firstInput) firstInput.focus();
        }, 10);
    }

    function closeModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('active');
        document.body.style.overflow = '';
        setTimeout(() => {
            modalOverlay.style.display = 'none';
            if (alertBox) {
                alertBox.style.display = 'none';
                alertBox.className = 'demo-alert';
                alertBox.innerHTML = '';
            }
        }, 300);
    }

    if (btnOpenHero) btnOpenHero.addEventListener('click', openModal);
    if (btnOpenCta) btnOpenCta.addEventListener('click', openModal);
    if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);

    // Cerrar al presionar Escape o clic fuera
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('active')) {
            closeModal();
        }
    });

    // Toggle de visibilidad de contraseña
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const inputPassword = document.getElementById('admin_password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (btnTogglePassword && inputPassword && toggleIcon) {
        btnTogglePassword.addEventListener('click', function() {
            const isPassword = inputPassword.getAttribute('type') === 'password';
            inputPassword.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    }

    // Envío del formulario de registro
    if (formRegisterDemo) {
        formRegisterDemo.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Asegurar que el input de huella digital esté poblado
            if (inputFingerprint && clientVisitorId && !inputFingerprint.value) {
                inputFingerprint.value = clientVisitorId;
            }

            const companyName = document.getElementById('company_name').value.trim();
            const adminName = document.getElementById('admin_name').value.trim();
            const adminEmail = document.getElementById('admin_email').value.trim();
            const adminPassword = document.getElementById('admin_password').value;

            // Validaciones rápidas del lado del cliente
            if (companyName.length < 3) {
                showAlert('El nombre de la empresa debe tener al menos 3 caracteres.', 'error');
                return;
            }
            if (!adminEmail.includes('@') || !adminEmail.includes('.')) {
                showAlert('Por favor ingresa un correo electrónico válido.', 'error');
                return;
            }
            if (adminPassword.length < 8) {
                showAlert('La contraseña debe tener al menos 8 caracteres.', 'error');
                return;
            }

            // Estado visual de carga
            setLoading(true);

            try {
                const response = await fetch('php/api_register_demo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        company_name: companyName,
                        admin_name: adminName,
                        admin_email: adminEmail,
                        admin_password: adminPassword,
                        device_fingerprint: inputFingerprint ? inputFingerprint.value : null
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showAlert(data.message + '<br><small>Redirigiendo al inicio de sesión en 2 segundos...</small>', 'success');
                    formRegisterDemo.reset();
                    setTimeout(() => {
                        window.location.href = data.data && data.data.login_url ? data.data.login_url : 'login.php';
                    }, 2000);
                } else {
                    showAlert(data.message || 'No fue posible registrar la empresa demo.', 'error');
                    setLoading(false);
                }
            } catch (err) {
                console.error('Error al registrar empresa demo:', err);
                showAlert('Error de conexión con el servidor. Por favor verifica que Apache y la base de datos estén activos.', 'error');
                setLoading(false);
            }
        });
    }

    function showAlert(msg, type) {
        if (!alertBox) return;
        alertBox.className = 'demo-alert ' + type;
        alertBox.innerHTML = msg;
        alertBox.style.display = 'block';
    }

    function setLoading(isLoading) {
        if (!btnSubmit) return;
        btnSubmit.disabled = isLoading;
        const btnText = btnSubmit.querySelector('.btn-text');
        const btnSpinner = btnSubmit.querySelector('.btn-spinner');

        if (btnText && btnSpinner) {
            btnText.style.display = isLoading ? 'none' : 'inline-block';
            btnSpinner.style.display = isLoading ? 'inline-block' : 'none';
        }
    }
});
