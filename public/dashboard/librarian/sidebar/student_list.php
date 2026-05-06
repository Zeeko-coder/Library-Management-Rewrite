<?php
session_start();
require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Librarian') {
    header("Location: ../../../../loginAs.php");
    exit();
}

$success_message = $_SESSION['success_message'] ?? "";
$error_message = $_SESSION['error_message'] ?? "";
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Handle Request Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $borrow_id = $_POST['borrow_id'];
        $action = $_POST['action'];

        try {
            if ($action === 'approve_request') {
                $days = (int)$_POST['duration'];
                $due_date = date('Y-m-d', strtotime("+$days days"));
                
                $stmt = $pdo->prepare("UPDATE borrowings SET status = 'borrowed', borrow_date = CURRENT_DATE, due_date = ? WHERE id = ?");
                $stmt->execute([$due_date, $borrow_id]);
                $_SESSION['success_message'] = "Borrow request approved for $days days.";
            } elseif ($action === 'reject_request') {
                $stmt = $pdo->prepare("UPDATE borrowings SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$borrow_id]);
                $_SESSION['success_message'] = "Borrow request rejected.";
            }
            header("Location: student_list.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Action failed: " . $e->getMessage();
        }
    }
}

// Fetch Students
try {
    // 1. All Students
    $students_stmt = $pdo->prepare("
        SELECT u.*, 
        (SELECT COUNT(*) FROM borrowings WHERE user_id = u.user_id AND status = 'borrowed') as borrowed_count
        FROM users u 
        WHERE u.role = 'Student'
        ORDER BY u.created_at DESC
    ");
    $students_stmt->execute();
    $all_students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Borrow Requests (Pending)
    $requests_stmt = $pdo->prepare("
        SELECT b.*, bk.title, u.first_name, u.last_name, u.username as id_num
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.status = 'pending'
        ORDER BY b.created_at DESC
    ");
    $requests_stmt->execute();
    $borrow_requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Overdue Students
    $overdue_stmt = $pdo->prepare("
        SELECT b.*, bk.title, u.first_name, u.last_name, u.username as id_num
        FROM borrowings b
        JOIN books bk ON b.book_id = bk.book_id
        JOIN users u ON b.user_id = u.user_id
        WHERE (b.status = 'overdue') OR (b.status = 'borrowed' AND b.due_date < CURRENT_DATE)
        ORDER BY b.due_date ASC
    ");
    $overdue_stmt->execute();
    $overdue_list = $overdue_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tab counts
    $request_count = count($borrow_requests);
    $overdue_count = count($overdue_list);

} catch (PDOException $e) {
    $all_students = $borrow_requests = $overdue_list = [];
    $request_count = $overdue_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/librarian_dashboard.css">
    <style>
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .member-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
            background: #f1f5f9;
            padding: 5px;
            border-radius: 12px;
            width: fit-content;
        }

        .tab-btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: none;
            background: none;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .tab-btn.active {
            background: white;
            color: #3b82f6;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .users-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .users-table th {
            background: #f8fafc;
            padding: 15px 20px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-light);
        }

        .users-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-light);
            font-size: 14px;
        }

        .user-identity {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-pfp {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-manage { background: #f1f5f9; color: var(--text-dark); border: 1px solid var(--border-light); }

        .duration-select {
            padding: 6px;
            border-radius: 6px;
            border: 1px solid var(--border-light);
            font-size: 12px;
            font-family: inherit;
            outline: none;
        }

        .badge-count {
            background: #ef4444;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
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
            <a href="../dashboard_librarian.php" class="menu-item">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="book_cataloging.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Book Cataloging</span>
            </a>
            <a href="circulation.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span>Circulation</span>
            </a>
            <a href="student_list.php" class="menu-item active">
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
                <input type="text" placeholder="Search students by name or ID...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Librarian'; ?></span>
                    <span class="user-role">Librarian</span>
                </div>
                <div class="user-avatar">
                    <?php echo isset($_SESSION['username']) ? strtoupper(substr($_SESSION['username'], 0, 2)) : 'LB'; ?>
                </div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="management-header animate-up delay-1">
                <div>
                    <div class="breadcrumb">Librarian / Student List</div>
                    <h1 id="pageTitle">Student Directory</h1>
                </div>
            </div>

            <!-- Custom Tabs -->
            <div class="member-tabs animate-up delay-2">
                <button class="tab-btn active" data-tab="all">All Students</button>
                <button class="tab-btn" data-tab="requests">
                    Borrow Book Request
                    <?php if ($request_count > 0): ?>
                        <span class="badge-count"><?php echo $request_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" data-tab="overdue">
                    Overdue Students
                    <?php if ($overdue_count > 0): ?>
                        <span class="badge-count"><?php echo $overdue_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success animate-fade" style="margin-bottom: 20px; padding: 12px 20px; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Tab Content: All Students -->
            <div id="allTab" class="tab-content animate-up delay-3">
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Student Member</th>
                                <th>ID Number</th>
                                <th>Status</th>
                                <th>Borrowed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_students as $student): 
                                $fname = decryptionData($student['first_name']);
                                $lname = decryptionData($student['last_name']);
                                $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                            ?>
                                <tr>
                                    <td>
                                        <div class="user-identity">
                                            <div class="user-pfp" style="background: #3b82f6;"><?php echo $initials; ?></div>
                                            <div>
                                                <span class="user-name" style="font-weight: 600;"><?php echo $fname . " " . $lname; ?></span>
                                                <span class="user-email" style="font-size: 12px; color: var(--text-muted);"><?php echo decryptionData($student['email']); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo $student['user_id']; ?></td>
                                    <td>
                                        <span style="background: #f0fdf4; color: #15803d; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                            <?php echo $student['approval_status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $student['borrowed_count']; ?> Books</td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-manage">View Profile</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Borrow Requests -->
            <div id="requestsTab" class="tab-content animate-up delay-3" style="display: none;">
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book Title</th>
                                <th>Request Date</th>
                                <th>Set Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($borrow_requests)): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">No pending borrow requests.</td></tr>
                            <?php else: ?>
                                <?php foreach ($borrow_requests as $req): 
                                    $name = decryptionData($req['first_name']) . " " . decryptionData($req['last_name']);
                                ?>
                                    <tr>
                                        <td><strong><?php echo $name; ?></strong><br><small>ID: <?php echo $req['user_id']; ?></small></td>
                                        <td><?php echo htmlspecialchars($req['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" id="form-<?php echo $req['id']; ?>" style="display: flex; align-items: center; gap: 10px;">
                                                <input type="hidden" name="borrow_id" value="<?php echo $req['id']; ?>">
                                                <select name="duration" class="duration-select">
                                                    <option value="3">3 Days</option>
                                                    <option value="7" selected>7 Days</option>
                                                    <option value="14">14 Days</option>
                                                    <option value="30">30 Days</option>
                                                </select>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button type="submit" name="action" value="approve_request" class="btn-action btn-approve"><i class="fas fa-check"></i> Approve</button>
                                                <button type="submit" name="action" value="reject_request" class="btn-action btn-reject"><i class="fas fa-times"></i> Reject</button>
                                            </div>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Overdue Students -->
            <div id="overdueTab" class="tab-content animate-up delay-3" style="display: none;">
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Book Title</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($overdue_list)): ?>
                                <tr><td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">No overdue students found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($overdue_list as $od): 
                                    $name = decryptionData($od['first_name']) . " " . decryptionData($od['last_name']);
                                ?>
                                    <tr>
                                        <td><strong><?php echo $name; ?></strong><br><small>ID: <?php echo $od['user_id']; ?></small></td>
                                        <td><?php echo htmlspecialchars($od['title']); ?></td>
                                        <td><span style="color: #ef4444; font-weight: 600;"><?php echo date('M d, Y', strtotime($od['due_date'])); ?></span></td>
                                        <td><span class="status-badge" style="background: #fef2f2; color: #991b1b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Overdue</span></td>
                                        <td>
                                            <div class="actions">
                                                <button class="btn-action btn-manage">Notify Student</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');
            const pageTitle = document.getElementById('pageTitle');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.getAttribute('data-tab');
                    
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    contents.forEach(c => c.style.display = 'none');
                    document.getElementById(target + 'Tab').style.display = 'block';

                    // Update Title
                    if (target === 'all') pageTitle.innerText = 'Student Directory';
                    else if (target === 'requests') pageTitle.innerText = 'Borrow Requests';
                    else if (target === 'overdue') pageTitle.innerText = 'Overdue Monitoring';
                });
            });

            // Simple Search
            const searchInput = document.querySelector('.header-search input');
            searchInput.addEventListener('input', function() {
                const term = this.value.toLowerCase();
                const rows = document.querySelectorAll('.tab-content:not([style*="display: none"]) .users-table tbody tr');
                rows.forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            });
        });
    </script>
</body>

</html>
