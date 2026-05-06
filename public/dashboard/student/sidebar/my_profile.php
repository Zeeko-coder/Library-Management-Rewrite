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
    <title>My Profile | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for My Profile (Split Layout) */
        .profile-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
            align-items: start;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-light);
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            font-size: 3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .profile-card h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .profile-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 5px 0 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
        }

        .stat-box h4 {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin: 0;
        }

        .stat-box span {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-light);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-light);
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .form-row {
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
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-group input {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            outline: none;
            background: #f8fafc;
            color: var(--text-dark);
            font-family: inherit;
        }

        .btn-save {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save:hover {
            filter: brightness(0.9);
            transform: translateY(-1px);
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
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="my_profile.php" class="menu-item active">
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
                <input type="text" placeholder="Search profile settings...">
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
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / My Profile</div>
                <h1>Profile Settings</h1>
            </div>

            <div class="profile-grid animate-up delay-2">
                <!-- Left: Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar-large">ST</div>
                    <h2>Student User</h2>
                    <p>Computer Science Department</p>
                    <div class="profile-stats">
                        <div class="stat-box">
                            <h4>12</h4>
                            <span>Returned</span>
                        </div>
                        <div class="stat-box" style="border-left: 1px solid var(--border-light); border-right: 1px solid var(--border-light); padding: 0 20px;">
                            <h4>04</h4>
                            <span>Active</span>
                        </div>
                        <div class="stat-box">
                            <h4>0</h4>
                            <span>Fines</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Details Card -->
                <div class="details-card">
                    <div class="section-header">
                        <h3>Personal Information</h3>
                        <i class="fas fa-user-edit" style="color: var(--text-muted);"></i>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="Student User" readonly>
                        </div>
                        <div class="form-group">
                            <label>Student ID</label>
                            <input type="text" value="ST-2024-001" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="student@university.edu">
                        </div>
                        <div class="form-group">
                            <label>Contact Number</label>
                            <input type="text" placeholder="+63 9XX XXX XXXX">
                        </div>
                    </div>

                    <div class="section-header" style="margin-top: 20px;">
                        <h3>Security</h3>
                        <i class="fas fa-shield-alt" style="color: var(--text-muted);"></i>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" placeholder="********">
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" placeholder="Enter new password">
                        </div>
                    </div>

                    <div style="margin-top: 30px; text-align: right;">
                        <button class="btn-save">Update Profile</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>