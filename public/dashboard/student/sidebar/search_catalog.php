<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include '../../../../database/db_connection.php';
include '../../../../helpers/cryptography_process.php';

$student_id = $_SESSION['user_id'];

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$student_id]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings 
        WHERE user_id = ? 
        AND (
            (status IN ('borrowed', 'Borrowed') AND borrow_date > ?) OR 
            (status IN ('overdue', 'Overdue') AND due_date > ?) OR
            (status = 'rejected' AND created_at > ?)
        )");
    $notif_stmt->execute([$student_id, $last_view, $last_view, $last_view]);
    $unread_count = $notif_stmt->fetchColumn();

    // Add manual notifications from the notifications table
    $manual_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $manual_stmt->execute([$student_id]);
    $unread_count += $manual_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

// Fetch categories for filter
try {
    $cat_stmt = $pdo->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != ''");
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Handle Borrow Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_book'])) {
    $book_id = $_POST['book_id'];
    $quantity = (int)$_POST['quantity'];

    try {
        $pdo->beginTransaction();

        // Check availability
        $check_stmt = $pdo->prepare("SELECT title, available_copies FROM books WHERE book_id = ? FOR UPDATE");
        $check_stmt->execute([$book_id]);
        $book = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            throw new Exception("Book not found.");
        }

        if ($book['available_copies'] < $quantity) {
            throw new Exception("Not enough copies available.");
        }

        // Insert pending borrowing request
        $insert_stmt = $pdo->prepare("INSERT INTO borrowings (user_id, book_id, quantity, borrow_date, due_date, status) VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'pending')");
        $insert_stmt->execute([$student_id, $book_id, $quantity]);

        // Reserve the copies
        $update_stmt = $pdo->prepare("UPDATE books SET available_copies = available_copies - ? WHERE book_id = ?");
        $update_stmt->execute([$quantity, $book_id]);

        $pdo->commit();
        $_SESSION['success_message'] = "Borrowing request for '" . $book['title'] . "' has been sent to the Librarian for approval.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
    header("Location: search_catalog.php");
    exit();
}

// Search and Filter Logic
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$availability_filter = $_GET['availability'] ?? '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if ($availability_filter === 'Available Only') {
    $sql .= " AND available_copies > 0";
}

$sql .= " ORDER BY title ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        .btn-borrow {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-borrow:hover {
            background: var(--primary-dark);
        }

        .btn-borrow:disabled {
            background: #e2e8f0;
            color: #94a3b8;
            cursor: not-allowed;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 0;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .close-modal {
            cursor: pointer;
            font-size: 1.5rem;
            color: #94a3b8;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            outline: none;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
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
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Global search for books...">
            </div>
            <div class="header-user">
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
                            <div class="save-btn"><i class="far fa-bookmark"></i></div>
                            <div class="book-cover">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="book-info">
                                <h3 title="<?php echo htmlspecialchars($book['title']); ?>"><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                                <p>Category: <?php echo htmlspecialchars($book['category']); ?></p>
                                <?php if ($book['available_copies'] > 0): ?>
                                    <span class="status-pill available">Available (<?php echo $book['available_copies']; ?>)</span>
                                <?php else: ?>
                                    <span class="status-pill unavailable">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <div class="book-footer">
                                <button class="btn-borrow"
                                    data-id="<?php echo $book['book_id']; ?>"
                                    data-title="<?php echo htmlspecialchars($book['title']); ?>"
                                    data-available="<?php echo $book['available_copies']; ?>"
                                    <?php echo $book['available_copies'] <= 0 ? 'disabled' : ''; ?>>
                                    <?php echo $book['available_copies'] <= 0 ? 'Out of Stock' : 'Borrow Book'; ?>
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
                    <button type="button" class="btn-borrow close-modal" style="background: #94a3b8;">Cancel</button>
                    <button type="submit" name="borrow_book" class="btn-borrow">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('borrowModal');
            const closeBtns = document.querySelectorAll('.close-modal');
            const borrowBtns = document.querySelectorAll('.btn-borrow[data-id]');

            const modalBookId = document.getElementById('modalBookId');
            const modalBookTitle = document.getElementById('modalBookTitle');
            const modalQuantity = document.getElementById('modalQuantity');
            const availableHint = document.getElementById('availableHint');

            borrowBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const available = this.getAttribute('data-available');

                    modalBookId.value = id;
                    modalBookTitle.value = title;
                    modalQuantity.max = available;
                    availableHint.textContent = 'Available copies: ' + available;

                    modal.style.display = 'block';
                });
            });

            closeBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    modal.style.display = 'none';
                });
            });

            window.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });
        });
    </script>
</body>

</html>