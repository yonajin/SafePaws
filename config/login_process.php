<?php
session_start();
include('db.php'); // Ensure this connects using $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // --- 1️⃣ Check if the email belongs to an admin ---
    $admin_sql = "SELECT * FROM admin WHERE email = ? LIMIT 1";
    $admin_stmt = $conn->prepare($admin_sql);

    if ($admin_stmt) {
        $admin_stmt->bind_param("s", $email);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();

        if ($admin_result->num_rows === 1) {
            $admin = $admin_result->fetch_assoc();

            if (password_verify($password, $admin['password'])) {
                // ✅ Set session for admin
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['role'] = $admin['role'] ?? 'Admin';

                header("Location: ../admin/admin_dashboard.php"); // ✅ Redirect to admin dashboard
                exit();
            } else {
                echo "<script>alert('Incorrect admin password.'); window.location='../login.php';</script>";
                exit();
            }
        }
        $admin_stmt->close();
    }

    // --- 2️⃣ If not admin, check regular users ---
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if user is active
            if ($user['status'] !== 'Active') {
                echo "<script>alert('Your account is inactive. Please contact admin.'); window.location='../login.php';</script>";
                exit();
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['full_name'];

                header("Location: ../user/user_dashboard.php"); // ✅ Redirect to user dashboard
                exit();
            } else {
                echo "<script>alert('Incorrect password. Please try again.'); window.location='../login.php';</script>";
                exit();
            }
        } else {
            echo "<script>alert('No account found with that email.'); window.location='../login.php';</script>";
        }

        $stmt->close();
    } else {
        echo "SQL prepare failed: " . $conn->error;
    }

    $conn->close();
}
?>
