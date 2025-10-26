<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['admin_name'])) {
  $_SESSION['admin_name'] = "Admin";
}

// Handle Add Pet form submission
if (isset($_POST['add_pet'])) {
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $type = mysqli_real_escape_string($conn, $_POST['type']);
  $age = mysqli_real_escape_string($conn, $_POST['age']);
  $breed = mysqli_real_escape_string($conn, $_POST['breed']);
  $gender = mysqli_real_escape_string($conn, $_POST['gender']);
  $status = mysqli_real_escape_string($conn, $_POST['status']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);

  $image = $_FILES['image']['name'];
  $target = "uploads/" . basename($image);

  $sql = "INSERT INTO pets (name, type, age, breed, gender, status, description, image, date_added) 
          VALUES ('$name', '$type', '$age', '$breed', '$gender', '$status', '$description', '$image', NOW())";
  
  if (mysqli_query($conn, $sql)) {
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    echo "<script>alert('✅ Pet added successfully!'); window.location='manage_pets.php';</script>";
  } else {
    echo "<script>alert('❌ Error adding pet: " . mysqli_error($conn) . "');</script>";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Pets | SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }

    /* --- Sidebar --- */
    .sidebar {
      height: calc(100vh - 30px);
      width: 240px;
      background-color: #ffffff;
      border-right: 1px solid #ddd;
      position: fixed;
      top: 15px;
      left: 25px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      padding: 25px 0;
    }
    .sidebar h2 { font-family: 'Quicksand', sans-serif; color: #A9745B; font-weight: 700; font-size: 28px; margin-bottom: 25px; }
    .sidebar .nav { width: 100%; }
    .sidebar .nav-link { color: #333; font-weight: 500; padding: 12px 19px; display: block; transition: all 0.3s ease; border-radius: 8px; margin: 2px 10px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0e1d8; color: #A9745B; }
    .sidebar .nav-link.text-danger { color: #dc3545 !important; }

    /* --- Topbar --- */
    .topbar {
      background-color: #A9745B;
      height: 60px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 30px;
      color: white;
      margin-left: 288px;
      margin-right: 23px;
      border-radius: 15px;
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
      position: relative;
    }
    .topbar i { font-size: 26px; cursor: pointer; transition: 0.2s ease; }
    .topbar i:hover { opacity: 0.85; }

    /* --- Main Content --- */
    .main-content { margin-left: 260px; padding: 30px; margin-top: 20px; }

    /* --- Table --- */
    table { border-collapse: collapse; width: 100%; }
    th, td { text-align: center; padding: 12px; vertical-align: middle; }
    thead th { background-color: #f0e1d8; color: #A9745B; font-weight: 600; }
    tbody tr:nth-child(odd) { background-color: #ffffff; }
    tbody tr:nth-child(even) { background-color: #f9f9f9; }
    tbody tr:hover { background-color: #f1edea; transition: 0.2s; }

    .btn-add { background-color: #A9745B; color: white; margin-top: -10px;}
    .btn-add:hover { background-color: #8e5f47; }

    /* --- Profile Dropdown --- */
    .profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
    .profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: #333; }
    .profile-dropdown a:hover { background-color: #f8f8f8; }

    img.pet-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 10px; }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
      <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link active"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
      <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
      <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
      <a href="#" class="nav-link text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </a>
    </nav>
  </div>

  <!-- Topbar -->
  <div class="topbar">
    <i id="profileBtn" class="bi bi-person-circle"></i>
    <div id="profileDropdown" class="profile-dropdown">
      <a href="admin_profile.php"><i class="bi bi-person"></i> View Profile</a>
      <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
      <hr class="m-0">
      <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold" style="color:#A9745B; margin:0;">🐾 Manage Pets</h3>
      <button class="btn btn-add px-3 py-2" id="openAddPet"><i class="bi bi-plus-circle me-1"></i> Add Pet</button>
    </div>

    <div class="table-responsive shadow-sm bg-white rounded p-3 mt-2">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Photo</th>
            <th>Name</th>
            <th>Classification</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Adoption Status</th>
            <th>Date Sheltered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($conn, "SELECT * FROM pets ORDER BY date_sheltered DESC");
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>
                      <td>{$row['pet_id']}</td>
                      <td><img src='uploads/{$row['image_url']}' class='pet-thumb'></td>
                      <td>{$row['name']}</td>
                      <td>{$row['classification']}</td>
                      <td>{$row['breed']}</td>
                      <td>{$row['age']}</td>
                      <td>{$row['gender']}</td>
                      <td><span class='badge bg-".(
                          $row['adoption_status']=='Available' ? 'success' : (
                          $row['adoption_status']=='Adopted' ? 'secondary' : 'warning')
                        )."'>{$row['adoption_status']}</span></td>
                      <td>{$row['date_sheltered']}</td>
                      <td>
                        <a href='edit_pet.php?id={$row['pet_id']}' class='btn btn-sm btn-warning'><i class='bi bi-pencil'></i></a>
                        <a href='delete_pet.php?id={$row['pet_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this pet?\")'><i class='bi bi-trash'></i></a>
                      </td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='10' class='text-center'>No pets found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Pet Modal -->
  <div class="modal fade" id="addPetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header" style="background-color:#A9745B;color:white;">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Pet</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body bg-light">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Pet Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Classification</label>
                <select name="classification" class="form-select" required>
                  <option value="Dog">Dog</option>
                  <option value="Cat">Cat</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Breed</label>
                <input type="text" name="breed" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Age</label>
                <input type="text" name="age" class="form-control" placeholder="e.g. 2 years">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Gender</label>
                <select name="gender" class="form-select"><option>Male</option><option>Female</option></select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Color</label>
                <input type="text" name="color" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Health Status</label>
                <input type="text" name="health_status" class="form-control" placeholder="e.g. Vaccinated, Healthy">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Temperament</label>
                <input type="text" name="temperament" class="form-control" placeholder="e.g. Friendly, Calm">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Adoption Status</label>
                <select name="adoption_status" class="form-select">
                  <option>Available</option>
                  <option>Adopted</option>
                  <option>Pending</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Date Sheltered</label>
                <input type="date" name="date_sheltered" class="form-control" required>
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold">Photo</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-white">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" name="add_pet" class="btn btn-add px-4">Add Pet</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header" style="background-color:#A9745B; color:white;">
          <h5 class="modal-title" id="logoutModalLabel"><i class="bi bi-box-arrow-right"></i> Confirm Logout</h5>
        </div>
        <div class="modal-body text-center">
          <p class="fw-semibold mb-3" style="color:#333;">Are you sure you want to log out?</p>
          <div class="d-flex justify-content-center gap-3">
            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger px-4" id="confirmLogoutBtn">Yes</button>
          </div>
        </div>
      </div>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const profileBtn = document.getElementById("profileBtn");
const profileDropdown = document.getElementById("profileDropdown");
profileBtn.addEventListener("click", () => {
  profileDropdown.style.display = profileDropdown.style.display === "block" ? "none" : "block";
});
document.addEventListener("click", e => {
  if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
    profileDropdown.style.display = "none";
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("addPetModal");
  const openButton = document.getElementById("openAddPet");
  if (modalElement && openButton) {
    const addPetModal = new bootstrap.Modal(modalElement);
    openButton.addEventListener("click", () => addPetModal.show());
  }
});

// ✅ Logout redirect
document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
  window.location.href = 'logout.php';
});
</script>
</body>
</html>