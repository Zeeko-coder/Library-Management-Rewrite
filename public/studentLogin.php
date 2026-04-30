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
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-user-graduate"></i>
        </div>
        <h1>Student Login</h1>
        <p>LibroTech Library Management System</p>

        <form action="../auth/process_student_login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="e.g. jhon_doe" required>
            </div>

            <div class="form-group">
                <label for="otpMethod">OTP Method</label>
                <select name="otpMethod" id="otpMethod">
                    <option value="">Select OTP Method</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Type your password">
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

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <div class="back-home">
            <a href="loginAs.php">
                <i class="fas fa-arrow-left"></i>
                Back to Login Options
            </a>
        </div>

        <div class="register-link">
            <p>Don't have an account? <a href="studentRegister.php">Sign up</a></p>
        </div>
    </div>
</body>

</html>