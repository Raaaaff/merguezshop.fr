<?php
// Inclure la connexion à la base de données
include('config.php');
require('libs/fpdf/fpdf.php'); // Inclure la bibliothèque FPDF

// Désactiver la sortie des erreurs
ob_start(); // Commence une nouvelle mise en tampon de sortie pour éviter les erreurs

// Récupérer l'ID de la facture depuis l'URL
$facture_id = isset($_GET['facture_id']) ? (int)$_GET['facture_id'] : 0;

if ($facture_id > 0) {
    // Récupérer les détails de la commande
    $query = $pdo->prepare("SELECT * FROM Commandes WHERE id = ?");
    $query->execute([$facture_id]);
    $commande = $query->fetch(PDO::FETCH_ASSOC);

    // Vérifier si la commande existe
    if ($commande) {
        // Récupérer les articles de la commande
        $queryArticles = $pdo->prepare("
        SELECT a.nom, c.quantite, a.prix 
        FROM Cart c 
        JOIN Article a ON c.article_id = a.id 
        WHERE c.commande_id = ?
        ");
        $queryArticles->execute([$facture_id]);
        $cart_items = $queryArticles->fetchAll(PDO::FETCH_ASSOC);

        // Créer le PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);

        // Titre de la facture
        $pdf->Cell(200, 10, "Facture de commande #$facture_id", 0, 1, 'C');
        $pdf->Ln(10);

        // Détails de la commande
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(100, 10, "Nom : " . $commande['user_id'], 0, 1);
        $pdf->Cell(100, 10, "Adresse de facturation : " . $commande['adresse_facturation'], 0, 1);
        $pdf->Cell(100, 10, "Code Postal : " . $commande['code_postal'], 0, 1);
        $pdf->Cell(100, 10, "Ville : " . $commande['ville'], 0, 1);
        $pdf->Ln(10);

        // Tableau des articles
        $pdf->Cell(100, 10, "Article", 1, 0, 'C');
        $pdf->Cell(30, 10, "Quantité", 1, 0, 'C');
        $pdf->Cell(30, 10, "Prix", 1, 0, 'C');
        $pdf->Cell(30, 10, "Total", 1, 1, 'C');
        
        $total_facture = 0;
        foreach ($cart_items as $item) {
            $total_article = $item['prix'] * $item['quantite'];
            $total_facture += $total_article;
            
            $pdf->Cell(100, 10, $item['nom'], 1);
            $pdf->Cell(30, 10, $item['quantite'], 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($item['prix'], 2) . " €", 1, 0, 'C');
            $pdf->Cell(30, 10, number_format($total_article, 2) . " €", 1, 1, 'C');
        }

        // Total général
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(160, 10, "Total à payer", 1);
        $pdf->Cell(30, 10, number_format($total_facture, 2) . " €", 1, 1, 'C');

        // Générer le PDF et l'envoyer pour le téléchargement
        $pdf->Output('D', "facture_$facture_id.pdf"); // 'D' pour forcer le téléchargement

        ob_end_clean(); // Nettoie le tampon de sortie avant de quitter
        exit();
    } else {
        echo "Facture non trouvée.";
    }
} else {
    // Si l'ID de la facture n'est pas valide
    echo "ID de facture invalide.";
}
?>
