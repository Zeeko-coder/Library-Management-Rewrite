<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';

$student_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Handle Return Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_book') {
    $borrow_id = $_POST['borrow_id'];
    try {
        // Fetch book and student info for notification
        $info_stmt = $pdo->prepare("
            SELECT b.title, u.first_name, u.last_name 
            FROM borrowings br 
            JOIN books b ON br.book_id = b.book_id 
            JOIN users u ON br.user_id = u.user_id 
            WHERE br.id = ?
        ");
        $info_stmt->execute([$borrow_id]);
        $info = $info_stmt->fetch();
        
        $student_name = decryptionData($info['first_name']) . " " . decryptionData($info['last_name']);
        $book_title = $info['title'];

        // Return copies to stock
        $update_stock = $pdo->prepare("UPDATE books b JOIN borrowings br ON b.book_id = br.book_id SET b.available_copies = b.available_copies + br.quantity WHERE br.id = ?");
        $update_stock->execute([$borrow_id]);

        $stmt = $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$borrow_id, $student_id]);
        
        // Notify Librarians
        $librarians = $pdo->query("SELECT user_id FROM users WHERE role = 'Librarian'")->fetchAll(PDO::FETCH_COLUMN);
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'news', ?, ?)");
        $notif_title = "Book Returned: $book_title";
        $notif_msg = "Student $student_name has returned the book '$book_title'.";
        
        foreach ($librarians as $lib_id) {
            $notif_stmt->execute([$lib_id, $notif_title, $notif_msg]);
        }

        $success_message = "Book returned successfully!";
    } catch (PDOException $e) {
        $error_message = "Error returning book: " . $e->getMessage();
    }
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$student_id]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings 
        WHERE user_id = ? 
        AND (
            (status IN ('borrowed', 'Borrowed') AND borrow_date > ?) OR 
            (status IN ('overdue', 'Overdue') AND due_date > ?) OR
            (status = 'rejected' AND created_at > ?)
        )");
    $notif_stmt->execute([$student_id, $last_view, $last_view, $last_view]);
    $unread_count = $notif_stmt->fetchColumn();

    // Add manual notifications from the notifications table
    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$student_id]);
    $unread_count += $manual_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

try {
    // 1. Stats Calculation
    // Currently Borrowed (Active, not overdue)
    $currently_borrowed = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND LOWER(status) = 'borrowed' AND due_date >= NOW() AND (return_date IS NULL OR return_date = '')");
    $currently_borrowed->execute([$student_id]);
    $borrowed_count = $currently_borrowed->fetchColumn() ?: 0;

    // Overdue Items (Status is 'overdue' OR due_date has passed)
    $overdue_items = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND (LOWER(status) = 'overdue' OR (LOWER(status) = 'borrowed' AND due_date < NOW())) AND (return_date IS NULL OR return_date = '')");
    $overdue_items->execute([$student_id]);
    $overdue_count = $overdue_items->fetchColumn() ?: 0;

    // Successfully Returned
    $returned_items = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND return_date IS NOT NULL AND return_date != ''");
    $returned_items->execute([$student_id]);
    $returned_count = $returned_items->fetchColumn() ?: 0;

    // 2. Fetch Active Borrows
    $active_borrows_stmt = $pdo->prepare("
        SELECT br.*, b.title, b.author 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? AND (br.return_date IS NULL OR br.return_date = '') AND br.status NOT IN ('pending', 'rejected')
        ORDER BY br.due_date ASC
    ");
    $active_borrows_stmt->execute([$student_id]);
    $active_borrows = $active_borrows_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Recently Returned
    $returned_loans_stmt = $pdo->prepare("
        SELECT br.*, b.title 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? AND br.return_date IS NOT NULL AND br.return_date != ''
        ORDER BY br.return_date DESC 
        LIMIT 10
    ");
    $returned_loans_stmt->execute([$student_id]);
    $returned_loans = $returned_loans_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $borrowed_count = $overdue_count = $returned_count = 0;
    $active_borrows = $returned_loans = [];
}
?>
