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
    <title>Saved Books | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for Saved Books (Book Wishlist) */
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .book-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid var(--border-light);
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            width: 100%;
            height: 180px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--primary-light);
        }

        .book-info h3 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .book-info p {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin: 4px 0;
        }

        .book-footer {
            margin-top: auto;
            display: flex;
            gap: 8px;
        }

        .btn-reserve {
            flex: 1;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-remove {
            width: 32px;
            background: #fef2f2;
            color: #ef4444;
            border: 1px solid #fee2e2;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-remove:hover {
            background: #ef4444;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 100px 0;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.2;
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
            <a href="saved_books.php" class="menu-item active">
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
                <input type="text" placeholder="Search through your saved books...">
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
                    <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / Saved Books</div>
                    <h1>My Watchlist</h1>
                </div>
                <button class="btn" style="background: #f1f5f9; color: var(--text-dark); border: 1px solid var(--border-light); font-size: 13px; padding: 8px 15px; border-radius: 8px;">
                    <i class="fas fa-trash-alt"></i> Clear All
                </button>
            </div>

            <!-- Saved Books Grid -->
            <div class="book-grid animate-up delay-2">
                <!-- Saved Book 1 -->
                <div class="book-card">
                    <div class="book-cover"><i class="fas fa-book"></i></div>
                    <div class="book-info">
                        <h3>Clean Code</h3>
                        <p>Robert C. Martin</p>
                        <span style="font-size: 11px; color: #10b981; font-weight: 600;">Available Now</span>
                    </div>
                    <div class="book-footer">
                        <button class="btn-reserve">Reserve Now</button>
                        <button class="btn-remove" title="Remove from saved"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <!-- Saved Book 2 -->
                <div class="book-card">
                    <div class="book-cover"><i class="fas fa-book-open"></i></div>
                    <div class="book-info">
                        <h3>The Pragmatic Programmer</h3>
                        <p>Andrew Hunt</p>
                        <span style="font-size: 11px; color: #f59e0b; font-weight: 600;">Currently Borrowed</span>
                    </div>
                    <div class="book-footer">
                        <button class="btn-reserve" style="background: #e2e8f0; color: #94a3b8;" disabled>Notify Me</button>
                        <button class="btn-remove" title="Remove from saved"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            </div>

            <!-- Example of Empty State (Hidden) -->
            <!-- 
            <div class="empty-state">
                <i class="fas fa-bookmark"></i>
                <h2>Your watchlist is empty</h2>
                <p>Start exploring the catalog and save books you want to read later!</p>
            </div> 
            -->
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>