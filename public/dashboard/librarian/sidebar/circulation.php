<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

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

// Stats counts for Circulation
try {
    $active_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND return_date IS NULL")->fetchColumn() ?: 0;

    $due_today = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND due_date = CURRENT_DATE")->fetchColumn() ?: 0;
    $overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND due_date < CURRENT_DATE")->fetchColumn() ?: 0;
    $returns_today = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE return_date = CURRENT_DATE")->fetchColumn() ?: 0;

    $stmt = $pdo->query("
        SELECT b.*, bk.title, u.first_name, u.last_name 
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        ORDER BY b.created_at DESC 
        LIMIT 10
    ");
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $active_borrows = $due_today = $overdue_books = $returns_today = 0;
    $recent_transactions = [];
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circulation Desk | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/librarian_dashboard.css">
    <style>
        /* Specific styles for Circulation page (Mirrored from Admin) */
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .breadcrumb {
            display: flex;
            gap: 10px;
            font-size: 14px;
            color: var(--text-lighter);
            margin-bottom: 5px;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Action Toolbar */
        .action-toolbar {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .tool-group {
            display: flex;
            gap: 10px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: var(--border-radius-lg);
            width: 500px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            animation: modalSlideDown 0.3s ease-out;
        }

        @keyframes modalSlideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px 25px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .close-modal {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-lighter);
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: var(--danger-color);
        }

        .modal-form {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group select,
        .form-group input {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group select:focus,
        .form-group input:focus {
            border-color: var(--primary-color);
        }

        .modal-footer {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        /* Alert Styles */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            font-weight: 500;
            animation: fadeInDown 0.4s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }

        .alert-success {
            background-color: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        @keyframes fadeInDown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Circulation Stats */
        .circ-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .circ-stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .circ-stat-card h4 {
            font-size: 12px;
            color: var(--text-lighter);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .circ-stat-card .value {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Transaction Table */
        .table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .circ-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .circ-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-lighter);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        .circ-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        .indicator-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-active {
            color: #3b82f6;
        }

        .status-active .indicator-dot {
            background: #3b82f6;
        }

        .status-overdue {
            color: #ef4444;
        }

        .status-overdue .indicator-dot {
            background: #ef4444;
        }

        .status-returned {
            color: #10b981;
        }

        .status-returned .indicator-dot {
            background: #10b981;
        }

        .due-date {
            font-weight: 600;
        }

        .due-warning {
            color: #f59e0b;
        }

        .due-danger {
            color: #ef4444;
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
            <a href="../dashboard_librarian.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="book_cataloging.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
            </a>
            <a href="circulation.php" class="menu-item active">
                <i class="fas fa-exchange-alt"></i>
                <span>Circulation</span>
            </a>
            <a href="student_list.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Student List</span>
            </a>
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="statistics.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Statistics</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search transactions, student IDs...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Librarian'; ?></span>
                    <span class="user-role">Librarian</span>
                </div>
                <div class="user-avatar">
                    <?php
                    $initials = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'AD';
                    echo $initials;
                    ?>
                </div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb">
                        <a href="../dashboard_librarian.php">Dashboard</a>
                        <span>/</span>
                        <span>Circulation</span>
                    </div>
                    <h1>Circulation Desk</h1>
                </div>
            </div>

            <!-- Circulation Stats (Mirrored from Admin) -->
            <div class="circ-stats animate-up delay-2">
                <div class="circ-stat-card" style="border-left-color: #3b82f6;">
                    <h4>Active Borrows</h4>
                    <div class="value"><?php echo number_format($active_borrows); ?></div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #f59e0b;">
                    <h4>Due Today</h4>
                    <div class="value"><?php echo number_format($due_today); ?></div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #ef4444;">
                    <h4>Overdue books</h4>
                    <div class="value"><?php echo number_format($overdue_books); ?></div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #10b981;">
                    <h4>Returns (Today)</h4>
                    <div class="value"><?php echo number_format($returns_today); ?></div>
                </div>
            </div>



            <!-- Transaction Table (Mirrored from Admin) -->

            <div class="table-container animate-up delay-3">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px;">Current Transactions</h3>
                    <div style="display: flex; gap: 10px;">
                        <select style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px;">
                            <option>Filter by Status</option>
                            <option>Active</option>
                            <option>Overdue</option>
                            <option>Returned</option>
                        </select>
                    </div>
                </div>
                <table class="circ-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrower</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_transactions)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-lighter);">
                                    No recent transactions found in the database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_transactions as $tx):
                                $name = decryptionData($tx['first_name']) . " " . decryptionData($tx['last_name']);
                                $status = ucfirst($tx['status']);
                                $status_class = strtolower($tx['status']);
                                $is_overdue = (strtotime($tx['due_date']) < time() && $tx['status'] === 'borrowed');
                                if ($is_overdue) {
                                    $status = "Overdue";
                                    $status_class = "overdue";
                                }
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($tx['title']); ?></strong></td>
                                    <td><?php echo $name; ?></td>
                                    <td><?php echo date('M d, h:i A', strtotime($tx['borrow_date'])); ?></td>
                                    <td>
                                        <span class="due-date <?php echo $is_overdue ? 'due-danger' : ''; ?>" style="font-size: 13px;">
                                            <?php echo date('M d, Y', strtotime($tx['due_date'])); ?><br>
                                            <small style="opacity: 0.7;"><?php echo date('h:i A', strtotime($tx['due_date'])); ?></small>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="status-indicator status-<?php echo $status_class; ?>">
                                            <div class="indicator-dot"></div> <?php echo $status; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Messages -->
    <div style="position: fixed; top: 20px; right: 20px; z-index: 2000; width: 350px;">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>
    </div>



    <script src="../../../../src/js/dashboard.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const closeBtns = document.querySelectorAll('.close-modal');

            closeBtns.forEach(btn => {
                btn.onclick = () => {
                    // Logic for any remaining modals if any
                }
            });

            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>