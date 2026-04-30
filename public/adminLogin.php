<?php
session_start();
$fieldErrors = $_SESSION['field_errors'] ?? [];
unset($_SESSION['field_errors']);
$page_title = "Admin Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/adminLogin.css">
    <style>
       
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-user-shield"></i>
        </div>
        <h1>Admin Login</h1>
        <p>LibroTech Library Management System</p>

        <form action="../auth/process_admin_login.php" method="POST">
            <div class="form-group">
                <label for="adminID">Admin ID</label>
                <input type="text" id="adminID" name="adminID" placeholder="Enter Admin ID" 
                       class="<?php echo isset($fieldErrors['adminID']) ? 'input-error' : ''; ?>">
                
                <?php if (isset($fieldErrors['adminID'])): ?>
                    <div class="error-message"><?php echo $fieldErrors['adminID']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password"
                       class="<?php echo isset($fieldErrors['password']) ? 'input-error' : ''; ?>">
                
                <?php if (isset($fieldErrors['password'])): ?>
                    <div class="error-message"><?php echo $fieldErrors['password']; ?></div>
                <?php endif; ?>
            </div>


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
    </div>
</body>
</html>
