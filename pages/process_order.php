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

// Vérifier que la session contient les données de la commande
if (!isset($_SESSION['order_data'])) {
    header('Location: confirmation.php');
    exit;
}

// Récupérer les données de la commande depuis la session
$order_data = $_SESSION['order_data'];
$total_panier = $order_data['total_panier'];
$adresse_facturation = $order_data['adresse_facturation'];
$code_postal = $order_data['code_postal'];
$ville = $order_data['ville'];

// Récupérer le solde de l'utilisateur
$querySolde = $pdo->prepare("SELECT solde FROM User WHERE id = ?");
$querySolde->execute([$user_id]);
$user_data = $querySolde->fetch(PDO::FETCH_ASSOC);
$solde_user = $user_data['solde'];

// Vérifier que l'utilisateur a suffisamment de solde
if ($solde_user >= $total_panier) {
    try {
        // Début de la transaction
        $pdo->beginTransaction();

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

        // Récupérer l'ID de la commande après insertion
        $facture_id = $pdo->lastInsertId();

        // Mettre à jour les articles du panier pour leur attribuer le commande_id
        $updateCartQuery = $pdo->prepare("UPDATE Cart SET commande_id = :commande_id WHERE user_id = :user_id");
        $updateCartQuery->execute([
            'commande_id' => $facture_id,
            'user_id' => $user_id
        ]);

        // Valider la transaction
        $pdo->commit();

        // Rediriger vers la page de facture avec l'ID de la commande
        header("Location: facture.php?facture_id=$facture_id");
        exit;
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Erreur : Solde insuffisant.";
}
?>
