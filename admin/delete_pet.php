<?php
// Turn on error reporting for debugging
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/db.php';
session_start();

// --- SECURITY CHECK: ADMIN LOGIN ---
// Ensure only the admin can access this page (using the dedicated admin session flag)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // FIX: Redirect to the admin-specific login page
    header('location: admin_login.php'); 
    exit();
}

// Ensure an ID is present and is an integer
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "<script>alert('❌ Invalid pet ID provided for deletion.'); window.location='manage_pets.php';</script>";
    exit();
}

$pet_id_to_delete = $_GET['id'];
$msg = "";

// --- FILE CLEANUP (Retrieve Image URL first) ---

// FIX: Using correct primary key 'pet_id'
$sql_select = "SELECT image_url FROM pets WHERE pet_id = ?";
$stmt_select = mysqli_prepare($conn, $sql_select);

if ($stmt_select) {
    mysqli_stmt_bind_param($stmt_select, "i", $pet_id_to_delete);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $image_to_delete = $row['image_url'];
        
        // --- CRITICAL FIX: Correct relative path for file deletion ---
        // Since delete_pet.php is in admin/, we must go up one directory (../) to find uploads/
        $file_path = "../uploads/" . $image_to_delete; 
        
        // Check if the file exists and delete it
        if (!empty($image_to_delete) && $image_to_delete != 'default.jpg' && file_exists($file_path)) {
            if (unlink($file_path)) {
                $msg .= "Image file removed successfully. ";
            } else {
                $msg .= "Warning: Could not delete image file from server. (Check file permissions). ";
            }
        } else {
             $msg .= "No associated image file found to delete or using default image. ";
        }
    } else {
        $msg .= "Pet record not found, continuing deletion attempt. ";
    }
    mysqli_stmt_close($stmt_select);
} else {
    $msg .= "❌ Database error during image lookup. ";
}

// --- DATABASE DELETION (Secure Prepared Statement) ---

// FIX: Using correct primary key 'pet_id'
$sql_delete = "DELETE FROM pets WHERE pet_id = ?";
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

// --- REDIRECTION AND FEEDBACK ---

// Use JavaScript alert for feedback and redirect
echo "<script>alert('{$msg}'); window.location='manage_pets.php';</script>";
exit();
?>