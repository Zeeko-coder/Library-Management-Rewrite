<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/studentLogin.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="login-container">
        <div class="role-badge">
            <i class="fas fa-user-graduate"></i>
        </div>
        <h1>Student Portal</h1>
        <p>LibroTech Management System</p>

        <form action="../auth/process_student_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. jhon_doe" required>
            </div>

            <div class="form-group">
                <label for="otpMethod">OTP Method</label>
                <select name="otpMethod" id="otpMethod" required>
                    <option value="">Select OTP Method</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Type your password" required>
            </div>

            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<div class="success-message" style="color: #059669; font-size: 13px; margin-bottom: 10px;">' . htmlspecialchars($_SESSION['success']) . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Portal
            </button>
        </form>

        <div class="back-home">
            <a href="loginAs.php">
                <i class="fas fa-arrow-left"></i>
                Switch Login Type
            </a>
        </div>

        <div class="register-link" style="margin-top: 15px; font-size: 14px;">
            <p>Don't have an account? <a href="studentRegister.php" style="color: var(--accent-color); font-weight: 600; text-decoration: none;">Sign up</a></p>
        </div>
    </div>
</body>

</html>