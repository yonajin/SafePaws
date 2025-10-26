<?php
include 'db_connect.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle date filtering
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$filter = "";
if (!empty($start_date) && !empty($end_date)) {
  $filter = "WHERE date_requested BETWEEN '$start_date' AND '$end_date'";
}

// Analytics counts
$total_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets"))['total'];
$available_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE status='Available'"))['total'];
$adopted_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE status='Adopted'"))['total'];
$pending_pets = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM pets WHERE status='For Approval'"))['total'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];

$total_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests"))['total'];
$approved_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Approved'"))['total'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Pending'"))['total'];
$rejected_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM adoption_requests WHERE status='Rejected'"))['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reports | SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #FFF8F3; font-family: 'Poppins', sans-serif; }
    .sidebar { height: 100vh; background-color: #FFF8F3; border-right: 1px solid #ddd; position: fixed; width: 230px; padding: 20px 0; }
    .sidebar h2 { font-family: 'Quicksand', sans-serif; color: #A9745B; font-weight: 700; text-align: center; margin-bottom: 30px; }
    .sidebar .nav-link { color: #000; padding: 12px 25px; display: block; font-weight: 500; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #f0e1d8; border-radius: 8px; color: #A9745B; }
    .topbar { background-color: #A9745B; height: 60px; display: flex; justify-content: flex-end; align-items: center; padding: 0 30px; color: white; margin-left: 230px; }
    .main-content { margin-left: 230px; padding: 30px; }
    .card { border: none; border-radius: 10px; }
    .card h5 { color: #A9745B; font-weight: 600; }
    .btn-generate { background-color: #A9745B; color: white; }
    .btn-generate:hover { background-color: #8e5f47; }
    table { border-collapse: collapse; width: 100%; }
    thead tr { background-color: #f0e1d8; color: #A9745B; }
    tbody tr:nth-child(even) { background-color: #fdf7f3; }
    tbody tr:nth-child(odd) { background-color: #ffffff; }
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
    <a href="users.php" class="nav-link">Users</a>
    <a href="reports.php" class="nav-link active">Reports</a>
    <a href="logout.php" class="nav-link text-danger">Logout</a>
  </nav>
</div>

<!-- Topbar -->
<div class="topbar">
  <i class="bi bi-person-circle"></i>
</div>

<!-- Main content -->
<div class="main-content">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold" style="color:#A9745B;">📊 Reports & Analytics</h3>
  </div>

  <!-- Filter -->
  <form method="GET" class="row g-3 mb-4">
    <div class="col-md-4">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= $start_date ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= $end_date ?>">
    </div>
    <div class="col-md-4 d-flex align-items-end">
      <button type="submit" class="btn btn-generate w-100"><i class="bi bi-graph-up"></i> Generate Report</button>
    </div>
  </form>

  <!-- Overview Cards -->
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

  <!-- Chart -->
  <div class="card shadow-sm p-4 mb-4">
    <canvas id="adoptionChart" height="120"></canvas>
  </div>

  <!-- Detailed Adoption Table -->
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
          $query = "SELECT * FROM adoption_requests $filter ORDER BY date_requested DESC";
          $result = mysqli_query($conn, $query);
          if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
              echo "<tr>
                      <td>{$row['request_id']}</td>
                      <td>{$row['pet_id']}</td>
                      <td><span class='badge bg-".($row['status']=='Approved'?'success':($row['status']=='Pending'?'warning':'danger'))."'>{$row['status']}</span></td>
                      <td>{$row['date_requested']}</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No adoption records found for this date range.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Chart Script -->
<script>
const ctx = document.getElementById('adoptionChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Approved', 'Pending', 'Rejected'],
    datasets: [{
      label: 'Adoption Requests',
      data: [<?= $approved_requests ?>, <?= $pending_requests ?>, <?= $rejected_requests ?>],
      backgroundColor: ['#8BC34A', '#FFC107', '#E57373']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      title: { display: true, text: 'Adoption Requests Summary', color: '#A9745B', font: { size: 18 } }
    }
  }
});
</script>

</body>
</html>
