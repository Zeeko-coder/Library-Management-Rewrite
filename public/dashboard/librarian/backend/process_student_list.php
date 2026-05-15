<?php
session_start();
require_once __DIR__ . '/../../../../database/db_connection.php';
require_once __DIR__ . '/../../../../helpers/cryptography_process.php';
require_once __DIR__ . '/../../../../config/smtp_config.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../../loginAs.php");
    exit();
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $pending_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE status = 'pending' AND created_at > ?");
    $pending_stmt->execute([$last_view]);
    $unread_count += $pending_stmt->fetchColumn();

    $overdue_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE (status = 'borrowed' OR status = 'overdue') AND due_date < NOW() AND due_date > ?");
    $overdue_stmt->execute([$last_view]);
    $unread_count += $overdue_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

// Update last notification view timestamp
try {
    $pdo->prepare("UPDATE users SET last_notif_view = NOW() WHERE user_id = ?")->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Silently fail
}

$success_message = $_SESSION['success_message'] ?? "";
$error_message = $_SESSION['error_message'] ?? "";
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Handle Request Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $borrow_id = $_POST['borrow_id'];
        $action = $_POST['action'];

        try {
            // Fetch Request/Student/Book Details for Email
            $stmt = $pdo->prepare("SELECT b.user_id, b.book_id, b.quantity, b.due_date, u.first_name, u.last_name, u.email, bk.title, bk.category
                                  FROM borrowings b
                                  JOIN users u ON b.user_id = u.user_id
                                  JOIN books bk ON b.book_id = bk.book_id
                                  WHERE b.id = ?");
            $stmt->execute([$borrow_id]);
            $details = $stmt->fetch();

            if ($details) {
                $student_name = decryptionData($details['first_name']) . " " . decryptionData($details['last_name']);
                $student_email = decryptionData($details['email']);
                $user_id = $details['user_id'];
                $book_title = $details['title'];
                $book_id = $details['book_id'];
                $category = $details['category'];
                $quantity = $details['quantity'];

                // Librarian Info
                $lib_stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE user_id = ?");
                $lib_stmt->execute([$_SESSION['user_id']]);
                $lib_data = $lib_stmt->fetch();
                $librarian_full_name = decryptionData($lib_data['first_name']) . " " . decryptionData($lib_data['last_name']);

                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = SMTP_PORT;
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($student_email);
                $mail->isHTML(true);

                if ($action === 'approve_request') {
                    $duration = $_POST['duration']; // e.g. "1 minute", "1 hour", "7 days"
                    $new_quantity = (int)$_POST['quantity'];
                    $original_quantity = (int)$details['quantity'];
                    $due_date = date('Y-m-d H:i:s', strtotime("+$duration"));

                    // Update stock: add back the difference (if librarian reduced it)
                    // or subtract more (if librarian increased it - though UI should limit this)
                    $diff = $original_quantity - $new_quantity;
                    $update_stock = $pdo->prepare("UPDATE books SET 
                        available_copies = available_copies + ?,
                        status = CASE WHEN (available_copies + ?) <= 0 THEN 'Not Available' ELSE 'Available' END
                        WHERE book_id = ?");
                    $update_stock->execute([$diff, $diff, $book_id]);

                    $stmt = $pdo->prepare("UPDATE borrowings SET status = 'borrowed', borrow_date = NOW(), due_date = ?, quantity = ? WHERE id = ?");
                    $stmt->execute([$due_date, $new_quantity, $borrow_id]);
                    $_SESSION['success_message'] = "Borrow request approved for $duration (Quantity: $new_quantity).";

                    // Send Email
                    $mail->Subject = 'Book Borrowing Request Approved';
                    $mail->Body    = "Hello <b>$student_name</b> (ID: <b>$user_id</b>), your book borrowing request for '<b>$book_title</b>' (ID: <b>$book_id</b>, Category: <b>$category</b>, Copies: <b>$new_quantity</b>) has been approved by Librarian <b>$librarian_full_name</b>. The borrowing period will end in <b>$duration</b> from now.";
                    $mail->send();
                } elseif ($action === 'reject_request') {
                    // Return stock on rejection
                    $update_stock = $pdo->prepare("UPDATE books SET 
                        available_copies = available_copies + ?,
                        status = CASE WHEN (available_copies + ?) <= 0 THEN 'Not Available' ELSE 'Available' END
                        WHERE book_id = ?");
                    $update_stock->execute([$quantity, $quantity, $book_id]);

                    $stmt = $pdo->prepare("UPDATE borrowings SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$borrow_id]);
                    $_SESSION['success_message'] = "Borrow request rejected. $quantity copies returned to stock.";

                    // Send Email
                    $mail->Subject = 'Book Borrowing Request Rejected';
                    $mail->Body    = "Hello <b>$student_name</b> (ID: <b>$user_id</b>), we regret to inform you that your book borrowing request for '<b>$book_title</b>' (ID: <b>$book_id</b>, Category: <b>$category</b>, Copies: <b>$quantity</b>) has been rejected by Librarian <b>$librarian_full_name</b>.";
                    $mail->send();
                } elseif ($action === 'notify_overdue') {
                    // Send Email
                    $mail->Subject = 'Overdue Book Reminder - LibroTech';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; color: #333;'>
                            <h2 style='color: #ef4444;'>Overdue Book Reminder</h2>
                            <p>Hello <strong>$student_name</strong>,</p>
                            <p>This is a reminder from <strong>LibroTech Library</strong> that the following book is currently overdue:</p>
                            <div style='background: #f8fafc; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                                <p><strong>Book Title:</strong> $book_title</p>
                                <p><strong>Due Date:</strong> " . date('M d, Y', strtotime($details['due_date'])) . "</p>
                            </div>
                            <p>Please return the book as soon as possible to avoid further fines or penalties.</p>
                            <p>Thank you!</p>
                            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                            <p style='font-size: 12px; color: #64748b;'>This is an automated notification. Please do not reply to this email.</p>
                        </div>
                    ";
                    $mail->send();

                    // Dashboard Notification
                    $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message) VALUES (?, 'reminder', ?, ?)");
                    $notif_title = "Overdue Reminder: " . $book_title;
                    $notif_msg = "The librarian has sent you a reminder for the book '$book_title' which was due on " . date('M d, Y', strtotime($details['due_date'])) . ". Please return it as soon as possible.";
                    $notif_stmt->execute([$user_id, $notif_title, $notif_msg]);

                    $_SESSION['success_message'] = "Overdue reminder sent to $student_name.";
                }
            }
            header("Location: student_list.php");
            exit();
        } catch (Exception $e) {
            $error_message = "Action failed: " . $e->getMessage();
        }
    }
}

// Fetch Students
try {
    // 1. All Students
    $students_stmt = $pdo->prepare("
        SELECT u.*, 
        (SELECT GROUP_CONCAT(bk.title SEPARATOR ', ') FROM borrowings b JOIN books bk ON b.book_id = bk.book_id WHERE b.user_id = u.user_id AND b.status IN ('borrowed', 'overdue')) as book_titles,
        (SELECT SUM(quantity) FROM borrowings WHERE user_id = u.user_id AND status IN ('borrowed', 'overdue')) as borrowed_copies,
        (SELECT COUNT(*) FROM borrowings WHERE user_id = u.user_id AND status = 'borrowed') as borrowed_count,
        (SELECT COUNT(*) FROM borrowings WHERE user_id = u.user_id AND status = 'returned') as returned_count
        FROM users u 
        WHERE u.role = 'Student'
        ORDER BY u.created_at DESC
    ");
    $students_stmt->execute();
    $all_students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Borrow Requests (Pending)
    $requests_stmt = $pdo->prepare("
        SELECT b.*, bk.title, bk.available_copies as stock, u.first_name, u.last_name, u.username as id_num
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'pending'
        ORDER BY b.created_at DESC
    ");
    $requests_stmt->execute();
    $borrow_requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Overdue Students
    $overdue_stmt = $pdo->prepare("
        SELECT b.*, bk.title, u.first_name, u.last_name, u.username as id_num
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE (b.status = 'overdue') OR (b.status = 'borrowed' AND b.due_date < NOW())
        ORDER BY b.due_date ASC
    ");
    $overdue_stmt->execute(); // Added missing execute()
    $overdue_list = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Returned Books
    $returned_stmt = $pdo->prepare("
        SELECT b.*, bk.title, u.first_name, u.last_name, u.email, u.user_id
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status IN ('returned', 'Returned')
        ORDER BY b.return_date DESC
    ");
    $returned_stmt->execute();
    $returned_list = $returned_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Active Borrow Students (Exclude Overdue)
    $active_stmt = $pdo->prepare("
        SELECT b.*, bk.title, bk.author, u.first_name, u.last_name, u.email, u.user_id
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'borrowed' AND b.due_date >= NOW()
        ORDER BY b.borrow_date DESC
    ");
    $active_stmt->execute();
    $active_borrow_list = $active_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Counts for badges
    $active_borrow_count = count($active_borrow_list);
    $returned_total_count = count($returned_list);

    // Tab counts (Unread since last visit)
    $new_request_count = 0;
    foreach ($borrow_requests as $req) {
        if ($req['created_at'] > $last_view) $new_request_count++;
    }

    $new_overdue_count = 0;
    foreach ($overdue_list as $od) {
        if ($od['due_date'] < date('Y-m-d H:i:s') && $od['due_date'] > $last_view) $new_overdue_count++;
    }

    $new_active_count = 0;
    foreach ($active_borrow_list as $active) {
        if ($active['borrow_date'] > $last_view) $new_active_count++;
    }

    $new_returned_count = 0;
    foreach ($returned_list as $ret) {
        if ($ret['return_date'] > $last_view) $new_returned_count++;
    }
} catch (PDOException $e) {
    $all_students = $borrow_requests = $overdue_list = $returned_list = $active_borrow_list = [];
    $new_request_count = $new_overdue_count = $new_active_count = $new_returned_count = 0;
}
