<?php
// Inclure la connexion à la base de données
include('config.php');

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Requête pour récupérer les articles du panier de l'utilisateur
$query = $pdo->prepare("
    SELECT a.nom, a.prix, a.image, c.quantite 
    FROM Cart c 
    JOIN Article a ON c.article_id = a.id 
    WHERE c.user_id = ?
");
$query->execute([$user_id]);
$cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - MerguezShop</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
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
                </ul>
            </nav>
        </div>
    </header>

    <!-- Contenu principal -->
    <main>
        <h2>Panier</h2>

        <?php if (count($cart_items) > 0): ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Quantité</th>
                        <th>Prix unitaire</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_general = 0;
                    foreach ($cart_items as $item):
                        $total_article = $item['prix'] * $item['quantite'];
                        $total_general += $total_article;
                    ?>
                    <tr>
                        <td>
                            <img src="<?= $item['image'] ?>" alt="<?= $item['nom'] ?>" class="cart-image">
                            <span><?= $item['nom'] ?></span>
                        </td>
                        <td><?= $item['quantite'] ?></td>
                        <td><?= number_format($item['prix'], 2) ?> €</td>
                        <td><?= number_format($total_article, 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-total">
                <h3>Total général : <?= number_format($total_general, 2) ?> €</h3>
            </div>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </main>

    <!-- Pied de page -->
    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
