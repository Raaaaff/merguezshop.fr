<?php
session_start();
include('db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Récupérer les articles du panier
$query = $pdo->prepare("SELECT Cart.id, Article.nom, Article.prix, Cart.quantite 
                        FROM Cart 
                        JOIN Article ON Cart.article_id = Article.id 
                        WHERE Cart.user_id = ?");
$query->execute([$userId]);
$panier = $query->fetchAll(PDO::FETCH_ASSOC);

// Calculer le total
$total = 0;
foreach ($panier as $article) {
    $total += $article['prix'] * $article['quantite'];
}

// Vérifier le solde et vider le panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
    $adresse = $_POST['adresse'];
    $userQuery = $pdo->prepare("SELECT solde FROM User WHERE id = ?");
    $userQuery->execute([$userId]);
    $solde = $userQuery->fetchColumn();

    if ($solde >= $total) {
        // Mettre à jour le solde
        $updateSolde = $pdo->prepare("UPDATE User SET solde = solde - ? WHERE id = ?");
        $updateSolde->execute([$total, $userId]);

        // Vider le panier
        $deleteCart = $pdo->prepare("DELETE FROM Cart WHERE user_id = ?");
        $deleteCart->execute([$userId]);

        // Générer une facture (simplifié)
        $facture = "Facture pour la commande de " . date('Y-m-d H:i:s') . "\n";
        foreach ($panier as $article) {
            $facture .= $article['nom'] . " x " . $article['quantite'] . " - " . ($article['prix'] * $article['quantite']) . "€\n";
        }
        $facture .= "Total : " . $total . "€\nAdresse : " . $adresse . "\n";

        file_put_contents("factures/facture_user_$userId.txt", $facture);

        $message = "Commande validée avec succès. Votre facture a été générée.";
    } else {
        $message = "Solde insuffisant.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
</head>
<body>
    <h1>Confirmation de Commande</h1>

    <form method="POST">
        <label for="adresse">Adresse de facturation :</label>
        <input type="text" name="adresse" id="adresse" required>
        <button type="submit" name="valider">Valider la commande</button>
    </form>

    <?php if (isset($message)): ?>
        <p><?= $message ?></p>
    <?php endif; ?>
</body>
</html>
