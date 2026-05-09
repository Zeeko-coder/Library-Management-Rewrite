<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include '../../../../database/db_connection.php';
include '../../../../helpers/cryptography_process.php';

$user_id = $_SESSION['user_id'];

// Get unread notification count for sidebar (calculate before resetting)
$unread_count = 0;
$unread_reminders = 0;
$unread_news = 0;
$alerts = [];

try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$user_id]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    // Fetch all borrowings for this student to generate notifications
    $stmt = $pdo->prepare("
        SELECT br.*, b.title 
        FROM borrowings br 
        JOIN books b ON br.book_id = b.book_id 
        WHERE br.user_id = ? 
        ORDER BY br.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $borrowings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($borrowings as $item) {
        $notif = null;
        $status = strtolower($item['status']);
        $is_unread = false;

        if ($status === 'pending') {
            $notif = [
                'title' => "Request Sent: " . $item['title'],
                'desc' => "Your request to borrow this book is pending librarian approval.",
                'time' => $item['created_at'],
                'icon' => 'fa-paper-plane',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['created_at'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'borrowed') {
            $notif = [
                'title' => "Request Approved: " . $item['title'],
                'desc' => "Your borrow request has been approved! Due date: " . date('M d, Y', strtotime($item['due_date'])),
                'time' => $item['borrow_date'] ?: $item['created_at'],
                'icon' => 'fa-check-circle',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['borrow_date'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'overdue') {
            $notif = [
                'title' => "Overdue Notice: " . $item['title'],
                'desc' => "This book is past its due date. Please return it immediately.",
                'time' => $item['due_date'],
                'icon' => 'fa-exclamation-triangle',
                'class' => 'icon-reminder',
                'type' => 'reminder'
            ];
            if ($item['due_date'] > $last_view) {
                $is_unread = true;
                $unread_reminders++;
            }
        } elseif ($status === 'returned') {
            $notif = [
                'title' => "Book Returned: " . $item['title'],
                'desc' => "Thank you! You have successfully returned this book.",
                'time' => $item['return_date'],
                'icon' => 'fa-history',
                'class' => 'icon-news',
                'type' => 'news'
            ];
            if ($item['return_date'] > $last_view) {
                $is_unread = true;
                $unread_news++;
            }
        } elseif ($status === 'rejected') {
            $notif = [
                'title' => "Request Rejected: " . $item['title'],
                'desc' => "Unfortunately, your borrow request was rejected by the librarian.",
                'time' => $item['created_at'],
                'icon' => 'fa-times-circle',
                'class' => 'icon-reminder',
                'type' => 'reminder'
            ];
            if ($item['created_at'] > $last_view) {
                $is_unread = true;
                $unread_reminders++;
            }
        }

        if ($notif) {
            $notif['unread'] = $is_unread;
            $alerts[] = $notif;
        }
    }

    // --- NEW: Fetch Manual Reminders from notifications table ---
    $manual_stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $manual_stmt->execute([$user_id]);
    $manual_notifs = $manual_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($manual_notifs as $m_notif) {
        $is_unread = ($m_notif['is_read'] == 0);
        $type = $m_notif['type'] ?: 'reminder';
        
        $alerts[] = [
            'id' => $m_notif['id'],
            'title' => $m_notif['title'],
            'desc' => $m_notif['message'],
            'time' => $m_notif['created_at'],
            'icon' => ($type === 'reminder' ? 'fa-bell' : 'fa-info-circle'),
            'class' => ($type === 'reminder' ? 'icon-reminder' : 'icon-news'),
            'type' => $type,
            'unread' => $is_unread
        ];

        if ($is_unread) {
            if ($type === 'reminder') $unread_reminders++;
            else $unread_news++;
        }
    }
    // --- END NEW ---

    $unread_count = $unread_reminders + $unread_news;

    // Sort alerts by time
    usort($alerts, function ($a, $b) {
        return strtotime($b['time']) - strtotime($a['time']);
    });

    // Update last_notif_view when visiting this page
    $pdo->prepare("UPDATE users SET last_notif_view = NOW() WHERE user_id = ?")->execute([$user_id]);
    
    // Also mark manual notifications as read
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
} catch (PDOException $e) {
    $unread_count = $unread_reminders = $unread_news = 0;
    $alerts = [];
}
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
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for Notifications (Student Feed Mirror) */
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
            background: rgba(99, 102, 241, 0.02);
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

        .icon-reminder {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .icon-reserve {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .icon-news {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .notif-content {
            flex: 1;
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .notif-title {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1rem;
        }

        .notif-time {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .notif-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .btn-text {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            padding: 0;
        }

        .tab-btn {
            position: relative;
        }

        .tab-badge {
            background: #ef4444;
            color: white;
            font-size: 9px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 10px;
            position: absolute;
            top: -5px;
            right: -5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            pointer-events: none;
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
            <a href="../dashboard_student.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>My Dashboard</span>
            </a>
            <a href="search_catalog.php" class="menu-item">
                <i class="fas fa-search"></i>
                <span>Search Catalog</span>
            </a>
            <a href="my_barrowed.php" class="menu-item">
                <i class="fas fa-book-reader"></i>
                <span>My Borrowed</span>
            </a>
            <a href="notification.php" class="menu-item active">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="my_profile.php" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
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
                    <span class="user-name">Student</span>
                    <span class="user-role">Student</span>
                </div>
                <div class="user-avatar">ST</div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / Notifications</div>
                    <h1>Recent Updates</h1>
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="notif-tabs animate-up delay-2">
                <button class="tab-btn active" onclick="filterNotifs('all', this)">All Alerts</button>
                <button class="tab-btn" onclick="filterNotifs('reminder', this)">
                    Reminders
                    <?php if ($unread_reminders > 0): ?>
                        <span class="tab-badge"><?php echo $unread_reminders; ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" onclick="filterNotifs('news', this)">
                    Library Updates
                    <?php if ($unread_news > 0): ?>
                        <span class="tab-badge"><?php echo $unread_news; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Notification Feed (Professional List) -->
            <div class="notif-feed-container animate-up delay-3">
                <?php if (empty($alerts)): ?>
                    <div style="text-align: center; padding: 50px; color: var(--text-muted);">
                        <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No notifications yet. Check back later for updates!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($alerts as $notif):
                        $time_diff = time() - strtotime($notif['time']);
                        if ($time_diff < 60) $time_str = "Just now";
                        elseif ($time_diff < 3600) $time_str = floor($time_diff / 60) . " mins ago";
                        elseif ($time_diff < 86400) $time_str = floor($time_diff / 3600) . " hours ago";
                        else $time_str = date('M d, Y', strtotime($notif['time']));
                    ?>
                        <div class="notif-item <?php echo $notif['unread'] ? 'unread' : ''; ?>" data-type="<?php echo $notif['type']; ?>">
                            <div class="notif-icon-box <?php echo $notif['class']; ?>">
                                <i class="fas <?php echo $notif['icon']; ?>"></i>
                            </div>
                            <div class="notif-content">
                                <div class="notif-header">
                                    <span class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                                    <span class="notif-time"><?php echo $time_str; ?></span>
                                </div>
                                <p class="notif-desc"><?php echo $notif['desc']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        function filterNotifs(type, btn) {
            // Update tabs
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter items
            document.querySelectorAll('.notif-item').forEach(item => {
                if (type === 'all' || item.dataset.type === type) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>
</body>

</html>