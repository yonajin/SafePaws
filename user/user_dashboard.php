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

  <style>
    body {
      background-color: #fff6f1;
      font-family: 'Poppins', sans-serif;
    }

    /* Navbar styling */
     .navbar {
  background-color: #A9745B !important;
  height: 70px;
}

.navbar .nav-link,
.navbar .navbar-brand {
  color: #FFFFFF !important;
}

.navbar .nav-link:hover,
.navbar .navbar-brand:hover {
  color: #ffe6d5 !important; /* optional hover color */
}

    .navbar-brand {
  font-family: 'Quicksand', sans-serif;
  color: #FFF8F3 !important;
  font-weight: 700; /* optional, makes it bolder */
  font-size: 40px;
}

.navbar .nav-link {
  font-family: 'Poppins', sans-serif;
  color: #FFF8F3 !important;
  font-weight: 500;
  font-size: 17px;
  margin-left: 20px;
}

    /* Dashboard box */
    .dashboard-container {
      max-width: 700px;
      margin: 100px auto;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px;
      text-align: center;
    }

    h2 {
      color: #333;
      font-weight: 700;
    }

    .btn-logout {
      background-color: #f8a488;
      border: none;
      color: #fff;
    }

    .btn-logout:hover {
      background-color: #e78d73;
    }
  </style>
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
