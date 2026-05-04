<?php
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
//     header("Location: ../../../public/studentLogin.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../src/css/student_dashboard.css">
</head>

<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../../img/techbook.png" alt="LibroTech Logo">
            <span>LibroTech</span>
        </div>
        <nav class="sidebar-menu">
            <a href="#" class="menu-item active">
                <i class="fas fa-th-large"></i>
                <span>My Dashboard</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-search"></i>
                <span>Search Catalog</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-book-reader"></i>
                <span>My Borrowed</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-bookmark"></i>
                <span>Saved Books</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Find your next great read...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Student User'; ?></span>
                    <span class="user-role">Undergraduate</span>
                </div>
                <div class="user-avatar">
                    <?php 
                        $initials = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'ST';
                        echo $initials;
                    ?>
                </div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <div class="welcome-section animate-up delay-1">
                <h1>Hello, <?php echo $_SESSION['username'] ?? 'Student'; ?>!</h1>
                <p>Welcome back to your digital library. What would you like to read today?</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card animate-up delay-2">
                    <div class="stat-details">
                        <h3>Borrowed Books</h3>
                        <span class="number">04</span>
                    </div>
                    <div class="stat-icon icon-books">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-3">
                    <div class="stat-details">
                        <h3>Returned All Time</h3>
                        <span class="number">12</span>
                    </div>
                    <div class="stat-icon icon-users">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-4">
                    <div class="stat-details">
                        <h3>Due Soon</h3>
                        <span class="number">01</span>
                    </div>
                    <div class="stat-icon icon-borrow">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-5">
                    <div class="stat-details">
                        <h3>Pending Penalties</h3>
                        <span class="number">₱0.00</span>
                    </div>
                    <div class="stat-icon icon-overdue">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Currently Reading -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Currently Reading</h2>
                        <a href="#" class="view-all">View All Borrowed</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-img">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="activity-info">
                                <span class="activity-title">Introduction to Algorithms</span>
                                <span class="activity-desc">Due on: <strong>May 15, 2026</strong></span>
                            </div>
                            <div class="activity-time" style="color: #ef4444;">3 Days Left</div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-img">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="activity-info">
                                <span class="activity-title">Modern Web Design</span>
                                <span class="activity-desc">Due on: <strong>May 20, 2026</strong></span>
                            </div>
                            <div class="activity-time">8 Days Left</div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Section -->
                <div class="data-card recommendation-card animate-up delay-6">
                    <div class="card-header">
                        <h2 style="color: white;">Recommended For You</h2>
                    </div>
                    <p style="font-size: 13px; opacity: 0.9; margin-bottom: 20px;">Based on your interest in Technology and Engineering.</p>
                    <div class="activity-list">
                        <div class="activity-item" style="border-bottom-color: rgba(255,255,255,0.1);">
                            <div class="activity-info">
                                <span class="activity-title" style="color: white;">Clean Code</span>
                                <span class="activity-desc" style="color: rgba(255,255,255,0.7);">Robert C. Martin</span>
                            </div>
                            <button class="btn btn-outline" style="color: white; border-color: white; padding: 5px 10px; font-size: 11px;">Reserve</button>
                        </div>
                        <div class="activity-item" style="border-bottom: none;">
                            <div class="activity-info">
                                <span class="activity-title" style="color: white;">The Pragmatic Programmer</span>
                                <span class="activity-desc" style="color: rgba(255,255,255,0.7);">Andrew Hunt</span>
                            </div>
                            <button class="btn btn-outline" style="color: white; border-color: white; padding: 5px 10px; font-size: 11px;">Reserve</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../src/js/dashboard.js"></script>
</body>

</html>
