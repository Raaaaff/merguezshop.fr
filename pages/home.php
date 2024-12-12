<?php
// Inclure la connexion à la base de données
include('config.php');

// Récupérer les articles depuis la base de données
$query = $pdo->query("SELECT * FROM Article");
$articles = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - MerguezShop</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="logo">
            <h1>MerguezShop</h1>
        </div>
        <nav>
            <ul>
                <li><a href="home.php">Accueil</a></li>
                <li><a href="login.php">Se connecter</a></li>
                <li><a href="profile.php">Mon Profil</a></li>
            </ul>
        </nav>
    </header>

    <!-- Contenu principal -->
    <main>
        <h2>Nos Articles</h2>
        <div class="article-list">
            <?php foreach ($articles as $article): ?>
                <div class="article-item">
                    <img src="<?= $article['image'] ?>" alt="<?= $article['nom'] ?>" class="article-image">
                    <div class="article-details">
                        <h3 class="article-title"><?= $article['nom'] ?></h3>
                        <p class="article-description"><?= substr($article['description'], 0, 100) . '...' ?></p>
                        <p class="article-price"><?= $article['prix'] ?> €</p>
                        <a href="#" class="btn">Ajouter au panier</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Pied de page -->
    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
