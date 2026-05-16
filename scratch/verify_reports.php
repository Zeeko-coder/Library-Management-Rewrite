<?php
require_once __DIR__ . '/../database/db_connection.php';

try {
    echo "--- Report Data Verification ---\n";
    
    // Check Trend Data
    $trend_stmt = $pdo->query("
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM borrowings 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $trend_data = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Trend Data (Last 7 Days):\n";
    foreach ($trend_data as $t) {
        echo " - {$t['date']}: {$t['count']} borrows\n";
    }

    // Check Status Distribution
    $status_stmt = $pdo->query("SELECT status, COUNT(*) as count FROM borrowings GROUP BY status");
    $status_data = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nStatus Distribution:\n";
    foreach ($status_data as $s) {
        echo " - {$s['status']}: {$s['count']}\n";
    }

    // Check Category Distribution
    $cat_stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY category
    ");
    $cat_data = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCategory Distribution:\n";
    foreach ($cat_data as $c) {
        echo " - {$c['category']}: {$c['count']}\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
