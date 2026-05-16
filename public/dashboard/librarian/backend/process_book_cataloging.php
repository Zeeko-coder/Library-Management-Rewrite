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

    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$_SESSION['user_id']]);
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

// Search and Filter Logic
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$availability_filter = $_GET['availability'] ?? '';

try {
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $sql .= " AND (title LIKE ? OR author LIKE ? OR category LIKE ?)";
        $params[] = "%$search%";
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

    $sql .= " ORDER BY book_id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $all_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    $status = ($available_copies <= 0) ? 'Not Available' : $_POST['status'];
    $description = $_POST['description'] ?? '';
    $year_published = (int)($_POST['year_published'] ?? date('Y'));
    
    // Image handling
    $book_image = null;
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === 0) {
        $upload_dir = __DIR__ . '/../../../../uploads/books/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_extensions)) {
            $new_filename = 'book_' . $book_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_path)) {
                $book_image = 'uploads/books/' . $new_filename;
                
                // Delete old image if it exists
                try {
                    $old_stmt = $pdo->prepare("SELECT book_image FROM books WHERE book_id = ?");
                    $old_stmt->execute([$book_id]);
                    $old_image = $old_stmt->fetchColumn();
                    if ($old_image && file_exists(__DIR__ . '/../../../../' . $old_image)) {
                        unlink(__DIR__ . '/../../../../' . $old_image);
                    }
                } catch (PDOException $e) {}
            }
        }
    }

    try {
        if ($book_image) {
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ?, book_image = ?, description = ?, year_published = ? WHERE book_id = ?");
            $stmt->execute([$title, $author, $category, $available_copies, $status, $book_image, $description, $year_published, $book_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ?, description = ?, year_published = ? WHERE book_id = ?");
            $stmt->execute([$title, $author, $category, $available_copies, $status, $description, $year_published, $book_id]);
        }
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
