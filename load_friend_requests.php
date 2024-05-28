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

$user_id = $_SESSION['user_id'];

$sql = "SELECT fr.id, u.name AS sender_name FROM friend_requests fr JOIN users u ON fr.sender_id = u.id WHERE fr.receiver_id = '$user_id' AND fr.status = 'pending'";
$result = $conn->query($sql);

$requests = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $requests[] = [
            'id' => $row['id'],
            'sender_name' => $row['sender_name']
        ];
    }
}

echo json_encode(['requests' => $requests]);

$conn->close();
?>
