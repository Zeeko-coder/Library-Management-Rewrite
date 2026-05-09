<?php
require_once 'database/db_connection.php';
$student_id = 7;

echo "--- Stats for User 7 ---\n";

$q1 = "SELECT COUNT(*) FROM borrowings WHERE user_id = $student_id AND status IN ('borrowed', 'Borrowed') AND due_date >= CURRENT_DATE AND return_date IS NULL";
$c1 = $pdo->query($q1)->fetchColumn();
echo "Borrowed Count (Query 1): $c1\n";

$q2 = "SELECT COUNT(*) FROM borrowings WHERE user_id = $student_id AND ((status IN ('overdue', 'Overdue')) OR (status IN ('borrowed', 'Borrowed') AND due_date < CURRENT_DATE)) AND return_date IS NULL";
$c2 = $pdo->query($q2)->fetchColumn();
echo "Overdue Count (Query 2): $c2\n";

$q3 = "SELECT COUNT(*) FROM borrowings WHERE user_id = $student_id AND return_date IS NOT NULL";
$c3 = $pdo->query($q3)->fetchColumn();
echo "Returned Count (Query 3): $c3\n";

// Check raw data for this user
echo "\n--- Raw Data for User 7 ---\n";
$stmt = $pdo->query("SELECT * FROM borrowings WHERE user_id = $student_id");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($row);
}
?>
