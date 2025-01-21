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
        $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_ID = :article_id");
        $stockQuery->execute(['article_id' => $article_id]);
        $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);
        
        if ($stock === false) {
            // Si la requête échoue ou retourne false, loggez une erreur
            error_log("Aucun stock trouvé pour l'article avec l'ID : " . $article_id);
            return 0; // Retourne 0 si aucun résultat trouvé
        }
        
        return (int)$stock['quantite']; // Retourne la quantité trouvée, convertie en entier
    } catch (PDOException $e) {
        // Gérer l'erreur (optionnel, pour déboguer uniquement)
        error_log("Erreur dans getMaxStock : " . $e->getMessage());
        return 0; // Retourne 0 en cas d'erreur
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
            return 0; // Retourne 0 si aucun résultat trouvé
        }

        // Log pour vérifier la valeur récupérée
        echo '<script>console.log("Solde récupéré pour l\'utilisateur ID ' . $user_id . ' : ' . $balance['solde'] . '");</script>';

        return (float)$balance['solde']; // Retourne le solde de l'utilisateur
    } catch (PDOException $e) {
        // Log d'erreur
        echo '<script>console.log("Erreur dans getUserBalance : ' . $e->getMessage() . '");</script>';
        return 0; // Retourne 0 en cas d'erreur
    }
}



// Vérifier si une modification ou une suppression est demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = $_POST['article_id'] ?? null;

    if (isset($_POST['update']) && $article_id) {
        $quantite = (int)$_POST['quantite'];

        // Vérifier la quantité en stock
        $maxStock = getMaxStock($article_id, $pdo);
        if ($quantite > $maxStock) {
            echo "<script>alert('Quantité demandée dépasse le stock disponible.');</script>";
        } elseif ($quantite < 1) {
            echo "<script>alert('Quantité invalide.');</script>";
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

    if (isset($_POST['remove']) && $article_id) {
        // Supprimer l'article du panier
        $removeQuery = $pdo->prepare("DELETE FROM Cart WHERE user_id = :user_id AND article_id = :article_id");
        $removeQuery->execute([
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
        echo "<script>alert('Article supprimé du panier.');</script>";
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
                        $total_general = 0;
                        foreach ($cart_items as $item):
                            $maxStock = getMaxStock($item['id'], $pdo);

                            if ($maxStock === 0) {
                                $is_out_of_stock = true;
                            } else {
                                $is_out_of_stock = false;
                            }

                            $total_article = $item['prix'] * $item['quantite'];
                            $total_general += $total_article;
                        ?>
                        <tr>
                            <td>
                                <img src="data:image/jpeg;base64,<?= base64_encode($item['image']) ?>" alt="<?= htmlspecialchars($item['nom']) ?>" class="cart-image">
                                <span><?= htmlspecialchars($item['nom']) ?></span>
                            </td>
                            <td>
                                <?php if ($is_out_of_stock): ?>
                                    <span>Hors stock</span>
                                <?php else: ?>
                                    <input type="number" name="quantite" value="<?= $item['quantite'] ?>" min="1" max="<?= $maxStock ?>" required>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($item['prix'], 2) ?> €</td>
                            <td><?= number_format($total_article, 2) ?> €</td>
                            <td>
                                <?php if (!$is_out_of_stock): ?>
                                    <button type="submit" name="update" value="true">Mettre à jour</button>
                                <?php endif; ?>
                                <button type="submit" name="remove" value="true">Supprimer</button>
                                <input type="hidden" name="article_id" value="<?= $item['id'] ?>">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <h3>Total général : <?= number_format($total_general, 2) ?> €</h3>
                    <button class="confirm-button" onclick="location.href='confirmation.php';">
                        Confirmer la commande
                    </button>
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
