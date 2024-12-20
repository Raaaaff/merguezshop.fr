<?php
    // Inclure la connexion à la base de données
    include('config.php');
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    };

    if (isset($_GET['id'])) {
        $article_id = intval($_GET['id']); // Convertir l'ID en entier pour éviter les problèmes de sécurité
    } else {
        header('Location: login.php');
        exit;
    }

    // Récupérer les informations de l'article depuis la base de données
    $query = $pdo->prepare("SELECT * FROM Article WHERE ID = :id");
    $query->execute(['id' => $article_id]);
    $article = $query->fetch(PDO::FETCH_ASSOC);

    // Récupérer les photos supplémentaires depuis la base de données
    $query_photos = $pdo->prepare("SELECT * FROM Photos WHERE article_ID = :id");
    $query_photos->execute(['id' => $article_id]);
    $photos = $query_photos->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'article - MerguezShop</title>
    <link rel="stylesheet" href="css/details.css">
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="top-bar">
            <div class="logo">
                <h1>MerguezShop</h1>
            </div>
            <nav>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="sale.php">Vente</a></li>
                    <li><a href="profile.php">Mon Profil</a></li>
                    <li><a href="cart.php">🛒 Panier</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu principal -->
    <main>
        <?php if ($article): ?>
            <div class="product-details">
                <div class="product-images">
                    <img src="data:image/jpeg;base64,<?= base64_encode($article['image']) ?>" alt="Image principale de l'article" class="main-image">
                    <div class="additional-images">
                        <?php foreach ($photos as $photo): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($photo['image']) ?>" alt="Image de l'article" class="additional-image">
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="product-info">
                    <h2><?= htmlspecialchars($article['nom']) ?></h2>
                    <p class="description"><?= htmlspecialchars($article['description']) ?></p>
                    <p class="price"><?= htmlspecialchars($article['prix']) ?> &euro;</p>
                    <a href="#" class="btn">Ajouter au panier</a>
                </div>
            </div>
        <?php else: ?>
            <p>Article non trouvé.</p>
        <?php endif; ?>
    </main>
    <script>
        document.querySelectorAll('.additional-image').forEach(img => {
            img.addEventListener('click', function() {
                document.querySelector('.main-image').src = this.src;
            });
        });
    </script>


    <!-- Pied de page -->
    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
