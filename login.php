<?php
session_start();

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

$pseudo = $_POST['pseudo'];
$email = $_POST['email'];

$sql = "SELECT * FROM users WHERE pseudo='$pseudo' AND email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['is_admin'] = $user['is_admin'];
    echo json_encode([
        'success' => true,
        'user_id' => $user['id'],
        'is_admin' => (int)$user['is_admin'],
        'name' => $user['name'],
        'photo' => $user['photo'],
        'background' => $user['background']
    ]);
} else {
    echo json_encode(['success' => false]);
}

$conn->close();
?>
