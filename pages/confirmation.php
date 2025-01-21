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

// Récupérer les articles du panier de l'utilisateur
$query = $pdo->prepare("
    SELECT a.id, a.nom, a.prix, a.image, c.quantite 
    FROM Cart c 
    JOIN Article a ON c.article_id = a.id 
    WHERE c.user_id = ?
");
$query->execute([$user_id]);
$cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

// Récupérer le solde de l'utilisateur
$querySolde = $pdo->prepare("SELECT solde FROM User WHERE id = ?");
$querySolde->execute([$user_id]);
$user_data = $querySolde->fetch(PDO::FETCH_ASSOC);
$solde_user = $user_data['solde'];

// Calculer le total du panier
$total_panier = 0;
foreach ($cart_items as $item) {
    $total_panier += $item['prix'] * $item['quantite'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier que le solde est suffisant
    if ($solde_user >= $total_panier) {
        // Enregistrer la commande et les informations de facturation
        $_SESSION['order_data'] = [
            'total_panier' => $total_panier,
            'adresse_facturation' => $_POST['adresse_facturation'],
            'code_postal' => $_POST['code_postal'],
            'ville' => $_POST['ville']
        ];

        // Rediriger vers le fichier de traitement de la commande
        header('Location: process_order.php');
        exit;
    } else {
        $message = "Vous n'avez pas suffisamment de solde pour passer cette commande.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - MerguezShop</title>
    <link rel="stylesheet" href="css/confirmation.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
</head>
<body>
    <!-- HTML pour afficher les articles du panier, etc... -->
    <h3>Total à payer : <?= number_format($total_panier, 2) ?> €</h3>

    <?php if ($solde_user >= $total_panier): ?>
        <form action="confirmation.php" method="POST">
            <h3>Informations de facturation</h3>
            <label for="adresse_facturation">Adresse de facturation :</label>
            <input type="text" id="adresse_facturation" name="adresse_facturation" required>

            <label for="code_postal">Code Postal :</label>
            <input type="text" id="code_postal" name="code_postal" required>

            <label for="ville">Ville :</label>
            <input type="text" id="ville" name="ville" required>

            <button type="submit">Valider la commande</button>
        </form>
    <?php else: ?>
        <p>Votre solde est insuffisant pour passer cette commande.</p>
    <?php endif; ?>
</body>
</html>
