<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Use __DIR__ to ensure paths are resolved relative to this file
require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../../public/loginAs.php");
//     exit();
// }

// Stats counts
try {
    $total_books = $pdo->query("SELECT COUNT(book_id) FROM books")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_books = 0; // Table might not exist yet
}

try {
    $registered_users = $pdo->query("SELECT COUNT(*) FROM users WHERE approval_status = 'Approved'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $registered_users = 0;
}

try {
    $transactions = $pdo->query("SELECT COUNT(*) FROM borrowings")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $transactions = 0;
}


try {
    $overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'Overdue'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $overdue_books = 0;
}


// Pending Approvals
$pending_stmt = $pdo->query("SELECT * FROM users WHERE approval_status = 'Pending' ORDER BY created_at DESC LIMIT 5");
$pending_users = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// System-wide Activity (Combined Users and Books)
try {
    $activity_query = "
        (SELECT 
            created_at, 
            'user_registration' as type, 
            first_name, 
            last_name, 
            NULL as title, 
            NULL as category,
            NULL as admin_name,
            NULL as admin_role
        FROM users)
        UNION ALL
        (SELECT 
            b.created_at, 
            'book_addition' as type, 
            u.first_name, 
            u.last_name, 
            b.title, 
            b.category,
            u.username as admin_name,
            u.role as admin_role
        FROM books b
        LEFT JOIN users u ON b.added_by = u.user_id)
        ORDER BY created_at DESC 
        LIMIT 10
    ";
    $activity_stmt = $pdo->query($activity_query);
    $recent_activities = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_activities = [];
}

function getTimeAgo($timestamp)
{
    $time = strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 0) return "Just now"; // Handle future timestamps
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return round($diff / 60) . " mins ago";
    if ($diff < 86400) return round($diff / 3600) . " hours ago";
    if ($diff < 2592000) return round($diff / 86400) . " days ago";
    return date("M d, Y", $time);
}
