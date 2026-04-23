// ===== DOM Elements =====
const navbar = document.querySelector('.navbar');
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-link');
const loginBtn = document.getElementById('loginBtn');
const registerBtn = document.getElementById('registerBtn');
const getStartedBtn = document.getElementById('getStartedBtn');
const learnMoreBtn = document.getElementById('learnMoreBtn');
const loginModal = document.getElementById('loginModal');
const registerModal = document.getElementById('registerModal');
const closeLogin = document.getElementById('closeLogin');
const closeRegister = document.getElementById('closeRegister');
const showRegister = document.getElementById('showRegister');
const showLogin = document.getElementById('showLogin');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const contactForm = document.getElementById('contactForm');
const aboutCtaBtn = document.getElementById('aboutCtaBtn');

// ===== Navbar Scroll Effect =====
window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }

    // Update active nav link based on scroll position
    updateActiveNavLink();

    // Trigger scroll animations
    handleScrollAnimations();
});

// ===== Update Active Nav Link =====
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const scrollPosition = window.scrollY + 100;

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const sectionId = section.getAttribute('id');

        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${sectionId}`) {
                    link.classList.add('active');
                }
            });
        }
    });
}

// ===== Mobile Menu Toggle =====
hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
});

// Close mobile menu when clicking on a link
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// ===== Modal Functions =====
function openModal(modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Login Modal Events
loginBtn.addEventListener('click', () => openModal(loginModal));
getStartedBtn.addEventListener('click', () => openModal(loginModal));
closeLogin.addEventListener('click', () => closeModal(loginModal));
showRegister.addEventListener('click', (e) => {
    e.preventDefault();
    closeModal(loginModal);
    setTimeout(() => openModal(registerModal), 300);
});

// Register Modal Events
registerBtn.addEventListener('click', () => openModal(registerModal));
closeRegister.addEventListener('click', () => closeModal(registerModal));
showLogin.addEventListener('click', (e) => {
    e.preventDefault();
    closeModal(registerModal);
    setTimeout(() => openModal(loginModal), 300);
});

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === loginModal) {
        closeModal(loginModal);
    }
    if (e.target === registerModal) {
        closeModal(registerModal);
    }
});

// Close modal with Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeModal(loginModal);
        closeModal(registerModal);
    }
});

// ===== Learn More Button - Smooth Scroll to About =====
learnMoreBtn.addEventListener('click', () => {
    document.getElementById('about').scrollIntoView({
        behavior: 'smooth'
    });
});

// About CTA Button - Scroll to Features
aboutCtaBtn.addEventListener('click', () => {
    document.getElementById('features').scrollIntoView({
        behavior: 'smooth'
    });
});

// ===== Form Submissions =====
loginForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    // Simulate login (replace with actual authentication)
    console.log('Login attempt:', { email, password });

    // Show success message
    alert('Login functionality will be implemented with backend integration.');
    closeModal(loginModal);
});

registerForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const phone = document.getElementById('registerPhone').value;
    const password = document.getElementById('registerPassword').value;
    const confirm = document.getElementById('registerConfirm').value;

    // Validate passwords match
    if (password !== confirm) {
        alert('Passwords do not match!');
        return;
    }

    // Simulate registration (replace with actual API call)
    console.log('Registration:', { name, email, phone, password });

    // Show success message
    alert('Registration functionality will be implemented with backend integration.');
    closeModal(registerModal);
});

contactForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(contactForm);
    const data = Object.fromEntries(formData);

    // Simulate contact form submission
    console.log('Contact form:', data);

    // Show success message
    alert('Thank you for your message! We will get back to you soon.');
    contactForm.reset();
});

// ===== Smooth Scroll for All Anchor Links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href.length > 1) {
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

// ===== Scroll Animations Manager =====
function handleScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -80px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    // Observe section headers
    document.querySelectorAll('.section-header .section-title, .section-header .section-subtitle').forEach(el => {
        observer.observe(el);
    });

    // Observe about section elements
    document.querySelectorAll('.about-image, .about-text').forEach(el => {
        observer.observe(el);
    });

    // Observe about features
    document.querySelectorAll('.about-feature').forEach((el, index) => {
        el.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(el);
    });

    // Observe feature cards with staggered delays
    document.querySelectorAll('.feature-card').forEach((el, index) => {
        el.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(el);
    });

    // Observe contact elements
    document.querySelectorAll('.contact-info, .contact-form').forEach(el => {
        observer.observe(el);
    });

    // Observe contact items
    document.querySelectorAll('.contact-item').forEach((el, index) => {
        el.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(el);
    });

    // Observe social links
    document.querySelectorAll('.social-links').forEach(el => {
        observer.observe(el);
    });

    // Observe footer sections
    document.querySelectorAll('.footer-section').forEach((el, index) => {
        el.style.transitionDelay = `${index * 0.15}s`;
        observer.observe(el);
    });

    // Prevent re-running
    window.removeEventListener('scroll', handleScrollAnimations);
}

// Run scroll animations on load
document.addEventListener('DOMContentLoaded', () => {
    handleScrollAnimations();
});

// ===== Parallax Effect for Hero =====
window.addEventListener('scroll', () => {
    const scrolled = window.scrollY;
    const hero = document.querySelector('.hero');
    if (scrolled < window.innerHeight) {
        hero.style.backgroundPositionY = `${scrolled * 0.5}px`;
    }
});

// ===== Counter Animation for Stats =====
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);

    function updateCounter() {
        start += increment;
        if (start < target) {
            element.textContent = Math.floor(start).toLocaleString();
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target.toLocaleString();
        }
    }

    updateCounter();
}

// Trigger counter animation when stats are visible
const statsObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const statNumbers = entry.target.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const text = stat.textContent;
                const hasPlus = text.includes('+');
                const hasComma = text.includes(',');

                if (hasComma) {
                    const num = parseInt(text.replace(/,/g, ''));
                    if (!isNaN(num)) {
                        animateCounter(stat, num);
                    }
                }
            });
            statsObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const heroStats = document.querySelector('.hero-stats');
if (heroStats) {
    statsObserver.observe(heroStats);
}

// ===== Page Load Animations =====
window.addEventListener('load', () => {
    // Add fade-in class to elements that should animate on scroll
    document.querySelectorAll('.section-header, .about-content, .features-grid, .contact-content, .footer-content').forEach(el => {
        el.classList.add('fade-in');
    });

    // Trigger initial animations
    setTimeout(() => {
        document.querySelectorAll('.fade-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
});

// ===== Enhanced Scroll Indicator Animation =====
const scrollIndicator = document.querySelector('.scroll-indicator');
if (scrollIndicator) {
    window.addEventListener('scroll', () => {
        const scrollPercent = window.scrollY / (document.body.scrollHeight - window.innerHeight);
        scrollIndicator.style.opacity = 1 - scrollPercent * 3;
        if (scrollPercent > 0.3) {
            scrollIndicator.style.display = 'none';
        } else {
            scrollIndicator.style.display = 'flex';
        }
    });
}

// ===== Button Ripple Effect =====
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;

        this.style.position = 'relative';
        this.style.overflow = 'hidden';
        this.appendChild(ripple);

        setTimeout(() => ripple.remove(), 600);
    });
});

// Add ripple keyframes
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);

// ===== Floating Animation for Feature Icons =====
document.querySelectorAll('.feature-icon').forEach((icon, index) => {
    icon.style.animation = `float 3s ease-in-out ${index * 0.2}s infinite`;
});

// Add float keyframes if not already present
const floatStyle = document.createElement('style');
floatStyle.textContent = `
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
`;
document.head.appendChild(floatStyle);

// ===== Typing Effect for Hero Subtitle (Optional Enhancement) =====
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.textContent = '';

    function type() {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }

    type();
}

// ===== Console Welcome Message =====
console.log('%c Welcome to LibroTech! ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 20px; padding: 15px; border-radius: 8px;');
console.log('%c Library Management System - Ready to use ', 'color: #4f46e5; font-size: 14px; font-weight: bold;');
