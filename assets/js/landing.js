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
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
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
});
