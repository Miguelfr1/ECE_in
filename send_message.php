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

$friend_id = $_POST['Friend_id'];
$sender_id = $_SESSION['user_id'];
$message = $_POST['message'];

$sql_chat = "SELECT id FROM chats WHERE (user1_id = $sender_id and user2_id = $friend_id) or (user1_id = $friend_id and user2_id = $sender_id)";

$result_chat = $conn->query($sql_chat);
if ($result_chat->num_rows > 0) {
    $row = $result_chat->fetch_assoc();
    $chat_id = $row['id'];
}

$sql = "INSERT INTO messages (chat_id, sender_id, message) VALUES ('$chat_id', '$sender_id', '$message')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
