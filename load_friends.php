<?php
$servername = "localhost:3307";
$username = "root";
$password = "";
$dbname = "ece_in";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_GET['user_id'];

$sql = "SELECT users.id, users.pseudo, users.name, users.photo FROM friends 
        JOIN users ON friends.friend_id = users.id 
        WHERE friends.user_id = '$user_id'";
$result = $conn->query($sql);

$friends = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $friends[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'pseudo' => $row['pseudo'],
            'photo' => $row['photo']
        ];
    }
}

echo json_encode(['friends' => $friends]);

$conn->close();
?>
