<?php
session_start();
require 'config.php'; // Assurez-vous d'avoir un fichier config.php pour la connexion à la BD

// Fonction pour vérifier si l'API est accessible
function is_api_accessible($url) {
    $headers = @get_headers($url);
    return $headers && strpos($headers[0], '200') !== false; // Si le code de réponse contient 200, l'API est accessible
}

// Vérifier la connexion à l'API Flask avant de procéder
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // URL de l'API Flask
        $url = "http://localhost:5000/verifier-connexion?email=" . urlencode($email);

        // Vérifier si l'API est accessible
        if (is_api_accessible($url)) {
            // L'API est accessible, on peut procéder à l'appel
            $response = file_get_contents($url);

            // Décoder la réponse JSON
            $data = json_decode($response, true);

            // Vérification du message de la réponse de l'API
            if (isset($data['message'])) {
                // Si l'API renvoie le message de limitation de connexions
                if ($data['message'] === "Limite de connexions atteinte. Veuillez réessayer plus tard.") {
                    $error = "Vous avez atteint la limite de connexions. Veuillez réessayer plus tard.";
                }
            }

            // Si l'utilisateur n'est pas limité, on continue avec la vérification du mot de passe
            if (!isset($error)) {
                // Connexion à la base de données
                $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

                if ($conn->connect_error) {
                    die("Échec de la connexion : " . $conn->connect_error);
                }

                // Préparer et exécuter la requête pour récupérer l'utilisateur par email
                $stmt = $conn->prepare("SELECT id, nom, prenom, mot_de_passe, nb_connexions FROM utilisateurs WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    // Vérification du mot de passe
                    if (password_verify($password, $user['mot_de_passe'])) {
                        // Incrémenter le nombre de connexions
                        $new_nb_connexions = $user['nb_connexions'] + 1;
                        $update_stmt = $conn->prepare("UPDATE utilisateurs SET nb_connexions = ? WHERE id = ?");
                        $update_stmt->bind_param("ii", $new_nb_connexions, $user['id']);
                        $update_stmt->execute();
                        $update_stmt->close();

                        // Stocker les informations de l'utilisateur dans la session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];

                        // Rediriger l'utilisateur vers la page d'accueil
                        header("Location: accueil.php");
                        exit();
                    } else {
                        $error = "Mot de passe incorrect";
                    }
                } else {
                    $error = "Aucun compte trouvé avec cet email";
                }

                $stmt->close();
                $conn->close();
            }
        
        } else {
            $error = "Vous avez atteint la limite de connexions. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        /* Reset des marges et du padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            background-color: #f1f1f6;
            color: #333;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
            margin: 100px auto;
            background-color: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            font-size: 14px;
            color: #777;
            font-weight: 500;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-top: 8px;
            transition: all 0.3s ease;
            background-color: #f7f7f7;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #007aff;
            outline: none;
            background-color: #ffffff;
        }

        button {
            width: 100%;
            padding: 15px;
            background-color: #007aff;
            color: white;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #005bb5;
        }

        .error {
            color: #ff3b30;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .link {
            font-size: 14px;
            color: #007aff;
            text-decoration: none;
            margin-top: 10px;
        }

        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Connexion</h2>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="post" action="">
        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>

</div>

</body>
</html>
