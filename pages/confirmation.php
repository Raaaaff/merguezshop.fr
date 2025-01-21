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

// Requête pour récupérer les articles du panier
$query = $pdo->prepare("
    SELECT a.id, a.nom, a.prix, c.quantite, s.quantite AS stock
    FROM Cart c
    JOIN Article a ON c.article_id = a.id
    JOIN Stock s ON a.id = s.article_ID
    WHERE c.user_id = ?
");
$query->execute([$user_id]);
$cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total du panier
$total_general = 0;
foreach ($cart_items as $item) {
    $total_general += $item['prix'] * $item['quantite'];
}

// Récupérer le solde de l'utilisateur
$userQuery = $pdo->prepare("SELECT solde FROM User WHERE id = ?");
$userQuery->execute([$user_id]);
$user = $userQuery->fetch(PDO::FETCH_ASSOC);
$solde = $user['solde'];

// Vérification du solde et validation de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($solde >= $total_general) {
        // Déduction du solde
        $updateSolde = $pdo->prepare("UPDATE User SET solde = solde - ? WHERE id = ?");
        $updateSolde->execute([$total_general, $user_id]);

        // Mise à jour des stocks
        foreach ($cart_items as $item) {
            $new_stock = $item['stock'] - $item['quantite'];
            $updateStock = $pdo->prepare("UPDATE Stock SET quantite = ? WHERE article_ID = ?");
            $updateStock->execute([$new_stock, $item['id']]);
        }

        // Vider le panier
        $clearCart = $pdo->prepare("DELETE FROM Cart WHERE user_id = ?");
        $clearCart->execute([$user_id]);

        // Redirection vers une page de succès
        header('Location: success.php');
        exit;
    } else {
        $error = "Solde insuffisant pour effectuer cette commande.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande</title>
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <header>
        <h1>Confirmation de la commande</h1>
    </header>
    <main>
        <h2>Résumé de votre commande</h2>
        <ul>
            <?php foreach ($cart_items as $item): ?>
                <li>
                    <?= htmlspecialchars($item['nom']) ?> - 
                    Quantité : <?= $item['quantite'] ?> - 
                    Total : <?= number_format($item['prix'] * $item['quantite'], 2) ?> €
                </li>
            <?php endforeach; ?>
        </ul>
        <h3>Total général : <?= number_format($total_general, 2) ?> €</h3>
        <h3>Votre solde : <?= number_format($solde, 2) ?> €</h3>

        <?php if (isset($error)): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <button type="submit">Payer</button>
        </form>
        <a href="cart.php">Retour au panier</a>
    </main>
</body>
</html>
