<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
session_start();

// Flash message handler: Load, then clear session variables
$message = $_SESSION['user_message'] ?? '';
$message_type = $_SESSION['user_message_type'] ?? '';
unset($_SESSION['user_message'], $_SESSION['user_message_type']);

// SECURITY CHECK & ADMIN SESSION SETUP

if (!isset($_SESSION['admin_name'])) {
    // Fallback: fetch/set admin name for the topbar
    $result_admin = mysqli_query($conn, "SELECT name FROM admin WHERE id = 1");
    if ($result_admin && $row_admin = mysqli_fetch_assoc($result_admin)) {
        $_SESSION['admin_name'] = $row_admin['name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
}

// === SECURE LOGIC: HANDLE USER UPDATE (NEW MODAL LOGIC) ===
if (isset($_POST['update_user'])) {
    // Sanitize and validate inputs
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $status = $_POST['status']; // Active or Inactive
    $success = false;
    $msg = '❌ Error updating user.';

    if ($user_id && !empty($full_name) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Use a Prepared Statement for the update
        $sql = "UPDATE users SET full_name = ?, email = ?, status = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            // "sssi" means string, string, string, integer
            mysqli_stmt_bind_param($stmt, "sssi", $full_name, $email, $status, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $msg = "✅ User ID {$user_id} updated successfully!";
                $success = true;
            } else {
                $msg = "❌ Database execution error: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $msg = "❌ Database preparation error: " . mysqli_error($conn);
        }
    } else {
        $msg = "❌ Invalid user data provided.";
    }

    // Set flash message and redirect to reload the page with updated data
    $_SESSION['user_message'] = $msg;
    $_SESSION['user_message_type'] = $success ? 'success' : 'danger';
    header("Location: users.php");
    exit();
}
// =============================================================

// === SECURE LOGIC: HANDLE USER DELETION ===
if (isset($_POST['delete_user'])) {
    // Ensure input is an integer
    $user_id = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);
    $success = false;
    $msg = '❌ Error deleting user.';
    
    if ($user_id) {
        $sql = "DELETE FROM users WHERE user_id = ?"; 
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (mysqli_stmt_execute($stmt)) {
                 $msg = "🗑️ User ID {$user_id} deleted successfully.";
                 $success = true;
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    $_SESSION['user_message'] = $msg;
    $_SESSION['user_message_type'] = $success ? 'success' : 'danger';
    header("Location: users.php");
    exit();
}

// === SECURE LOGIC: ADMIN PROFILE UPDATE (RETAINED) ===
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id = 1; 
    $msg = "";
    $success = true; 

    // --- Name Update Logic ---
    $sql_name = "UPDATE admin SET name = ? WHERE id = ?";
    $stmt_name = mysqli_prepare($conn, $sql_name);
    if ($stmt_name) {
        mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $admin_id);
        if (mysqli_stmt_execute($stmt_name)) {
            $_SESSION['admin_name'] = $admin_name; 
            $msg .= "Profile name updated successfully! ";
        } else {
            $msg .= "Error updating profile name: " . mysqli_stmt_error($stmt_name);
            $success = false;
        }
        mysqli_stmt_close($stmt_name);
    } else {
        $msg .= "Database error for name update. ";
        $success = false;
    }

    // --- Password Change Logic ---
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $sql_pass = "UPDATE admin SET password = ? WHERE id = ?";
            $stmt_pass = mysqli_prepare($conn, $sql_pass);
            if ($stmt_pass) {
                mysqli_stmt_bind_param($stmt_pass, "si", $new_password, $admin_id);
                if (mysqli_stmt_execute($stmt_pass)) {
                    $msg .= " Password updated successfully!";
                } else {
                    $msg .= " Error updating password: " . mysqli_stmt_error($stmt_pass);
                    $success = false;
                }
                mysqli_stmt_close($stmt_pass);
            } else {
                 $msg .= " Database error for password update. ";
                 $success = false;
            }
        } else {
             $msg .= " Error: Passwords do not match.";
             $success = false;
        }
    }
    
    $_SESSION['user_message'] = $msg;
    $_SESSION['user_message_type'] = $success ? 'success' : 'danger';
    header('location: users.php');
    exit();
}

// === DATA FETCH LOGIC WITH SEARCH (RETAINED AND FIXED) ===

$search_term = '';
$result = false; 

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $search_param = "%" . $search_term . "%"; 
    
    $sql_users = "SELECT * FROM users WHERE full_name LIKE ? OR email LIKE ? OR user_id LIKE ? ORDER BY date_registered DESC";
    
    $stmt = mysqli_prepare($conn, $sql_users); 
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $search_param);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
        } else {
            die("Error executing search statement: " . mysqli_stmt_error($stmt));
        }
    } else {
        die("Error preparing search statement: " . mysqli_error($conn));
    }

} else {
    // Default fetch all
    $sql_users = "SELECT * FROM users ORDER BY date_registered DESC";
    $result = mysqli_query($conn, $sql_users);

    if (!$result) {
        die("Error fetching users: " . mysqli_error($conn));
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management | SafePaws</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
.sidebar { height: calc(100vh - 30px); width: 240px; background-color: #fff; border-right:1px solid #ddd; position: fixed; top:15px; left:25px; display:flex; flex-direction: column; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:12px; padding:25px 0; }
.sidebar h2 { font-family: 'Quicksand', sans-serif; color:#A9745B; font-weight:700; font-size:28px; margin-bottom:25px; }
.sidebar .nav { width:100%; }
.sidebar .nav-link { color:#333; font-weight:500; padding:12px 19px; display:block; border-radius:8px; margin:2px 10px; transition:0.3s; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background-color:#f0e1d8; color:#A9745B; }
.sidebar .nav-link.text-danger { color:#dc3545 !important; }
.topbar { background-color:#A9745B; height:60px; display:flex; justify-content:flex-end; align-items:center; padding:0 30px; color:white; margin-left:288px; margin-right:23px; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,0.1); position:relative; }
.topbar i { font-size:26px; cursor:pointer; transition:0.2s; }
.topbar i:hover { opacity:0.85; }
.main-content { margin-left:260px; padding:30px; margin-top:20px; }
table { border-collapse: collapse; width:100%; }
th, td { text-align:center; padding:12px; vertical-align:middle; }
thead th { background-color:#f0e1d8; color:#A9745B; font-weight:600; }
tbody tr:nth-child(odd) { background-color:#fff; }
tbody tr:nth-child(even) { background-color:#f9f9f9; }
tbody tr:hover { background-color:#f1edea; transition:0.2s; }
.btn-action { margin:0 2px; }

/* --- Custom Button Styles (UPDATED) --- */
.btn-save { background-color: #A9745B; color: white; }
.btn-save:hover { background-color: #8e5f47; }

/* --- Profile Dropdown --- */
.profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
.profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: #333; }
.profile-dropdown a:hover { background-color: #f8f8f8; }

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
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
      <a href="users.php" class="nav-link active"><i class="bi bi-people me-2"></i> Users</a>
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

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0" style="color:#A9745B;">User Management</h3>
    
    <form method="GET" action="users.php" class="input-group" style="max-width: 400px;">
        <input type="text" name="search" class="form-control" placeholder="Search by ID, Name, or Email" value="<?php echo htmlspecialchars($search_term); ?>">
        <button class="btn btn-save" type="submit"><i class="bi bi-search"></i></button>
        <?php if (!empty($search_term)): ?>
            <a href="users.php" class="btn btn-outline-secondary" title="Clear Search"><i class="bi bi-x"></i></a>
        <?php endif; ?>
    </form>
</div>

<?php if ($message): // Display flash message if session message exists ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($search_term)): ?>
    <p class="text-muted">Showing results for: <strong><?php echo htmlspecialchars($search_term); ?></strong></p>
<?php endif; ?>

<div class="table-responsive shadow-sm bg-white rounded p-3">
<table class="table align-middle">
<thead>
<tr>
<th>ID</th>
<th>Full Name</th>
<th>Email</th>
<th>Status</th>
<th>Date Registered</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Status badge color logic
        $status_color = ($row['status'] == 'Active') ? 'success' : 'secondary';
        
        // Use data attributes to store all user data for the modal
        $data_attributes = "
            data-id='{$row['user_id']}'
            data-name='" . htmlspecialchars($row['full_name'], ENT_QUOTES) . "'
            data-email='" . htmlspecialchars($row['email'], ENT_QUOTES) . "'
            data-status='{$row['status']}'
            data-registered='{$row['date_registered']}'
        ";

        echo "<tr>
        <td>{$row['user_id']}</td>
        <td>{$row['full_name']}</td>
        <td>{$row['email']}</td>
        <td><span class='badge bg-{$status_color}'>{$row['status']}</span></td>
        <td>{$row['date_registered']}</td>
        <td>
            <button class='btn btn-sm btn-warning btn-action edit-user-btn' 
                    data-bs-toggle='modal' 
                    data-bs-target='#editUserModal' 
                    {$data_attributes}>
                <i class='bi bi-pencil'></i>
            </button>
            <button class='btn btn-sm btn-danger btn-action' data-bs-toggle='modal' data-bs-target='#deleteModal' data-id='{$row['user_id']}'>
                <i class='bi bi-trash'></i>
            </button>
        </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No registered users found.</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B; color:white;">
                <h5 class="modal-title" id="editUserModalLabel"><i class="bi bi-person-fill"></i> Edit User Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="users.php">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="hidden" name="update_user" value="1">

                <div class="modal-body text-start bg-light">
                    <div class="mb-3">
                        <label for="editFullName" class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="editFullName" name="full_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="editStatus" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="editStatus" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Date Registered</label>
                        <p class="form-control-plaintext" id="editDateRegistered"></p>
                    </div>
                </div>
                
                <div class="modal-footer bg-white d-flex justify-content-end align-items-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-save px-4"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B;color:white;">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="users.php"> 
                <div class="modal-body text-center py-4 bg-light">
                    <p class="fw-semibold mb-3">Are you sure you want to delete this user?</p>
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_user" class="btn btn-danger px-4">Delete</button>
                    </div>
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
// Logic to pass ID to delete modal (RETAINED)
const deleteModal = document.getElementById('deleteModal');
if(deleteModal) {
    deleteModal.addEventListener('show.bs.modal', function(event){
        const button = event.relatedTarget;
        const userId = button.getAttribute('data-id');
        document.getElementById('deleteUserId').value = userId;
    });
}

// Logic to populate the Edit User Modal (NEW)
const editUserModal = document.getElementById('editUserModal');
if(editUserModal) {
    editUserModal.addEventListener('show.bs.modal', function(event){
        const button = event.relatedTarget; // Button that triggered the modal
        
        // Retrieve data from the button's data attributes
        const userId = button.getAttribute('data-id');
        const fullName = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const status = button.getAttribute('data-status');
        const dateRegistered = button.getAttribute('data-registered');

        // Update the modal's fields
        document.getElementById('editUserId').value = userId;
        document.getElementById('editFullName').value = fullName;
        document.getElementById('editEmail').value = email;
        document.getElementById('editDateRegistered').textContent = dateRegistered;
        
        // Select the correct status option
        const statusSelect = document.getElementById('editStatus');
        for (let i = 0; i < statusSelect.options.length; i++) {
            if (statusSelect.options[i].value === status) {
                statusSelect.selectedIndex = i;
                break;
            }
        }
    });
}


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