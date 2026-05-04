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
    <title>Manage Users | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <style>
        /* Specific styles for Manage Users page */
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

        /* Tabbed Interface */
        .user-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 25px;
            background: #e2e8f0;
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
            color: var(--text-lighter);
            cursor: pointer;
            transition: var(--transition);
        }

        .tab-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .users-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-lighter);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .user-identity {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-pfp {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .user-name {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-email {
            display: block;
            font-size: 12px;
            color: var(--text-lighter);
        }

        .role-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-librarian {
            background: #fff7ed;
            color: #c2410c;
        }

        .role-student {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .role-admin {
            background: #f5f3ff;
            color: #6d28d9;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-manage {
            background: #f1f5f9;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.9);
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
            <a href="manage_user.php" class="menu-item active">
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
                <input type="text" placeholder="Search for users by name or email...">
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
                        <span>Manage Users</span>
                    </div>
                    <h1>User Management</h1>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </button>
            </div>

            <!-- Role Tabs -->
            <div class="user-tabs animate-up delay-2">
                <button class="tab-btn active">All Users</button>
                <button class="tab-btn">Librarians</button>
                <button class="tab-btn">Students</button>
                <button class="tab-btn" style="position: relative;">
                    Pending Approvals
                    <span style="position: absolute; top: -5px; right: -10px; background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;">3</span>
                </button>
            </div>

            <!-- Users Table -->
            <div class="users-table-container animate-up delay-3">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User Identity</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Reg. Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Pending User Example -->
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-pfp" style="background: #f59e0b;">AL</div>
                                    <div>
                                        <span class="user-name">Alice Johnson</span>
                                        <span class="user-email">alice@librotech.edu</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-pill role-librarian">Librarian</span></td>
                            <td><span class="badge badge-pending">Pending</span></td>
                            <td>May 04, 2026</td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-approve"><i class="fas fa-check"></i> Approve</a>
                                    <a href="#" class="action-btn btn-reject"><i class="fas fa-times"></i> Reject</a>
                                </div>
                            </td>
                        </tr>
                        <!-- Active Librarian Example -->
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-pfp" style="background: #fbbf24;">JS</div>
                                    <div>
                                        <span class="user-name">John Smith</span>
                                        <span class="user-email">smith.j@librotech.edu</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-pill role-librarian">Librarian</span></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>Apr 12, 2026</td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-manage">Manage Account</a>
                                </div>
                            </td>
                        </tr>
                        <!-- Active Student Example -->
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-pfp" style="background: #3b82f6;">MD</div>
                                    <div>
                                        <span class="user-name">Mark Davis</span>
                                        <span class="user-email">mark.student@librotech.edu</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-pill role-student">Student</span></td>
                            <td><span class="badge badge-success">Active</span></td>
                            <td>May 01, 2026</td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-manage">Manage Account</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>