<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafePaws - Landing Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<!-- Hero Section -->
<section class="hero">
  <h1>Adopt Love,<br>Save Lives</h1>
  <p>Find Your New Best Friend at SafePaws</p>
  <button class="btn btn-lg mt-2" onclick="window.location.href='adopt.php'">
  Find a Pet
</button>

</section>

<!-- Meet Our Pets -->
<section class="container text-center py-5">
  <h2 class="section-title mb-4">Meet Our Pets</h2>
  <div class="row g-4">

    <div class="col-md-3 col-sm-6 pet-item">
  <a href="pet_details.php">
    <img src="assets/images/cat1.jpg" class="pet-img1" alt="Rigby">
  </a>
  <p style="font-size: 20px;">Rigby</p>
   </div>

    <div class="col-md-3 col-sm-6 pet-item">
  <a href="pet_details.php">
    <img src="assets/images/cat4.jpg" class="pet-img1" alt="Wowo">
  </a>
  <p style="font-size: 20px;">Wowo</p>
   </div>

    <div class="col-md-3 col-sm-6 pet-item">
  <a href="pet_details.php">
    <img src="assets/images/dog1.jpg" class="pet-img1" alt="Bapi">
  </a>
  <p style="font-size: 20px;">Bapi</p>
   </div>

    <div class="col-md-3 col-sm-6 pet-item">
  <a href="pet_details.php">
    <img src="assets/images/dog2.jpg" class="pet-img1" alt="Jimbo">
  </a>
  <p style="font-size: 20px;">Jimbo</p>
   </div>
    <!-- add more as needed -->
  </div>
 <a href="adopt.php" class="btn btn-outline-dark mt-4">View More</a>
</section>

<!-- Donation Section -->
<section class="container text-center py-5">
  <div class="p-5 rounded-4 shadow-sm" style="background-color: #fff6f1; margin-bottom: 20px;">
    <h2 class="section-title">Support Our Mission</h2>
    <p class="mx-auto" style="max-width:700px;">
      Your donation plays a vital role in sustaining SafePaws’ rescue, rehabilitation, and adoption programs.
      Every contribution directly funds essential veterinary care, shelter maintenance, and community outreach
      initiatives that promote responsible pet ownership. With your support, we can continue improving the
      welfare of animals and creating lasting, positive change within our communities.
    </p>
    <a href="donations.php" class="btn btn-primary mt-3" style="background-color:#f8a488; border:none;">
      Donate Now
    </a>
  </div>
</section>


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
      <img src="assets/images/about_img.jpg" alt="About SafePaws" class="about-img shadow">
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

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
