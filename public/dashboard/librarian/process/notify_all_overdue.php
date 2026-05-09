<?php
session_start();
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';
require_once '../../../../config/smtp_config.php';
require_once '../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

try {
    // Fetch all overdue records
    $stmt = $pdo->query("
        SELECT br.*, b.title, u.user_id as student_uid, u.first_name, u.last_name, u.email
        FROM borrowings br
        JOIN books b ON br.book_id = b.book_id
        JOIN users u ON br.user_id = u.user_id
        WHERE br.status = 'borrowed' AND br.due_date < NOW()
    ");
    $overdue_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($overdue_records)) {
        echo json_encode(['success' => true, 'message' => 'No overdue books found to notify.']);
        exit();
    }

    $count = 0;
    foreach ($overdue_records as $record) {
        $student_name = decryptionData($record['first_name']) . " " . decryptionData($record['last_name']);
        $student_email = decryptionData($record['email']);

        // 1. Send Email
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($student_email, $student_name);

            $mail->isHTML(true);
            $mail->Subject = 'Overdue Book Reminder - LibroTech';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                    <h2 style='color: #ef4444;'>Overdue Book Reminder</h2>
                    <p>Hello <strong>{$student_name}</strong>,</p>
                    <p>This is a reminder from <strong>LibroTech Library</strong> that the following book is currently overdue:</p>
                    <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                        <p><strong>Book Title:</strong> {$record['title']}</p>
                        <p><strong>Due Date:</strong> " . date('M d, Y', strtotime($record['due_date'])) . "</p>
                    </div>
                    <p>Please return the book as soon as possible.</p>
                </div>
            ";
            $mail->send();
        } catch (Exception $e) {
            // Log error but continue
        }

        // 2. Create Dashboard Notification
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message) 
            VALUES (?, 'reminder', ?, ?)
        ");
        $notif_title = "URGENT: Overdue Book - " . $record['title'];
        $notif_msg = "Your borrowed book '{$record['title']}' is overdue as of " . date('M d, Y', strtotime($record['due_date'])) . ". Please return it immediately.";
        $notif_stmt->execute([$record['student_uid'], $notif_title, $notif_msg]);
        
        $count++;
    }

    echo json_encode(['success' => true, 'message' => "Successfully sent reminders and notifications to $count students."]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
}
?>
