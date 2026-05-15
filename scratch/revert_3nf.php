<?php
include 'database/db_connection.php';

function tableExists($pdo, $table) {
    try {
        $pdo->query("SELECT 1 FROM `$table` LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // 1. Rename users_old to users if users doesn't exist
    if (tableExists($pdo, 'users_old') && !tableExists($pdo, 'users')) {
        $pdo->exec("RENAME TABLE users_old TO users");
        echo "Renamed users_old to users.\n";
    }

    // 2. Clear users table to ensure fresh migration
    $pdo->exec("TRUNCATE TABLE users");
    echo "Truncated users table.\n";

    // 3. Migrate Students
    $pdo->exec("INSERT INTO users (user_id, library_id, first_name, last_name, email, phone_number, gender, civil_status, username, password, role, approval_status, created_at, last_notif_view)
                SELECT student_id, library_id, first_name, last_name, email, phone_number, gender, civil_status, username, password, 'Student', approval_status, created_at, last_notif_view FROM students");
    echo "Migrated students back to users.\n";

    // 4. Migrate Librarians (Librarians don't have library_id, set to NULL)
    $pdo->exec("INSERT INTO users (user_id, library_id, first_name, last_name, email, phone_number, gender, civil_status, username, password, role, approval_status, created_at, last_notif_view)
                SELECT librarian_id, NULL, first_name, last_name, email, phone_number, gender, civil_status, username, password, 'Librarian', approval_status, created_at, last_notif_view FROM librarians");
    echo "Migrated librarians back to users.\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Migration complete.\n";

} catch (Exception $e) {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Error: " . $e->getMessage() . "\n";
}
?>
