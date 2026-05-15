<?php
require_once 'database/db_connection.php';

try {
    echo "Columns in 'notifications':\n";
    $stmt = $pdo->query("DESCRIBE notifications");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
