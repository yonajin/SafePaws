<?php
$servername = "localhost";
$username = "root";     // or your DB username
$password = "";         // your DB password
$dbname = "safepaws_db"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
