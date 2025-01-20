<?php
session_start();
include('db_connection.php'); // Connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Récupérer les articles du panier de l'utilisateur
$query = $pdo->prepare("SELECT Cart.id, Article.nom, Article.prix, Cart.quantite 
                        FROM Cart 
                        JOIN Article ON Cart.article_id = Article.id 
                        WHERE Cart.user_id = ?");
$query->execute([$userId]);
$panier = $query->fetchAll(PDO::FETCH_ASSOC);

// Calculer le total du panier
$total = 0;
foreach ($panier as $article) {
    $total += $article['prix'] * $article['quantite'];
}

// Ajout d'articles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $cartId = $_POST['cart_id'];
    $nouvelleQuantite = $_POST['quantite'];
    $updateQuery = $pdo->prepare("UPDATE Cart SET quantite = ? WHERE id = ?");
    $updateQuery->execute([$nouvelleQuantite, $cartId]);
    header('Location: panier.php');
    exit;
}

// Suppression d'articles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    $cartId = $_POST['cart_id'];
    $deleteQuery = $pdo->prepare("DELETE FROM Cart WHERE id = ?");
    $deleteQuery->execute([$cartId]);
    header('Location: panier.php');
    exit;
}

// Vérification pour passer commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commander'])) {
    $userQuery = $pdo->prepare("SELECT solde FROM User WHERE id = ?");
    $userQuery->execute([$userId]);
    $solde = $userQuery->fetchColumn();

    if ($solde >= $total) {
        header('Location: confirmation.php');
        exit;
    } else {
        $message = "Solde insuffisant pour passer la commande.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier</title>
</head>
<body>
    <h1>Votre Panier</h1>

    <?php if (!empty($panier)): ?>
        <table>
            <tr>
                <th>Article</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($panier as $article): ?>
                <tr>
                    <td><?= htmlspecialchars($article['nom']) ?></td>
                    <td><?= number_format($article['prix'], 2) ?>€</td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?= $article['id'] ?>">
                            <input type="number" name="quantite" value="<?= $article['quantite'] ?>" min="1">
                            <button type="submit" name="ajouter">Mettre à jour</button>
                        </form>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?= $article['id'] ?>">
                            <button type="submit" name="supprimer">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p>Total : <?= number_format($total, 2) ?>€</p>

        <form method="POST">
            <button type="submit" name="commander">Passer commande</button>
        </form>
    <?php else: ?>
        <p>Votre panier est vide.</p>
    <?php endif; ?>

    <?php if (isset($message)): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>
</body>
</html>
