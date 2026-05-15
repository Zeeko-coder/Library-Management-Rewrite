<?php
require_once 'database/db_connection.php';

try {
    $tables = ['books', 'borrowings', 'notifications'];
    foreach ($tables as $table) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "Table '$table' count: $count\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
