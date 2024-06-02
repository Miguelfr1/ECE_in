<?php
session_start();

include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            if ($row['is_admin']) {
                header("Location: admin_dashboard.php"); // Rediriger vers une page spécifique pour l'admin
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "Mot de passe incorrect.";
        }
    } else {
        echo "Nom d'utilisateur incorrect.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - ECE In</title>
</head>
<body>
    <div id="loginForm"class="container">
        <div class="bloc1Login">
            <img src="logoPiscine.png" alt="logo" class="logoPiscineDebut">
            <p>Bienvenue sur ECE In, le réseau social innovant créé par une équipe de quatre étudiants passionnés dans le cadre de leurs projet de web dynamique. ECE In est conçu pour être un espace où les membres de la communauté ECE peuvent se connecter, interagir et évoluer professionnellement.</p>

            </p>Sur ECE In, vous avez la possibilité d'explorer et de postuler à des offres d'emploi adaptées à votre profil, de partager vos idées et expériences à travers des publications, et de construire un réseau solide en établissant des connexions avec d'autres membres. De plus, grâce à notre messagerie intégrée, vous pouvez facilement discuter et échanger avec vos amis et contacts professionnels en temps réel.

            Rejoignez-nous sur ECE In et découvrez un environnement convivial et professionnel, propice à la croissance personnelle et à l'enrichissement de votre carrière.</p>
        </div>
        <div class="bloc2Login">
            <form class="form" action="login.php" method="POST">
                <h2>Connexion</h2>
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit">Se connecter</button>
                <p>Pas encore inscrit ? <a href="register.php">Inscrivez-vous ici</a></p>
            </form>
        </div>
    </div>
</body>
</html>
