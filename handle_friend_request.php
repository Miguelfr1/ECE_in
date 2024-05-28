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

$request_id = $_POST['request_id'];
$action = $_POST['action']; // 'accept' or 'decline'
$user_id = $_SESSION['user_id'];

// Fetch request details
$sql = "SELECT * FROM friend_requests WHERE id='$request_id' AND receiver_id='$user_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $request = $result->fetch_assoc();
    if ($action == 'accept') {
        // Add to friends table
        $sender_id = $request['sender_id'];
        $receiver_id = $request['receiver_id'];
        $conn->query("INSERT INTO friends (user_id, friend_id) VALUES ('$sender_id', '$receiver_id'), ('$receiver_id', '$sender_id')");
        // Update request status
        $conn->query("UPDATE friend_requests SET status='accepted' WHERE id='$request_id'");
        echo json_encode(['success' => true]);
    } elseif ($action == 'decline') {
        // Update request status
        $conn->query("UPDATE friend_requests SET status='declined' WHERE id='$request_id'");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Request not found']);
}

$conn->close();
?>
