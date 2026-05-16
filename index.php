<?php
include 'database/db_connection.php';

// Fetch settings from database
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($results as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Helper to safely get setting
function getSetting($key, $default = '') {
    global $settings;
    return $settings[$key] ?? $default;
}

$library_name = getSetting('library_name', 'LibroTech');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="src/css/styles.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="img/techbook.png" alt="LibroTech Logo">
                <span><?php echo htmlspecialchars($library_name); ?></span>
            </div>

            <ul class="nav-menu">
                <li><a href="#home" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <li><a href="#features" class="nav-link">Features</a></li>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            <div class="nav-buttons">
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="hero">
        <div class="hero-bg-slides">
            <div class="slide active" style="background-image: url('img/hero-bg.png');"></div>
            <div class="slide" style="background-image: url('img/hero-bg-2.png');"></div>
            <div class="slide" style="background-image: url('img/hero-bg-3.png');"></div>
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Welcome to <span class="highlight"><?php echo htmlspecialchars($library_name); ?></span></h1>
            <p class="hero-subtitle">Your Modern Library Management Solution</p>
            <p class="hero-description">
                Streamline your library operations with our comprehensive management system.
                From cataloging to circulation, we've got you covered.
            </p>
            <div class="hero-buttons">
                <a href="public/loginAs.php"><button class="btn btn-primary btn-large" id="getStartedBtn">Get Started</button></a>
                <a href="#about"><button class="btn btn-outline btn-large" id="learnMoreBtn">Learn More</button></a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <i class="fas fa-book"></i>
                    <span class="stat-number">10,000+</span>
                    <span class="stat-label">Books Managed</span>
                </div>
                <div class="stat">
                    <i class="fas fa-users"></i>
                    <span class="stat-number">5,000+</span>
                    <span class="stat-label">Active Users</span>
                </div>
                <div class="stat">
                    <i class="fas fa-university"></i>
                    <span class="stat-number">50+</span>
                    <span class="stat-label">Partner Libraries</span>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
            <span>Scroll Down</span>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">About Us</h2>
                <p class="section-subtitle">Discover what makes LibroTech special</p>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <img src="img/about-laptop.png" alt="LibroTech Dashboard" class="floating-mockup">
                </div>
                <div class="about-text">
                    <h3>A Unified Library Management Ecosystem</h3>
                    <p>
                        LibroTech is a specialized management system designed to bridge the gap between students, librarians, and administrators. Our platform streamlines the entire lifecycle of a book—from initial cataloging to real-time availability tracking.
                    </p>
                    <p>
                        Built with a focus on security and efficiency, LibroTech features multi-factor authentication (OTP), encrypted user data, and automated stock synchronization to ensure that physical inventory is always accurately reflected digitally.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Role-Based Dashboards</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Secure OTP Verification</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Automated Stock Sync</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Overdue Tracking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Features</h2>
                <p class="section-subtitle">Everything you need to run your library efficiently</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3>Smart Cataloging</h3>
                    <p>Librarians can manage inventory with automated status updates that sync instantly with the student catalog.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>MFA Security</h3>
                    <p>Enhanced protection using OTP verification for student and librarian logins via SMS and Email.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Circulation Desk</h3>
                    <p>Full control over book borrowing, returns, and approvals with precise quantity tracking.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <h3>Data Privacy</h3>
                    <p>Industry-standard AES-256 encryption ensures that all sensitive member information is protected at rest.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Reports & Analytics</h3>
                    <p>Admins can generate detailed borrowing insights, overdue monitoring, and library usage statistics.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Overdue Tracking</h3>
                    <p>Automated identification of overdue books with quick-notify buttons for librarians to alert students.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Contact Us</h2>
                <p class="section-subtitle">Get in touch with our team</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>We'd Love to Hear From You</h3>
                    <p>Have questions about LibroTech? Our team is here to help you get started.</p>
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Address</h4>
                                <p><?php echo htmlspecialchars(getSetting('library_address', '123 Library Street, Book City, BC 12345')); ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p><?php echo htmlspecialchars(getSetting('library_phone', '+1 (555) 123-4567')); ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p><?php echo htmlspecialchars(getSetting('library_email', 'support@librotech.com')); ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Business Hours</h4>
                                <p>Mon - Fri: 9:00 AM - 6:00 PM</p>
                            </div>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="contact-form">
                    <form id="contactForm">
                        <div class="form-group">
                            <input type="text" name="name" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" name="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="text" name="subject" placeholder="Subject">
                        </div>
                        <div class="form-group">
                            <textarea name="message" placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <button class="modal-close" id="closeSuccess">&times;</button>
            <div class="modal-header">
                <h2>Message Sent!</h2>
            </div>
            <div class="modal-body" style="padding: 0 30px 30px;">
                <div style="width: 80px; height: 80px; background: #dcfce7; color: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 40px;">
                    <i class="fas fa-check"></i>
                </div>
                <p id="successMessage" style="color: var(--text-light); margin-bottom: 25px;"></p>
                <button class="btn btn-primary btn-block" onclick="closeModal(document.getElementById('successModal'))">Understood</button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-book-reader"></i> <?php echo htmlspecialchars($library_name); ?></h3>
                    <p>Modern library management for the digital age.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($library_name); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="src/js/script.js"></script>
</body>

</html>