<?php
require_once 'database/db_connection.php';
$stmt = $pdo->query("DESCRIBE borrowings");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $columns);
