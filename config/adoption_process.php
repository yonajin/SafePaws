<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('db.php');
session_start();

if (isset($_POST['submit'])) {

    // ✅ Make sure user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('You must be logged in to apply for adoption.'); window.location='../login.php';</script>";
        exit();
    }

    // ✅ Collect and sanitize form inputs
    $user_id        = $_SESSION['user_id'];
    $first_name     = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name      = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email          = mysqli_real_escape_string($conn, $_POST['email']);
    $address        = mysqli_real_escape_string($conn, $_POST['address']);
    $phone          = mysqli_real_escape_string($conn, $_POST['phone']);
    $birth_date     = mysqli_real_escape_string($conn, $_POST['birth_date']);
    $occupation     = mysqli_real_escape_string($conn, $_POST['occupation']);
    $company        = mysqli_real_escape_string($conn, $_POST['company']);
    $social_media   = mysqli_real_escape_string($conn, $_POST['social_media']);
    $classification = mysqli_real_escape_string($conn, $_POST['classification']);
    $adopted_before = mysqli_real_escape_string($conn, $_POST['adopted_before']);
    $reason         = mysqli_real_escape_string($conn, $_POST['reason']);
    $pet_id         = intval($_POST['pet_id']); // ✅ ensure integer
    $pet_name       = mysqli_real_escape_string($conn, $_POST['pet_name']);

    // ✅ Double-check that pet_id exists
    if (empty($pet_id)) {
        echo "<script>alert('Pet ID is missing. Please select a pet again.'); window.location='../user/user_adopt.php';</script>";
        exit();
    }

    // ✅ Handle file upload
    $targetDir = "../uploads/";
    if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = basename($_FILES["valid_id"]["name"]);
    $fileTmp = $_FILES["valid_id"]["tmp_name"];
    $fileSize = $_FILES["valid_id"]["size"];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

    // ✅ Validate upload
    if (!empty($fileName)) {
        if (in_array($fileExt, $allowed)) {
            if ($fileSize <= 8 * 1024 * 1024) { // 8MB limit
                $newFileName = time() . "_" . uniqid() . "." . $fileExt;
                $targetFilePath = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmp, $targetFilePath)) {

                    // ✅ Insert adoption request
                    $query = "INSERT INTO adoption_requests 
                        (user_id, pet_id, pet_name, first_name, last_name, email, address, phone, birth_date, occupation, company, social_media, classification, adopted_before, reason, valid_id, status, request_date)
                        VALUES 
                        ('$user_id', '$pet_id', '$pet_name', '$first_name', '$last_name', '$email', '$address', '$phone', '$birth_date', '$occupation', '$company', '$social_media', '$classification', '$adopted_before', '$reason', '$targetFilePath', 'Pending', NOW())";

                    if (mysqli_query($conn, $query)) {
                        echo "<script>alert('Application submitted successfully! Our team will review your request.'); window.location='../user/user_adoption_status.php';</script>";
                        exit();
                    } else {
                        echo "<script>alert('Database error: " . mysqli_error($conn) . "'); window.history.back();</script>";
                        exit();
                    }

                } else {
                    echo "<script>alert('Failed to upload file. Please try again.'); window.history.back();</script>";
                    exit();
                }
            } else {
                echo "<script>alert('File is too large. Max size is 8MB.'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file type. Please upload JPG, PNG, or PDF only.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Please upload a valid ID file.'); window.history.back();</script>";
        exit();
    }

} else {
    echo "<script>alert('Invalid form submission.'); window.location='../user/user_adopt.php';</script>";
    exit();
}
?>
