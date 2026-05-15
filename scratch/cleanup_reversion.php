<?php
include 'database/db_connection.php';
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("DROP TABLE IF EXISTS students, librarians");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "Dropped students and librarians tables.\n";
?>
