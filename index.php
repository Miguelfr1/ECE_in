<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';
$username = $_SESSION['username'];
$user_sql = "SELECT * FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

$time_actu = date("Y-m-d H:i:s");

$carousel_sql = "
    SELECT posts.*, users.profile_picture 
    FROM posts 
    INNER JOIN users ON posts.username = users.username 
    WHERE (posts.username = '$username'
    OR posts.username IN (
        SELECT CASE
            WHEN user1 = '$username' THEN user2
            ELSE user1
        END AS friend
        FROM friends
        WHERE user1 = '$username' OR user2 = '$username'
    ))
    AND posts.datetime > '$time_actu'
    ORDER BY posts.datetime ASC 
    LIMIT 4
";
$result_carousel = $conn->query($carousel_sql);

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECE In - Accueil</title>

    <script>
        let currentIndex = 0;

        function moveSlide() {
            const items = document.querySelectorAll('.carousel-item');
            const totalItems = items.length;

            currentIndex = (currentIndex + 1) % totalItems;

            const carouselInner = document.querySelector('.carousel-inner');
            carouselInner.style.transform = `translateX(-${currentIndex * 100}%)`;

            updateActiveClass(items);
        }

        function updateActiveClass(items) {
            items.forEach((item, index) => {
                if (index === currentIndex) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Change slide every 15 seconds
        setInterval(moveSlide, 5000);

        // Initial active class setting
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.carousel-item');
            updateActiveClass(items);
        });
    </script>

</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="logoPiscine.png" alt="ECE Paris Logo">
            <h1>Bienvenue sur ECE In</h1>
        </div>

        <div class="leftcolonne">
            <div class="navigation">
                <a href="index.php">Accueil</a><br><br><br>
                <a href="mon_reseau.php">Mon Réseau</a><br><br><br>
                <a href="notifications.php">Notifications</a><br><br><br>
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
                <h2>ECE In</h2>
                <div class="bloc-droit">
                    <div class="leftcolonne2">
                        <p>Bienvenue sur ECE In, le réseau social innovant créé par une équipe de quatre étudiants passionnés dans le cadre de leurs projet de web dynamique. ECE In est conçu pour être un espace où les membres de la communauté ECE peuvent se connecter, interagir et évoluer professionnellement.
                            <br><br>
                            Sur ECE In, vous avez la possibilité d'explorer et de postuler à des offres d'emploi adaptées à votre profil, de partager vos idées et expériences à travers des publications, et de construire un réseau solide en établissant des connexions avec d'autres membres. De plus, grâce à notre messagerie intégrée, vous pouvez facilement discuter et échanger avec vos amis et contacts professionnels en temps réel.
                            <br><br>
                            Rejoignez-nous sur ECE In et découvrez un environnement convivial et professionnel, propice à la croissance personnelle et à l'enrichissement de votre carrière.
                            <br><br>          
                        </p>
                        <div class="l">
                            <?php
                                $mon_post_sql = "SELECT * FROM `posts` WHERE username = '$username' ORDER BY created_at DESC LIMIT 1;";
                                $mon_post_result = $conn->query($mon_post_sql);
                                $mon_post = $mon_post_result->fetch_assoc();

                                if ($mon_post_result->num_rows == 0 ){
                                    echo "<p>Vous n'avez pas encore crée de post/event</p>";
                                }
                                else{
                                    $post_id = $mon_post['id'];
                                    echo "<div class='post'>";
                                    echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($mon_post['username']) . "'><img src='" . htmlspecialchars($user['profile_picture']) . "' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'>" . htmlspecialchars($mon_post['username']) . "</a></strong></p>";
                                    echo "<p><strong>" . htmlspecialchars($mon_post['feeling']) . "</strong></p>";
                                    echo "<p>" . htmlspecialchars($mon_post['content']) . "</p>";
                                    echo "<p>Lieu: " . htmlspecialchars($mon_post['location']) . "</p>";

                                    if ($mon_post['media_path']) {
                                        if (strpos($mon_post['media_path'], '.mp4') !== false || strpos($mon_post['media_path'], '.avi') !== false || strpos($mon_post['media_path'], '.mov') !== false) {
                                            echo "<p><video width='320' height='240' controls><source src='" . htmlspecialchars($mon_post['media_path']) . "' type='video/mp4'>Your browser does not support the video tag.</video></p>";
                                        } else {
                                            echo "<p><img src='" . htmlspecialchars($mon_post['media_path']) . "' alt='media' width='100px' style='max-width:100%'></p>";
                                        }
                                    }
                                    if (!empty($mon_post['datetime']) && $mon_post['datetime'] !== '0000-00-00 00:00:00') {
                                        echo "<p>Le: " . htmlspecialchars($mon_post['datetime']) . "</p>";
                                    }
                                    echo "<p>Créé le: " . htmlspecialchars($mon_post['created_at']) . "</p>";
                                    
                                }
                                
                            ?>
                        </div>
                        <div class="r">
                            <?php
                                $mon_post_sql = "   SELECT posts.*, users.profile_picture 
                                                    FROM posts 
                                                    INNER JOIN users ON posts.username = users.username 
                                                    WHERE posts.username IN (
                                                        SELECT CASE
                                                            WHEN user1 = '$username' THEN user2
                                                            ELSE user1
                                                        END AS friend
                                                        FROM friends
                                                        WHERE user1 = '$username' OR user2 = '$username'
                                                    )
                                                    ORDER BY posts.created_at DESC LIMIT 1
                                                ";
                                $mon_post_result = $conn->query($mon_post_sql);
                                $mon_post = $mon_post_result->fetch_assoc();

                                if ($mon_post_result->num_rows == 0 ){
                                    echo "<p>Vous n'avez pas encore crée de post/event</p>";
                                }
                                else{
                                    $post_id = $mon_post['id'];
                                    echo "<div class='post'>";
                                    echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($mon_post['username']) . "'><img src='" . htmlspecialchars($mon_post['profile_picture']) . "' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'>" . htmlspecialchars($mon_post['username']) . "</a></strong></p>";
                                    echo "<p><strong>" . htmlspecialchars($mon_post['feeling']) . "</strong></p>";
                                    echo "<p>" . htmlspecialchars($mon_post['content']) . "</p>";
                                    echo "<p>Lieu: " . htmlspecialchars($mon_post['location']) . "</p>";

                                    if ($mon_post['media_path']) {
                                        if (strpos($mon_post['media_path'], '.mp4') !== false || strpos($mon_post['media_path'], '.avi') !== false || strpos($mon_post['media_path'], '.mov') !== false) {
                                            echo "<p><video width='320' height='240' controls><source src='" . htmlspecialchars($mon_post['media_path']) . "' type='video/mp4'>Your browser does not support the video tag.</video></p>";
                                        } else {
                                            echo "<p><img src='" . htmlspecialchars($mon_post['media_path']) . "' alt='media' width='100px' style='max-width:100%'></p>";
                                        }
                                    }
                                    if (!empty($mon_post['datetime']) && $mon_post['datetime'] !== '0000-00-00 00:00:00') {
                                        echo "<p>Le: " . htmlspecialchars($mon_post['datetime']) . "</p>";
                                    }
                                    echo "<p>Créé le: " . htmlspecialchars($mon_post['created_at']) . "</p>";
                                    
                                }
                                
                            ?>
                        </div>
                    </div>  
                    <div class="rightcolonne2">       
                        <h3>Événement a venir</h3>
                        <div class="carousel">
                            <div class="carousel-inner">
                                <?php
                                    if ($result_carousel->num_rows > 0) {
                                        $active = true;
                                        while ($row = $result_carousel->fetch_assoc()) {

                                            $username = htmlspecialchars($row['username']);
                                            $media = htmlspecialchars($row['media_path']);
                                            $content = htmlspecialchars($row['content']);
                                            $datetime = htmlspecialchars($row['datetime']);
                                            $location = htmlspecialchars($row['location']);

                                            $date = new DateTime($datetime);
                                            $formatted_date = $date->format('d m Y');

                                            echo '<div class="carousel-item' . ($active ? ' active' : '') . '">';
                                            echo '<div class="post">';
                                            echo '<div class="post-header">';
                                            echo '<div class="post-content"><strong>' . $content . '</strong></div>';
                                            if (strpos($media, '.mp4') !== false || strpos($media, '.avi') !== false || strpos($media, '.mov') !== false) {
                                                echo "<p><video width='320' height='240' controls><source src='$media' type='video/mp4'>Your browser does not support the video tag.</video></p>";
                                            } else {
                                                echo '<img src="' . $media . '" alt="Profile Picture" class="profile-picture" width=500px height = 250px>';
                                            }
                                            echo '<span class="username"> Publié par: <a href="user_profile.php?username=' . $username . '"><img src="' . $row['profile_picture'] . '" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">' . $username . '</a></span><br>';
                                            echo '<span class="datetime"> A lieu le: ' . $formatted_date . ' </span>';
                                            echo '<div class="location">A lieu a: ' . $location . '</div>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</div>';

                                            $active = false;
                                        }
                                    } else {
                                        echo '<div class="carousel-item active">Aucun post disponible</div>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contact">
                    <h3>Contact</h3>
                    <p>Pour toute question ou information, vous pouvez nous contacter à l'adresse suivante :</p>
                    <p>Email : contact@ecein.com</p>
                    <p>Téléphone : +33 6 95 95 75 11</p>
                    <p>Adresse : 10 Rue de Sextius Michel, 75015 Paris, France</p>
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3708775973523!2d2.286017777763897!3d48.85113777133131!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e67151e3c16d05%3A0x1e3446766ada1337!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1717113604133!5m2!1sfr!2sfr" width="200" height="150" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
                
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
