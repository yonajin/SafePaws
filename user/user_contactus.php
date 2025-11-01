<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include('../includes/user_navbar.php'); ?>

<!-- Contact Section -->
<section class="container py-5">
  <h2 class="section-title text-center" style="color: #2e2e2e;">Contact Safe Paws</h2>
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

<!-- Care Tips Section -->
<section class="container text-center py-5">
  <div class="p-5 rounded-4 shadow-sm" style="background-color: #fff6f1; margin-bottom: 20px;">
    <h2 class="section-title">Care Tips for Pets</h2>
    <p class="mx-auto" style="max-width:700px;">
      Discover helpful tips and best practices to keep your pets happy, healthy, and safe. 
      From proper nutrition and grooming to training and wellness routines, our care guides 
      are designed to help every pet owner provide the love and attention their furry friends deserve. 
      Learn how small, thoughtful actions can make a big difference in your pet’s life.
    </p>
    <a href="user_care_tips.php" class="btn btn-primary mt-3" style="background-color:#f8a488; border:none;">
      View Care Tips
    </a>
  </div>
</section>

<?php include('../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>