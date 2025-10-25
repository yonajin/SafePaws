<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION['admin_name'])) {
  $_SESSION['admin_name'] = "Admin";
}

// Fetch all users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY date_registered DESC");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users | SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
    <style>
      body { background-color: #FFF8F3; font-family: 'Poppins', sans-serif; }
      .sidebar { height: 100vh; background-color: #FFF8F3; border-right: 1px solid #ddd; position: fixed; width: 230px; padding: 20px 0; }
      .sidebar h2 { font-family: 'Quicksand', sans-serif; color: #A9745B; font-weight: 700; text-align: center; margin-bottom: 30px; }
      .sidebar .nav-link { color: #000; padding: 12px 25px; display: block; font-weight: 500; }
      .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0e1d8; border-radius: 8px; color: #A9745B; }
      .topbar { background-color: #A9745B; height: 60px; display: flex; justify-content: flex-end; align-items: center; padding: 0 30px; color: white; margin-left: 230px; position: relative; }
      .main-content { margin-left: 230px; padding: 30px; }

      table { border-collapse: collapse; width: 100%; }
      th, td { text-align: center; padding: 12px; vertical-align: middle; }
      thead th { background-color: #f0e1d8; color: #A9745B; font-weight: 600; }
      tbody tr:nth-child(odd) { background-color: #ffffff; }
      tbody tr:nth-child(even) { background-color: #f9f9f9; }
      tbody tr:hover { background-color: #f1edea; transition: 0.2s; }

      .btn-edit { background-color: #f0a04b; color: white; }
      .btn-edit:hover { background-color: #e48a28; }
      .profile-btn { background: none; border: none; color: white; font-size: 1.8rem; cursor: pointer; }
      .profile-dropdown { position: absolute; top: 60px; right: 20px; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); display: none; width: 200px; z-index: 999; }
      .profile-dropdown a { display: block; padding: 10px 15px; text-decoration: none; color: #333; }
      .profile-dropdown a:hover { background-color: #f8f8f8; }
    </style>
  </head>
  <body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column">
      <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
      <a href="manage_pets.php" class="nav-link">Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link">Adoption Requests</a>
      <a href="care_tips.php" class="nav-link">Care Tips</a>
      <a href="users.php" class="nav-link active">Users</a>
      <a href="reports.php" class="nav-link">Reports</a>
      <a href="logout.php" class="nav-link text-danger">Logout</a>
    </nav>
  </div>

  <!-- Topbar -->
  <div class="topbar">
    <button id="profileBtn" class="profile-btn">
      <i class="bi bi-person-circle"></i>
    </button>
    <div id="profileDropdown" class="profile-dropdown">
      <a href="admin_profile.php"><i class="bi bi-person"></i> View Profile</a>
      <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
      <hr class="m-0">
      <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold" style="color:#A9745B;">👥 User Management</h3>
    </div>

    <div class="table-responsive shadow-sm bg-white rounded p-3">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Status</th>
            <th>Date Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>
                      <td>{$row['id']}</td>
                      <td>{$row['full_name']}</td>
                      <td>{$row['email']}</td>
                      <td>{$row['username']}</td>
                      <td>
                        <span class='badge bg-" . 
                          ($row['status'] == 'Active' ? 'success' : 'secondary') . "'>
                          {$row['status']}
                        </span>
                      </td>
                      <td>{$row['date_registered']}</td>
                      <td>
                        <a href='edit_user.php?id={$row['id']}' class='btn btn-sm btn-edit'>
                          <i class='bi bi-pencil'></i>
                        </a>
                      </td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='7'>No registered users found.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const profileBtn = document.getElementById("profileBtn");
    const profileDropdown = document.getElementById("profileDropdown");
    profileBtn.addEventListener("click", () => {
      profileDropdown.style.display = profileDropdown.style.display === "block" ? "none" : "block";
    });
    document.addEventListener("click", e => {
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.style.display = "none";
      }
    });
  </script>
  </body>
</html>
