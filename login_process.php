<?php
session_start();
include 'config.php'; // 🔹 contains your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            header("Location: dashboard.php"); // or any protected page
            exit();
        } else {
            echo "<script>alert('Incorrect password. Please try again.'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.'); window.location='login.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
