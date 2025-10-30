<?php
include '../config/db.php';
session_start();

// SECURITY CHECK
// Ensure only the admin can access this page
if (!isset($_SESSION['admin_name'])) {
    header('location: login.php');
    exit();
}

// Ensure an ID is present and is an integer
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "<script>alert('❌ Invalid pet ID provided for deletion.'); window.location='manage_pets.php';</script>";
    exit();
}

$pet_id_to_delete = $_GET['id'];
$msg = "";

// FILE CLEANUP (Retrieve Image URL first)

// Prepare statement to get the image_url before deletion
$sql_select = "SELECT image_url FROM pets WHERE id = ?";
$stmt_select = mysqli_prepare($conn, $sql_select);

if ($stmt_select) {
    mysqli_stmt_bind_param($stmt_select, "i", $pet_id_to_delete);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $image_to_delete = $row['image_url'];
        $file_path = "uploads/" . $image_to_delete;
        
        // Check if the file exists and delete it (skip deletion if it's the default/a problem)
        if (!empty($image_to_delete) && file_exists($file_path)) {
            if (unlink($file_path)) {
                $msg .= "Image file removed successfully. ";
            } else {
                $msg .= "Warning: Could not delete image file from server. ";
            }
        }
    }
    mysqli_stmt_close($stmt_select);
}

// DATABASE DELETION (Secure Prepared Statement)

$sql_delete = "DELETE FROM pets WHERE id = ?";
$stmt_delete = mysqli_prepare($conn, $sql_delete);

if ($stmt_delete) {
    mysqli_stmt_bind_param($stmt_delete, "i", $pet_id_to_delete);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        $msg .= "Pet record deleted successfully!";
    } else {
        $msg .= "❌ Error deleting pet record: " . mysqli_stmt_error($stmt_delete);
    }
    mysqli_stmt_close($stmt_delete);
} else {
    $msg .= "❌ Database query preparation failed for deletion.";
}

// REDIRECTION AND FEEDBACK

// Use JavaScript alert for feedback and redirect
echo "<script>alert('{$msg}'); window.location='manage_pets.php';</script>";
exit();
?>