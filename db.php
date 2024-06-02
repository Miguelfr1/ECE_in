<?php
$servername = "localhost";
$username = "id22247170_root";
$password = "r6=WF>By88QPx*lh";
$dbname = "id22247170_ece_in";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
