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
    <title>Search Catalog | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for Search Catalog (Student Visual Grid) */
        .search-controls {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            outline: none;
            min-width: 150px;
            font-family: inherit;
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
            border: 1px solid var(--border-light);
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
            justify-content: space-between;
            align-items: center;
        }

        .status-pill {
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .available {
            background: #f0fdf4;
            color: #15803d;
        }

        .unavailable {
            background: #fef2f2;
            color: #b91c1c;
        }

        .btn-reserve {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .save-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            border: 1px solid var(--border-light);
        }

        .save-btn:hover {
            color: #ef4444;
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
            <a href="search_catalog.php" class="menu-item active">
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
                <input type="text" placeholder="Global search for books...">
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
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / Search Catalog</div>
                <h1>Browse Library Collection</h1>
            </div>

            <!-- Filter Controls (Admin-Style) -->
            <div class="search-controls animate-up delay-2">
                <div class="filter-group" style="flex: 1;">
                    <label>Search Books</label>
                    <input type="text" placeholder="Search by title, author, or ISBN..." style="width: 100%;">
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select>
                        <option value="">All Categories</option>
                        <option>Computer Science</option>
                        <option>Engineering</option>
                        <option>Literature</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Availability</label>
                    <select>
                        <option value="">All Books</option>
                        <option>Available Only</option>
                    </select>
                </div>
                <button class="btn-reserve" style="margin-top: auto; padding: 10px 20px;">
                    <i class="fas fa-filter"></i> Apply
                </button>
            </div>

            <!-- Book Grid (Visual Cards) -->
            <div class="book-grid animate-up delay-3">
                <!-- Book Card 1 -->
                <div class="book-card">
                    <div class="save-btn"><i class="far fa-bookmark"></i></div>
                    <div class="book-cover"><i class="fas fa-book-open"></i></div>
                    <div class="book-info">
                        <h3>Advanced PHP Development</h3>
                        <p>Author: John Smith</p>
                        <span class="status-pill available">Available</span>
                    </div>
                    <div class="book-footer">
                        <button class="btn-reserve">Reserve Book</button>
                    </div>
                </div>

                <!-- Book Card 2 -->
                <div class="book-card">
                    <div class="save-btn"><i class="far fa-bookmark"></i></div>
                    <div class="book-cover"><i class="fas fa-code"></i></div>
                    <div class="book-info">
                        <h3>Modern JavaScript</h3>
                        <p>Author: Jane Doe</p>
                        <span class="status-pill unavailable">Borrowed</span>
                    </div>
                    <div class="book-footer">
                        <button class="btn-reserve" style="background: #e2e8f0; color: #94a3b8;" disabled>Out of Stock</button>
                    </div>
                </div>

                <!-- Book Card 3 -->
                <div class="book-card">
                    <div class="save-btn"><i class="far fa-bookmark"></i></div>
                    <div class="book-cover"><i class="fas fa-database"></i></div>
                    <div class="book-info">
                        <h3>Database Design Patterns</h3>
                        <p>Author: Alan Turing</p>
                        <span class="status-pill available">Available</span>
                    </div>
                    <div class="book-footer">
                        <button class="btn-reserve">Reserve Book</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
</body>

</html>