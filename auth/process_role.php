<?php
session_start();
include '../database/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/loginAs.php");
    exit();
}

$userId = $_SESSION['user_id'];

$role = $_SESSION['role'] ?? '';

// If role is missing from session, try to fetch it from database
if (empty($role) && isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        $role = $user['role'];
        $_SESSION['role'] = $role;
    }
}

if ($role) {

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
