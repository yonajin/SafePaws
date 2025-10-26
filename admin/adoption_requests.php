<?php
include 'db_connect.php';
session_start();

// Handle status updates
if (isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];
    $sql = "UPDATE adoption_requests SET status='$new_status' WHERE id=$request_id";
    mysqli_query($conn, $sql);
    echo "<script>window.location='adoption_requests.php';</script>";
}

// Handle deletion
if (isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'];
    $sql = "DELETE FROM adoption_requests WHERE id=$request_id";
    mysqli_query($conn, $sql);
    echo "<script>window.location='adoption_requests.php';</script>";
}
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

<style>
body { font-family: 'Poppins', sans-serif; background-color: #FFF8F3; padding: 15px; }
.sidebar { height: calc(100vh - 30px); width: 240px; background-color: #fff; border-right:1px solid #ddd; position: fixed; top:15px; left:25px; display:flex; flex-direction: column; align-items:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-radius:12px; padding:25px 0; }
.sidebar h2 { font-family: 'Quicksand', sans-serif; color:#A9745B; font-weight:700; font-size:28px; margin-bottom:25px; }
.sidebar .nav { width:100%; }
.sidebar .nav-link { color:#333; font-weight:500; padding:12px 19px; display:block; border-radius:8px; margin:2px 10px; transition:0.3s; }
.sidebar .nav-link:hover, .sidebar .nav-link.active { background-color:#f0e1d8; color:#A9745B; }
.sidebar .nav-link.text-danger { color:#dc3545 !important; }
.topbar { background-color:#A9745B; height:60px; display:flex; justify-content:flex-end; align-items:center; padding:0 30px; color:white; margin-left:288px; margin-right:23px; border-radius:15px; box-shadow:0 3px 8px rgba(0,0,0,0.1); position:relative; }
.topbar i { font-size:26px; cursor:pointer; transition:0.2s; }
.topbar i:hover { opacity:0.85; }
.main-content { margin-left:260px; padding:30px; margin-top:20px; }
table { border-collapse: collapse; width:100%; }
th, td { text-align:center; padding:12px; vertical-align:middle; }
thead th { background-color:#f0e1d8; color:#A9745B; font-weight:600; }
tbody tr:nth-child(odd) { background-color:#fff; }
tbody tr:nth-child(even) { background-color:#f9f9f9; }
tbody tr:hover { background-color:#f1edea; transition:0.2s; }
.btn-action { margin:0 2px; }
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>SafePaws</h2>
    <nav class="nav flex-column text-start w-100">
      <a href="admin_dashboard.php" class="nav-link"><i class="bi bi-house-door me-2"></i> Dashboard</a>
      <a href="manage_pets.php" class="nav-link"><i class="bi bi-box-seam me-2"></i> Manage Pets</a>
      <a href="adoption_requests.php" class="nav-link active"><i class="bi bi-envelope-check me-2"></i> Adoption Requests</a>
      <a href="care_tips.php" class="nav-link"><i class="bi bi-book me-2"></i> Care Tips</a>
      <a href="users.php" class="nav-link"><i class="bi bi-people me-2"></i> Users</a>
      <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-line me-2"></i> Reports</a>
      <a href="#" class="nav-link text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
    </nav>
</div>

<!-- Topbar -->
<div class="topbar">
  <i class="bi bi-person-circle"></i>
</div>

<!-- Main Content -->
<div class="main-content">
<h3 class="fw-bold mb-4" style="color:#A9745B;">🐾 Adoption Requests</h3>

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
$sql = "SELECT ar.id, ar.user_name, ar.status, ar.request_date, p.name AS pet_name 
        FROM adoption_requests ar 
        JOIN pets p ON ar.pet_id = p.id 
        ORDER BY ar.request_date DESC";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['user_name']}</td>
        <td>{$row['pet_name']}</td>
        <td><span class='badge bg-".($row['status']=='Pending'?'warning':($row['status']=='Approved'?'success':'danger'))."'>{$row['status']}</span></td>
        <td>{$row['request_date']}</td>
        <td>
            <button class='btn btn-sm btn-success btn-action' data-bs-toggle='modal' data-bs-target='#statusModal' data-id='{$row['id']}' data-status='Approved'>Approve</button>
            <button class='btn btn-sm btn-warning btn-action' data-bs-toggle='modal' data-bs-target='#statusModal' data-id='{$row['id']}' data-status='Denied'>Deny</button>
            <button class='btn btn-sm btn-danger btn-action' data-bs-toggle='modal' data-bs-target='#deleteModal' data-id='{$row['id']}'>Delete</button>
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

<!-- Status Modal -->
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
<button type="submit" name="update_status" class="btn btn-success px-4">Confirm</button>
</div>
</div>
</form>
</div>
</div>
</div>

<!-- Delete Modal -->
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

<!-- Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content shadow-lg" style="border-radius:20px; overflow:hidden;">
<div class="modal-header text-white" style="background-color:#A9745B; border-bottom:none;">
<h5 class="modal-title w-100 text-center"><i class="bi bi-box-arrow-right"></i> Confirm Logout</h5>
</div>
<div class="modal-body text-center py-4" style="background-color:#FFF8F3;">
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
const statusModal = document.getElementById('statusModal');
statusModal.addEventListener('show.bs.modal', function(event){
    const button = event.relatedTarget;
    const requestId = button.getAttribute('data-id');
    const status = button.getAttribute('data-status');
    document.getElementById('statusRequestId').value = requestId;
    document.getElementById('newStatus').value = status;
    document.getElementById('statusMessage').innerText = `Are you sure you want to mark this request as "${status}"?`;
});

const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function(event){
    const button = event.relatedTarget;
    const requestId = button.getAttribute('data-id');
    document.getElementById('deleteRequestId').value = requestId;
});

document.getElementById('confirmLogoutBtn').addEventListener('click', function(){
    window.location.href = 'logout.php';
});
</script>
</body>
</html>