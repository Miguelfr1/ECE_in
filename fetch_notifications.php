<?php
session_start();
include 'db.php';

$current_user = $_SESSION['username'];
$sql = "SELECT * FROM notifications WHERE receiver = '$current_user' AND statut = 'pending ' ORDER BY id DESC";
$result = $conn->query($sql);

$notifications = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
echo json_encode($notifications);
?>
