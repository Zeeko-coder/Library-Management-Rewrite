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
                <img src="img/logo.svg" alt="LibroTech Logo">
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
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Welcome to <span class="highlight">LibroTech</span></h1>
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
                    <div class="image-placeholder">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="about-text">
                    <h3>Transforming Library Management Since 2020</h3>
                    <p>
                        LibroTech is a cutting-edge library management system designed to modernize
                        how libraries operate. We combine intuitive design with powerful features to
                        make library management effortless.
                    </p>
                    <p>
                        Our mission is to empower librarians, educators, and administrators with tools
                        that simplify daily operations while enhancing the user experience for patrons.
                    </p>
                    <div class="about-features">
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>User-Friendly Interface</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Cloud-Based Access</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>24/7 Support</span>
                        </div>
                        <div class="about-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Secure & Reliable</span>
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
                    <h3>Book Cataloging</h3>
                    <p>Organize and manage your entire book collection with advanced search and categorization features.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h3>Member Management</h3>
                    <p>Track members, manage registrations, and maintain user profiles with ease.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h3>Circulation System</h3>
                    <p>Handle book borrowing, returns, and renewals with automated tracking and notifications.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Advanced Search</h3>
                    <p>Powerful search capabilities to quickly find books by title, author, genre, or ISBN.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Reports & Analytics</h3>
                    <p>Generate detailed reports on circulation, popular books, and library usage statistics.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h3>Notifications</h3>
                    <p>Automated reminders for due dates, overdue books, and new arrivals via email or SMS.</p>
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
                                <p>123 Library Street, Book City, BC 12345</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Phone</h4>
                                <p>+1 (555) 123-4567</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>support@librotech.com</p>
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
                            <input type="text" placeholder="Your Name" required>
                        </div>
                        <div class="form-group">
                            <input type="email" placeholder="Your Email" required>
                        </div>
                        <div class="form-group">
                            <input type="text" placeholder="Subject">
                        </div>
                        <div class="form-group">
                            <textarea placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-book-reader"></i> LibroTech</h3>
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
                <p>&copy; 2026 LibroTech. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <button class="modal-close" id="closeLogin">&times;</button>

            <div class="modal-header">
                <h2>Welcome Back</h2>
                <p>Login to access your account</p>
            </div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">Username</label>
                    <input type="text" id="loginEmail" placeholder="Example@12345" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" placeholder="Type your password" required>
                </div>
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="modal-footer">
                <p>Don't have an account? <a href="#" id="showRegister">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-content">
            <button class="modal-close" id="closeRegister">&times;</button>
            <div class="modal-header">
                <h2>Create Account</h2>
                <p>Join LibroTech today</p>
            </div>
            <div id="registerError" class="error-message" style="display: none;"></div>
            <div id="registerSuccess" class="success-message" style="display: none;"></div>
            <form id="registerForm" action="process_register.php" method="POST">
                <div class="form-group">
                    <label for="registerName">Firstname</label>
                    <input type="text" id="registerName" name="firstname" placeholder="e.g. John" required>
                </div>

                <div class="form-group">
                    <label for="registerLastName">Lastname</label>
                    <input type="text" id="registerLastName" name="lastname" placeholder="e.g. Doe" required>
                </div>

                <div class="form-group">
                    <label for="civilStatus">Civil Status</label>
                    <select name="civilStatus">
                        <option value="">-- Select Civil status --</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="widowed">Widowed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="userRole">User Role</label>
                    <select name="userRole" id="role">
                        <option value="">-- Select User Role --</option>
                        <option value="student">Student</option>
                        <option value="librarian">Librarian</option>
                    </select>
                </div>

                <div class="form-group" id="studentFields" style="display: none;">
                    <label for="studentID">Student ID</label>
                    <input type="text" id="studentID" name="studentID" placeholder="25-xxxx" required>

                    <label for="program">Program</label>
                    <select name="program" id="program">
                        <option value="">-- Select Program --</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSIS">BSIS</option>
                        <option value="BLIS">BLIS</option>
                        <option value="BSEMEC">BSEMEC</option>
                    </select>
                </div>

                <div class="form-group" id="librarianFields" style="display: none;">
                    <label for="librarianID">Librarian ID</label>
                    <input type="text" id="librarianID" name="librarianID" placeholder="LIB-xxxx" required>
                </div>

                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" placeholder="e.g. john.doe@example.com" required>
                </div>

                <div class="form-group">
                    <label for="registerPhone">Phone Number</label>
                    <input type="tel" id="registerPhone" name="phoneNumber" placeholder="e.g. +63 917 123 4567" required>
                </div>

                <div class="form-group">
                    <label for="registerUsername">Username</label>
                    <input type="text" id="registerUsername" name="username" placeholder="e.g. john_doe" required>
                </div>

                <div class="form-group">
                    <label for="registerPassword">Password</label>
                    <input type="password" id="registerPassword" name="password" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label for="registerConfirm">Confirm Password</label>
                    <input type="password" id="registerConfirm" name="confirmPassword" placeholder="Confirm your password" required>
                </div>
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        <span>I agree to the <a href="#">Terms of Service</a></span>
                    </label>
                </div>
                <input type="submit" value="Register" class="btn btn-primary btn-block" value="Register">
            </form>
            <div class="modal-footer">
                <p>Already have an account? <a href="#" id="showLogin">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="src/js/script.js"></script>
</body>

</html>