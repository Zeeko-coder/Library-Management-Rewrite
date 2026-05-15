<?php
require_once 'database/db_connection.php';

try {
    $pdo->beginTransaction();

    // Disable foreign key checks to allow truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    echo "Clearing 'borrowings' table...\n";
    $pdo->exec("TRUNCATE TABLE borrowings");

    echo "Clearing 'books' table...\n";
    $pdo->exec("TRUNCATE TABLE books");

    // Optional: Clear notifications as they are likely related to old borrows
    echo "Clearing 'notifications' table...\n";
    $pdo->exec("TRUNCATE TABLE notifications");

    // Enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $pdo->commit();
    echo "\nDatabase tables related to books have been successfully cleared.\n";
    echo "This includes 'books', 'borrowings', and 'notifications' to ensure system stability.\n";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
