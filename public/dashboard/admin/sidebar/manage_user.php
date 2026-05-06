<?php
session_start();

require_once '../../../../database/db_connection.php';
require_once '../../../../helpers/cryptography_process.php';
require_once '../../../../config/smtp_config.php';
require_once '../../../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Authentication check
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: ../../../../public/loginAs.php");
//     exit();
// }

$success_msg = "";
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];

        if ($action === 'delete') {
            try {
                $delete_query = "DELETE FROM users WHERE user_id = ?";
                $delete_stmt = $pdo->prepare($delete_query);
                $delete_stmt->execute([$user_id]);
                $success_msg = "User account deleted successfully.";
            } catch (PDOException $e) {
                $error_msg = "Failed to delete user: " . $e->getMessage();
            }
        } else {
            // Fetch user data for email
            $user_query = "SELECT * FROM users WHERE user_id = ?";
            $user_stmt = $pdo->prepare($user_query);
            $user_stmt->execute([$user_id]);
            $target_user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            if ($target_user) {
                $user_email = decryptionData($target_user['email']);
                $user_name = decryptionData($target_user['username']);
                $first_name = decryptionData($target_user['first_name']);
                $last_name = decryptionData($target_user['last_name']);

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = SMTP_HOST;
                    $mail->SMTPAuth   = true;
                    $mail->Username   = SMTP_USER;
                    $mail->Password   = SMTP_PASS;
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = SMTP_PORT;

                    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                    $mail->addAddress($user_email, $first_name . " " . $last_name);

                    $mail->isHTML(true);

                    if ($action === 'approve') {
                        $new_role = $_POST['role'] ?? 'Student';
                        $update_query = "UPDATE users SET approval_status = 'Approved', role = ? WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$new_role, $user_id]);

                        $mail->Subject = 'Account Approved - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account (User ID: <b>$user_id</b>, Username: <b>$user_name</b>) was approved by the administrator.<br>You can now login to the system.<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User approved and notified via email.";
                    } elseif ($action === 'reject') {
                        $reason = $_POST['reason'] ?? 'No reason provided.';
                        $update_query = "UPDATE users SET approval_status = 'Rejected' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Registration Update - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account registration was rejected by the administration.<br><b>Reason:</b> $reason<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User rejected and notified via email.";
                    } elseif ($action === 'deactivate') {
                        $reason = $_POST['reason'] ?? 'No reason provided.';
                        $update_query = "UPDATE users SET approval_status = 'Inactive' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Deactivated - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account (Username: <b>$user_name</b>) was deactivated by the administration.<br><b>Reason:</b> $reason<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User deactivated and notified via email.";
                    } elseif ($action === 'activate') {
                        $update_query = "UPDATE users SET approval_status = 'Approved' WHERE user_id = ?";
                        $update_stmt = $pdo->prepare($update_query);
                        $update_stmt->execute([$user_id]);

                        $mail->Subject = 'Account Reactivated - LibroTech';
                        $mail->Body    = "Hello $first_name,<br><br>Your account (Username: <b>$user_name</b>) has been reactivated by the administration.<br>You can now login to the system again.<br><br>Best regards,<br>LibroTech Administration";

                        $success_msg = "User reactivated and notified via email.";
                    }
                    $mail->send();
                } catch (Exception $e) {
                    $error_msg = "Action completed, but email failed: " . $mail->ErrorInfo;
                }
            } else {
                $error_msg = "User not found.";
            }
        }
    }
}

// Fetch all users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pending_count = 0;
foreach ($all_users as $u) {
    if ($u['approval_status'] === 'Pending') $pending_count++;
}
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
    <style>
        /* Specific styles for Manage Users page */
        .management-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .breadcrumb {
            display: flex;
            gap: 10px;
            font-size: 14px;
            color: var(--text-lighter);
            margin-bottom: 5px;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        /* Tabbed Interface */
        .user-tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 25px;
            background: #e2e8f0;
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
            color: var(--text-lighter);
            cursor: pointer;
            transition: var(--transition);
        }

        .tab-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: var(--shadow-sm);
        }

        .tab-btn:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
        }

        /* Users Table */
        .users-table-container {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
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
            color: var(--text-lighter);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table td {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
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
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        .user-name {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-email {
            display: block;
            font-size: 12px;
            color: var(--text-lighter);
        }

        .role-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-librarian {
            background: #fff7ed;
            color: #c2410c;
        }

        .role-student {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .role-admin {
            background: #f5f3ff;
            color: #6d28d9;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-approve {
            background: #10b981;
            color: white;
        }

        .btn-reject {
            background: #ef4444;
            color: white;
        }

        .btn-manage {
            background: #f1f5f9;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .badge-inactive {
            background: #f1f5f9;
            color: #64748b;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #ef4444;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            filter: brightness(0.9);
        }

        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bdf0cc;
        }

        .alert-error {
            background: #fee2e2;
            color: #ef4444;
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
                                                    <button type="submit" name="action" value="delete" class="action-btn" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to permanently delete this account?')">Delete</button>
                                                </div>
                                            <?php elseif ($status === 'Inactive'): ?>
                                                <div class="actions">
                                                    <button type="submit" name="action" value="activate" class="action-btn" style="background: var(--primary-color); color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;">Activate</button>
                                                    <button type="submit" name="action" value="delete" class="action-btn" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to permanently delete this account?')">Delete</button>
                                                </div>
                                            <?php elseif ($status === 'Rejected'): ?>
                                                <div class="actions">
                                                    <button type="submit" name="action" value="delete" class="action-btn" style="background: #ef4444; color: white; border-radius: 6px; padding: 5px 10px; font-size: 11px; width: auto; border: none; cursor: pointer;" onclick="return confirm('Are you sure you want to permanently delete this account?')">Delete Account</button>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const filter = btn.getAttribute('data-filter');
                if (!filter) return; // Skip if it's the badge-only part or something

                // Update active tab
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                // Filter rows
                document.querySelectorAll('.user-row').forEach(row => {
                    const role = row.getAttribute('data-role');
                    const status = row.getAttribute('data-status');

                    if (filter === 'all') {
                        row.style.display = 'table-row';
                    } else if (filter === 'Pending') {
                        row.style.display = (status === 'Pending') ? 'table-row' : 'none';
                    } else if (filter === 'Librarian' || filter === 'Student') {
                        row.style.display = (role === filter && status === 'Approved') ? 'table-row' : 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>