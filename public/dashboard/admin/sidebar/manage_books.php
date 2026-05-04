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
    <title>Manage Books | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <style>
        /* Specific styles for Manage Books page */
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

        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-lighter);
            text-transform: uppercase;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            outline: none;
            min-width: 150px;
        }

        .books-table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .books-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .books-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-lighter);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        .books-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .book-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .book-cover-mini {
            width: 40px;
            height: 55px;
            background: #e2e8f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }

        .book-title {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .book-isbn {
            display: block;
            font-size: 12px;
            color: var(--text-lighter);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
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
            <a href="manage_books.php" class="menu-item active">
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
                <input type="text" placeholder="Global search for books or users...">
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
                        <span>Manage Books</span>
                    </div>
                    <h1>Book Inventory</h1>
                </div>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Book
                </button>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar animate-up delay-2">
                <div class="filter-group">
                    <label>Category</label>
                    <select>
                        <option value="">All Categories</option>
                        <option value="cs">Computer Science</option>
                        <option value="math">Mathematics</option>
                        <option value="fiction">Fiction</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Availability</label>
                    <select>
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Search Inventory</label>
                    <input type="text" placeholder="Title, Author, or ISBN">
                </div>
                <button class="btn btn-outline" style="margin-top: auto; height: 38px;">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
            </div>

            <!-- Books Table -->
            <div class="books-table-container animate-up delay-3">
                <table class="books-table">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="book-info">
                                    <div class="book-cover-mini"><i class="fas fa-book"></i></div>
                                    <div>
                                        <span class="book-title">Introduction to Algorithms</span>
                                        <span class="book-isbn">ISBN: 978-0262033848</span>
                                    </div>
                                </div>
                            </td>
                            <td>Thomas H. Cormen</td>
                            <td>Computer Science</td>
                            <td><span class="badge badge-success">Available</span></td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-view" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="action-btn btn-edit" title="Edit Book"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn btn-delete" title="Delete Book"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="book-info">
                                    <div class="book-cover-mini"><i class="fas fa-book"></i></div>
                                    <div>
                                        <span class="book-title">Clean Code</span>
                                        <span class="book-isbn">ISBN: 978-0132350884</span>
                                    </div>
                                </div>
                            </td>
                            <td>Robert C. Martin</td>
                            <td>Software Engineering</td>
                            <td><span class="badge badge-pending" style="background: #fff7ed; color: #c2410c;">Borrowed</span></td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-view" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="action-btn btn-edit" title="Edit Book"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn btn-delete" title="Delete Book"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="book-info">
                                    <div class="book-cover-mini"><i class="fas fa-book"></i></div>
                                    <div>
                                        <span class="book-title">Design Patterns</span>
                                        <span class="book-isbn">ISBN: 978-0201633610</span>
                                    </div>
                                </div>
                            </td>
                            <td>Erich Gamma</td>
                            <td>Computer Science</td>
                            <td><span class="badge badge-success">Available</span></td>
                            <td>
                                <div class="actions">
                                    <a href="#" class="action-btn btn-view" title="View Details"><i class="fas fa-eye"></i></a>
                                    <a href="#" class="action-btn btn-edit" title="Edit Book"><i class="fas fa-edit"></i></a>
                                    <a href="#" class="action-btn btn-delete" title="Delete Book"><i class="fas fa-trash"></i></a>
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