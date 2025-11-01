<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
session_start();

// --- SECURITY CHECK & SESSION SETUP ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch Admin Name for UI display
if (!isset($_SESSION['admin_name']) && isset($_SESSION['user_id'])) {
    $user_id_check = $_SESSION['user_id'];
    // FIX: Using 'users' table and 'full_name' column
    $result = mysqli_query($conn, "SELECT full_name FROM users WHERE user_id = '$user_id_check'");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $_SESSION['admin_name'] = $row['full_name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
}

$msg = "";
$upload_msg = "";

// --- SECURE LOGIC: HANDLE ADD PET (Prepared Statement) ---

if (isset($_POST['add_pet'])) {
    // Collect data (11 fields + image)
    $name = trim($_POST['name']);
    $classification = trim($_POST['classification']); 
    $age = trim($_POST['age']);
    $breed = trim($_POST['breed']);
    $gender = trim($_POST['gender']);
    $color = trim($_POST['color'] ?? ''); 
    $health_status = trim($_POST['health_status'] ?? ''); 
    $temperament = trim($_POST['temperament'] ?? ''); 
    $adoption_status = trim($_POST['adoption_status']); 
    $date_sheltered = trim($_POST['date_sheltered']);
    $description = trim($_POST['description'] ?? ''); // Unified field
    
    $image_filename = 'default.jpg'; // Default image if upload fails or is skipped

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_FILES['image']['name'])) {
        $file_name = $_FILES['image']['name'];
        $file_tmp = $_FILES['image']['tmp_name'];
        
        // Sanitize filename to prevent directory traversal or script injection
        $file_name_safe = preg_replace('/[^A-Za-z0-9.\-_]/', '', basename($file_name));
        $image_filename = time() . "_" . $file_name_safe; 

        // FIX: Correct relative path for upload from admin/ folder
        $target_dir = "../uploads/";
        $target_file = $target_dir . $image_filename;
        
        if (move_uploaded_file($file_tmp, $target_file)) {
            $upload_msg = "Image uploaded successfully. ";
        } else {
            $upload_msg = "Warning: Image upload failed. Check folder permissions. Using default image. ";
            $image_filename = 'default.jpg';
        }
    }

    // Prepare SQL INSERT statement with 11 fields + image_url
    // NOTE: Assuming the pet table uses an auto-increment column named `pet_id`
    $sql = "INSERT INTO pets (name, classification, age, breed, gender, color, health_status, temperament, adoption_status, date_sheltered, description, image_url) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // Bind parameters (11 strings + 1 string for image_url)
        mysqli_stmt_bind_param($stmt, "ssssssssssss", 
            $name, $classification, $age, $breed, $gender, $color, 
            $health_status, $temperament, $adoption_status, $date_sheltered, $description, $image_filename
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = "✅ Pet added successfully! ";
        } else {
            $msg = "❌ Error adding pet to database: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $msg = "❌ Database query preparation failed for insertion.";
    }
    
    // Combine messages and redirect
    $final_msg = $upload_msg . $msg;
    echo "<script>alert('{$final_msg}'); window.location='manage_pets.php';</script>";
    exit();
}

// --- SECURE LOGIC: ADMIN PROFILE UPDATE (for modal) ---
// Note: Logic consolidated using 'users' table schema (user_id, full_name, password)

if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $user_id_to_update = $_SESSION['user_id']; 
    $msg_profile = "";
    
    // Update Name
    $sql_name = "UPDATE users SET full_name = ? WHERE user_id = ?"; 
    $stmt_name = mysqli_prepare($conn, $sql_name);
    if ($stmt_name) {
        mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $user_id_to_update);
        if (mysqli_stmt_execute($stmt_name)) {
            $_SESSION['admin_name'] = $admin_name; 
            $msg_profile .= "Profile name updated successfully! ";
        } else {
            $msg_profile .= "Error updating profile name: " . mysqli_stmt_error($stmt_name);
        }
        mysqli_stmt_close($stmt_name);
    } 

    // Update Password
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $sql_pass = "UPDATE users SET password = ? WHERE user_id = ?"; 
            $stmt_pass = mysqli_prepare($conn, $sql_pass);
            if ($stmt_pass) {
                mysqli_stmt_bind_param($stmt_pass, "si", $new_password, $user_id_to_update);
                if (mysqli_stmt_execute($stmt_pass)) {
                    $msg_profile .= " Password updated successfully!";
                } else {
                    $msg_profile .= " Error updating password: " . mysqli_stmt_error($stmt_pass);
                }
                mysqli_stmt_close($stmt_pass);
            }
        } else {
             $msg_profile .= " Error: Passwords do not match.";
        }
    }
    
    echo "<script>alert('{$msg_profile}'); window.location='manage_pets.php';</script>";
    exit();
}

// --- DATA FETCH: PET LIST (with Filtering) ---

$filter_status = $_GET['status'] ?? 'All';
$search_query = $_GET['search'] ?? '';

$sql = "SELECT * FROM pets WHERE 1=1";
$params = [];
$types = "";

if ($filter_status != 'All') {
    $sql .= " AND adoption_status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

if (!empty($search_query)) {
    // Search by name or breed
    $sql .= " AND (name LIKE ? OR breed LIKE ?)";
    $params[] = "%" . $search_query . "%";
    $params[] = "%" . $search_query . "%";
    $types .= "ss";
}

$sql .= " ORDER BY pet_id DESC"; // FIX: Using correct primary key 'pet_id'

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    if (!empty($params)) {
        $bind_params = [$stmt, $types];
        foreach ($params as &$param) {
            $bind_params[] = &$param;
        }
        call_user_func_array('mysqli_stmt_bind_param', $bind_params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
} else {
    $result = false;
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
.sidebar { height: calc(100vh - 30px); width: 240px; background-color: #fff; border-right:1px solid #ddd; position: fixed; top:15px; left:25px; display:flex; flex-direction: column; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:12px; padding:25px 0; }
.sidebar h2 { font-family: 'Quicksand', sans-serif; color:#A9745B; font-weight:700; font-size:28px; margin-bottom:25px; }
.sidebar .nav { width:100%; }
.sidebar .nav-link { color:#333; font-weight:500; padding:12px 19px; display:block; border-radius:8px; margin:2px 10px; transition:0.3s; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background-color:#f0e1d8; color:#A9745B; }
.sidebar .nav-link.text-danger { color:#dc3545 !important; }

/* --- Topbar --- */
.topbar { background-color:#A9745B; height:60px; display:flex; justify-content:flex-end; align-items:center; padding:0 30px; color:white; margin-left:288px; margin-right:23px; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,0.1); position:relative; }
.topbar i { font-size:26px; cursor:pointer; transition:0.2s; }
.topbar i:hover { opacity:0.85; }

/* --- Main Content --- */
.main-content { margin-left:260px; padding:30px; margin-top:20px; }

/* --- Table Styles --- */
.table th, .table td { vertical-align: middle; text-align: center; }
.pet-img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
thead th { background-color:#f0e1d8; color:#A9745B; font-weight:600; }
tbody tr:nth-child(odd) { background-color:#fff; }
tbody tr:nth-child(even) { background-color:#f9f9f9; }
tbody tr:hover { background-color:#f1edea; transition:0.2s; }

/* --- Custom Buttons/Input --- */
.btn-primary-custom { background-color: #A9745B; border-color: #A9745B; }
.btn-primary-custom:hover { background-color: #8e5f47; border-color: #8e5f47; }
.btn-save { background-color: #A9745B; color: white; }
.btn-save:hover { background-color: #8e5f47; }
.form-select-custom { border-radius: 0.5rem; }

/* --- Profile Dropdown --- */
.profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
.profile-dropdown a { display:block; padding:10px 15px; text-decoration:none; color:#333; }
.profile-dropdown a:hover { background:#f8f8f8; }

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
<h3 class="fw-bold mb-4" style="color:#A9745B;">🐕‍ Pet Management</h3>

<div class="d-flex justify-content-between align-items-center mb-4">
    <button class="btn btn-primary-custom px-4" data-bs-toggle="modal" data-bs-target="#addPetModal">
        <i class="bi bi-plus-circle me-2"></i> Add New Pet
    </button>
    <div class="d-flex gap-3">
        <form method="GET" class="d-flex" id="filterForm">
            <select name="status" class="form-select form-select-custom me-2" onchange="document.getElementById('filterForm').submit()">
                <option value="All" <?php if ($filter_status == 'All') echo 'selected'; ?>>All Statuses</option>
                <option value="Available" <?php if ($filter_status == 'Available') echo 'selected'; ?>>Available</option>
                <option value="Pending" <?php if ($filter_status == 'Pending') echo 'selected'; ?>>Pending</option>
                <option value="Adopted" <?php if ($filter_status == 'Adopted') echo 'selected'; ?>>Adopted</option>
            </select>
        </form>
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control" placeholder="Search by name or breed..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-primary-custom ms-2" type="submit"><i class="bi bi-search"></i></button>
            <?php if (!empty($filter_status) && $filter_status != 'All') echo "<input type='hidden' name='status' value='{$filter_status}'>"; ?>
        </form>
    </div>
</div>

<div class="table-responsive shadow-sm bg-white rounded p-3">
<table class="table align-middle">
<thead>
<tr>
<th>ID</th>
<th>Photo</th>
<th>Name</th>
<th>Type</th>
<th>Breed</th>
<th>Age</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
if($result && mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        // Status badge color logic
        $status_color = match ($row['adoption_status']) {
            'Available' => 'success',
            'Pending' => 'warning',
            default => 'danger',
        };
        
        echo "<tr>
        <td>{$row['pet_id']}</td>
        <td><img src='../uploads/{$row['image_url']}' alt='{$row['name']}' class='pet-img'></td>
        <td>{$row['name']}</td>
        <td>{$row['classification']}</td>
        <td>{$row['breed']}</td>
        <td>{$row['age']}</td>
        <td><span class='badge bg-{$status_color}'>{$row['adoption_status']}</span></td>
        <td>
            <a href='edit_pet.php?id={$row['pet_id']}' class='btn btn-sm btn-info text-white'><i class='bi bi-pencil'></i></a>
            <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deletePetModal' data-id='{$row['pet_id']}' data-name='{$row['name']}'><i class='bi bi-trash'></i></button>
        </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='8'>No pets found matching your criteria.</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>

<div class="modal fade" id="addPetModal" tabindex="-1" aria-labelledby="addPetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B; color:white;">
                <h5 class="modal-title" id="addPetModalLabel"><i class="bi bi-plus-circle me-2"></i> Add New Pet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="add_pet" value="1">
                <div class="modal-body p-4 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 text-secondary">Basic Info</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pet Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Classification <span class="text-danger">*</span></label>
                                <select name="classification" class="form-select" required>
                                    <option value="" disabled selected>Select type</option>
                                    <option value="Dog">Dog</option>
                                    <option value="Cat">Cat</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Breed</label>
                                <input type="text" name="breed" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Age</label>
                                <input type="text" name="age" class="form-control" placeholder="e.g. 1 year">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 text-secondary">Status & Health</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Adoption Status <span class="text-danger">*</span></label>
                                <select name="adoption_status" class="form-select" required>
                                    <option value="Available">Available</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Adopted">Adopted</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date Sheltered <span class="text-danger">*</span></label>
                                <input type="date" name="date_sheltered" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Color</label>
                                <input type="text" name="color" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Health Status</label>
                                <input type="text" name="health_status" class="form-control" placeholder="e.g. Vaccinated, Neutered">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Temperament</label>
                                <input type="text" name="temperament" class="form-control" placeholder="e.g. Friendly, Playful">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 text-secondary">Details & Photo</h6>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pet Photo</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save px-4">Add Pet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deletePetModal" tabindex="-1" aria-labelledby="deletePetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deletePetModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4 bg-light">
                <p class="fw-semibold mb-3">Are you sure you want to delete <strong id="petNamePlaceholder"></strong> (ID: <span id="petIdPlaceholder"></span>)?</p>
                <p class="text-danger small">This action cannot be undone and will delete the database record and associated image.</p>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger px-4">Delete Pet</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header" style="background-color:#A9745B; color:white;">
          <h5 class="modal-title" id="logoutModalLabel"><i class="bi bi-box-arrow-right"></i> Confirm Logout</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
document.addEventListener("DOMContentLoaded", function() {
    
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

    // Delete Modal Logic
    const deletePetModal = document.getElementById('deletePetModal');
    if(deletePetModal) {
        deletePetModal.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const petId = button.getAttribute('data-id');
            const petName = button.getAttribute('data-name');
            
            document.getElementById('petIdPlaceholder').innerText = petId;
            document.getElementById('petNamePlaceholder').innerText = petName;
            
            // Set the href for the confirmation button to link to delete_pet.php
            document.getElementById('confirmDeleteBtn').href = `delete_pet.php?id=${petId}`;
        });
    }

    // Logout Confirmation Logic
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'admin_logout.php';
    });
});
</script>
</body>
</html>