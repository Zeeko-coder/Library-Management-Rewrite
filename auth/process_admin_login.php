<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $adminID = trim($_POST['adminID']);
    $password = $_POST['password'];
    $errors = [];

    if (empty($adminID)) {
        $errors['adminID'] = "Admin ID is required.";
    }

    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (!empty($errors)) {
        $_SESSION['field_errors'] = $errors;
        header("Location: ../public/adminLogin.php");
        exit();
    }

    if ($adminID === "admin" && $password === "admin12345") {
        $_SESSION['adminID'] = $adminID;
        header("Location: ../public/dashboard/admin/dashboard_admin.php");
        exit();
    } else {
        $errors['adminID'] = "Invalid Admin ID.";
        $errors['password'] = "Invalid Password.";
        $_SESSION['field_errors'] = $errors;
        header("Location: ../public/adminLogin.php");
        exit();
    }
}
