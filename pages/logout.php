<?php
session_start();

// Vérifier si une session est active
if (session_status() === PHP_SESSION_ACTIVE) {
    // Supprimer toutes les variables de session
    $_SESSION = [];

    // Détruire la session
    session_destroy();
}

// Rediriger vers la page de connexion ou d'accueil
header('Location: login.php');
exit;
?>
