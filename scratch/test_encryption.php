<?php
require_once 'helpers/cryptography_process.php';

$phone = "09123456789";
$encrypted = encryptionData($phone);
echo "Encrypted: " . $encrypted . "\n";
echo "Decrypted: " . decryptionData($encrypted) . "\n";
echo "Length: " . strlen($encrypted) . "\n";
