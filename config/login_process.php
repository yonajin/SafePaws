<?php
session_start();
include('db.php'); // Make sure this file connects using $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Prepare SQL query to get user
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
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];

                header("Location: ../user/user_dashboard.php"); // ✅ Adjust as needed
                exit();
            } else {
                echo "<script>alert('Incorrect password. Please try again.'); window.location='../login.php';</script>";
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
