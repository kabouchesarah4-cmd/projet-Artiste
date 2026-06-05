<?php
session_start();

// sécurité
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien_mdp  = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirm_mdp = $_POST['confirm_mdp'] ?? '';

    // on récupère l'id de l'admin stocké lors de la connexion
    $admin_id = $_SESSION['admin_id'] ?? 1; // 1 par défaut si jamais

    if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirm_mdp)) {
        $erreur = "Veuillez remplir tous les champs.";
    } elseif ($nouveau_mdp !== $confirm_mdp) {
        $erreur = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        // On récupère le mot de passe actuel en BDD pour le vérifier
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM admin WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        // On vérifie que l'ancien mot de passe tapé est le bon
        if ($admin && password_verify($ancien_mdp, $admin['mot_de_passe'])) {
            // Hachage du nouveau mot de passe
            $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

            // Mise à jour en BDD
            $update = $pdo->prepare("UPDATE admin SET mot_de_passe = ? WHERE id = ?");
            $update->execute([$nouveau_hash, $admin_id]);

            $message = "Mot de passe modifié avec succès !";
        } else {
            $erreur = "L'ancien mot de passe est incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-logo">KAZ <span>ADMIN</span></div>
        <a href="dashboard.php" class="admin-nav-lien">Tableau de bord</a>
        <a href="produits.php" class="admin-nav-lien">Œuvres</a>
        <a href="ajouter.php" class="admin-nav-lien">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien">Messages</a>
        <a href="profil.php" class="admin-nav-lien actif">Mon Profil</a>
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <main class="admin-main">
        <h1 class="admin-titre">Modifier mon mot de passe</h1>

        <div class="form-container" style="max-width: 500px;">

            <?php if (!empty($message)): ?>
                <div class="succes"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="erreur-admin"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

            <form method="POST" action="profil.php" class="formulaire">
                <div class="champ-groupe">
                    <label for="ancien_mdp">Ancien mot de passe</label>
                    <input type="password" name="ancien_mdp" id="ancien_mdp" required>
                </div>

                <div class="champ-groupe">
                    <label for="nouveau_mdp">Nouveau mot de passe</label>
                    <input type="password" name="nouveau_mdp" id="nouveau_mdp" required>
                </div>

                <div class="champ-groupe">
                    <label for="confirm_mdp">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_mdp" id="confirm_mdp" required>
                </div>

                <button type="submit" class="bouton-principal" style="width: 100%; margin-top: 1.5rem; justify-content: center;">
                    Enregistrer le mot de passe →
                </button>
            </form>
        </div>
    </main>

</div>

</body>
</html>