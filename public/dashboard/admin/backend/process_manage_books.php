<?php
session_start();
require_once __DIR__ . '/../../../../database/db_connection.php';

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

// Handle Delete Book
if (isset($_GET['delete_id'])) {
    $book_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $_SESSION['success_message'] = "Book deleted successfully!";
        header("Location: manage_books.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting book: " . $e->getMessage();
    }
}

// Handle Add Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $available_copies = (int)$_POST['available_copies'];
    $status = $_POST['status'];
    $added_by = $_SESSION['user_id'] ?? 0; // Use session user_id

    // File upload handling
    $book_image = null;
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['book_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid('book_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/../../../../uploads/books/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_dir . $new_name)) {
                $book_image = 'uploads/books/' . $new_name;
            }
        }
    }

    try {
        $status = ($available_copies <= 0) ? 'Not Available' : $_POST['status'];
        $stmt = $pdo->prepare("INSERT INTO books (title, author, category, book_image, available_copies, status, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $category, $book_image, $available_copies, $status, $added_by]);
        $_SESSION['success_message'] = "Book added successfully!";
        header("Location: manage_books.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding book: " . $e->getMessage();
    }
}

// Handle Edit Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $available_copies = (int)$_POST['available_copies'];
    $status = $_POST['status'];

    // File upload handling
    $image_update = "";
    $params = [$title, $author, $category, $available_copies, $status];
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['book_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_name = uniqid('book_', true) . '.' . $ext;
            $upload_dir = __DIR__ . '/../../../../uploads/books/';
            if (move_uploaded_file($_FILES['book_image']['tmp_name'], $upload_dir . $new_name)) {
                $image_update = ", book_image = ?";
                $params[] = 'uploads/books/' . $new_name;

                // Delete old image
                try {
                    $old_stmt = $pdo->prepare("SELECT book_image FROM books WHERE book_id = ?");
                    $old_stmt->execute([$book_id]);
                    $old_image = $old_stmt->fetchColumn();
                    if ($old_image && file_exists(__DIR__ . '/../../../../' . $old_image)) {
                        unlink(__DIR__ . '/../../../../' . $old_image);
                    }
                } catch (Exception $e) {}
            }
        }
    }
    $params[] = $book_id;

    try {
        $status = ($available_copies <= 0) ? 'Not Available' : $_POST['status'];
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ? $image_update WHERE book_id = ?");
        $stmt->execute($params);
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: manage_books.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
