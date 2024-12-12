<?php
include("config.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);


    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo = file_get_contents($_FILES['photo']['tmp_name']);
    } else {
        $photo = null; 
    }

    
    $sql = "INSERT INTO User (photo, username, prenom, nom, email, password, created_at, updated_at) 
            VALUES (:photo, :username, :prenom, :nom, :email, :password, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'photo' => $photo,
        'username' => $username,
        'prenom' => $prenom,
        'nom' => $nom,
        'email' => $email,
        'password' => $password,
    ]);

    if ($result) {
        $message = 'Inscription réussie!';
        header('Location: login.php');
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
    <title>Inscription</title>
    <link rel="stylesheet" href="css/register.css">
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
</div>
</body>
</html>
