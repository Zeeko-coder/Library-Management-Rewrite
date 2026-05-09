<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../public/studentLogin.php");
    exit();
}

include '../../../database/db_connection.php';
include '../../../helpers/cryptography_process.php';

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

// Fetch Statistics
// 1. Currently Borrowed Books (Active, not overdue)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND LOWER(status) = 'borrowed' AND due_date >= NOW() AND (return_date IS NULL OR return_date = '')");
$stmt->execute([$student_id]);
$borrowed_count = $stmt->fetchColumn() ?: 0;


// 2. Returned All Time
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND return_date IS NOT NULL AND return_date != ''");
$stmt->execute([$student_id]);
$returned_count = $stmt->fetchColumn() ?: 0;


// 3. Due Soon (Due in next 3 days, but not yet overdue)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status IN ('borrowed', 'Borrowed') AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND due_date >= CURDATE()");
$stmt->execute([$student_id]);
$due_soon_count = $stmt->fetchColumn();


// Fetch Currently Reading Books
$stmt = $pdo->prepare("
    SELECT b.title, br.due_date, br.status, DATEDIFF(br.due_date, CURDATE()) as days_left 
    FROM borrowings br 
    JOIN books b ON br.book_id = b.book_id 
    WHERE br.user_id = ? AND br.status IN ('borrowed', 'overdue', 'Borrowed', 'Overdue')
    ORDER BY br.due_date ASC
");
$stmt->execute([$student_id]);
$currently_reading = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../src/css/student_dashboard.css">
</head>

<body class="dashboard-body">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../../../img/techbook.png" alt="LibroTech Logo">
            <span>LibroTech</span>
        </div>
        <nav class="sidebar-menu">
            <a href="#" class="menu-item active">
                <i class="fas fa-th-large"></i>
                <span>My Dashboard</span>
            </a>
            <a href="sidebar/search_catalog.php" class="menu-item">
                <i class="fas fa-search"></i>
                <span>Search Catalog</span>
            </a>
            <a href="sidebar/my_barrowed.php" class="menu-item">
                <i class="fas fa-book-reader"></i>
                <span>My Borrowed</span>
            </a>
            <a href="sidebar/notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="sidebar/my_profile.php" class="menu-item">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header animate-fade">
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Find your next great read...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Student User'; ?></span>
                    <span class="user-role">Undergraduate</span>
                </div>
                <div class="user-avatar">
                    <?php
                    $initials = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'ST';
                    echo $initials;
                    ?>
                </div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <div class="welcome-section animate-up delay-1">
                <h1>Hello, <?php echo $_SESSION['username'] ?? 'Student'; ?>!</h1>
                <p>Welcome back to your digital library. What would you like to read today?</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card animate-up delay-2">
                    <div class="stat-details">
                        <h3>Borrowed Books</h3>
                        <span class="number"><?php echo $borrowed_count; ?></span>
                    </div>
                    <div class="stat-icon icon-books">
                        <i class="fas fa-book-open"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-3">
                    <div class="stat-details">
                        <h3>Returned All Time</h3>
                        <span class="number"><?php echo $returned_count; ?></span>
                    </div>
                    <div class="stat-icon icon-users">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-4">
                    <div class="stat-details">
                        <h3>Due Soon</h3>
                        <span class="number"><?php echo $due_soon_count; ?></span>
                    </div>
                    <div class="stat-icon icon-borrow">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

            </div>


            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Currently Reading -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Currently Reading</h2>
                        <a href="#" class="view-all">View All Borrowed</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($currently_reading)): ?>
                            <div class="activity-item" style="justify-content: center; padding: 20px; color: var(--text-lighter);">
                                <span>You are not currently reading any books. <a href="sidebar/search_catalog.php" style="color: var(--primary-color);">Explore the catalog</a></span>
                            </div>
                        <?php else: ?>
                            <?php foreach ($currently_reading as $book): ?>
                                <div class="activity-item">
                                    <div class="activity-img">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="activity-info">
                                        <span class="activity-title"><?php echo htmlspecialchars($book['title']); ?></span>
                                        <span class="activity-desc">Due on: <strong><?php echo date('M d, Y', strtotime($book['due_date'])); ?></strong></span>
                                    </div>
                                    <?php
                                    $days = $book['days_left'];
                                    $color = '';
                                    $text = '';

                                    if (strtolower($book['status']) === 'overdue' || $days < 0) {

                                        $color = '#ef4444';
                                        $text = 'Overdue';
                                    } elseif ($days == 0) {
                                        $color = '#f59e0b';
                                        $text = 'Due Today';
                                    } elseif ($days <= 3) {
                                        $color = '#f59e0b';
                                        $text = $days . ' Day' . ($days > 1 ? 's' : '') . ' Left';
                                    } else {
                                        $text = $days . ' Days Left';
                                    }
                                    ?>
                                    <div class="activity-time" style="color: <?php echo $color; ?>;"><?php echo $text; ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <script src="../../../src/js/dashboard.js"></script>
</body>

</html>