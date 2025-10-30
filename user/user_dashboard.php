<?php
session_start();

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <!-- 🔹 Dashboard Body -->
  <div class="dashboard-container">
    <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🐾</h2>
    <p class="mt-3">Thank you for being part of the <strong>SafePaws</strong> community.</p>
    <p>Explore available pets, make donations, and help us create a better world for animals.</p>
    <a href="user_adopt.php" class="btn btn-logout mt-3">View Adoptable Pets</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
