<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../src/css/otp_verification.css">
</head>

<body>
    <div class="background-overlay"></div>
    <div class="otp-container">
        <div class="logo">
            <img src="../img/techbook.png" alt="LibroTech Logo">
        </div>
        <h1>OTP Verification</h1>
        <p>We have sent a 6-digit verification code to your email address.</p>

        <form action="../auth/process_otp_verification.php" method="POST">
            <div class="otp-input-container">
                <input type="text" name="otp1" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling.focus()">
                <input type="text" name="otp2" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling.focus()">
                <input type="text" name="otp3" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling.focus()">
                <input type="text" name="otp4" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling.focus()">
                <input type="text" name="otp5" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,''); if(this.value.length==1) this.nextElementSibling.focus()">
                <input type="text" name="otp6" maxlength="1" required oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </div>
            <button type="submit" name="verify-otp" class="verify-btn">Verify Account</button>
        </form>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        ?>

        <div class="back-home">
            <a href="loginAs.php">
                <i class="fas fa-arrow-left"></i>
                Switch Login Type
            </a>
        </div>
        <br>

        <p class="resend-text">
            Didn't receive a code? <a href="../auth/process_otp_verification.php?resend=true">Resend code</a>
        </p>
    </div>

    <script>
        // Auto-focus first input on load
        window.onload = () => {
            document.getElementsByName('otp1')[0].focus();
        };

        // Handle backspace to move to previous input
        const inputs = document.querySelectorAll('.otp-input-container input');
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });
        });
    </script>
</body>

</html>