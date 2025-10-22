<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafePaws - Navbar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

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
    .section-title {
      font-weight: 600;
      margin-bottom: 20px;
    }

  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light px-5">
  <a class="navbar-brand" href="index.php">SafePaws</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" href="about.php">About Us</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Gallery</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Donations</a></li>
      <li class="nav-item"><a class="nav-link" href="#">Contact Us</a></li>
      <li class="nav-item"><a class="nav-link" href="adopt.php">Adopt Now</a></li>
    </ul>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
