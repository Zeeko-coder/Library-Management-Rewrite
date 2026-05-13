<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include __DIR__ . '/../../../../database/db_connection.php';
include __DIR__ . '/../../../../helpers/cryptography_process.php';

$student_id = $_SESSION['user_id'];

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

// Fetch categories for filter
try {
    $cat_stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Handle Borrow Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book'])) {
    $book_id = $_POST['book_id'];
    $quantity = (int)$_POST['quantity'];

    try {
        $pdo->beginTransaction();

        // Check availability
        $check_stmt = $pdo->prepare("SELECT title, available_copies FROM books WHERE book_id = ? FOR UPDATE");
        $check_stmt->execute([$book_id]);
        $book = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            throw new Exception("Book not found.");
        }

        if ($book['available_copies'] < $quantity) {
            throw new Exception("Not enough copies available.");
        }

        // Check if user already has this book title borrowed (active loan)
        $dup_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM borrowings br 
            JOIN books b ON br.book_id = b.book_id 
            WHERE br.user_id = ? 
            AND b.title = ? 
            AND br.status NOT IN ('returned', 'rejected', 'Returned', 'Rejected')
        ");
        $dup_stmt->execute([$student_id, $book['title']]);
        if ($dup_stmt->fetchColumn() > 0) {
            throw new Exception("You have already borrowed this book title with borrow copies");
        }

        // Insert pending borrowing request
        $insert_stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, quantity, borrow_date, due_date, status) VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'pending')");
        $insert_stmt->execute([$student_id, $book_id, $quantity]);

        // Reserve the copies
        $update_stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - ? WHERE book_id = ?");
        $update_stmt->execute([$quantity, $book_id]);

        $pdo->commit();
        $_SESSION['success_message'] = "Borrowing request for '" . $book['title'] . "' has been sent to the Librarian for approval.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header("Location: search_catalog.php");
    exit();
}

// Search and Filter Logic
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$availability_filter = $_GET['availability'] ?? '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if ($availability_filter === 'Available Only') {
    $sql .= " AND available_copies > 0";
}

$sql .= " ORDER BY title ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
