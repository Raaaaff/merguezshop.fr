<?php
include("config.php");
session_start(); // Démarrer la session

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $quantite = $_POST['quantite'];
    $author_ID = $_SESSION['user_id']; 

    $photos = $_FILES['photos'];
    $photo_count = count($photos['name']);

    if ($photo_count > 0 && $photo_count <= 4) {
        $photo_content = file_get_contents($photos['tmp_name'][0]); 

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

        $article_ID = $pdo->lastInsertId();

        // Insertion dans la table Stock
        $sql_stock = "INSERT INTO Stock (article_ID, quantite) VALUES (:article_ID, :quantite)";
        $stmt_stock = $pdo->prepare($sql_stock);
        $stmt_stock->execute([
            'article_ID' => $article_ID,
            'quantite' => $quantite
        ]);

        // Insertion des autres photos dans la table Photos
        for ($i = 1; $i < $photo_count; $i++) {
            $photo_content = file_get_contents($photos['tmp_name'][$i]);
            $sql_photo = "INSERT INTO Photos (article_ID, image) VALUES (:article_ID, :image)";
            $stmt_photo = $pdo->prepare($sql_photo);
            $stmt_photo->execute([
                'article_ID' => $article_ID,
                'image' => $photo_content
            ]);
        }

        $message = 'Article mis en vente avec succès !';
    } else {
        $message = 'Vous devez télécharger entre 1 et 4 images.';
    }
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
            <label for="photos">Photos (jusqu'à 4) :</label>
            <input type="file" id="photos" name="photos[]" accept="image/*" multiple required>
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
