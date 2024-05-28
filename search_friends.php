<?php
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

$query = $_GET['query'];

$sql = "SELECT * FROM users WHERE name LIKE '%$query%' OR pseudo LIKE '%$query%'";
$result = $conn->query($sql);

$friends = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $friends[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'photo' => $row['photo']
        ];
    }
}

echo json_encode(['friends' => $friends]);

$conn->close();
?>
