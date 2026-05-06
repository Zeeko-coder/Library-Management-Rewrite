<?php
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
//     header("Location: ../../../auth/login.php");
//     exit();
// }
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
            <a href="saved_books.php" class="menu-item">
                <i class="fas fa-bookmark"></i>
                <span>Saved Books</span>
            </a>
            <a href="notification.php" class="menu-item active">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
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
                <button class="btn-text" style="color: var(--text-muted); font-weight: 500;">
                    <i class="fas fa-check-double"></i> Mark all as read
                </button>
            </div>

            <!-- Category Tabs (Admin Mirror Style) -->
            <div class="notif-tabs animate-up delay-2">
                <button class="tab-btn active">All Alerts</button>
                <button class="tab-btn">Reminders</button>
                <button class="tab-btn">Reservations</button>
                <button class="tab-btn">Library News</button>
            </div>

            <!-- Notification Feed (Professional List) -->
            <div class="notif-feed-container animate-up delay-3">
                <!-- Unread Reminder -->
                <div class="notif-item unread">
                    <div class="notif-icon-box icon-reminder">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-header">
                            <span class="notif-title">Due Date Reminder: "Introduction to Algorithms"</span>
                            <span class="notif-time">1 hour ago</span>
                        </div>
                        <p class="notif-desc">Your borrowed book is due in <strong>3 days</strong>. Please return it by May 15 to avoid late fees.</p>
                        <button class="btn-text">Extend Loan</button>
                    </div>
                </div>

                <!-- Unread Reservation -->
                <div class="notif-item unread">
                    <div class="notif-icon-box icon-reserve">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-header">
                            <span class="notif-title">Reservation Available!</span>
                            <span class="notif-time">5 hours ago</span>
                        </div>
                        <p class="notif-desc">"Clean Code" by Robert C. Martin is now available for pickup at the main library desk.</p>
                        <button class="btn-text">View Reservation Details</button>
                    </div>
                </div>

                <!-- Read News -->
                <div class="notif-item">
                    <div class="notif-icon-box icon-news">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-header">
                            <span class="notif-title">Extended Library Hours for Finals</span>
                            <span class="notif-time">Yesterday</span>
                        </div>
                        <p class="notif-desc">The library will be open until 10:00 PM starting next week to support your final exam preparations.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>