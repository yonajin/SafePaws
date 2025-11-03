<?php
include '../config/db.php'; 
session_start();

// Redirect to login if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('location:login.php');
    exit();
}

// Ensure admin_name is set from session or database
if (!isset($_SESSION['admin_name']) && isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $result_admin = mysqli_query($conn, "SELECT admin_name FROM admin WHERE admin_id = '$admin_id'");
    if ($result_admin && $row_admin = mysqli_fetch_assoc($result_admin)) {
        $_SESSION['admin_name'] = $row_admin['admin_name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
} elseif (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = "Admin";
}

// Flash message handler
$message = $_SESSION['dashboard_message'] ?? '';
$message_type = $_SESSION['dashboard_message_type'] ?? '';
unset($_SESSION['dashboard_message'], $_SESSION['dashboard_message_type']);

// Handle profile update (CODE REMAINS UNCHANGED)
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id_to_update = $_SESSION['admin_id'] ?? 0;
    $msg = "";
    $success = true;

    if ($admin_id_to_update == 0) {
        $msg = "Error: Admin ID not found in session.";
        $success = false;
    }

    if ($success) {
        // --- Update name ---
        $sql_name = "UPDATE admin SET full_name = ? WHERE admin_id = ?";
        $stmt_name = mysqli_prepare($conn, $sql_name);
        if ($stmt_name) {
            mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $admin_id_to_update);
            if (mysqli_stmt_execute($stmt_name)) {
                $_SESSION['admin_name'] = $admin_name; 
                $msg .= "Profile name updated successfully! ";
            } else {
                $msg .= "Error updating profile name: " . mysqli_stmt_error($stmt_name);
                $success = false;
            }
            mysqli_stmt_close($stmt_name);
        } else {
            $msg .= "Database error on name update.";
            $success = false;
        }

        // --- Update password if provided ---
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql_pass = "UPDATE admin SET password = ? WHERE admin_id = ?";
                $stmt_pass = mysqli_prepare($conn, $sql_pass);
                if ($stmt_pass) {
                    mysqli_stmt_bind_param($stmt_pass, "si", $new_password, $admin_id_to_update);
                    if (mysqli_stmt_execute($stmt_pass)) {
                        $msg .= "Password updated successfully!";
                    } else {
                        $msg .= " Error updating password: " . mysqli_stmt_error($stmt_pass);
                        $success = false;
                    }
                    mysqli_stmt_close($stmt_pass);
                } else {
                    $msg .= "Database error on password update.";
                    $success = false;
                }
            } else {
                $msg .= " Error: Passwords do not match.";
                $success = false;
            }
        }
    }
    
    $_SESSION['dashboard_message'] = $msg;
    $_SESSION['dashboard_message_type'] = $success ? 'success' : 'danger';
    
    header("Location: admin_dashboard.php"); 
    exit();
}

// Pet Counts
$sql_pets = "SELECT COUNT(CASE WHEN adoption_status = 'Available' THEN 1 END) AS available_pets FROM pets";
$res_pets = mysqli_query($conn, $sql_pets);
$data_pets = mysqli_fetch_assoc($res_pets);

// Adoption Requests (Overall)
$sql_requests = "SELECT 
                    COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS pending_requests,
                    COUNT(CASE WHEN status = 'Approved' THEN 1 END) AS approved_adoptions,
                    COUNT(*) AS total_requests
                 FROM adoption_requests";
$res_requests = mysqli_query($conn, $sql_requests);
$data_requests = mysqli_fetch_assoc($res_requests);

// Monthly Adoption Requests
$sql_monthly_requests = "SELECT 
                            COUNT(CASE WHEN status = 'Approved' THEN 1 END) AS approved_monthly,
                            COUNT(*) AS total_monthly_requests
                         FROM adoption_requests
                         WHERE MONTH(request_date) = MONTH(CURRENT_DATE())
                         AND YEAR(request_date) = YEAR(CURRENT_DATE())";
$res_monthly_requests = mysqli_query($conn, $sql_monthly_requests);
$data_monthly_requests = mysqli_fetch_assoc($res_monthly_requests);

$sql_users = "SELECT COUNT(*) AS total_users FROM users"; 
$res_users = mysqli_query($conn, $sql_users);
$data_users = mysqli_fetch_assoc($res_users);

$sql_admins = "SELECT COUNT(*) AS total_admins FROM admin"; 
$res_admins = mysqli_query($conn, $sql_admins);
$data_admins = mysqli_fetch_assoc($res_admins);


// Assign variables
$available_pets = $data_pets['available_pets'] ?? 0;
$pending_requests = $data_requests['pending_requests'] ?? 0;
$approved_adoptions = $data_requests['approved_adoptions'] ?? 0;
$total_requests = $data_requests['total_requests'] ?? 0;
$total_users = $data_users['total_users'] ?? 0; 
$total_staff_admins = $data_admins['total_admins'] ?? 0; // New count


// New Monthly Variables
$approved_monthly = $data_monthly_requests['approved_monthly'] ?? 0;
$total_monthly_requests = $data_monthly_requests['total_monthly_requests'] ?? 0;
$current_month_name = date("F"); 
$monthly_pending = $total_monthly_requests - $approved_monthly;

// Adoption Rate (Overall)
$adoption_rate = $total_requests > 0 ? number_format(($approved_adoptions / $total_requests) * 100, 1) : 0;

// Adoption Rate (Monthly)
$monthly_adoption_rate = $total_monthly_requests > 0 ? number_format(($approved_monthly / $total_monthly_requests) * 100, 1) : 0;


// Data for Tabs (Recent Registrations)

// Query for Recent Regular Users
$sql_recent_users = "SELECT full_name, email, date_registered FROM users ORDER BY date_registered DESC LIMIT 5";
$res_recent_users = mysqli_query($conn, $sql_recent_users);
$recent_users = mysqli_fetch_all($res_recent_users, MYSQLI_ASSOC);

// Query for Recent Staff/Admins
$sql_recent_admins = "SELECT full_name, email, role, date_created FROM admin ORDER BY date_created DESC LIMIT 5";
$res_recent_admins = mysqli_query($conn, $sql_recent_admins);
$recent_admins = mysqli_fetch_all($res_recent_admins, MYSQLI_ASSOC);


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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

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
     /* Chart Styling */
    .chart-container { position: relative; height: 180px; width: 180px; margin: 0 auto; }
    .chart-center-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; }
    /* Tab Styling */
    .nav-tabs .nav-link.active {
        color: #A9745B; 
        background-color: #fff6f1;
        border-color: #fff6f1 #fff6f1 #A9745B; 
        font-weight: 600;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
        border-radius: 10px 10px 0 0;
    }
    .nav-tabs {
        border-bottom: 1px solid #eee;
    }
  </style>
</head>
<body>

<?php 
// Assuming admin_header.php contains the sidebar and topbar structure
include('../includes/admin_header.php'); 
?>

  <div class="main-content">
    <h3 class="mb-4 fw-bold" style="color:#A9745B;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></h3>

    <?php if ($message): // Display alert message if session message exists ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

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
          <h5>Total Users & Staff</h5>
          <div class="value"><?php echo $total_users + $total_staff_admins; ?></div>
          <p class="text-muted mb-1">Community & Admin size</p>
          <a href="users.php" class="text-decoration-none small text-secondary">Manage accounts →</a>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card p-0 h-100">
            <div class="card-header bg-white" style="border-top-left-radius:15px; border-top-right-radius:15px; padding-bottom: 0;">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-pane" type="button" role="tab" aria-controls="user-pane" aria-selected="true">
                            <i class="bi bi-people-fill"></i> Users (<?php echo $total_users; ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-pane" type="button" role="tab" aria-controls="admin-pane" aria-selected="false">
                            <i class="bi bi-person-gear"></i> Staff/Admin (<?php echo $total_staff_admins; ?>)
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="user-pane" role="tabpanel" aria-labelledby="user-tab" tabindex="0">
                    <div class="p-3">
                        <p class="text-muted small">Displaying the 5 most recently registered **Community Users**:</p>
                        <?php if (!empty($recent_users)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_users as $user): ?>
                                <?php
                                $time_ago = time() - strtotime($user['date_registered']);
                                $minutes = round(abs($time_ago) / 60);
                                $display_time = ($minutes < 60) ? "{$minutes}m ago" : date('M d, H:i', strtotime($user['date_registered']));
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0">
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                                        <span class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"><?php echo $display_time; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <p class="text-center text-muted mt-3">No community users registered yet.</p>
                        <?php endif; ?>
                        <a href="users.php" class="btn btn-sm btn-outline-secondary w-100 mt-3">View All Community Users</a>
                    </div>
                </div>

                <div class="tab-pane fade" id="admin-pane" role="tabpanel" aria-labelledby="admin-tab" tabindex="0">
                    <div class="p-3">
                        <p class="text-muted small">Displaying the 5 most recently added **Staff/Admin** accounts:</p>
                        <?php if (!empty($recent_admins)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recent_admins as $admin): ?>
                                <?php
                                $time_ago = time() - strtotime($admin['date_created']);
                                $minutes = round(abs($time_ago) / 60);
                                $display_time = ($minutes < 60) ? "{$minutes}m ago" : date('M d, H:i', strtotime($admin['date_created']));
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0">
                                    <div>
                                        <strong class="text-dark"><?php echo htmlspecialchars($admin['full_name']); ?></strong><br>
                                        <span class="small text-muted text-uppercase fw-semibold" style="color:#A9745B !important;">(<?php echo htmlspecialchars($admin['role']); ?>)</span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-secondary fw-normal"><?php echo $display_time; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <p class="text-center text-muted mt-3">No admin/staff accounts found yet.</p>
                        <?php endif; ?>
                        <a href="admins.php" class="btn btn-sm btn-outline-secondary w-100 mt-3">Manage All Staff/Admins</a>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-4 h-100 d-flex flex-column justify-content-between" style="background-color:#fff6f1;">
          <h5 class="fw-semibold text-center" style="color:#A9745B;">
              📅 <?php echo htmlspecialchars($current_month_name); ?> Adoption Rate
          </h5>
          
          <div class="text-center my-4">
              <div class="chart-container">
                  <canvas id="monthlyAdoptionRateChart"></canvas>
                  <div class="chart-center-text">
                      <div class="value" style="font-size: 32px; color:#A9745B; line-height: 1;"><?php echo $monthly_adoption_rate; ?>%</div>
                      <p class="text-muted fw-semibold small mb-0">Success</p>
                  </div>
              </div>
              <p class="text-muted fw-semibold mt-3 mb-0">Adoptions this month</p>
          </div>

          <div class="row text-center border-top pt-3">
              <div class="col-4">
                  <small class="text-muted d-block">Total Requests</small>
                  <strong class="text-dark"><?php echo $total_monthly_requests; ?></strong>
              </div>
              <div class="col-4">
                  <small class="text-muted d-block">Approved</small>
                  <strong class="text-success"><?php echo $approved_monthly; ?></strong>
              </div>
              <div class="col-4">
                  <small class="text-muted d-block">Pending</small>
                  <strong class="text-warning"><?php echo $monthly_pending; ?></strong>
              </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card p-4 h-100 d-flex flex-column justify-content-between" style="background-color:#fff6f1;">
          <h5 class="fw-semibold text-center" style="color:#A9745B;">
              📊 Overall Adoption Progress
          </h5>
          
          <div class="text-center my-4">
              <div class="chart-container">
                  <canvas id="overallAdoptionRateChart"></canvas>
                  <div class="chart-center-text">
                      <div class="value" style="font-size: 32px; color:#A9745B; line-height: 1;"><?php echo $adoption_rate; ?>%</div>
                      <p class="text-muted fw-semibold small mb-0">Adopted</p>
                  </div>
              </div>
              <p class="text-muted fw-semibold mt-3 mb-0">Successful Adoptions vs. Total Requests (All Time)</p>
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
      
      <div class="col-md-6">
        <div class="card p-4 h-100 d-flex flex-column justify-content-between">
          <h5 class="fw-semibold text-center" style="color:#A9745B;">
              ℹ️ Quick Summary
          </h5>
          <div class="text-center my-4">
              <div class="row g-2">
                  <div class="col-6">
                      <i class="bi bi-person-heart display-4" style="color: #A9745B;"></i>
                      <h4 class="mt-2 fw-bold"><?php echo $total_users; ?></h4>
                      <p class="text-muted mb-0">Total Community Users</p>
                  </div>
                   <div class="col-6">
                      <i class="bi bi-calendar-check display-4 text-success"></i>
                      <h4 class="mt-2 fw-bold"><?php echo $approved_monthly; ?></h4>
                      <p class="text-muted mb-0">Approved this Month</p>
                  </div>
                  <div class="col-6">
                      <i class="bi bi-house-door display-4 text-secondary"></i>
                      <h4 class="mt-2 fw-bold"><?php echo $available_pets; ?></h4>
                      <p class="text-muted mb-0">Pets Awaiting Home</p>
                  </div>
                  <div class="col-6">
                      <i class="bi bi-clipboard-data display-4 text-warning"></i>
                      <h4 class="mt-2 fw-bold"><?php echo $total_staff_admins; ?></h4>
                      <p class="text-muted mb-0">Total Staff/Admins</p>
                  </div>
              </div>
          </div>
          <a href="reports.php" class="btn btn-sm btn-save w-100 mt-3">View Detailed Reports</a>
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
    
    // PHP variables transferred to JS
    const approvedOverall = <?php echo json_encode($approved_adoptions); ?>;
    const totalOverall = <?php echo json_encode($total_requests); ?>;
    const approvedMonthly = <?php echo json_encode($approved_monthly); ?>;
    const totalMonthly = <?php echo json_encode($total_monthly_requests); ?>;

    // Calculate non-approved requests
    const nonApprovedOverall = totalOverall - approvedOverall;
    const nonApprovedMonthly = totalMonthly - approvedMonthly;

    // Default Chart Options (Doughnut)
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%', // Makes it a Doughnut chart
        plugins: {
            legend: {
                display: false 
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed !== null) {
                            label += context.parsed + ' requests';
                        }
                        return label;
                    }
                }
            }
        }
    };

    // --- Monthly Adoption Rate Chart ---
    const monthlyCtx = document.getElementById('monthlyAdoptionRateChart');
    if (monthlyCtx) {
        const monthlyData = {
            labels: ['Approved', 'Not Approved/Pending'],
            datasets: [{
                data: [approvedMonthly, nonApprovedMonthly],
                backgroundColor: ['#A9745B', '#E5E5E5'], // Brown for Approved, Light Gray for the rest
                hoverBackgroundColor: ['#8e5f47', '#D1D1D1'],
                borderWidth: 0,
            }]
        };

        new Chart(monthlyCtx, {
            type: 'doughnut',
            data: monthlyData,
            options: chartOptions
        });
    }

    // --- Overall Adoption Rate Chart ---
    const overallCtx = document.getElementById('overallAdoptionRateChart');
    if (overallCtx) {
        const overallData = {
            labels: ['Approved', 'Not Approved/Pending'],
            datasets: [{
                data: [approvedOverall, nonApprovedOverall],
                backgroundColor: ['#A9745B', '#E5E5E5'], 
                hoverBackgroundColor: ['#8e5f47', '#D1D1D1'],
                borderWidth: 0,
            }]
        };

        new Chart(overallCtx, {
            type: 'doughnut',
            data: overallData,
            options: chartOptions
        });
    }

  </script>
</body>
</html>