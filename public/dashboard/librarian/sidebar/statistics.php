<?php
session_start();
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

try {
    // 1. Total Borrowing Transactions
    $total_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings")->fetchColumn() ?: 0;

    // 2. Collection Distribution (Borrow counts per category)
    $category_stmt = $pdo->query("
        SELECT bk.category, COUNT(br.id) as count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY bk.category
        ORDER BY count DESC
    ");
    $category_data = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Most Borrowed Titles (Top 5)
    $top_books_stmt = $pdo->query("
        SELECT bk.title, COUNT(br.id) as borrow_count 
        FROM books bk
        JOIN borrowings br ON bk.book_id = br.book_id
        GROUP BY bk.book_id
        ORDER BY borrow_count DESC
        LIMIT 5
    ");
    $top_books = $top_books_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $total_borrows = 0;
    $category_data = $top_books = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics & Analytics | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/librarian_dashboard.css">
    <style>
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .progress-stat-item { margin-bottom: 20px; }
        .progress-stat-info { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; font-weight: 500; color: var(--text-dark); }
        .progress-bar-bg { height: 8px; background: #f1f5f9; border-radius: 10px; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: var(--primary-color); border-radius: 10px; transition: width 0.5s ease-in-out; }

        .ranking-list { display: flex; flex-direction: column; gap: 12px; }
        .ranking-item { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 8px; background: #f8fafc; border: 1px solid var(--border-light); transition: transform 0.2s; }
        .ranking-item:hover { transform: translateX(5px); }

        .rank-number { width: 28px; height: 28px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
        .rank-details h5 { font-size: 14px; margin: 0; color: var(--text-dark); }
        .rank-details span { font-size: 12px; color: var(--text-muted); }

        .export-buttons { display: flex; gap: 12px; }
        .btn-export {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-pdf { background: #fee2e2; color: #ef4444; border-color: #fecaca; }
        .btn-pdf:hover { background: #fecaca; }
        .btn-excel { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; }
        .btn-excel:hover { background: #bbf7d0; }

        @media print {
            .sidebar, .top-header, .export-buttons { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .analytics-grid { grid-template-columns: 1fr !important; }
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
            <a href="circulation.php" class="menu-item">
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
            <a href="statistics.php" class="menu-item active">
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
                <input type="text" placeholder="Search analytics reports...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Librarian'; ?></span>
                    <span class="user-role">Librarian</span>
                </div>
                <div class="user-avatar"><?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'LB'; ?></div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb">Librarian / Statistics</div>
                    <h1>Library Analytics</h1>
                </div>
                <div class="export-buttons">
                    <button class="btn-export btn-pdf" onclick="window.print()">
                        <i class="fas fa-file-pdf"></i> PDF Report
                    </button>
                    <button class="btn-export btn-excel" id="exportExcel">
                        <i class="fas fa-file-excel"></i> Excel Data
                    </button>
                </div>
            </div>

            <!-- Analytics Content -->
            <div class="analytics-grid">
                <!-- Collection Distribution -->
                <div class="glass-card animate-up delay-2" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <div class="card-header" style="margin-bottom: 25px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: var(--text-dark);">Collection Distribution</h2>
                    </div>
                    <?php if (empty($category_data)): ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 40px;">No data available.</p>
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

                <!-- Most Borrowed Titles -->
                <div class="glass-card animate-up delay-3" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <div class="card-header" style="margin-bottom: 25px;">
                        <h2 style="font-size: 18px; font-weight: 600; color: var(--text-dark);">Most Borrowed Titles</h2>
                    </div>
                    <div class="ranking-list">
                        <?php if (empty($top_books)): ?>
                            <p style="text-align: center; color: var(--text-muted); padding: 40px;">No records yet.</p>
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
            const rows = [
                ["LIBROTECH LIBRARY ANALYTICS REPORT"],
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

            let csvContent = "data:text/csv;charset=utf-8,";
            rows.forEach(function(rowArray) {
                let row = rowArray.map(value => `"${value}"`).join(",");
                csvContent += row + "\r\n";
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "LibroTech_Librarian_Stats_<?php echo date('Y-m-d'); ?>.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    </script>
</body>

</html>