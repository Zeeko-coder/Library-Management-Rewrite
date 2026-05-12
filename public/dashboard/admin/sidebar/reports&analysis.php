<?php
require_once __DIR__ . '/../backend/process_reports&analysis.php';
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
    <link rel="stylesheet" href="../src/css/reports&analysis.css">
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
                    <div class="export-btn btn-pdf" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF Report</div>
                    <div class="export-btn btn-excel" id="exportExcel"><i class="fas fa-file-excel"></i> Excel Data</div>
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

            <!-- Analytics Content (Adjusted Layout) -->
            <div class="analytics-row">
                <!-- Left: Category Distribution -->
                <div class="data-card animate-up delay-3">
                    <div class="card-header">
                        <h2>Category Distribution</h2>
                        <i class="fas fa-info-circle" style="color: var(--text-lighter);"></i>
                    </div>
                    <div style="padding: 10px 0;">
                        <?php if (empty($category_data)): ?>
                            <p style="text-align: center; color: var(--text-lighter); padding: 20px;">No borrowing data available yet.</p>
                        <?php else: ?>
                            <?php
                            $colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];
                            $i = 0;
                            foreach ($category_data as $cat):
                                $percentage = ($total_borrows > 0) ? round(($cat['count'] / $total_borrows) * 100) : 0;
                                $color = $colors[$i % count($colors)];
                                $i++;
                            ?>
                                <div class="progress-stat-item">
                                    <div class="progress-stat-info">
                                        <span><?php echo htmlspecialchars($cat['category']); ?></span>
                                        <span><?php echo $percentage; ?>% (<?php echo $cat['count']; ?> Borrows)</span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Most Popular Books -->
                <div class="data-card animate-up delay-4">
                    <div class="card-header">
                        <h2>Top Rated Titles</h2>
                    </div>
                    <div class="ranking-list">
                        <?php if (empty($top_books)): ?>
                            <p style="text-align: center; color: var(--text-lighter); padding: 20px;">No books borrowed yet.</p>
                        <?php else: ?>
                            <?php $rank = 1;
                            foreach ($top_books as $book): ?>
                                <div class="ranking-item">
                                    <div class="rank-number" <?php echo $rank > 3 ? 'style="background: #e2e8f0; color: #64748b;"' : ''; ?>><?php echo $rank++; ?></div>
                                    <div class="rank-details">
                                        <h5><?php echo htmlspecialchars($book['title']); ?></h5>
                                        <span><?php echo $book['borrow_count']; ?> Total Borrows</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        const categoryData = <?php echo json_encode($category_data); ?>;
        const topBooks = <?php echo json_encode($top_books); ?>;
        const totalBorrows = <?php echo (int)$total_borrows; ?>;
        const reportDate = "<?php echo date('Y-m-d H:i'); ?>";
        const reportFileNameDate = "<?php echo date('Y-m-d'); ?>";
    </script>
    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/reports&analysis.js"></script>
</body>
</html>

</html>