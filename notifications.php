<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'accept' || $_POST['action'] == 'reject' || $_POST['action'] == 'read') {
        $request_id = $conn->real_escape_string($_POST['request_id']);
        $new_status = $_POST['action'] == 'accept' ? 'accepted' : ($_POST['action'] == 'reject' ? 'rejected' : 'read');
        
        $sql = "UPDATE notifications SET statut='$new_status' WHERE id='$request_id'";
        if (!$conn->query($sql)) {
            die("Erreur de mise à jour: " . $conn->error);
        }

        if ($_POST['action'] == 'accept') {
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
        }
    } elseif ($_POST['action'] == 'read_all') {
        $sql = "UPDATE notifications SET statut='read' WHERE receiver='$username' AND statut='pending'";
        if (!$conn->query($sql)) {
            die("Erreur de mise à jour: " . $conn->error);
        }
    }
    header("Location: notifications.php");
    exit();
}

$user_sql = "SELECT * FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - ECE In</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function fetchNotifications() {
            fetch('fetch_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationsDiv = document.getElementById('notif');
                    notificationsDiv.innerHTML = '<h2>Notifications</h2>' + 
                        '<form method="POST">' + 
                        '<input type="hidden" name="action" value="read_all">' + 
                        '<button type="submit" class="read-all-button">Marquer toutes comme lues</button>' + 
                        '</form>';

                    if (data.length > 0) {
                        data.forEach(row => {
                            let notifHtml = '';
                            if(row.types == 'friend_request') {
                                notifHtml = "<div class='profile-container1'><a href='user_profile.php?username=" + row.sender + "'><img src='" + row.profile_picture + "' alt='Profile Picture' class='profile-picture1'></a><p>" + row.sender + 
                                " <form method='POST' style='display:inline;'>" +
                                "<input type='hidden' name='request_id' value='" + row.id + "'>" +
                                "<button type='submit' name='action' value='accept' class='acceptrefus'>Accepter</button>" +
                                "<button type='submit' name='action' value='reject' class='acceptrefus'>Rejeter</button>" +
                                "</form></p></div><br>";
                            } else if(row.types == 'comment') {
                                notifHtml = "<div class='profile-container1'><p>" + row.sender + " a commenté votre publication." +
                                    "<form method='POST' style='display:inline;'>" +
                                    "<input type='hidden' name='request_id' value='" + row.id + "'>" +
                                    "<button type='submit' name='action' value='read' class='LU'>Lu</button>" +
                                    "</form></p></div><br>";
                            } else if(row.types == 'like') {
                                notifHtml = "<div class='profile-container1'><p>" + row.sender + " a liké votre publication." +
                                    "<form method='POST' style='display:inline;'>" +
                                    "<input type='hidden' name='request_id' value='" + row.id + "'>" +
                                    "<button type='submit' name='action' value='read' class='LU'>Lu</button>" +
                                    "</form></p></div><br>";
                            } else if(row.types == 'new_post') {
                                notifHtml = "<div class='profile-container1'><p>" + row.sender + " a publié un nouvel évenement." +
                                    "<form method='POST' style='display:inline;'>" +
                                    "<input type='hidden' name='request_id' value='" + row.id + "'>" +
                                    "<button type='submit' name='action' value='read' class='LU'>Lu</button>" +
                                    "</form></p></div><br>";
                            }
                            
                            else if(row.types == 'offre_emplois') {
                                notifHtml = "<div class='profile-container1'><p>" + row.sender +  " vous a envoyé une offre d'emplois." +
                                    "<form method='POST' style='display:inline;'>" +
                                    "<input type='hidden' name='request_id' value='" + row.id + "'>" +
                                    "<button type='submit' name='action' value='read' class='LU'>Lu</button>" +
                                    "</form></p></div><br>";
                            }
                            notificationsDiv.innerHTML += notifHtml;
                        });
                    } else {
                        notificationsDiv.innerHTML += "<p>Aucune notification en attente.</p>";
                    }

                    // Update notification count
                    const notificationCount = data.filter(notification => notification.statut === 'pending').length;
                    $('.notificationb p').text(notificationCount);
                })
                .catch(error => console.error('Erreur:', error));
        }

        setInterval(fetchNotifications, 1000); // Fetch notifications every 5 seconds

        $(document).ready(function() {
            fetchNotifications(); // Initial fetch
        });
    </script>
</head>
<body onload="fetchNotifications()">
    <div class="wrapper">
        <div class="header">
            <img src="logoPiscine.png" alt="ECE Paris Logo">
            <h1>ECE In: Social Media Professionnel de l’ECE Paris</h1>
        </div>

        <div class="leftcolonne">
            <div class="navigation">
                <a href="index.php">Accueil</a><br><br><br>
                <a href="mon_reseau.php">Mon Réseau</a><br><br><br>
                <div class="notificationb">
                    <?php
                    $nbr_notif_sql = "SELECT COUNT(*) FROM notifications WHERE receiver = '$username' and statut = 'pending'";
                    $reponse = $conn->query($nbr_notif_sql);
                    $resultat = $reponse->fetch_assoc();
                    $nbr_notif = $resultat['COUNT(*)'];
                    echo "<p>$nbr_notif</p>";
                    ?>
                </div>
                <a href="notifications.php" class="navcurrent">Notifications</a><br><br><br>
                <a href="messagerie.php">Messagerie</a><br><br><br>
                <a href="fil_d_actualite.php">Fil d'actualité</a><br><br><br>
                <a href="emplois.php">Emplois</a>
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
            <div class="section" id="notif">
                <!-- Notifications will be dynamically inserted here -->
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
