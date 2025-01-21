<?php
include("config.php");

session_start();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login']);  // Nettoyage des entrées utilisateur
    $password = $_POST['password'];

    if (!empty($login) && !empty($password)) {
        // Rechercher l'utilisateur via l'email ou le nom d'utilisateur
        $sql = "SELECT * FROM User WHERE email = :login OR username = :login";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['login' => $login]);
        $user = $stmt->fetch();

        if ($user) {
            // Vérification du mot de passe
            if (password_verify($password, $user['password'])) {
                // Initialiser la session utilisateur
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // Rediriger vers la page d'accueil après connexion réussie
                header('Location: home.php');
                exit;
            } else {
                $message = 'Mot de passe incorrect. Veuillez réessayer.';
            }
        } else {
            $message = 'Utilisateur non trouvé. Vérifiez vos informations de connexion.';
        }
    } else {
        $message = 'Veuillez remplir tous les champs.';
    }
}

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="login-container">
    <h2>Connexion</h2>

    <?php if (!empty($message)): ?>
        <p style="color:red"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="login.php" method="post">
        <div>
            <label for="login">Nom d'utilisateur ou e-mail:</label>
            <input type="text" id="login" name="login" required>
        </div>
        <div>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="Se connecter">
        </div>
    </form>
    <p>Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
</div>
</body>
</html>
