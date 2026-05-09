<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

$encryptedUser = encryptionData('gab');
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->execute([$encryptedUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
echo "User ID for 'gab': " . ($user['user_id'] ?? 'Not found') . "\n";

// Also check the borrowing records user_id
$stmt = $pdo->query("SELECT DISTINCT user_id FROM borrowings");
echo "User IDs in borrowings: " . implode(", ", $stmt->fetchAll(PDO::FETCH_COLUMN)) . "\n";
?>
