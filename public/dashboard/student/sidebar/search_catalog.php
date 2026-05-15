<?php
require_once __DIR__ . '/../backend/process_search_catalog.php';
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
    <link rel="stylesheet" href="../src/css/search_catalog.css">
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
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
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
            <div class="header-user" style="margin-left: auto;">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Student'; ?></span>
                    <span class="user-role">Student</span>
                </div>
                <div class="user-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'ST'; ?>
                </div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="animate-up delay-1" style="margin-bottom: 25px;">
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / Search Catalog</div>
                <h1>Browse Library Collection</h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success animate-up">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message'];
                                                        unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-error animate-up">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message'];
                                                                unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Filter Controls -->
            <form action="search_catalog.php" method="GET" class="search-controls animate-up delay-2">
                <div class="filter-group" style="flex: 1;">
                    <label>Search Books</label>
                    <input type="text" name="search" placeholder="Search by title or author..." value="<?php echo htmlspecialchars($search); ?>" style="width: 100%;">
                </div>
                <div class="filter-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="">All Books</option>
                        <option value="Available Only" <?php echo $availability_filter === 'Available Only' ? 'selected' : ''; ?>>Available Only</option>
                    </select>
                </div>
                <button type="submit" class="btn-borrow" style="margin-top: auto; padding: 10px 20px;">
                    <i class="fas fa-filter"></i> Apply
                </button>
            </form>

            <!-- Book Grid -->
            <div class="book-grid animate-up delay-3">
                <?php if (empty($books)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 50px; color: var(--text-muted);">
                        <i class="fas fa-book-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>No books found matching your criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <?php if (!empty($book['book_image'])): ?>
                                    <img src="../../../../<?php echo htmlspecialchars($book['book_image']); ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <i class="fas fa-book-open"></i>
                                <?php endif; ?>
                            </div>
                            <div class="book-info">
                                <h3 title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                                <p>Category: <?php echo htmlspecialchars($book['category']); ?></p>
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="status-pill available">Available (<?php echo $book['available_copies']; ?>)</span>
                                <?php else: ?>
                                    <span class="status-pill unavailable">Not Available</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-footer">
                                <button class="btn-borrow"
                                    data-id="<?php echo $book['book_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                    data-available="<?php echo $book['available_copies']; ?>"
                                    <?php echo $book['available_copies'] <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo $book['available_copies'] <= 0 ? 'Not Available' : 'Borrow Book'; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Borrow Modal -->
    <div id="borrowModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Borrow Book</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="search_catalog.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="book_id" id="modalBookId">
                    <div class="form-group">
                        <label>Book Title</label>
                        <input type="text" id="modalBookTitle" readonly style="background: #f1f5f9;">
                    </div>
                    <div class="form-group">
                        <label>Number of Copies</label>
                        <input type="number" name="quantity" id="modalQuantity" value="1" min="1" required>
                        <small id="availableHint" style="color: var(--text-muted); font-size: 11px;"></small>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 10px;">
                        <i class="fas fa-info-circle"></i> Your request will be sent to the Librarian for approval.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="borrow_book" class="btn-borrow">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/search_catalog.js"></script>
</body>

</html>