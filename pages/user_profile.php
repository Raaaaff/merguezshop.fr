<?php
// Inclure la connexion à la base de données
include('config.php');

// Démarrer la session
session_start();

// Récupérer l'ID de l'utilisateur depuis l'URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier que l'ID est valide
if ($user_id <= 0) {
    die('Utilisateur non trouvé.');
}

// Récupérer les détails de l'utilisateur
$userQuery = $pdo->prepare("SELECT * FROM User WHERE id = ?");
$userQuery->execute([$user_id]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);

// Récupérer les articles de l'utilisateur
$articlesQuery = $pdo->prepare("SELECT * FROM Article WHERE author_ID = ?");
$articlesQuery->execute([$user_id]);
$articles = $articlesQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="css/user_profile.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
</head>
<body>
    <header>
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
    </header>

    <main>
        <h2>Profil de <?= htmlspecialchars($user['username']) ?></h2>
        <div class="user-details">
            <img src="data:image/jpeg;base64,<?= base64_encode($user['photo']) ?>" alt="Photo de <?= htmlspecialchars($user['username']) ?>">
            <p>Prénom : <?= htmlspecialchars($user['prenom']) ?></p>
            <p>Nom : <?= htmlspecialchars($user['nom']) ?></p>
            <p>Email : <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <h3>Articles en vente</h3>
        <div class="article-list">
            <?php foreach ($articles as $article): ?>
                <a href="detail.php?id=<?= htmlspecialchars($article['id']) ?>" class="article-item">
                    <img src="data:image/jpeg;base64,<?= base64_encode($article['image']) ?>" alt="<?= htmlspecialchars($article['nom']) ?>">
                    <p><?= htmlspecialchars($article['nom']) ?> - <?= htmlspecialchars($article['prix']) ?> &euro;</p>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
