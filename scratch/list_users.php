<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

$stmt = $pdo->query("SELECT * FROM users");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['user_id'] . " | Username: " . decryptionData($row['username']) . " | Role: " . $row['role'] . "\n";
}
?>
