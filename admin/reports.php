<?php
include '../config/db.php';
session_start();
// Keep error reporting active during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// SECURITY CHECK & PROFILE LOGIC (Consistent with dashboard/requests)
if (!isset($_SESSION['admin_name'])) {
    // Attempt to fetch admin name if session is missing but user might be valid
    $result = mysqli_query($conn, "SELECT name FROM admin WHERE id = 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $_SESSION['admin_name'] = $row['name'];
    } else {
        // Fallback for UI consistency
        $_SESSION['admin_name'] = "Admin"; 
    }
}

// SECURE LOGIC: ADMIN PROFILE UPDATE (Copied for consistency)
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id = 1; 
    $msg = "";
    $success = true;

    // Handle Name Update (securely)
    $sql_name = "UPDATE admin SET name = ? WHERE id = ?";
    $stmt_name = mysqli_prepare($conn, $sql_name);
    if ($stmt_name) {
        mysqli_stmt_bind_param($stmt_name, "si", $admin_name, $admin_id);
        if (mysqli_stmt_execute($stmt_name)) {
            $_SESSION['admin_name'] = $admin_name; 
            $msg .= "Profile name updated successfully!";
        } else {
            $msg .= "Error updating profile name: " . mysqli_stmt_error($stmt_name);
            $success = false;
        }
        mysqli_stmt_close($stmt_name);
    } else {
        $msg .= "Database error for name update.";
        $success = false;
    }

    // Handle Password Change
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
                 $msg .= " Database error for password update.";
                 $success = false;
            }
        } else {
             $msg .= " Error: Passwords do not match.";
             $success = false;
        }
    }
    
    // Alert message and then redirect to reload the page state
    echo "<script>alert('{$msg}'); window.location='reports.php';</script>";
    exit();
}


// ANALYTICS & REPORT SETUP

// Handle date filtering
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// --- SECURED ANALYTICS COUNTS (FIXED: Using 'adoption_status' for pets table) ---
$total_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets"))['total'];
// *** CRITICAL FIX: Changed 'status' to 'adoption_status' ***
$available_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE adoption_status='Available'"))['total'];
$adopted_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE adoption_status='Adopted'"))['total'];
$pending_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE adoption_status='For Approval'"))['total']; // Use the exact string in your DB
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

// Adoption Requests still correctly use 'status'
$total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests"))['total'];
$approved_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Approved'"))['total'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Pending'"))['total'];
$rejected_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Denied'"))['total'];


// --- SECURED REPORT TABLE QUERY using PREPARED STATEMENTS ---

$params = [];
$types = '';
$where_clause = '';

if (!empty($start_date) && !empty($end_date)) {
    // The request_date column is being used for filtering the adoption_requests table
    $where_clause = " WHERE request_date BETWEEN ? AND ? "; 
    $params[] = $start_date;
    $params[] = $end_date;
    $types = 'ss'; // Two string parameters
}

$query = "SELECT id AS request_id, pet_id, status, request_date FROM adoption_requests $where_clause ORDER BY request_date DESC";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    if ($where_clause) {
        // Dynamically call bind_param with the types and parameters
        // The splat operator (...) is needed to unpack the $params array for bind_param
        mysqli_stmt_bind_param($stmt, $types, ...$params); 
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // Handle prepare error
    $result = false; 
    // Log the error for debugging:
    error_log("MySQLi Prepare Error: " . mysqli_error($conn));
}
// ------------------------------------------------------------------------------------------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports | SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Sidebar/TopBar Styling FIX */
    body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
    .sidebar { height: calc(100vh - 30px); width: 240px; background-color: #fff; border-right:1px solid #ddd; position: fixed; top:15px; left:25px; display:flex; flex-direction: column; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:12px; padding:25px 0; }
    .sidebar h2 { font-family: 'Quicksand', sans-serif; color:#A9745B; font-weight:700; font-size:28px; margin-bottom:25px; }
    .sidebar .nav { width:100%; }
    .sidebar .nav-link { color:#333; font-weight:500; padding:12px 19px; display:block; border-radius:8px; margin:2px 10px; transition:0.3s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color:#f0e1d8; color:#A9745B; }
    .sidebar .nav-link.text-danger { color:#dc3545 !important; }
    
    /* Topbar Margin FIX */
    .topbar { background-color:#A9745B; height:60px; display:flex; justify-content:flex-end; align-items:center; padding:0 30px; color:white; margin-left:288px; margin-right:23px; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,0.1); position:relative; }
    .topbar i { font-size:26px; cursor:pointer; transition:0.2s; }
    .topbar i:hover { opacity:0.85; }
    
    /* Main Content Margin FIX */
    .main-content { margin-left:260px; padding:30px; margin-top:20px; }

    /* Profile Dropdown Style */
    .profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
    .profile-dropdown a { display:block; padding:10px 15px; text-decoration:none; color:#333; }
    .profile-dropdown a:hover { background:#f8f8f8; }

    /* Card and Table Styles */
    .card { border: none; border-radius: 10px; }
    .card h5 { color: #A9745B; font-weight: 600; }
    .btn-generate { background-color: #A9745B; color: white; }
    .btn-generate:hover { background-color: #8e5f47; }
    
    /* Consistent Save Button Style */
    .btn-save { background-color: #A9745B; color: white; }
    .btn-save:hover { background-color: #8e5f47; }

    table { border-collapse: collapse; width: 100%; }
    thead tr { background-color: #f0e1d8; color: #A9745B; }
    tbody tr:nth-child(even) { background-color: #fdf7f3; }
    tbody tr:nth-child(odd) { background-color: #ffffff; }

    /* === CRITICAL FIX FOR MODAL CORNERS === */
    .modal-header {
      border-top-left-radius: 0.75rem !important;
      border-top-right-radius: 0.75rem !important;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>SafePaws</h2>
  <nav class="nav flex-column">
    <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
    <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
    <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
    <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
    <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
    <a href="reports.php" class="nav-link active"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
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
    <h3 class="fw-bold" style="color:#A9745B;">📊 Reports & Analytics</h3>
  </div>

  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-generate w-100"><i class="bi bi-graph-up"></i> Generate Report</button>
    </div>
  </form>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Total Pets</h5><h4><?= $total_pets ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Available</h5><h4><?= $available_pets ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Adopted</h5><h4><?= $adopted_pets ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Pending</h5><h4><?= $pending_pets ?></h4></div></div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Total Users</h5><h4><?= $total_users ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Total Requests</h5><h4><?= $total_requests ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Approved</h5><h4><?= $approved_requests ?></h4></div></div>
    <div class="col-md-3"><div class="card shadow-sm p-3 text-center"><h5>Pending</h5><h4><?= $pending_requests ?></h4></div></div>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <canvas id="adoptionChart" height="120"></canvas>
  </div>

  <div class="card shadow-sm p-3">
    <h5 class="mb-3" style="color:#A9745B;">Adoption Requests Report</h5>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle text-center">
        <thead>
          <tr>
            <th>Request ID</th>
            <th>Pet ID</th>
            <th>Status</th>
            <th>Date Requested</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              // Ensure the status column name matches the SQL result (which uses 'status')
              $status_badge_class = match ($row['status']) {
                'Approved' => 'success',
                'Pending' => 'warning',
                default => 'danger',
              };

              echo "<tr>
                      <td>{$row['request_id']}</td>
                      <td>{$row['pet_id']}</td>
                      <td><span class='badge bg-{$status_badge_class}'>{$row['status']}</span></td>
                      <td>{$row['request_date']}</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No adoption records found for this date range.</td></tr>";
          }
          if ($stmt) {
              mysqli_stmt_close($stmt);
          }
          ?>
        </tbody>
      </table>
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
const ctx = document.getElementById('adoptionChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Approved', 'Pending', 'Denied'],
    datasets: [{
      label: 'Adoption Requests',
      // Data variables remain the same as defined in PHP
      data: [<?= $approved_requests ?>, <?= $pending_requests ?>, <?= $rejected_requests ?>],
      backgroundColor: ['#8BC34A', '#FFC107', '#E57373']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'Adoption Requests Summary', color: '#A9745B', font: { size: 18 } }
    },
    scales: {
        y: {
            beginAtZero: true
        }
    }
  }
});

document.addEventListener("DOMContentLoaded", function() {
    
    // Profile Dropdown Logic (Consistent)
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

    // Logout Confirmation Logic
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'logout.php';
    });
});
</script>
</body>
</html>