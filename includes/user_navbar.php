<?php
include('../config/db.php');
session_start();

$pending_count = 0;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $query = "SELECT COUNT(*) AS total FROM adoption_requests 
              WHERE user_id = '$user_id' AND status = 'Pending'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $pending_count = $row['total'];
    }
}
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-5">
  <a class="navbar-brand fw-bold" href="user_index.php">SafePaws</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav align-items-center">
      <li class="nav-item"><a class="nav-link" href="user_about.php">About Us</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Gallery</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Donations</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Contact Us</a></li>
      <li class="nav-item"><a class="nav-link" href="user_adopt.php">Adopt Now</a></li>

      <!-- Adoption Status Link with Badge -->
      <li class="nav-item position-relative">
        <a class="nav-link" href="user_adoption_status.php">
          <i class="bi bi-bell"></i> Adoption Status
          <?php if ($pending_count > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
              <?= $pending_count ?>
            </span>
          <?php endif; ?>
        </a>
      </li>

      <li class="nav-item"><a class="nav-link text-danger" href="user_logout.php">Logout</a></li>
    </ul>
  </div>
</nav>
