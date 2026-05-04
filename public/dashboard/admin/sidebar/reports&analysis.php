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
    <title>Reports & Analysis | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <style>
        /* Specific styles for Reports page */
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

        /* Report Controls */
        .report-controls {
            background: white;
            padding: 15px 25px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        /* Analytics Grid */
        .analytics-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        /* Progress Stats */
        .progress-stat-item {
            margin-bottom: 20px;
        }

        .progress-stat-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .progress-bar-bg {
            height: 10px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary-color);
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }

        /* Ranking List */
        .ranking-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .ranking-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px;
            border-radius: 8px;
            transition: var(--transition);
        }

        .ranking-item:hover {
            background: #f8fafc;
        }

        .rank-number {
            width: 24px;
            height: 24px;
            background: var(--primary-light);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .rank-details h5 {
            font-size: 14px;
            color: var(--text-dark);
            margin: 0;
        }

        .rank-details span {
            font-size: 12px;
            color: var(--text-lighter);
        }

        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-pdf { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        .btn-excel { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
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
            <a href="circulation.php" class="menu-item">
                <i class="fas fa-id-card"></i>
                <span>Circulation</span>
            </a>
            <a href="reports&analysis.php" class="menu-item active">
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
                <input type="text" placeholder="Global search for analytics...">
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
                        <span>Reports & Analysis</span>
                    </div>
                    <h1>Library Insights</h1>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div class="export-btn btn-pdf"><i class="fas fa-file-pdf"></i> PDF Report</div>
                    <div class="export-btn btn-excel"><i class="fas fa-file-excel"></i> Excel Data</div>
                </div>
            </div>

            <!-- Report Controls -->
            <div class="report-controls animate-up delay-2">
                <div style="display: flex; gap: 15px; align-items: center;">
                    <span style="font-size: 14px; font-weight: 600;">Time Period:</span>
                    <select style="padding: 8px 15px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 13px;">
                        <option>Last 30 Days</option>
                        <option>This Semester</option>
                        <option>Last Year</option>
                        <option>Custom Range</option>
                    </select>
                </div>
                <div style="color: var(--text-lighter); font-size: 13px;">
                    Last updated: <strong><?php echo date('H:i, M d, Y'); ?></strong>
                </div>
            </div>

            <!-- Analytics Content -->
            <div class="analytics-row">
                <!-- Left: Category Distribution -->
                <div class="data-card animate-up delay-3">
                    <div class="card-header">
                        <h2>Category Distribution</h2>
                        <i class="fas fa-info-circle" style="color: var(--text-lighter);"></i>
                    </div>
                    <div style="padding: 10px 0;">
                        <div class="progress-stat-item">
                            <div class="progress-stat-info">
                                <span>Computer Science & Technology</span>
                                <span>42% (5,242 Borrows)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: 42%; background: #3b82f6;"></div>
                            </div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-info">
                                <span>Literature & Fiction</span>
                                <span>28% (3,495 Borrows)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: 28%; background: #10b981;"></div>
                            </div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-info">
                                <span>Mathematics & Engineering</span>
                                <span>18% (2,246 Borrows)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: 18%; background: #f59e0b;"></div>
                            </div>
                        </div>
                        <div class="progress-stat-item">
                            <div class="progress-stat-info">
                                <span>Others</span>
                                <span>12% (1,499 Borrows)</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: 12%; background: #94a3b8;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Most Popular Books -->
                <div class="data-card animate-up delay-4">
                    <div class="card-header">
                        <h2>Top Rated Titles</h2>
                    </div>
                    <div class="ranking-list">
                        <div class="ranking-item">
                            <div class="rank-number">1</div>
                            <div class="rank-details">
                                <h5>Clean Code</h5>
                                <span>856 Borrows this month</span>
                            </div>
                        </div>
                        <div class="ranking-item">
                            <div class="rank-number">2</div>
                            <div class="rank-details">
                                <h5>Modern PHP 8</h5>
                                <span>642 Borrows this month</span>
                            </div>
                        </div>
                        <div class="ranking-item">
                            <div class="rank-number">3</div>
                            <div class="rank-details">
                                <h5>Design Patterns</h5>
                                <span>512 Borrows this month</span>
                            </div>
                        </div>
                        <div class="ranking-item">
                            <div class="rank-number" style="background: #e2e8f0; color: #64748b;">4</div>
                            <div class="rank-details">
                                <h5>Algorithms v4</h5>
                                <span>489 Borrows this month</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Summary Metrics -->
            <div class="stats-grid animate-up delay-5">
                <div class="stat-card" style="padding: 20px;">
                    <div class="stat-details">
                        <h3 style="font-size: 12px;">Borrowing Velocity</h3>
                        <span class="number" style="font-size: 20px;">14.2 Books/Day</span>
                        <span style="font-size: 11px; color: #10b981;"><i class="fas fa-caret-up"></i> 5.2% vs Last Month</span>
                    </div>
                </div>
                <div class="stat-card" style="padding: 20px;">
                    <div class="stat-details">
                        <h3 style="font-size: 12px;">Unique Borrowers</h3>
                        <span class="number" style="font-size: 20px;">842 Students</span>
                        <span style="font-size: 11px; color: #10b981;"><i class="fas fa-caret-up"></i> 12.8% active rate</span>
                    </div>
                </div>
                <div class="stat-card" style="padding: 20px;">
                    <div class="stat-details">
                        <h3 style="font-size: 12px;">Inventory Turnover</h3>
                        <span class="number" style="font-size: 20px;">0.85 Rate</span>
                        <span style="font-size: 11px; color: #3b82f6;">Optimal Performance</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>
