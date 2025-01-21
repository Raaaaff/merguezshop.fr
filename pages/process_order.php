<?php
// Inclure la connexion à la base de données
include('config.php');

// Inclure la bibliothèque FPDF
require('libs/fpdf/fpdf.php');

session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier si des informations de commande ont été envoyées via le formulaire
if (isset($_SESSION['order_data'])) {
    // Récupérer les informations de la commande
    $total_panier = $_SESSION['order_data']['total_panier'];
    $adresse_facturation = $_SESSION['order_data']['adresse_facturation'];
    $code_postal = $_SESSION['order_data']['code_postal'];
    $ville = $_SESSION['order_data']['ville'];

    // Récupérer les articles du panier de l'utilisateur
    $query = $pdo->prepare("SELECT c.id, c.article_id, c.quantite, a.nom, a.prix 
                            FROM Cart c 
                            JOIN Article a ON c.article_id = a.id 
                            WHERE c.user_id = ?");
    $query->execute([$user_id]);
    $cart_items = $query->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total pour vérifier
    $total_calculé = 0;
    foreach ($cart_items as $item) {
        $total_calculé += $item['prix'] * $item['quantite'];
    }

    // Vérifier que le total correspond (sécurité supplémentaire)
    if ($total_calculé != $total_panier) {
        die("Erreur dans le calcul du total.");
    }

    // Insérer la commande dans la table Commandes
    $queryCommande = $pdo->prepare("INSERT INTO Commandes (user_id, total, adresse_facturation, code_postal, ville, date_commande)
                                    VALUES (?, ?, ?, ?, ?, NOW())");
    $queryCommande->execute([$user_id, $total_panier, $adresse_facturation, $code_postal, $ville]);

    // Récupérer l'ID de la commande insérée
    $commande_id = $pdo->lastInsertId();

    // Mettre à jour les articles dans la table Cart avec l'ID de la commande
    $queryUpdateCart = $pdo->prepare("UPDATE Cart SET commande_id = ? WHERE user_id = ?");
    $queryUpdateCart->execute([$commande_id, $user_id]);

    // Vider le panier de l'utilisateur
    $queryDeleteCart = $pdo->prepare("DELETE FROM Cart WHERE user_id = ?");
    $queryDeleteCart->execute([$user_id]);

    // Mettre à jour le solde de l'utilisateur en fonction du total de la commande
    $querySolde = $pdo->prepare("UPDATE User SET solde = solde - ? WHERE id = ?");
    $querySolde->execute([$total_panier, $user_id]);

    // Création du PDF de la facture
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Titre de la facture
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, "Facture - MerguezShop", 0, 1, 'C');

    // Informations de commande
    $pdf->SetFont('Arial', '', 12);
    $pdf->Ln(10);
    $pdf->Cell(0, 10, "Numéro de commande: $commande_id", 0, 1);
    $pdf->Cell(0, 10, "Total à payer: " . number_format($total_panier, 2) . chr(128), 0, 1);
    $pdf->Cell(0, 10, "Adresse de facturation: $adresse_facturation", 0, 1);
    $pdf->Cell(0, 10, "Code Postal: $code_postal", 0, 1);
    $pdf->Cell(0, 10, "Ville: $ville", 0, 1);
    $pdf->Ln(10);

    // Détails des articles
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(100, 10, "Article", 1);
    $pdf->Cell(30, 10, "Quantité", 1, 0, 'C');
    $pdf->Cell(30, 10, "Prix Unitaire", 1, 0, 'C');
    $pdf->Cell(30, 10, "Total", 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);
    foreach ($cart_items as $item) {
        $total_item = $item['prix'] * $item['quantite'];
        $pdf->Cell(100, 10, $item['nom'], 1);
        $pdf->Cell(30, 10, $item['quantite'], 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($item['prix'], 2) . chr(128), 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($total_item, 2) . chr(128), 1, 1, 'C');
    }

    // Enregistrer la facture en PDF
    $file_name = "facture_$commande_id.pdf";
    $pdf->Output('F', 'factures/' . $file_name);

    // Réinitialiser les données de commande
    $_SESSION['order_data'] = null; 

    // Message de confirmation
    $message = "Commande passée avec succès. Votre facture est prête et peut être téléchargée.";
} else {
    // Si l'utilisateur accède directement à la page sans passer par le formulaire de commande
    header('Location: home.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Commande - MerguezShop</title>
    <link rel="stylesheet" href="css/process_order.css">
</head>
<body>
    <h2>Merci pour votre commande !</h2>
    <p>Votre commande a été validée. Voici les détails :</p>
    <p><strong>Numéro de commande :</strong> <?= $commande_id ?></p>
    <p><strong>Total :</strong> <?= number_format($total_panier, 2) ?> €</p>
    <p><strong>Adresse de facturation :</strong> <?= htmlspecialchars($adresse_facturation) ?></p>
    <p><strong>Code Postal :</strong> <?= htmlspecialchars($code_postal) ?></p>
    <p><strong>Ville :</strong> <?= htmlspecialchars($ville) ?></p>

    <p>Votre facture est disponible. <a href="factures/<?= $file_name ?>" target="_blank">Télécharger la facture PDF</a></p>

    <a href="home.php">Retour à l'accueil</a>
</body>
</html>
