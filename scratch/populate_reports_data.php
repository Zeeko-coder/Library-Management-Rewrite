<?php
require_once __DIR__ . '/../database/db_connection.php';
require_once __DIR__ . '/../helpers/cryptography_process.php';

/**
 * SMART SEEDER for Library Management Reports
 * This script populates the 'borrowings' table with a 7-day spread of data.
 */

try {
    $pdo->beginTransaction();

    echo "--- Starting Smart Seeding Process ---\n";

    // 1. Get Students and Books
    $students = $pdo->query("SELECT user_id FROM users WHERE role = 'Student'")->fetchAll(PDO::FETCH_COLUMN);
    $books = $pdo->query("SELECT book_id, available_copies FROM books")->fetchAll(PDO::FETCH_ASSOC);

    if (empty($students) || empty($books)) {
        throw new Exception("Insufficient data (students or books) to perform seeding.");
    }

    // 2. Clear previous test data (Optional - let's just add new ones for safety)
    // $pdo->exec("DELETE FROM borrowings WHERE status IN ('borrowed', 'returned', 'overdue')");

    $record_count = 0;

    // 3. Loop through the last 7 days
    for ($i = 7; $i >= 0; $i--) {
        $date_str = date('Y-m-d', strtotime("-$i days"));
        $timestamp = $date_str . " " . sprintf("%02d:%02d:%02d", rand(8, 17), rand(0, 59), rand(0, 59));
        
        echo "Processing Date: $date_str...\n";

        // Generate 3-5 records per day
        $daily_records = rand(3, 5);
        for ($j = 0; $j < $daily_records; $j++) {
            $student_id = $students[array_rand($students)];
            $book_index = array_rand($books);
            $book_id = $books[$book_index]['book_id'];
            
            // Randomize status
            // Early days (7-5 days ago) might be 'returned' or 'overdue'
            // Recent days (3-0 days ago) are likely 'borrowed'
            $rand_val = rand(1, 10);
            $status = 'borrowed';
            $return_date = null;
            
            if ($i >= 5 && $rand_val > 7) {
                $status = 'returned';
                $return_date = date('Y-m-d H:i:s', strtotime($timestamp . " + " . rand(1, 4) . " days"));
            } elseif ($i >= 6 && $rand_val < 3) {
                $status = 'overdue';
            }

            $quantity = rand(1, 2);
            $borrow_date = $timestamp;
            $due_date = date('Y-m-d H:i:s', strtotime($timestamp . " + 7 days"));

            // Insert Record
            $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, quantity, borrow_date, due_date, return_date, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_id, $book_id, $quantity, $borrow_date, $due_date, $return_date, $status, $timestamp]);

            // Update Book Stock if not returned
            if ($status !== 'returned') {
                $pdo->prepare("UPDATE books SET available_copies = available_copies - ?, status = CASE WHEN (available_copies - ?) <= 0 THEN 'Not Available' ELSE 'Available' END WHERE book_id = ?")
                    ->execute([$quantity, $quantity, $book_id]);
            }

            $record_count++;
        }
    }

    $pdo->commit();
    echo "\n--- SUCCESS ---\n";
    echo "Generated $record_count records across the last 7 days.\n";
    echo "You can now check the Reports & Analysis page.\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
