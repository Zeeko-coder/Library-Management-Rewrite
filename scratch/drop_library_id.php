<?php
include 'database/db_connection.php';
try {
    $pdo->exec("ALTER TABLE users DROP COLUMN library_id");
    echo "Column 'library_id' dropped successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
