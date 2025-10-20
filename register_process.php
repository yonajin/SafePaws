<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('Email already registered. Please login instead.'); window.location='login.php';</script>";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $sql = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $sql->bind_param("sss", $name, $email, $hashedPassword);

    if ($sql->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Something went wrong. Please try again.'); window.location='register.php';</script>";
    }

    $check->close();
    $sql->close();
    $conn->close();
}
?>
