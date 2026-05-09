<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

$student_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Handle Return Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'return_book') {
    $borrow_id = $_POST['borrow_id'];
    try {
        $stmt = $pdo->prepare("UPDATE borrowings SET status = 'returned', return_date = NOW() WHERE id = ? AND user_id = ?");
        $stmt->execute([$borrow_id, $student_id]);
        $success_message = "Book returned successfully!";
    } catch (PDOException $e) {
        $error_message = "Error returning book: " . $e->getMessage();
    }
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$student_id]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings 
        WHERE user_id = ? 
        AND (
            (status IN ('borrowed', 'Borrowed') AND borrow_date > ?) OR 
            (status IN ('overdue', 'Overdue') AND due_date > ?) OR
            (status = 'rejected' AND created_at > ?)
        )");
    $notif_stmt->execute([$student_id, $last_view, $last_view, $last_view]);
    $unread_count = $notif_stmt->fetchColumn();

    // Add manual notifications from the notifications table
    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$student_id]);
    $unread_count += $manual_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

try {
    // 1. Stats Calculation
    // Currently Borrowed (Active, not overdue)
    $currently_borrowed = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND LOWER(status) = 'borrowed' AND due_date >= NOW() AND (return_date IS NULL OR return_date = '')");
    $currently_borrowed->execute([$student_id]);
    $borrowed_count = $currently_borrowed->fetchColumn() ?: 0;

    // Overdue Items (Status is 'overdue' OR due_date has passed)
    $overdue_items = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND (LOWER(status) = 'overdue' OR (LOWER(status) = 'borrowed' AND due_date < NOW())) AND (return_date IS NULL OR return_date = '')");
    $overdue_items->execute([$student_id]);
    $overdue_count = $overdue_items->fetchColumn() ?: 0;

    // Successfully Returned
    $returned_items = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND return_date IS NOT NULL AND return_date != ''");
    $returned_items->execute([$student_id]);
    $returned_count = $returned_items->fetchColumn() ?: 0;

    // 2. Fetch Active Borrows
    $active_borrows_stmt = $pdo->prepare("
        SELECT br.*, b.title, b.author 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? AND (br.return_date IS NULL OR br.return_date = '') AND br.status NOT IN ('pending', 'rejected')
        ORDER BY br.due_date ASC
    ");
    $active_borrows_stmt->execute([$student_id]);
    $active_borrows = $active_borrows_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Fetch Recently Returned
    $returned_loans_stmt = $pdo->prepare("
        SELECT br.*, b.title 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? AND br.return_date IS NOT NULL AND br.return_date != ''
        ORDER BY br.return_date DESC 
        LIMIT 10
    ");
    $returned_loans_stmt->execute([$student_id]);
    $returned_loans = $returned_loans_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $borrowed_count = $overdue_count = $returned_count = 0;
    $active_borrows = $returned_loans = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for My Borrowed (Personal Circulation Mirror) */
        .personal-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-mini-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-mini-card h4 {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-mini-card .value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .loans-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .loans-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .loans-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-light);
        }

        .loans-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
        }

        .book-link {
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .book-link:hover {
            text-decoration: underline;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .active-loan {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .overdue-loan {
            background: #fef2f2;
            color: #b91c1c;
        }

        .returned-loan {
            background: #f0fdf4;
            color: #15803d;
        }
    </style>
</head>

<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../../../img/techbook.png" alt="LibroTech Logo">
            <span>LibroTech</span>
        </div>
        <nav class="sidebar-menu">
            <a href="../dashboard_student.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>My Dashboard</span>
            </a>
            <a href="search_catalog.php" class="menu-item">
                <i class="fas fa-search"></i>
                <span>Search Catalog</span>
            </a>
            <a href="my_barrowed.php" class="menu-item active">
                <i class="fas fa-book-reader"></i>
                <span>My Borrowed</span>
            </a>
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="my_profile.php" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search my borrowing history...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Student'; ?></span>
                    <span class="user-role">Student</span>
                </div>
                <div class="user-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'ST'; ?>
                </div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="animate-up delay-1" style="margin-bottom: 25px;">
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / My Borrowed</div>
                <h1>My Borrowed History</h1>
                <?php if ($success_message): ?>
                    <div style="background: #ecfdf5; color: #10b981; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div style="background: #fef2f2; color: #ef4444; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Active Borrows Table -->
            <div class="loans-container animate-up delay-3">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Active Borrows</h3>
                </div>
                <table class="loans-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($active_borrows)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No active borrows found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($active_borrows as $loan):
                                $is_overdue = (strtolower($loan['status']) === 'overdue' || strtotime($loan['due_date']) < time());
                                $due_time = strtotime($loan['due_date']);
                                $diff = $due_time - time();
                                $days_left = ceil($diff / (60 * 60 * 24));
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($loan['title']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($loan['author']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></td>
                                    <td><strong <?php echo $is_overdue ? 'style="color: #ef4444;"' : ''; ?>><?php echo date('M d, Y', $due_time); ?></strong></td>
                                    <td>
                                        <?php if ($is_overdue): ?>
                                            <span class="status-badge overdue-loan"><i class="fas fa-exclamation-triangle"></i> Overdue</span>
                                        <?php else: ?>
                                            <span class="status-badge active-loan"><i class="fas fa-clock"></i> <?php echo $days_left; ?> Days Left</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent History -->
            <div class="loans-container animate-up delay-4" style="margin-top: 30px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Recently Returned</h3>
                </div>
                <table class="loans-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($returned_loans)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No return history found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($returned_loans as $r_loan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r_loan['title']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($r_loan['return_date'])); ?></td>
                                    <td><span class="status-badge returned-loan"><i class="fas fa-check-circle"></i> Returned</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>