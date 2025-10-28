<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafePaws - Adoption Page</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include('includes/navbar.php'); ?>

<!-- Meet Our Pets -->
<section class="container text-center py-5">
  <h2 class="section-title mb-4">Meet Our Pets</h2>

  <!-- Filter Buttons -->
  <div class="mb-4">
    <button class="btn btn-outline-dark active filter-btn" data-filter="all">All</button>
    <button class="btn btn-outline-dark filter-btn" data-filter="cat">Cats</button>
    <button class="btn btn-outline-dark filter-btn" data-filter="dog">Dogs</button>
  </div>

  <div class="row g-4" id="pets-container">
    <!-- Cats -->

    <div class="col-md-3 col-sm-6 pet-item cat">
  <a href="pet_details.php">
    <img src="assets/images/cat1.jpg" class="pet-img1" alt="Rigby">
  </a>
  <p style="font-size: 20px;">Rigby</p>
   </div>

    <div class="col-md-3 col-sm-6 pet-item cat">
  <a href="pet_details.php">
    <img src="assets/images/cat4.jpg" class="pet-img1" alt="Wowo">
  </a>
  <p style="font-size: 20px;">Wowo</p>
   </div>

    <!-- Dogs -->
    <div class="col-md-3 col-sm-6 pet-item dog">
  <a href="pet_details.php">
    <img src="assets/images/dog1.jpg" class="pet-img1" alt="Bapi">
  </a>
  <p style="font-size: 20px;">Bapi</p>
   </div>

   <div class="col-md-3 col-sm-6 pet-item dog">
  <a href="pet_details.php">
    <img src="assets/images/dog2.jpg" class="pet-img1" alt="Jimbo">
  </a>
  <p style="font-size: 20px;">Jimbo</p>
   </div>
</section>

<!-- JavaScript Filter Function -->
<script>
  const filterButtons = document.querySelectorAll('.filter-btn');
  const petItems = document.querySelectorAll('.pet-item');

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      // Remove active state from all buttons
      filterButtons.forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');

      const filter = button.getAttribute('data-filter');
      petItems.forEach(item => {
        if (filter === 'all' || item.classList.contains(filter)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
</script>

<!-- Adoption FAQ Section -->
<section class="container py-5">
  <div class="text-center mb-5">
    <h2 class="section-title" style="color: #2e2e2e;">Adoption FAQ</h2>
    <p class="text-muted">Learn more about our adoption process and requirements</p>
  </div>

  <div class="row g-4 align-items-start">
    <!-- Left Column -->
    <div class="col-md-6">
      <div class="faq-item mb-4">
        <h5 class="fw-bold">Can I return my adopted pet if I change my mind?</h5>
        <p>
          A pet is a <strong>lifetime commitment</strong>. However, if you truly can’t keep your adopted pet,
          please don’t abandon them. Contact us so we can find another loving home for them.
        </p>
      </div>

      <div class="faq-item mb-4">
        <h5 class="fw-bold">Can my adoption application get denied?</h5>
        <p>
          Yes. Some reasons include not being able to keep pets indoors, household incompatibility,
          or any condition that could harm the health and safety of our animals.
        </p>
      </div>

      <div class="faq-item mb-4">
        <h5 class="fw-bold">I live in the province/abroad. Can I still adopt?</h5>
        <p>
          Yes, but special arrangements may be needed for meet-and-greet sessions depending on your location.
          Please contact us to discuss your options.
        </p>
      </div>
    </div>

    <!-- Right Column -->
    <div class="col-md-6">
      <div class="p-4 rounded-4 shadow-sm animate-fade" style="background-color: #333; color: #fff;">
        <ul class="list-unstyled mb-0">
          <li class="fade-item">🐾 Submit the adoption application form</li>
          <li class="fade-item">💬 Attend the online/onsite interview</li>
          <li class="fade-item">🐶 Meet our shelter animals in person</li>
          <li class="fade-item">🏠 Visit your chosen pet to confirm your choice</li>
          <li class="fade-item">📋 Wait for vet clearance and schedule pick up</li>
          <li class="fade-item">💰 Pay the adoption fee: <strong>₱500 (cat)</strong> / <strong>₱1000 (dog)</strong></li>
          <li class="fade-item">❤️ Take your pet home!</li>
        </ul>
      </div>

      <div class="faq-item mt-4">
        <h5 class="fw-bold">Why is there an adoption fee?</h5>
        <p>
          The adoption fee is a token of your commitment. It helps cover your pet’s
          spay/neuter surgery, vaccinations, and tick-flea treatment.
        </p>
      </div>

      <div class="faq-item">
        <h5 class="fw-bold">Can I adopt more than one pet?</h5>
        <p>
          Yes, some applicants may adopt more than one pet depending on our evaluation,
          especially if the animals belong to a bonded pair.
        </p>
      </div>
    </div>
  </div>
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
    <button class="btn btn-primary mt-3" style="background-color:#f8a488; border:none;">
      Donate Now
    </button>
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
      <img src="assets/images/about_img.jpg" alt="About SafePaws" class="about-img shadow">    </div>
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
