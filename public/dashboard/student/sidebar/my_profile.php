<?php
require_once __DIR__ . '/../backend/process_my_profile.php';
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
    <link rel="stylesheet" href="../src/css/my_profile.css">
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
            <div class="header-user" style="margin-left: auto;">
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
    <script src="../src/js/my_profile.js"></script>
</body>

</html>