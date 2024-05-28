<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "ece_in";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'];
$content = $_POST['content'];
$media = $_POST['media'];
$location = $_POST['location'];
$date = $_POST['date'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO posts (user_id, title, content, media, location, date)
VALUES ('$user_id', '$title', '$content', '$media', '$location', '$date')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'user_id' => $user_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
