<?php
// Inclure la connexion à la base de données
include('config.php');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Récupérer l'action (ajouter ou supprimer un favori)
$action = $_GET['action'] ?? null;
$article_id = $_GET['article_id'] ?? null;
$user_id = $_SESSION['user_id'];  // ID de l'utilisateur connecté

if ($action && $article_id) {
    // Vérification si l'article est déjà dans les favoris
    $query = $pdo->prepare("SELECT * FROM favorites WHERE user_id = :user_id AND article_id = :article_id");
    $query->execute(['user_id' => $user_id, 'article_id' => $article_id]);
    $favorite = $query->fetch(PDO::FETCH_ASSOC);

    if ($action == 'toggle') {
        if ($favorite) {
            // Supprimer le favori
            $deleteQuery = $pdo->prepare("DELETE FROM favorites WHERE user_id = :user_id AND article_id = :article_id");
            $deleteQuery->execute(['user_id' => $user_id, 'article_id' => $article_id]);
        } else {
            // Ajouter le favori
            $insertQuery = $pdo->prepare("INSERT INTO favorites (user_id, article_id) VALUES (:user_id, :article_id)");
            $insertQuery->execute(['user_id' => $user_id, 'article_id' => $article_id]);
        }
    }
}

// Rediriger l'utilisateur vers la page d'accueil
header('Location: home.php');
exit();
?>
