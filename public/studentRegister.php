<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/studentRegister.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="register-container">

        <div class="logo">
            <i class="fas fa-user-graduate"></i>
        </div>

        <h1>Student Registration</h1>
        <p>LibroTech Library Management System</p>

        <form action="../auth/process_student_register.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" placeholder="Jhon" required>
                </div>

                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="civilStatus">Civil Status</label>
                    <select id="civilStatus" name="civilStatus" required>
                        <option value="">Select civil status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phonenumber">Phone Number</label>
                    <input type="tel" id="phonenumber" name="phonenumber" placeholder="e.g. 639123456789" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. student_123" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create your password" required>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Re-type your password" required>
                </div>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_SESSION['success']) . '</p>';
                unset($_SESSION['success']);
            }
            ?>

            <div class="agreement-group">
                <label class="agreement-checkbox">
                    <input type="checkbox" id="agreement" name="agreement">
                    <span>I agree to the <a href="#" class="terms-link">Terms and Conditions</a> of LibroTech</span>
                </label>
                <div class="terms-summary">
                    By checking this box, you agree to follow the library policies, maintain the condition of borrowed books, and adhere to the specified return dates.
                </div>
            </div>

            <button type="submit" class="register-btn" id="registerBtn" disabled>
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <div class="login-link">
            <p>Already have an account? <a href="studentLogin.php">Sign in</a></p>
        </div>

        <div class="back-home">
            <a href="loginAs.php">
                <i class="fas fa-arrow-left"></i>
                Back to Login Options
            </a>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Terms and Conditions</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="terms-body">
                <h4>1. Data Collection</h4>
                <p>We collect personal information including your name, email, phone number, and academic details to manage your library account. This data is used for:</p>
                <ul>
                    <li>Maintaining borrowing records.</li>
                    <li>Sending automated notifications for due dates and overdues.</li>
                    <li>Ensuring account security and system integrity.</li>
                </ul>
                <p>All sensitive data is encrypted using industry-standard AES-256 encryption.</p>

                <h4>2. Conditions of Use</h4>
                <p>By registering at LibroTech, you agree to the following:</p>
                <ul>
                    <li>You will provide accurate and up-to-date information during registration.</li>
                    <li>You are responsible for the physical condition of all books borrowed under your account.</li>
                    <li>Books must be returned on or before the specified due date.</li>
                    <li>Losing or damaging books may result in fines or suspension of borrowing privileges.</li>
                    <li>Unauthorized access to other user accounts or system internals is strictly prohibited.</li>
                </ul>

                <h4>3. System Operations</h4>
                <p>LibroTech reserves the right to suspend accounts that violate library policies or engage in suspicious activity.</p>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('termsModal');
        const termsLink = document.querySelector('.terms-link');
        const closeBtn = document.querySelector('.close-modal');
        const agreement = document.getElementById('agreement');
        const registerBtn = document.getElementById('registerBtn');

        // Toggle Register Button
        agreement.addEventListener('change', function() {
            registerBtn.disabled = !this.checked;
        });

        // Modal Open
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        // Modal Close
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });

        window.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    </script>
</body>

</html>