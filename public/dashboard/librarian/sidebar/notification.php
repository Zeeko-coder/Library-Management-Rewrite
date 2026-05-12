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
    <link rel="stylesheet" href="../../../../src/css/librarian_dashboard.css">
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
            <a href="../dashboard_librarian.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="book_cataloging.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
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
        <script src="../src/js/notification.js"></script>
</body>

</html>