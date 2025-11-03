<?php
include '../config/db.php';
session_start();

// Redirect to login if admin is not logged in
if (!isset($_SESSION['admin_id'])) {
    header('location: ../login.php');
    exit();
}

// Ensure admin_name is set 
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
$message = $_SESSION['reports_message'] ?? '';
$message_type = $_SESSION['reports_message_type'] ?? '';
unset($_SESSION['reports_message'], $_SESSION['reports_message_type']);


// --- SECURE LOGIC: ADMIN PROFILE UPDATE ---
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
        // Update name 
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

        // Update password if provided 
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
    
    $_SESSION['reports_message'] = $msg;
    $_SESSION['reports_message_type'] = $success ? 'success' : 'danger';
    
    header("Location: reports.php"); 
    exit();
}


// --- ANALYTICS & REPORT SETUP ---

// Fixed All-Time Counts (General Overview)
$total_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets"))['total'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'] ?? 0;
$total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests"))['total'] ?? 0;
$all_time_approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Approved'"))['total'] ?? 0;


// Handle date filtering for the CHART and TABLE
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// --- Filtered Counts (For Chart and Filter Summary) ---
$params = [];
$types = '';
$where_clause = '';

if (!empty($start_date) && !empty($end_date)) {
    // Add 23:59:59 to the end date to include the entire day in the filter range
    $adjusted_end_date = $end_date . ' 23:59:59';
    $where_clause = " WHERE request_date BETWEEN ? AND ? "; 
    $params[] = $start_date;
    $params[] = $adjusted_end_date;
    $types = 'ss'; 
}

// Build base query for filtered counts
$base_query = "SELECT COUNT(*) AS total FROM adoption_requests ";

// Retrieve filtered data for the chart and summary cards
$filtered_approved = 0;
$filtered_pending = 0;
$filtered_rejected = 0;

if (!empty($start_date) && !empty($end_date)) {
    // Use the filter for card and chart data
    $stmt_app = mysqli_prepare($conn, $base_query . $where_clause . " AND status='Approved'");
    $stmt_pen = mysqli_prepare($conn, $base_query . $where_clause . " AND status='Pending'");
    $stmt_rej = mysqli_prepare($conn, $base_query . $where_clause . " AND status='Denied'");
    
    $stmts = [$stmt_app, $stmt_pen, $stmt_rej];
    $results = [];

    foreach ($stmts as $stmt_item) {
        if ($stmt_item) {
            if ($where_clause) {
                mysqli_stmt_bind_param($stmt_item, $types, ...$params);
            }
            mysqli_stmt_execute($stmt_item);
            $res = mysqli_stmt_get_result($stmt_item);
            $results[] = mysqli_fetch_assoc($res)['total'] ?? 0;
            mysqli_stmt_close($stmt_item);
        } else {
            $results[] = 0;
            error_log("MySQLi Prepare Error in Filtered Counts: " . mysqli_error($conn));
        }
    }
    
    list($filtered_approved, $filtered_pending, $filtered_rejected) = $results;
    $filtered_total = $filtered_approved + $filtered_pending + $filtered_rejected;


} else {
    // If no filter is applied, use the All-Time Counts for the Chart/Table Summary
    $filtered_approved = $all_time_approved;
    $filtered_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Pending'"))['total'] ?? 0;
    $filtered_rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Denied'"))['total'] ?? 0;
    $filtered_total = $total_requests; 
}


// --- SECURED REPORT TABLE QUERY using PREPARED STATEMENTS ---

$query = "SELECT request_id, pet_id, status, request_date FROM adoption_requests $where_clause ORDER BY request_date DESC";
$stmt = mysqli_prepare($conn, $query);

$report_data = [];

if ($stmt) {
    if ($where_clause) {
        mysqli_stmt_bind_param($stmt, $types, ...$params); 
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
             $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
    } else {
        error_log("MySQLi Execute Error: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
} else {
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
  <link rel="stylesheet" href="../assets/css/reports.css">
</head>
<body>

<div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
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
  <h3 class="fw-bold mb-4" style="color:#A9745B;">Reports & Analytics</h3>

  <?php if ($message): // Display alert message if session message exists ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
      <?php echo htmlspecialchars($message); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <h5 class="fw-bold mb-3" style="color:#A9745B;">Global Overview (All Time)</h5>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card-global shadow-sm p-3 text-center">
        <h5 class="text-muted">Total Pets</h5>
        <h4><i class="bi bi-box-seam me-1"></i> <?= $total_pets ?></h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-global shadow-sm p-3 text-center">
        <h5 class="text-muted">Total Users</h5>
        <h4><i class="bi bi-people me-1"></i> <?= $total_users ?></h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-global shadow-sm p-3 text-center">
        <h5 class="text-muted">Total Requests</h5>
        <h4><i class="bi bi-clipboard-data me-1"></i> <?= $total_requests ?></h4>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card-global shadow-sm p-3 text-center">
        <h5 class="text-muted">Successful Adoptions</h5>
        <h4><i class="bi bi-house-door-fill me-1" style="color:#8BC34A;"></i> <?= $all_time_approved ?></h4>
      </div>
    </div>
  </div>

  <h5 class="fw-bold mb-4 mt-4" style="color:#A9745B;">Adoption Activity Filter</h5>
  <form method="GET" class="row g-3 mb-4 card p-3 shadow-sm align-items-end">

    <div class="col-md-8">
      <label class="form-label fw-semibold text-muted">Select Date Range:</label>
      <div class="input-group">
        
        <span class="input-group-text bg-white border-end-0 fw-semibold text-muted">Start Date:</span>
        <input type="date" name="start_date" id="startDateInput" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        
        <span class="input-group-text bg-white border-start-0 border-end-0">-</span>
        
        <span class="input-group-text bg-white border-start-0 fw-semibold text-muted">End Date:</span>
        <input type="date" name="end_date" id="endDateInput" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
      </div>
    </div>
    
    <div class="col-md-4 d-flex">
      <button type="submit" class="btn btn-generate w-100"><i class="bi bi-funnel-fill me-1"></i> Generate Report</button>
    </div>
  </form>

  <h5 class="fw-bold mb-3 mt-4" style="color:#A9745B;">Summary for Filtered Period (Total: <?= $filtered_total ?>)</h5>
  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center" style="background-color:#E8F5E9;">
        <h5 class="text-success">Approved</h5>
        <h4><?= $filtered_approved ?></h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center" style="background-color:#FFF8E1;">
        <h5 class="text-warning">Pending</h5>
        <h4><?= $filtered_pending ?></h4>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm p-3 text-center" style="background-color:#FFEBEE;">
        <h5 class="text-danger">Denied</h5>
        <h4><?= $filtered_rejected ?></h4>
      </div>
    </div>
  </div>

  <div class="card shadow-sm p-4 mb-4">
    <canvas id="adoptionChart" height="100"></canvas>
      </div>
  

  <div class="card shadow-sm p-3">
    <div class="card-header-main p-3" style="border-radius:10px 10px 0 0;">
        <i class="bi bi-table me-1"></i> Adoption Requests Detail 
        <span class="small text-muted float-end">
            <?= !empty($start_date) ? "Report from $start_date to $end_date" : "Showing All Time Requests" ?>
        </span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle text-center mb-0">
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
          if (!empty($report_data)) {
            foreach ($report_data as $row) {
              $status_badge_class = match ($row['status']) {
                'Approved' => 'success',
                'Pending' => 'warning',
                default => 'danger', // This covers 'Denied'
              };

              echo "<tr>
                      <td>{$row['request_id']}</td>
                      <td>{$row['pet_id']}</td>
                      <td><span class='badge bg-{$status_badge_class}'>{$row['status']}</span></td>
                      <td>{$row['request_date']}</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No adoption requests found for this date range.</td></tr>";
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
// PHP variables injected here for the chart data (using the filtered counts)
const approvedCount = <?= $filtered_approved ?>;
const pendingCount = <?= $filtered_pending ?>;
const deniedCount = <?= $filtered_rejected ?>;

// --- Chart.js Initialization ---
const ctx = document.getElementById('adoptionChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Approved', 'Pending', 'Denied'],
    datasets: [{
      label: 'Adoption Requests',
      data: [approvedCount, pendingCount, deniedCount],
      backgroundColor: [
        '#8BC34A', // Green for Approved
        '#FFC107', // Amber for Pending
        '#E57373'  // Red for Denied
      ],
      borderRadius: 5,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { 
        display: true, 
        text: 'Adoption Requests Status Summary', 
        color: '#A9745B', 
        font: { size: 18, weight: '600', family: 'Poppins' } 
      }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0 
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    }
  }
});

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

    // Logout Confirmation Logic
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'admin_logout.php';
    });
});
</script>
</body>
</html>