<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

$stmt = $pdo->query("
    SELECT br.*, u.username, u.first_name, u.last_name
    FROM borrowings br
    JOIN users u ON br.user_id = u.user_id
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: " . $row['id'] . " | User: " . decryptionData($row['username']) . " (" . decryptionData($row['first_name']) . " " . decryptionData($row['last_name']) . ") | Status: " . $row['status'] . " | Due: " . $row['due_date'] . " | Return: " . ($row['return_date'] ?: 'NULL') . "\n";
}
?>
