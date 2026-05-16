<?php
require_once 'database/db_connection.php';
require_once 'helpers/cryptography_process.php';

try {
    $stmt = $pdo->query("SELECT user_id, first_name, last_name, phone_number FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        $first = decryptionData($user['first_name']);
        $last = decryptionData($user['last_name']);
        
        if (stripos($first . ' ' . $last, 'Gabriel Jillana') !== false) {
            $phone = decryptionData($user['phone_number']);
            echo "User found:\n";
            echo "Name: $first $last\n";
            echo "Phone Number: $phone\n";
            exit();
        }
    }
    echo "User 'Gabriel Jillana' not found.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
