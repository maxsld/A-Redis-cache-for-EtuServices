<?php
$conn = new mysqli("localhost", "root", "", "etu_services");

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Liste des utilisateurs à insérer
$utilisateurs = [
    ["nom" => "Soldan", "prenom" => "Maxens", "email" => "soldan.maxens@gmail.com", "mot_de_passe" => "admin123"],
    ["nom" => "Dupont", "prenom" => "Pierre", "email" => "dupont.pierre@example.com", "mot_de_passe" => "password123"],
    ["nom" => "Martin", "prenom" => "Sophie", "email" => "martin.sophie@example.com", "mot_de_passe" => "sophie456"],
    ["nom" => "Leclerc", "prenom" => "Lucas", "email" => "leclerc.lucas@example.com", "mot_de_passe" => "lucas789"],
    ["nom" => "Lemoine", "prenom" => "Julie", "email" => "lemoine.julie@example.com", "mot_de_passe" => "julie1010"],
    ["nom" => "Durand", "prenom" => "Éric", "email" => "durand.eric@example.com", "mot_de_passe" => "eric2020"],
    ["nom" => "Hernandez", "prenom" => "Alba", "email" => "hernandez.alba@example.com", "mot_de_passe" => "alba1234"],
    ["nom" => "Benoit", "prenom" => "Charlotte", "email" => "benoit.charlotte@example.com", "mot_de_passe" => "charlotte5678"],
    ["nom" => "Gomez", "prenom" => "Luis", "email" => "gomez.luis@example.com", "mot_de_passe" => "luis9876"],
    ["nom" => "Pierre", "prenom" => "Michel", "email" => "pierre.michel@example.com", "mot_de_passe" => "michel333"]
];

// Insertion des utilisateurs dans la base de données
$stmt = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");

foreach ($utilisateurs as $utilisateur) {
    $nom = $utilisateur['nom'];
    $prenom = $utilisateur['prenom'];
    $email = $utilisateur['email'];
    $mot_de_passe = $utilisateur['mot_de_passe'];
    $hashed_password = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    $stmt->bind_param("ssss", $nom, $prenom, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "Utilisateur $prenom $nom inséré avec succès.<br>";

        // Incrémenter le nombre de connexions pour l'utilisateur
        $update_connexions_stmt = $conn->prepare("UPDATE utilisateurs SET nb_connexions = nb_connexions + 1 WHERE email = ?");
        $update_connexions_stmt->bind_param("s", $email);
        $update_connexions_stmt->execute();

        // Enregistrer une connexion dans la table connexions pour un service aléatoire ('vente' ou 'achat')
        $services = ['vente', 'achat'];
        $service = $services[array_rand($services)];

        $insert_connexion_stmt = $conn->prepare("INSERT INTO connexions (user_id, service) SELECT id, ? FROM utilisateurs WHERE email = ?");
        $insert_connexion_stmt->bind_param("ss", $service, $email);
        $insert_connexion_stmt->execute();
        
        echo "Connexion au service '$service' enregistrée pour $prenom $nom.<br>";

        $insert_connexion_stmt->close();
        $update_connexions_stmt->close();
    } else {
        echo "Erreur lors de l'insertion de $prenom $nom : " . $stmt->error . "<br>";
    }
}

$stmt->close();
$conn->close();
?>
