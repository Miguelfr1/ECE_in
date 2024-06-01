<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$current_user = $_SESSION['username'];

// Requête pour obtenir les posts de l'utilisateur connecté et de ses amis
$sql = "
    SELECT posts.*, users.profile_picture 
    FROM posts 
    INNER JOIN users ON posts.username = users.username 
    LEFT JOIN post_visibility ON posts.id = post_visibility.post_id
    WHERE 
    (posts.visibility = 'public') OR 
    (posts.visibility = 'private' AND posts.username = '$current_user') OR 
    (posts.visibility = 'all_friends' AND posts.username IN (
        SELECT CASE
            WHEN user1 = '$current_user' THEN user2
            ELSE user1
        END AS friend
        FROM friends
        WHERE user1 = '$current_user' OR user2 = '$current_user'
    )) OR 
    (posts.visibility = 'selected_friends' AND post_visibility.friend_username = '$current_user') OR 
    (posts.username = '$current_user')
    GROUP BY posts.id
    ORDER BY posts.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fil d'actualité - ECE In</title>
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

                $('#visibility').on('change', function() {
                    if ($(this).val() == 'selected_friends') {
                        $('#friends-selection').show();
                    } else {
                        $('#friends-selection').hide();
                    }
                });
            }

            $('#post-form').on('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'publish_post.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var postData = JSON.parse(response);
                        if (postData.status === 'success') {
                            var post = postData.post;
                            var newPost = `
                                <div class='post'>
                                    <p><strong><a href='user_profile.php?username=${post.username}'><img src='${post.profile_picture ?? 'default.jpg'}' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'></a>${post.username}</strong></p>
                                    <p><strong>${post.feeling}</strong></p>
                                    <p>${post.content}</p>
                                    ${post.media_path ? (post.media_path.match(/\.(mp4|avi|mov)$/i) ? `<p><video width='320' height='240' controls><source src='${post.media_path}' type='video/mp4'>Your browser does not support the video tag.</video></p>` : `<p><img src='${post.media_path}' alt='media' style='max-width:100%'></p>`) : ''}
                                    <p>Lieu: ${post.location}</p>
                                    ${post.datetime !== '0000-00-00 00:00:00' ? `<p>Le: ${post.datetime}</p>` : ''}
                                    <p>Créé le: ${post.created_at}</p>
                                    <p class='likes-count'>Likes: 0</p>
                                    <form action='comment.php' method='POST' class='comment-form'>
                                        <input type='hidden' name='post_id' value='${post.id}'>
                                        <input type='text' name='comment' placeholder='Commenter' required>
                                        <button type='submit' class='comment-button'><i class='far fa-comment'></i></button>
                                    </form>
                                    <button class='like-button' data-post-id='${post.id}'><i class='far fa-heart'></i></button>
                                    <div class='comments'></div>
                                    <form action='send_job_offer.php' method='POST' class='job-offer-form'>
                                        <input type='hidden' name='post_id' value='${post.id}'>
                                        <button type='submit'>Envoyer une offre d'emploi</button>
                                    </form>
                                </div>
                            `;
                            $('#posts-section').prepend(newPost);
                            $('#post-form')[0].reset();
                            $('#friends-selection').hide();
                            attachLikeAndCommentHandlers();
                        } else {
                            alert('Erreur lors de la publication du post: ' + postData.message);
                        }
                    }
                });
            });

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

            <h3>Publier un événement</h3>
            <form id="post-form" enctype="multipart/form-data">
                <input type="text" name="feeling" placeholder="Titre">
                <textarea name="content" placeholder="Quoi de neuf?" required></textarea>
                <input type="text" name="location" placeholder="Lieu">
                <input type="file" name="media">
                <label for="datetime">Date et heure (optionnel):</label>
                <input type="datetime-local" name="datetime">
                <label for="visibility">Visibilité:</label>
                <select name="visibility" id="visibility">
                    <option value="public">Public</option>
                    <option value="all_friends">Tous les amis</option>
                    <option value="selected_friends">Certains amis</option>
                    <option value="private">Privé</option>
                </select>
                <div id="friends-selection" style="display: none;">
                    <label for="friends">Choisissez des amis:</label>
                    <select name="friends[]" id="friends" multiple>
                        <?php
                        $friends_sql = "
                            SELECT CASE 
                                WHEN user1 = '$current_user' THEN user2
                                ELSE user1
                            END AS friend
                            FROM friends
                            WHERE user1 = '$current_user' OR user2 = '$current_user'
                        ";
                        $friends_result = $conn->query($friends_sql);
                        while ($friend_row = $friends_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($friend_row['friend']) . "'>" . htmlspecialchars($friend_row['friend']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit">Publier</button>
            </form>

            <h3>Mur des publications</h3>
            <div id="posts-section">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $post_id = $row['id'];
                    echo "<div class='post'>";
                    echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($row['username']) . "'><img src='" . htmlspecialchars($row['profile_picture'] ?? 'default.jpg') . "' alt='Profile Picture' style='width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;'></a>" . htmlspecialchars($row['username']) . "</strong></p>";
                    echo "<p><strong>" . htmlspecialchars($row['feeling']) . "</strong></p>";
                    echo "<p>" . htmlspecialchars($row['content']) . "</p>";
                    echo "<p>Lieu: " . htmlspecialchars($row['location']) . "</p>";

                    if ($row['media_path']) {
                        if (strpos($row['media_path'], '.mp4') !== false || strpos($row['media_path'], '.avi') !== false || strpos($row['media_path'], '.mov') !== false) {
                            echo "<p><video width='320' height='240' controls><source src='" . htmlspecialchars($row['media_path']) . "' type='video/mp4'>Your browser does not support the video tag.</video></p>";
                        } else {
                            echo "<p><img src='" . htmlspecialchars($row['media_path']) . "' alt='media' style='max-width:100%'></p>";
                        }
                    }
                    if ($row['datetime'] !== '0000-00-00 00:00:00') {
                        echo "<p>Le: " . htmlspecialchars($row['datetime']) . "</p>";
                    }
                    echo "<p>Créé le: " . htmlspecialchars($row['created_at']) . "</p>";
                    echo "<p class='likes-count'>Likes: " . htmlspecialchars($row['likes']) . "</p>";

                    echo "<form action='comment.php' method='POST' class='comment-form'>";
                    echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                    echo "<input type='text' name='comment' placeholder='Commenter' required>";
                    echo "<button type='submit' class='comment-button'><i class='far fa-comment'></i></button>";
                    echo "</form>";

                    echo "<button class='like-button' data-post-id='" . $post_id . "'><i class='far fa-heart'></i></button>";

                    $comment_sql = "SELECT comments.*, users.profile_picture FROM comments 
                                    INNER JOIN users ON comments.username = users.username 
                                    WHERE comments.post_id='$post_id' ORDER BY comments.created_at ASC";
                    $comment_result = $conn->query($comment_sql);

                    echo "<div class='comments'>";
                    if ($comment_result->num_rows > 0) {
                        while ($comment_row = $comment_result->fetch_assoc()) {
                            echo "<p><strong><a href='user_profile.php?username=" . htmlspecialchars($comment_row['username']) . "'><img src='" . htmlspecialchars($comment_row['profile_picture'] ?? 'default.jpg') . "' alt='Profile Picture' style='width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;'></a>" . htmlspecialchars($comment_row['username']) . ":</strong> " . htmlspecialchars($comment_row['comment']) . "</p>";
                        }
                    }
                    echo "</div>";

                    echo "<form action='send_job_offer.php' method='POST' class='job-offer-form'>";
                    echo "<input type='hidden' name='post_id' value='" . $post_id . "'>";
                    echo "<button type='submit'>Envoyer une offre d'emploi</button>";
                    echo "</form>";
                    echo "</div>";
                }
            } else {
                echo "<p>Aucune publication pour le moment.</p>";
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
