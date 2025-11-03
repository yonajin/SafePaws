<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // 🔹 Admin Check
    $admin_sql = "SELECT * FROM admin WHERE email = ? LIMIT 1";
    $admin_stmt = $conn->prepare($admin_sql);
    $admin_stmt->bind_param("s", $email);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();

    if ($admin_result->num_rows === 1) {
        $admin = $admin_result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['role'] = $admin['role'] ?? 'Admin';
            header("Location: ../admin/admin_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Incorrect admin password.'); window.location='../login.php';</script>";
            exit();
        }
    }

    // 🔹 User Check
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] !== 'Active') {
            echo "<script>alert('Your account is inactive.'); window.location='../login.php';</script>";
            exit();
        }

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['full_name'];
            header("Location: ../user/user_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Incorrect password.'); window.location='../login.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('No account found with that email.'); window.location='../login.php';</script>";
        exit();
    }

    $conn->close();
}
?>
