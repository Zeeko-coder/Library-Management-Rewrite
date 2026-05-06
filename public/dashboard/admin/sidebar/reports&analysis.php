<?php
session_start();
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

try {
    // Stats for reports
    $total_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings")->fetchColumn() ?: 0;
    $unique_borrowers = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM borrowings")->fetchColumn() ?: 0;
    $returned_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'returned'")->fetchColumn() ?: 0;
    $overdue_count = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'borrowed' AND due_date < CURRENT_DATE")->fetchColumn() ?: 0;

    // Fetch category distribution
    $category_stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY category
        ORDER BY count DESC
    ");
    $category_data = $category_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch Top Books
    $top_books_stmt = $pdo->query("
        SELECT bk.title, bk.author, COUNT(br.id) as borrow_count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY bk.book_id
        ORDER BY borrow_count DESC
        LIMIT 5
    ");
    $top_books = $top_books_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total_borrows = $unique_borrowers = $returned_books = $overdue_count = 0;
    $category_data = $top_books = [];
}
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

        .btn-pdf {
            background: #fee2e2;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .btn-excel {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        @media print {
            .sidebar, .management-header .tool-group, .report-controls {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .data-card {
                box-shadow: none !important;
                border: 1px solid #eee !important;
            }
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
                            <?php $rank = 1; foreach ($top_books as $book): ?>
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
        document.getElementById('exportExcel').addEventListener('click', function() {
            // CSV Content Preparation
            const rows = [
                ["LIBROTECH LIBRARY MANAGEMENT SYSTEM - ANALYTICS REPORT"],
                ["Generated on", "<?php echo date('Y-m-d H:i'); ?>"],
                [],
                ["CATEGORY DISTRIBUTION"],
                ["Category", "Borrow Count", "Percentage"]
            ];

            <?php foreach ($category_data as $cat): 
                $percentage = ($total_borrows > 0) ? round(($cat['count'] / $total_borrows) * 100) : 0;
            ?>
                rows.push(["<?php echo addslashes($cat['category']); ?>", "<?php echo $cat['count']; ?>", "<?php echo $percentage; ?>%"]);
            <?php endforeach; ?>

            rows.push([], ["TOP RANKED BOOKS"], ["Rank", "Title", "Borrow Count"]);
            <?php $rank = 1; foreach ($top_books as $book): ?>
                rows.push(["<?php echo $rank++; ?>", "<?php echo addslashes($book['title']); ?>", "<?php echo $book['borrow_count']; ?>"]);
            <?php endforeach; ?>

            // Create CSV string
            let csvContent = "data:text/csv;charset=utf-8,";
            rows.forEach(function(rowArray) {
                let row = rowArray.map(value => `"${value}"`).join(",");
                csvContent += row + "\r\n";
            });

            // Trigger Download
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "LibroTech_Analytics_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</body>

</html>