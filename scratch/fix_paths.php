<?php
require_once 'database/db_connection.php';
try {
    $stmt = $pdo->query("SELECT book_id, book_image FROM books WHERE book_image IS NOT NULL AND book_image NOT LIKE 'public/%'");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($books as $book) {
        $new_path = 'public/uploads/books/' . $book['book_image'];
        $update = $pdo->prepare("UPDATE books SET book_image = ? WHERE book_id = ?");
        $update->execute([$new_path, $book['book_id']]);
        echo "Updated book ID {$book['book_id']} to $new_path\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
