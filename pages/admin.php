<?php
// Démarrer la session
session_start();

// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

// Vérifier si l'utilisateur est un administrateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT admin FROM User WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'est pas un admin, rediriger vers la page d'accueil ou une autre page
if ($user['admin'] != 1) {
    header("Location: home.php"); // Rediriger vers la page d'accueil si non admin
    exit();
}

// Gérer la suppression d'un utilisateur
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM User WHERE id = :delete_id");
    $stmt->bindParam(':delete_id', $delete_id, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger vers la page admin après la suppression
    header("Location: admin.php");
    exit();
}

// Gérer la suppression d'un article
if (isset($_GET['delete_article_id'])) {
    $delete_article_id = $_GET['delete_article_id'];

    // Supprimer d'abord les articles associés dans la table Stock
    $stmt = $pdo->prepare("DELETE FROM Stock WHERE article_id = :delete_article_id");
    $stmt->bindParam(':delete_article_id', $delete_article_id, PDO::PARAM_INT);
    $stmt->execute();

    // Puis supprimer l'article de la table Article
    $stmt = $pdo->prepare("DELETE FROM Article WHERE id = :delete_article_id");
    $stmt->bindParam(':delete_article_id', $delete_article_id, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger vers la page admin après la suppression
    header("Location: admin.php");
    exit();
}


// Récupérer la liste des utilisateurs
$stmt = $pdo->prepare("SELECT id, username, nom, prenom, email FROM User");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des articles
$stmt = $pdo->prepare("
    SELECT a.id, a.nom, a.prix, a.publish_date, u.username 
    FROM Article a
    JOIN User u ON a.author_ID = u.id
");
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - MerguezShop</title>
    <link rel="stylesheet" href="css/home.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="top-bar">
            <div class="logo">
                <h1>MerguezShop - Admin Space</h1>
            </div>
        </div>
    </header>

<main>
    <h2>Utilisateurs</h2>
    <table>
        <thead>
            <tr>
                <th>Pseudo</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <!-- Bouton pour supprimer l'utilisateur -->
                        <a href="admin.php?delete_id=<?php echo $user['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Articles</h2>
    <table>
        <thead>
            <tr>
                <th>Nom de l'article</th>
                <th>Prix</th>
                <th>Date de publication</th>
                <th>Publié par</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><a href="/pages/detail.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['nom']); ?></a></td>
                    <td><?php echo htmlspecialchars($article['prix']); ?> €</td>
                    <td><?php echo htmlspecialchars($article['publish_date']); ?></td>
                    <td><?php echo htmlspecialchars($article['username']); ?></td>
                    <td>
                        <!-- Bouton pour supprimer l'article -->
                        <a href="admin.php?delete_article_id=<?php echo $article['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

</body>
</html>
