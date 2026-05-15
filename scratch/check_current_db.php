<?php
include 'database/db_connection.php';

echo "Tables:\n";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
print_r($tables);

foreach ($tables as $table) {
    echo "\n--- Structure of $table ---\n";
    $stmt = $pdo->query("DESCRIBE `$table` ");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
?>
