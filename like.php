<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ece_in";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO likes (post_id, user_id) VALUES ('$post_id', '$user_id')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
    