<?php
include('../config/db.php');
session_start();

$pending_count = 0;

// ✅ Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get logged-in user ID

// ✅ Use mysqli_query() style
$sql = "
    SELECT 
        ar.request_id,
        ar.pet_id,
        ar.pet_name,
        CONCAT(ar.first_name, ' ', ar.last_name) AS full_name,
        ar.classification,
        ar.status,
        ar.request_date
    FROM adoption_requests ar
    WHERE ar.user_id = '$user_id'
    ORDER BY ar.request_date DESC
";

$result = mysqli_query($conn, $sql); // 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Adoption Requests - SafePaws</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body {
      background-color: #fff8f3;
      font-family: 'Poppins', sans-serif;
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
          <?php
          if (mysqli_num_rows($result) > 0) {
              $i = 1;
              while ($row = mysqli_fetch_assoc($result)) {
                  $statusClass = '';
                  if ($row['status'] == 'Pending') $statusClass = 'status-pending';
                  elseif ($row['status'] == 'Approved') $statusClass = 'status-approved';
                  else $statusClass = 'status-denied';

                  echo "<tr>
                      <td>{$i}</td>
                      <td>{$row['pet_id']}</td>
                      <td>{$row['pet_name']}</td>
                      <td>{$row['full_name']}</td>
                      <td>{$row['classification']}</td>
                      <td><span class='{$statusClass}'>{$row['status']}</span></td>
                      <td>" . date('F j, Y g:i A', strtotime($row['request_date'])) . "</td>
                  </tr>";
                  $i++;
              }
          } else {
              echo "<tr><td colspan='7' class='text-muted'>You haven’t submitted any adoption requests yet.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


</body>
</html>
