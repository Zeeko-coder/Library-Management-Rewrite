<?php
session_start();
require_once '../../../../database/db_connection.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
//     header("Location: ../../../auth/login.php");
//     exit();
// }

// Fetch books from database
$search = $_GET['search'] ?? '';
try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR category LIKE ? ORDER BY book_id ASC");
        $stmt->execute(["%$search%", "%$search%", "%$search%"]);
        $all_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $books_stmt = $pdo->query("SELECT * FROM books ORDER BY book_id ASC");
        $all_books = $books_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $all_books = [];
}

// Handle Delete Book
if (isset($_GET['delete_id'])) {
    $book_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
        $stmt->execute([$book_id]);
        $_SESSION['success_message'] = "Book deleted successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error deleting book: " . $e->getMessage();
    }
}

// Handle Add Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $available_copies = (int)$_POST['available_copies'];
    $status = $_POST['status'];
    $added_by = $_SESSION['user_id'] ?? 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, category, available_copies, status, added_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $category, $available_copies, $status, $added_by]);
        $_SESSION['success_message'] = "Book added successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding book: " . $e->getMessage();
    }
}

// Handle Edit Book Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_book'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $available_copies = (int)$_POST['available_copies'];
    $status = $_POST['status'];

    try {
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, category = ?, available_copies = ?, status = ? WHERE book_id = ?");
        $stmt->execute([$title, $author, $category, $available_copies, $status, $book_id]);
        $_SESSION['success_message'] = "Book updated successfully!";
        header("Location: book_cataloging.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating book: " . $e->getMessage();
    }
}
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
    <style>
        /* Specific styles for Cataloging (Admin-Style Mirror) */
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .filter-bar {
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

        .catalog-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .catalog-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .catalog-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-light);
        }

        .catalog-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
        }

        .book-info-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .book-cover-mini {
            width: 40px;
            height: 55px;
            background: #f1f5f9;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            border: 1px solid var(--border-light);
        }

        .book-title {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .book-isbn {
            display: block;
            font-size: 12px;
            color: var(--text-muted);
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
            transition: all 0.2s;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: modalSlideDown 0.3s ease-out;
        }

        @keyframes modalSlideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 20px 25px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .close-modal {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: #ef4444;
        }

        .modal-form {
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group input, .form-group select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            outline: none;
            font-family: inherit;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .modal-footer {
            margin-top: 10px;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge-available { background: #f0fdf4; color: #15803d; }
        .badge-borrowed { background: #fff7ed; color: #c2410c; }
        .badge-not-available { background: #fef2f2; color: #991b1b; }
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
            <a href="../dashboard_librarian.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="book_cataloging.php" class="menu-item active">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
            </a>
            <a href="circulation.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Circulation</span>
            </a>
            <a href="student_list.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Student List</span>
            </a>
            <a href="notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
            <a href="statistics.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Statistics</span>
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
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search for books in catalog...">
            </div>
            <div class="header-user">
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
                <button class="btn btn-primary" id="openAddModal">
                    <i class="fas fa-plus"></i> Add New Book
                </button>
            </div>

            <!-- Search Bar -->
            <form action="book_cataloging.php" method="GET" class="filter-bar animate-up delay-2">
                <div class="filter-group" style="flex: 1;">
                    <label>Search book</label>
                    <input type="text" name="search" placeholder="Search by title, author, or category..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top: auto; height: 42px; padding: 0 30px;">
                    <i class="fas fa-search"></i> Search Books
                </button>
            </form>

            <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success animate-up">
                    <i class="fas fa-check-circle"></i> 
                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger animate-up">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
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
                            <th>Actions</th>
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
                                $status = $book['status'] ?? 'Available';
                                $badge_class = strtolower(str_replace(' ', '-', $status));
                            ?>
                            <tr>
                                <td>
                                    <div class="book-info-cell">
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
            <form action="book_cataloging.php" method="POST" class="modal-form">
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
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
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
                </div>
            </div>
            <div class="modal-footer" style="padding: 20px 25px;">
                <button type="button" class="btn btn-secondary close-modal">Close</button>
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
            <form action="book_cataloging.php" method="POST" class="modal-form">
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
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="update_book" class="btn btn-primary">Update Book</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modals
            const addModal = document.getElementById('addBookModal');
            const viewModal = document.getElementById('viewBookModal');
            const editModal = document.getElementById('editBookModal');
            
            // Buttons
            const openAddBtn = document.getElementById('openAddModal');
            const viewBtns = document.querySelectorAll('.btn-view');
            const editBtns = document.querySelectorAll('.btn-edit');
            const deleteBtns = document.querySelectorAll('.btn-delete');
            const closeBtns = document.querySelectorAll('.close-modal');

            // Open Add Modal
            openAddBtn.addEventListener('click', () => {
                addModal.style.display = 'block';
            });

            // Delete Confirmation
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const bookId = btn.dataset.id;
                    const bookTitle = btn.dataset.title;
                    if (confirm(`Are you sure you want to delete "${bookTitle}"? This action cannot be undone.`)) {
                        window.location.href = `book_cataloging.php?delete_id=${bookId}`;
                    }
                });
            });

            // Open View Modal with Data
            viewBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const data = btn.dataset;
                    
                    document.getElementById('viewTitle').textContent = data.title;
                    document.getElementById('viewAuthor').textContent = 'By ' + data.author;
                    document.getElementById('viewID').textContent = '#' + data.id;
                    document.getElementById('viewCategory').textContent = data.category;
                    document.getElementById('viewCopies').textContent = data.copies;
                    document.getElementById('viewDate').textContent = data.date;
                    
                    const statusBadge = document.getElementById('viewStatus');
                    statusBadge.textContent = data.status;
                    statusBadge.className = 'badge badge-' + data.status.toLowerCase().replace(' ', '-');
                    
                    viewModal.style.display = 'block';
                });
            });

            // Open Edit Modal with Data
            editBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const data = btn.dataset;
                    
                    document.getElementById('editBookID').value = data.id;
                    document.getElementById('editTitle').value = data.title;
                    document.getElementById('editAuthor').value = data.author;
                    document.getElementById('editCategory').value = data.category;
                    document.getElementById('editCopies').value = data.copies;
                    document.getElementById('editStatus').value = data.status;
                    
                    editModal.style.display = 'block';
                });
            });

            // Close Modals
            closeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    addModal.style.display = 'none';
                    viewModal.style.display = 'none';
                    editModal.style.display = 'none';
                });
            });

            window.addEventListener('click', (e) => {
                if (e.target === addModal) addModal.style.display = 'none';
                if (e.target === viewModal) viewModal.style.display = 'none';
                if (e.target === editModal) editModal.style.display = 'none';
            });
        });
    </script>
</body>

</html>