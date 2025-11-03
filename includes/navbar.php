<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-5">
  <a class="navbar-brand" href="index.php">SafePaws</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
      <li class="nav-item"><a class="nav-link" href="gallery.php">Gallery</a></li>
      <li class="nav-item"><a class="nav-link" href="care_tips.php">Care Tips</a></li>
      <li class="nav-item"><a class="nav-link" href="contactus.php">Contact Us</a></li>
      <li class="nav-item"><a class="nav-link" href="adopt.php">Adopt Now</a></li>
    </ul>
  </div>
</nav>