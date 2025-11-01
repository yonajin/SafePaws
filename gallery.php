<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<!-- 🐾 Gallery Section with Filters -->
<section class="container py-5 text-center">
  <div class="p-5 rounded-4 shadow-sm" style="background-color: #fff6f1;">
    <h2 class="section-title mb-4">Gallery of Love and Rescue</h2>
    <p class="mx-auto" style="max-width:700px;">
      Explore our gallery filled with joyful moments — from adorable adoptable pets to inspiring adoption stories 
      and daily life at SafePaws. Every photo tells a story of compassion and hope.
    </p>

    <!-- 🧡 Filter Buttons -->
    <div class="btn-group mt-3" role="group" aria-label="Gallery Filters">
      <button class="btn btn-sm btn-outline-warning active" data-filter="all">All</button>
      <button class="btn btn-sm btn-outline-warning" data-filter="adoptable">Adoptable</button>
      <button class="btn btn-sm btn-outline-warning" data-filter="adopted">Adopted</button>
      <button class="btn btn-sm btn-outline-warning" data-filter="shelter">Shelter Moments</button>
    </div>

    <!-- 🐶 Gallery Grid -->
    <div class="row mt-4 g-4 justify-content-center">

      <!-- Adoptable -->
      <div class="col-md-4 gallery-item adoptable">
        <div class="card border-0 shadow-sm h-100">
          <img src="assets/images/cat1.jpg" class="card-img-top rounded-top-4" alt="Rigby">
          <div class="card-body">
            <h5 class="card-title">Rigby</h5>
            <p class="card-text text-muted">A playful 2-year-old cat who loves long walks and cuddles.</p>
            <a href="adopt.php" class="btn btn-sm" style="background-color:#f8a488; color:white;">Adopt Me</a>
          </div>
        </div>
      </div>

      <!-- Adopted -->
      <div class="col-md-4 gallery-item adopted">
        <div class="card border-0 shadow-sm h-100">
          <img src="assets/images/adopted.jpg" class="card-img-top rounded-top-4" alt="Bobby">
          <div class="card-body">
            <h5 class="card-title">Bobby’s New Home</h5>
            <p class="card-text text-muted">Rescued and now loved — Bobby enjoys her forever home.</p>
            <span class="badge bg-success">Adopted</span>
          </div>
        </div>
      </div>

      <!-- Shelter -->
      <div class="col-md-4 gallery-item shelter">
        <div class="card border-0 shadow-sm h-100">
          <img src="assets/images/volunteer.webp" class="card-img-top rounded-top-4" alt="Volunteers caring for pets">
          <div class="card-body">
            <h5 class="card-title">Moments That Matter</h5>
            <p class="card-text text-muted">Our volunteers giving love and care during feeding time at the shelter.</p>
          </div>
        </div>
      </div>

      <!-- More Adoptable -->
      <div class="col-md-4 gallery-item adoptable">
        <div class="card border-0 shadow-sm h-100">
          <img src="assets/images/dog1.jpg" class="card-img-top rounded-top-4" alt="Bapi">
          <div class="card-body">
            <h5 class="card-title">Bapi</h5>
            <p class="card-text text-muted">A sweet and friendly Beagle waiting for a loving home.</p>
            <a href="adopt.php" class="btn btn-sm" style="background-color:#f8a488; color:white;">Adopt Me</a>
          </div>
        </div>
      </div>

      <!-- More Shelter -->
      <div class="col-md-4 gallery-item shelter">
        <div class="card border-0 shadow-sm h-100">
          <img src="assets/images/feeding.jpg" class="card-img-top rounded-top-4" alt="Feeding stray animals">
          <div class="card-body">
            <h5 class="card-title">Care in Action</h5>
            <p class="card-text text-muted">Feeding time — a daily reminder of compassion and teamwork.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 🧡 Filter Script -->
<script>
  const filterButtons = document.querySelectorAll('[data-filter]');
  const galleryItems = document.querySelectorAll('.gallery-item');

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      // Toggle active state
      filterButtons.forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');

      const filter = button.getAttribute('data-filter');
      galleryItems.forEach(item => {
        if (filter === 'all' || item.classList.contains(filter)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
</script>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    
</body>
</html>