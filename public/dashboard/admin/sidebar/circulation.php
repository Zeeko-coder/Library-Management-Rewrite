<?php
session_start();
// Authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../../public/loginAs.php");
//     exit();
// }
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
    <style>
        /* Specific styles for Circulation page */
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
            <a href="#" class="menu-item">
                <i class="fas fa-user-shield"></i>
                <span>System Security</span>
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
                <div class="tool-group">
                    <button class="btn btn-outline" style="border-color: #3b82f6; color: #3b82f6;">
                        <i class="fas fa-undo"></i> Return Book
                    </button>
                    <button class="btn btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Issue Book
                    </button>
                </div>
            </div>

            <!-- Circulation Stats -->
            <div class="circ-stats animate-up delay-2">
                <div class="circ-stat-card" style="border-left-color: #3b82f6;">
                    <h4>Active Borrows</h4>
                    <div class="value">1,124</div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #f59e0b;">
                    <h4>Due Today</h4>
                    <div class="value">42</div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #ef4444;">
                    <h4>Overdue Items</h4>
                    <div class="value">18</div>
                </div>
                <div class="circ-stat-card" style="border-left-color: #10b981;">
                    <h4>Returns (Today)</h4>
                    <div class="value">156</div>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Introduction to Algorithms</strong></td>
                            <td>Mark Davis (ST-2024-001)</td>
                            <td>May 01, 2026</td>
                            <td><span class="due-date">May 08, 2026</span></td>
                            <td>
                                <div class="status-indicator status-active">
                                    <div class="indicator-dot"></div> Active
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Return</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Clean Architecture</strong></td>
                            <td>Alice Johnson (LB-2024-055)</td>
                            <td>Apr 20, 2026</td>
                            <td><span class="due-date due-danger">Apr 27, 2026</span></td>
                            <td>
                                <div class="status-indicator status-overdue">
                                    <div class="indicator-dot"></div> Overdue
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-primary" style="padding: 4px 10px; font-size: 11px; background: #ef4444;">Remind</button>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Design Patterns</strong></td>
                            <td>Sam Miller (ST-2024-089)</td>
                            <td>May 03, 2026</td>
                            <td><span class="due-date due-warning">May 04, 2026</span></td>
                            <td>
                                <div class="status-indicator status-active">
                                    <div class="indicator-dot"></div> Due Today
                                </div>
                            </td>
                            <td>
                                <button class="btn btn-outline" style="padding: 4px 10px; font-size: 11px;">Return</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>