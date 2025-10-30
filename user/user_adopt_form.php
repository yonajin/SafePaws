<?php
session_start();
include('../config/db.php'); // ✅ correct relative path

// ✅ Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ Get pet ID from URL (ex: user_adopt_form.php?pet_id=3)
if (!isset($_GET['pet_id'])) {
    echo "<script>alert('No pet selected for adoption.'); window.location='user_adopt.php';</script>";
    exit();
}

$pet_id = intval($_GET['pet_id']);
$pet_query = mysqli_query($conn, "SELECT * FROM pets WHERE pet_id = '$pet_id'");
$pet = mysqli_fetch_assoc($pet_query);

if (!$pet) {
    echo "<script>alert('Pet not found.'); window.location='user_adopt.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Adoption Form - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include('../includes/user_navbar.php'); ?>

<div class="container_form">
  <h2>Adoption Form for <?= htmlspecialchars($pet['name']) ?></h2>
  <p class="text-muted text-center">Fields marked with * are required</p>

  <form action="../config/adoption_process.php" method="POST" enctype="multipart/form-data">

    <!-- ✅ Hidden Pet Info -->
    <input type="hidden" name="pet_id" value="<?= htmlspecialchars($pet['pet_id']) ?>">
    <input type="hidden" name="pet_name" value="<?= htmlspecialchars($pet['name']) ?>">
    <input type="hidden" name="classification" value="<?= htmlspecialchars($pet['classification']) ?>">

    <h5 class="mt-4 mb-4" style="text-align: center;">Applicant’s Information</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">First Name *</label>
        <input type="text" name="first_name" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Last Name *</label>
        <input type="text" name="last_name" class="form-control" required>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Address *</label>
      <input type="text" name="address" class="form-control" required>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Phone *</label>
        <input type="text" name="phone" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Email *</label>
        <input type="email" name="email" class="form-control" required>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Birth Date *</label>
        <input type="date" name="birth_date" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Occupation</label>
        <input type="text" name="occupation" class="form-control">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Company / Business Name</label>
      <input type="text" name="company" class="form-control" placeholder="Type N/A if unemployed">
    </div>

    <div class="mb-3">
      <label class="form-label">Social Media Profile</label>
      <input type="url" name="social_media" class="form-control" placeholder="Enter FB, Twitter, or IG link">
    </div>

    <h5 class="mt-4">Adoption Details</h5>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Have you adopted from SafePaws before? *</label>
        <select name="adopted_before" class="form-select" required>
          <option value="">Select</option>
          <option value="Yes">Yes</option>
          <option value="No">No</option>
        </select>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Why do you want to adopt?</label>
      <textarea name="reason" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Upload a valid ID</label>
      <input type="file" name="valid_id" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
      <small class="text-muted">Max. file size: 8 MB</small>
    </div>

    <div class="text-center mt-4">
      <button type="submit" name="submit" class="btn btn-submit px-4">Apply Now</button>
    </div>
  </form>
</div>

<?php include('../includes/footer.php'); ?>

</body>
</html>
