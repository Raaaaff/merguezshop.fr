<?php
$host = '192.168.1.97';
$dbname = 'merguezshop';
$user = 'sqlcommuser';
$password = 'GHU7L8jxrs4RBjsB';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=$charset";

try {

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<script>console.log("Connexion réussie à la base de données merguezshop.");</script>';
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
