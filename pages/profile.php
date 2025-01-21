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

// Récupérer l'ID de l'utilisateur depuis la session
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM User WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Gérer la mise à jour des informations personnelles
if (isset($_POST['update_profile'])) {
    // Récupérer les nouvelles valeurs depuis le formulaire
    $username = $_POST['username'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $solde = $_POST['solde'];

    // Mettre à jour les informations dans la base de données
    $stmt = $pdo->prepare("UPDATE User SET username = :username, nom = :nom, prenom = :prenom, email = :email, solde = :solde WHERE id = :user_id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':solde', $solde);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger après la mise à jour
    header("Location: profile.php");
    exit();
}

// Gérer la mise à jour des articles
if (isset($_POST['update_article'])) {
    $article_id = $_POST['article_id'];
    $nom_article = $_POST['nom_article'];
    $prix_article = $_POST['prix_article'];
    $description_article = $_POST['description_article'];

    // Mettre à jour les informations de l'article dans la base de données
    $stmt = $pdo->prepare("UPDATE Article SET nom = :nom, prix = :prix, description = :description WHERE id = :article_id AND author_ID = :user_id");
    $stmt->bindParam(':nom', $nom_article);
    $stmt->bindParam(':prix', $prix_article);
    $stmt->bindParam(':description', $description_article);
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirection pour éviter la soumission multiple
    header("Location: profile.php");
    exit();
}


// Gérer la suppression d'article
if (isset($_POST['delete_article'])) {
    $article_id = $_POST['article_id'];

    // Supprimer les entrées dans la table Stock qui font référence à cet article
    $stmt = $pdo->prepare("DELETE FROM Stock WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->execute();

    // Supprimer les entrées dans la table Photos qui font référence à cet article
    $stmt = $pdo->prepare("DELETE FROM Photos WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->execute();

    // Supprimer l'article de la table Article
    $stmt = $pdo->prepare("DELETE FROM Article WHERE id = :article_id AND author_ID = :user_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    // Rediriger après suppression
    header("Location: profile.php");
    exit();
}

// Récupérer les articles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Article WHERE author_ID = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MerguezShop</title>
    <link rel="stylesheet" href="css/home.css">
    <style>
        input[type="text"],
        input[type="email"],
        input[type="number"] {
            width: 60%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="number"]:focus {
            border-color: #007BFF;
            outline: none;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
            text-align: left;
        }

        label {
            font-size: 1.1rem;
            font-weight: bold;
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="submit"] {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
            display: inline-block;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .articles-table {
            width: 100%;
            margin-top: 2rem;
            border-collapse: collapse;
        }

        .articles-table th, .articles-table td {
            border: 1px solid #ccc;
            padding: 0.75rem;
            text-align: left;
        }

        .articles-table th {
            background-color: #f4f4f4;
        }

        .articles-table td a {
            color: #007BFF;
            text-decoration: none;
        }

        .articles-table td a:hover {
            text-decoration: underline;
        }

        .articles-table td button {
            padding: 0.5rem 1rem;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .articles-table td button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="top-bar">
            <div class="logo">
                <h1>MerguezShop</h1>
            </div>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="query" placeholder="Rechercher un produit..." required>
                <button type="submit">Rechercher</button>
            </form>
            <nav>
                <ul>
                    <li><a href="home.php">Accueil</a></li>
                    <li><a href="sale.php">Vente</a></li>
                    <li><a href="profile.php">Mon Profil</a></li>
                    <li><a href="cart.php">🛒 Panier</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <h2>Mettre à jour vos informations</h2>
        <form action="profile.php" method="POST">
            <div>
                <label for="username">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
            </div>
            <div>
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>">
            </div>
            <div>
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>">
            </div>
            <div>
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>
            <div>
                <label for="solde">Solde :</label>
                <input type="number" id="solde" name="solde" value="<?php echo htmlspecialchars($user['solde']); ?>" step="0.01">
            </div>
            <input type="submit" name="update_profile" value="Mettre à jour">
        </form>

        <h2>Vos articles</h2>
        <table class="articles-table">
            <thead>
                <tr>
                    <th>Nom de l'article</th>
                    <th>Prix</th>
                    <th>Date de publication</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($articles) > 0): ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><a href="detail.php?id=<?php echo $article['id']; ?>"><?php echo htmlspecialchars($article['nom']); ?></a></td>
                            <td><?php echo number_format($article['prix'], 2, ',', ' '); ?> €</td>
                            <td><?php echo date('d/m/Y', strtotime($article['publish_date'])); ?></td>
                            <td>
                            <form action="profile.php" method="POST">
    <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
    
    <div>
        <label for="nom_article_<?php echo $article['id']; ?>">Nom de l'article :</label>
        <input type="text" id="nom_article_<?php echo $article['id']; ?>" name="nom_article" value="<?php echo htmlspecialchars($article['nom']); ?>">
    </div>
    
    <div>
        <label for="prix_article_<?php echo $article['id']; ?>">Prix :</label>
        <input type="number" id="prix_article_<?php echo $article['id']; ?>" name="prix_article" value="<?php echo htmlspecialchars($article['prix']); ?>" step="0.01">
    </div>
    
    <div>
        <label for="description_article_<?php echo $article['id']; ?>">Description :</label>
        <textarea id="description_article_<?php echo $article['id']; ?>" name="description_article"><?php echo htmlspecialchars($article['description']); ?></textarea>
    </div>

    <div style="margin-top: 1rem;">
        <button type="submit" name="update_article" style="background-color: #28a745; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer;">
            Mettre à jour
        </button>
        
        <button type="submit" name="delete_article" style="background-color: #dc3545; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">
            Supprimer
        </button>
    </div>
</form>



                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Vous n'avez publié aucun article pour le moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
