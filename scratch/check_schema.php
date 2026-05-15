<?php
include 'database/db_connection.php';

try {
    echo "--- TABLE: books ---\n";
    $stmt = $pdo->query("DESCRIBE books");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- TABLE: borrowings ---\n";
    $stmt = $pdo->query("DESCRIBE borrowings");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
