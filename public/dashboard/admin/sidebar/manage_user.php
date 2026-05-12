<?php
require_once __DIR__ . '/../backend/process_manage_user.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <link rel="stylesheet" href="../src/css/manage_user.css">
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
            <a href="manage_books.php" class="menu-item">
                <i class="fas fa-book"></i>
                <span>Manage Books</span>
            </a>
            <a href="manage_user.php" class="menu-item active">
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
                <input type="text" placeholder="Search for users by name or email...">
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
                        <span>Manage Users</span>
                    </div>
                    <h1>User Management</h1>
                </div>
            </div>

            <?php if ($success_msg): ?>
                <div class="alert alert-success animate-fade"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-error animate-fade"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <!-- Role Tabs -->
            <div class="user-tabs animate-up delay-2">
                <button class="tab-btn active" data-filter="all">All Users</button>
                <button class="tab-btn" data-filter="Librarian">Librarians</button>
                <button class="tab-btn" data-filter="Student">Students</button>
                <button class="tab-btn" style="position: relative;" data-filter="Pending">
                    Pending Approvals
                    <?php if ($pending_count > 0): ?>
                        <span style="position: absolute; top: -5px; right: -10px; background: #ef4444; color: white; font-size: 10px; padding: 2px 6px; border-radius: 10px;"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </button>
            </div>

            <!-- Users Table -->
            <div class="users-table-container animate-up delay-3">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User Identity</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Reg. Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($all_users as $user):
                            $fname = decryptionData($user['first_name']);
                            $lname = decryptionData($user['last_name']);
                            $email = decryptionData($user['email']);
                            $role = $user['role'] ?? 'Student';
                            $status = $user['approval_status'] ?? 'Pending';
                            $pfp_initials = strtoupper(substr($fname, 0, 1) . substr($lname, 0, 1));
                        ?>
                            <tr class="user-row" data-role="<?php echo $role; ?>" data-status="<?php echo $status; ?>">
                                <td>
                                    <div class="user-identity">
                                        <div class="user-pfp"><?php echo $pfp_initials; ?></div>
                                        <div>
                                            <span class="user-name"><?php echo $fname . " " . $lname; ?></span>
                                            <span class="user-email"><?php echo $email; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($status === 'Pending'): ?>
                                        <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="role" style="padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); font-size: 12px; font-family: inherit;">
                                                <option value="Student" <?php echo ($role === 'Student') ? 'selected' : ''; ?>>Student</option>
                                                <option value="Librarian" <?php echo ($role === 'Librarian') ? 'selected' : ''; ?>>Librarian</option>
                                            </select>
                                        <?php else: ?>
                                            <span class="role-pill role-<?php echo strtolower($role); ?>"><?php echo $role; ?></span>
                                        <?php endif; ?>
                                </td>
                                <td><span class="badge badge-<?php echo strtolower($status); ?>"><?php echo $status; ?></span></td>
                                <td><?php echo date("M d, Y", strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($status === 'Pending'): ?>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <input type="text" name="reason" placeholder="Reject reason..." style="font-size: 11px; padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); width: 120px;">
                                            <div class="actions">
                                                <button type="submit" name="action" value="approve" class="action-btn btn-approve" title="Approve"><i class="fas fa-check"></i></button>
                                                <button type="submit" name="action" value="reject" class="action-btn btn-reject" title="Reject"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" style="display: flex; gap: 8px; align-items: center;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <?php if ($status === 'Approved'): ?>
                                                <input type="text" name="reason" placeholder="Deactivation reason..." style="font-size: 11px; padding: 6px; border-radius: 6px; border: 1px solid var(--border-color); width: 120px;">
                                                <div class="actions">
                                                    <button type="submit" name="action" value="deactivate" class="action-btn" style="background: #94a3b8; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Deactivate</button>
                                                    <button type="button" class="action-btn open-delete-modal" data-id="<?php echo $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($fname . " " . $lname); ?>" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Delete</button>
                                                </div>
                                            <?php elseif ($status === 'Inactive'): ?>
                                                <div class="actions">
                                                    <button type="submit" name="action" value="activate" class="action-btn" style="background: var(--primary-color); color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Activate</button>
                                                    <button type="button" class="action-btn open-delete-modal" data-id="<?php echo $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($fname . " " . $lname); ?>" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Delete</button>
                                                </div>
                                            <?php elseif ($status === 'Rejected'): ?>
                                                <div class="actions">
                                                    <button type="button" class="action-btn open-delete-modal" data-id="<?php echo $user['user_id']; ?>" data-name="<?php echo htmlspecialchars($fname . " " . $lname); ?>" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Delete Account</button>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="emptyStateRow" style="display: none;">
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                <span id="emptyStateMessage">No users found matching this filter.</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="modal-content" style="background: white; margin: 15vh auto; padding: 0; border-radius: 16px; width: 90%; max-width: 400px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); overflow: hidden; text-align: center;">
            <div class="modal-header" style="padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: center; position: relative;">
                <h2 style="margin: 0; font-size: 1.2rem; color: var(--text-dark);">Confirm Delete</h2>
                <span class="close-modal" style="position: absolute; right: 20px; top: 18px; font-size: 24px; color: #94a3b8; cursor: pointer;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div style="font-size: 50px; color: #ef4444; margin-bottom: 20px;">
                    <i class="fas fa-user-times"></i>
                </div>
                <p style="font-size: 1rem; color: var(--text-dark); line-height: 1.5; margin-bottom: 10px;">Are you sure you want to permanently delete <br><strong id="deleteUserName" style="color: #ef4444;"></strong>'s account?</p>
                <p style="font-size: 13px; color: var(--text-muted);">
                    <i class="fas fa-exclamation-triangle"></i> This action is irreversible and will remove all user data.
                </p>
            </div>
            <form action="manage_user.php" method="POST">
                <input type="hidden" name="user_id" id="deleteUserId">
                <input type="hidden" name="action" value="delete">
                <div class="modal-footer" style="padding: 0 30px 35px; display: flex; justify-content: center; gap: 12px; background: white; border-top: none;">
                    <button type="submit" name="confirm_delete" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px;">Yes, Delete Account</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/manage_user.js"></script>
</body>

</html>

</html>