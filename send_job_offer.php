<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_id'])) {
    $sender_username = $_SESSION['username'];
    $post_id = $_POST['post_id'];

    // Récupérer le nom d'utilisateur de la publication associée
    include 'db.php';


    $sql_post = "SELECT username FROM posts WHERE id='$post_id'";
    $result_post = $conn->query($sql_post);

    if ($result_post->num_rows > 0) {
        $row_post = $result_post->fetch_assoc();
        $receiver_username = $row_post['username'];

        // Insérer l'offre d'emploi dans la table job_offers
        $sql_insert = "INSERT INTO job_offers (sender_username, receiver_username, post_id) 
                       VALUES ('$sender_username', '$receiver_username', '$post_id')";

        if ($conn->query($sql_insert) === TRUE) {
            echo "Offre d'emploi envoyée avec succès à $receiver_username!";
        } else {
            echo "Erreur: " . $sql_insert . "<br>" . $conn->error;
        }
    } else {
        echo "Publication non trouvée.";
    }

    $conn->close();
}
?>
