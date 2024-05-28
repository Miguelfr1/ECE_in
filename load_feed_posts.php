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

$user_id = $_GET['user_id'];

// Fetch posts by the user, including user_name, like_count, and comments with commenter_name
$sql = "SELECT p.id, p.title, p.content, p.media, p.location, p.reg_date, u.name as user_name, 
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.user_id='$user_id' 
        ORDER BY p.reg_date DESC";

$result = $conn->query($sql);

$posts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $post_id = $row['id'];
        $comment_sql = "SELECT c.content, u.name as commenter_name 
                        FROM comments c 
                        JOIN users u ON c.user_id = u.id 
                        WHERE c.post_id='$post_id' 
                        ORDER BY c.reg_date DESC";
        $comment_result = $conn->query($comment_sql);

        $comments = [];
        if ($comment_result->num_rows > 0) {
            while($comment_row = $comment_result->fetch_assoc()) {
                $comments[] = [
                    'content' => $comment_row['content'],
                    'commenter_name' => $comment_row['commenter_name']
                ];
            }
        }

        $posts[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'content' => $row['content'],
            'media' => $row['media'],
            'location' => $row['location'],
            'reg_date' => $row['reg_date'],
            'user_name' => $row['user_name'],
            'like_count' => $row['like_count'],
            'comments' => $comments
        ];
    }
}

echo json_encode(['posts' => $posts]);

$conn->close();
?>
