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

// Vérifier si une modification de quantité ou suppression est demandée
if (isset($_POST['update'])) {
    $article_id = $_POST['article_id'];
    $quantite = $_POST['quantite'];

    // Vérifier la quantité en stock
    $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_ID = :article_id");
    $stockQuery->execute(['article_id' => $article_id]);
    $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);

    if ($quantite > $stock['quantite']) {
        echo "<script>alert('Quantité demandée dépasse le stock disponible.');</script>";
    } else {
        // Mettre à jour la quantité dans le panier
        $updateQuery = $pdo->prepare("UPDATE Cart SET quantite = :quantite WHERE user_id = :user_id AND article_id = :article_id");
        $updateQuery->execute([
            'quantite' => $quantite,
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
    }
}

if (isset($_POST['remove'])) {
    $article_id = $_POST['article_id'];
    // Supprimer l'article du panier
    $removeQuery = $pdo->prepare("DELETE FROM Cart WHERE user_id = :user_id AND article_id = :article_id");
    $removeQuery->execute([
        'user_id' => $user_id,
        'article_id' => $article_id
    ]);
}

// Requête pour récupérer les articles du panier de l'utilisateur
$query = $pdo->prepare("
    SELECT a.id, a.nom, a.prix, a.image, c.quantite 
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
    <link rel="stylesheet" href="css/cart.css">
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

    <main>
        <h2>Panier</h2>

        <?php if (count($cart_items) > 0): ?>
            <form action="cart.php" method="POST">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th>Quantité</th>
                            <th>Prix unitaire</th>
                            <th>Total</th>
                            <th>Actions</th>
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
                                <img src="data:image/jpeg;base64,<?= base64_encode($item['image']) ?>" alt="<?= $item['nom'] ?>" class="cart-image">
                                <span><?= $item['nom'] ?></span>
                            </td>
                            <td>
                                <input type="number" name="quantite" value="<?= $item['quantite'] ?>" min="1" max="<?= getMaxStock($item['id'], $pdo) ?>" required>
                            </td>
                            <td><?= number_format($item['prix'], 2) ?> €</td>
                            <td><?= number_format($total_article, 2) ?> €</td>
                            <td>
                                <button type="submit" name="update" value="true">Mettre à jour</button>
                                <button type="submit" name="remove" value="true">Supprimer</button>
                                <input type="hidden" name="article_id" value="<?= $item['id'] ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <h3>Total général : <?= number_format($total_general, 2) ?> €</h3>
                </div>
            </form>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>

<?php
// Fonction pour récupérer la quantité maximale en stock
function getMaxStock($article_id, $pdo) {
    $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_ID = :article_id");
    $stockQuery->execute(['article_id' => $article_id]);
    $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);
    return $stock ? $stock['quantite'] : 0;
}
?>
