<?php
session_start();
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';
require_once '../../../../config/smtp_config.php';
require_once '../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];

    try {
        // Fetch record details
        $stmt = $pdo->prepare("
            SELECT br.*, b.title, u.first_name, u.last_name, u.email
            FROM borrowings br
            JOIN books b ON br.book_id = b.book_id
            JOIN users u ON br.user_id = u.user_id
            WHERE br.id = ?
        ");
        $stmt->execute([$record_id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            echo json_encode(['success' => false, 'message' => 'Record not found.']);
            exit();
        }

        $student_name = decryptionData($record['first_name']) . " " . decryptionData($record['last_name']);
        $student_email = decryptionData($record['email']);

        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($student_email, $student_name);

        // Content
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
                <p>Please return the book as soon as possible to avoid further fines or penalties.</p>
                <p>Thank you!</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #64748b;'>This is an automated notification. Please do not reply to this email.</p>
            </div>
        ";

        $mail->send();

        // Also create a dashboard notification
        $notif_stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message) 
            VALUES (?, 'reminder', ?, ?)
        ");
        $notif_title = "Overdue Reminder: " . $record['title'];
        $notif_msg = "The librarian has sent you a reminder for the book '{$record['title']}' which was due on " . date('M d, Y', strtotime($record['due_date'])) . ". Please return it as soon as possible.";
        $notif_stmt->execute([$record['user_id'], $notif_title, $notif_msg]);

        echo json_encode(['success' => true, 'message' => "Reminder sent successfully to {$student_name} and notification created."]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
