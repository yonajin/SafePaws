<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafePaws - About Page</title>
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
    
.hero {
  background-image: url("assets/dogcat.webp");
  background-position: center;
  background-size: cover;
  background-repeat: no-repeat;
  height: 700px;
  color: #FFF8F3;
  
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start; /* 🔹 aligns to the left */
  text-align: left;        /* 🔹 left-align text */
  padding-left: 100px;     /* 🔹 space from the left edge */
}

    .hero h1 {
      font-family: 'Quicksand', sans-serif;
      font-weight: 700;
      font-size: 50px;
    }
    .hero p {
      font-family: 'Quicksand', sans-serif;
      font-size: 22px;
      font-weight: 600;
    }
    .hero button {
      background-color: #FFB6A0;
      font-family: 'Quicksand', sans-serif;
      font-weight: 600;
      border: none;
      color: #0A0000;
      width: 175px;
    }
    .section-title {
      font-weight: 600;
      margin-bottom: 20px;
    }
    .about-img {
      width: 100%;
      border-radius: 10px;
    }
    footer {
      background: #f1ece9;
      text-align: center;
      padding: 10px;
      font-size: 14px;
      color: #333;
      margin-top: 40px;
    }

    .square {
  background-color: #5a5755ff; /* or bg-secondary if using Bootstrap */
  aspect-ratio: 1 / 1;       /* makes width = height */
  width: 100%;               /* fills the column width */
  border-radius: 8px;    
  margin-bottom: 8px;    /* optional, rounded corners */
}

.pet-img {
  width: 100%;
  aspect-ratio: 1 / 1;     /* keeps the image perfectly square */
  object-fit: cover;       /* crops and fills square nicely */
  border-radius: 8px;      /* optional rounded corners */
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

<!-- About Section -->
<section class="container py-5 border-top border-bottom">
  <div class="row align-items-center">
    <div class="col-md-7">
      <h2 class="section-title">About SafePaws</h2>
      <p>
        Learn more about our work, compassionate caregiving, and dedication to animal welfare.
        At SafePaws, we strive to rescue, rehabilitate, and rehome stray and abandoned pets.
        Our mission is to promote responsible pet ownership and provide safe havens for animals in need.
        Every adoption helps us save more lives. Join our community and make a difference today.
      </p>
    </div>
    <div class="col-md-5">
      <div class="bg-secondary about-img" style="height:250px;"></div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section class="container py-5">
  <h2 class="section-title text-center">Contact Us</h2>
  <form class="mx-auto" style="max-width:600px;">
    <div class="row mb-3">
      <div class="col"><input type="text" class="form-control" placeholder="Your Name"></div>
      <div class="col"><input type="text" class="form-control" placeholder="Last"></div>
    </div>
    <div class="row mb-3">
      <div class="col"><input type="email" class="form-control" placeholder="Email"></div>
      <div class="col"><input type="text" class="form-control" placeholder="Phone"></div>
    </div>
    <div class="mb-3">
      <textarea class="form-control" rows="4" placeholder="Your message"></textarea>
    </div>
    <div class="text-center">
      <button type="submit" class="btn btn-dark">Submit</button>
    </div>
  </form>
</section>

<!-- Footer -->
<footer>
  © 2025 SafePaws. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
