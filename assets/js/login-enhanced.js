// ============================================
// LOGIN PAGE ENHANCEMENTS
// Floating labels, form validation, animations
// ============================================

document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // HEXAGON PATTERN ANIMATION (Right Panel)
    // ============================================

    const patternCanvas = document.getElementById('hexagonPatternCanvas');
    if (patternCanvas) {
        const ctx = patternCanvas.getContext('2d');
        let hexagons = [];

        function resizeCanvas() {
            patternCanvas.width = patternCanvas.offsetWidth;
            patternCanvas.height = patternCanvas.offsetHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        class Hexagon {
            constructor(x, y, size) {
                this.x = x;
                this.y = y;
                this.size = size;
                this.opacity = Math.random() * 0.25;
                this.fadeDirection = Math.random() > 0.5 ? 1 : -1;
                this.fadeSpeed = 0.001 + Math.random() * 0.002;
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
                ctx.lineWidth = 1;
                ctx.stroke();
            }

            update() {
                this.opacity += this.fadeSpeed * this.fadeDirection;
                if (this.opacity > 0.25 || this.opacity < 0.05) {
                    this.fadeDirection *= -1;
                }
                this.draw();
            }
        }

        function createHexagons() {
            hexagons = [];
            const size = 25;
            const spacing = size * 1.8;

            for (let y = -size; y < patternCanvas.height + size; y += spacing * 1.5) {
                for (let x = -size; x < patternCanvas.width + size; x += spacing * Math.sqrt(3)) {
                    const offsetX = (y / (spacing * 1.5)) % 2 === 0 ? 0 : spacing * Math.sqrt(3) / 2;
                    hexagons.push(new Hexagon(x + offsetX, y, size));
                }
            }
        }

        createHexagons();
        window.addEventListener('resize', createHexagons);

        function animate() {
            ctx.clearRect(0, 0, patternCanvas.width, patternCanvas.height);
            hexagons.forEach(hex => hex.update());
            requestAnimationFrame(animate);
        }
        animate();
    }

    // ============================================
    // FLOATING LABELS
    // ============================================

    const floatingInputs = document.querySelectorAll('.floating-label-group input');

    floatingInputs.forEach(input => {
        // Check if input has value on page load (autofill)
        if (input.value) {
            input.classList.add('has-value');
        }

        // Monitor for changes
        input.addEventListener('input', function() {
            if (this.value) {
                this.classList.add('has-value');
            } else {
                this.classList.remove('has-value');
            }
        });

        // Handle autofill
        input.addEventListener('animationstart', function(e) {
            if (e.animationName === 'onAutoFillStart') {
                this.classList.add('has-value');
            }
        });
    });

    // ============================================
    // ENHANCED PASSWORD TOGGLE
    // ============================================

    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordToggle && passwordInput && toggleIcon) {
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icon
            if (type === 'text') {
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        });
    }

    // ============================================
    // FORM VALIDATION & SUBMISSION
    // ============================================

    const loginForm = document.getElementById('loginForm');
    const loginButton = document.querySelector('.btn-login-enhanced');

    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            // Add loading state
            if (loginButton) {
                loginButton.classList.add('loading');
                loginButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            }

            // Form will submit normally, loading state will show during processing
        });
    }

    // ============================================
    // AUTO-HIDE ALERT AFTER 5 SECONDS
    // ============================================

    const alert = document.querySelector('.alert-enhanced');
    if (alert) {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    }
});

// CSS for autofill detection
const style = document.createElement('style');
style.textContent = `
    @keyframes onAutoFillStart {
        from { /*dummy*/ }
        to { /*dummy*/ }
    }
    input:-webkit-autofill {
        animation-name: onAutoFillStart;
    }
`;
document.head.appendChild(style);
