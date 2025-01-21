<?php
// Inclure la connexion à la base de données
include('config.php');

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


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
        <div class="top-bar">
            <div class="logo">
                <h1>MerguezShop</h1>
            </div>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="query" placeholder="Rechercher un produit..." required>
                <button type="submit">Rechercher</button>
            </form>
            <nav>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="sale.php">Vente</a></li>
                    <li><a href="profile.php">Mon Profil</a></li>
                    <li><a href="cart.php">🛒 Panier</a></li>
                    <!-- Bouton de déconnexion -->
                    <li><a href="logout.php" class="logout-btn">Déconnexion</a></li>
                </ul>
            </nav>

        </div>
    </header>

   <!-- Contenu principal -->
<main>
    <h2>Nos Articles</h2>
    
    <div class="article-list"> <!-- Conteneur principal -->
        <?php foreach ($articles as $article): ?>
            <a href="detail.php?id=<?= htmlspecialchars($article['id']) ?>" class="article-item-link">
                <div class="article-item"> 
                    <?php
                        // Récupération de l'image depuis la base de données
                        $imageData = $article['image'] ?? null;

                        // Vérification si l'image est un BLOB non vide et assez long pour être valide
                        if ($imageData && strlen($imageData) > 100) { 
                            $imageBase64 = base64_encode($imageData);
                            $imageSrc = "data:image/jpeg;base64," . $imageBase64;
                        } elseif (!empty($article['imageSrc'])) { 
                            $imageSrc = htmlspecialchars($article['imageSrc']);
                        } else {
                            // Image par défaut
                            $imageSrc = "../img/no_found.jpg";
                        }
                    ?>
                    <!-- Affichage de l'image -->
                    <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Image de l'article" class="article-image">
                    
                    <!-- Détails de l'article -->
                    <div class="article-details">
                        <h3 class="article-title"> <?= htmlspecialchars($article['nom']) ?> </h3>
                        <p class="article-description"> 
                            <?= htmlspecialchars(substr($article['description'], 0, 100)) . '...' ?> 
                        </p>
                        <p class="article-price"> <?= htmlspecialchars($article['prix']) ?> &euro; </p>
                        <span class="btn">Ajouter au panier</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div> <!-- Fin de article-list -->
</main>

<!-- Pied de page -->
<footer>
    <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
</footer>
</body>
</html>