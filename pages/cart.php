<?php
// Inclure la connexion à la base de données
include('config.php');
include('libs/fpdf/fpdf.php'); // Inclure FPDF

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

        // Créer une entrée de commande
        $insertCommandeQuery = $pdo->prepare("INSERT INTO Commandes (user_id, total, adresse_facturation, code_postal, ville) VALUES (:user_id, :total, :adresse_facturation, :code_postal, :ville)");
        $insertCommandeQuery->execute([
            'user_id' => $user_id,
            'total' => $total_panier,
            'adresse_facturation' => $adresse_facturation,
            'code_postal' => $code_postal,
            'ville' => $ville
        ]);

        // Récupérer l'ID de la commande
        $commande_id = $pdo->lastInsertId();

        // Mettre à jour la table Stock pour réduire les quantités
        foreach ($cart_items as $item) {
            $updateStockQuery = $pdo->prepare("UPDATE Stock SET quantite = quantite - :quantite WHERE article_ID = :article_id");
            $updateStockQuery->execute([
                'quantite' => $item['quantite'],
                'article_id' => $item['id']
            ]);
        }

        // Vider le panier de l'utilisateur
        $deletePanierQuery = $pdo->prepare("DELETE FROM Cart WHERE user_id = :user_id");
        $deletePanierQuery->execute(['user_id' => $user_id]);

        // Générer la facture PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        // Titre de la facture
        $pdf->Cell(200, 10, 'Facture - MerguezShop', 0, 1, 'C');
        
        // Informations sur la commande
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(200, 10, 'Commande #: ' . $commande_id, 0, 1, 'L');
        $pdf->Cell(200, 10, 'Adresse de facturation: ' . $adresse_facturation, 0, 1, 'L');
        $pdf->Cell(200, 10, 'Code Postal: ' . $code_postal, 0, 1, 'L');
        $pdf->Cell(200, 10, 'Ville: ' . $ville, 0, 1, 'L');
        
        $pdf->Ln(10); // Ligne vide

        // Détails des articles
        $pdf->Cell(60, 10, 'Article', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Quantité', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Prix', 1, 0, 'C');
        $pdf->Cell(40, 10, 'Total', 1, 1, 'C');
        
        foreach ($cart_items as $item) {
            $pdf->Cell(60, 10, $item['nom'], 1);
            $pdf->Cell(40, 10, $item['quantite'], 1);
            $pdf->Cell(40, 10, number_format($item['prix'], 2) . ' €', 1);
            $pdf->Cell(40, 10, number_format($item['prix'] * $item['quantite'], 2) . ' €', 1, 1);
        }

        // Total de la commande
        $pdf->Ln(10); // Ligne vide
        $pdf->Cell(140, 10, 'Total à payer', 1);
        $pdf->Cell(40, 10, number_format($total_panier, 2) . ' €', 1, 1, 'C');

      
        $pdf->Output('D', 'facture_' . $facture_id . '.pdf');
        
        // Afficher un message de succès et rediriger vers la page d'accueil après 3 secondes
        echo "<script>
                alert('Votre commande a été confirmée et la facture a été générée avec succès.');
                setTimeout(function() {
                    window.location.href = 'home.php';
                }, 3000);
              </script>";
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

        <h3>Total: <?= number_format($total_panier, 2) ?> €</h3>

        <h3>Adresse de Facturation</h3>
        <form method="post">
            <input type="text" name="adresse_facturation" placeholder="Adresse" required>
            <input type="text" name="code_postal" placeholder="Code Postal" required>
            <input type="text" name="ville" placeholder="Ville" required>
            <button type="submit">Confirmer la Commande</button>
        </form>
    </main>

    <footer>
        <p>&copy; 2025 MerguezShop. Tous droits réservés.</p>
    </footer>
</body>
</html>
