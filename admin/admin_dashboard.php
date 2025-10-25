<?php
session_start();
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
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #FFF8F3;
      padding: 15px;
    }

    /* --- Sidebar --- */
    .sidebar {
      height: calc(100vh - 30px);
      width: 240px;
      background-color: #ffffff;
      border-right: 1px solid #ddd;
      position: fixed;
      top: 15px;
      left: 25px;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      border-radius: 12px;
      padding: 25px 0;
    }

    .sidebar h2 {
      font-family: 'Quicksand', sans-serif;
      color: #A9745B;
      font-weight: 700;
      font-size: 28px;
      margin-bottom: 25px;
    }

    .sidebar .nav {
      width: 100%;
    }

    .sidebar .nav-link {
      color: #333;
      font-weight: 500;
      padding: 12px 19px;
      display: block;
      transition: all 0.3s ease;
      border-radius: 8px;
      margin: 2px 10px;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background-color: #f0e1d8;
      color: #A9745B;
    }

    .sidebar .nav-link.text-danger {
      color: #dc3545 !important;
    }

    /* --- Topbar --- */
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
      box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
    }

    .topbar i {
      font-size: 26px;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .topbar i:hover {
      opacity: 0.85;
    }

    /* --- Main Content --- */
    .main-content {
      margin-left: 260px;
      padding: 30px;
      margin-top: 8px;
    }

    .card {
      border: none;
      border-radius: 15px;
      padding: 20px;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .card h5 {
      font-weight: 600;
      color: #A9745B;
    }

    .value {
      font-size: 28px;
      font-weight: 700;
      color: #333;
    }

    .progress-bar {
      background-color: #A9745B;
    }

    .chart-placeholder {
      background-color: #fff;
      border-radius: 15px;
      padding: 25px;
      height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #a9745b;
      font-weight: 500;
      border: 1px solid #eee;
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
      <a href="#" class="nav-link active"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
      <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
      <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
      <a href="#" class="nav-link text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="bi bi-box-arrow-right me-2"></i> Logout
      </a>
    </nav>
  </div>

  <!-- Top Bar -->
  <div class="topbar">
    <i class="bi bi-person-circle"></i>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <h3 class="mb-4 fw-bold" style="color:#A9745B;">Dashboard Overview</h3>

    <!-- Dashboard Cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card text-center">
          <h5>Raised Funds</h5>
          <div class="value">₱105,000</div>
          <p class="text-muted mb-1">+15% since last week</p>
          <a href="#" class="text-decoration-none small text-secondary">View report →</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card text-center">
          <h5>Active Pets</h5>
          <div class="value">84</div>
          <p class="text-muted mb-1">Ready for adoption</p>
          <a href="manage_pets.php" class="text-decoration-none small text-secondary">Manage pets →</a>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card text-center">
          <h5>Approved Adoptions</h5>
          <div class="value">53</div>
          <p class="text-muted mb-1">Successful adoptions this month</p>
          <a href="reports.php" class="text-decoration-none small text-secondary">Analytics →</a>
        </div>
      </div>
    </div>

    <!-- Analytics Section -->
    <div class="row g-4">
      <div class="col-md-6">
        <div class="chart-placeholder">
          📈 Adoption Trends (Monthly)
        </div>
      </div>

      <div class="col-md-6">
        <div class="chart-placeholder">
          📊 Requests & Approvals Summary
        </div>
      </div>
    </div>

    <!-- Reports Summary -->
    <div class="row g-4 mt-2">
      <div class="col-md-4">
        <div class="card text-center" style="background-color:#fff6f1;">
          <p class="mb-1 fw-semibold">Pending Requests</p>
          <h4>12</h4>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center" style="background-color:#fff6f1;">
          <p class="mb-1 fw-semibold">Approved Adoptions</p>
          <h4>53</h4>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card text-center" style="background-color:#fff6f1;">
          <p class="mb-1 fw-semibold">Total Donations</p>
          <h4>₱105,000</h4>
        </div>
      </div>
    </div>
  </div>

  <!-- Logout Confirmation Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content shadow-lg" style="border-radius: 20px; overflow: hidden;">
        
        <!-- Header -->
        <div class="modal-header text-white" style="background-color: #A9745B; border-bottom: none;">
          <h5 class="modal-title w-100 text-center" id="logoutModalLabel">
            <i class="bi bi-box-arrow-right"></i> Confirm Logout
          </h5>
        </div>

        <!-- Body -->
        <div class="modal-body text-center py-4" style="background-color: #FFF8F3;">
          <p class="fw-semibold mb-4" style="color:#333;">Are you sure you want to log out?</p>
          <div class="d-flex justify-content-center gap-3">
            <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">No</button>
            <button type="button" class="btn btn-danger px-4 rounded-pill" id="confirmLogoutBtn">Yes</button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
      window.location.href = 'logout.php';
    });
  </script>
</body>
</html>