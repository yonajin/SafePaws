<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($conn)) {
    include(__DIR__ . '/../config/db.php');
}

$pending_count = 0;

$sql = "SELECT id, name, content, image_url, date_published 
        FROM care_tips 
        WHERE status = 'Published' 
        ORDER BY date_published DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
$icon_class = "bi bi-heart-fill";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Care Tips</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
    body {
      font-family: 'Poppins', sans-serif;
    }

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
      color: #ffe6d5 !important;
      color: #ffe6d5 !important; /* optional hover color */
    }

    .navbar-brand {
      font-family: 'Quicksand', sans-serif;
      color: #FFF8F3 !important;
      font-weight: 700;
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

    
    .hero {
      background-image: url("../assets/images/dogcat.webp");
      background-position: center;
      background-size: cover;
      background-repeat: no-repeat;
      height: 700px;
      color: #FFF8F3;
      
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start; 
      text-align: left;        
      padding-left: 100px;     
    }

    .hero h1 {
      font-family: 'Quicksand', sans-serif;
      font-weight: 700;
      font-size: 50px;
    }
    .hero p {
      font-family: 'Quicksand', sans-serif;
      font-size: 22px;
      font-weight: 600;
    }
    .hero button {
      background-color: #FFB6A0;
      font-family: 'Quicksand', sans-serif;
      font-weight: 600;
      border: none;
      color: #0A0000;
      width: 175px;
    }
    .section-title {
      font-weight: 600;
      margin-bottom: 20px;
    }

    .about-img {
      width: 100%;
      border-radius: 10px;
    }
    footer {
      background: #f1ece9;
      text-align: center;
      padding: 10px;
      font-size: 14px;
      color: #333;
      margin-top: 40px;
    }

    /* === Care Tip Specific Styles === */
    .tip-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        height: 100%;
        height: 100%; /* Ensures all cards in a row are the same height */
    }
    .tip-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .tip-img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }
    .tip-title {
        font-family: 'Quicksand', sans-serif;
        font-weight: 700;
        color: #A9745B;
        margin-bottom: 10px;
    }
    .tip-content {
        color: #555;
        font-size: 0.95rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        /* Truncate content after a few lines */
        display: -webkit-box;
        -webkit-line-clamp: 3; /* Show up to 3 lines */
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 15px;
    }
    .btn-read-more {
        background-color: #FFB6A0;
        color: #0A0000;
        font-weight: 600;
        border: none;
    }
    .btn-read-more:hover {
        background-color: #ff997a;
        color: #0A0000;
    }

    </style>
</head>
<body>

<?php include(__DIR__ . '/../includes/user_navbar.php'); ?>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="section-title display-5" style="color:#A9745B;"><i class="bi bi-book me-2"></i> Pet Care Wisdom</h2>
        <p class="lead text-muted">Essential guides and tips to keep your beloved pet happy and healthy.</p>
    </div>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            
            <?php while ($tip = mysqli_fetch_assoc($result)): 
                // Determine the image path
                $image_path = !empty($tip['image_url']) 
  ? "assets/images/" . htmlspecialchars($tip['image_url']) 
  : "assets/images/default-tip.webp";
            ?>
            <div class="col d-flex">
                <div class="card tip-card flex-fill">
                    <img src="../assets/images/<?php echo htmlspecialchars($tip['image_url']); ?>" 
                     class="tip-img" 
                     alt="Image for <?php echo htmlspecialchars($tip['name']); ?>">

                    <div class="card-body">
                        <h5 class="tip-title"><?php echo htmlspecialchars($tip['name']); ?></h5>
                        <p class="text-muted small">Published: <?php echo date('M d, Y', strtotime($tip['date_published'])); ?></p>
                        <p class="tip-content"><?php echo htmlspecialchars($tip['content']); ?></p>
                        
                        <button class="btn btn-read-more" 
                                data-bs-toggle="modal" 
                                data-bs-target="#tipModal"
                                data-id="<?php echo $tip['id']; ?>"
                                data-title="<?php echo htmlspecialchars($tip['name']); ?>"
                                data-content="<?php echo htmlspecialchars($tip['content']); ?>"
                                data-image="<?php echo $image_path; ?>"
                                data-published="<?php echo date('M d, Y', strtotime($tip['date_published'])); ?>">
                            Read More
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>

        </div>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">
            <i class="bi bi-info-circle me-2"></i> We don't have any published care tips yet! Check back soon.
        </div>
    <?php endif; ?>
</div>
<div class="modal fade" id="tipModal" tabindex="-1" aria-labelledby="tipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0 px-5 pb-5">
                <img id="modalTipImage" class="img-fluid rounded-3 mb-4" style="max-height: 400px; width: 100%; object-fit: cover;" alt="Tip Image">
                <h3 id="modalTipTitle" class="tip-title display-6"></h3>
                <p class="text-muted small mb-3">Published: <span id="modalTipPublished"></span></p>
                <div id="modalTipContent" class="tip-detail-content"></div>
            </div>
        </div>
    </div>
</div>

<?php include(__DIR__ . '/../includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// === JAVASCRIPT TO POPULATE MODAL ===
const tipModalElement = document.getElementById('tipModal');
if (tipModalElement) {
    tipModalElement.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget; // Button that triggered the modal
        
        // Retrieve data from the button's data attributes
        const title = button.getAttribute('data-title');
        const content = button.getAttribute('data-content');
        const image = button.getAttribute('data-image');
        const published = button.getAttribute('data-published');

        // Update the modal's elements
        document.getElementById('modalTipTitle').textContent = title;
        document.getElementById('modalTipContent').innerHTML = content.replace(/\n/g, '<br>'); // Format content
        document.getElementById('modalTipImage').src = image;
        document.getElementById('modalTipPublished').textContent = published;
    });
}
// === END JAVASCRIPT ===
</script>
    
</body>
</html>
