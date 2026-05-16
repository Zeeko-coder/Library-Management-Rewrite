<?php
require_once __DIR__ . '/../database/db_connection.php';
$p = password_hash('librarian123', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE user_id = 2")->execute([$p]);
echo "Updated password for user 2 to librarian123\n";

$p_student = password_hash('student123', PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE user_id = 14")->execute([$p_student]);
echo "Updated password for user 14 to student123\n";
