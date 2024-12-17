<?php
include("config.php");

session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $photo = $_FILES['photo']['name'];
    $quantite = $_POST['quantite'];
    $author_ID = 1; 

    

    if (isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
        $photos = [];
        foreach ($_FILES['photos']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['photos']['error'][$index] === UPLOAD_ERR_OK) {
                $photo_content = file_get_contents($tmpName);
                $photos[] = $photo_content;
            }
        }
    } else {
        // Si aucune photo n'est envoyée
        $photos = [];
    }
    

    // Vérifie qu'il y a au maximum 4 photos
    if (count($photos) > 4) {
        die("Erreur : Vous ne pouvez télécharger que 4 photos au maximum.");
    }

    // Insertion dans la table Article
    $sql_article = "INSERT INTO Article (nom, description, prix, publish_date, author_ID) 
                    VALUES (:nom, :description, :prix, NOW(), :author_ID)";
    $stmt_article = $pdo->prepare($sql_article);
    $stmt_article->execute([
        'nom' => $nom,
        'description' => $description,
        'prix' => $prix,
        'author_ID' => $author_ID
    ]);

    $article_ID = $pdo->lastInsertId(); // Récupère l'ID de l'article

    // Insertion des photos dans une table séparée
    $sql_photo = "INSERT INTO Photos (article_ID, image) VALUES (:article_ID, :image)";
    $stmt_photo = $pdo->prepare($sql_photo);

    foreach ($photos as $photo) {
        $stmt_photo->execute([
            'article_ID' => $article_ID,
            'image' => $photo
        ]);
    }

    // Insertion dans la table Stock
    $sql_stock = "INSERT INTO Stock (article_ID, quantite) VALUES (:article_ID, :quantite)";
    $stmt_stock = $pdo->prepare($sql_stock);
    $stmt_stock->execute([
        'article_ID' => $article_ID,
        'quantite' => $quantite
    ]);

    $message = 'Article mis en vente avec succès avec plusieurs photos !';

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
            <label for="photo">Photos (max 4) :</label>
            <input type="file" id="photo" name="photos[]" accept="image/*" multiple required>
            <small>Vous pouvez télécharger jusqu'à 4 photos.</small>
        </div>
        <script>
        document.getElementById('photo').addEventListener('change', function() {
            if (this.files.length > 4) {
                alert("Vous ne pouvez sélectionner que 4 photos maximum.");
                this.value = ""; // Réinitialise l'input
            }
        });
        </script>

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
