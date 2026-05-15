<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include __DIR__ . '/../../../../database/db_connection.php';
include __DIR__ . '/../../../../helpers/cryptography_process.php';

$user_id = $_SESSION['user_id'];
$update_success = false;
$update_error = "";

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([
            encryptionData($first_name),
            encryptionData($last_name),
            encryptionData($username),
            encryptionData($email),
            $user_id
        ]);
        $update_success = true;
    } catch (PDOException $e) {
        $update_error = "Error updating profile: " . $e->getMessage();
    }
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view, first_name, last_name, username, email FROM users WHERE user_id = ?");
    $user_stmt->execute([$user_id]);
    $student_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

    $last_view = $student_data['last_notif_view'] ?: '1970-01-01 00:00:00';

    $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings 
        WHERE user_id = ? 
        AND (
            (status IN ('borrowed', 'Borrowed') AND borrow_date > ?) OR 
            (status IN ('overdue', 'Overdue') AND due_date > ?) OR
            (status = 'rejected' AND created_at > ?)
        )");
    $notif_stmt->execute([$user_id, $last_view, $last_view, $last_view]);
    $unread_count = $notif_stmt->fetchColumn();

    // Add manual notifications from the notifications table
    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$user_id]);
    $unread_count += $manual_stmt->fetchColumn();

    $full_name = decryptionData($student_data['first_name']) . " " . decryptionData($student_data['last_name']);
    $initials = strtoupper(substr(decryptionData($student_data['first_name']), 0, 1) . substr(decryptionData($student_data['last_name']), 0, 1));
    $decrypted_username = decryptionData($student_data['username']);
    $decrypted_email = decryptionData($student_data['email']);
} catch (PDOException $e) {
    $unread_count = 0;
    $full_name = "Student User";
    $initials = "ST";
}
?>
