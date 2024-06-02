<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

// Vérifier si l'utilisateur est un administrateur
$user_sql = "SELECT is_admin FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

if (!$user['is_admin']) {
    header("Location: index.php");
    exit();
}

// Ajouter un nouvel utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $new_username = $_POST['new_username'];
    $new_mail = $_POST['new_mail'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $new_is_admin = isset($_POST['new_is_admin']) ? 1 : 0;
    $new_is_partner = isset($_POST['new_is_partner']) ? 1 : 0;

    $add_user_sql = "INSERT INTO users (username, email, password, is_admin, partenaire_ece) VALUES ('$new_username', '$new_mail', '$new_password', '$new_is_admin', '$new_is_partner')";
    if ($conn->query($add_user_sql) === TRUE) {
        echo "Nouvel utilisateur ajouté avec succès!";
    } else {
        echo "Erreur: " . $conn->error;
    }
}

// Supprimer un utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $delete_username = $_POST['delete_username'];
    $delete_user_sql = "DELETE FROM users WHERE username='$delete_username'";
    if ($conn->query($delete_user_sql) === TRUE) {
        echo "Utilisateur supprimé avec succès!";
    } else {
        echo "Erreur: " . $conn->error;
    }
}

// Récupérer tous les utilisateurs
$users_sql = "SELECT * FROM users";
$users_result = $conn->query($users_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - ECE In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Tableau de bord Admin</h1>
        </div>

        <div class="deconnexion">
            <a href="logout.php">Déconnexion</a>
        </div>

        <div class="section1">
            <div class="blocprincipal">
                <div class="blocadmin1">
                    <h2>Ajouter un utilisateur</h2>
                    <form method="POST" action="admin_dashboard.php">
                        <label for="new_username">Nom d'utilisateur</label>
                        <input type="text" id="new_username" name="new_username" required>

                        <label for="new_mail">Adresse mail</label>
                        <input type="text" id="new_mail" name="new_mail" required>

                        <label for="new_password">Mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required>

                        <label for="new_is_admin">Administrateur</label>
                        <input type="checkbox" id="new_is_admin" name="new_is_admin">

                        <label for="new_is_partner">Partenaire ECE</label>
                        <input type="checkbox" id="new_is_partner" name="new_is_partner">

                        <button type="submit" name="add_user">Ajouter</button>
                    </form>
                </div>
                <div class="blocadmin2">
                    <h2>Liste des utilisateurs</h2>
                    <table>
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Administrateur</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        if ($users_result->num_rows > 0) {
                            while ($user = $users_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                                echo "<td>" . ($user['is_admin'] ? 'Oui' : 'Non') . "</td>";
                                echo "<td>
                                        <form method='POST' action='admin_dashboard.php' style='display:inline-block;'>
                                            <input type='hidden' name='delete_username' value='" . htmlspecialchars($user['username']) . "'>
                                            <button type='submit' name='delete_user'>Supprimer</button>
                                        </form>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>Aucun utilisateur trouvé.</td></tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
