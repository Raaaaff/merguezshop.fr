<?php
// Inclure la connexion à la base de données
include('config.php');

// Démarrer la session
session_start();

// Récupérer tous les utilisateurs
$query = $pdo->query("SELECT id, username, photo FROM User");
$users = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - MerguezShop</title>
    <link rel="stylesheet" href="css/users.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="top-bar">
            <h1>MerguezShop</h1>
            <nav>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="sale.php">Vente</a></li>
                    <li><a href="profile.php">Mon Profil</a></li>
                    <li><a href="cart.php">🛒 Panier</a></li>
                    <li><a href="users.php">Users</a></li>
                    <li><a href="logout.php" class="logout-btn">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <h2>Liste des utilisateurs</h2>
        <div class="users-list">
            <?php foreach ($users as $user): ?>
                <a href="user_profile.php?id=<?= htmlspecialchars($user['id']) ?>" class="user-item">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($user['photo']) ?>" alt="Photo de <?= htmlspecialchars($user['username']) ?>">
                    <?php else: ?>
                        <img src="img/default-user.png" alt="Photo par défaut">
                    <?php endif; ?>
                    <p><?= htmlspecialchars($user['username']) ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
