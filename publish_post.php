<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
    $username = $_SESSION['username'];
    $user_id_query = "SELECT id FROM users WHERE username = '$username'";
    $user_id_result = $conn->query($user_id_query);
    $user_id_row = $user_id_result->fetch_assoc();
    $user_id = $user_id_row['id'];
    $content = $_POST['content'];
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $datetime = isset($_POST['datetime']) && !empty($_POST['datetime']) ? $_POST['datetime'] : NULL;
    $feeling = isset($_POST['feeling']) ? $_POST['feeling'] : '';
    $visibility = $_POST['visibility'];
    $friends = isset($_POST['friends']) ? $_POST['friends'] : [];
    $media_path = '';

    // Créer le dossier de l'utilisateur s'il n'existe pas déjà
    $user_dir = "uploads/$user_id/posts";
    if (!is_dir($user_dir)) {
        mkdir($user_dir, 0777, true);
    }

    if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
        $target_file = $user_dir . '/' . basename($_FILES["media"]["name"]);
        move_uploaded_file($_FILES["media"]["tmp_name"], $target_file);
        $media_path = $target_file;
    }

    if (is_null($datetime)) {
        $sql = "INSERT INTO posts (username, content, location, feeling, media_path, visibility, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $content, $location, $feeling, $media_path, $visibility);
    } else {
        $sql = "INSERT INTO posts (username, content, location, datetime, feeling, media_path, visibility, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $username, $content, $location, $datetime, $feeling, $media_path, $visibility);
    }

    if ($stmt->execute()) {
        $post_id = $stmt->insert_id;

        // Insert visibility for selected friends
        if ($visibility == 'selected_friends' && !empty($friends)) {
            foreach ($friends as $friend) {
                $visibility_sql = "INSERT INTO post_visibility (post_id, friend_username) VALUES (?, ?)";
                $visibility_stmt = $conn->prepare($visibility_sql);
                $visibility_stmt->bind_param("is", $post_id, $friend);
                $visibility_stmt->execute();
            }
        }

        $response['status'] = 'success';
        $response['post'] = [
            'id' => $post_id,
            'username' => $username,
            'content' => $content,
            'location' => $location,
            'datetime' => $datetime,
            'feeling' => $feeling,
            'media_path' => $media_path,
            'created_at' => date('Y-m-d H:i:s'),
            'visibility' => $visibility,
            'friends' => $friends
        ];

        // Obtenir l'image de profil de l'utilisateur
        $profile_sql = "SELECT profile_picture FROM users WHERE username = ?";
        $stmt = $conn->prepare($profile_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $profile_result = $stmt->get_result();
        $profile_row = $profile_result->fetch_assoc();
        $response['post']['profile_picture'] = $profile_row['profile_picture'];
    } else {
        $response['status'] = 'error';
        $response['message'] = $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
