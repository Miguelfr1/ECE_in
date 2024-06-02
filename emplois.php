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
$username = $_SESSION['username'];

// Récupérer les informations de l'utilisateur
$user_sql = "SELECT * FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emplois Reçus</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchNotifications() {
            $.ajax({
                url: 'fetch_notifications.php',
                method: 'GET',
                success: function(response) {
                    const notifications = JSON.parse(response);
                    const notificationCount = notifications.length;
                    $('.notificationb p').text(notificationCount);
                }
            });
        }

        $(document).ready(function() {
            setInterval(fetchNotifications, 1000); // Fetch notifications every 1 second
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Emplois Reçus</h1>
        </div>

        <div class="leftcolonne">
            <div class="navigation">
                <a href="index.php">Accueil</a><br><br><br>
                <a href="mon_reseau.php">Mon Réseau</a><br><br><br>
                <div class="notificationb">
                    <?php
                    $nbr_notif_sql = "SELECT COUNT(*) as count FROM notifications WHERE receiver = '".$username."' AND statut = 'pending'";
                    $reponse = $conn->query($nbr_notif_sql);
                    $resultat = $reponse->fetch_assoc();
                    $nbr_notif = $resultat['count'];
                    echo "<p>".$nbr_notif."</p>";
                    ?>
                </div>
                <a href="notifications.php">Notifications</a><br><br><br>
                <a href="messagerie.php">Messagerie</a><br><br><br>
                <a href="fil_d_actualite.php">Fil d'actualité</a><br><br><br>
                <a href="emplois.php" class="navcurrent">Emplois</a>
            </div>
        </div>
        <div class="menu">
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Menu Icon" class="menu-icon" onclick="toggleDropdown()">
            <p class="nom-profil"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <div id="myDropdown" class="dropdown-content">
                <a href="profile.php">Vous</a>
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
        <script>
            function toggleDropdown() {
                document.getElementById("myDropdown").classList.toggle("show");
            }

            // Fermer le dropdown si on clique en dehors
            window.onclick = function(event) {
                if (!event.target.matches('.menu-icon')) {
                    var dropdowns = document.getElementsByClassName("dropdown-content");
                    for (var i = 0; i < dropdowns.length; i++) {
                        var openDropdown = dropdowns[i];
                        if (openDropdown.classList.contains('show')) {
                            openDropdown.classList.remove('show');
                        }
                    }
                }
            }
        </script>
        <div class="rightcolonne">
            <div class="section">
                <h2>Offres d'emploi reçues</h2>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='job-offer'>";
                        echo "<p><strong>Expéditeur:</strong> " . htmlspecialchars($row['sender_username']) . "</p>";
                        echo "<p><strong>Type d'emploi:</strong> " . htmlspecialchars($row['offer_type']) . "</p>";
                        echo "<p><strong>Contenu de la publication associée:</strong> " . htmlspecialchars($row['post_content']) . "</p>";
                        echo "<p><strong>Date:</strong> " . htmlspecialchars($row['created_at']) . "</p>";
                        echo "</div>";
                    }
                } else {
                    echo "<p>Aucune offre d'emploi reçue pour le moment.</p>";
                }
                ?>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
