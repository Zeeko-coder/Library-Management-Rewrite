<?php
require_once __DIR__ . '/../backend/process_student_list.php';
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
    <link rel="stylesheet" href="../src/css/student_list.css">
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
            <a href="student_list.php" class="menu-item active">
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
                    <?php if ($new_request_count > 0): ?>
                        <span class="badge-count"><?php echo $new_request_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" data-tab="overdue">
                    Overdue Students
                    <?php if ($new_overdue_count > 0): ?>
                        <span class="badge-count"><?php echo $new_overdue_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" data-tab="active">
                    Active Borrow Students
                    <?php if ($new_active_count > 0): ?>
                        <span class="badge-count"><?php echo $new_active_count; ?></span>
                    <?php endif; ?>
                </button>
                <button class="tab-btn" data-tab="returned">
                    Returned Book
                    <?php if ($new_returned_count > 0): ?>
                        <span class="badge-count"><?php echo $new_returned_count; ?></span>
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
                                <th>Book title</th>
                                <th>Copies</th>
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
                                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($student['book_titles'] ?? 'None'); ?>
                                    </td>
                                    <td><?php echo $student['borrowed_copies'] ?? 0; ?> Copies</td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-action btn-manage"
                                                onclick="viewProfile({
                                                    name: '<?php echo htmlspecialchars($fname . " " . $lname); ?>',
                                                    username: '<?php echo htmlspecialchars(decryptionData($student['username'])); ?>',
                                                    email: '<?php echo htmlspecialchars(decryptionData($student['email'])); ?>',
                                                    book_titles: '<?php echo htmlspecialchars($student['book_titles'] ?? 'None'); ?>',
                                                    copies: '<?php echo $student['borrowed_copies'] ?? 0; ?>',
                                                    initials: '<?php echo $initials; ?>'
                                                })">
                                                View Profile
                                            </button>
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
                                 <th>Copies</th>
                                 <th>Set Duration</th>
                                 <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($borrow_requests)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">No pending borrow requests.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($borrow_requests as $req):
                                    $name = decryptionData($req['first_name']) . " " . decryptionData($req['last_name']);
                                ?>
                                    <tr>
                                        <td><strong><?php echo $name; ?></strong><br><small>ID: <?php echo $req['user_id']; ?></small></td>
                                         <td><?php echo htmlspecialchars($req['title']); ?></td>
                                         <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                         <td>
                                             <input type="number" name="quantity" value="<?php echo $req['quantity']; ?>" min="1" max="<?php echo $req['quantity'] + $req['stock']; ?>" form="form-<?php echo $req['id']; ?>" class="quantity-input">
                                             <br><small style="font-size: 10px; color: var(--text-muted);">Max: <?php echo $req['quantity'] + $req['stock']; ?></small>
                                         </td>
                                         <td>
                                             <select name="duration" form="form-<?php echo $req['id']; ?>" class="duration-select">
                                                 <option value="1 minute">1 Minute</option>
                                                 <option value="1 hour">1 Hour</option>
                                                 <option value="3 days">3 Days</option>
                                                 <option value="7 days" selected>7 Days</option>
                                                 <option value="14 days">14 Days</option>
                                                 <option value="30 days">30 Days</option>
                                             </select>
                                         </td>
                                         <td>
                                             <div class="actions">
                                                 <form method="POST" id="form-<?php echo $req['id']; ?>" style="display: flex; align-items: center; gap: 8px;">
                                                     <input type="hidden" name="borrow_id" value="<?php echo $req['id']; ?>">
                                                     <button type="submit" name="action" value="approve_request" class="btn-action btn-approve"><i class="fas fa-check"></i> Approve</button>
                                                     <button type="submit" name="action" value="reject_request" class="btn-action btn-reject"><i class="fas fa-times"></i> Reject</button>
                                                 </form>
                                             </div>
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
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">No overdue students found.</td>
                                </tr>
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
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="borrow_id" value="<?php echo $od['id']; ?>">
                                                    <button type="submit" name="action" value="notify_overdue" class="btn-action btn-manage">
                                                        <i class="fas fa-bell"></i> Notify Student
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Tab Content: Active Borrow Students -->
            <div id="activeTab" class="tab-content animate-up delay-3" style="display: none;">
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Student Member</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($active_borrow_list)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">No active borrow students found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($active_borrow_list as $active): 
                                    $fname = decryptionData($active['first_name']);
                                    $lname = decryptionData($active['last_name']);
                                    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                                ?>
                                    <tr>
                                        <td>
                                            <div class="user-identity">
                                                <div class="user-pfp" style="background: #3b82f6;"><?php echo $initials; ?></div>
                                                <div>
                                                    <span class="user-name" style="font-weight: 600;"><?php echo $fname . " " . $lname; ?></span>
                                                    <span class="user-email" style="font-size: 12px; color: var(--text-muted);"><?php echo decryptionData($active['email']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($active['title']); ?></td>
                                        <td><?php echo htmlspecialchars($active['author']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($active['borrow_date'])); ?></td>
                                        <td><span style="color: #3b82f6; font-weight: 600;"><?php echo date('M d, Y', strtotime($active['due_date'])); ?></span></td>
                                        <td><span class="status-badge" style="background: #eff6ff; color: #1e40af; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600;">Borrowed</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Content: Returned Books -->
            <div id="returnedTab" class="tab-content animate-up delay-3" style="display: none;">
                <div class="users-table-container">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Student Member</th>
                                <th>Book title</th>
                                <th>Copies</th>
                                <th>Return Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($returned_list)): ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No returned books found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($returned_list as $ret): 
                                    $fname = decryptionData($ret['first_name']);
                                    $lname = decryptionData($ret['last_name']);
                                    $initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                                ?>
                                    <tr>
                                        <td>
                                            <div class="user-identity">
                                                <div class="user-pfp" style="background: #10b981;"><?php echo $initials; ?></div>
                                                <div>
                                                    <span class="user-name" style="font-weight: 600;"><?php echo $fname . " " . $lname; ?></span>
                                                    <span class="user-email" style="font-size: 12px; color: var(--text-muted);"><?php echo decryptionData($ret['email']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($ret['title']); ?></td>
                                        <td><?php echo $ret['quantity']; ?> Copies</td>
                                        <td><?php echo date('M d, Y', strtotime($ret['return_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- View Profile Modal -->
    <div id="viewProfileModal" class="profile-modal">
        <div class="profile-modal-content">
            <div class="profile-modal-header">
                <span class="modal-close-btn" onclick="closeProfileModal()">&times;</span>
                <div id="modalAvatar" class="modal-avatar">ST</div>
                <h2 id="modalFullName" style="margin: 0;">Student Name</h2>
                <p id="modalUserRole" style="margin: 5px 0 0; opacity: 0.8; font-size: 0.9rem;">Student</p>
            </div>
            <div class="profile-modal-body">
                <div class="modal-info-row">
                    <label>Username / ID Number</label>
                    <p id="modalUsername">ST-2024-001</p>
                </div>
                <div class="modal-info-row">
                    <label>Email Address</label>
                    <p id="modalEmail">student@example.com</p>
                </div>
                <div class="modal-info-row">
                    <label>Book Title</label>
                    <p id="modalBookTitle">None</p>
                </div>
                <div class="modal-info-row">
                    <label>Number of Copies</label>
                    <p id="modalBookCopies">0</p>
                </div>
                <div class="modal-info-row">
                    <label>Member Role</label>
                    <p>Student</p>
                </div>
                <div style="margin-top: 25px; text-align: center;">
                    <button class="btn-action btn-manage" style="width: 100%; justify-content: center; padding: 12px;" onclick="closeProfileModal()">Close Profile</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/student_list.js"></script>
</body>

</html>