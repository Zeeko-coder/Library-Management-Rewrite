<?php
require_once 'database/db_connection.php';
try {
    // Standardize to root-relative path: uploads/books/filename.ext
    $stmt = $pdo->query("SELECT book_id, book_image FROM books WHERE book_image IS NOT NULL");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($books as $book) {
        $img = $book['book_image'];
        // Remove public/ prefix if it exists
        if (strpos($img, 'public/') === 0) {
            $new_path = substr($img, 7);
        } elseif (strpos($img, 'uploads/') === false) {
            // It's just a filename
            $new_path = 'uploads/books/' . $img;
        } else {
            $new_path = $img;
        }
        
        $update = $pdo->prepare("UPDATE books SET book_image = ? WHERE book_id = ?");
        $update->execute([$new_path, $book['book_id']]);
        echo "Updated book ID {$book['book_id']} to $new_path\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
