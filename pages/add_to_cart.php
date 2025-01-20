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

    // Vérifier si l'article existe dans le panier de l'utilisateur
    $checkQuery = $pdo->prepare("SELECT * FROM Cart WHERE user_id = :user_id AND article_id = :article_id");
    $checkQuery->execute([
        'user_id' => $user_id,
        'article_id' => $article_id
    ]);
    $cartItem = $checkQuery->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // Si l'article existe, augmenter la quantité
        $updateQuery = $pdo->prepare("UPDATE Cart SET quantite = quantite + 1 WHERE user_id = :user_id AND article_id = :article_id");
        $updateQuery->execute([
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
    } else {
        // Sinon, ajouter un nouvel article dans le panier
        $insertQuery = $pdo->prepare("INSERT INTO Cart (user_id, article_id, quantite) VALUES (:user_id, :article_id, 1)");
        $insertQuery->execute([
            'user_id' => $user_id,
            'article_id' => $article_id
        ]);
    }

    echo "L'article a été ajouté au panier.";
    header("Location: cart.php"); // Rediriger vers la page du panier
    exit;
} else {
    echo "Aucun article sélectionné.";
    exit;
}
