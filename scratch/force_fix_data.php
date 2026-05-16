<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

try {
    $placeholder = encryptionData('639000000000');
    $sql = "UPDATE users SET phone_number = ? WHERE user_id IN (2,3,4,5,6,7,8,9)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$placeholder]);
    echo "Successfully updated " . $stmt->rowCount() . " corrupted phone numbers to placeholder.\n";
    
    // Also check if any others are failing
    $check = $pdo->query("SELECT user_id, phone_number FROM users");
    $users = $check->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        if (decryptionData($u['phone_number']) === false) {
            $stmt = $pdo->prepare("UPDATE users SET phone_number = ? WHERE user_id = ?");
            $stmt->execute([$placeholder, $u['user_id']]);
            echo "Fixed ID " . $u['user_id'] . " (additional)\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
