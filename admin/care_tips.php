<?php
include '../config/db.php';
session_start();

if (!isset($_SESSION['admin_name'])) {
  // Fetch the actual admin name from the database if available (assuming admin ID 1 is default)
  $result = mysqli_query($conn, "SELECT name FROM admin WHERE id = 1");
  if ($result && $row = mysqli_fetch_assoc($result)) {
      $_SESSION['admin_name'] = $row['name'];
  } else {
      $_SESSION['admin_name'] = "Admin"; // Fallback name
  }
}

// SECURE LOGIC: HANDLE ADMIN PROFILE UPDATE AND PASSWORD CHANGE (SAVES AND RELOADS PAGE)

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
    
    // This line handles the closure (by reloading the page after the alert)
    echo "<script>alert('{$msg}'); window.location='care_tips.php';</script>";
}

// SECURE LOGIC: FETCH TIP DATA (Handles AJAX request for Edit Modal)

if (isset($_GET['action']) && $_GET['action'] === 'fetch' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    $tip_id = $_GET['id'];

    $sql = "SELECT id, name, content, image_url, status FROM care_tips WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    $response = ['error' => 'Tip not found.'];

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $tip_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tip = mysqli_fetch_assoc($result);

        if ($tip) {
            $response = $tip; 
        }
        mysqli_stmt_close($stmt);
    }
    
    echo json_encode($response);
    exit; 
}

// SECURE LOGIC: ADD NEW TIP (Draft or Publish)

if (isset($_POST['add_draft']) || isset($_POST['add_publish'])) {
    
    $status = isset($_POST['add_publish']) ? 'Published' : 'Unpublished';
  
    $name = trim($_POST['name']);
    $content = trim($_POST['content']);
    
    $image = $_FILES['image']['name'] ?? null;
    $target = "uploads/" . basename($image);

    $sql = "INSERT INTO care_tips (name, content, image_url, status, date_published) 
            VALUES (?, ?, ?, ?, NOW())";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $name, $content, $image, $status);
        
        if (mysqli_stmt_execute($stmt)) {
            if (!empty($image) && move_uploaded_file($_FILES['image']['tmp_name'], $target)) { /* success */ }
            
            $msg = ($status === 'Published') ? '✅ Care Tip published successfully!' : '✅ Care Tip saved as draft (Unpublished)!';
            echo "<script>alert('{$msg}'); window.location='care_tips.php';</script>";

        } else {
            echo "<script>alert('❌ Error adding tip: " . mysqli_stmt_error($stmt) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('❌ Database query preparation failed.');</script>";
    }
}

// SECURE LOGIC: UPDATE TIP (Save Draft, Publish, or Unpublish)

if (isset($_POST['update_tip_action'])) {
    $tip_id = $_POST['tip_id'];
    $name = trim($_POST['name']);
    $content = trim($_POST['content']);
    $new_image = $_FILES['image']['name'] ?? null;
    $target = "uploads/" . basename($new_image);
    
    $action = $_POST['update_tip_action'];
    
    if ($action === 'publish') {
        $status = 'Published';
    } elseif ($action === 'unpublish') {
        $status = 'Unpublished';
    } else {
        $status = 'Unpublished';
    }

    $params = [$name, $content, $status, $tip_id];
    $types = "sssi"; 
    $image_update_clause = "";

    if (!empty($new_image)) {
        $image_update_clause = ", image_url = ?";
        array_splice($params, 2, 0, $new_image); 
        $types = "ssssi"; 

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
             echo "<script>alert('❌ Error uploading new image. Update failed.'); window.location='care_tips.php';</script>";
             exit;
        }
    }

    $sql = "UPDATE care_tips SET name = ?, content = ?{$image_update_clause}, status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        $bind_args = array_merge([$stmt, $types], $params);
        call_user_func_array('mysqli_stmt_bind_param', $bind_args);
        
        if (mysqli_stmt_execute($stmt)) {
            $msg = ($action === 'publish') ? '✅ Tip Published!' : (($action === 'unpublish') ? '✅ Tip Unpublished!' : '✅ Tip Saved as Draft!');
            echo "<script>alert('{$msg}'); window.location='care_tips.php';</script>";
        } else {
            echo "<script>alert('❌ Error updating tip: " . mysqli_stmt_error($stmt) . "');</script>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('❌ Database query preparation failed.');</script>";
    }
}

// SECURE LOGIC: DELETE TIP 

if (isset($_POST['delete_tip_id'])) {
    $tip_id = $_POST['delete_tip_id'];

    $sql_select = "SELECT image_url FROM care_tips WHERE id = ?";
    $stmt_select = mysqli_prepare($conn, $sql_select);
    if ($stmt_select) {
        mysqli_stmt_bind_param($stmt_select, "i", $tip_id);
        mysqli_stmt_execute($stmt_select);
        $result = mysqli_stmt_get_result($stmt_select);
        $row = mysqli_fetch_assoc($result);
        $image_to_delete = $row['image_url'] ?? null;
        mysqli_stmt_close($stmt_select);
    }
    
    $sql_delete = "DELETE FROM care_tips WHERE id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);

    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, "i", $tip_id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            if ($image_to_delete && file_exists("uploads/" . $image_to_delete)) {
                unlink("uploads/" . $image_to_delete);
            }
            echo "<script>alert('✅ Care Tip deleted successfully!'); window.location='care_tips.php';</script>";
        } else {
            echo "<script>alert('❌ Error deleting tip: " . mysqli_stmt_error($stmt_delete) . "');</script>";
        }
        mysqli_stmt_close($stmt_delete);
    } else {
        echo "<script>alert('❌ Database query preparation failed.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Care Tips | SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">

  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
    .sidebar {
      height: calc(100vh - 30px);
      width: 240px;
      background-color: #fff;
      border-right: 1px solid #ddd;
      position: fixed;
      top: 15px;
      left: 25px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border-radius: 12px;
      padding: 25px 0;
    }
    .sidebar h2 { font-family: 'Quicksand', sans-serif; color: #A9745B; font-weight: 700; font-size: 28px; margin-bottom: 25px; }
    .sidebar .nav-link { color: #333; font-weight: 500; padding: 12px 19px; border-radius: 8px; margin: 2px 10px; transition: 0.3s; display:block; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0e1d8; color: #A9745B; }
    .topbar {
      background-color: #A9745B;
      height: 60px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      padding: 0 30px;
      color: white;
      margin-left: 288px;
      margin-right: 23px;
      border-radius: 15px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      position: relative;
    }
    .topbar i { font-size: 26px; cursor: pointer; transition: 0.2s ease; }
    .topbar i:hover { opacity: 0.85; }
    .main-content { margin-left: 260px; padding: 30px; margin-top: 20px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { text-align: center; padding: 12px; vertical-align: middle; }
    thead th { background-color: #f0e1d8; color: #A9745B; font-weight: 600; }
    tbody tr:nth-child(odd) { background-color: #fff; }
    tbody tr:nth-child(even) { background-color: #f9f9f9; }
    tbody tr:hover { background-color: #f1edea; transition: 0.2s; }
    .btn-add { background-color: #A9745B; color: white; margin-top: -10px;}
    .btn-add:hover { background-color: #8e5f47; }
    .profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
    .profile-dropdown a { display:block; padding:10px 15px; text-decoration:none; color:#333; }
    .profile-dropdown a:hover { background:#f8f8f8; }
    .modal-header {
      border-top-left-radius: 0.75rem !important;
      border-top-right-radius: 0.75rem !important;
    }
  </style>
</head>
<body>

  <div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column w-100">
      <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link active"><i class="bi bi-book me-2"></i> Care Tips</a>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold" style="color:#A9745B; margin:0;">📘 Manage Care Tips</h3>
      <button class="btn btn-add px-3 py-2" id="openAddTip"><i class="bi bi-plus-circle me-1"></i> Add Tip</button>
    </div>

    <div class="table-responsive shadow-sm bg-white rounded p-3 mt-2">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Status</th>
            <th>Date Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $result = mysqli_query($conn, "SELECT * FROM care_tips ORDER BY date_published DESC");
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              $status_class = ($row['status'] == 'Published') ? 'bg-success' : 'bg-secondary';
              
              echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['name']}</td>
                <td><span class='badge {$status_class}'>{$row['status']}</span></td>
                <td>{$row['date_published']}</td>
                <td>
                  <a href='#' data-id='{$row['id']}' class='btn btn-sm btn-warning edit-tip-btn'><i class='bi bi-pencil'></i></a>
                  <a href='#' data-id='{$row['id']}' class='btn btn-sm btn-danger delete-tip-btn'><i class='bi bi-trash'></i></a>
                </td>
              </tr>";
            }
          } else {
            echo "<tr><td colspan='5' class='text-center'>No care tips found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="modal fade" id="addTipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header" style="background-color:#A9745B;color:white;">
          <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add New Care Tip</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" enctype="multipart/form-data">
          <div class="modal-body bg-light">
            <div class="mb-3">
              <label class="form-label fw-semibold">Title</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Content</label>
              <textarea name="content" class="form-control" rows="5" required></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Image (optional)</label>
              <input type="file" name="image" class="form-control" accept="image/*">
            </div>
          </div>
          <div class="modal-footer bg-white d-flex justify-content-between align-items-center">
              <button type="submit" name="add_publish" class="btn btn-success px-4"><i class="bi bi-globe me-1"></i> Publish</button>
              <div class="d-flex gap-2">
                  <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" name="add_draft" class="btn btn-secondary px-4">Save</button>
              </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editTipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B;color:white;">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Care Tip</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editTipForm">
                <input type="hidden" name="tip_id" id="edit_tip_id">
                <div class="modal-body bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Title</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Content</label>
                        <textarea name="content" id="edit_content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Image (optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="form-text text-muted" id="current_image_path"></small>
                    </div>
                </div>
                <div class="modal-footer bg-white d-flex justify-content-between align-items-center">
                    <div id="publish-unpublish-container"></div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" onclick="submitEditTip('save_draft')" class="btn btn-secondary px-4">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
  </div>

  <div class="modal fade" id="deleteTipModal" tabindex="-1" aria-labelledby="deleteTipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header" style="background-color:#A9745B; color:white;">
                <h5 class="modal-title" id="deleteTipModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="confirmDeleteForm">
                <input type="hidden" name="delete_tip_id" id="delete_tip_id_input">
                <div class="modal-body text-center">
                    <p class="fw-semibold mb-3" style="color:#333;">Are you sure you want to permanently delete this Care Tip?</p>
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No</button>
                        <button type="submit" class="btn btn-danger px-4">Yes, Delete It</button>
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
                      <h5 class="mt-2 mb-4 fw-bold"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h5>
                      
                      <div class="mb-3 text-start">
                          <label for="adminNameInput" class="form-label fw-semibold">Admin Name</label>
                          <input type="text" name="admin_name" id="adminNameInput" class="form-control" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" required>
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
                      <button type="submit" class="btn btn-secondary px-4">Save Changes</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

// GLOBAL FUNCTION: Submit Edit Tip with dynamic status action

function submitEditTip(action) {
    const form = document.getElementById('editTipForm');
    let actionInput = document.getElementById('update_tip_action_input');
    
    if (!actionInput) {
        actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'update_tip_action';
        actionInput.id = 'update_tip_action_input';
        form.appendChild(actionInput);
    }
    
    actionInput.value = action;
    form.submit();
}

document.addEventListener("DOMContentLoaded", function() {
    // Profile Dropdown Logic
    const profileBtn = document.getElementById("profileBtn");
    const profileDropdown = document.getElementById("profileDropdown");
    const viewProfileLink = document.querySelector('.view-profile-link'); 

    profileBtn.addEventListener("click", () => {
      profileDropdown.style.display = profileDropdown.style.display === "block" ? "none" : "block";
    });

    if (viewProfileLink) {
        viewProfileLink.addEventListener('click', () => {
            profileDropdown.style.display = 'none';
        });
    }

    document.addEventListener("click", e => {
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.style.display = "none";
      }
    });

    // Add Tip Modal Initialization
    const addTipModalElement = document.getElementById("addTipModal");
    const openAddTipButton = document.getElementById("openAddTip");
    if (addTipModalElement && openAddTipButton) {
        const addTipModal = new bootstrap.Modal(addTipModalElement);
        openAddTipButton.addEventListener("click", () => addTipModal.show());
    }
    
    // Logout Confirmation Logic
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'logout.php';
    });

    // Edit Tip Modal Logic (Fetches data via AJAX/Fetch)
    const editTipModal = new bootstrap.Modal(document.getElementById('editTipModal'));
    const pubUnpubContainer = document.getElementById('publish-unpublish-container');

    document.querySelectorAll('.edit-tip-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const tipId = this.getAttribute('data-id');
            
            fetch('care_tips.php?action=fetch&id=' + tipId) 
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error fetching tip data: ' + data.error);
                        console.error(data.error);
                        return;
                    }
                    
                    document.getElementById('edit_tip_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_content').value = data.content;
                    
                    const imagePathElement = document.getElementById('current_image_path');
                    imagePathElement.textContent = data.image_url ? 'Current Image: ' + data.image_url : 'No current image uploaded.';
                    
                    // Dynamic Publish/Unpublish Button
                    let dynamicButtonHTML = '';
                    if (data.status === 'Published') {
                        dynamicButtonHTML = `
                            <button type="button" onclick="submitEditTip('unpublish')" class="btn btn-danger px-4">
                                <i class="bi bi-x-circle me-1"></i> Unpublish
                            </button>`;
                    } else {
                        dynamicButtonHTML = `
                            <button type="button" onclick="submitEditTip('publish')" class="btn btn-success px-4">
                                <i class="bi bi-globe me-1"></i> Publish
                            </button>`;
                    }
                    pubUnpubContainer.innerHTML = dynamicButtonHTML;

                    editTipModal.show();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    alert('Could not connect to the server to fetch tip data.');
                });
        });
    });
    
    // Delete Tip Modal Logic
    const deleteTipModal = new bootstrap.Modal(document.getElementById('deleteTipModal'));
    const deleteIdInput = document.getElementById('delete_tip_id_input');
    
    document.querySelectorAll('.delete-tip-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const tipId = this.getAttribute('data-id');
            deleteIdInput.value = tipId;
            deleteTipModal.show();
        });
    });
});
</script>
</body>
</html>