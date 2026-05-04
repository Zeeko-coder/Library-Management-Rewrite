<?php
session_start();
include '../database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/loginAs.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the role from the database to ensure it's current
$sql = "SELECT role FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $role = $user['role'];
    $_SESSION['role'] = $role; // Store role in session for access control

    // Redirect based on role
    if ($role === 'Admin') {
        header("Location: ../public/dashboard/admin/dashboard_admin.php");
    } else if ($role === 'Librarian') {
        header("Location: ../public/dashboard/librarian/dashboard_librarian.php");
    } else if ($role === 'Student') {
        header("Location: ../public/dashboard/student/dashboard_student.php");
    } else {
        // Unknown role fallback
        $_SESSION['error'] = "Unknown user role encountered.";
        header("Location: ../index.php");
    }

    exit();
} else {
    // User record not found
    session_destroy();
    header("Location: ../public/loginAs.php");
    exit();
}
