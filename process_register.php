<?php
session_start();
include 'database/db_connection.php';

if($_SERVER['REQUEST_METHOD'] === "POST") {
    $firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_SPECIAL_CHARS);
    $lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_SPECIAL_CHARS);
    $civilStatus = filter_input(INPUT_POST, 'civilStatus', FILTER_SANITIZE_SPECIAL_CHARS);
    $userRole = filter_input(INPUT_POST, 'userRole', FILTER_SANITIZE_SPECIAL_CHARS);
    $studentID = filter_input(INPUT_POST, 'studentID', FILTER_SANITIZE_SPECIAL_CHARS);
    $program = filter_input(INPUT_POST, 'program', FILTER_SANITIZE_SPECIAL_CHARS);
    $librarianID = filter_input(INPUT_POST, 'librarianID', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phoneNumber = filter_input(INPUT_POST, 'phoneNumber', FILTER_SANITIZE_NUMBER_INT);
    $newUser = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
    $confirmPassword = filter_input(INPUT_POST, 'confirmPassword', FILTER_SANITIZE_SPECIAL_CHARS);

    if (empty($firstname) || empty($lastname) || empty($civilStatus) || empty($userRole) || empty($studentID) || empty($program) || empty($librarianID) || empty($email) || empty($newUser) || empty($password) || empty($confirmPassword)) {
        $_SESSION['registerError'] = "Please fill in all required fields!";
        header("Location: index.html?error=" . urlencode("Please fill in all required fields!"));
        exit();
    }

    if(strlen($firstname) < 2 || strlen($lastname) < 2) {
        $_SESSION['fieldErrors']['name'] = "Firstname and Lastname must be at least 2 characters long";
        $_SESSION['registerError'] = "Firstname and Lastname must be at least 2 characters long!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (!preg_match('/^\d{2}-\d{4}$/', $studentID)) {
        $_SESSION['fieldErrors']['studentID'] = "Student ID must be in the format XX-XXXX";
        $_SESSION['registerError'] = "Student ID must be in the format XX-XXXX!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (!preg_match('/^LIB-\d{4}$/', $librarianID)) {
        $_SESSION['fieldErrors']['librarianID'] = "Librarian ID must be in the format LIB-XXXX";
        $_SESSION['registerError'] = "Librarian ID must be in the format LIB-XXXX!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['fieldErrors']['email'] = "Invalid email format";
        $_SESSION['registerError'] = "Please enter a valid email address!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (!preg_match('/^639\d{9}$/', $phoneNumber)) {
        $_SESSION['fieldErrors']['phoneNumber'] = "Phone number must start with 639 and be 12 digits long.";
        $_SESSION['registerError'] = "Phone number must start with 63!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (strlen($newUser) < 3) {
        $_SESSION['fieldErrors']['username'] = "Username must be at least 3 characters long";
        $_SESSION['registerError'] = "Username must be at least 3 characters long!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W]).{8,}$/', $password)) {
        $_SESSION['fieldErrors']['password'] = "Password must be at least 8 characters with uppercase, lowercase, numbers, and special characters";
        $_SESSION['registerError'] = "Password must be at least 8 characters long and include uppercase, lowercase, numbers, and special characters!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    if ($password !== $confirmPassword) {
        $_SESSION['fieldErrors']['confirmPassword'] = "Passwords do not match";
        $_SESSION['registerError'] = "Passwords do not match!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    $checkUser = "SELECT * FROM users WHERE username = '$newUser'";

    $result = $conn->query($checkUser);

    if(mysqli_num_rows($result) > 0) {
        $_SESSION['registerError'] = "Username already exists!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }


    $checkStudentID = "SELECT * FROM users WHERE studentID = '$studentID'";
    $result = $conn->query($checkStudentID);

    if(mysqli_num_rows($result) > 0) {
        $_SESSION['registerError'] = "Student ID already exists!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    $checkLibrarianID = "SELECT * FROM users WHERE librarianID = '$librarianID'";
    $result = $conn->query($checkLibrarianID);

    if(mysqli_num_rows($result) > 0) {
        $_SESSION['registerError'] = "Librarian ID already exists!";
        header("Location: index.html?error=" . urlencode($_SESSION['registerError']));
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    //$approval = 'pending';

    $sql = "INSERT INTO users (firstname, lastname, civilStatus, userRole, studentID, program, librarianID, email, phoneNumber, username, password) VALUES ('$firstname', '$lastname', '$civilStatus', '$userRole', '$studentID', '$program', '$librarianID', '$email', '$phoneNumber', '$newUser', '$hashedPassword')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['registerSuccess'] = "Registration successful! Please wait for account approval.";
        header("Location: index.html?success=" . urlencode("Registration successful! Please wait for account approval."));
        exit();
    } else {
        $_SESSION['registerError'] = "Error: " . mysqli_error($conn);
        header("Location: index.html?error=" . urlencode("Error: " . mysqli_error($conn)));
        exit();
    }
}