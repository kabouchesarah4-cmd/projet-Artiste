<?php
// session en premier — toujours
session_start();
require_once '../config/database.php';

// déjà connecté → on redirige direct
if (isset($_SESSION['admin_connecte']) && $_SESSION['admin_connecte'] === true) {
    header('Location: dashboard.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant  = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // pas de trim sur le mot de passe

    if (empty($identifiant) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // requête préparée → sécurisée contre les injections SQL
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE identifiant = ?");
        $stmt->execute([$identifiant]);
        $admin = $stmt->fetch();

        // password_verify compare le mot de passe saisi avec le hash stocké en BDD
        if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) {
            $_SESSION['admin_connecte'] = true;
            $_SESSION['admin_id']       = $admin['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            // message flou volontaire → on ne dit pas si c'est l'ID ou le MDP qui est faux
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-page">

<div class="login-box">
    <div class="login-box">
    <p class="login-logo">KAZ <span>AHMED KONÉ</span></p>
    <p class="login-sous-titre">Espace administration</p>

    <?php if (!empty($erreur)): ?>
        <div class="login-erreur"><?php echo htmlspecialchars($erreur); ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="formulaire">

        <div class="champ-groupe" style="text-align: left;">
            <label for="identifiant">Identifiant</label>
            <input type="text" id="identifiant" name="identifiant"
                   placeholder="Votre identifiant" required
                   value="<?php echo htmlspecialchars($_POST['identifiant'] ?? ''); ?>">
        </div>

        <div class="champ-groupe" style="text-align: left;">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe"
                   placeholder="Votre mot de passe" required>
        </div>

        <button type="submit" class="bouton-principal"
                style="width: 100%; margin-top: 1rem; justify-content: center;">
            Se connecter →
        </button>

    </form>

    <a href="../index.html" class="login-retour">← Retour à la galerie publique</a>

</div>

</body>
</html>