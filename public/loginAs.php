<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/loginAs.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="../img/logo.png" alt="LibroTech Logo">
        </div>
        <h1>Welcome to LibroTech</h1>
        <p>Select your login type to continue</p>

        <div class="login-options">
            <a href="adminLogin.php" class="login-btn">
                <i class="fa-regular fa-id-card"></i>
                Login as Admin
            </a>

            <a href="librarianLogin.php" class="login-btn librarian">
                <i class="fa-regular fa-address-book"></i>
                Login as Librarian
            </a>

            <a href="studentLogin.php" class="login-btn student">
                <i class="fa-regular fa-circle-user"></i>
                Login as Student
            </a>
        </div>

        <div class="back-home">
            <a href="../index.php">
                <i class="fa-regular fa-circle-left"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
