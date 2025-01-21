<?php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/merguez_shop/');
}

$host = '192.168.1.57';
$dbname = 'merguezshop';
$user = 'merguezshop';
$password = 'merguezshop';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=$charset";

try {

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo '<script>console.log("Connexion réussie à la base de données merguezshop.");</script>';
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
