<?php
require_once __DIR__ . '/../backend/process_system_settings.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | LibroTech Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../../../src/css/styles.css">
    <link rel="stylesheet" href="../../../../src/css/dashboard.css">
    <link rel="stylesheet" href="../src/css/system_settings.css">
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
            <a href="manage_user.php" class="menu-item">
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
            <a href="system_settings.php" class="menu-item active">
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
            <button id="sidebarToggle" class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="header-user" style="margin-left: auto;">
                <div class="user-info">
                    <span class="user-name">System Admin</span>
                    <span class="user-role">Administrator</span>
                </div>
                <div class="user-avatar">AD</div>
            </div>
        </header>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #22c55e;">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success'];
                                                        unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; border-left: 4px solid #ef4444;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error'];
                                                                unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="welcome-section animate-up delay-1">
                <h1>System Settings</h1>
                <p>Configure library policies, global defaults, and system preferences.</p>
            </div>

            <div class="settings-single animate-up delay-3">
                <div class="settings-display-card">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
                        <h2>General Library Profile</h2>
                        <button class="btn-edit-profile" id="openEditModal" title="Edit Profile">
                            <i class="fas fa-edit"></i> Edit Profile
                        </button>
                    </div>

                    <div class="profile-display-content">
                        <div class="profile-main-info">
                            <div class="profile-avatar-large">
                                <i class="fas fa-university"></i>
                            </div>
                            <h1 class="display-library-name"><?php echo htmlspecialchars(getSetting('library_name', 'LibroTech Library')); ?></h1>
                            <p class="display-library-address"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(getSetting('library_address', 'Main Campus, Library Building')); ?></p>
                        </div>

                        <div class="profile-details-grid">
                            <div class="detail-item">
                                <label>Official Email</label>
                                <span><?php echo htmlspecialchars(getSetting('library_email', 'library@librotech.edu')); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Contact Hotline</label>
                                <span><?php echo htmlspecialchars(getSetting('library_phone', '+1 (234) 567-890')); ?></span>
                            </div>
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
                <h2>Edit Library Profile</h2>
                <span class="close-modal">&times;</span>
            </div>
            <form action="system_settings.php" method="POST" class="modal-form">
                <div class="form-group">
                    <label for="library_name">Library Display Name</label>
                    <input type="text" id="library_name" name="library_name" value="<?php echo htmlspecialchars(getSetting('library_name')); ?>" required>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="library_email">Official Email</label>
                        <input type="email" id="library_email" name="library_email" value="<?php echo htmlspecialchars(getSetting('library_email')); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="library_phone">Contact Hotline</label>
                        <input type="text" id="library_phone" name="library_phone" value="<?php echo htmlspecialchars(getSetting('library_phone')); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="library_address">Campus Location / Address</label>
                    <textarea id="library_address" name="library_address" rows="3" required><?php echo htmlspecialchars(getSetting('library_address')); ?></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" name="save_settings" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../../src/js/dashboard.js"></script>
    <script src="../src/js/system_settings.js"></script>
</body>

</html>