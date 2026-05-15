<?php
session_start();
require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';

try {
    // Stats for reports
    $total_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings")->fetchColumn() ?: 0;
    $unique_borrowers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM borrowings")->fetchColumn() ?: 0;
    $returned_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'returned'")->fetchColumn() ?: 0;
    $overdue_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE (status = 'overdue' OR (status = 'borrowed' AND due_date < NOW())) AND return_date IS NULL")->fetchColumn() ?: 0;
    $pending_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'pending'")->fetchColumn() ?: 0;
    $rejected_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'rejected'")->fetchColumn() ?: 0;

    // Fetch Status Distribution for Pie Chart
    $status_dist = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM borrowings 
        GROUP BY status
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Fetch Borrowing Trends (Last 7 Days)
    $trend_stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM borrowings 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $trend_data = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch category distribution
    $category_stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY category
        ORDER BY count DESC
    ");
    $category_data = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Top Books
    $top_books_stmt = $pdo->query("
        SELECT bk.title, bk.author, COUNT(br.id) as borrow_count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY bk.book_id
        ORDER BY borrow_count DESC
        LIMIT 5
    ");
    $top_books = $top_books_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $total_borrows = $unique_borrowers = $returned_books = $overdue_count = 0;
    $category_data = $top_books = [];
}
?>
