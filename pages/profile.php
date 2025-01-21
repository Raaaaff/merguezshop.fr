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
?>

<!DOCTYPE html>
<html lang="fr">

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
    </style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MerguezShop</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>
    <!-- En-tête -->
    <header>
        <div class="top-bar">
            <div class="logo">
                <h1>MerguezShop - Mon Profil</h1>
            </div>
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
    </main>

    <!-- Pied de page -->
    <footer>
        <p>&copy; 2024 MerguezShop | Tous droits réservés</p>
    </footer>
</body>
</html>
