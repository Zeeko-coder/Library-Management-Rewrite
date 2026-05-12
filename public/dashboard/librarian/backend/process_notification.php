<?php
session_start();
require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../../loginAs.php");
    exit();
}

// Update last notification view timestamp
try {
    $pdo->prepare("UPDATE users SET last_notif_view = NOW() WHERE user_id = ?")->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Silently fail if update fails
}

// Get unread notification count for sidebar (will be 0 after update above)
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $pending_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE status = 'pending' AND created_at > ?");
    $pending_stmt->execute([$last_view]);
    $unread_count += $pending_stmt->fetchColumn();

    $overdue_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE (status = 'borrowed' OR status = 'overdue') AND due_date < NOW() AND due_date > ?");
    $overdue_stmt->execute([$last_view]);
    $unread_count += $overdue_stmt->fetchColumn();

    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$_SESSION['user_id']]);
    $unread_count += $manual_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

// Fetch dynamic notifications from existing tables
$alerts = [];

try {
    // 1. Fetch Overdue Borrowings
    $overdue_stmt = $pdo->query("
        SELECT b.id, b.due_date as date, b.created_at, bk.title, u.first_name, u.last_name, 'overdue' as type
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'overdue' OR (b.status = 'borrowed' AND b.due_date < CURRENT_DATE)
        ORDER BY b.due_date ASC
    ");
    $overdue_items = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($overdue_items as $item) {
        $alerts[] = [
            'id' => $item['id'],
            'title' => "Overdue: " . $item['title'],
            'desc' => "Student <strong>" . decryptionData($item['first_name']) . " " . decryptionData($item['last_name']) . "</strong> has not returned this book. Due date was " . date('M d, Y', strtotime($item['date'])),
            'time' => $item['date'],
            'type' => 'overdue',
            'icon' => 'fa-clock',
            'class' => 'icon-alert',
            'link' => 'circulation.php'
        ];
    }

    // 2. Fetch Borrow Requests
    $request_stmt = $pdo->query("
        SELECT b.id, b.created_at as date, bk.title, u.first_name, u.last_name, 'request' as type
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'pending'
        ORDER BY b.created_at DESC
    ");
    $request_items = $request_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($request_items as $item) {
        $alerts[] = [
            'id' => $item['id'],
            'title' => "New Borrow Request",
            'desc' => "<strong>" . decryptionData($item['first_name']) . " " . decryptionData($item['last_name']) . "</strong> requested <strong>" . $item['title'] . "</strong>.",
            'time' => $item['date'],
            'type' => 'request',
            'icon' => 'fa-book-reader',
            'class' => 'icon-info',
            'link' => 'student_list.php'
        ];
    }

    // 3. Fetch Manual Notifications from notifications table
    $manual_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $manual_stmt->execute([$_SESSION['user_id']]);
    $manual_items = $manual_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($manual_items as $item) {
        $type = $item['type'] ?: 'news';
        $alerts[] = [
            'id' => $item['id'],
            'title' => $item['title'],
            'desc' => $item['message'],
            'time' => $item['created_at'],
            'type' => $type,
            'icon' => ($type === 'reminder' ? 'fa-bell' : 'fa-info-circle'),
            'class' => ($type === 'reminder' ? 'icon-alert' : 'icon-success'),
            'link' => '#'
        ];
    }

    // Mark manual notifications as read
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$_SESSION['user_id']]);

    // Sort by time (most recent first)
    usort($alerts, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

} catch (PDOException $e) {
    $alerts = [];
}

$overdue_count = count(array_filter($alerts, fn($a) => $a['type'] === 'overdue'));
?>
