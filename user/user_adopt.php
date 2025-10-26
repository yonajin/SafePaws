<?php
include('../config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SafePaws - Adopt a Pet</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif;}

    /* Navbar */
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

    /* Hero */
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
      align-items: flex-start;
      text-align: left;
      padding-left: 100px;
    }
    .hero h1 { font-family: 'Quicksand', sans-serif; font-weight: 700; font-size: 50px; }
    .hero p { font-family: 'Quicksand', sans-serif; font-size: 22px; font-weight: 600; }

    .section-title {
      font-weight: 600;
      margin-bottom: 20px;
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
    .btn-view {
      background-color: #f8a488;
      border: none;
      color: white;
      transition: background-color 0.2s ease;
    }
    .btn-view:hover { background-color: #e78d73; }

    footer {
      background: #f1ece9;
      text-align: center;
      padding: 10px;
      font-size: 14px;
      color: #333;
      margin-top: 40px;
    }
  </style>
</head>
<body>

<?php include('../includes/user_navbar.php'); ?>

<!-- 🐾 Hero Section -->
<section class="hero">
  <h1>Find Your New Best Friend</h1>
  <p>Adopt, don’t shop. Give a furry friend a forever home today!</p>
</section>

<!-- 🐾 Meet Our Pets -->
<section class="container text-center py-5">
  <h2 class="section-title mb-4">Meet Our Lovely Pets</h2>

  <!-- Filter Buttons -->
  <div class="mb-4">
    <button class="btn btn-outline-dark active filter-btn" data-filter="all">All</button>
    <button class="btn btn-outline-dark filter-btn" data-filter="Dog">Dogs</button>
    <button class="btn btn-outline-dark filter-btn" data-filter="Cat">Cats</button>
  </div>

  <div class="row g-4" id="pets-container">
    <?php
    $sql = "SELECT * FROM pets ORDER BY date_sheltered DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0):
      while ($pet = $result->fetch_assoc()):
    ?>
      <div class="col-md-3 col-sm-6 pet-item" data-type="<?php echo $pet['classification']; ?>">
        <div class="pet-card">
          <a href="user_pet_details.php?pet_id=<?php echo $pet['pet_id']; ?>">
            <img src="<?php echo !empty($pet['image_url']) ? $pet['image_url'] : '../assets/images/cat.jpg'; ?>" 
                 class="pet-img" alt="<?php echo htmlspecialchars($pet['name']); ?>">
          </a>
          <div class="pet-info">
            <p class="pet-name"><?php echo htmlspecialchars($pet['name']); ?></p>
            <p class="text-muted mb-1"><?php echo htmlspecialchars($pet['breed']); ?></p>
            <p class="text-muted small"><?php echo htmlspecialchars($pet['age']); ?></p>
          </div>
        </div>
      </div>
    <?php
      endwhile;
    else:
      echo "<p>No pets available for adoption right now.</p>";
    endif;
    ?>
  </div>
</section>

<!-- 🔍 Filter JS -->
<script>
  const filterButtons = document.querySelectorAll('.filter-btn');
  const petItems = document.querySelectorAll('.pet-item');

  filterButtons.forEach(button => {
    button.addEventListener('click', () => {
      filterButtons.forEach(btn => btn.classList.remove('active'));
      button.classList.add('active');
      const filter = button.getAttribute('data-filter');

      petItems.forEach(item => {
        const type = item.getAttribute('data-type');
        item.style.display = (filter === 'all' || filter === type) ? 'block' : 'none';
      });
    });
  });
</script>

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
