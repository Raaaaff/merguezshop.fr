<?php

include('config.php');

$sql = "SELECT image FROM Article WHERE ID = :article_ID";
$stmt = $pdo->prepare($sql);
$stmt->execute(['article_ID' => $article_ID]);
$imageData = $stmt->fetchColumn(); 

if ($imageData) {
    $imageBase64 = base64_encode($imageData);  
} else {
    $imageBase64 = null; 
}