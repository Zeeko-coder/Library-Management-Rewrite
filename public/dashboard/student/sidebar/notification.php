<?php
require_once __DIR__ . '/../backend/process_notification.php';
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
    <link rel="stylesheet" href="../src/css/notification.css">
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
                                    <span class="notif-time" data-timestamp="<?php echo strtotime($notif['time']); ?>"><?php echo $time_str; ?></span>
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
    <script src="../src/js/notification.js"></script>
</body>

</html>