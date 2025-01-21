<?php
// Inclure la connexion à la base de données
include('config.php');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour ajouter un article au panier.";
    exit;
}

// Vérifier si l'article ID est passé en paramètre POST
if (isset($_POST['article_id'])) {
    $article_id = intval($_POST['article_id']);
    $user_id = $_SESSION['user_id'];

    try {
        // Vérifier si l'article existe dans le panier de l'utilisateur
        $checkQuery = $pdo->prepare("SELECT * FROM Cart WHERE user_id = :user_id AND article_id = :article_id");
        $checkQuery->execute([
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
        $cartItem = $checkQuery->fetch(PDO::FETCH_ASSOC);

        // Vérifier la quantité disponible en stock
        $stockQuery = $pdo->prepare("SELECT quantite FROM Stock WHERE article_id = :article_id");
        $stockQuery->execute([ 'article_id' => $article_id ]);
        $stock = $stockQuery->fetch(PDO::FETCH_ASSOC);

        if (!$stock) {
            echo "Cet article n'est pas disponible en stock.";
            exit;
        }

        // Vérifier si la quantité demandée dépasse le stock disponible
        $availableStock = $stock['quantite'];
        if ($cartItem) {
            // Si l'article existe, augmenter la quantité, mais vérifier si le stock le permet
            if ($cartItem['quantite'] < $availableStock) {
                $updateQuery = $pdo->prepare("UPDATE Cart SET quantite = quantite + 1 WHERE user_id = :user_id AND article_id = :article_id");
                $updateQuery->execute([
                    'user_id' => $user_id,
                    'article_id' => $article_id
                ]);
                $message = "La quantité de l'article a été augmentée.";
            } else {
                $message = "Le stock est insuffisant pour ajouter plus d'articles.";
            }
        } else {
            // Sinon, ajouter un nouvel article dans le panier
            if ($availableStock > 0) {
                $insertQuery = $pdo->prepare("INSERT INTO Cart (user_id, article_id, quantite) VALUES (:user_id, :article_id, 1)");
                $insertQuery->execute([
                    'user_id' => $user_id,
                    'article_id' => $article_id
                ]);
            } else {
                $message = "Cet article est en rupture de stock.";
            }
        }

        // Afficher un message à l'utilisateur
        echo "<p>$message</p>";
        
        // Rediriger vers la page de détail après 2 secondes
        echo "<meta http-equiv='refresh' content='2;url=detail.php?id=$article_id'>";

        exit;

    } catch (Exception $e) {
        // Gestion des erreurs
        echo "Une erreur est survenue. Veuillez réessayer plus tard.";
        error_log($e->getMessage()); // Consigner l'erreur dans le fichier de log
    }
} else {
    echo "Aucun article sélectionné.";
    exit;
}
?>
