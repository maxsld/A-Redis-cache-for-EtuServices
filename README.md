# A-Redis-cache-for-EtuServices

Ce projet permet à des utilisateurs de se connecter et de choisir un service entre "Vente" ou "Achat". L'application gère les utilisateurs, leurs connexions et les services choisis à l'aide d'une base de données et d'une API Flask pour vérifier les connexions.

---

## Prérequis

Avant de démarrer l'application, assurez-vous d'avoir les éléments suivants installés sur votre machine :

- **WAMP** ou tout autre serveur local (XAMPP, MAMP, etc.) pour faire tourner le serveur Apache et MySQL.
- **Redis** : utilisé pour suivre les connexions des utilisateurs et éviter les doublons.
- **Flask** : une API pour gérer et vérifier les connexions.

---

## Installation

### 1. Lancer WAMP (ou autre serveur local)

- Téléchargez et installez **WAMP** depuis [ici](https://www.wampserver.com/).
- Ouvrez **WAMP** et assurez-vous que le serveur Apache et MySQL sont bien démarrés (icône verte).

### 2. Lancer Redis

- Téléchargez et installez **Redis** depuis [ici](https://redis.io/download).
- Démarrez Redis via la ligne de commande avec la commande suivante :

  ```bash
  redis-server
  ```

  Assurez-vous que Redis fonctionne sur **localhost:6379**.

### 3. Lancer l'API Flask

- Assurez-vous d'avoir **Python** installé sur votre machine. Vous pouvez vérifier cela avec la commande :

  ```bash
  python --version
  ```

- Installez **Flask** et **redis-py** via `pip` :

  ```bash
  pip install flask redis
  ```

- Téléchargez et exécutez le fichier de l'API Flask (`app.py`). Cette API est utilisée pour vérifier les connexions et gérer la limite des connexions pour chaque utilisateur.

  Vous pouvez démarrer l'API Flask en exécutant la commande suivante dans le répertoire contenant votre `app.py` :

  ```bash
  python app.py
  ```

  L'API sera disponible sur `http://localhost:5000`.

### 4. Configuration de la base de données

- Créez une base de données MySQL appelée **etu_services** dans votre serveur WAMP. Vous pouvez utiliser phpMyAdmin pour cela.
- Importez les tables suivantes dans votre base de données via un fichier SQL ou en exécutant les commandes ci-dessous :

  ```sql
  -- Table des utilisateurs
  CREATE TABLE utilisateurs (
      id INT AUTO_INCREMENT PRIMARY KEY,
      nom VARCHAR(100),
      prenom VARCHAR(100),
      email VARCHAR(100) UNIQUE,
      mot_de_passe VARCHAR(255),
      nb_connexions INT DEFAULT 0
  );

  -- Table des connexions
  CREATE TABLE connexions (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      service VARCHAR(20),  -- 'vente' ou 'achat'
      timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
  );
  ```

### 5. Configuration de l'application

- Modifiez le fichier `config.php` pour inclure les paramètres corrects de votre base de données. Voici un exemple de fichier `config.php` :

  ```php
  <?php
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'etu_services');
  ?>
  ```

- Assurez-vous que le serveur Apache et MySQL dans WAMP sont démarrés.

---

## Lancer l'application

1. **Démarrer WAMP** : Ouvrez WAMP et assurez-vous que les services Apache et MySQL sont en cours d'exécution.
2. **Démarrer Redis** : Ouvrez une fenêtre de terminal et lancez `redis-server` pour démarrer Redis.
3. **Démarrer l'API Flask** : Dans le terminal, exécutez la commande `python app.py` pour démarrer l'API Flask.
4. **Accéder à l'application** : Ouvrez votre navigateur et allez sur `http://localhost/login.php` pour voir l'interface d'accueil.

---

## Fonctionnalités

1. **Connexion Utilisateur** : Les utilisateurs peuvent se connecter avec leur email et mot de passe.
2. **Limitation des Connexions** : Si un utilisateur dépasse 10 connexions en 10 minutes, il ne pourra plus se connecter pendant un certain temps.
3. **Choix du Service** : Lors de la connexion, l'utilisateur peut choisir entre les services "Vente" et "Achat". Le service est enregistré dans la base de données.
4. **Suivi des Connexions** : Les connexions sont suivies et stockées dans Redis pour une gestion efficace des tentatives de connexion et des restrictions.

---

Si vous avez des questions ou des problèmes, n'hésitez pas à contacter le développeur.

