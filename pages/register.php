<?php
include("config.php");

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $defaultImagePath = "../img/user.png";

    // Vérification et récupération de l'image
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        // L'utilisateur a uploadé une image
        $photoContent = file_get_contents($_FILES['photo']['tmp_name']);
        $format = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    } else {
        // Utiliser l'image par défaut
        $photoContent = file_get_contents($defaultImagePath);
        $format = pathinfo($defaultImagePath, PATHINFO_EXTENSION);
    }

    // Insertion dans la base de données
    $sql = "INSERT INTO User (photo, username, prenom, nom, email, password, created_at, updated_at) 
            VALUES (:photo, :username, :prenom, :nom, :email, :password, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'photo' => $photoContent,
        'username' => $username,
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'password' => $password,
    ]);

    if ($result) {
        // Récupérer l'ID de l'utilisateur inséré
        $userId = $pdo->lastInsertId(); // Récupère l'ID de l'utilisateur inséré

        // Créer la session pour l'utilisateur
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;  // Tu peux aussi stocker d'autres informations

        $message = 'Inscription réussie!';
        header('Location: home.php');  // Rediriger vers la page d'accueil
        exit;
    } else {
        $message = 'Erreur lors de l\'inscription.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription- MerguezShop</title>
    <link rel="stylesheet" href="css/register.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
</head>
<body>
<div class="register-container">
    <h2>Inscription</h2>

    <?php if (!empty($message)): ?>
        <p style="color:red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="register.php" method="post" enctype="multipart/form-data">
        <div>
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="prenom">Prénom:</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>
        <div>
            <label for="nom">Nom:</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div>
            <label for="email">Adresse e-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <label for="photo">Photo de profil:</label>
            <input type="file" id="photo" name="photo" accept="image/*">
        </div>
        <div>
            <input type="submit" value="S'inscrire">
        </div>
    </form>
    <p>Vous avez déjà un compte ? <a href="login.php">Connexion</a></p>
</div>
</body>
</html>
