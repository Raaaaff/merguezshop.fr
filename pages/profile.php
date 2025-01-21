<?php
// Démarrer la session
session_start();

// Inclure le fichier de configuration
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM User WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Gérer la mise à jour des informations personnelles
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $solde = $_POST['solde'];

    $stmt = $pdo->prepare("UPDATE User SET username = :username, nom = :nom, prenom = :prenom, email = :email, solde = :solde WHERE id = :user_id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prenom', $prenom);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':solde', $solde);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: profile.php");
    exit();
}

// Gérer les autres actions sur les articles (mise à jour, suppression)
if (isset($_POST['update_article'])) {
    $article_id = $_POST['article_id'];
    $nom_article = $_POST['nom_article'];
    $prix_article = $_POST['prix_article'];
    $description_article = $_POST['description_article'];

    $stmt = $pdo->prepare("UPDATE Article SET nom = :nom, prix = :prix, description = :description WHERE id = :article_id AND author_ID = :user_id");
    $stmt->bindParam(':nom', $nom_article);
    $stmt->bindParam(':prix', $prix_article);
    $stmt->bindParam(':description', $description_article);
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: profile.php");
    exit();
}

if (isset($_POST['delete_article'])) {
    $article_id = $_POST['article_id'];

    $stmt = $pdo->prepare("DELETE FROM Stock WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM Photos WHERE article_id = :article_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM Article WHERE id = :article_id AND author_ID = :user_id");
    $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: profile.php");
    exit();
}

// Récupérer les articles de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM Article WHERE author_ID = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles favoris de l'utilisateur
$stmt = $pdo->prepare("SELECT a.* FROM Article a JOIN favorites f ON a.id = f.article_id WHERE f.user_id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MerguezShop</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="icon" type="image/jpeg" href="../img/icon.jpg">
    <style>
        /* Structure de la page */
        .container {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }

        .profile-info {
            width: 60%;
        }

        .favorites {
            width: 35%;
            padding-left: 20px;
            border-left: 2px solid #ccc;
        }

        /* Styles pour les formulaires et boutons */
        input[type="text"], input[type="email"], input[type="number"] {
            width: 60%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        input[type="submit"] {
            padding: 0.75rem 1.5rem;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .favorites-table {
            width: 100%;
            margin-top: 2rem;
            border-collapse: collapse;
        }

        .favorites-table th, .favorites-table td {
            border: 1px solid #ccc;
            padding: 0.75rem;
            text-align: left;
        }

        .favorites-table th {
            background-color: #f4f4f4;
        }

        .favorites-table td a {
            color: #007BFF;
            text-decoration: none;
        }

        .favorites-table td a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo"><h1>MerguezShop</h1></div>
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
        <div class="container">
            <!-- Section Profil -->
            <div class="profile-info">
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
                            <th>Nom</th>
                            <th>Prix</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($article['nom']); ?></td>
                                <td><?php echo htmlspecialchars($article['prix']); ?> €</td>
                                <td>
                                    <form action="profile.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                        <input type="submit" name="delete_article" value="Supprimer">
                                    </form>
                                    <form action="update_article.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                        <input type="submit" name="update_article" value="Modifier">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Section Articles Favoris -->
            <div class="favorites">
                <h2>Articles favoris</h2>
                <table class="favorites-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($favorites as $favorite): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($favorite['nom']); ?></td>
                                <td><?php echo htmlspecialchars($favorite['prix']); ?> €</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
