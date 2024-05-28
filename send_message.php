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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$chat_id = $_POST['chat_id'];
$sender_id = $_SESSION['user_id'];
$message = $_POST['message'];

$sql = "INSERT INTO messages (chat_id, sender_id, message) VALUES ('$chat_id', '$sender_id', '$message')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
