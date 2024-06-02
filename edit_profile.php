<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

$username = $_SESSION['username'];

// Récupérer les informations de l'utilisateur
$user_sql = "SELECT * FROM users WHERE username='$username'";
$user_result = $conn->query($user_sql);
$user = $user_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = !empty($_POST['email']) ? $conn->real_escape_string($_POST['email']) : $user['email'];
    $new_description = !empty($_POST['description']) ? $conn->real_escape_string($_POST['description']) : $user['description'];

    // Update profile picture if a new one is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "uploads/{$user['id']}/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
        $new_profile_picture = $target_file;
    } else {
        $new_profile_picture = $user['profile_picture'];
    }

    // Update overlay if a new one is uploaded
    if (isset($_FILES['overlay']) && $_FILES['overlay']['error'] == 0) {
        $target_dir = "uploads/{$user['id']}/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["overlay"]["name"]);
        move_uploaded_file($_FILES["overlay"]["tmp_name"], $target_file);
        $new_overlay = $target_file;
    } else {
        $new_overlay = $user['overlay'];
    }

    // Handle XML CV upload
    if (isset($_FILES['xml_cv']) && $_FILES['xml_cv']['error'] == 0) {
        $target_dir = "uploads/{$user['id']}/xml/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["xml_cv"]["name"]);
        move_uploaded_file($_FILES["xml_cv"]["tmp_name"], $target_file);
        $new_cv_xml = $target_file;
        $xml = simplexml_load_file($target_file);
        $cv_data = [
            'name' => (string) $xml->InformationsPersonnelles->Nom,
            'address' => (string) $xml->InformationsPersonnelles->Adresse,
            'email' => (string) $xml->InformationsPersonnelles->Email,
            'phone' => (string) $xml->InformationsPersonnelles->Téléphone,
            'experience' => [],
            'education' => [
                'diploma' => (string) $xml->Éducation->Diplôme,
                'institution' => (string) $xml->Éducation->Établissement,
                'years' => (string) $xml->Éducation->Années,
            ],
            'skills' => []
        ];

        foreach ($xml->Expérience->Emploi as $emploi) {
            $cv_data['experience'][] = [
                'title' => (string) $emploi->Titre,
                'company' => (string) $emploi->Entreprise,
                'years' => (string) $emploi->Années,
                'description' => (string) $emploi->Description,
            ];
        }

        foreach ($xml->Compétences->Compétence as $compétence) {
            $cv_data['skills'][] = (string) $compétence;
        }
    } else {
        $new_cv_xml = $user['cv_xml'];
    }

    // Handle password change
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update_sql = "UPDATE users SET password='$hashed_new_password' WHERE username='$username'";
                $conn->query($password_update_sql);
            } else {
                echo "Les nouveaux mots de passe ne correspondent pas.";
                exit();
            }
        } else {
            echo "Le mot de passe actuel est incorrect.";
            exit();
        }
    }

    $sql = "UPDATE users SET email='$new_email', description='$new_description', profile_picture='$new_profile_picture', overlay='$new_overlay', cv_xml='$new_cv_xml' WHERE username='$username'";

    if ($conn->query($sql) === TRUE) {
        header("Location: profile.php");
        exit();
    } else {
        echo "Erreur: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer Profil - ECE In</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Éditer Profil</h1>
        </div>

        <div class="leftcolonne">
            <div class="navigation">
                <a href="index.php">Accueil</a><br><br><br>
                <a href="mon_reseau.php">Mon Réseau</a><br><br><br>
                <div class="notificationb"><?php

                    $nbr_notif_sql = "SELECT COUNT(*) FROM notifications WHERE receiver = '".$username."' and statut = 'pending';";
                    $reponse = $conn->query($nbr_notif_sql);
                    $resultat = $reponse->fetch_assoc();
                    $nbr_notif = $resultat['COUNT(*)'];
                    echo "<p>".$nbr_notif."</p>";
                
                ?>
                </div>
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
            <div class="section" id="editprofil1">
                <h2>Éditer votre profil</h2>
                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <div class="blocprincipaledit">
                        <div class="blocedit2">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="blocedit2">
                            <label for="profile_picture">Photo de profil</label>
                            <input type="file" id="profile_picture" name="profile_picture">

                            <label for="overlay">Overlay</label>
                            <input type="file" id="overlay" name="overlay">
                        
                            <label for="xml_cv">Importer votre CV (XML)</label>
                            <input type="file" id="xml_cv" name="xml_cv">

                        </div>
                    </div>
                    <h3>Changer de mot de passe</h3>
                    <label for="current_password">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password">

                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password">

                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password">

                    <button type="submit">Enregistrer</button>
                    <a href="profile.php"><button type="button">Annuler</button></a>
                </form>

                <?php if (isset($cv_data)): ?>
                    <h2>Votre CV</h2>
                    <p><strong>Nom:</strong> <?php echo htmlspecialchars($cv_data['name']); ?></p>
                    <p><strong>Adresse:</strong> <?php echo htmlspecialchars($cv_data['address']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($cv_data['email']); ?></p>
                    <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($cv_data['phone']); ?></p>

                    <h3>Expérience</h3>
                    <?php foreach ($cv_data['experience'] as $experience): ?>
                        <p><strong>Titre:</strong> <?php echo htmlspecialchars($experience['title']); ?></p>
                        <p><strong>Entreprise:</strong> <?php echo htmlspecialchars($experience['company']); ?></p>
                        <p><strong>Années:</strong> <?php echo htmlspecialchars($experience['years']); ?></p>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($experience['description']); ?></p>
                    <?php endforeach; ?>

                    <h3>Éducation</h3>
                    <p><strong>Diplôme:</strong> <?php echo htmlspecialchars($cv_data['education']['diploma']); ?></p>
                    <p><strong>Établissement:</strong> <?php echo htmlspecialchars($cv_data['education']['institution']); ?></p>
                    <p><strong>Années:</strong> <?php echo htmlspecialchars($cv_data['education']['years']); ?></p>

                    <h3>Compétences</h3>
                    <ul>
                        <?php foreach ($cv_data['skills'] as $skill): ?>
                            <li><?php echo htmlspecialchars($skill); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer">
            <p>&copy; 2024 ECE In. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>
