<?php
session_start();
require_once '../../../database/db_connection.php';
require_once '../../../helpers/cryptography_process.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../loginAs.php");
    exit();
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view FROM users WHERE user_id = ?");
    $user_stmt->execute([$_SESSION['user_id']]);
    $last_view = $user_stmt->fetchColumn() ?: '1970-01-01 00:00:00';

    $pending_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE status = 'pending' AND created_at > ?");
    $pending_stmt->execute([$last_view]);
    $unread_count += $pending_stmt->fetchColumn();

    $overdue_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE (status = 'borrowed' OR status = 'overdue') AND due_date < NOW() AND due_date > ?");
    $overdue_stmt->execute([$last_view]);
    $unread_count += $overdue_stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_count = 0;
}

// Stats counts
try {
    $total_books = $pdo->query("SELECT COUNT(book_id) FROM books")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_books = 0;
}

try {
    $active_borrows = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'Borrowed'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $active_borrows = 0;
}

try {
    $due_today = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE due_date = CURRENT_DATE AND status = 'Borrowed'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $due_today = 0;
}

try {
    $overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE (status = 'Borrowed' OR status = 'Overdue') AND due_date < NOW() AND return_date IS NULL")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $overdue_books = 0;
}

// Recent Activities (Combined Circulation and Book Additions)
try {
    $activity_query = "
        (SELECT 
            br.borrow_date as activity_date, 
            'circulation' as type, 
            u.first_name, 
            u.last_name, 
            b.title, 
            br.return_date,
            NULL as category,
            NULL as admin_role
        FROM borrowings br
        JOIN books b ON br.book_id = b.book_id
        JOIN users u ON br.user_id = u.user_id)
        UNION ALL
        (SELECT 
            b.created_at as activity_date, 
            'book_addition' as type, 
            u.first_name, 
            u.last_name, 
            b.title, 
            NULL as return_date,
            b.category,
            u.role as admin_role
        FROM books b
        LEFT JOIN users u ON b.added_by = u.user_id)
        ORDER BY activity_date DESC 
        LIMIT 10
    ";
    $activity_stmt = $pdo->query($activity_query);
    $recent_activities = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_activities = [];
}

// Overdue Reminders
try {
    $overdue_stmt = $pdo->query("
        SELECT br.*, b.title, u.first_name, u.last_name, u.email
        FROM borrowings br
        JOIN books b ON br.book_id = b.book_id
        JOIN users u ON br.user_id = u.user_id
        WHERE (br.status = 'Borrowed' OR br.status = 'Overdue') 
        AND br.due_date < NOW() 
        AND br.return_date IS NULL
        ORDER BY br.due_date ASC
        LIMIT 5
    ");
    $overdue_reminders = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $overdue_reminders = [];
}

function getTimeAgo($timestamp)
{
    $time = strtotime($timestamp);
    if (!$time) return "N/A";
    $diff = time() - $time;

    if ($diff < 0) return "Just now";
    if ($diff < 60) return "Just now";
    if ($diff < 3600) return round($diff / 60) . " mins ago";
    if ($diff < 86400) return round($diff / 3600) . " hours ago";
    if ($diff < 2592000) return round($diff / 86400) . " days ago";
    return date("M d, Y", $time);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../src/css/librarian_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <span>Dashboard</span>
            </a>
            <a href="sidebar/book_cataloging.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
            </a>
            <a href="sidebar/student_list.php" class="menu-item">
                <i class="fas fa-user-graduate"></i>
                <span>Student List</span>
            </a>
            <a href="sidebar/notification.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                <?php if ($unread_count > 0): ?>
                    <span class="nav-badge"><?php echo $unread_count; ?></span>
                <?php endif; ?>
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
                <input type="text" placeholder="Search for books or records...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Librarian'; ?></span>
                    <span class="user-role">Librarian</span>
                </div>
                <div class="user-avatar">
                    <?php
                    $initials = isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'LB';
                    echo $initials;
                    ?>
                </div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <div class="welcome-section animate-up delay-1">
                <h1>Welcome, <?php echo $_SESSION['username'] ?? 'Librarian'; ?>!</h1>
                <p>Efficiently manage your library assets and circulation records.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card animate-up delay-2">
                    <div class="stat-details">
                        <h3>Books Cataloged</h3>
                        <span class="number"><?php echo number_format($total_books); ?></span>
                    </div>
                    <div class="stat-icon icon-books">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-3">
                    <div class="stat-details">
                        <h3>Active Borrows</h3>
                        <span class="number"><?php echo number_format($active_borrows); ?></span>
                    </div>
                    <div class="stat-icon icon-borrow">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-4">
                    <div class="stat-details">
                        <h3>Return Due Today</h3>
                        <span class="number"><?php echo number_format($due_today); ?></span>
                    </div>
                    <div class="stat-icon icon-users">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-5">
                    <div class="stat-details">
                        <h3>Overdue Books</h3>
                        <span class="number"><?php echo number_format($overdue_books); ?></span>
                    </div>
                    <div class="stat-icon icon-overdue">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Recent Activities</h2>
                        <a href="#" class="view-all">View All History</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($recent_activities)): ?>
                            <div class="activity-item">No recent activity.</div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <?php if ($activity['type'] === 'circulation'): 
                                        $is_return = !is_null($activity['return_date']);
                                        $student_name = decryptionData($activity['first_name']) . " " . decryptionData($activity['last_name']);
                                    ?>
                                        <div class="activity-img">
                                            <?php if ($is_return): ?>
                                                <i class="fas fa-arrow-up" style="color: #10b981;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-arrow-down" style="color: #f59e0b;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="activity-info">
                                            <span class="activity-title"><?php echo $is_return ? 'Return' : 'Borrow'; ?>: <?php echo htmlspecialchars($activity['title']); ?></span>
                                            <span class="activity-desc"><?php echo $is_return ? 'Returned' : 'Borrowed'; ?> by <strong><?php echo $student_name; ?></strong></span>
                                        </div>
                                    <?php else: 
                                        $admin_display = decryptionData($activity['first_name']) . " " . decryptionData($activity['last_name']);
                                        if (empty(trim($admin_display))) {
                                            $admin_display = $activity['admin_role'] ?: 'Administrator';
                                        }
                                    ?>
                                        <div class="activity-img">
                                            <i class="fas fa-book-medical" style="color: #10b981;"></i>
                                        </div>
                                        <div class="activity-info">
                                            <span class="activity-title">New Book Added</span>
                                            <span class="activity-desc">
                                                <strong><?php echo $admin_display; ?></strong> added new book
                                                (title: <?php echo htmlspecialchars($activity['title']); ?>)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="activity-time"><?php echo getTimeAgo($activity['activity_date']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Overdue Reminders -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Overdue Reminders</h2>
                        <a href="#" class="view-all notify-all-btn">Notify All</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($overdue_reminders)): ?>
                            <div class="activity-item">No overdue reminders.</div>
                        <?php else: ?>
                            <?php foreach ($overdue_reminders as $reminder):
                                $fname = decryptionData($reminder['first_name']);
                                $lname = decryptionData($reminder['last_name']);
                                $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                                $due_date = new DateTime($reminder['due_date']);
                                $today = new DateTime();
                                $days_overdue = $today->diff($due_date)->days;
                            ?>
                                <div class="activity-item">
                                    <div class="user-avatar" style="background: #ef4444;"><?php echo $initials; ?></div>
                                    <div class="activity-info">
                                        <span class="activity-title"><?php echo $fname . " " . $lname; ?></span>
                                        <span class="activity-desc"><?php echo $days_overdue; ?> day(s) overdue: "<?php echo htmlspecialchars($reminder['title']); ?>"</span>
                                    </div>
                                    <button class="btn btn-primary remind-btn"
                                        data-record-id="<?php echo $reminder['id']; ?>"
                                        data-student-name="<?php echo $fname . " " . $lname; ?>"
                                        data-book-title="<?php echo htmlspecialchars($reminder['title']); ?>"
                                        style="padding: 5px 10px; font-size: 12px;">
                                        Remind
                                    </button>
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