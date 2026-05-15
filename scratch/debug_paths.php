<?php
require_once 'database/db_connection.php';
try {
    $stmt = $pdo->query("SELECT book_id, title, book_image FROM books LIMIT 10");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($books);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
