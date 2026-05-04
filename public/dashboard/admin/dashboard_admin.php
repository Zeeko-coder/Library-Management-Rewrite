<?php
session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../public/loginAs.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../src/css/dashboard.css">
    <style>
        /* Admin specific overrides if needed */
        :root {
            --primary-color: #1E3A8A;
            /* Deep Blue for Admin */
            --primary-dark: #1e1b4b;
        }
    </style>
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
                <span>Dashboard</span>
            </a>
            <a href="sidebar/manage_books.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Manage Books</span>
            </a>
            <a href="sidebar/manage_user.php" class="menu-item">
                <i class="fas fa-users-cog"></i>
                <span>Manage Users</span>
            </a>
            <a href="sidebar/circulation.php" class="menu-item">
                <i class="fas fa-id-card"></i>
                <span>Circulation</span>
            </a>
            <a href="sidebar/reports&analysis.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-user-shield"></i>
                <span>System Security</span>
            </a>
            <a href="sidebar/system_settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../auth/logout.php" class="menu-item">
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
                <input type="text" placeholder="Search for system records, users, or books...">
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
            <div class="welcome-section animate-up delay-1">
                <h1>Welcome Back, Administrator</h1>
                <p>Monitor and manage all library operations from your command center.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card animate-up delay-2">
                    <div class="stat-details">
                        <h3>Total Inventory</h3>
                        <span class="number">12,482</span>
                    </div>
                    <div class="stat-icon icon-books">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-3">
                    <div class="stat-details">
                        <h3>Registered Users</h3>
                        <span class="number">3,254</span>
                    </div>
                    <div class="stat-icon icon-users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-4">
                    <div class="stat-details">
                        <h3>Transactions</h3>
                        <span class="number">1,148</span>
                    </div>
                    <div class="stat-icon icon-borrow">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-5">
                    <div class="stat-details">
                        <h3>Overdue Books</h3>
                        <span class="number">24</span>
                    </div>
                    <div class="stat-icon icon-overdue">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>System-wide Activity</h2>
                        <a href="#" class="view-all">View Audit Logs</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-img">
                                <i class="fas fa-plus" style="color: #10b981;"></i>
                            </div>
                            <div class="activity-info">
                                <span class="activity-title">New Book Added</span>
                                <span class="activity-desc">"Advanced PHP" added by <strong>Librarian A</strong></span>
                            </div>
                            <div class="activity-time">5 mins ago</div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-img">
                                <i class="fas fa-user-plus" style="color: #3b82f6;"></i>
                            </div>
                            <div class="activity-info">
                                <span class="activity-title">New Student Registered</span>
                                <span class="activity-desc"><strong>Jane Doe</strong> completed registration</span>
                            </div>
                            <div class="activity-time">15 mins ago</div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-img">
                                <i class="fas fa-trash" style="color: #ef4444;"></i>
                            </div>
                            <div class="activity-info">
                                <span class="activity-title">Record Deleted</span>
                                <span class="activity-desc">Expired membership for <strong>John Smith</strong></span>
                            </div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Pending Approvals</h2>
                        <a href="#" class="view-all">Manage All</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="user-avatar" style="background: #f59e0b;">AL</div>
                            <div class="activity-info">
                                <span class="activity-title">Alice Johnson</span>
                                <span class="activity-desc">Role: Librarian</span>
                            </div>
                            <span class="badge badge-pending">Pending</span>
                        </div>
                        <div class="activity-item">
                            <div class="user-avatar" style="background: #f59e0b;">SM</div>
                            <div class="activity-info">
                                <span class="activity-title">Sam Miller</span>
                                <span class="activity-desc">Role: Student</span>
                            </div>
                            <span class="badge badge-pending">Pending</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../src/js/dashboard.js"></script>
</body>

</html>