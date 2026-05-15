<?php
require_once 'database/db_connection.php';
try {
    $pdo->exec("ALTER TABLE books ADD COLUMN book_image VARCHAR(255) DEFAULT NULL AFTER category");
    echo "Column 'book_image' added successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
