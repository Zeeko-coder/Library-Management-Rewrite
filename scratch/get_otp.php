<?php
require_once __DIR__ . '/../database/db_connection.php';
$user_id = $_GET['user_id'] ?? 14; // Default to student mel
$stmt = $pdo->prepare("SELECT otp_code FROM otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
echo $stmt->fetchColumn();
