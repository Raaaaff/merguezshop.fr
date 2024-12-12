<?php
// Inclure la connexion à la base de données
include('config.php');

// Requête pour récupérer les articles
$query = "SELECT * FROM Article";
$stmt = $pdo->prepare($query);
$stmt->execute();

// Récupérer tous les articles
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des articles - MerguezShop</title>
</head>
<body>

<h1>Liste des articles</h1>

<?php if ($articles): ?>
    <ul>
        <?php foreach ($articles as $article): ?>
            <li>
                <h3><?php echo htmlspecialchars($article['nom']); ?></h3>
                <p><?php echo htmlspecialchars($article['description']); ?></p>
                <p>Prix : <?php echo htmlspecialchars($article['prix']); ?> €</p>
                <p><small>Publié le : <?php echo htmlspecialchars($article['publish_date']); ?></small></p>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Aucun article disponible.</p>
<?php endif; ?>

</body>
</html>
