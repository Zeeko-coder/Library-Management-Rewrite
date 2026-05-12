<?php
include 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

$sql = "SELECT username, phone_number FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<pre>";
foreach ($users as $user) {
    echo "Username: " . decryptionData($user['username']) . "\n";
    echo "Raw Phone: " . $user['phone_number'] . "\n";
    echo "Decrypted Phone: " . decryptionData($user['phone_number']) . "\n";
    echo "-------------------\n";
}
echo "</pre>";
