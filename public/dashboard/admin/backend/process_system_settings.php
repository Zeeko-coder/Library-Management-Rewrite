<?php
session_start();

// Database connection
require_once __DIR__ . '/../../../../database/db_connection.php';

// 1. Handle Form Submission (Procedural)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $library_name = filter_input(INPUT_POST, 'library_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $library_email = filter_input(INPUT_POST, 'library_email', FILTER_SANITIZE_EMAIL);
    $library_phone = filter_input(INPUT_POST, 'library_phone', FILTER_SANITIZE_SPECIAL_CHARS);
    $library_address = filter_input(INPUT_POST, 'library_address', FILTER_SANITIZE_SPECIAL_CHARS);
    $settings_to_update = [
        'library_name' => $library_name,
        'library_email' => $library_email,
        'library_phone' => $library_phone,
        'library_address' => $library_address
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
