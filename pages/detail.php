<?php
// Inclure la connexion à la base de données
include('config.php');
session_start();

// Vérifier si l'article ID est passé en paramètre
if (isset($_GET['id'])) {
    $article_id = $_GET['id'];

    // Récupérer les détails de l'article depuis la base de données
    $query = $pdo->prepare("SELECT * FROM Article WHERE id = :id");
    $query->execute(['id' => $article_id]);
    $article = $query->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        // Si l'article n'existe pas
        echo "Article non trouvé.";
        exit;
    }

    // Récupérer les images supplémentaires de l'article (entre 1 et 4 images)
    $imageQuery = $pdo->prepare("SELECT * FROM Photos WHERE article_ID = :article_ID LIMIT 4");
    $imageQuery->execute(['article_ID' => $article_id]);
    $images = $imageQuery->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier la quantité en stock
    $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_ID = :article_ID");
    $stockQuery->execute(['article_ID' => $article_id]);
    $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);
    $quantite_disponible = $stock ? $stock['quantite'] : 0;
} else {
    echo "Aucun article sélectionné.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de l'article</title>
    <link rel="stylesheet" href="css/details.css">
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
        <div class="article-details-container">
            <!-- Affichage de l'image principale -->
            <?php
                $imageData = $article['image'] ?? null;
                if ($imageData && strlen($imageData) > 100) {
                    $imageBase64 = base64_encode($imageData);
                    $imageSrc = "data:image/jpeg;base64," . $imageBase64;
                } elseif (!empty($article['imageSrc'])) {
                    $imageSrc = htmlspecialchars($article['imageSrc']);
                } else {
                    $imageSrc = "../img/no_found.jpg";
                }
            ?>
            <div class="article-image">
                <img src="<?= htmlspecialchars($imageSrc) ?>" alt="Image de l'article">
            </div>

            <!-- Détails de l'article -->
            <div class="article-info">
                <h2 class="article-title"><?= htmlspecialchars($article['nom']) ?></h2>
                <p class="article-description"><?= htmlspecialchars($article['description']) ?></p>
                <p class="article-price"><?= htmlspecialchars($article['prix']) ?> &euro;</p>

                <!-- Affichage de la quantité en stock -->
                <p class="article-quantity">
                    Quantité disponible : <?= $quantite_disponible ?> article(s)
                </p>

                <!-- Ajouter au panier -->
                <?php if ($quantite_disponible > 0): ?>
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                        <button type="submit" class="btn">Ajouter au panier</button>
                    </form>
                <?php else: ?>
                    <button class="btn" disabled>Rupture de stock</button>
                <?php endif; ?>

                <!-- Affichage des images supplémentaires -->
                <div class="article-images-container">
                    <div class="additional-images">
                        <?php
                            foreach ($images as $image) {
                                if (!empty($image['image']) && strlen($image['image']) > 100) {
                                    $imageSrc = "data:image/jpeg;base64," . base64_encode($image['image']);
                                } else {
                                    $imageSrc = htmlspecialchars($image['imageSrc']);
                                }
                                echo '<img src="' . $imageSrc . '" class="additional-image" alt="Image supplémentaire">';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
