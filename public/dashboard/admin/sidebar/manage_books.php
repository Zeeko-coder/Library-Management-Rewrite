<?php
require_once __DIR__ . '/../backend/process_manage_books.php';
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
    <link rel="stylesheet" href="../src/css/manage_books.css">
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
                <button class="btn btn-primary" id="openAddModal">
                    <i class="fas fa-plus"></i> Add New Book
                </button>
            </div>

            <!-- Search Bar -->
            <form action="manage_books.php" method="GET" class="filter-bar animate-up delay-2">
                <div class="filter-group" style="flex: 1;">
                    <label>Search book</label>
                    <input type="text" name="search" placeholder="Search by title, author, or category..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: auto; height: 42px; padding: 0 30px;">
                    <i class="fas fa-search"></i> Search Book
                </button>
            </form>

            <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success animate-up">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger animate-up">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <!-- Books Table -->
            <div class="books-table-container animate-up delay-3">
                <table class="books-table">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Available Copies</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_books)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-lighter);">
                                    <i class="fas fa-book-open" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                    No books found in the database.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_books as $book):
                                $status = $book['status'] ?? 'Available';
                                $badge_class = strtolower(str_replace(' ', '-', $status));
                            ?>
                                <tr>
                                    <td>
                                        <div class="book-info">
                                            <div class="book-cover-mini"><i class="fas fa-book"></i></div>
                                            <div>
                                                <span class="book-title"><?php echo htmlspecialchars($book['title']); ?></span>
                                                <span class="book-isbn">ID: <?php echo $book['book_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['category']); ?></td>
                                    <td><span style="font-weight: 600; color: var(--primary-color);"><?php echo $book['available_copies']; ?></span></td>
                                    <td><span class="badge badge-<?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <a href="#" class="action-btn btn-view" title="View Details"
                                                data-id="<?php echo $book['book_id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                                data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                                data-category="<?php echo htmlspecialchars($book['category']); ?>"
                                                data-copies="<?php echo $book['available_copies']; ?>"
                                                data-status="<?php echo $book['status']; ?>"
                                                data-date="<?php echo date('M d, Y', strtotime($book['created_at'])); ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="action-btn btn-edit" title="Edit Book"
                                                data-id="<?php echo $book['book_id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                                data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                                data-category="<?php echo htmlspecialchars($book['category']); ?>"
                                                data-copies="<?php echo $book['available_copies']; ?>"
                                                data-status="<?php echo $book['status']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#" class="action-btn btn-delete" title="Delete Book"
                                                data-id="<?php echo $book['book_id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add Book Modal -->
    <div id="addBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Book</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="manage_books.php" method="POST" class="modal-form">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" required placeholder="Enter book title">
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" required placeholder="Enter author name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required placeholder="e.g. Computer Science">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Available Copies</label>
                        <input type="number" name="available_copies" required min="1" value="1">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="Available">Available</option>
                            <option value="Not Available">Not Available</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_book" class="btn btn-primary">Save Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Book Modal -->
    <div id="viewBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Book Details</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div style="display: flex; gap: 30px; margin-bottom: 25px;">
                    <div style="width: 120px; height: 160px; background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 50px; color: var(--primary-color);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 id="viewTitle" style="font-size: 20px; margin-bottom: 5px;">Book Title</h3>
                        <p id="viewAuthor" style="color: var(--text-lighter); margin-bottom: 15px;">By Author Name</p>
                        <span id="viewStatus" class="badge">Available</span>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-lighter); font-weight: 600; margin-bottom: 5px;">Book ID</label>
                        <span id="viewID" style="font-weight: 500;">#001</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-lighter); font-weight: 600; margin-bottom: 5px;">Category</label>
                        <span id="viewCategory" style="font-weight: 500;">Computer Science</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-lighter); font-weight: 600; margin-bottom: 5px;">Available Copies</label>
                        <span id="viewCopies" style="font-weight: 500;">5</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-lighter); font-weight: 600; margin-bottom: 5px;">Date Added</label>
                        <span id="viewDate" style="font-weight: 500;">May 05, 2026</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="padding: 20px 25px;">
            </div>
        </div>
    </div>

    <!-- Edit Book Modal -->
    <div id="editBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Book</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="manage_books.php" method="POST" class="modal-form">
                <input type="hidden" name="book_id" id="editBookID">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" id="editTitle" required placeholder="Enter book title">
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" id="editAuthor" required placeholder="Enter author name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" id="editCategory" required placeholder="e.g. Computer Science">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Available Copies</label>
                        <input type="number" name="available_copies" id="editCopies" required min="1">
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="editStatus">
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Not Available">Not Available</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="update_book" class="btn btn-primary">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteBookModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; position: relative;">
                <h2 style="margin: 0; font-size: 1.2rem;">Confirm Delete</h2>
                <span class="close-modal" style="position: absolute; right: 20px; cursor: pointer;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div style="font-size: 50px; color: #ef4444; margin-bottom: 20px;">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <p style="font-size: 1rem; color: var(--text-dark); line-height: 1.5;">Are you sure you want to permanently delete <br><strong id="deleteBookTitle" style="color: #ef4444;"></strong>?</p>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 15px;">
                    <i class="fas fa-exclamation-triangle"></i> This action cannot be undone.
                </p>
            </div>
            <form action="manage_books.php" method="POST">
                <input type="hidden" name="book_id" id="deleteBookId">
                <div class="modal-footer" style="justify-content: center; gap: 12px; background: white; border-top: none; padding: 0 30px 35px;">
                    <button type="submit" name="delete_book" class="btn btn-primary" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">Yes, Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/manage_books.js"></script>
</body>
</html>