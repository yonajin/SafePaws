<?php
include('../config/db.php');
session_start();

// --- Check if admin is logged in ---
if (!isset($_SESSION['admin_name'])) {
  $result = mysqli_query($conn, "SELECT admin_name FROM admin WHERE admin_id = 1");
  if ($result && $row = mysqli_fetch_assoc($result)) {
    $_SESSION['admin_name'] = $row['admin_name'];
  } else {
    $_SESSION['admin_name'] = "Admin";
  }
}

// --- ADD PET ---
if (isset($_POST['add_pet'])) {
  $name = trim($_POST['name']);
  $classification = trim($_POST['classification']);
  $age = trim($_POST['age']);
  $breed = trim($_POST['breed']);
  $gender = trim($_POST['gender']);
  $color = trim($_POST['color']);
  $health_status = trim($_POST['health_status']);
  $temperament = trim($_POST['temperament']);
  $adoption_status = trim($_POST['adoption_status']);
  $date_sheltered = trim($_POST['date_sheltered']);
  $image = $_FILES['image']['name'] ?? null;
  $target = "../uploads/" . basename($image);

  $sql = "INSERT INTO pets (name, classification, age, breed, gender, color, health_status, temperament, adoption_status, date_sheltered, image_url)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
  $stmt = mysqli_prepare($conn, $sql);

  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "sssssssssss", 
      $name, $classification, $age, $breed, $gender, $color, 
      $health_status, $temperament, $adoption_status, $date_sheltered, $image
    );
    if (mysqli_stmt_execute($stmt)) {
      if (!empty($image)) {
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
      }
      echo "<script>alert('✅ Pet added successfully!'); window.location='manage_pets.php';</script>";
    } else {
      echo "<script>alert('❌ Error adding pet');</script>";
    }
    mysqli_stmt_close($stmt);
  }
}

// --- DELETE PET ---
if (isset($_GET['delete'])) {
  $id = intval($_GET['delete']);
  $delete_sql = "DELETE FROM pets WHERE pet_id = ?";
  $stmt = mysqli_prepare($conn, $delete_sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('🗑️ Pet deleted successfully!'); window.location='manage_pets.php';</script>";
  }
  mysqli_stmt_close($stmt);
}

// --- UPDATE PET ---
if (isset($_POST['update_pet'])) {
  $pet_id = $_POST['pet_id'];
  $name = $_POST['name'];
  $classification = $_POST['classification'];
  $age = $_POST['age'];
  $breed = $_POST['breed'];
  $gender = $_POST['gender'];
  $color = $_POST['color'];
  $health_status = $_POST['health_status'];
  $temperament = $_POST['temperament'];
  $adoption_status = $_POST['adoption_status'];
  $date_sheltered = $_POST['date_sheltered'];
  
  $image = $_FILES['image']['name'] ?? '';
  $target = "../uploads/" . basename($image);

  if (!empty($image)) {
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
    $update_sql = "UPDATE pets SET name=?, classification=?, age=?, breed=?, gender=?, color=?, health_status=?, temperament=?, adoption_status=?, date_sheltered=?, image_url=? WHERE pet_id=?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sssssssssssi", $name, $classification, $age, $breed, $gender, $color, $health_status, $temperament, $adoption_status, $date_sheltered, $image, $pet_id);
  } else {
    $update_sql = "UPDATE pets SET name=?, classification=?, age=?, breed=?, gender=?, color=?, health_status=?, temperament=?, adoption_status=?, date_sheltered=? WHERE pet_id=?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "ssssssssssi", $name, $classification, $age, $breed, $gender, $color, $health_status, $temperament, $adoption_status, $date_sheltered, $pet_id);
  }

  if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('✅ Pet updated successfully!'); window.location='manage_pets.php';</script>";
  } else {
    echo "<script>alert('❌ Error updating pet');</script>";
  }
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Pets - SafePaws Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
  <style>
     body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
    .container { margin-top: 20px; }
    .main-content { margin-left:260px; padding:20px;}
    .table img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
    .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); font-size:12px; }
    .btn-custom { background-color: #f8a488; color: white; border: none; }
    .btn-custom:hover { background-color: #e78d73; }

    /* --- Sidebar --- */
   .sidebar { height: calc(100vh - 30px); width: 240px; background-color: #fff; border-right:1px solid #ddd; position: fixed; top:15px; left:25px; display:flex; flex-direction: column; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:12px; padding:25px 0; }
.sidebar h2 { font-family: 'Quicksand', sans-serif; color:#A9745B; font-weight:700; font-size:28px; margin-bottom:25px; }
.sidebar .nav { width:100%; }
.sidebar .nav-link { color:#333; font-weight:500; padding:12px 19px; display:block; border-radius:8px; margin:2px 10px; transition:0.3s; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background-color:#f0e1d8; color:#A9745B; }
.sidebar .nav-link.text-danger { color:#dc3545 !important; }
    /* --- Topbar & Profile Dropdown --- */
.topbar { background-color:#A9745B; height:60px; display:flex; justify-content:flex-end; align-items:center; padding:0 30px; color:white; margin-left:288px; margin-right:23px; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,0.1); position:relative; }
.topbar i { font-size:26px; cursor:pointer; transition:0.2s; }
.topbar i:hover { opacity:0.85; }
.profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
.profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: #333; }
.profile-dropdown a:hover { background-color: #f8f8f8; }

/* --- Custom Button Styles (UPDATED) --- */
.btn-save { background-color: #A9745B; color: white; }
.btn-save:hover { background-color: #8e5f47; }

/* === MODAL CORNERS === */
.modal-header {
  border-top-left-radius: 0.75rem !important;
  border-top-right-radius: 0.75rem !important;
}
  </style>
</head>
<body>

<div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
      <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link active"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
      <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
      <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
    </nav>
</div>

<div class="topbar">
  <i id="profileBtn" class="bi bi-person-circle"></i>
  <div id="profileDropdown" class="profile-dropdown">
      <a href="#" data-bs-toggle="modal" data-bs-target="#adminProfileModal" class="view-profile-link"><i class="bi bi-person"></i> View Profile</a>
      <hr class="m-0">
      <a href="#" class="text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" id="dropdownLogoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>
</div>

<div class="main-content">

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="fw-bold m-0" style="color:#A9745B;">Manage Pets </h3>
  </div>


  <!-- Add Pet -->
  <div class="card p-4 mb-4">
    <h5>Add New Pet</h5>
    <form method="POST" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Pet Name" required></div>
        <div class="col-md-4"><input type="text" name="classification" class="form-control" placeholder="Classification" required></div>
        <div class="col-md-4"><input type="text" name="age" class="form-control" placeholder="Age" required></div>
        <div class="col-md-4"><input type="text" name="breed" class="form-control" placeholder="Breed"></div>
        <div class="col-md-4">
          <select name="gender" class="form-control">
            <option>Male</option><option>Female</option>
          </select>
        </div>
        <div class="col-md-4"><input type="text" name="color" class="form-control" placeholder="Color"></div>
        <div class="col-md-6"><input type="text" name="health_status" class="form-control" placeholder="Health Status"></div>
        <div class="col-md-6"><input type="text" name="temperament" class="form-control" placeholder="Temperament"></div>
        <div class="col-md-6">
          <select name="adoption_status" class="form-control">
            <option value="Available">Available</option><option value="Adopted">Adopted</option>
          </select>
        </div>
        <div class="col-md-6"><input type="date" name="date_sheltered" class="form-control" required></div>
        <div class="col-md-12"><input type="file" name="image" class="form-control"></div>
      </div>
      <button type="submit" name="add_pet" class="btn btn-custom mt-3">Add Pet</button>
    </form>
  </div>

  <!-- Table -->
  <div class="card p-4">
    <h5>Pet Records</h5>
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th><th>Image</th><th>Name</th><th>Classification</th><th>Age</th>
          <th>Breed</th><th>Gender</th><th>Color</th><th>Health</th><th>Temperament</th>
          <th>Status</th><th>Date Sheltered</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $pets = mysqli_query($conn, "SELECT * FROM pets ORDER BY pet_id DESC");
        if (mysqli_num_rows($pets) > 0) {
          while ($row = mysqli_fetch_assoc($pets)) {
            ?>
            <tr>
              <td><?= $row['pet_id'] ?></td>
              <td><img src="../assets/images/<?= htmlspecialchars($row['image_url']) ?>" alt=""></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['classification']) ?></td>
              <td><?= htmlspecialchars($row['age']) ?></td>
              <td><?= htmlspecialchars($row['breed']) ?></td>
              <td><?= htmlspecialchars($row['gender']) ?></td>
              <td><?= htmlspecialchars($row['color']) ?></td>
              <td><?= htmlspecialchars($row['health_status']) ?></td>
              <td><?= htmlspecialchars($row['temperament']) ?></td>
              <td><?= htmlspecialchars($row['adoption_status']) ?></td>
              <td><?= htmlspecialchars($row['date_sheltered']) ?></td>
              <td>
                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['pet_id'] ?>">Edit</button>
                <a href="?delete=<?= $row['pet_id'] ?>" onclick="return confirm('Delete this pet?');" class="btn btn-sm btn-danger">Delete</a>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $row['pet_id'] ?>" tabindex="-1">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Edit Pet</h5></div>
                  <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                      <input type="hidden" name="pet_id" value="<?= $row['pet_id'] ?>">
                      <div class="row g-3">
                        <div class="col-md-4"><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>"></div>
                        <div class="col-md-4"><input type="text" name="classification" class="form-control" value="<?= htmlspecialchars($row['classification']) ?>"></div>
                        <div class="col-md-4"><input type="text" name="age" class="form-control" value="<?= htmlspecialchars($row['age']) ?>"></div>
                        <div class="col-md-4"><input type="text" name="breed" class="form-control" value="<?= htmlspecialchars($row['breed']) ?>"></div>
                        <div class="col-md-4"><input type="text" name="gender" class="form-control" value="<?= htmlspecialchars($row['gender']) ?>"></div>
                        <div class="col-md-4"><input type="text" name="color" class="form-control" value="<?= htmlspecialchars($row['color']) ?>"></div>
                        <div class="col-md-6"><input type="text" name="health_status" class="form-control" value="<?= htmlspecialchars($row['health_status']) ?>"></div>
                        <div class="col-md-6"><input type="text" name="temperament" class="form-control" value="<?= htmlspecialchars($row['temperament']) ?>"></div>
                        <div class="col-md-6"><input type="text" name="adoption_status" class="form-control" value="<?= htmlspecialchars($row['adoption_status']) ?>"></div>
                        <div class="col-md-6"><input type="date" name="date_sheltered" class="form-control" value="<?= htmlspecialchars($row['date_sheltered']) ?>"></div>
                        <div class="col-md-12"><input type="file" name="image" class="form-control"></div>
                      </div>
                      <button type="submit" name="update_pet" class="btn btn-custom mt-3">Save Changes</button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
            <?php
          }
        } else {
          echo "<tr><td colspan='13' class='text-center'>No pets found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg" style="border-radius:20px; overflow:hidden;">
            <div class="modal-header text-white" style="background-color:#A9745B; border-bottom:none;">
                <h5 class="modal-title w-100 text-center"><i class="bi bi-box-arrow-right"></i> Confirm Logout</h5>
            </div>
            <div class="modal-body text-center py-4" style="background-color:#FFF8F3;">
                <p class="fw-semibold mb-4" style="color:#333;">Are you sure you want to log out?</p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger px-4 rounded-pill" id="confirmLogoutBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adminProfileModal" tabindex="-1" aria-labelledby="adminProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B; color:white;">
                <h5 class="modal-title" id="adminProfileModalLabel"><i class="bi bi-person-circle"></i> Admin Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="update_admin_profile" value="1">
                <div class="modal-body text-center bg-light">
                    <i class="bi bi-person-circle" style="font-size: 60px; color: #A9745B;"></i>
                    <h5 class="mt-2 mb-4 fw-bold"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h5>
                    
                    <div class="mb-3 text-start">
                        <label for="adminNameInput" class="form-label fw-semibold">Admin Name</label>
                        <input type="text" name="admin_name" id="adminNameInput" class="form-control" value="<?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>" required>
                    </div>

                    <h6 class="mt-4 mb-2 text-start fw-bold">Change Password</h6>
                    
                    <div class="mb-3 text-start">
                        <label for="newPasswordInput" class="form-label">New Password</label>
                        <input type="password" name="new_password" id="newPasswordInput" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="mb-3 text-start">
                        <label for="confirmPasswordInput" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirmPasswordInput" class="form-control" placeholder="Confirm new password">
                    </div>
                </div>
                
                <div class="modal-footer bg-white d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Logout Confirmation Logic
document.getElementById('confirmLogoutBtn').addEventListener('click', function(){
    window.location.href = 'admin_logout.php';
});

// Profile Dropdown Logic
const profileBtn = document.getElementById("profileBtn");
const profileDropdown = document.getElementById("profileDropdown");
const viewProfileLink = document.querySelector('.view-profile-link'); 

if (profileBtn) {
    profileBtn.addEventListener("click", () => {
      profileDropdown.style.display = profileDropdown.style.display === "block" ? "none" : "block";
    });
}

if (viewProfileLink) {
    viewProfileLink.addEventListener('click', () => {
        profileDropdown.style.display = 'none';
    });
}

document.addEventListener("click", e => {
  if (profileBtn && !profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
    profileDropdown.style.display = "none";
  }
});
</script>

</body>
</html>
