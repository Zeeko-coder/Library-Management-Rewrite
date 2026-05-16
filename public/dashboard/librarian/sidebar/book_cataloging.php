<?php
require_once __DIR__ . '/../backend/process_book_cataloging.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Cataloging | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/librarian_dashboard.css">
    <link rel="stylesheet" href="../src/css/book_cataloging.css">
</head>

<body class="dashboard-body">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../../../img/techbook.png" alt="LibroTech Logo">
            <span>LibroTech</span>
        </div>
        <nav class="sidebar-menu">
            <a href="../dashboard_librarian.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="book_cataloging.php" class="menu-item active">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
            </a>
            <a href="student_list.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Student List</span>
            </a>
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
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
            <button id="sidebarToggle" class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-user" style="margin-left: auto;">
                <div class="user-info">
                    <span class="user-name">Librarian</span>
                    <span class="user-role">Librarian</span>
                </div>
                <div class="user-avatar">LB</div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb">
                        <a href="../dashboard_librarian.php" style="color: var(--primary-color); text-decoration: none;">Dashboard</a>
                        <span>/</span>
                        <span>Book Cataloging</span>
                    </div>
                    <h1>Book Inventory</h1>
                </div>
            </div>

            <!-- Search Bar -->
            <form action="book_cataloging.php" method="GET" class="filter-bar animate-up delay-2">
                <div class="filter-group" style="flex: 1;">
                    <label>Search Books</label>
                    <input type="text" name="search" placeholder="Search by title, author, or category..." value="<?php echo htmlspecialchars($search); ?>">
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
                <button type="submit" class="btn btn-primary" style="margin-top: auto; height: 42px; padding: 0 30px;">
                    <i class="fas fa-filter"></i> Apply Filters
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

            <div class="catalog-table-container animate-up delay-3">
                <table class="catalog-table">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Available Copies</th>
                            <th>Status</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_books)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    <i class="fas fa-book-open" style="font-size: 40px; margin-bottom: 15px; display: block; opacity: 0.5;"></i>
                                    No books found in the catalog.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_books as $book):
                                $copies = (int)$book['available_copies'];
                                $status = ($copies <= 0) ? 'Not Available' : ($book['status'] ?? 'Available');
                                $badge_class = strtolower(str_replace(' ', '-', $status));
                            ?>
                                <tr>
                                    <td>
                                        <div class="book-info-cell">
                                            <div class="book-cover-mini">
                                                <?php if (!empty($book['book_image'])): ?>
                                                    <img src="../../../../<?php echo htmlspecialchars($book['book_image']); ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px;">
                                                <?php else: ?>
                                                    <i class="fas fa-book"></i>
                                                <?php endif; ?>
                                            </div>
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
                                                data-status="<?php echo $status; ?>"
                                                data-description="<?php echo htmlspecialchars($book['description'] ?? ''); ?>"
                                                data-year="<?php echo htmlspecialchars($book['year_published'] ?? ''); ?>"
                                                data-date="<?php echo date('M d, Y', strtotime($book['created_at'])); ?>"
                                                data-image="<?php echo !empty($book['book_image']) ? '../../../../' . htmlspecialchars($book['book_image']) : ''; ?>">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="#" class="action-btn btn-edit" title="Edit Book"
                                                data-id="<?php echo $book['book_id']; ?>"
                                                data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                                data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                                data-category="<?php echo htmlspecialchars($book['category']); ?>"
                                                data-copies="<?php echo $book['available_copies']; ?>"
                                                data-status="<?php echo $status; ?>"
                                                data-description="<?php echo htmlspecialchars($book['description'] ?? ''); ?>"
                                                data-year="<?php echo htmlspecialchars($book['year_published'] ?? ''); ?>"
                                                data-image="<?php echo !empty($book['book_image']) ? '../../../../' . htmlspecialchars($book['book_image']) : ''; ?>">
                                                <i class="fas fa-edit"></i>
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


    <!-- View Book Modal -->
    <div id="viewBookModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Book Details</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div style="display: flex; gap: 30px; margin-bottom: 25px;">
                    <div id="viewBookCover" style="width: 120px; height: 160px; background: #f1f5f9; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 50px; color: var(--primary-color); overflow: hidden;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div style="flex: 1;">
                        <h3 id="viewTitle" style="font-size: 20px; margin-bottom: 5px; color: var(--primary-color);">Book Title</h3>
                        <p id="viewAuthor" style="color: var(--text-muted); margin-bottom: 15px;">By Author Name</p>
                        <span id="viewStatus" class="badge">Available</span>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; background: #f8fafc; padding: 20px; border-radius: 12px;">
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Book ID</label>
                        <span id="viewID" style="font-weight: 500;">#001</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Category</label>
                        <span id="viewCategory" style="font-weight: 500;">Computer Science</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Available Copies</label>
                        <span id="viewCopies" style="font-weight: 500;">5</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Date Added</label>
                        <span id="viewDate" style="font-weight: 500;">May 05, 2026</span>
                    </div>
                    <div>
                        <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Year Published</label>
                        <span id="viewYear" style="font-weight: 500;">2024</span>
                    </div>
                </div>
                <div style="margin-top: 20px; background: #f8fafc; padding: 20px; border-radius: 12px;">
                    <label style="display: block; font-size: 11px; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 5px;">Description</label>
                    <p id="viewDescription" style="font-size: 14px; line-height: 1.6; color: var(--text-dark); margin: 0;"></p>
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
            <form action="book_cataloging.php" method="POST" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" name="book_id" id="editBookID">
                <div class="form-group">
                    <label>Book Image</label>
                    <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 10px;">
                        <div id="editImagePreview" style="width: 60px; height: 80px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <i class="fas fa-book" style="color: var(--primary-color);"></i>
                        </div>
                        <input type="file" name="book_image" id="editBookImage" accept="image/*" style="font-size: 13px;">
                    </div>
                </div>
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" id="editTitle" required placeholder="Enter book title">
                </div>
                <div class="form-group">
                    <label>Author</label>
                    <input type="text" name="author" id="editAuthor" required placeholder="Enter author name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Category</label>
                        <input type="text" name="category" id="editCategory" required placeholder="e.g. Computer Science">
                    </div>
                    <div class="form-group">
                        <label>Year Published</label>
                        <input type="number" name="year_published" id="editYear" required placeholder="e.g. 2024">
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editDescription" rows="3" placeholder="Enter book description..." style="width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; resize: vertical;"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Available Copies</label>
                        <input type="number" name="available_copies" id="editCopies" required min="0">
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

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/book_cataloging.js"></script>
</body>

</html>