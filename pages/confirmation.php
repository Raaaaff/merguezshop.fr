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

// Vérifier si l'utilisateur a suffisamment de solde pour passer la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier que le solde est suffisant
    if ($solde_user >= $total_panier) {
        // Récupérer les informations de facturation
        $adresse_facturation = $_POST['adresse_facturation'];
        $code_postal = $_POST['code_postal'];
        $ville = $_POST['ville'];
        
        // Mettre à jour le solde de l'utilisateur après la commande
        $nouveau_solde = $solde_user - $total_panier;
        $updateSoldeQuery = $pdo->prepare("UPDATE User SET solde = :solde WHERE id = :user_id");
        $updateSoldeQuery->execute([
            'solde' => $nouveau_solde,
            'user_id' => $user_id
        ]);

        // Créer une entrée de commande (facultatif, selon l'architecture du projet)
        $insertCommandeQuery = $pdo->prepare("INSERT INTO Commandes (user_id, total, adresse_facturation, code_postal, ville) VALUES (:user_id, :total, :adresse_facturation, :code_postal, :ville)");
        $insertCommandeQuery->execute([
            'user_id' => $user_id,
            'total' => $total_panier,
            'adresse_facturation' => $adresse_facturation,
            'code_postal' => $code_postal,
            'ville' => $ville
        ]);

        // Vider le panier de l'utilisateur
        $deletePanierQuery = $pdo->prepare("DELETE FROM Cart WHERE user_id = :user_id");
        $deletePanierQuery->execute(['user_id' => $user_id]);

        // Générer une facture
        $facture_id = $pdo->lastInsertId();
        header("Location: facture.php?facture_id=$facture_id");
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
    <title>Confirmation de Commande - MerguezShop</title>
    <link rel="stylesheet" href="css/confirmation.css">
</head>
<body>
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
                    <!-- Bouton de déconnexion -->
                    <li><a href="logout.php" class="logout-btn">Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <h2>Confirmation de Commande</h2>

        <?php if (isset($message)): ?>
            <p style="color: red;"><?= $message; ?></p>
        <?php endif; ?>

        <h3>Articles dans votre panier</h3>
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
                <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nom']) ?></td>
                    <td><?= $item['quantite'] ?></td>
                    <td><?= number_format($item['prix'], 2) ?> €</td>
                    <td><?= number_format($item['prix'] * $item['quantite'], 2) ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

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
    </main>

    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
