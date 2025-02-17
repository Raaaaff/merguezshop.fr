<?php

$host = '192.168.1.57';
$dbname = 'merguezshop';
$user = 'merguezshop';
$password = 'merguezshop';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}