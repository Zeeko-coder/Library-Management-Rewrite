<?php

/**
 * Process Librarian Registration
 * This file handles the registration of new librarians.
 * It validates input, hashes the password, and stores the data in the database.
 */

session_start();
require_once '../database/db_connection.php';
require_once '../helpers/cryptography_process.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstname = encryptionData(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_SPECIAL_CHARS));
    $lastname = encryptionData(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_SPECIAL_CHARS));
    $civilStatus = filter_input(INPUT_POST, 'civilStatus', FILTER_SANITIZE_SPECIAL_CHARS);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_SPECIAL_CHARS);
    $rawPhonenumber = filter_input(INPUT_POST, 'phonenumber', FILTER_SANITIZE_SPECIAL_CHARS);
    $phonenumber = encryptionData($rawPhonenumber);
    $email = encryptionData(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $username = encryptionData(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Basic validation
    if (
        empty($firstname) || empty($lastname) || empty($civilStatus) || empty($gender) ||
        empty($phonenumber) || empty($email) || empty($username) || empty($password) || empty($confirmPassword)
    ) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    //check user string length

    if (strlen($firstname) < 3) {
        $_SESSION['error'] = "First name must be at least 3 characters long.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    if (strlen($lastname) < 3) {
        $_SESSION['error'] = "Last name must be at least 3 characters long.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    if (strlen($username) < 2) {
        $_SESSION['error'] = "Username must be at least 2 characters long.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    // Phone number validation: must start with 63
    if (strpos($rawPhonenumber, '63') !== 0) {
        $_SESSION['error'] = "Phone number must start with 63 (e.g., 639631667321).";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    // Password matching
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    // Email validation
    if (!filter_var(decryptionData($email), FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    // Password strength check (At least 8 characters and one special character)
    if (!preg_match("/^(?=.*[!@#$%^&*]).{8,}$/", $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long and contain at least one special character (!@#$%^&*).";
        header("Location: ../public/librarianRegister.php");
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "Username already exists.";
            header("Location: ../public/librarianRegister.php");
            exit();
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        // Default role is 'Librarian' and status is 'Pending'
        $sql = "INSERT INTO users (first_name, last_name, email, phone_number, gender, civil_status, username, password, role, approval_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Librarian', 'Pending')";

        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $firstname,
            $lastname,
            $email,
            $phonenumber,
            $gender,
            $civilStatus,
            $username,
            $hashedPassword
        ]);

        if ($result) {
            $_SESSION['success'] = "Registration successful! Your account is pending admin approval.";
            header("Location: ../public/librarianRegister.php");
            exit();
        } else {
            $_SESSION['error'] = "Something went wrong during registration. Please try again.";
            header("Location: ../public/librarianRegister.php");
            exit();
        }
    } catch (PDOException $e) {
        // Log error and show a generic message or the specific error for debugging
        // error_log($e->getMessage()); 
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: ../public/librarianRegister.php");
        exit();
    }
} else {
    // If not a POST request, redirect to the registration page
    header("Location: ../public/librarianRegister.php");
    exit();
}
