<?php
include("config.php");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $photo = $_FILES['photo']['name'];
    $quantite = $_POST['quantite'];
    $author_ID = 1; // Par défaut, on utilise un ID d'auteur (à ajuster)

    // Vérification de l'upload de la photo
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_content = file_get_contents($photo_tmp);
    }

    // Insertion dans la table Article
    $sql_article = "INSERT INTO Article (nom, description, prix, publish_date, image, author_ID) 
                    VALUES (:nom, :description, :prix, NOW(), :image, :author_ID)";
    $stmt_article = $pdo->prepare($sql_article);
    $stmt_article->execute([
        'nom' => $nom,
        'description' => $description,
        'prix' => $prix,
        'image' => $photo_content,
        'author_ID' => $author_ID
    ]);

    // Récupérer l'ID de l'article inséré
    $article_ID = $pdo->lastInsertId();

    // Insertion dans la table Stock
    $sql_stock = "INSERT INTO Stock (article_ID, quantite) VALUES (:article_ID, :quantite)";
    $stmt_stock = $pdo->prepare($sql_stock);
    $stmt_stock->execute([
        'article_ID' => $article_ID,
        'quantite' => $quantite
    ]);

    $message = 'Article mis en vente avec succès !';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vente d'article</title>
    <link rel="stylesheet" href="css/vente.css">
</head>
<body>
<div class="vente-container">
    <h2>Mettre un article en vente</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form action="sale.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nom">Nom de l'article :</label>
            <input type="text" id="nom" name="nom" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <textarea id="description" name="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="prix">Prix :</label>
            <input type="number" id="prix" name="prix" required step="0.01">
        </div>
        <div class="form-group">
            <label for="photo">Photo :</label>
            <input type="file" id="photo" name="photo" accept="image/*" required>
        </div>
        <div class="form-group">
            <label for="quantite">Quantité en stock :</label>
            <input type="number" id="quantite" name="quantite" required>
        </div>
        <div class="form-group">
            <input type="submit" value="Mettre en vente">
        </div>
    </form>
</div>
</body>
</html>
