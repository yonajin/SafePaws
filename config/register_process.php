<?php
include('db.php');
session_start();

if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "⚠️ Passwords do not match!";
        header("Location: register.php");
        exit();
    }

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "⚠️ Email is already registered!";
        header("Location: register.php");
        exit();
    }

    // Hash password and insert
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (full_name, email, password, status, date_registered)
              VALUES ('$full_name', '$email', '$hashed_password', 'Active', NOW())";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "✅ Registration successful! You can now log in.";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['error'] = "❌ Error: " . mysqli_error($conn);
        header("Location: register.php");
        exit();
    }
}
?>
