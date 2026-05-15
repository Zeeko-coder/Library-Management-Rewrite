<?php
include 'database/db_connection.php';

try {
    // 1. Add library_id column if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS library_id VARCHAR(20) UNIQUE AFTER user_id");
    echo "Column 'library_id' added or already exists.\n";

    // 2. Update existing students
    $stmt = $pdo->query("SELECT user_id FROM users WHERE role = 'Student' AND (library_id IS NULL OR library_id = '')");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($students as $student) {
        $id = $student['user_id'];
        // Format: 25-0001 (using 25 as current year prefix)
        $formatted_id = "25-" . str_pad($id, 4, "0", STR_PAD_LEFT);
        
        $update = $pdo->prepare("UPDATE users SET library_id = ? WHERE user_id = ?");
        $update->execute([$formatted_id, $id]);
        echo "Updated User ID {$id} to Library ID {$formatted_id}\n";
    }

    echo "Migration complete.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
