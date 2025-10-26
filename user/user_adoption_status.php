<?php
include('../config/db.php');
session_start();

// ✅ Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Use prepared statement (no mixing with mysqli_query)
$stmt = $conn->prepare("
    SELECT 
        ar.request_id,
        ar.pet_id,
        COALESCE(p.name, 'Unknown') AS pet_name,
        CONCAT(ar.first_name, ' ', ar.last_name) AS full_name,
        COALESCE(p.classification, ar.classification) AS classification,
        ar.status,
        ar.request_date
    FROM adoption_requests ar
    LEFT JOIN pets p ON ar.pet_id = p.pet_id
    WHERE ar.user_id = ?
    ORDER BY ar.request_date DESC
");
$stmt = $conn->prepare("SELECT * FROM adoption_requests WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Adoption Requests - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fff8f3;
    }
    /* Navbar */
    .navbar {
  background-color: #A9745B !important;
  height: 70px;
}

.navbar .nav-link,
.navbar .navbar-brand {
  color: #FFFFFF !important;
}

.navbar .nav-link:hover,
.navbar .navbar-brand:hover {
  color: #ffe6d5 !important; /* optional hover color */
}

    .navbar-brand {
  font-family: 'Quicksand', sans-serif;
  color: #FFF8F3 !important;
  font-weight: 700; /* optional, makes it bolder */
  font-size: 40px;
}

.navbar .nav-link {
  font-family: 'Poppins', sans-serif;
  color: #FFF8F3 !important;
  font-weight: 500;
  font-size: 17px;
  margin-left: 20px;
}

    .container {
      margin-top: 70px;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: none;
    }
    .status-pending { color: #f0ad4e; font-weight: bold; }
    .status-approved { color: #28a745; font-weight: bold; }
    .status-denied { color: #dc3545; font-weight: bold; }
  </style>
</head>
<body>

<?php include('../includes/user_navbar.php'); ?>

<div class="container">
  <div class="card p-4">
    <h3 class="text-center mb-4">My Adoption Requests</h3>

    <?php if ($result->num_rows > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Pet ID</th>
              <th>Pet Name</th>
              <th>Applicant Name</th>
              <th>Classification</th>
              <th>Status</th>
              <th>Date Requested</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['pet_id']) ?></td>
                <td><?= htmlspecialchars($row['pet_name']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
                <td><?= htmlspecialchars($row['classification'] ?? 'N/A') ?></td>
                <td>
                  <?php if ($row['status'] == 'Pending'): ?>
                    <span class="status-pending">Pending</span>
                  <?php elseif ($row['status'] == 'Approved'): ?>
                    <span class="status-approved">Approved</span>
                  <?php else: ?>
                    <span class="status-denied">Denied</span>
                  <?php endif; ?>
                </td>
                <td><?= date("F j, Y g:i A", strtotime($row['request_date'])) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-center text-muted">You haven’t submitted any adoption requests yet.</p>
    <?php endif; ?>

  </div>
</div>

</body>
</html>