<?php
session_start();
date_default_timezone_set('Asia/Manila');

// Use __DIR__ to ensure paths are resolved relative to this file
require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';

// Stats counts for Circulation
try {
    $pending_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'pending'")->fetchColumn() ?: 0;
    $active_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND return_date IS NULL AND due_date >= NOW()")->fetchColumn() ?: 0;
    $overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE (status = 'overdue' OR (status = 'borrowed' AND due_date < NOW())) AND return_date IS NULL")->fetchColumn() ?: 0;
    $returned_total = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'returned'")->fetchColumn() ?: 0;
    $rejected_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'rejected'")->fetchColumn() ?: 0;

    $stmt = $pdo->query("
        SELECT b.*, bk.title, u.first_name, u.last_name 
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        ORDER BY b.created_at DESC 
        LIMIT 50
    ");
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch users for Issue Book dropdown (mostly students)
    $user_stmt = $pdo->query("SELECT user_id, first_name, last_name, role FROM users WHERE approval_status = 'Approved' ORDER BY last_name ASC");
    $users_list = $user_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch available books for Issue Book dropdown
    $books_stmt = $pdo->query("SELECT book_id, title, available_copies FROM books WHERE available_copies > 0");
    $dropdown_books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch active borrowings for Return Book dropdown
    $active_stmt = $pdo->query("
        SELECT b.id, b.book_id, bk.title, u.first_name, u.last_name 
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'borrowed'
    ");
    $active_borrows_list = $active_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $active_borrows = $due_today = $overdue_books = $returns_today = 0;
    $recent_transactions = $dropdown_users = $dropdown_books = $active_borrows_list = [];
}

// Handle Issue Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_book'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $due_date = $_POST['due_date'];
    $borrow_date = date('Y-m-d');

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
        $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
        $stmt = $pdo->prepare("UPDATE books SET 
            available_copies = available_copies - 1,
            status = CASE WHEN (available_copies - 1) <= 0 THEN 'Not Available' ELSE 'Available' END
            WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $pdo->commit();
        $_SESSION['success_message'] = "Book issued successfully!";
        header("Location: circulation.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error issuing book: " . $e->getMessage();
    }
}

// Handle Return Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $borrowing_id = $_POST['borrowing_id'];
    $return_date = date('Y-m-d');

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT book_id FROM borrowings WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        $book_id = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE borrowings SET return_date = ?, status = 'returned' WHERE id = ?");
        $stmt->execute([$return_date, $borrowing_id]);
        $stmt = $pdo->prepare("UPDATE books SET 
            available_copies = available_copies + 1,
            status = CASE WHEN (available_copies + 1) <= 0 THEN 'Not Available' ELSE 'Available' END
            WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $pdo->commit();
        $_SESSION['success_message'] = "Book returned successfully!";
        header("Location: circulation.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error returning book: " . $e->getMessage();
    }
}
