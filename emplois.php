<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';


// Sélectionner toutes les offres d'emploi reçues par l'utilisateur connecté
$receiver_username = $_SESSION['username'];
$sql = "SELECT job_offers.*, posts.content AS post_content, posts.username AS post_author 
        FROM job_offers 
        INNER JOIN posts ON job_offers.post_id = posts.id 
        WHERE job_offers.receiver_username = '$receiver_username' 
        ORDER BY job_offers.created_at DESC";

$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emplois Reçus</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Emplois Reçus</h1>
            <a href="index.php">Retour à l'accueil</a>
        </div>

        <div class="navigation">
            <a href="index.php">Accueil</a>
            <a href="mon_reseau.php">Mon Réseau</a>
            <a href="fil_d_actualite.php">Fil d'actualité</a>
            <a href="profile.php">Vous</a>
            <a href="notifications.php">Notifications</a>
            <a href="messagerie.php">Messagerie</a>
            <a href="emplois.php">Emplois</a>
            <a href="logout.php">Déconnexion</a>
        </div>

        <div class="section">
            <h2>Offres d'emploi reçues</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='job-offer'>";
                    echo "<p><strong>Expéditeur:</strong> " . htmlspecialchars($row['sender_username']) . "</p>";
                    echo "<p><strong>Contenu de la publication associée:</strong> " . htmlspecialchars($row['post_content']) . "</p>";
                    echo "<p><strong>Auteur de la publication:</strong> " . htmlspecialchars($row['post_author']) . "</p>";
                    echo "<p><strong>Date:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Aucune offre d'emploi reçue pour le moment.</p>";
            }
            ?>
        </div>

        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
