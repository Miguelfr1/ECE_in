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

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['friend_id'];

$sql = "INSERT INTO friend_requests (sender_id, receiver_id) VALUES ('$sender_id', '$receiver_id')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
