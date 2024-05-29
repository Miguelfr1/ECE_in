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

$user_id = $_GET['user_id'];

// Get friend ids
$friend_sql = "SELECT friend_id FROM friends WHERE user_id='$user_id'";
$friend_result = $conn->query($friend_sql);

$friend_ids = [];
if ($friend_result->num_rows > 0) {
    while($row = $friend_result->fetch_assoc()) {
        $friend_ids[] = $row['friend_id'];
    }
}

if (!empty($friend_ids)) {
    $friend_ids_string = implode(',', $friend_ids);
    $sql = "SELECT posts.*, users.pseudo AS user_name, 
                   (SELECT COUNT(*) FROM likes WHERE post_id=posts.id) AS like_count 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id IN ($friend_ids_string) 
            ORDER BY posts.reg_date DESC";
    $result = $conn->query($sql);

    $posts = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $post_id = $row['id'];
            $comment_sql = "SELECT comments.*, users.pseudo AS commenter_name 
                            FROM comments 
                            JOIN users ON comments.user_id = users.id 
                            WHERE post_id='$post_id' 
                            ORDER BY reg_date DESC";
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

            $recherche = "SELECT * FROM likes WHERE post_id = $post_id and user_id = $user_id;";
            $result_recherche = $conn->query($recherche);
            if( $result_recherche->num_rows == 0) {
                $is_like = 0;
            }
            else{
                $is_like = 1;
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
                'comments' => $comments,
                'is_like' => $is_like,
            ];
        }
    }

    echo json_encode(['posts' => $posts]);
} else {
    echo json_encode(['posts' => []]);
}

$conn->close();
?>