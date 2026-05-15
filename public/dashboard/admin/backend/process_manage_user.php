<?php
session_start();

require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';
require_once __DIR__ . '/../../../../config/smtp_config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../../public/loginAs.php");
//     exit();
// }

$success_msg = "";
$error_msg = "";
$search = $_GET['search'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];

        if ($action === 'delete') {
            try {
                $delete_stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $delete_stmt->execute([$user_id]);
                $success_msg = "User account deleted successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete user: " . $e->getMessage();
            }
        } else {
            // Fetch user data for email
            $user_query = "SELECT * FROM users WHERE user_id = ?";
            $user_stmt = $pdo->prepare($user_query);
            $user_stmt->execute([$user_id]);
            $target_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            if ($target_user) {
                $user_email = decryptionData($target_user['email']);
                $user_name = decryptionData($target_user['username']);
                $first_name = decryptionData($target_user['first_name']);
                $last_name = decryptionData($target_user['last_name']);

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
                    $mail->addAddress($user_email, $first_name . " " . $last_name);

                    $mail->isHTML(true);

                    if ($action === 'approve') {
                        $update_query = "UPDATE users SET approval_status = 'Approved' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Body    = "Hello $first_name,<br><br>Your account (Username: <b>$user_name</b>) was approved by the administrator.<br>You can now login to the system.<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User approved and notified via email.";
                    } elseif ($action === 'reject') {
                        $reason = $_POST['reason'] ?? 'No reason provided.';
                        $update_query = "UPDATE users SET approval_status = 'Rejected' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Registration Update - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account registration was rejected by the administration.<br><b>Reason:</b> $reason<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User rejected and notified via email.";
                    } elseif ($action === 'deactivate') {
                        $reason = $_POST['reason'] ?? 'No reason provided.';
                        $update_query = "UPDATE users SET approval_status = 'Inactive' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Deactivated - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account (Username: <b>$user_name</b>) was deactivated by the administration.<br><b>Reason:</b> $reason<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User deactivated and notified via email.";
                    } elseif ($action === 'activate') {
                        $update_query = "UPDATE users SET approval_status = 'Approved' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Reactivated - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account (Username: <b>$user_name</b>) has been reactivated by the administration.<br>You can now login to the system again.<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User reactivated and notified via email.";
                    }
                    $mail->send();
                } catch (Exception $e) {
                    $error_msg = "Action completed, but email failed: " . $mail->ErrorInfo;
                }
            } else {
                $error_msg = "User not found.";
            }
        }
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $raw_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $raw_users = [];
}

$all_users = [];
$pending_count = 0;

foreach ($raw_users as $user) {
    if ($user['approval_status'] === 'Pending') $pending_count++;

    // Decrypt data for searching
    $fname = decryptionData($user['first_name']);
    $lname = decryptionData($user['last_name']);
    $email = decryptionData($user['email']);
    $username = decryptionData($user['username']);

    $fullName = $fname . " " . $lname;

    if (!empty($search)) {
        $searchLower = strtolower($search);
        $match = str_contains(strtolower($fullName), $searchLower) ||
            str_contains(strtolower($email), $searchLower) ||
            str_contains(strtolower($username), $searchLower);

        if ($match) {
            $all_users[] = $user;
        }
    } else {
        $all_users[] = $user;
    }
}
