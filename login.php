<?php
// DÉMARRAGE DE LA SESSION : Doit impérativement être la toute première ligne.
session_start();

// Connexion à la base de données
require_once 'config/database.php';

// Redirection automatique :
// Si l'admin  est déjà connecté, on l'envoie direct au tableau de bord.
if (isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // On ne met jamais de "trim" sur un mot de passe (les espaces peuvent en faire partie)

    if (empty($identifiant) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Requête préparée pour chercher l'utilisateur
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $admin = $stmt->fetch();

        // VÉRIFICATION SÉCURISÉE DU MOT DE PASSE
        // Si on a trouvé un compte, et que le mot de passe correspond au hachage
        if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) {

            // Création des variables de session (Le fameux bracelet VIP)
            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id'] = $admin['id'];

            // Redirection vers l'espace d'administration
            header('Location: dashboard.php');
            exit;
        } else {
            // Message d'erreur volontairement flou (Sécurité : on ne dit pas si c'est l'ID ou le MDP qui est faux)
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin — Kaz Ahmed Koné</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: var(--fond);
            margin: 0;
        }
        .login-box {
            background: #111;
            padding: 3rem;
            border: 1px solid var(--bordure);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-box h1 {
            font-family: 'Playfair Display', serif;
            color: var(--or);
            margin-bottom: 2rem;
            font-weight: 400;
        }
        .erreur {
            background: rgba(139, 0, 0, 0.1);
            border: 1px solid #8b0000;
            color: #ff6b6b;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Accès Réservé</h1>

    <?php if (!empty($erreur)): ?>
        <div class="erreur"><?php echo $erreur; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="formulaire">
        <div class="champ-groupe" style="text-align: left;">
            <label for="identifiant">Identifiant</label>
            <input type="text" id="identifiant" name="identifiant" required>
        </div>

        <div class="champ-groupe" style="text-align: left;">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit" class="bouton-principal" style="width: 100%; margin-top: 1rem; justify-content: center;">
            Se connecter
        </button>
    </form>

    <a href="index.html" style="display: inline-block; margin-top: 2rem; color: var(--texte-discret); font-size: 0.8rem; text-decoration: none; border-bottom: 1px solid transparent; transition: 0.3s;">
        ← Retour à la galerie publique
    </a>
</div>

</body>
</html>