<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Gestion de l'upload de la photo de profil
    $uploadOk = 1;

    // Vérifiez si les images sont des images réelles ou des fausses images
    $check_profile_picture = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    $check_overlay = getimagesize($_FILES["overlay"]["tmp_name"]);
    if($check_profile_picture !== false && $check_overlay !== false) {
        $uploadOk = 1;
    } else {
        echo "Un ou les deux fichiers ne sont pas des images.";
        $uploadOk = 0;
    }

    // Vérifiez la taille des fichiers
    if ($_FILES["profile_picture"]["size"] > 500000 || $_FILES["overlay"]["size"] > 500000) {
        echo "Désolé, un ou les deux fichiers sont trop volumineux.";
        $uploadOk = 0;
    }

    // Limitez les formats de fichiers autorisés
    $profile_picture_type = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $overlay_type = strtolower(pathinfo($_FILES["overlay"]["name"], PATHINFO_EXTENSION));
    if(($profile_picture_type != "jpg" && $profile_picture_type != "png" && $profile_picture_type != "jpeg") 
    || ($overlay_type != "jpg" && $overlay_type != "png" && $overlay_type != "jpeg")) {
        echo "Désolé, seuls les fichiers JPG, JPEG et PNG sont autorisés.";
        $uploadOk = 0;
    }

    // Vérifiez si $uploadOk est défini à 0 par une erreur
    if ($uploadOk == 0) {
        echo "Désolé, vos fichiers n'ont pas été téléchargés.";
    } else {
        // Insérez les informations de l'utilisateur dans la base de données
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            $user_id = $conn->insert_id;
            $target_dir = "uploads/$user_id/";
            mkdir($target_dir, 0777, true);

            $profile_picture_file = $target_dir . "profile_picture." . $profile_picture_type;
            $overlay_file = $target_dir . "overlay." . $overlay_type;

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profile_picture_file) 
            && move_uploaded_file($_FILES["overlay"]["tmp_name"], $overlay_file)) {
                // Mettez à jour les informations de l'utilisateur avec les chemins des fichiers
                $sql_update = "UPDATE users SET profile_picture='$profile_picture_file', overlay='$overlay_file' WHERE id=$user_id";
                if ($conn->query($sql_update) === TRUE) {
                    header("Location: login.php");
                    exit();
                } else {
                    echo "Erreur: " . $sql_update . "<br>" . $conn->error;
                }
            } else {
                echo "Désolé, une erreur s'est produite lors du téléchargement de vos fichiers.";
            }
        } else {
            echo "Erreur: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - ECE In</title>
</head>
<body>
    <div class="container" id="registerForm">
        <div class="bloc1Login">
            <img src="logoPiscine.png" alt="logo" class="logoPiscineDebut">
            <p>Bienvenue sur ECE In, le réseau social innovant créé par une équipe de quatre étudiants passionnés dans le cadre de leurs projet de web dynamique. ECE In est conçu pour être un espace où les membres de la communauté ECE peuvent se connecter, interagir et évoluer professionnellement.</p>

            </p>Sur ECE In, vous avez la possibilité d'explorer et de postuler à des offres d'emploi adaptées à votre profil, de partager vos idées et expériences à travers des publications, et de construire un réseau solide en établissant des connexions avec d'autres membres. De plus, grâce à notre messagerie intégrée, vous pouvez facilement discuter et échanger avec vos amis et contacts professionnels en temps réel.

            Rejoignez-nous sur ECE In et découvrez un environnement convivial et professionnel, propice à la croissance personnelle et à l'enrichissement de votre carrière.</p>
        </div>
        <div class="bloc2Login">
            <form class="form" action="register.php" method="POST" enctype="multipart/form-data">
                <h2>Inscription</h2>
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <label for="profile_picture">Photo de profil:</label>
                <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required><br>
                <label for="overlay">Overlay:</label><br>
                <input type="file" id="overlay" name="overlay" accept="image/*" required>
                <button type="submit">S'inscrire</button>
                <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
            </form>
        </div>
    </div>
</body>
</html>
