<?php
require_once __DIR__ . '/../backend/process_my_barrowed.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Borrowed Books | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <link rel="stylesheet" href="../src/css/my_barrowed.css">
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
            <a href="search_catalog.php" class="menu-item">
                <i class="fas fa-search"></i>
                <span>Search Catalog</span>
            </a>
            <a href="my_barrowed.php" class="menu-item active">
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
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / My Borrowed</div>
                <h1>My Borrowed History</h1>
                <?php if ($success_message): ?>
                    <div style="background: #ecfdf5; color: #10b981; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div style="background: #fef2f2; color: #ef4444; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Active Borrows Table -->
            <div class="loans-container animate-up delay-3">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Active Borrows</h3>
                </div>
                <div class="loans-table-wrapper">
                    <table class="loans-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Borrow Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($active_borrows)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">No active borrows found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($active_borrows as $loan):
                                    $is_overdue = (strtolower($loan['status']) === 'overdue' || strtotime($loan['due_date']) < time());
                                    $due_time = strtotime($loan['due_date']);
                                    $diff = $due_time - time();
                                    $days_left = ceil($diff / (60 * 60 * 24));
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($loan['title']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($loan['author']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></td>
                                        <td><strong <?php echo $is_overdue ? 'style="color: #ef4444;"' : ''; ?>><?php echo date('M d, Y', $due_time); ?></strong></td>
                                        <td>
                                            <?php if ($is_overdue): ?>
                                                <span class="status-badge overdue-loan"><i class="fas fa-exclamation-triangle"></i> Overdue</span>
                                            <?php else: ?>
                                                <span class="status-badge active-loan"><i class="fas fa-clock"></i> <?php echo $days_left; ?> Days Left</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button type="button" class="btn-action btn-approve open-return-modal"
                                                    data-id="<?php echo $loan['id']; ?>"
                                                    data-title="<?php echo htmlspecialchars($loan['title']); ?>"
                                                    style="padding: 6px 12px; font-size: 12px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                                    <i class="fas fa-undo"></i> Return
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent History -->
            <div class="loans-container animate-up delay-4" style="margin-top: 30px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-light);">
                    <h3 style="font-size: 16px;">Recently Returned</h3>
                </div>
                <div class="loans-table-wrapper">
                    <table class="loans-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Return Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($returned_loans)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No return history found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($returned_loans as $r_loan): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r_loan['title']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($r_loan['return_date'])); ?></td>
                                        <td><span class="status-badge returned-loan"><i class="fas fa-check-circle"></i> Returned</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Return Confirmation Modal -->
        <div id="returnModal" class="modal">
            <div class="modal-content" style="max-width: 400px; text-align: center;">
                <div class="modal-header" style="justify-content: center; position: relative;">
                    <h2 style="margin: 0; font-size: 1.2rem;">Confirm Return</h2>
                    <span class="close-modal" style="position: absolute; right: 20px; cursor: pointer;">&times;</span>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <div style="font-size: 50px; color: #3b82f6; margin-bottom: 20px;">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <p style="font-size: 1rem; color: var(--text-dark); line-height: 1.5;">Are you sure you want to return <br><strong id="returnBookTitle" style="color: var(--primary-color);"></strong> today?</p>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 15px;">
                        <i class="fas fa-info-circle"></i> This will send a return notification to the librarian.
                    </p>
                </div>
                <form action="my_barrowed.php" method="POST">
                    <input type="hidden" name="borrow_id" id="returnBorrowId">
                    <input type="hidden" name="action" value="return_book">
                    <div class="modal-footer" style="justify-content: center; gap: 12px; background: white; border-top: none; padding: 0 30px 35px;">
                        <button type="button" class="btn btn-secondary close-modal" style="background: #f1f5f9; color: #64748b; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">Cancel</button>
                        <button type="submit" name="confirm_return" class="btn btn-primary" style="background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">Yes, Return</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/my_barrowed.js"></script>
</body>

</html>