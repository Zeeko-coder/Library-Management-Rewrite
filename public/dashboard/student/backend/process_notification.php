<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include __DIR__ . '/../../../../database/db_connection.php';
include __DIR__ . '/../../../../helpers/cryptography_process.php';

$user_id = $_SESSION['user_id'];

// Get unread notification count for sidebar (calculate before resetting)
$unread_count = 0;
$unread_reminders = 0;
$unread_news = 0;
$alerts = [];

try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$user_id]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    // Fetch all borrowings for this student to generate notifications
    $stmt = $pdo->prepare("
        SELECT br.*, b.title 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? 
        ORDER BY br.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($borrowings as $item) {
        $notif = null;
        $status = strtolower($item['status']);
        $is_unread = false;

        if ($status === 'pending') {
            $notif = [
                'title' => "Request Sent: " . $item['title'],
                'desc' => "Your request to borrow this book is pending librarian approval.",
                'time' => $item['created_at'],
                'icon' => 'fa-paper-plane',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['created_at'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'borrowed') {
            $notif = [
                'title' => "Request Approved: " . $item['title'],
                'desc' => "Your borrow request has been approved! Due date: " . date('M d, Y', strtotime($item['due_date'])),
                'time' => $item['borrow_date'] ?: $item['created_at'],
                'icon' => 'fa-check-circle',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['borrow_date'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'overdue') {
            $notif = [
                'title' => "Overdue Notice: " . $item['title'],
                'desc' => "This book is past its due date. Please return it immediately.",
                'time' => $item['due_date'],
                'icon' => 'fa-exclamation-triangle',
                'class' => 'icon-reminder',
                'type' => 'reminder'
            ];
            if ($item['due_date'] > $last_view) {
                $is_unread = true;
                $unread_reminders++;
            }
        } elseif ($status === 'returned') {
            $notif = [
                'title' => "Book Returned: " . $item['title'],
                'desc' => "Thank you! You have successfully returned this book.",
                'time' => $item['return_date'],
                'icon' => 'fa-history',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['return_date'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'rejected') {
            $notif = [
                'title' => "Request Rejected: " . $item['title'],
                'desc' => "Unfortunately, your borrow request was rejected by the librarian.",
                'time' => $item['created_at'],
                'icon' => 'fa-times-circle',
                'class' => 'icon-reminder',
                'type' => 'reminder'
            ];
            if ($item['created_at'] > $last_view) {
                $is_unread = true;
                $unread_reminders++;
            }
        }

        if ($notif) {
            $notif['unread'] = $is_unread;
            $alerts[] = $notif;
        }
    }

    // --- Fetch Manual Reminders from notifications table ---
    $manual_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $manual_stmt->execute([$user_id]);
    $manual_notifs = $manual_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($manual_notifs as $m_notif) {
        $is_unread = ($m_notif['is_read'] == 0);
        $type = $m_notif['type'] ?: 'reminder';
        
        $alerts[] = [
            'id' => $m_notif['id'],
            'title' => $m_notif['title'],
            'desc' => $m_notif['message'],
            'time' => $m_notif['created_at'],
            'icon' => ($type === 'reminder' ? 'fa-bell' : 'fa-info-circle'),
            'class' => ($type === 'reminder' ? 'icon-reminder' : 'icon-news'),
            'type' => $type,
            'unread' => $is_unread
        ];

        if ($is_unread) {
            if ($type === 'reminder') $unread_reminders++;
            else $unread_news++;
        }
    }

    // --- Fetch New Books added since last view ---
    $new_books_stmt = $pdo->prepare("SELECT title, author, created_at FROM books WHERE created_at > ? ORDER BY created_at DESC");
    $new_books_stmt->execute([$last_view]);
    $new_books = $new_books_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($new_books as $nb) {
        $alerts[] = [
            'title' => "New Book Added!",
            'desc' => "The library has added '" . htmlspecialchars($nb['title']) . "' by " . htmlspecialchars($nb['author']) . " to the collection.",
            'time' => $nb['created_at'],
            'icon' => 'fa-book-medical',
            'class' => 'icon-news',
            'type' => 'news',
            'unread' => true
        ];
        $unread_news++;
    }

    $unread_count = $unread_reminders + $unread_news;

    // Sort alerts by time
    usort($alerts, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Update last_notif_view when visiting this page
    $pdo->prepare("UPDATE users SET last_notif_view = NOW() WHERE user_id = ?")->execute([$user_id]);
    
    // Also mark manual notifications as read
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
} catch (PDOException $e) {
    $unread_count = $unread_reminders = $unread_news = 0;
    $alerts = [];
}
?>
