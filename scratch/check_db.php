<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/../helpers/cryptography_process.php';

try {
    echo "--- Checking Database Status ---\n";
    
    // Check Students
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, username, role FROM users WHERE role = 'Student'");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total Students found: " . count($students) . "\n";
    foreach ($students as $s) {
        echo " - [ID: {$s['user_id']}] Name: " . decryptionData($s['first_name']) . " " . decryptionData($s['last_name']) . " | Username: " . decryptionData($s['username']) . "\n";
    }

    // Check Librarians
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, username, role FROM users WHERE role = 'Librarian'");
    $librarians = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTotal Librarians found: " . count($librarians) . "\n";
    foreach ($librarians as $l) {
        echo " - [ID: {$l['user_id']}] Name: " . decryptionData($l['first_name']) . " " . decryptionData($l['last_name']) . " | Username: " . decryptionData($l['username']) . "\n";
    }

    // Check Books
    $stmt = $pdo->query("SELECT book_id, title, available_copies, category FROM books");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTotal Books found: " . count($books) . "\n";
    foreach ($books as $b) {
        echo " - [ID: {$b['book_id']}] {$b['title']} ({$b['category']}) | Stock: {$b['available_copies']}\n";
    }

    // Check existing borrowings
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM borrowings GROUP BY status");
    $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nCurrent Borrowings Summary:\n";
    foreach ($borrows as $b) {
        echo " - {$b['status']}: {$b['count']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
