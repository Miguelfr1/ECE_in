<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$current_user = $_SESSION['username'];

// Get user's friends
$sql = "SELECT u1.username AS user1, u1.profile_picture AS profile_picture1, 
        u2.username AS user2, u2.profile_picture AS profile_picture2 
        FROM friends 
        JOIN users u1 ON friends.user1 = u1.username 
        JOIN users u2 ON friends.user2 = u2.username 
        WHERE user1='$current_user' OR user2='$current_user'";
$result = $conn->query($sql);
$friends = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if ($row['user1'] == $current_user) {
            $friends[] = array('username' => $row['user2'], 'profile_picture' => $row['profile_picture2']);
        } else {
            $friends[] = array('username' => $row['user1'], 'profile_picture' => $row['profile_picture1']);
        }
    }
}

// Get user's groups
$sql = "SELECT group_chats.*, group_members.username AS member_username
        FROM group_chats
        JOIN group_members ON group_chats.id = group_members.group_id
        WHERE group_members.username = '$current_user'";
$group_result = $conn->query($sql);
$groups = array();

if ($group_result->num_rows > 0) {
    while ($row = $group_result->fetch_assoc()) {
        $groups[] = array('id' => $row['id'], 'group_name' => $row['group_name']);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        #createGroupForm {
            display: none;
        }
    </style>
    
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Messagerie</h1>
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
            <h2>Vos Conversations</h2>
            <ul>
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <a href="messagerie.php?user=<?php echo htmlspecialchars($friend['username']); ?>" class="user-link">
                            <img src="<?php echo htmlspecialchars($friend['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 10px;">
                            <?php echo htmlspecialchars($friend['username']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h2>Vos Groupes</h2>
            <button id="createGroupButton">Créer un groupe</button>
            <form id="createGroupForm" action="create_group.php" method="POST">
                <label for="group_name">Nom du groupe:</label>
                <input type="text" id="group_name" name="group_name" required>
                <label for="members">Ajouter des membres:</label>
                <select id="members" name="members[]" multiple required>
                    <?php foreach ($friends as $friend): ?>
                        <option value="<?php echo htmlspecialchars($friend['username']); ?>"><?php echo htmlspecialchars($friend['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Créer</button>
            </form>
            <ul>
                <?php foreach ($groups as $group): ?>
                    <li>
                        <a href="messagerie.php?group=<?php echo htmlspecialchars($group['id']); ?>" class="group-link">
                            <?php echo htmlspecialchars($group['group_name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div id="conversation-section">
                <?php if (isset($_GET['user'])): ?>
                    <?php
                    $otherUser = $_GET['user'];
                    if (!in_array($otherUser, array_column($friends, 'username'))) {
                        die("Vous ne pouvez afficher la conversation qu'avec vos amis.");
                    }
                    $sql = "SELECT messages.*, users.profile_picture 
                            FROM messages 
                            JOIN users ON messages.sender = users.username 
                            WHERE (sender='$current_user' AND recipient='$otherUser') OR (sender='$otherUser' AND recipient='$current_user') 
                            ORDER BY timestamp ASC";
                    $result = $conn->query($sql);
                    ?>

                    <h2>Conversation avec <?php echo htmlspecialchars($otherUser); ?></h2>
                    <div class="conversation" id="conversation">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="message">
                                    <p>
                                        <strong>
                                            <a href="user_profile.php?username=<?php echo htmlspecialchars($row['sender']); ?>">
                                                <img src="<?php echo htmlspecialchars($row['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">
                                            </a>
                                            <?php echo htmlspecialchars($row['sender']); ?>
                                        </strong> (<?php echo $row['timestamp']; ?>): 
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Aucun message avec <?php echo htmlspecialchars($otherUser); ?>.</p>
                        <?php endif; ?>
                    </div>

                    <form id="messageForm" class="message-form">
                        <input type="hidden" name="recipient" value="<?php echo htmlspecialchars($otherUser); ?>">
                        <textarea name="message" placeholder="Entrez votre message" required></textarea>
                        <button type="submit">Envoyer</button>
                    </form>

                    <button id="closeConversation">Fermer la conversation</button>
                <?php endif; ?>

                <?php if (isset($_GET['group'])): ?>
                    <?php
                    $group_id = $_GET['group'];
                    $sql = "SELECT * FROM group_chats WHERE id='$group_id'";
                    $group_info_result = $conn->query($sql);
                    $group_info = $group_info_result->fetch_assoc();

                    $sql = "SELECT group_members.username, users.profile_picture 
                            FROM group_members 
                            JOIN users ON group_members.username = users.username 
                            WHERE group_id='$group_id'";
                    $members_result = $conn->query($sql);

                    $sql = "SELECT group_messages.*, users.profile_picture 
                            FROM group_messages 
                            JOIN users ON group_messages.sender = users.username 
                            WHERE group_messages.group_id='$group_id' 
                            ORDER BY timestamp ASC";
                    $result = $conn->query($sql);
                    ?>

                    <h2>Conversation du groupe: <?php echo htmlspecialchars($group_info['group_name']); ?></h2>
                    <div class="group-members">
                        <h3>Membres du groupe:</h3>
                        <ul>
                            <?php while ($member = $members_result->fetch_assoc()): ?>
                                <li>
                                    <img src="<?php echo htmlspecialchars($member['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">
                                    <?php echo htmlspecialchars($member['username']); ?>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <div class="conversation" id="group-conversation">
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="message">
                                    <p>
                                        <strong>
                                            <a href="user_profile.php?username=<?php echo htmlspecialchars($row['sender']); ?>">
                                                <img src="<?php echo htmlspecialchars($row['profile_picture'] ?? 'default.jpg'); ?>" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">
                                            </a>
                                            <?php echo htmlspecialchars($row['sender']); ?>
                                        </strong> (<?php echo $row['timestamp']; ?>): 
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Aucun message dans ce groupe.</p>
                        <?php endif; ?>
                    </div>

                    <form id="groupMessageForm" class="message-form">
                        <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group_id); ?>">
                        <textarea name="message" placeholder="Entrez votre message" required></textarea>
                        <button type="submit">Envoyer</button>
                    </form>

                    <button id="closeGroupConversation">Fermer la conversation</button>
                <?php endif; ?>
            </div>

            <script>
                function loadMessages() {
                    if (window.location.search.includes('user=')) {
                        var user = new URLSearchParams(window.location.search).get('user');
                        $.ajax({
                            url: 'check_messages.php',
                            type: 'GET',
                            data: { user: user },
                            success: function(response) {
                                $('#conversation').html(response);
                            }
                        });
                    } else if (window.location.search.includes('group=')) {
                        var group = new URLSearchParams(window.location.search).get('group');
                        $.ajax({
                            url: 'check_group_messages.php',
                            type: 'GET',
                            data: { group: group },
                            success: function(response) {
                                $('#group-conversation').html(response);
                            }
                        });
                    }
                }

                $(document).ready(function() {
                    setInterval(loadMessages, 1000);

                    $('#messageForm').on('submit', function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'send_message.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $(this).serialize(),
                            success: function(response) {
                                if (response.error) {
                                    console.error(response.error);
                                } else {
                                    $('#conversation').append(
                                        '<div class="message">' +
                                            '<p>' +
                                                '<strong>' +
                                                    '<a href="user_profile.php?username=' + response.sender + '">' +
                                                        '<img src="' + response.profile_picture + '" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">' +
                                                    '</a>' +
                                                    response.sender +
                                                '</strong> (' + response.timestamp + '): ' +
                                                response.message +
                                            '</p>' +
                                        '</div>'
                                    );
                                    $('textarea[name="message"]').val('');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    });

                    $('#groupMessageForm').on('submit', function(event) {
                        event.preventDefault();
                        $.ajax({
                            url: 'send_group_message.php',
                            type: 'POST',
                            dataType: 'json',
                            data: $(this).serialize(),
                            success: function(response) {
                                if (response.status === 'error') {
                                    console.error(response.message);
                                } else {
                                    $('#group-conversation').append(
                                        '<div class="message">' +
                                            '<p>' +
                                                '<strong>' +
                                                    '<a href="user_profile.php?username=' + response.sender + '">' +
                                                        '<img src="' + response.profile_picture + '" alt="Profile Picture" style="width: 20px; height: 20px; border-radius: 50%; margin-right: 5px;">' +
                                                    '</a>' +
                                                    response.sender +
                                                '</strong> (' + response.timestamp + '): ' +
                                                response.message +
                                            '</p>' +
                                        '</div>'
                                    );
                                    $('textarea[name="message"]').val('');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    });

                    $('#closeConversation').on('click', function() {
                        window.location.href = 'messagerie.php';
                    });

                    $('#closeGroupConversation').on('click', function() {
                        window.location.href = 'messagerie.php';
                    });

                    $('#createGroupButton').on('click', function() {
                        $('#createGroupForm').toggle();
                    });
                });
            </script>
        </div>

        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
