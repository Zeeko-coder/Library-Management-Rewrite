<?php
require_once __DIR__ . '/../database/db_connection.php';
$pdo->exec("UPDATE books SET available_copies = 10 WHERE available_copies < 0");
echo "Fixed negative stock values.\n";
