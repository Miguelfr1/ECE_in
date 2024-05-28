<?php
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
$name = $_POST['name'];
$photo = $_POST['photo'];
$background = $_POST['background'];

$sql = "INSERT INTO users (pseudo, email, name, photo, background)
VALUES ('$pseudo', '$email', '$name', '$photo', '$background')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
    header("Location: index.html");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
