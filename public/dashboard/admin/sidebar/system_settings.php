<?php
session_start();
// Authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../../public/loginAs.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <style>
        /* Settings Page Layout */
        .settings-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .settings-nav {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            height: fit-content;
        }

        .settings-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-lighter);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 5px;
            transition: var(--transition);
        }

        .settings-nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .settings-nav-item:hover:not(.active) {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .settings-form-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
        }

        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input, .form-group select, .form-group textarea {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            outline: none;
            font-size: 14px;
        }

        /* Toggle Component */
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .toggle-info h4 { font-size: 14px; font-weight: 600; color: var(--text-dark); }
        .toggle-info p { font-size: 12px; color: var(--text-lighter); }

        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider { background-color: var(--primary-color); }
        input:checked + .slider:before { transform: translateX(20px); }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
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
            <a href="reports&analysis.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="#" class="menu-item">
                <i class="fas fa-user-shield"></i>
                <span>System Security</span>
            </a>
            <a href="system_settings.php" class="menu-item active">
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
                <input type="text" placeholder="Search configuration settings...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name">System Admin</span>
                    <span class="user-role">Administrator</span>
                </div>
                <div class="user-avatar">AD</div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <div class="welcome-section animate-up delay-1">
                <h1>System Settings</h1>
                <p>Configure library policies, global defaults, and system preferences.</p>
            </div>

            <div class="settings-grid">
                <!-- Settings Sidebar (Inner) -->
                <div class="settings-nav animate-up delay-2">
                    <a href="#" class="settings-nav-item active"><i class="fas fa-university"></i> Library Profile</a>
                    <a href="#" class="settings-nav-item"><i class="fas fa-history"></i> Circulation Rules</a>
                    <a href="#" class="settings-nav-item"><i class="fas fa-coins"></i> Fines & Penalties</a>
                    <a href="#" class="settings-nav-item"><i class="fas fa-user-lock"></i> Account Security</a>
                    <a href="#" class="settings-nav-item"><i class="fas fa-server"></i> Maintenance</a>
                </div>

                <!-- Settings Content -->
                <div class="settings-content animate-up delay-3">
                    <div class="settings-form-card">
                        <div class="section-header">
                            <h2>General Library Profile</h2>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label>Library Display Name</label>
                            <input type="text" value="LibroTech Central Library System">
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Official Email</label>
                                <input type="email" value="library@librotech.edu.ph">
                            </div>
                            <div class="form-group">
                                <label>Contact Hotline</label>
                                <input type="text" value="+63 2 8888 1234">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 30px;">
                            <label>Campus Location / Address</label>
                            <textarea rows="3">Main Academic Building, Ground Floor, East Wing</textarea>
                        </div>

                        <div class="section-header">
                            <h2>Circulation Defaults</h2>
                        </div>

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Automatic Book Approvals</h4>
                                <p>Enable to bypass librarian approval for borrowing requests.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <h4>Email Notifications</h4>
                                <p>Send automated reminders for due dates and penalties.</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="action-bar">
                            <button class="btn btn-outline">Discard Changes</button>
                            <button class="btn btn-primary">Save System Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>
