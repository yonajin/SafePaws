<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['pet_id'])) {
    $pet_id = (int)$_GET['pet_id'];
    $user_name = mysqli_real_escape_string($conn, $_SESSION['username']);

    // Check if already requested
    $check = mysqli_query($conn, "SELECT * FROM adoption_requests WHERE pet_id=$pet_id AND user_name='$user_name'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('You have already requested this pet.'); window.location='pets.php';</script>";
        exit();
    }

    // Insert request
    $sql = "INSERT INTO adoption_requests (user_name, pet_id) VALUES ('$user_name', $pet_id)";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Adoption request submitted successfully!'); window.location='pets.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "'); window.location='pets.php';</script>";
    }
} else {
    header("Location: pets.php"); //change to pet gallery page
    exit();
}
?>