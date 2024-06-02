<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

// Fetch user info
$user_sql = "SELECT * FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

// Fetch friends for "Certains amis" option
$friends_sql = "
    SELECT CASE
        WHEN user1 = '$username' THEN user2
        ELSE user1
    END AS friend
    FROM friends
    WHERE user1 = '$username' OR user2 = '$username'
";
$friends_result = $conn->query($friends_sql);
$friends = [];
while ($row = $friends_result->fetch_assoc()) {
    $friends[] = $row['friend'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Profil - ECE In</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            function attachLikeAndCommentHandlers() {
                $('.like-button').off('click').on('click', function(event) {
                    event.preventDefault();
                    var postId = $(this).data('post-id');
                    var $button = $(this);
                    $.ajax({
                        url: 'like_post.php',
                        type: 'POST',
                        data: { post_id: postId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.liked) {
                                $button.find('i').removeClass('far').addClass('fas');
                            } else {
                                $button.find('i').removeClass('fas').addClass('far');
                            }
                            $button.closest('.post').find('.likes-count').text('Likes: ' + response.likes);
                        }
                    });
                });

                $('.comment-form').off('submit').on('submit', function(event) {
                    event.preventDefault();
                    var $form = $(this);
                    $.ajax({
                        url: 'comment.php',
                        type: 'POST',
                        data: $form.serialize(),
                        success: function(response) {
                            var commentData = JSON.parse(response);
                            var newComment = '<div class="commentaires"><a href="user_profile.php?username=' + commentData.username + '"><img src="' + commentData.profile_picture + '" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></a><p class="usercom">' + commentData.username + ':</p><p> ' + commentData.comment + '</p>';
                            $form.closest('.post').find('.comments').append(newComment);
                            $form.find('input[name="comment"]').val('');
                        }
                    });
                });

                $('.visibility-form').off('submit').on('submit', function(event) {
                    event.preventDefault();
                    var $form = $(this);
                    $.ajax({
                        url: 'update_visibility.php',
                        type: 'POST',
                        data: $form.serialize(),
                        success: function(response) {
                            if (response === 'success') {
                                alert('Visibility updated successfully');
                            } else {
                                alert('Error updating visibility');
                            }
                        }
                    });
                });

                $('.visibility-select').on('change', function() {
                    var visibility = $(this).val();
                    if (visibility === 'selected_friends') {
                        var postId = $(this).data('post-id');
                        $('#friends-modal').data('post-id', postId).show();
                    }
                });

                $('#save-friends').on('click', function() {
                    var postId = $('#friends-modal').data('post-id');
                    var selectedFriends = [];
                    $('#friends-list input:checked').each(function() {
                        selectedFriends.push($(this).val());
                    });
                    $.ajax({
                        url: 'update_selected_friends.php',
                        type: 'POST',
                        data: { post_id: postId, friends: selectedFriends },
                        success: function(response) {
                            alert('Selected friends updated successfully');
                            $('#friends-modal').hide();
                        }
                    });
                });

                $('#close-modal').on('click', function() {
                    $('#friends-modal').hide();
                });
            }

            attachLikeAndCommentHandlers();
            $('#generate-cv-button').on('click', function() {
                $('#cv-container').toggle();
            });
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <img src="logoPiscine.png" alt="ECE Paris Logo">
            <h1>ECE In: Social Media Professionnel de l’ECE Paris</h1>
        </div>

        <div class="leftcolonne">
            <div class="navigation">
                <a href="index.php">Accueil</a><br><br><br>
                <a href="mon_reseau.php">Mon Réseau</a><br><br><br>
                <a href="notifications.php">Notifications</a>
                <div class="notificationb"><?php

                    $nbr_notif_sql = "SELECT COUNT(*) FROM notifications WHERE receiver = '".$username."' and statut = 'pending';";
                    $reponse = $conn->query($nbr_notif_sql);
                    $resultat = $reponse->fetch_assoc();
                    $nbr_notif = $resultat['COUNT(*)'];
                    echo "<p>".$nbr_notif."</p>";
                
                ?>
                </div><br><br><br>
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
        <div id="friends-modal" style="display:none;">
                    <h3>Sélectionner les amis</h3>
                    <div id="friends-list">
                        <?php foreach ($friends as $friend): ?>
                            <label>
                                <input type="checkbox" value="<?php echo htmlspecialchars($friend); ?>"> <?php echo htmlspecialchars($friend); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </div>
                    <button id="save-friends">Enregistrer</button>
                    <button id="close-modal">Fermer</button>
                </div>
        <div class="rightcolonne">
            <div class="section">
                <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

                <h3>Votre Profil</h3>
                <div class="profile-overview">
                    <div class="profilcomp">
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" style="width: 100px; height: 100px; border-radius: 50%;">
                        <h3 class="nom-profilvue"><?php echo htmlspecialchars($user['username']); ?></h3>
                        <a href="edit_profile.php"><button class="editprofil"><img src="ProfilEdit.png" alt="profil-edit"></button></a>
                    </div>
                    <p><?php echo htmlspecialchars($user['description'] ?? ''); ?></p>
                </div>
                <style>
                    .profilcomp{
                        margin-bottom:20px;
                        padding:20px;
                        display:flex;
                        align-items:center;
                        width: 95%; /* Largeur du conteneur */
                        height: 100px; /* Hauteur du conteneur */
                        background-image: url('<?php echo htmlspecialchars($user['overlay']); ?>'); /* Chemin de l'image */
                        background-size: cover; /* Redimensionner l'image pour remplir complètement le conteneur */
                        background-position: center; /* Positionner l'image au centre */
                        background-repeat: no-repeat; /* Ne pas répéter l'image */
                        border-radius:5px;
                    }
                </style>
                <button id="generate-cv-button">Générer le CV</button>

                <div id="cv-container" style="display: none;">
                    <?php
                    $cv_file = $user['cv_xml'];
                    if (file_exists($cv_file)) {
                        $xml = simplexml_load_file($cv_file);
                        echo "<div class='CV-container'>";
                        echo "<div class='info-perso'>";
                        echo "<p><strong>Nom:</strong> " . $xml->InformationsPersonnelles->Nom . "</p>";
                        echo "<p><strong>Adresse:</strong> " . $xml->InformationsPersonnelles->Adresse . "</p>";
                        echo "<p><strong>Email:</strong> " . $xml->InformationsPersonnelles->Email . "</p>";
                        echo "<p><strong>Téléphone:</strong> " . $xml->InformationsPersonnelles->Téléphone . "</p>";
                        echo "</div><br>";
                        echo "<h3 class='titre-CV'>CV de " . htmlspecialchars($user['username']) . "</h3>";
                        echo "<h4>Expérience</h4>";
                        foreach ($xml->Expérience->Emploi as $emploi) {
                            echo "<p><strong> - Titre: </strong> " . $emploi->Titre . "</p>";
                            echo "<p class='espaceentre'><strong>Entreprise:</strong> " . $emploi->Entreprise . "</p>";
                            echo "<p class='espaceentre'><strong>Années:</strong> " . $emploi->Années . "</p>";
                            echo "<p id='dernierp' class='espaceentre'><strong>Description:</strong> " . $emploi->Description . "</p>";
                        }

                        echo "<h4>Éducation</h4>";
                        echo "<p><strong> - Diplôme: </strong> " . $xml->Éducation->Diplôme . "</p>";
                        echo "<p class='espaceentre'><strong>Établissement:</strong> " . $xml->Éducation->Établissement . "</p>";
                        echo "<p id='dernierp' class='espaceentre'><strong>Années:</strong> " . $xml->Éducation->Années . "</p>";

                        echo "<h4>Compétences</h4>";
                        foreach ($xml->Compétences->Compétence as $compétence) {
                            echo "<p> - " . $compétence . "</p>";
                        }
                        echo "</div>";
                    } else {
                        echo "<p>Aucun CV disponible.</p>";
                    }
                    ?>
                </div>

                <h3>Vos Publications</h3>
                <?php
                $sql = "SELECT posts.*, users.profile_picture FROM posts INNER JOIN users ON posts.username = users.username WHERE posts.username='$username' ORDER BY posts.created_at DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $post_id = $row['id'];
                        echo "<div class='post'>";
                        // Form to update visibility
                        echo "<form action='update_visibility.php' method='POST' class='visibility-form'>";
                        echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                        echo "<select name='visibility' class='visibility-select' data-post-id='" . $post_id . "'>";
                        echo "<option value='public'" . ($row['visibility'] == 'public' ? ' selected' : '') . ">Public</option>";
                        echo "<option value='all_friends'" . ($row['visibility'] == 'all_friends' ? ' selected' : '') . ">Tous les amis</option>";
                        echo "<option value='selected_friends'" . ($row['visibility'] == 'selected_friends' ? ' selected' : '') . ">Certains amis</option>";
                        echo "<option value='private'" . ($row['visibility'] == 'private' ? ' selected' : '') . ">Privé</option>";
                        echo "</select>";
                        echo "<button type='submit' class='modifvisi'>Modifier</button>";
                        echo "</form>";
                        echo "<div class='contenairpubli'><p>" . htmlspecialchars($row['username']) . "</p><a href='user_profile.php?username=" . htmlspecialchars($row['username']) . "'><img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture' style='width: 60px; height: 60px; border-radius: 50%; margin-left: 10px;'></a></div>";
                        echo "<p class='titre-post'><strong>" . htmlspecialchars($row['feeling']) . "</strong></p>";
                        echo "<p>" . htmlspecialchars($row['content']) . "</p>";

                        if ($row['media_path']) {
                            echo "<p><img src='" . htmlspecialchars($row['media_path']) . "' alt='media' style='max-width:100%'></p>";
                        }
                        echo "<div class='post-info'><p>Lieu: " . htmlspecialchars($row['location']) . "</p>";
                        echo "<p>Visibilité: ";
                        if ($row['visibility'] == 'public') {
                            echo "Public";
                        } elseif ($row['visibility'] == 'all_friends') {
                            echo "Tous les amis";
                        } elseif ($row['visibility'] == 'selected_friends') {
                            echo "Certains amis";
                        } elseif ($row['visibility'] == 'private') {
                            echo "Privé";
                        }
                        echo "</p>";
                        echo "<p>Créé le: " . htmlspecialchars($row['created_at']) . "</p></div>";
                        echo "<div class='supp'><a href='delete_post.php?id=" . $row['id'] . "'><img src='supprimer.png' alt='supp'></a></div>";

                        // Display comments
                        $comment_sql = "SELECT comments.*, users.profile_picture FROM comments 
                                        INNER JOIN users ON comments.username = users.username 
                                        WHERE comments.post_id='$post_id' ORDER BY comments.created_at ASC";
                        $comment_result = $conn->query($comment_sql);
                        echo "<form action='comment.php' method='POST' class='comment-form'>";
                        echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                        echo "<input type='text' name='comment' placeholder='Commenter' required>";
                        echo "<button type='submit' class='comment-button'><i class='far fa-comment'></i></button>";
                        echo "<button class='like-button' data-post-id='" . $post_id . "'><i class='far fa-heart'></i></button>";
                        echo "<p class='likes-count'>" . htmlspecialchars($row['likes']) . "</p>";
                        echo "</form>";
                        echo "<div class='comments'>";
                        if ($comment_result->num_rows > 0) {
                            while ($comment_row = $comment_result->fetch_assoc()) {
                                echo "<div class='commentaires'><a href='user_profile.php?username=" . htmlspecialchars($comment_row['username']) . "'><img src='" . htmlspecialchars($comment_row['profile_picture']) . "' alt='Profile Picture' style='width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;'></a><p class='usercom'>" . htmlspecialchars($comment_row['username']) . ":</p><p> " . htmlspecialchars($comment_row['comment']) . "</p></div>";
                            }
                        }
                        echo "</div>";

                        echo "</div>";
                    }
                } else {
                    echo "<p>Aucune publication pour le moment.</p>";
                }

                $conn->close();
                ?>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>