<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pet Details - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
  border-radius: 10px;
  aspect-ratio: 1 / 1;     /* keeps the image perfectly square */
  object-fit: cover; 
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: pointer;
}

.pet-img:hover {
  transform: scale(1.05); /* zoom in slightly */
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* add subtle shadow */
}

    .pet-details-container {
      background-color: #ffffff;
      border-radius: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 40px;
      margin-top: 60px;
    }
    .pet-img {
      width: 100%;
      border-radius: 15px;
      object-fit: cover;
    }
    .pet-name {
      font-size: 2rem;
      font-weight: 700;
      color: #333;
    }
    .pet-age, .pet-breed, .pet-gender, .pet-color, .pet-health-status, .pet-temperament,
    .pet-adoption-status, .pet-date-sheltered{
      font-size: 1.1rem;
      color: #777;
      margin-bottom: 15px;
    }
    .pet-desc {
      font-size: 1rem;
      line-height: 1.6;
      color: #555;
    }
    .btn-adopt {
      background-color: #f8a488;
      border: none;
      color: #fff;
    }
    .btn-adopt:hover {
      background-color: #e78d73;
    }
  </style>
</head>
<body>

<?php include('includes/navbar.php'); ?>

  <div class="container pet-details-container">
    <div class="row align-items-center">
      <!-- Pet Image -->
      <div class="col-md-6">
        <img src="assets/images/dog1.jpg" alt="Bapi" class="pet-img">
      </div>

      <!-- Pet Details -->
      <div class="col-md-6 text-start mt-4 mt-md-0">
        <h2 class="pet-name">Bapi</h2>
        <p class="pet-age">Age: 2 years old</p>
        <p class="pet-breed">Breed: Chiuaua</p>
        <p class="pet-gender">Gender: Male</p>
        <p class="pet-color">Color: Brown</p>
        <p class="pet-health-status">Health Status: Vaccinated</p>
        <p class="pet-temperament">Temperament: Playful, Friendly</p>
        <p class="pet-adoption-status">Adoption Status: Available</p>
        <p class="pet-date-sheltered">Date Sheltered: 10/21/2025</p>
        <button class="btn btn-adopt mt-3" onclick="window.location.href='login.php'">Adopt Me</button>

      </div>
    </div>
  </div>

  <!-- Footer -->
<footer>
  © 2025 SafePaws. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>