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
                            var newComment = '<p><strong><a href="user_profile.php?username=' + commentData.username + '"><img src="' + commentData.profile_picture + '" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;"></a>' + commentData.username + ':</strong> ' + commentData.comment + '</p>';
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
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>ECE In: Social Media Professionnel de l’ECE Paris</h1>
            <img src="https://via.placeholder.com/150" alt="ECE Paris Logo">
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
            <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

            <h3>Votre Profil</h3>
            <div class="profile-overview">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Photo de profil" style="width: 100px; height: 100px; border-radius: 50%;">
                <h3><?php echo htmlspecialchars($user['username']); ?></h3>
                <p><?php echo htmlspecialchars($user['description'] ?? ''); ?></p>
                <?php if (!empty($user['overlay'])): ?>
                    <img src="<?php echo htmlspecialchars($user['overlay']); ?>" alt="Overlay" style="width: 100px; height: 100px;">
                <?php endif; ?>
                <a href="edit_profile.php"><button>Éditer le profil</button></a>
            </div>

            <button id="generate-cv-button">Générer le CV</button>

            <div id="cv-container" style="display: none;">
                <?php
                $cv_file = $user['cv_xml'];
                if (file_exists($cv_file)) {
                    $xml = simplexml_load_file($cv_file);
                    echo "<h3>CV de " . htmlspecialchars($user['username']) . "</h3>";
                    echo "<p><strong>Nom:</strong> " . $xml->InformationsPersonnelles->Nom . "</p>";
                    echo "<p><strong>Adresse:</strong> " . $xml->InformationsPersonnelles->Adresse . "</p>";
                    echo "<p><strong>Email:</strong> " . $xml->InformationsPersonnelles->Email . "</p>";
                    echo "<p><strong>Téléphone:</strong> " . $xml->InformationsPersonnelles->Téléphone . "</p>";

                    echo "<h4>Expérience</h4>";
                    foreach ($xml->Expérience->Emploi as $emploi) {
                        echo "<p><strong>Titre:</strong> " . $emploi->Titre . "</p>";
                        echo "<p><strong>Entreprise:</strong> " . $emploi->Entreprise . "</p>";
                        echo "<p><strong>Années:</strong> " . $emploi->Années . "</p>";
                        echo "<p><strong>Description:</strong> " . $emploi->Description . "</p>";
                    }

                    echo "<h4>Éducation</h4>";
                    echo "<p><strong>Diplôme:</strong> " . $xml->Éducation->Diplôme . "</p>";
                    echo "<p><strong>Établissement:</strong> " . $xml->Éducation->Établissement . "</p>";
                    echo "<p><strong>Années:</strong> " . $xml->Éducation->Années . "</p>";

                    echo "<h4>Compétences</h4>";
                    foreach ($xml->Compétences->Compétence as $compétence) {
                        echo "<p>" . $compétence . "</p>";
                    }
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
                    echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($row['username']) . "'><img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'></a>" . htmlspecialchars($row['username']) . "</strong></p>";
                    echo "<p><strong>" . htmlspecialchars($row['feeling']) . "</strong></p>";
                    echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                    if ($row['media_path']) {
                        echo "<p><img src='" . htmlspecialchars($row['media_path']) . "' alt='media' style='max-width:100%'></p>";
                    }
                    echo "<p>Lieu: " . htmlspecialchars($row['location']) . "</p>";
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

                    // Form to update visibility
                    echo "<form action='update_visibility.php' method='POST' class='visibility-form'>";
                    echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                    echo "<select name='visibility' class='visibility-select' data-post-id='" . $post_id . "'>";
                    echo "<option value='public'" . ($row['visibility'] == 'public' ? ' selected' : '') . ">Public</option>";
                    echo "<option value='all_friends'" . ($row['visibility'] == 'all_friends' ? ' selected' : '') . ">Tous les amis</option>";
                    echo "<option value='selected_friends'" . ($row['visibility'] == 'selected_friends' ? ' selected' : '') . ">Certains amis</option>";
                    echo "<option value='private'" . ($row['visibility'] == 'private' ? ' selected' : '') . ">Privé</option>";
                    echo "</select>";
                    echo "<button type='submit'>Modifier</button>";
                    echo "</form>";

                    echo "<p>Créé le: " . htmlspecialchars($row['created_at']) . "</p>";
                    echo "<p class='likes-count'>Likes: " . htmlspecialchars($row['likes']) . "</p>";
                    echo "<p><a href='delete_post.php?id=" . $row['id'] . "'>Supprimer</a></p>";

                    // Display comments
                    $comment_sql = "SELECT comments.*, users.profile_picture FROM comments 
                                    INNER JOIN users ON comments.username = users.username 
                                    WHERE comments.post_id='$post_id' ORDER BY comments.created_at ASC";
                    $comment_result = $conn->query($comment_sql);

                    echo "<div class='comments'>";
                    if ($comment_result->num_rows > 0) {
                        while ($comment_row = $comment_result->fetch_assoc()) {
                            echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($comment_row['username']) . "'><img src='" . htmlspecialchars($comment_row['profile_picture']) . "' alt='Profile Picture' style='width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;'></a>" . htmlspecialchars($comment_row['username']) . ":</strong> " . htmlspecialchars($comment_row['comment']) . "</p>";
                        }
                    }
                    echo "</div>";

                    echo "<form action='comment.php' method='POST' class='comment-form'>";
                    echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                    echo "<input type='text' name='comment' placeholder='Commenter' required>";
                    echo "<button type='submit' class='comment-button'><i class='far fa-comment'></i></button>";
                    echo "</form>";

                    echo "<button class='like-button' data-post-id='" . $post_id . "'><i class='far fa-heart'></i></button>";

                    echo "</div>";
                }
            } else {
                echo "<p>Aucune publication pour le moment.</p>";
            }

            $conn->close();
            ?>
        </div>

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

        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
