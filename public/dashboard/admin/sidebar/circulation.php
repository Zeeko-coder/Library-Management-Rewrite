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
            <button id="sidebarToggle" class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-user" style="margin-left: auto;">
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
                <div class="circ-stat-card">
                    <div class="stat-content">
                        <h4>Pending</h4>
                        <div class="value"><?php echo number_format($pending_count); ?></div>
                    </div>
                    <div class="stat-icon icon-pending">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="circ-stat-card">
                    <div class="stat-content">
                        <h4>Active</h4>
                        <div class="value"><?php echo number_format($active_borrows); ?></div>
                    </div>
                    <div class="stat-icon icon-active">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="circ-stat-card">
                    <div class="stat-content">
                        <h4>Overdue</h4>
                        <div class="value"><?php echo number_format($overdue_books); ?></div>
                    </div>
                    <div class="stat-icon icon-overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="circ-stat-card">
                    <div class="stat-content">
                        <h4>Returned</h4>
                        <div class="value"><?php echo number_format($returned_total); ?></div>
                    </div>
                    <div class="stat-icon icon-returned">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="circ-stat-card">
                    <div class="stat-content">
                        <h4>Rejected</h4>
                        <div class="value"><?php echo number_format($rejected_count); ?></div>
                    </div>
                    <div class="stat-icon icon-rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Borrowing Records Table -->
            <div class="table-container animate-up delay-3">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px;">Circulation Records</h3>
                    <div style="display: flex; gap: 10px;">
                        <div style="position: relative;">
                            <i class="fas fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-lighter); font-size: 12px;"></i>
                            <input type="text" id="nameSearch" placeholder="Search by name or ID..." style="padding: 6px 12px 6px 30px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; outline: none; width: 220px; transition: var(--transition);">
                        </div>
                        <select id="statusFilter" style="padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 13px; cursor: pointer; outline: none; background: white;">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="borrowed">Active</option>
                            <option value="overdue">Overdue</option>
                            <option value="returned">Returned</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="table-wrapper" style="max-height: calc(100vh - 300px); overflow-y: auto;">
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
                        <tbody id="circTableBody">
                            <?php if (empty($recent_transactions)): ?>
                                <tr class="empty-row">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-lighter);">
                                        No recent circulation records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_transactions as $tx):
                                    $name = decryptionData($tx['first_name']) . " " . decryptionData($tx['last_name']);
                                    $raw_status = strtolower($tx['status']);
                                    $display_status = ucfirst($raw_status);
                                    $is_overdue = (strtotime($tx['due_date']) < time() && $raw_status === 'borrowed');

                                    $final_status = $raw_status;
                                    if ($is_overdue) {
                                        $final_status = "overdue";
                                        $display_status = "Overdue";
                                    }
                                ?>
                                    <tr class="transaction-row" data-status="<?php echo $final_status; ?>">
                                        <td><strong><?php echo htmlspecialchars($tx['title']); ?></strong></td>
                                        <td><strong><?php echo $name; ?></strong><br><small style="color: var(--text-lighter);">ID: <?php echo htmlspecialchars($tx['user_id']); ?></small></td>
                                        <td><?php echo date('M d, h:i A', strtotime($tx['borrow_date'])); ?></td>
                                        <td>
                                            <span class="due-date <?php echo $is_overdue ? 'due-danger' : ''; ?>" style="font-size: 13px;">
                                                <?php echo date('M d, Y', strtotime($tx['due_date'])); ?><br>
                                                <small style="opacity: 0.7;"><?php echo date('h:i A', strtotime($tx['due_date'])); ?></small>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="status-indicator status-<?php echo $final_status; ?>">
                                                <div class="indicator-dot"></div> <?php echo $display_status; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr id="emptyFilterRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-lighter);">
                                        No records found matching this status.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
            const statusFilter = document.getElementById('statusFilter');
            const rows = document.querySelectorAll('.transaction-row');
            const emptyFilterRow = document.getElementById('emptyFilterRow');

            if (statusFilter && nameSearch) {
                const filterRecords = () => {
                    const filterValue = statusFilter.value;
                    const searchTerm = nameSearch.value.toLowerCase();
                    let visibleCount = 0;

                    rows.forEach(row => {
                        const status = row.getAttribute('data-status');
                        const text = row.innerText.toLowerCase();

                        const statusMatch = (filterValue === 'all' || status === filterValue);
                        const nameMatch = text.includes(searchTerm);

                        if (statusMatch && nameMatch) {
                            row.style.display = 'table-row';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    if (emptyFilterRow) {
                        emptyFilterRow.style.display = (visibleCount === 0) ? 'table-row' : 'none';
                    }
                };

                statusFilter.addEventListener('change', filterRecords);
                nameSearch.addEventListener('input', filterRecords);
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