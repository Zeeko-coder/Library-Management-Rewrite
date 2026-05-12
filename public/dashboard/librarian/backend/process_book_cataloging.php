<?php
session_start();
require_once __DIR__ . '/../../../../database/db_connection.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../../loginAs.php");
    exit();
}

// Get unread notification count for sidebar
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
} catch (PDOException $e) {
    $unread_count = 0;
}

// Fetch books from database
$search = $_GET['search'] ?? '';
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY book_id ASC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        $all_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $books_stmt = $pdo->query("SELECT * FROM books ORDER BY book_id ASC");
        $all_books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $all_books = [];
}



// Handle Edit Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $available_copies = (int)$_POST['available_copies'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ? WHERE book_id = ?");
        $stmt->execute([$title, $author, $category, $available_copies, $status, $book_id]);
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
?>
