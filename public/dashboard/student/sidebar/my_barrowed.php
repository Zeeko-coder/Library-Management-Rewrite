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
    <title>My Borrowed Books | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for My Borrowed (Personal Circulation Mirror) */
        .personal-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-mini-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .stat-mini-card h4 {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-mini-card .value {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .loans-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .loans-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .loans-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-light);
        }

        .loans-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
        }

        .book-link {
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .book-link:hover {
            text-decoration: underline;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .active-loan {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .overdue-loan {
            background: #fef2f2;
            color: #b91c1c;
        }

        .returned-loan {
            background: #f0fdf4;
            color: #15803d;
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
            <a href="my_barrowed.php" class="menu-item active">
                <i class="fas fa-book-reader"></i>
                <span>My Borrowed</span>
            </a>
            <a href="saved_books.php" class="menu-item">
                <i class="fas fa-bookmark"></i>
                <span>Saved Books</span>
            </a>
            <a href="notification.php" class="menu-item">
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
                <input type="text" placeholder="Search my borrowing history...">
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
            <div class="animate-up delay-1" style="margin-bottom: 25px;">
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / My Borrowed</div>
                <h1>My Reading History</h1>
            </div>

            <!-- Personal Circulation Stats -->
            <div class="personal-stats animate-up delay-2">
                <div class="stat-mini-card" style="border-left-color: #6366f1;">
                    <h4>Currently Borrowed</h4>
                    <div class="value">04 Books</div>
                </div>
                <div class="stat-mini-card" style="border-left-color: #ef4444;">
                    <h4>Overdue Items</h4>
                    <div class="value">01 Book</div>
                </div>
                <div class="stat-mini-card" style="border-left-color: #10b981;">
                    <h4>Successfully Returned</h4>
                    <div class="value">12 Books</div>
                </div>
                <div class="stat-mini-card" style="border-left-color: #f59e0b;">
                    <h4>Late Fees</h4>
                    <div class="value">₱0.00</div>
                </div>
            </div>

            <!-- Active Loans Table -->
            <div class="loans-container animate-up delay-3">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Active Loans</h3>
                </div>
                <table class="loans-table">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <a href="#" class="book-link">Introduction to Algorithms</a>
                                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0;">ISBN: 978-0262033848</p>
                            </td>
                            <td>May 01, 2026</td>
                            <td><strong>May 15, 2026</strong></td>
                            <td><span class="status-badge active-loan"><i class="fas fa-clock"></i> 3 Days Left</span></td>
                        </tr>
                        <tr>
                            <td>
                                <a href="#" class="book-link">Clean Architecture</a>
                                <p style="font-size: 12px; color: var(--text-muted); margin: 2px 0;">ISBN: 978-0134494166</p>
                            </td>
                            <td>Apr 10, 2026</td>
                            <td><strong style="color: #ef4444;">Apr 24, 2026</strong></td>
                            <td><span class="status-badge overdue-loan"><i class="fas fa-exclamation-triangle"></i> Overdue</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Recent History -->
            <div class="loans-container animate-up delay-4" style="margin-top: 30px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Recently Returned</h3>
                </div>
                <table class="loans-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Return Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Modern PHP 8</td>
                            <td>May 03, 2026</td>
                            <td><span class="status-badge returned-loan"><i class="fas fa-check-circle"></i> Returned</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>