<?php
require_once 'database/db_connection.php';
$stmt = $pdo->query("DESCRIBE books");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
