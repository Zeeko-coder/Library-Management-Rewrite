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
    $status = ($available_copies <= 0) ? 'Not Available' : $_POST['status'];
    
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
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ?, book_image = ? WHERE book_id = ?");
            $stmt->execute([$title, $author, $category, $available_copies, $status, $book_image, $book_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ? WHERE book_id = ?");
            $stmt->execute([$title, $author, $category, $available_copies, $status, $book_id]);
        }
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
