<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
session_start();

// Flash message handler: Load, then clear session variables
$message = $_SESSION['adoption_message'] ?? '';
$message_type = $_SESSION['adoption_message_type'] ?? '';
unset($_SESSION['adoption_message'], $_SESSION['adoption_message_type']);

// SECURITY CHECK: ENSURE ADMIN IS LOGGED IN & SET SESSION NAME
if (!isset($_SESSION['admin_name'])) {
    // This assumes the admin ID is 1 or can be determined otherwise.
    $result = mysqli_query($conn, "SELECT name FROM admin WHERE id = 1");
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $_SESSION['admin_name'] = $row['name'];
    } else {
        $_SESSION['admin_name'] = "Admin"; 
    }
}

// SECURE LOGIC: HANDLE STATUS UPDATES (Prepared Statement)

if (isset($_POST['update_status'])) {
    $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);
    $new_status = trim($_POST['new_status']);
    $success = false;
    $msg = '❌ Error updating request status.';
    
    // Basic status validation
    if ($request_id && in_array($new_status, ['Approved', 'Denied', 'Pending'])) {
        
        // NOTE: Uses 'request_id' as the primary key column
        $sql = "UPDATE adoption_requests SET status = ? WHERE request_id = ?"; 
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $new_status, $request_id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "✅ Request ID {$request_id} successfully marked as **{$new_status}**.";
                $success = true;
            }
            mysqli_stmt_close($stmt);
        }
    }
    $_SESSION['adoption_message'] = $msg;
    $_SESSION['adoption_message_type'] = $success ? 'success' : 'danger';
    header("Location: adoption_requests.php");
    exit();
}

// SECURE LOGIC: HANDLE DELETION (Prepared Statement)

if (isset($_POST['delete_request'])) {
    $request_id = filter_var($_POST['request_id'], FILTER_VALIDATE_INT);
    $success = false;
    $msg = '❌ Error deleting request.';
    
    if ($request_id) {
        // NOTE: Uses 'request_id' as the primary key column
        $sql = "DELETE FROM adoption_requests WHERE request_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $request_id);
            if (mysqli_stmt_execute($stmt)) {
                 $msg = "🗑️ Request ID {$request_id} deleted successfully.";
                 $success = true;
            }
            mysqli_stmt_close($stmt);
        }
    }
    $_SESSION['adoption_message'] = $msg;
    $_SESSION['adoption_message_type'] = $success ? 'success' : 'danger';
    header("Location: adoption_requests.php");
    exit();
}

// === REFINED SECURE LOGIC: ADMIN PROFILE UPDATE ===
if (isset($_POST['update_admin_profile'])) {
    $admin_name = trim($_POST['admin_name']);
    $admin_id = 1; // Assuming admin ID is hardcoded to 1
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
    if ($success && !empty($_POST['new_password'])) {
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
    
    // Use session flash message instead of alert box
    $_SESSION['adoption_message'] = $msg;
    $_SESSION['adoption_message_type'] = $success ? 'success' : 'danger';
    header('location: adoption_requests.php');
    exit();
}
// === END REFINED LOGIC ===

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Adoption Requests | SafePaws</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/adoption_requests.css">
</head>
<body>

<div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
      <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link active"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
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
<h3 class="fw-bold mb-4" style="color:#A9745B;">Adoption Requests</h3>

<?php if ($message): // Display flash message if session message exists ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="table-responsive shadow-sm bg-white rounded p-3">
<table class="table align-middle">
<thead>
<tr>
<th>ID</th>
<th>Customer Name</th>
<th>Pet Name</th>
<th>Status</th>
<th>Request Date</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
// DATA FETCH: Fetch adoption requests, joining with pets table and the users table for the full name.
$sql = "SELECT 
            ar.request_id, 
            ar.status, 
            ar.request_date, 
            p.name AS pet_name,
            u.full_name AS customer_name
        FROM adoption_requests ar
        JOIN pets p ON ar.pet_id = p.pet_id 
        JOIN users u ON ar.user_id = u.user_id /* CRITICAL: Join users table using user_id */
        ORDER BY ar.request_date DESC";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        // Status badge color logic
        $status_color = match ($row['status']) {
            'Pending' => 'warning',
            'Approved' => 'success',
            default => 'danger',
        };
        
        echo "<tr>
        <td>{$row['request_id']}</td> 
        <td>{$row['customer_name']}</td> 
        <td>{$row['pet_name']}</td>
        <td><span class='badge bg-{$status_color}'>{$row['status']}</span></td>
        <td>{$row['request_date']}</td>
        <td>
            <button class='btn btn-sm btn-success btn-action' data-bs-toggle='modal' data-bs-target='#statusModal' data-id='{$row['request_id']}' data-status='Approved'>Approve</button>
            <button class='btn btn-sm btn-warning btn-action' data-bs-toggle='modal' data-bs-target='#statusModal' data-id='{$row['request_id']}' data-status='Denied'>Deny</button>
            <button class='btn btn-sm btn-danger btn-action' data-bs-toggle='modal' data-bs-target='#deleteModal' data-id='{$row['request_id']}'>Delete</button>
        </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No adoption requests found.</td></tr>";
}
?>
</tbody>
</table>
</div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content border-0 shadow-lg rounded-4">
<div class="modal-header" style="background-color:#A9745B;color:white;">
<h5 class="modal-title">Confirm Status Change</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>
<form method="POST">
<div class="modal-body text-center py-4 bg-light">
<p class="fw-semibold mb-3" id="statusMessage"></p>
<input type="hidden" name="request_id" id="statusRequestId">
<input type="hidden" name="new_status" id="newStatus">
<div class="d-flex justify-content-center gap-3">
<button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="update_status" class="btn btn-success px-4" id="confirmStatusBtn">Confirm</button>
</div>
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
<form method="POST">
<div class="modal-body text-center py-4 bg-light">
<p class="fw-semibold mb-3">Are you sure you want to delete this request?</p>
<input type="hidden" name="request_id" id="deleteRequestId">
<div class="d-flex justify-content-center gap-3">
<button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
<button type="submit" name="delete_request" class="btn btn-danger px-4">Delete</button>
</div>
</div>
</form>
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

<!-- 🗓️ Schedule Interview Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header" style="background-color:#A9745B;color:white;">
        <h5 class="modal-title">Schedule Interview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body bg-light">
          <input type="hidden" name="request_id" id="scheduleRequestId">
          <div class="mb-3">
            <label for="interview_date" class="form-label fw-semibold">Interview Date & Time</label>
            <input type="datetime-local" class="form-control" name="interview_date" id="interview_date" required>
          </div>
          <div class="mb-3">
            <label for="interview_type" class="form-label fw-semibold">Interview Type</label>
            <select name="interview_type" id="interview_type" class="form-select" required>
              <option value="">Select Type</option>
              <option value="Online">Online</option>
              <option value="Onsite">Onsite</option>
            </select>
          </div>
          <p class="text-muted small">Once approved, the adopter will be notified about their interview schedule.</p>
        </div>
        <div class="modal-footer bg-white d-flex justify-content-end align-items-center">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="schedule_interview" class="btn btn-success px-4">Save & Approve</button>
        </div>
      </form>
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

    // Schedule Modal Logic
const scheduleModal = document.getElementById('scheduleModal');
if (scheduleModal) {
  scheduleModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const requestId = button.getAttribute('data-id');
    document.getElementById('scheduleRequestId').value = requestId;
  });
}

    
    // Profile Dropdown Logic (Functional)
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

    // Status Modal Logic (Functional)
    const statusModal = document.getElementById('statusModal');
    if(statusModal) {
        statusModal.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            const status = button.getAttribute('data-status');
            document.getElementById('statusRequestId').value = requestId;
            document.getElementById('newStatus').value = status;
            document.getElementById('statusMessage').innerHTML = `Are you sure you want to mark this request as <b>"${status}"</b>?`;
            
            // Set the correct button color and text for confirmation
            const confirmBtn = statusModal.querySelector('button[type="submit"]');
            confirmBtn.classList.remove('btn-danger', 'btn-warning', 'btn-success');

            if (status === 'Approved') {
                 confirmBtn.classList.add('btn-success');
                 confirmBtn.innerText = 'Approve Request';
            } else if (status === 'Denied') {
                 confirmBtn.classList.add('btn-danger');
                 confirmBtn.innerText = 'Deny Request';
            } else {
                 confirmBtn.classList.add('btn-secondary'); // Default/Fallback
                 confirmBtn.innerText = 'Confirm';
            }
        });
    }


    // Delete Modal Logic (Functional)
    const deleteModal = document.getElementById('deleteModal');
    if(deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function(event){
            const button = event.relatedTarget;
            const requestId = button.getAttribute('data-id');
            document.getElementById('deleteRequestId').value = requestId;
        });
    }

    // Logout Confirmation Logic (Functional)
    document.getElementById('confirmLogoutBtn').addEventListener('click', function(){
      window.location.href = 'admin_logout.php';
    });
});
</script>
</body>
</html>