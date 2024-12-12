<?php
include("config.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = $_POST['login'];  
    $password = $_POST['password'];


    $sql = "SELECT * FROM User WHERE email = :login OR username = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['login' => $login]);

    $user = $stmt->fetch();

    if ($user) {
        
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['prenom'] = $user['prenom'];

            header('Location: home.php');
            exit;
        } else {
            $message = 'Mot de passe incorrect.';
        }
    } else {
        $message = 'Utilisateur non trouvé.';
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
