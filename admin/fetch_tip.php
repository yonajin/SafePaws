<?php

include '../config/db.php'; 

header('Content-Type: application/json');

$response = ['error' => 'Invalid request: Tip ID missing or invalid.'];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $tip_id = $_GET['id'];

    $sql = "SELECT id, name, content, image_url FROM care_tips WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $tip_id); 
        
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        $tip = mysqli_fetch_assoc($result);

        if ($tip) {
            $response = $tip;
        } else {
            $response = ['error' => 'Tip not found in database.'];
        }
        
        mysqli_stmt_close($stmt);
    } else {
        $response = ['error' => 'Database error during query preparation.'];
    }
}

// Output the final JSON response
echo json_encode($response);
?>