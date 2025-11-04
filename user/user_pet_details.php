<?php
include('../config/db.php');

// Get pet ID from URL (e.g., pet_details.php?id=1)
if (isset($_GET['pet_id'])) {
    $pet_id = intval($_GET['pet_id']);

    $sql = "SELECT * FROM pets WHERE pet_id = $pet_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pet = $result->fetch_assoc();
    } else {
        echo "<div class='container mt-5'><h3>Pet not found.</h3></div>";
        exit;
    }
} else {
    echo "<div class='container mt-5'><h3>No pet selected.</h3></div>";
    exit;
}
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
    
    .section-title {
      font-weight: 600;
      margin-bottom: 20px;
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
<?php include('../includes/user_navbar.php'); ?>
  <div class="container pet-details-container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <img src="../uploads/<?php echo htmlspecialchars($pet['image_url']); ?>" 
     alt="<?php echo htmlspecialchars($pet['name']); ?>" 
     class="pet-img">

      </div>
      <div class="col-md-6 text-start mt-4 mt-md-0">
        <h2 class="pet-name"><?php echo $pet['name']; ?></h2>
        <p>Classification: <?php echo $pet['classification']; ?></p>
        <p>Age: <?php echo $pet['age']; ?></p>
        <p>Breed: <?php echo $pet['breed']; ?></p>
        <p>Gender: <?php echo $pet['gender']; ?></p>
        <p>Color: <?php echo $pet['color']; ?></p>
        <p>Health Status: <?php echo $pet['health_status']; ?></p>
        <p>Temperament: <?php echo $pet['temperament']; ?></p>
        <p>Adoption Status: <?php echo $pet['adoption_status']; ?></p>
        <p>Date Sheltered: <?php echo date('m/d/Y', strtotime($pet['date_sheltered'])); ?></p>
        <button class="btn btn-adopt mt-3" onclick="window.location.href='../user/user_adopt_form.php?pet_id=<?php echo $pet['pet_id']; ?>'">
             Adopt Me
        </button>
      </div>
    </div>
  </div>
<?php include('../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
