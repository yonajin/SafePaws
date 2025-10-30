<?php
include '../config/db.php'; // Ensure this path is correct for your setup
session_start();

// SECURITY CHECK & ADMIN SESSION SETUP
if (!isset($_SESSION['admin_name'])) {
    // Attempt to set a default admin name if not logged in
    $result_admin = mysqli_query($conn, "SELECT name FROM admin WHERE id = 1");
    if ($result_admin && $row_admin = mysqli_fetch_assoc($result_admin)) {
        $_SESSION['admin_name'] = $row_admin['name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
}

// FETCH DYNAMIC DASHBOARD DATA

// --- Pet Counts (Requires 'pets' table) ---
$sql_pets = "SELECT 
                COUNT(CASE WHEN adoption_status = 'Available' THEN 1 END) AS available_pets
             FROM pets";
$res_pets = mysqli_query($conn, $sql_pets);
$data_pets = mysqli_fetch_assoc($res_pets);

// --- Adoption Request Counts & TOTAL ---
$sql_requests = "SELECT 
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS pending_requests,
                    COUNT(CASE WHEN status = 'Approved' THEN 1 END) AS approved_adoptions,
                    COUNT(*) AS total_requests
                 FROM adoption_requests";
$res_requests = mysqli_query($conn, $sql_requests);
$data_requests = mysqli_fetch_assoc($res_requests);

// --- User Count (Requires 'users' table) ---
$sql_users = "SELECT COUNT(*) AS total_users FROM users WHERE status != 'Admin'";
$res_users = mysqli_query($conn, $sql_users);
$data_users = mysqli_fetch_assoc($res_users);


// Assign variables
$available_pets = $data_pets['available_pets'] ?? 0;
$pending_requests = $data_requests['pending_requests'] ?? 0;
$approved_adoptions = $data_requests['approved_adoptions'] ?? 0;
$total_requests = $data_requests['total_requests'] ?? 0;
$total_users = $data_users['total_users'] ?? 0; 

// --- CALCULATE ADOPTION RATE ---
$adoption_rate = 0;
if ($total_requests > 0) {
    $adoption_rate = number_format(($approved_adoptions / $total_requests) * 100, 1);
}


// ADMIN PROFILE UPDATE HANDLER (For Top Bar Modal)
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id = 1; 
    $msg = "";
    
    // --- Name Update Logic (Using Prepared Statements for security) ---
    $sql_name = "UPDATE admin SET name = ? WHERE id = ?";
    $stmt_name = mysqli_prepare($conn, $sql_name);
    if ($stmt_name) {
        mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $admin_id);
        if (mysqli_stmt_execute($stmt_name)) {
            $_SESSION['admin_name'] = $admin_name; 
            $msg .= "Profile name updated successfully! ";
        } else {
            $msg .= "Error updating profile name: " . mysqli_stmt_error($stmt_name);
        }
        mysqli_stmt_close($stmt_name);
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
                }
                mysqli_stmt_close($stmt_pass);
            }
        } else {
             $msg .= " Error: Passwords do not match.";
        }
    }
    
    echo "<script>alert('{$msg}'); window.location='admin_dashboard.php';</script>"; 
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SafePaws Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
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
    /* --- Main Content --- */
    .main-content { margin-left: 260px; padding: 30px; margin-top: 8px; }
    .card { border: none; border-radius: 15px; padding: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .card h5 { font-weight: 600; color: #A9745B; }
    .value { font-size: 28px; font-weight: 700; color: #333; }
    .chart-placeholder { background-color: #fff; border-radius: 15px; padding: 25px; height: 300px; display: flex; align-items: center; justify-content: center; color: #a9745b; font-weight: 500; border: 1px solid #eee; }
    /* --- Custom Button Styles (Consistent) --- */
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
      <a href="admin_dashboard.php" class="nav-link active"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
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
    <h3 class="mb-4 fw-bold" style="color:#A9745B;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h3>

    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card text-center">
          <h5>Pending Requests</h5>
          <div class="value"><?php echo $pending_requests; ?></div>
          <p class="text-muted mb-1">Awaiting review</p>
          <a href="adoption_requests.php" class="text-decoration-none small text-secondary">View requests →</a>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center">
          <h5>Available Pets</h5>
          <div class="value"><?php echo $available_pets; ?></div>
          <p class="text-muted mb-1">Ready for adoption</p>
          <a href="manage_pets.php" class="text-decoration-none small text-secondary">Manage pets →</a>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card text-center">
          <h5>Approved Adoptions</h5>
          <div class="value"><?php echo $approved_adoptions; ?></div>
          <p class="text-muted mb-1">Successful adoptions</p>
          <a href="reports.php" class="text-decoration-none small text-secondary">Analytics →</a>
        </div>
      </div>
      
      <div class="col-md-3">
        <div class="card text-center">
          <h5>Registered Users</h5>
          <div class="value"><?php echo $total_users; ?></div>
          <p class="text-muted mb-1">Community size</p>
          <a href="users.php" class="text-decoration-none small text-secondary">Manage users →</a>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="card p-0 h-100">
          <h5 class="card-header fw-semibold" style="color:#A9745B; background-color:#fff6f1; border-top-left-radius:15px; border-top-right-radius:15px;">
              👤 Recent User Registrations
          </h5>
          <div class="p-3">
              <p class="text-muted small">Displaying the 5 most recently registered users:</p>
              <?php
              // FIX APPLIED: Using date_registered instead of created_at
              $sql_recent_users = "SELECT full_name, email, date_registered FROM users WHERE status != 'Admin' ORDER BY date_registered DESC LIMIT 5";
              $res_recent_users = mysqli_query($conn, $sql_recent_users);
              
              if ($res_recent_users && mysqli_num_rows($res_recent_users) > 0) {
                  echo '<ul class="list-group list-group-flush">';
                  while ($user = mysqli_fetch_assoc($res_recent_users)) {
                      // FIX APPLIED: Using $user['date_registered']
                      $time_ago = time() - strtotime($user['date_registered']);
                      $minutes = round(abs($time_ago) / 60);
                      $display_time = ($minutes < 60) ? "{$minutes}m ago" : date('M d, H:i', strtotime($user['date_registered']));

                      echo '<li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0">';
                      echo '<div><strong class="text-dark">'. htmlspecialchars($user['full_name']) . '</strong><br><span class="small text-muted">' . htmlspecialchars($user['email']) . '</span></div>';
                      echo '<span class="badge bg-secondary-subtle text-secondary fw-normal">' . $display_time . '</span>';
                      echo '</li>';
                  }
                  echo '</ul>';
              } else {
                  echo '<p class="text-center text-muted mt-3">No user registrations yet.</p>';
              }
              ?>
              <a href="users.php" class="btn btn-sm btn-outline-secondary w-100 mt-3">View All Users</a>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4 h-100 d-flex flex-column justify-content-between" style="background-color:#fff6f1;">
          <h5 class="fw-semibold text-center" style="color:#A9745B;">
              📈 Overall Adoption Rate
          </h5>
          
          <div class="text-center my-4">
              <div class="value" style="font-size: 64px; color:#A9745B;"><?php echo $adoption_rate; ?>%</div>
              <p class="text-muted fw-semibold">Successful Adoptions vs. Total Requests</p>
          </div>

          <div class="row text-center border-top pt-3">
              <div class="col-4">
                  <small class="text-muted d-block">Total Requests</small>
                  <strong class="text-dark"><?php echo $total_requests; ?></strong>
              </div>
              <div class="col-4">
                  <small class="text-muted d-block">Approved</small>
                  <strong class="text-success"><?php echo $approved_adoptions; ?></strong>
              </div>
              <div class="col-4">
                  <small class="text-muted d-block">Pending</small>
                  <strong class="text-warning"><?php echo $pending_requests; ?></strong>
              </div>
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
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'logout.php';
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