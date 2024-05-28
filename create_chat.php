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

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user1_id = $_SESSION['user_id'];
$user2_id = $_POST['friend_id'];

$sql = "INSERT INTO chats (user1_id, user2_id) VALUES ('$user1_id', '$user2_id')";

if ($conn->query($sql) === TRUE) {
    $chat_id = $conn->insert_id;
    echo json_encode(['success' => true, 'chat_id' => $chat_id]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
