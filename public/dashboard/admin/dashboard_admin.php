<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../../../database/db_connection.php';
require_once '../../../helpers/cryptography_process.php';

// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../public/loginAs.php");
//     exit();
// }

// Stats counts
try {
    $total_books = $pdo->query("SELECT COUNT(book_id) FROM books")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $total_books = 0; // Table might not exist yet
}

try {
    $registered_users = $pdo->query("SELECT COUNT(*) FROM users WHERE approval_status = 'Approved'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $registered_users = 0;
}

try {
    $transactions = $pdo->query("SELECT COUNT(*) FROM borrowings")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $transactions = 0;
}


try {
    $overdue_books = $pdo->query("SELECT COUNT(*) FROM borrowings WHERE status = 'Overdue'")->fetchColumn() ?: 0;
} catch (PDOException $e) {
    $overdue_books = 0;
}


// Pending Approvals
$pending_stmt = $pdo->query("SELECT * FROM users WHERE approval_status = 'Pending' ORDER BY created_at DESC LIMIT 5");
$pending_users = $pending_stmt->fetchAll(PDO::FETCH_ASSOC);

// System-wide Activity (Combined Users and Books)
try {
    $activity_query = "
        (SELECT 
            created_at, 
            'user_registration' as type, 
            first_name, 
            last_name, 
            NULL as title, 
            NULL as category,
            NULL as admin_name,
            NULL as admin_role
        FROM users)
        UNION ALL
        (SELECT 
            b.created_at, 
            'book_addition' as type, 
            u.first_name, 
            u.last_name, 
            b.title, 
            b.category,
            u.username as admin_name,
            u.role as admin_role
        FROM books b
        LEFT JOIN users u ON b.added_by = u.user_id)
        ORDER BY created_at DESC 
        LIMIT 10
    ";
    $activity_stmt = $pdo->query($activity_query);
    $recent_activities = $activity_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_activities = [];
}

function getTimeAgo($timestamp)
{
    $time = strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 0) return "Just now"; // Handle future timestamps
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
    <title>Admin Dashboard | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../src/css/dashboard.css">
    <style>
        /* Admin specific overrides if needed */
        :root {
            --primary-color: #1E3A8A;
            /* Deep Blue for Admin 
            --primary-dark: #1e1b4b;
            */
        }
    </style>
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
            <a href="sidebar/manage_books.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Manage Books</span>
            </a>
            <a href="sidebar/manage_user.php" class="menu-item">
                <i class="fas fa-users-cog"></i>
                <span>Manage Users</span>
            </a>
            <a href="sidebar/circulation.php" class="menu-item">
                <i class="fas fa-id-card"></i>
                <span>Circulation</span>
            </a>
            <a href="sidebar/reports&analysis.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Reports & Analytics</span>
            </a>
            <a href="sidebar/system_settings.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../../../auth/logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Header -->
        <header class="top-header animate-fade">
            <div class="header-user" style="margin-left: auto;">
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
            <div class="welcome-section animate-up delay-1">
                <h1>Welcome Back, Administrator</h1>
                <p>Monitor and manage all library operations from your command center.</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card animate-up delay-2">
                    <div class="stat-details">
                        <h3>Total Books</h3>
                        <span class="number"><?php echo number_format($total_books); ?></span>
                    </div>
                    <div class="stat-icon icon-books">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-3">
                    <div class="stat-details">
                        <h3>Registered Users</h3>
                        <span class="number"><?php echo number_format($registered_users); ?></span>
                    </div>
                    <div class="stat-icon icon-users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-4">
                    <div class="stat-details">
                        <h3>Transactions</h3>
                        <span class="number"><?php echo number_format($transactions); ?></span>
                    </div>
                    <div class="stat-icon icon-borrow">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <div class="stat-card animate-up delay-5">
                    <div class="stat-details">
                        <h3>Overdue Books</h3>
                        <span class="number"><?php echo number_format($overdue_books); ?></span>
                    </div>
                    <div class="stat-icon icon-overdue">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>System-wide Activity</h2>
                        <a href="#" class="view-all">View Audit Logs</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($recent_activities)): ?>
                            <div class="activity-item">No recent activity.</div>
                        <?php else: ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item">
                                    <?php if ($activity['type'] === 'user_registration'):
                                        $name = decryptionData($activity['first_name']) . " " . decryptionData($activity['last_name']);
                                    ?>
                                        <div class="activity-img">
                                            <i class="fas fa-user-plus" style="color: #3b82f6;"></i>
                                        </div>
                                        <div class="activity-info">
                                            <span class="activity-title">New User Registered</span>
                                            <span class="activity-desc"><strong><?php echo $name; ?></strong> completed registration</span>
                                        </div>
                                    <?php else:
                                        if ($activity['admin_role'] === 'Librarian') {
                                            $admin_display = decryptionData($activity['first_name']) . " " . decryptionData($activity['last_name']);
                                        } else {
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
                                                (title: <?php echo htmlspecialchars($activity['title']); ?>),
                                                (category: <?php echo htmlspecialchars($activity['category']); ?>)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="activity-time"><?php echo getTimeAgo($activity['created_at']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="data-card animate-up delay-6">
                    <div class="card-header">
                        <h2>Pending Approvals</h2>
                        <a href="#" class="view-all">Manage All</a>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($pending_users)): ?>
                            <div class="activity-item">No pending approvals.</div>
                        <?php else: ?>
                            <?php foreach ($pending_users as $user):
                                $fname = decryptionData($user['first_name']);
                                $lname = decryptionData($user['last_name']);
                                $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                                $role = $user['role'] ?? $user['userRole'] ?? 'Student';
                            ?>
                                <div class="activity-item">
                                    <div class="user-avatar" style="background: #f59e0b;"><?php echo $initials; ?></div>
                                    <div class="activity-info">
                                        <span class="activity-title"><?php echo $fname . " " . $lname; ?></span>
                                        <span class="activity-desc">Role: <?php echo $role; ?></span>
                                    </div>
                                    <span class="badge badge-pending">Pending</span>
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