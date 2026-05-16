<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

try {
    $validPlaceholder = encryptionData('639171234567');
    $check = $pdo->query("SELECT user_id, phone_number FROM users");
    $users = $check->fetchAll(PDO::FETCH_ASSOC);
    $count = 0;
    foreach ($users as $u) {
        if (decryptionData($u['phone_number']) === false || strlen(decryptionData($u['phone_number'])) < 10) {
            $stmt = $pdo->prepare("UPDATE users SET phone_number = ? WHERE user_id = ?");
            $stmt->execute([$validPlaceholder, $u['user_id']]);
            $count++;
        }
    }
    echo "Updated $count users to valid placeholder format (639171234567).\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
