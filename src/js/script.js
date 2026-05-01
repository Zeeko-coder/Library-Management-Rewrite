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
if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });
}

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

// ===== Show fields based on the user role =====
const roleSelect = document.getElementById('role');
if (roleSelect) {
    roleSelect.addEventListener('change', function () {
        const studentFields = document.getElementById('studentFields');
        const librarianFields = document.getElementById('librarianFields');

        if (studentFields) studentFields.style.display = 'none';
        if (librarianFields) librarianFields.style.display = 'none';

        if (this.value === 'student' && studentFields) {
            studentFields.style.display = 'block';
        } else if (this.value === 'librarian' && librarianFields) {
            librarianFields.style.display = 'block';
        }
    });
}

// Login Modal Events
if (loginBtn) loginBtn.addEventListener('click', () => openModal(loginModal));
if (getStartedBtn) getStartedBtn.addEventListener('click', () => openModal(loginModal));
if (closeLogin) closeLogin.addEventListener('click', () => closeModal(loginModal));
if (showRegister) {
    showRegister.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal(loginModal);
        setTimeout(() => openModal(registerModal), 300);
    });
}

// Register Modal Events
if (registerBtn) registerBtn.addEventListener('click', () => openModal(registerModal));
if (closeRegister) closeRegister.addEventListener('click', () => closeModal(registerModal));
if (showLogin) {
    showLogin.addEventListener('click', (e) => {
        e.preventDefault();
        closeModal(registerModal);
        setTimeout(() => openModal(loginModal), 300);
    });
}

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
if (learnMoreBtn) {
    learnMoreBtn.addEventListener('click', () => {
        const aboutSection = document.getElementById('about');
        if (aboutSection) {
            aboutSection.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
}

// About CTA Button - Scroll to Features
if (aboutCtaBtn) {
    aboutCtaBtn.addEventListener('click', () => {
        const featuresSection = document.getElementById('features');
        if (featuresSection) {
            featuresSection.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
}

// ===== Form Submissions =====
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const emailInput = document.getElementById('loginEmail');
        const passwordInput = document.getElementById('loginPassword');
        const email = emailInput ? emailInput.value : '';
        const password = passwordInput ? passwordInput.value : '';

        // Simulate login (replace with actual authentication)
        console.log('Login attempt:', { email, password });

        // Show success message
        alert('Login functionality will be implemented with backend integration.');
        closeModal(loginModal);
    });
}

if (contactForm) {
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
}

// ===== Smooth Scroll for All Anchor Links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
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

// ===== Hero Background Slideshow =====
function initHeroSlideshow() {
    const slides = document.querySelectorAll('.hero-bg-slides .slide');
    if (slides.length <= 1) return;

    let currentSlide = 0;
    const slideInterval = 5000; // Change image every 5 seconds

    function nextSlide() {
        slides[currentSlide].classList.remove('active');
        currentSlide = (currentSlide + 1) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    setInterval(nextSlide, slideInterval);
}

// Initialize slideshow
document.addEventListener('DOMContentLoaded', () => {
    initHeroSlideshow();
});

// ===== Console Welcome Message =====
console.log('%c Welcome to LibroTech! ', 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 20px; padding: 15px; border-radius: 8px;');
console.log('%c Library Management System - Ready to use ', 'color: #4f46e5; font-size: 14px; font-weight: bold;');
