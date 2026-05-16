<?php
session_start();

require_once '../config/smtp_config.php';
require_once '../vendor/autoload.php';
include '../database/db_connection.php';
require_once '../helpers/cryptography_process.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Determine the correct login page based on temp_role
$tempRole = $_SESSION['temp_role'] ?? 'Librarian';
$loginPage = ($tempRole === 'Student') ? '../public/studentLogin.php' : '../public/librarianLogin.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $otp = "";
    for ($i = 1; $i <= 6; $i++) {
        $otp .= filter_input(INPUT_POST, 'otp' . $i, FILTER_SANITIZE_NUMBER_INT);
    }

    if (strlen($otp) !== 6) {
        $_SESSION['error'] = "Invalid OTP format.";
        header("Location: ../public/otp_verification.php");
        exit();
    }

    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['error'] = "Session expired. Please login again.";
        header("Location: $loginPage");
        exit();
    }

    $userId = $_SESSION['temp_user_id'];

    $sql = "SELECT * FROM otp WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otpRecord) {
        $_SESSION['error'] = "No OTP record found. Please resend the code.";
        header("Location: ../public/otp_verification.php");
        exit();
    }

    $currentTimestamp = time();
    $expirationTimestamp = strtotime($otpRecord['otp_expiration']);

    if ($currentTimestamp > $expirationTimestamp) {
        $_SESSION['error'] = "otp verification code is expired";
        header("Location: ../public/otp_verification.php");
        exit();
    }

    if ($otpRecord['otp_code'] !== $otp) {
        $_SESSION['error'] = "Invalid OTP code";
        header("Location: ../public/otp_verification.php");
        exit();
    }

    // Success!
    $delSql = "DELETE FROM otp WHERE user_id = ?";
    $delStmt = $pdo->prepare($delSql);
    $delStmt->execute([$userId]);

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $_SESSION['temp_username'];
    $_SESSION['role'] = $_SESSION['temp_role'];

    // Clear temp variables
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_username']);
    unset($_SESSION['temp_role']);
    unset($_SESSION['otp_method']);

    header("Location: process_role.php");
    exit();
} else if (isset($_GET['resend']) && $_GET['resend'] === 'true') {
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['error'] = "Session expired. Please login again.";
        header("Location: $loginPage");
        exit();
    }

    $userId = $_SESSION['temp_user_id'];

    // Get user details for OTP sending
    $userSql = "SELECT email, phone_number, role FROM users WHERE user_id = ?";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "User not found.";
        header("Location: $loginPage");
        exit();
    }

    $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date("Y-m-d H:i:s", time() + OTP_EXPIRY_SECONDS);

    // Delete existing
    $delSql = "DELETE FROM otp WHERE user_id = ?";
    $delStmt = $pdo->prepare($delSql);
    $delStmt->execute([$userId]);

    // Insert new
    $insSql = "INSERT INTO otp (user_id, otp_code, otp_expiration) VALUES (?, ?, ?)";
    $insStmt = $pdo->prepare($insSql);
    $insStmt->execute([$userId, $otpCode, $expiry]);

    // Send OTP based on method
    $otpMethod = $_SESSION['otp_method'] ?? 'email';

    if ($otpMethod === 'sms') {
        require_once '../helpers/sms_helper.php';
        $phoneNumber = decryptionData($user['phone_number']);
        $smsContent = "Your LibroTech verification code is: $otpCode. It will expire in 5 minutes.";
        
        $smsResult = sendUniSMS($phoneNumber, $smsContent);
        
        if ($smsResult['success']) {
            $_SESSION['success'] = "A new verification code has been sent to your phone.";
            header("Location: ../public/otp_verification.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to resend SMS: " . $smsResult['message'];
            header("Location: ../public/otp_verification.php");
            exit();
        }
    } else {
        // Default to email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress(decryptionData($user['email']));

            $mail->isHTML(true);
            $mail->Subject = 'Your Portal OTP (Resent)';
            $mail->Body    = "Your verification code is: <b>$otpCode</b>. It will expire in 5 minutes.";

            $mail->send();

            $_SESSION['success'] = "A new verification code has been sent to your email.";
            header("Location: ../public/otp_verification.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to send OTP email: " . $mail->ErrorInfo;
            header("Location: ../public/otp_verification.php");
            exit();
        }
    }
}
