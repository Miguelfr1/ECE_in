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

$user_id = $_SESSION['user_id'];
$friend_id = $_POST['friend_id'];
$sql_chat = "SELECT id FROM chats WHERE (user1_id = $user_id and user2_id = $friend_id) or (user1_id = $friend_id and user2_id = $user_id)";

$result_chat = $conn->query($sql_chat);
if ($result_chat->num_rows > 0) {
    $row = $result_chat->fetch_assoc();
    $chat_id = $row['id'];
}
else{
    $sql_new = "INSERT INTO chats (user1_id, user2_id) VALUES ('$user_id', '$friend_id')";
    $result_new = $conn->query($sql_new);
    $result_chat = $conn->query($sql_chat);
    $row = $result_chat->fetch_assoc();
    $chat_id = $row['id'];
}

$sql = "SELECT m.*, u.name AS sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.chat_id = '$chat_id' 
        ORDER BY m.created_at ASC";
$result = $conn->query($sql);

$messages = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_name' => $row['sender_name'],
            'message' => $row['message'],
            'created_at' => $row['created_at']
        ];
    }
}

echo json_encode(['messages' => $messages]);

$conn->close();
?>
