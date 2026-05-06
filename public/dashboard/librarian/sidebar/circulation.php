<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

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

    // Fetch users for Issue Book dropdown
    $users_stmt = $pdo->query("SELECT user_id, first_name, last_name, role FROM users WHERE approval_status = 'Approved'");
    $dropdown_users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch available books for Issue Book dropdown
    $books_stmt = $pdo->query("SELECT book_id, title, available_copies FROM books WHERE available_copies > 0");
    $dropdown_books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch active borrowings for Return Book dropdown
    $active_stmt = $pdo->query("
        SELECT b.id, b.book_id, bk.title, u.first_name, u.last_name 
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'borrowed'
    ");
    $active_loans = $active_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $active_borrows = $due_today = $overdue_books = $returns_today = 0;
    $recent_transactions = $dropdown_users = $dropdown_books = $active_loans = [];
}

// Handle Issue Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['issue_book'])) {
    $user_id = $_POST['user_id'];
    $book_id = $_POST['book_id'];
    $due_date = $_POST['due_date'];
    $borrow_date = date('Y-m-d');

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
        $stmt->execute([$user_id, $book_id, $borrow_date, $due_date]);
        $stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $pdo->commit();
        $_SESSION['success_message'] = "Book issued successfully!";
        header("Location: circulation.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error issuing book: " . $e->getMessage();
    }
}

// Handle Return Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_return'])) {
    $borrowing_id = $_POST['borrowing_id'];
    $return_date = date('Y-m-d');

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("SELECT book_id FROM borrowings WHERE id = ?");
        $stmt->execute([$borrowing_id]);
        $book_id = $stmt->fetchColumn();
        $stmt = $pdo->prepare("UPDATE borrowings SET return_date = ?, status = 'returned' WHERE id = ?");
        $stmt->execute([$return_date, $borrowing_id]);
        $stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $pdo->commit();
        $_SESSION['success_message'] = "Book returned successfully!";
        header("Location: circulation.php");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error returning book: " . $e->getMessage();
    }
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
                <div class="tool-group">
                    <button class="btn btn-outline" id="openReturnModal">
                        <i class="fas fa-undo"></i> Return Book
                    </button>
                    <button class="btn btn-primary" id="openIssueModal">
                        <i class="fas fa-sign-out-alt"></i> Issue Book
                    </button>
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
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
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
                                    <td><?php echo date('M d, Y', strtotime($tx['borrow_date'])); ?></td>
                                    <td>
                                        <span class="due-date <?php echo $is_overdue ? 'due-danger' : ''; ?>">
                                            <?php echo date('M d, Y', strtotime($tx['due_date'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="status-indicator status-<?php echo $status_class; ?>">
                                            <div class="indicator-dot"></div> <?php echo $status; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tx['status'] === 'borrowed'): ?>
                                            <button class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Return</button>
                                        <?php else: ?>
                                            <span style="font-size: 11px; color: var(--text-lighter);">Completed</span>
                                        <?php endif; ?>
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

    <!-- Issue Book Modal -->
    <div id="issueBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Issue New Book</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="circulation.php" method="POST" class="modal-form">
                <div class="form-group">
                    <label>Select Student / Borrower</label>
                    <select name="user_id" required>
                        <option value="">Choose a user...</option>
                        <?php foreach ($dropdown_users as $user):
                            $name = decryptionData($user['first_name']) . " " . decryptionData($user['last_name']);
                        ?>
                            <option value="<?php echo $user['user_id']; ?>"><?php echo $name; ?> (<?php echo $user['role']; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Book</label>
                    <select name="book_id" required>
                        <option value="">Choose a book...</option>
                        <?php foreach ($dropdown_books as $book): ?>
                            <option value="<?php echo $book['book_id']; ?>"><?php echo htmlspecialchars($book['title']); ?> (<?php echo $book['available_copies']; ?> available)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Due Date</label>
                    <input type="date" name="due_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="issue_book" class="btn btn-primary" style="background: #3b82f6; border-color: #3b82f6;">Issue Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Return Book Modal -->
    <div id="returnBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Process Return</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="circulation.php" method="POST" class="modal-form">
                <div class="form-group">
                    <label>Select Active Loan</label>
                    <select name="borrowing_id" required>
                        <option value="">Choose a transaction...</option>
                        <?php foreach ($active_loans as $loan):
                            $name = decryptionData($loan['first_name']) . " " . decryptionData($loan['last_name']);
                        ?>
                            <option value="<?php echo $loan['id']; ?>"><?php echo htmlspecialchars($loan['title']); ?> - <?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="process_return" class="btn btn-primary" style="background: #3b82f6;">Complete Return</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const issueModal = document.getElementById('issueBookModal');
            const returnModal = document.getElementById('returnBookModal');
            const openIssueBtn = document.getElementById('openIssueModal');
            const openReturnBtn = document.getElementById('openReturnModal');
            const closeBtns = document.querySelectorAll('.close-modal');

            openIssueBtn.onclick = () => issueModal.style.display = 'block';
            openReturnBtn.onclick = () => returnModal.style.display = 'block';

            closeBtns.forEach(btn => {
                btn.onclick = () => {
                    issueModal.style.display = 'none';
                    returnModal.style.display = 'none';
                }
            });

            window.onclick = (e) => {
                if (e.target === issueModal) issueModal.style.display = 'none';
                if (e.target === returnModal) returnModal.style.display = 'none';
            }

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