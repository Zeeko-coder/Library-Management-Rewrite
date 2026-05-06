<?php
session_start();

// Database connection
include '../../../../database/db_connection.php';

// 1. Handle Form Submission (Procedural)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $library_name = filter_input(INPUT_POST, 'library_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $library_email = filter_input(INPUT_POST, 'library_email', FILTER_SANITIZE_EMAIL);
    $library_phone = filter_input(INPUT_POST, 'library_phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $library_address = filter_input(INPUT_POST, 'library_address', FILTER_SANITIZE_SPECIAL_CHARS);
    $auto_approve = isset($_POST['auto_approve']) ? '1' : '0';
    $email_notify = isset($_POST['email_notify']) ? '1' : '0';

    $settings_to_update = [
        'library_name' => $library_name,
        'library_email' => $library_email,
        'library_phone' => $library_phone,
        'library_address' => $library_address,
        'auto_approve' => $auto_approve,
        'email_notify' => $email_notify,
        'max_books' => filter_input(INPUT_POST, 'max_books', FILTER_SANITIZE_NUMBER_INT),
        'borrow_duration' => filter_input(INPUT_POST, 'borrow_duration', FILTER_SANITIZE_NUMBER_INT),
        'reservation_limit' => filter_input(INPUT_POST, 'reservation_limit', FILTER_SANITIZE_NUMBER_INT),
        'renewal_limit' => filter_input(INPUT_POST, 'renewal_limit', FILTER_SANITIZE_NUMBER_INT),
        'enable_2fa' => isset($_POST['enable_2fa']) ? '1' : '0',
        'otp_duration' => filter_input(INPUT_POST, 'otp_duration', FILTER_SANITIZE_NUMBER_INT),
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0'
    ];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($settings_to_update as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        $pdo->commit();
        $_SESSION['success'] = "System settings updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Failed to update settings: " . $e->getMessage();
    }

    header("Location: system_settings.php");
    exit();
}

// 2. Fetch Current Settings (Procedural)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($results as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Helper to safely get setting
function getSetting($key, $default = '')
{
    global $settings;
    return $settings[$key] ?? $default;
}
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
    <style>
        /* Settings Page Layout */
        .settings-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        .settings-nav {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            height: fit-content;
        }

        .settings-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 8px;
            color: var(--text-lighter);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 5px;
            transition: var(--transition);
        }

        .settings-nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .settings-nav-item:hover:not(.active) {
            background: #f1f5f9;
            color: var(--primary-color);
        }

        .settings-form-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
        }

        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .form-grid {
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
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            outline: none;
            font-size: 14px;
        }

        /* Toggle Component */
        .toggle-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .toggle-info h4 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .toggle-info p {
            font-size: 12px;
            color: var(--text-lighter);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: var(--primary-color);
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        .action-bar {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        /* Tab Content Styling */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            <div class="header-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search configuration settings...">
            </div>
            <div class="header-user">
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

            <div class="settings-grid">
                <!-- Settings Sidebar (Inner) -->
                <div class="settings-nav animate-up delay-2" id="settingsTabs">
                    <a href="javascript:void(0)" class="settings-nav-item active" data-tab="profile"><i class="fas fa-university"></i> Library Profile</a>
                    <a href="javascript:void(0)" class="settings-nav-item" data-tab="circulation"><i class="fas fa-history"></i> Circulation Rules</a>
                    <a href="javascript:void(0)" class="settings-nav-item" data-tab="security"><i class="fas fa-user-lock"></i> Account Security</a>
                    <a href="javascript:void(0)" class="settings-nav-item" data-tab="maintenance"><i class="fas fa-server"></i> Maintenance</a>
                </div>

                <!-- Settings Content -->
                <div class="settings-content animate-up delay-3">
                    <form action="system_settings.php" method="POST" class="settings-form-card">

                        <!-- Tab: Library Profile -->
                        <div id="profile" class="tab-content active">
                            <div class="section-header">
                                <h2>General Library Profile</h2>
                            </div>

                            <div class="form-group" style="margin-bottom: 20px;">
                                <label for="library_name">Library Display Name</label>
                                <input type="text" id="library_name" name="library_name" value="<?php echo htmlspecialchars(getSetting('library_name')); ?>">
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="library_email">Official Email</label>
                                    <input type="email" id="library_email" name="library_email" value="<?php echo htmlspecialchars(getSetting('library_email')); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="library_phone">Contact Hotline</label>
                                    <input type="text" id="library_phone" name="library_phone" value="<?php echo htmlspecialchars(getSetting('library_phone')); ?>">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 30px;">
                                <label for="library_address">Campus Location / Address</label>
                                <textarea id="library_address" name="library_address" rows="3"><?php echo htmlspecialchars(getSetting('library_address')); ?></textarea>
                            </div>


                        </div>

                        <!-- Tab: Circulation Rules -->
                        <div id="circulation" class="tab-content">

                            <div class="section-header">
                                <h2>Circulation Defaults</h2>
                            </div>

                            <div class="toggle-row">
                                <div class="toggle-info">
                                    <h4>Automatic Book Approvals</h4>
                                    <p>Enable to bypass librarian approval for borrowing requests.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="auto_approve" <?php echo getSetting('auto_approve') === '1' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="toggle-row" style="border-bottom: none;">
                                <div class="toggle-info">
                                    <h4>Email Notifications</h4>
                                    <p>Send automated reminders for due dates and penalties.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="email_notify" <?php echo getSetting('email_notify') === '1' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="section-header">
                                <h2>Circulation Rules</h2>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="max_books">Max Books per User</label>
                                    <input type="number" id="max_books" name="max_books" value="<?php echo htmlspecialchars(getSetting('max_books')); ?>" min="1">
                                </div>
                                <div class="form-group">
                                    <label for="borrow_duration">Default Borrow Duration (Days)</label>
                                    <input type="number" id="borrow_duration" name="borrow_duration" value="<?php echo htmlspecialchars(getSetting('borrow_duration')); ?>" min="1">
                                </div>
                            </div>

                            <div class="form-grid" style="margin-top: 20px; margin-bottom: 20px;">
                                <div class="form-group">
                                    <label for="reservation_limit">Reservation Limit</label>
                                    <input type="number" id="reservation_limit" name="reservation_limit" value="<?php echo htmlspecialchars(getSetting('reservation_limit')); ?>" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="renewal_limit">Renewal Limit (Times)</label>
                                    <input type="number" id="renewal_limit" name="renewal_limit" value="<?php echo htmlspecialchars(getSetting('renewal_limit')); ?>" min="0">
                                </div>
                            </div>
                        </div>


                        <!-- Tab: Account Security -->
                        <div id="security" class="tab-content">
                            <div class="section-header">
                                <h2>Account Security</h2>
                            </div>

                            <div class="toggle-row" style="margin-bottom: 20px;">
                                <div class="toggle-info">
                                    <h4>Two-Factor Authentication (2FA)</h4>
                                    <p>Require OTP verification for all librarian and student logins.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="enable_2fa" <?php echo getSetting('enable_2fa') === '1' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="form-group" style="margin-top: 20px;">
                                <label for="otp_duration">OTP Validity Duration (Seconds)</label>
                                <input type="number" id="otp_duration" name="otp_duration" value="<?php echo htmlspecialchars(getSetting('otp_duration')); ?>" min="60" step="60">
                                <small style="color: var(--text-lighter); font-size: 11px;">Standard is 300 seconds (5 minutes).</small>
                            </div>
                        </div>

                        <!-- Tab: Maintenance -->
                        <div id="maintenance" class="tab-content">
                            <div class="section-header">
                                <h2>System Maintenance</h2>
                            </div>

                            <div class="toggle-row" style="margin-bottom: 25px;">
                                <div class="toggle-info">
                                    <h4>Maintenance Mode</h4>
                                    <p>When enabled, only administrators can access the system. Students and Librarians will see a maintenance message.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="maintenance_mode" <?php echo getSetting('maintenance_mode') === '1' ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="section-header">
                                <h2>System Information</h2>
                            </div>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>PHP Version</label>
                                    <input type="text" value="<?php echo PHP_VERSION; ?>" readonly style="background: #f8fafc; cursor: default;">
                                </div>
                                <div class="form-group">
                                    <label>Server Software</label>
                                    <input type="text" value="<?php echo $_SERVER['SERVER_SOFTWARE']; ?>" readonly style="background: #f8fafc; cursor: default;">
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 20px;">
                                <label>Database Engine</label>
                                <input type="text" value="MySQL / InnoDB" readonly style="background: #f8fafc; cursor: default;">
                            </div>
                        </div>

                        <div class="action-bar">
                            <button type="button" class="btn btn-outline" onclick="window.location.reload();">Discard Changes</button>
                            <button type="submit" name="save_settings" class="btn btn-primary">Save System Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../../../../src/js/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.settings-nav-item');
            const contents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-tab');

                    // Update active tab link
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Show target content
                    contents.forEach(content => {
                        content.classList.remove('active');
                        if (content.id === target) {
                            content.classList.add('active');
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>