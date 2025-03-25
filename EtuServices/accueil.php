<?php
session_start();
require 'config.php'; // Assurez-vous d'avoir un fichier config.php pour la connexion à la BD

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger si non connecté
    exit();
}

// Connexion à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT prenom, nb_connexions FROM utilisateurs WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Utilisateur introuvable.");
}

$prenom = $user['prenom'];

// Traiter le choix de service (Vente ou Achat)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['service'])) {
    $service = $_POST['service'];
    
    // Insérer le service dans la table connexions
    $stmt_service = $conn->prepare("INSERT INTO connexions (user_id, service) VALUES (?, ?)");
    $stmt_service->bind_param("is", $user_id, $service);
    
    if ($stmt_service->execute()) {
        echo "<p>Votre choix de service ($service) a été enregistré.</p>";
    } else {
        echo "<p>Erreur lors de l'enregistrement de votre choix.</p>";
    }

    // Fermer la requête d'insertion
    $stmt_service->close();
}

// Fermer la requête pour récupérer l'utilisateur
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil</title>
    <style>
        /* Ajouter un style simple pour la page d'accueil */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .service-form {
            margin-top: 20px;
        }
        select, button {
            padding: 10px;
            font-size: 16px;
            margin-top: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        button {
            background-color: #007aff;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #005bb5;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Bonjour, <?php echo $prenom; ?> !</h1>
    <p>Choisissez un service :</p>

    <!-- Formulaire de choix de service -->
    <form method="POST" action="" class="service-form">
        <select name="service" required>
            <option value="vente">Vente</option>
            <option value="achat">Achat</option>
        </select>
        <br>
        <button type="submit">Enregistrer mon choix</button>
    </form>

    <a href="services.php">Voir les statistiques</a>

</div>

</body>
</html>
