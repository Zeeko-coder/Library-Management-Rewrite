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

    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, category, available_copies, status, added_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $category, $available_copies, $status, $added_by]);
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

    try {
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ? WHERE book_id = ?");
        $stmt->execute([$title, $author, $category, $available_copies, $status, $book_id]);
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: manage_books.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
?>
