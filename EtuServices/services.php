<?php
session_start();
require 'config.php';

// Connexion à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Récupérer le top 3 des utilisateurs ayant le plus de connexions
$query_top_users = "SELECT nom, prenom, email, nb_connexions
                    FROM utilisateurs
                    ORDER BY nb_connexions DESC
                    LIMIT 3";
$result_top_users = $conn->query($query_top_users);

// Récupérer le service le plus utilisé
$query_top_service = "SELECT service, COUNT(*) AS nb_utilisations
                      FROM connexions
                      GROUP BY service
                      ORDER BY nb_utilisations DESC
                      LIMIT 1";
$result_top_service = $conn->query($query_top_service);

// Affichage des résultats
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
</head>
<body>

<h2>Statistiques des Connexions</h2>

<h3>Top 3 des utilisateurs les plus connectés :</h3>
<table border="1">
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Email</th>
        <th>Nombre de connexions</th>
    </tr>
    <?php while ($row = $result_top_users->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['nom']; ?></td>
            <td><?php echo $row['prenom']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['nb_connexions']; ?></td>
        </tr>
    <?php } ?>
</table>

<h3>Service le plus utilisé :</h3>
<?php 
if ($row = $result_top_service->fetch_assoc()) {
    echo "<p>Le service le plus utilisé est : " . $row['service'] . " avec " . $row['nb_utilisations'] . " utilisations.</p>";
} 
?>

</body>
</html>

<?php
$conn->close();
?>
