<?php
session_start();
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../../loginAs.php");
    exit();
}

// Update last notification view timestamp
try {
    $pdo->prepare("UPDATE users SET last_notif_view = NOW() WHERE user_id = ?")->execute([$_SESSION['user_id']]);
} catch (PDOException $e) {
    // Silently fail if update fails
}

// Get unread notification count for sidebar (will be 0 after update above)
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

// Fetch dynamic notifications from existing tables
$alerts = [];

try {
    // 1. Fetch Overdue Borrowings
    $overdue_stmt = $pdo->query("
        SELECT b.id, b.due_date as date, b.created_at, bk.title, u.first_name, u.last_name, 'overdue' as type
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'overdue' OR (b.status = 'borrowed' AND b.due_date < CURRENT_DATE)
        ORDER BY b.due_date ASC
    ");
    $overdue_items = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($overdue_items as $item) {
        $alerts[] = [
            'id' => $item['id'],
            'title' => "Overdue: " . $item['title'],
            'desc' => "Student <strong>" . decryptionData($item['first_name']) . " " . decryptionData($item['last_name']) . "</strong> has not returned this book. Due date was " . date('M d, Y', strtotime($item['date'])),
            'time' => $item['date'],
            'type' => 'overdue',
            'icon' => 'fa-clock',
            'class' => 'icon-alert',
            'link' => 'circulation.php'
        ];
    }

    // 2. Fetch Borrow Requests
    $request_stmt = $pdo->query("
        SELECT b.id, b.created_at as date, bk.title, u.first_name, u.last_name, 'request' as type
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'pending'
        ORDER BY b.created_at DESC
    ");
    $request_items = $request_stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($request_items as $item) {
        $alerts[] = [
            'id' => $item['id'],
            'title' => "New Borrow Request",
            'desc' => "<strong>" . decryptionData($item['first_name']) . " " . decryptionData($item['last_name']) . "</strong> requested <strong>" . $item['title'] . "</strong>.",
            'time' => $item['date'],
            'type' => 'request',
            'icon' => 'fa-book-reader',
            'class' => 'icon-info',
            'link' => 'student_list.php'
        ];
    }

    // Sort by time (most recent first)
    usort($alerts, function($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

} catch (PDOException $e) {
    $alerts = [];
}

$overdue_count = count(array_filter($alerts, fn($a) => $a['type'] === 'overdue'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | LibroTech</title>
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

        .notif-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 12px;
            width: fit-content;
        }

        .tab-btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            background: none;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .notif-feed-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-height: 650px;
            overflow-y: auto;
        }

        /* Custom Scrollbar for Notification Feed */
        .notif-feed-container::-webkit-scrollbar {
            width: 6px;
        }

        .notif-feed-container::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .notif-feed-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .notif-feed-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .notif-item {
            display: flex;
            gap: 1.25rem;
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-light);
            transition: background 0.2s;
            position: relative;
        }

        .notif-item:hover {
            background: #f8fafc;
        }

        .notif-item.unread {
            border-left: 4px solid var(--primary-color);
        }

        .notif-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .icon-alert { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .icon-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .icon-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }

        .notif-content { flex: 1; }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 4px;
        }

        .notif-title { font-weight: 600; color: var(--text-dark); font-size: 1rem; }
        .notif-time { font-size: 0.8rem; color: var(--text-muted); }
        .notif-desc { font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; }
        .notif-actions { margin-top: 12px; display: flex; gap: 15px; }

        .btn-text {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
        }

        .btn-text:hover { text-decoration: underline; }

        .badge-count {
            background: #ef4444;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
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
            <a href="notification.php" class="menu-item active">
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
                <input type="text" placeholder="Search notifications...">
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
                    <div class="breadcrumb">Librarian / Notifications</div>
                    <h1>System Alerts</h1>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="notif-tabs animate-up delay-2">
                <button class="tab-btn active" data-filter="all">All Alerts</button>
                <button class="tab-btn" data-filter="overdue">
                    Overdue
                    <?php if ($overdue_count > 0): ?>
                        <span class="badge-count"><?php echo $overdue_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Notification Feed -->
            <div class="notif-feed-container animate-up delay-3">
                <?php if (empty($alerts)): ?>
                    <div style="text-align: center; padding: 60px; color: var(--text-muted);">
                        <i class="fas fa-bell-slash" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>No new notifications at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($alerts as $notif): ?>
                        <div class="notif-item unread" data-type="<?php echo $notif['type']; ?>">
                            <div class="notif-icon-box <?php echo $notif['class']; ?>">
                                <i class="fas <?php echo $notif['icon']; ?>"></i>
                            </div>
                            <div class="notif-content">
                                <div class="notif-header">
                                    <span class="notif-title"><?php echo $notif['title']; ?></span>
                                    <span class="notif-time"><?php echo date('M d, Y', strtotime($notif['time'])); ?></span>
                                </div>
                                <p class="notif-desc"><?php echo $notif['desc']; ?></p>
                                <div class="notif-actions">
                                    <a href="<?php echo $notif['link']; ?>" class="btn-text">View Record</a>
                                    <button class="btn-text btn-dismiss" style="color: var(--text-muted);">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const items = document.querySelectorAll('.notif-item');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const filter = tab.getAttribute('data-filter');
                    
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    items.forEach(item => {
                        if (filter === 'all' || item.getAttribute('data-type') === filter) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            // Dismiss functionality (local only for now)
            const dismissBtns = document.querySelectorAll('.btn-dismiss');
            dismissBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const item = btn.closest('.notif-item');
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                });
            });
        });
    </script>
</body>

</html>