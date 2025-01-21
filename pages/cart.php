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

// Fonction pour récupérer la quantité maximale en stock
function getMaxStock($article_id, $pdo) {
    try {
        $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_ID = :article_ID");
        $stockQuery->execute(['article_ID' => $article_id]);
        $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);
        
        if ($stock === false) {
            return 0; 
        }
        
        return (int)$stock['quantite']; 
    } catch (PDOException $e) {
        return 0; 
    }
}

// Fonction pour récupérer le solde de l'utilisateur
function getUserBalance($user_id, $pdo) {
    try {
        $balanceQuery = $pdo->prepare("SELECT solde FROM User WHERE id = :user_id");
        $balanceQuery->execute(['user_id' => $user_id]);
        $balance = $balanceQuery->fetch(PDO::FETCH_ASSOC);

        if ($balance === false) {
            echo '<script>console.log("Aucun solde trouvé pour l\'utilisateur avec l\'ID : ' . $user_id . '");</script>';
            return 0;
        }

        return (float)$balance['solde']; 
    } catch (PDOException $e) {
        echo '<script>console.log("Erreur dans getUserBalance : ' . $e->getMessage() . '");</script>';
        return 0; // Retourne 0 en cas d'erreur
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'] ?? null;
    $new_quantities = $_POST['quantite'] ?? null; // Récupérer les quantités du tableau associatif

    if (isset($_POST['remove']) && $article_id) {
        // Supprimer l'article du panier
        $removeQuery = $pdo->prepare("DELETE FROM Cart WHERE user_id = :user_id AND article_id = :article_id");
        $removeQuery->execute([
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
        echo "<script>alert('Article supprimé du panier.');</script>";
    }

    if ($new_quantities) {
        foreach ($new_quantities as $article_id => $new_quantity) {
            if ($article_id) {
                // Vérifier la quantité demandée et la quantité en stock
                $maxStock = getMaxStock($article_id, $pdo);
                if ($new_quantity > $maxStock) {
                    echo "<script>alert('Quantité demandée pour l\'article ID $article_id dépasse le stock disponible.');</script>";
                } else {
                    // Mettre à jour la quantité dans le panier
                    $updateQuantityQuery = $pdo->prepare("UPDATE Cart SET quantite = :quantite WHERE user_id = :user_id AND article_id = :article_id");
                    $updateQuantityQuery->execute([
                        'quantite' => $new_quantity,
                        'user_id' => $user_id,
                        'article_id' => $article_id
                    ]);
                }
            }
        }
    }

    // Vérifier la commande
    if (isset($_POST['confirm_order'])) {
        // Vérification de la commande
        $total_general = 0;
        $cart_items = [];
        $query = $pdo->prepare("
            SELECT a.id, a.nom, a.prix, a.image, c.quantite 
            FROM Cart c 
            JOIN Article a ON c.article_id = a.id 
            WHERE c.user_id = ?
        ");
        $query->execute([$user_id]);
        $cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

        // Calcul du total général du panier
        foreach ($cart_items as $item) {
            $total_general += $item['prix'] * $item['quantite'];
        }

        $user_balance = getUserBalance($user_id, $pdo);

        if ($user_balance >= $total_general) {
            echo "<script>alert('Commande confirmée avec succès.'); window.location = 'confirmation.php';</script>";
        } else {
            echo "<script>alert('Vous n\'avez pas assez de solde pour passer cette commande.');</script>";
        }
    }
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

// Récupérer le solde de l'utilisateur
$user_balance = getUserBalance($user_id, $pdo);

// Calcul du total général
$total_general = 0;
foreach ($cart_items as $item) {
    $total_general += $item['prix'] * $item['quantite'];
}
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

        <div class="user-balance">
            <p>Votre solde actuel : <?= number_format($user_balance, 2) ?> €</p>
        </div>

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
                        foreach ($cart_items as $item):
                            $maxStock = getMaxStock($item['id'], $pdo);
                            $total_article = $item['prix'] * $item['quantite'];
                            ?>
                        <tr>
                            <td>
                                <img src="data:image/jpeg;base64,<?= base64_encode($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-image">
                                <span><?= htmlspecialchars($item['nom']) ?></span>
                            </td>
                            <td>
                                <input type="number" name="quantite[<?= $item['id'] ?>]" value="<?= $item['quantite'] ?>" min="1" max="<?= $maxStock ?>" required>
                            </td>

                            <td><?= number_format($item['prix'], 2) ?> €</td>
                            <td><?= number_format($total_article, 2) ?> €</td>
                            <td>
                                <button type="submit" name="remove" value="true">Supprimer</button>
                                <input type="hidden" name="article_id" value="<?= $item['id'] ?>">
                                <button type="submit">Mettre à jour</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <h3>Total général : <?= number_format($total_general, 2) ?> €</h3>
                    <?php if ($user_balance >= $total_general): ?>
                        <button class="confirm-button" type="submit" name="confirm_order">Confirmer la commande</button>
                    <?php else: ?>
                        <p class="insufficient-funds">Vous n'avez pas assez de solde pour cette commande.</p>
                    <?php endif; ?>
                </div>
            </form>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; 2025 MerguezShop. Tous droits réservés.</p>
    </footer>
</body>
</html>
