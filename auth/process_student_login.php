<?php

session_start();

require_once '../config/smtp_config.php';
require_once '../vendor/autoload.php';
include '../database/db_connection.php';
require_once '../helpers/cryptography_process.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $inputUser = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $inputPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    $otpMethod = filter_input(INPUT_POST, 'otpMethod', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($inputUser) || empty($inputPassword) || empty($otpMethod)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../public/studentLogin.php");
        exit();
    }

    // Encrypt username to match database storage
    $encryptedUser = encryptionData($inputUser);

    $sql = "SELECT * FROM users WHERE username = ? AND role = 'Student'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$encryptedUser]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($inputPassword, $user['password'])) {

        // 1. Check Approval Status
        if ($user['approval_status'] === 'Pending') {
            $_SESSION['error'] = "your account is pending for approval please wait for administration to approve your account.";
            header("Location: ../public/studentLogin.php");
            exit();
        }

        if ($user['approval_status'] === 'Rejected') {
            $_SESSION['error'] = "Your account has been rejected by the administrator.";
            header("Location: ../public/studentLogin.php");
            exit();
        }

        if ($user['approval_status'] === 'Inactive') {
            $_SESSION['error'] = "Your account is currently inactive. Please contact the administrator.";
            header("Location: ../public/studentLogin.php");
            exit();
        }

        // 2. Generate OTP
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = date("Y-m-d H:i:s", time() + OTP_EXPIRY_SECONDS);

        // 3. Store OTP in database
        // Delete any existing OTP for this user first
        $delSql = "DELETE FROM otp WHERE user_id = ?";
        $delStmt = $pdo->prepare($delSql);
        $delStmt->execute([$user['user_id']]);

        // Insert new OTP
        $insSql = "INSERT INTO otp (user_id, otp_code, otp_expiration) VALUES (?, ?, ?)";
        $insStmt = $pdo->prepare($insSql);
        $insStmt->execute([$user['user_id'], $otpCode, $expiry]);

        $_SESSION['temp_user_id'] = $user['user_id'];
        $_SESSION['temp_username'] = $inputUser;
        $_SESSION['temp_role'] = 'Student';
        $_SESSION['otp_method'] = $otpMethod;

        // 4. Handle OTP Method
        if ($otpMethod === 'email') {
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
                $mail->Subject = 'Your Student Portal OTP';
                $mail->Body    = "Your verification code is: <b>$otpCode</b>. It will expire in 5 minutes.";

                $mail->send();

                header("Location: ../public/otp_verification.php");
                exit();
            } catch (Exception $e) {
                $_SESSION['error'] = "Failed to send OTP email: " . $mail->ErrorInfo;
                header("Location: ../public/studentLogin.php");
                exit();
            }
        } else if ($otpMethod === "sms") {
            // SMS implementation to be added later
            header("Location: ../public/otp_verification.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: ../public/studentLogin.php");
        exit();
    }
}
