<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
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
  background-image: url("../assets/images/dogcat.webp");
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


 /* Pets Section */
    .pet-card {
      border-radius: 12px;
      overflow: hidden;
      background-color: #fff;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    .pet-card:hover {
      transform: scale(1.03);
      box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    }
    .pet-img {
      width: 100%;
      height: 250px;
      object-fit: cover;
      cursor: pointer;
    }
    .pet-info {
      padding: 15px;
      text-align: center;
    }
    .pet-name {
      font-family: 'Quicksand', sans-serif;
      font-weight: 700;
      font-size: 1.2rem;
    }

  </style>
</head>
<body>

<?php include('includes/navbar.php'); ?>


<!-- Footer -->
<footer>
  © 2025 SafePaws. All Rights Reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    
</body>
</html>