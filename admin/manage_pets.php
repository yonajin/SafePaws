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
  <style>
    body { background-color: #fff6f1; font-family: 'Poppins', sans-serif; }
    .container { margin-top: 40px; }
    .main-content {
  margin-left: 280px; /* same or slightly larger than sidebar width */
  padding: 20px;
}
    .table img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; }
    .card { border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .btn-custom { background-color: #f8a488; color: white; border: none; }
    .btn-custom:hover { background-color: #e78d73; }

    /* --- Sidebar --- */
    .sidebar { height: calc(100vh - 30px); width: 240px; background-color: #ffffff; border-right: 1px solid #ddd; position: fixed; top: 15px; left: 25px; display: flex; flex-direction: column; align-items: center; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border-radius: 12px; padding: 25px 0; }
    .sidebar h2 { font-family: 'Quicksand', sans-serif; color: #A9745B; font-weight: 700; font-size: 28px; margin-bottom: 25px; }
    .sidebar .nav { width: 100%; }
    .sidebar .nav-link { color: #333; font-weight: 500; padding: 12px 19px; display: block; transition: all 0.3s ease; border-radius: 8px; margin: 2px 10px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0e1d8; color: #A9745B; }
    /* --- Topbar & Profile Dropdown --- */
    .topbar { background-color: #A9745B; height: 60px; display: flex; justify-content: flex-end; align-items: center; padding: 0 30px; color: white; margin-left: 288px; margin-right: 23px; border-radius: 15px; box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); position: relative; }
    .topbar i { font-size: 26px; cursor: pointer; transition: 0.2s ease; }
    .topbar i:hover { opacity: 0.85; }
    .profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
    .profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: #333; }
    .profile-dropdown a:hover { background-color: #f8f8f8; }
  </style>
</head>
<body>

<?php include('../includes/admin_header.php'); ?>

<div class="main-content">

<div class="container">
  <h2 class="text-center mb-4">Manage Pets</h2>

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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
