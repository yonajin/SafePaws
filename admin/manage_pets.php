<?php
include('../config/db.php');
session_start();

// --- Redirect if admin is not logged in ---
if (!isset($_SESSION['admin_id'])) {
    header('location: ../login.php');
    exit();
}

// --- Ensure admin_name is set ---
if (!isset($_SESSION['admin_name']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $result_admin = mysqli_query($conn, "SELECT full_name FROM admin WHERE admin_id = '$admin_id'");
    if ($result_admin && $row_admin = mysqli_fetch_assoc($result_admin)) {
        $_SESSION['admin_name'] = $row_admin['full_name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
} elseif (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = "Admin";
}

// Flash message handler
$message = $_SESSION['manage_pets_message'] ?? '';
$message_type = $_SESSION['manage_pets_message_type'] ?? '';
unset($_SESSION['manage_pets_message'], $_SESSION['manage_pets_message_type']);

// SECURE LOGIC: HANDLE ADMIN PROFILE UPDATE AND PASSWORD CHANGE (Copied from care_tips logic)
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id = $_SESSION['admin_id'] ?? 0;
    $msg = "";
    $success = true;

    if ($admin_id == 0) { $msg = "Error: Admin ID not found."; $success = false; }
    
    // Handle Name Update (securely)
    if ($success) {
        $sql_name = "UPDATE admin SET full_name = ? WHERE admin_id = ?";
        $stmt_name = mysqli_prepare($conn, $sql_name);
        if ($stmt_name) {
            mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $admin_id);
            if (mysqli_stmt_execute($stmt_name)) {
                $_SESSION['admin_name'] = $admin_name; 
                $msg .= "Profile name updated successfully!";
            } else { $msg .= "Error updating profile name."; $success = false; }
            mysqli_stmt_close($stmt_name);
        } else { $msg .= "Database error for name update."; $success = false; }
    }

    // Handle Password Change
    if ($success && !empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $sql_pass = "UPDATE admin SET password = ? WHERE admin_id = ?";
            $stmt_pass = mysqli_prepare($conn, $sql_pass);
            if ($stmt_pass) {
                mysqli_stmt_bind_param($stmt_pass, "si", $new_password, $admin_id);
                if (mysqli_stmt_execute($stmt_pass)) {
                    $msg .= " Password updated successfully!";
                } else { $msg .= " Error updating password."; $success = false; }
                mysqli_stmt_close($stmt_pass);
            } else { $msg .= " Database error for password update."; $success = false; }
        } else { $msg .= " Error: Passwords do not match."; $success = false; }
    }
    
    // Set message and redirect back to the current page
    $_SESSION['manage_pets_message'] = $msg;
    $_SESSION['manage_pets_message_type'] = $success ? 'success' : 'danger';
    header('location: manage_pets.php');
    exit();
}


// SECURE LOGIC: FETCH PET DATA (For Edit Modal via AJAX/Fetch)
if (isset($_GET['action']) && $_GET['action'] === 'fetch' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    $pet_id = $_GET['id'];

    $sql = "SELECT * FROM pets WHERE pet_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    $response = ['error' => 'Pet not found.'];

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $pet_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $pet = mysqli_fetch_assoc($result);

        if ($pet) {
            $response = $pet; 
        }
        mysqli_stmt_close($stmt);
    }
    
    echo json_encode($response);
    exit; 
}


// --- FUNCTIONAL LOGIC: ADD PET ---
if (isset($_POST['add_pet'])) {
    // ... (Your existing logic for fetching post data) ...
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
    $success = false;

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssssssss", 
            $name, $classification, $age, $breed, $gender, $color, 
            $health_status, $temperament, $adoption_status, $date_sheltered, $image
        );
        if (mysqli_stmt_execute($stmt)) {
            if (!empty($image)) {
                move_uploaded_file($_FILES['image']['tmp_name'], $target);
            }
            $_SESSION['manage_pets_message'] = '✅ Pet added successfully!';
            $_SESSION['manage_pets_message_type'] = 'success';
            $success = true;
        } else {
            $_SESSION['manage_pets_message'] = '❌ Error adding pet: ' . mysqli_stmt_error($stmt);
            $_SESSION['manage_pets_message_type'] = 'danger';
        }
        mysqli_stmt_close($stmt);
    }
    header('location: manage_pets.php');
    exit();
}

// --- FUNCTIONAL LOGIC: DELETE PET ---
if (isset($_POST['delete_pet_id'])) {
    $id = intval($_POST['delete_pet_id']);
    
    // Get image file path to delete from server
    $sql_select = "SELECT image_url FROM pets WHERE pet_id = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select);
    $image_to_delete = null;
    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $id);
        mysqli_stmt_execute($stmt_select);
        $result = mysqli_stmt_get_result($stmt_select);
        $row = mysqli_fetch_assoc($result);
        $image_to_delete = $row['image_url'] ?? null;
        mysqli_stmt_close($stmt_select);
    }

    // Delete pet record
    $delete_sql = "DELETE FROM pets WHERE pet_id = ?";
    $stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        // 3. Delete image file if it exists
        if ($image_to_delete && file_exists("../uploads/" . $image_to_delete)) {
            unlink("../uploads/" . $image_to_delete);
        }
        $_SESSION['manage_pets_message'] = '🗑️ Pet deleted successfully!';
        $_SESSION['manage_pets_message_type'] = 'success';
    } else {
        $_SESSION['manage_pets_message'] = '❌ Error deleting pet.';
        $_SESSION['manage_pets_message_type'] = 'danger';
    }
    mysqli_stmt_close($stmt);
    header('location: manage_pets.php');
    exit();
}


// --- FUNCTIONAL LOGIC: UPDATE PET ---
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
    
    $new_image = $_FILES['image']['name'] ?? '';
    $target = "../uploads/" . basename($new_image);
    $success = false;
    $image_update_clause = "";
    $params = [$name, $classification, $age, $breed, $gender, $color, $health_status, $temperament, $adoption_status, $date_sheltered];
    $types = "ssssssssss"; 

    if (!empty($new_image)) {
        // Prepare to update image
        $image_update_clause = ", image_url = ?";
        $params[] = $new_image;
        $types .= "s";

        // Handle file upload
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
           $_SESSION['manage_pets_message'] = '❌ Error uploading new image. Update failed.';
           $_SESSION['manage_pets_message_type'] = 'danger';
           header('location: manage_pets.php');
           exit;
       }
        // OPTIONAL: Delete old image from server (requires additional SELECT query here)
   }

    // Append pet_id to the parameters array
   $params[] = $pet_id;
   $types .= "i";

   $update_sql = "UPDATE pets SET name=?, classification=?, age=?, breed=?, gender=?, color=?, health_status=?, temperament=?, adoption_status=?, date_sheltered=?{$image_update_clause} WHERE pet_id=?";
   $stmt = mysqli_prepare($conn, $update_sql);

   if ($stmt) {
    $bind_args = array_merge([$stmt, $types], $params);
    call_user_func_array('mysqli_stmt_bind_param', $bind_args);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['manage_pets_message'] = '✅ Pet updated successfully!';
        $_SESSION['manage_pets_message_type'] = 'success';
    } else {
        $_SESSION['manage_pets_message'] = '❌ Error updating pet.';
        $_SESSION['manage_pets_message_type'] = 'danger';
    }
    mysqli_stmt_close($stmt);
}
header('location: manage_pets.php');
exit();
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

        <?php if ($message): // Display alert message if session message exists ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card p-4">
            <h5><i class="bi bi-list-columns-reverse me-1"></i> Pet Records</h5>
            <div class="table-responsive">
                <table class="table table-striped align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th><th>Image</th><th>Name</th><th>Class</th><th>Age</th>
                            <th>Breed</th><th>Gender</th><th>Status</th><th>Date Sheltered</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pets = mysqli_query($conn, "SELECT * FROM pets ORDER BY pet_id DESC");
                        if (mysqli_num_rows($pets) > 0) {
                            while ($row = mysqli_fetch_assoc($pets)) {
                                $status_class = $row['adoption_status'] == 'Available' ? 'badge bg-success' : 'badge bg-secondary';
                                $image_path = empty($row['image_url']) ? 'placeholder.jpg' : htmlspecialchars($row['image_url']);
                                ?>
                                <tr>
                                    <td><?= $row['pet_id'] ?></td>
                                    <td><img src="../uploads/<?= $image_path ?>" alt="<?= htmlspecialchars($row['name']) ?>"></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['classification']) ?></td>
                                    <td><?= htmlspecialchars($row['age']) ?></td>
                                    <td><?= htmlspecialchars($row['breed']) ?></td>
                                    <td><?= htmlspecialchars($row['gender']) ?></td>
                                    <td><span class="<?= $status_class ?>"><?= htmlspecialchars($row['adoption_status']) ?></span></td>
                                    <td><?= htmlspecialchars($row['date_sheltered']) ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm edit-pet-btn" data-id="<?= $row['pet_id']; ?>" data-id="<?= $row['pet_id'] ?>"><i class='bi bi-pencil'></i></button>
                                        <button class="btn btn-sm btn-danger delete-pet-btn" data-id="<?= $row['pet_id'] ?>"><i class='bi bi-trash'></i></button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>No pets found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addPetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header" style="background-color:#A9745B;color:white;">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Pet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_pet" value="1">
                    <div class="modal-body bg-light">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label small text-muted">Name</label><input type="text" name="name" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label small text-muted">Classification</label><input type="text" name="classification" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label small text-muted">Age</label><input type="text" name="age" class="form-control" required></div>
                                
                            <div class="col-md-4"><label class="form-label small text-muted">Breed</label><input type="text" name="breed" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label small text-muted">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male">Male</option><option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label small text-muted">Color</label><input type="text" name="color" class="form-control"></div>

                            <div class="col-md-6"><label class="form-label small text-muted">Health Status</label><input type="text" name="health_status" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label small text-muted">Temperament</label><input type="text" name="temperament" class="form-control"></div>

                            <div class="col-md-4"><label class="form-label small text-muted">Adoption Status</label>
                                <select name="adoption_status" class="form-select">
                                    <option value="Available">Available</option><option value="Adopted">Adopted</option>
                                </select>
                            </div>
                            <div class="col-md-4"><label class="form-label small text-muted">Date Sheltered</label><input type="date" name="date_sheltered" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label small text-muted">Pet Image</label><input type="file" name="image" class="form-control"></div>
                        </div>
                    </div>

                    <div class="modal-footer bg-white d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-add px-4"><i class="bi bi-save me-1"></i> Save Pet Record</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- DELETE PET MODAL -->
    <div class="modal fade" id="deletePetModal" tabindex="-1" aria-labelledby="deletePetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header" style="background-color:#A9745B; color:white;">
                    <h5 class="modal-title" id="deletePetModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="confirmDeleteForm">
                    <input type="hidden" name="delete_pet_id" id="delete_pet_id_input">
                    <div class="modal-body text-center">
                        <p class="fw-semibold mb-3" style="color:#333;">Are you sure you want to permanently delete this Pet?</p>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No</button>
                            <button type="submit" class="btn btn-danger px-4">Yes, Delete It</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
      </div>    

    <!-- EDIT PET MODAL -->
    <div class="modal fade" id="editPetModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header" style="background-color:#A9745B;color:white;">
            <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Pet Details</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>

          <form method="POST" enctype="multipart/form-data" id="editPetForm">
            <input type="hidden" name="update_pet" value="1">
            <input type="hidden" name="pet_id" id="edit_pet_id">

            <div class="modal-body bg-light">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label small text-muted">Name</label>
                  <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Classification</label>
                  <input type="text" name="classification" id="edit_classification" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Age</label>
                  <input type="text" name="age" id="edit_age" class="form-control" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label small text-muted">Breed</label>
                  <input type="text" name="breed" id="edit_breed" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Gender</label>
                  <select name="gender" id="edit_gender" class="form-select">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Color</label>
                  <input type="text" name="color" id="edit_color" class="form-control">
                </div>

                <div class="col-md-6">
                  <label class="form-label small text-muted">Health Status</label>
                  <input type="text" name="health_status" id="edit_health_status" class="form-control">
                </div>
                <div class="col-md-6">
                  <label class="form-label small text-muted">Temperament</label>
                  <input type="text" name="temperament" id="edit_temperament" class="form-control">
                </div>

                <div class="col-md-4">
                  <label class="form-label small text-muted">Adoption Status</label>
                  <select name="adoption_status" id="edit_adoption_status" class="form-select">
                    <option value="Available">Available</option>
                    <option value="Adopted">Adopted</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Date Sheltered</label>
                  <input type="date" name="date_sheltered" id="edit_date_sheltered" class="form-control" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small text-muted">Change Image (optional)</label>
                  <input type="file" name="image" class="form-control">
                </div>
              </div>
            </div>

            <div class="modal-footer bg-white d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success px-4">
                <i class="bi bi-save me-1"></i> Save Changes
              </button>
            </div>
          </form>
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
    document.addEventListener("DOMContentLoaded", () => {

        const deleteTipModal = new bootstrap.Modal(document.getElementById('deletePetModal'));
        const deleteIdInput = document.getElementById('delete_pet_id_input');
    
        document.querySelectorAll('.delete-pet-btn').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const tipId = this.getAttribute('data-id');
                deleteIdInput.value = tipId;
                deleteTipModal.show();
            });
        });

    
      // Open edit modal and load data
        const editPetModal = new bootstrap.Modal(document.getElementById('editPetModal'));
        const pubUnpubContainer = document.getElementById('publish-unpublish-container');

        document.querySelectorAll(".edit-pet-btn").forEach(button => {
            button.addEventListener("click", function() {
              const petId = this.dataset.id;
              
              fetch(`get_pets.php?id=${petId}`)
                .then(res => res.json())
                .then(data => {
                  if (data.success) {
                    const pet = data.pet;
                    document.getElementById("edit_pet_id").value = pet.pet_id;
                    document.getElementById("edit_name").value = pet.name;
                    document.getElementById("edit_classification").value = pet.classification;
                    document.getElementById("edit_age").value = pet.age;
                    document.getElementById("edit_breed").value = pet.breed;
                    document.getElementById("edit_gender").value = pet.gender;
                    document.getElementById("edit_color").value = pet.color;
                    document.getElementById("edit_health_status").value = pet.health_status;
                    document.getElementById("edit_temperament").value = pet.temperament;
                    document.getElementById("edit_adoption_status").value = pet.adoption_status;
                    document.getElementById("edit_date_sheltered").value = pet.date_sheltered;
                    new bootstrap.Modal(document.getElementById("editPetModal")).show();
                  } else {
                    alert("Failed to fetch pet details.");
                  }
                })
                .catch(() => alert("Error loading pet details."));
            });
        });

    });

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