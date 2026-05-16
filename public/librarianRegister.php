<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Registration - LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/librarianRegister.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-user-tie"></i>
        </div>
        <h1>Librarian Registration</h1>
        <p>LibroTech Library Management System</p>

        <form action="../auth/process_librarian_register.php" method="POST">
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
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. librarian123" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
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
                    By checking this box, you agree to manage library resources professionally, adhere to administrative policies, and ensure accurate record-keeping of all circulation activities.
                </div>
            </div>

            <button type="submit" class="register-btn" id="registerBtn" disabled>
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <script>
            document.getElementById('agreement').addEventListener('change', function() {
                document.getElementById('registerBtn').disabled = !this.checked;
            });
        </script>

        <div class="login-link">
            <p>Already have an account? <a href="librarianLogin.php">Sign in</a></p>
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
                <p>LibroTech collects professional and personal information (Name, Email, Phone, Credentials) to facilitate library administration. This data is used for:</p>
                <ul>
                    <li>Authorized access to the librarian dashboard.</li>
                    <li>Managing book cataloging and circulation activities.</li>
                    <li>Communicating with students regarding borrowing requests.</li>
                </ul>
                <p>All administrative data is protected with AES-256 encryption standards.</p>

                <h4>2. Conditions of Use</h4>
                <p>As a librarian, you agree to:</p>
                <ul>
                    <li>Maintain accurate and truthful records of library inventory.</li>
                    <li>Process borrowing requests and returns promptly and fairly.</li>
                    <li>Protect the privacy of students and their borrowing history.</li>
                    <li>Use system administrative privileges only for authorized library business.</li>
                    <li>Report any technical issues or security vulnerabilities immediately.</li>
                </ul>

                <h4>3. Administrative Responsibility</h4>
                <p>Failure to adhere to library policies or misuse of the system may lead to administrative review and revocation of system access.</p>
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