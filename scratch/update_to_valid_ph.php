<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

try {
    $validPlaceholder = encryptionData('639171234567');
    $oldPlaceholder = encryptionData('639170000000');
    $sql = "UPDATE users SET phone_number = ? WHERE phone_number = ? OR phone_number = 'QfSC6YVxMPOVeK6Nhplu'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$validPlaceholder, $oldPlaceholder]);
    echo "Updated " . $stmt->rowCount() . " users to valid placeholder format.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
