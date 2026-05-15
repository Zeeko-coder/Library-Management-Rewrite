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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <span>Reports & Analysis</span>
                    </div>
                    <h1>Library Insights</h1>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div class="export-btn btn-pdf" onclick="window.print()"><i class="fas fa-file-pdf"></i> PDF Report</div>
                </div>
            </div>


            <!-- Summary Cards -->
            <div class="stats-summary animate-up delay-2">
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 12px; color: var(--text-lighter); margin-bottom: 2px;">Total Borrows</h4>
                        <span style="font-size: 20px; font-weight: 700;"><?php echo number_format($total_borrows); ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 12px; color: var(--text-lighter); margin-bottom: 2px;">Returned</h4>
                        <span style="font-size: 20px; font-weight: 700;"><?php echo number_format($returned_books); ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 12px; color: var(--text-lighter); margin-bottom: 2px;">Pending</h4>
                        <span style="font-size: 20px; font-weight: 700;"><?php echo number_format($pending_count); ?></span>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 12px; color: var(--text-lighter); margin-bottom: 2px;">Overdue</h4>
                        <span style="font-size: 20px; font-weight: 700;"><?php echo number_format($overdue_count); ?></span>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="analytics-grid">
                <!-- Status Distribution Chart -->
                <div class="data-card animate-up delay-3">
                    <div class="card-header">
                        <h2>Circulation Status</h2>
                        <i class="fas fa-chart-pie" style="color: var(--text-lighter);"></i>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Category Trends Chart -->
                <div class="data-card animate-up delay-4">
                    <div class="card-header">
                        <h2>Top Categories</h2>
                        <i class="fas fa-chart-bar" style="color: var(--text-lighter);"></i>
                    </div>
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Activity Trend Chart -->
                <div class="data-card animate-up delay-5" style="grid-column: span 2;">
                    <div class="card-header">
                        <h2>Borrowing Trends (Last 7 Days)</h2>
                        <i class="fas fa-chart-line" style="color: var(--text-lighter);"></i>
                    </div>
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Most Popular Books (Kept as list for variety) -->
                <div class="data-card animate-up delay-6" style="grid-column: span 2;">
                    <div class="card-header">
                        <h2>Top Borrowed Titles</h2>
                        <i class="fas fa-star" style="color: #f59e0b;"></i>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; padding: 10px 0;">
                        <?php if (empty($top_books)): ?>
                            <p style="text-align: center; color: var(--text-lighter); padding: 20px;">No books borrowed yet.</p>
                        <?php else: ?>
                            <?php foreach ($top_books as $book): ?>
                                <div class="ranking-item" style="border: 1px solid var(--border-color); padding: 15px; border-radius: 12px;">
                                    <div class="rank-details">
                                        <h5 style="font-size: 14px; margin-bottom: 4px;"><?php echo htmlspecialchars($book['title']); ?></h5>
                                        <p style="font-size: 12px; color: var(--text-lighter); margin-bottom: 8px;">By <?php echo htmlspecialchars($book['author']); ?></p>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="height: 6px; flex: 1; background: #f1f5f9; border-radius: 3px;">
                                                <div style="height: 100%; width: <?php echo ($total_borrows > 0) ? ($book['borrow_count'] / $total_borrows * 100) : 0; ?>%; background: var(--primary-color); border-radius: 3px;"></div>
                                            </div>
                                            <span style="font-size: 12px; font-weight: 600;"><?php echo $book['borrow_count']; ?> Borrows</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const categoryData = <?php echo json_encode($category_data); ?>;
        const trendData = <?php echo json_encode($trend_data); ?>;
        const statusDist = <?php echo json_encode($status_dist); ?>;
        const totalBorrows = <?php echo (int)$total_borrows; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // 1. Status Distribution Chart (Donut)
            const ctxStatus = document.getElementById('statusChart').getContext('2d');
            new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusDist).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
                    datasets: [{
                        data: Object.values(statusDist),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#64748b'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    },
                    cutout: '70%'
                }
            });

            // 2. Category Distribution Chart (Bar)
            const ctxCategory = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctxCategory, {
                type: 'bar',
                data: {
                    labels: categoryData.map(c => c.category),
                    datasets: [{
                        label: 'Borrows',
                        data: categoryData.map(c => c.count),
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { display: false } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 3. Borrowing Trends Chart (Line)
            const ctxTrend = document.getElementById('trendChart').getContext('2d');
            new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: trendData.map(t => new Date(t.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                    datasets: [{
                        label: 'Daily Activity',
                        data: trendData.map(t => t.count),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: '#3b82f6'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/reports&analysis.js"></script>
</body>
</html>