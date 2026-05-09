<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../studentLogin.php");
    exit();
}

include '../../../../database/db_connection.php';
include '../../../../helpers/cryptography_process.php';

$student_id = $_SESSION['user_id'];
$update_success = false;
$update_error = "";

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([
            encryptionData($first_name),
            encryptionData($last_name),
            encryptionData($username),
            encryptionData($email),
            $student_id
        ]);
        $update_success = true;
    } catch (PDOException $e) {
        $update_error = "Error updating profile: " . $e->getMessage();
    }
}

// Get unread notification count for sidebar
$unread_count = 0;
try {
    $user_stmt = $pdo->prepare("SELECT last_notif_view, first_name, last_name, username, email FROM users WHERE user_id = ?");
    $user_stmt->execute([$student_id]);
    $student_data = $user_stmt->fetch(PDO::FETCH_ASSOC);

    $last_view = $student_data['last_notif_view'] ?: '1970-01-01 00:00:00';

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

    // Fetch Profile Stats
    // 1. Returned
    $returned_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status = 'returned'");
    $returned_stmt->execute([$student_id]);
    $returned_count = $returned_stmt->fetchColumn();

    // 2. Active
    $active_stmt = $pdo->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND status IN ('borrowed', 'overdue')");
    $active_stmt->execute([$student_id]);
    $active_count = $active_stmt->fetchColumn();

    $full_name = decryptionData($student_data['first_name']) . " " . decryptionData($student_data['last_name']);
    $initials = strtoupper(substr(decryptionData($student_data['first_name']), 0, 1) . substr(decryptionData($student_data['last_name']), 0, 1));
    $decrypted_username = decryptionData($student_data['username']);
    $decrypted_email = decryptionData($student_data['email']);
} catch (PDOException $e) {
    $unread_count = 0;
    $returned_count = $active_count = 0;
    $full_name = "Student User";
    $initials = "ST";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | LibroTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/student_dashboard.css">
    <style>
        /* Specific styles for My Profile (Split Layout) */
        .profile-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
            align-items: start;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-light);
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            font-size: 3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .profile-card h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .profile-card p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 5px 0 20px;
        }

        .profile-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-light);
        }

        .stat-box h4 {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin: 0;
        }

        .stat-box span {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .details-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-light);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-light);
        }

        .section-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .form-group input {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-light);
            outline: none;
            background: #f8fafc;
            color: var(--text-dark);
            font-family: inherit;
        }

        .btn-save {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-save:hover {
            filter: brightness(0.9);
            transform: translateY(-1px);
        }

        /* Plain Data Display */
        .plain-data {
            font-size: 1rem;
            color: var(--text-dark);
            padding: 5px 0;
            font-weight: 500;
        }

        .edit-icon-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s;
            padding: 5px;
        }

        .edit-icon-btn:hover {
            color: var(--primary-color);
        }

        /* Modal Styles (Mirroring Professional Admin Theme) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 35px;
            border-radius: 16px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            animation: modalSlide 0.3s ease-out;
        }

        @keyframes modalSlide {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            margin: 0;
        }

        .close-modal {
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }

        .close-modal:hover {
            color: #ef4444;
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
            <a href="search_catalog.php" class="menu-item">
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
            <a href="my_profile.php" class="menu-item active">
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
                <input type="text" placeholder="Search profile settings...">
            </div>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                    <span class="user-role">Student</span>
                </div>
                <div class="user-avatar"><?php echo $initials; ?></div>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="animate-up delay-1" style="margin-bottom: 25px;">
                <div class="breadcrumb" style="font-size: 13px; color: var(--text-muted); margin-bottom: 5px;">Student / My Profile</div>
                <h1>Profile Settings</h1>
                <?php if ($update_success): ?>
                    <div style="background: #ecfdf5; color: #10b981; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle"></i> Profile updated successfully!
                    </div>
                <?php elseif ($update_error): ?>
                    <div style="background: #fef2f2; color: #ef4444; padding: 10px 15px; border-radius: 8px; margin-top: 15px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $update_error; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="profile-grid animate-up delay-2">
                <!-- Left: Profile Card -->
                <div class="profile-card">
                    <div class="profile-avatar-large"><?php echo $initials; ?></div>
                    <h2><?php echo htmlspecialchars($full_name); ?></h2>
                    <p>University Student</p>
                    <div class="profile-stats">
                        <div class="stat-box">
                            <h4><?php echo $returned_count; ?></h4>
                            <span>Returned</span>
                        </div>
                        <div class="stat-box" style="border-left: 1px solid var(--border-light); padding-left: 20px;">
                            <h4><?php echo $active_count; ?></h4>
                            <span>Active Borrows</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Details Card -->
                <div class="details-card">
                    <div class="section-header">
                        <h3>Personal Information</h3>
                        <button class="edit-icon-btn" onclick="openModal()">
                            <i class="fas fa-user-edit"></i>
                        </button>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <div class="plain-data"><?php echo htmlspecialchars($full_name); ?></div>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <div class="plain-data"><?php echo htmlspecialchars($decrypted_username); ?></div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="plain-data"><?php echo htmlspecialchars($decrypted_email); ?></div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <div class="plain-data">Student</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="my_profile.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars(decryptionData($student_data['first_name'])); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars(decryptionData($student_data['last_name'])); ?>" required>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($decrypted_username); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($decrypted_email); ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Role</label>
                    <input type="text" value="Student" readonly style="background: #f1f5f9; cursor: not-allowed;">
                </div>
                <div style="text-align: right; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn-save" style="background: #f1f5f9; color: var(--text-dark);" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="update_profile" class="btn-save">Update Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        function openModal() {
            document.getElementById('editProfileModal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('editProfileModal').classList.remove('active');
        }
        // Close modal when clicking outside
        window.onclick = function(event) {
            let modal = document.getElementById('editProfileModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>

</html>