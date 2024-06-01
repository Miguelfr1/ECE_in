<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $request_id = $conn->real_escape_string($_POST['request_id']);
    if ($_POST['action'] == 'accept') {
        $sql = "UPDATE notifications SET statut='accepted' WHERE id='$request_id'";
        if (!$conn->query($sql)) {
            die("Erreur de mise à jour: " . $conn->error);
        }

        $sql = "SELECT sender, receiver FROM notifications WHERE id='$request_id'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sender = $row['sender'];
            $receiver = $row['receiver'];

            $sql = "INSERT INTO friends (user1, user2) VALUES ('$sender', '$receiver')";
            if (!$conn->query($sql)) {
                die("Erreur d'insertion: " . $conn->error);
            }
        } else {
            die("Notification non trouvée.");
        }
    } elseif ($_POST['action'] == 'reject') {
        $sql = "UPDATE notifications SET statut='rejected' WHERE id='$request_id'";
        if (!$conn->query($sql)) {
            die("Erreur de mise à jour: " . $conn->error);
        }
    } elseif ($_POST['action'] == 'read') {
        $sql = "UPDATE notifications SET statut='read' WHERE id='$request_id'";
        if (!$conn->query($sql)) {
            die("Erreur de mise à jour: " . $conn->error);
        }
    }
    header("Location: notifications.php");
    exit();
}

$sql = "SELECT notifications.*, users.profile_picture 
        FROM notifications 
        JOIN users ON notifications.sender = users.username 
        WHERE receiver='$username' AND statut='pending'";
$requests_result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - ECE In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>ECE In: Social Media Professionnel de l’ECE Paris</h1>
            <img src="https://via.placeholder.com/150" alt="ECE Paris Logo">
        </div>

        <div class="navigation">
            <a href="index.php">Accueil</a>
            <a href="fil_d_actualite.php">Fil d'actualité</a>

            <a href="mon_reseau.php">Mon Réseau</a>
            <a href="profile.php">Vous</a>
            <a href="notifications.php">Notifications
                <?php

                    $nbr_notif_sql = "SELECT COUNT(*) FROM notifications WHERE receiver = '".$username."' and statut = 'pending';";
                    $reponse = $conn->query($nbr_notif_sql);
                    $resultat = $reponse->fetch_assoc();
                    $nbr_notif = $resultat['COUNT(*)'];
                    echo "<p>".$nbr_notif."</p>";
                
                ?>
            </a>
            <a href="messagerie.php">Messagerie</a>
            <a href="emplois.php">Emplois</a>
            <a href="logout.php">Déconnexion</a>
        </div>

        <div class="section">
            <h2>Demandes d'Amis</h2>
            <?php
            if ($requests_result->num_rows > 0) {
                while ($row = $requests_result->fetch_assoc()) {
                    if($row['types'] == 'friend_request'){
                        echo "<p><a href='user_profile.php?username=" . htmlspecialchars($row['sender']) . "'><img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'></a>" . htmlspecialchars($row['sender']) . 
                         " <form method='POST' style='display:inline;'>
                            <input type='hidden' name='request_id' value='" . htmlspecialchars($row['id']) . "'>
                            <button type='submit' name='action' value='accept'>Accepter</button>
                            <button type='submit' name='action' value='reject'>Rejeter</button>
                          </form></p>";
                    }
                    elseif($row["types"] == "comment"){
                        echo "  <p>".$row['sender']." a commenté votre publication.
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='request_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <button type='submit' name='action' value='read'>Lu</button>
                                    </form>
                                </p>";
                    }
                    elseif($row["types"] == "like"){
                        echo "  <p>".$row['sender']." a liké votre publication.
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='request_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <button type='submit' name='action' value='read'>Lu</button>
                                    </form>
                                </p>";
                    }
                    elseif($row["types"] == "new_post"){
                        echo "  <p>".$row['sender']." a publié un nouvel évenement.
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='request_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <button type='submit' name='action' value='read'>Lu</button>
                                    </form>
                                </p>";
                    }
                }
            } else {
                echo "<p>Aucune notification en attente.</p>";
            }
            ?>
        </div>

        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>