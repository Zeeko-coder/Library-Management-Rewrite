<?php
require_once __DIR__ . '/../backend/process_circulation.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circulation Desk | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <link rel="stylesheet" href="../src/css/circulation.css">

</head>

<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../../../img/techbook.png" alt="LibroTech Logo">
            <span>LibroTech</span>
        </div>
        <nav class="sidebar-menu">
            <a href="../dashboard_admin.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="manage_books.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Manage Books</span>
            </a>
            <a href="manage_user.php" class="menu-item">
                <i class="fas fa-users-cog"></i>
                <span>Manage Users</span>
            </a>
            <a href="circulation.php" class="menu-item active">
                <i class="fas fa-id-card"></i>
                <span>Circulation</span>
            </a>
            <a href="reports&analysis.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="system_settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
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
        <!-- Top Header -->
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search transactions, student IDs, or book titles...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'System Admin'; ?></span>
                    <span class="user-role">Administrator</span>
                </div>
                <div class="user-avatar">
                    <?php
                    $initials = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'AD';
                    echo $initials;
                    ?>
                </div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb">
                        <a href="../dashboard_admin.php">Dashboard</a>
                        <span>/</span>
                        <span>Circulation</span>
                    </div>
                    <h1>Circulation Desk</h1>
                </div>
            </div>

            <!-- Circulation Stats -->
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

            <!-- Transaction Table -->
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